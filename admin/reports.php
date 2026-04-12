<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'reports';
$pageTitle = 'Bao cao & Thong ke';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

$from = (string)($_GET['from'] ?? date('Y-m-01'));
$to = (string)($_GET['to'] ?? date('Y-m-d'));

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $from = date('Y-m-01');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $to = date('Y-m-d');
}
if (strtotime($from) > strtotime($to)) {
    [$from, $to] = [$to, $from];
}

$fromDT = $from . ' 00:00:00';
$toDT = $to . ' 23:59:59';

$totalRevenue = 0.0;
$carsSold = 0;
$newCustomers = 0;
$ordersCount = 0;
$topCars = [];
$topEmployees = [];

try {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount),0)
        FROM orders
        WHERE order_date BETWEEN ? AND ?
          AND status IN ('confirmed','completed')
    ");
    $stmt->execute([$fromDT, $toDT]);
    $totalRevenue = (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(quantity),0)
        FROM orders
        WHERE order_date BETWEEN ? AND ?
          AND status = 'completed'
    ");
    $stmt->execute([$fromDT, $toDT]);
    $carsSold = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM customers
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$fromDT, $toDT]);
    $newCustomers = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM orders
        WHERE order_date BETWEEN ? AND ?
    ");
    $stmt->execute([$fromDT, $toDT]);
    $ordersCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT c.name AS car_name,
               SUM(od.quantity) AS qty,
               SUM(od.total_price) AS revenue
        FROM order_details od
        JOIN orders o ON o.id = od.order_id
        JOIN cars c ON c.id = od.car_id
        WHERE o.order_date BETWEEN ? AND ?
          AND o.status IN ('confirmed','completed')
        GROUP BY c.id, c.name
        ORDER BY revenue DESC
        LIMIT 10
    ");
    $stmt->execute([$fromDT, $toDT]);
    $topCars = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT COALESCE(a.full_name, 'Khong xac dinh') AS admin_name,
               COUNT(o.id) AS orders_total,
               COALESCE(SUM(o.total_amount),0) AS revenue
        FROM orders o
        LEFT JOIN admins a ON a.id = o.created_by_admin_id
        WHERE o.order_date BETWEEN ? AND ?
        GROUP BY a.id, a.full_name
        ORDER BY revenue DESC
        LIMIT 10
    ");
    $stmt->execute([$fromDT, $toDT]);
    $topEmployees = $stmt->fetchAll();
} catch (Throwable $e) {
    $totalRevenue = 0;
    $carsSold = 0;
    $newCustomers = 0;
    $ordersCount = 0;
    $topCars = [];
    $topEmployees = [];
}

if ((string)($_GET['export'] ?? '') === 'csv') {
    $fileName = 'report-' . $from . '-to-' . $to . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');

    $out = fopen('php://output', 'w');
    if ($out !== false) {
        fputcsv($out, ['FLCar Report']);
        fputcsv($out, ['From', $from, 'To', $to]);
        fputcsv($out, []);
        fputcsv($out, ['Summary']);
        fputcsv($out, ['Total Revenue', number_format($totalRevenue, 2, '.', '')]);
        fputcsv($out, ['Cars Sold (completed)', $carsSold]);
        fputcsv($out, ['New Customers', $newCustomers]);
        fputcsv($out, ['Orders Count', $ordersCount]);
        fputcsv($out, []);
        fputcsv($out, ['Top Cars']);
        fputcsv($out, ['Car Name', 'Quantity', 'Revenue']);
        foreach ($topCars as $row) {
            fputcsv($out, [
                (string)$row['car_name'],
                (int)$row['qty'],
                number_format((float)$row['revenue'], 2, '.', ''),
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['Top Employees']);
        fputcsv($out, ['Admin Name', 'Orders', 'Revenue']);
        foreach ($topEmployees as $row) {
            fputcsv($out, [
                (string)$row['admin_name'],
                (int)$row['orders_total'],
                number_format((float)$row['revenue'], 2, '.', ''),
            ]);
        }
        fclose($out);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body class="admin-body">
<?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

  <main class="admin-content">
    <div class="admin-header d-flex justify-between align-items-center mb-4">
      <h1 class="admin-title" style="margin:0;"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap">
        <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
          <input type="date" name="from" value="<?php echo htmlspecialchars($from, ENT_QUOTES, 'UTF-8'); ?>" class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155;">
          <span style="display:flex; align-items:center;">-</span>
          <input type="date" name="to" value="<?php echo htmlspecialchars($to, ENT_QUOTES, 'UTF-8'); ?>" class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155;">
          <button class="btn-add" type="submit">Loc</button>
        </form>
        <a href="?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&export=csv" class="btn-add" style="background:#10b981;">
          Xuat CSV
        </a>
      </div>
    </div>

    <div class="stat-cards" style="margin-bottom:24px;">
      <div class="stat-card">
        <div class="stat-info"><h3>$<?php echo number_format($totalRevenue, 2); ?></h3><p>Tong doanh thu</p></div>
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3><?php echo number_format($carsSold); ?></h3><p>Xe da giao</p></div>
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2"/><path d="M9 17h6"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3><?php echo number_format($newCustomers); ?></h3><p>Khach hang moi</p></div>
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3><?php echo number_format($ordersCount); ?></h3><p>Tong don hang</p></div>
        <div class="stat-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div>
      </div>
    </div>

    <div style="display:flex; gap:24px; flex-wrap:wrap;">
      <div class="panel" style="flex:1; min-width:320px;">
        <div class="panel-header"><h2 style="font-size:1rem; margin:0; font-weight:600;">Top xe doanh thu</h2></div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Dong xe</th>
              <th>So luong</th>
              <th>Doanh thu</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$topCars): ?>
              <tr><td colspan="3" class="text-center" style="color:#64748b;">Chua co du lieu</td></tr>
            <?php else: ?>
              <?php foreach ($topCars as $r): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars((string)$r['car_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                  <td><?php echo (int)$r['qty']; ?></td>
                  <td><strong style="color:var(--primary)">$<?php echo number_format((float)$r['revenue'], 2); ?></strong></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="panel" style="flex:1; min-width:320px;">
        <div class="panel-header"><h2 style="font-size:1rem; margin:0; font-weight:600;">Hieu suat admin tao don</h2></div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Admin</th>
              <th>So don</th>
              <th>Doanh thu</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$topEmployees): ?>
              <tr><td colspan="3" class="text-center" style="color:#64748b;">Chua co du lieu</td></tr>
            <?php else: ?>
              <?php foreach ($topEmployees as $r): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars((string)$r['admin_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                  <td><?php echo (int)$r['orders_total']; ?></td>
                  <td><strong style="color:var(--primary)">$<?php echo number_format((float)$r['revenue'], 2); ?></strong></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

</body>
</html>

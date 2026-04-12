<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'orders';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectOrders(string $msg): void
{
    header('Location: orders.php?msg=' . urlencode($msg));
    exit;
}

function nextOrderNo(PDO $pdo): string
{
    $row = $pdo->query('SELECT order_no FROM orders WHERE order_no LIKE "DH-%" ORDER BY id DESC LIMIT 1')->fetch();
    if (!$row || empty($row['order_no'])) {
        return 'DH-000001';
    }
    $num = (int)preg_replace('/[^0-9]/', '', (string)$row['order_no']);
    return 'DH-' . str_pad((string)($num + 1), 6, '0', STR_PAD_LEFT);
}

$msg = (string)($_GET['msg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add') {
            $orderNo = trim((string)($_POST['order_no'] ?? ''));
            $customerId = (int)($_POST['customer_id'] ?? 0);
            $carId = (int)($_POST['car_id'] ?? 0);
            $orderType = (string)($_POST['order_type'] ?? 'purchase');
            $status = (string)($_POST['status'] ?? 'pending');
            $qty = max(1, (int)($_POST['quantity'] ?? 1));
            $unitPrice = max(0.0, (float)($_POST['unit_price'] ?? 0));
            $totalAmount = round($qty * $unitPrice, 2);

            if ($orderNo === '') {
                $orderNo = nextOrderNo($pdo);
            }
            if ($customerId <= 0 || $carId <= 0) {
                redirectOrders('invalid_data');
            }
            if (!in_array($orderType, ['purchase', 'deposit', 'consultation', 'test_drive', 'installment'], true)) {
                $orderType = 'purchase';
            }
            if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
                $status = 'pending';
            }

            $stmt = $pdo->prepare('
                INSERT INTO orders
                (order_no, customer_id, car_id, order_type, quantity, unit_price, total_amount, status, created_by_admin_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $orderNo,
                $customerId,
                $carId,
                $orderType,
                $qty,
                $unitPrice,
                $totalAmount,
                $status,
                (int)($_SESSION['admin_id'] ?? 0) ?: null,
            ]);

            redirectOrders('added');
        }

        if ($action === 'update_status') {
            $id = (int)($_POST['order_id'] ?? 0);
            $status = (string)($_POST['status'] ?? 'pending');
            if ($id <= 0) {
                redirectOrders('invalid_data');
            }
            if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
                $status = 'pending';
            }

            $oldStmt = $pdo->prepare('SELECT status FROM orders WHERE id = ?');
            $oldStmt->execute([$id]);
            $oldStatus = (string)($oldStmt->fetchColumn() ?? '');

            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);

            if ($oldStatus !== '' && $oldStatus !== $status) {
                $logStmt = $pdo->prepare('
                    INSERT INTO order_status_logs (order_id, old_status, new_status, changed_by_admin_id, note)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $logStmt->execute([
                    $id,
                    $oldStatus,
                    $status,
                    (int)($_SESSION['admin_id'] ?? 0) ?: null,
                    'Updated from admin/orders.php',
                ]);
            }

            redirectOrders('status_updated');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['order_id'] ?? 0);
            if ($id <= 0) {
                redirectOrders('invalid_data');
            }
            $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
            $stmt->execute([$id]);
            redirectOrders('deleted');
        }
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            redirectOrders('duplicate');
        }
        redirectOrders('db_error');
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = (string)($_GET['status'] ?? '');

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(o.order_no LIKE :q OR c.full_name LIKE :q OR c.phone LIKE :q OR car.name LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
    $where[] = 'o.status = :status';
    $params[':status'] = $statusFilter;
}

$sql = "
    SELECT o.*, c.full_name, c.phone, car.name AS car_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    JOIN cars car ON o.car_id = car.id
";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY o.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$customers = [];
$carsForSale = [];
$stats = ['total' => 0, 'pending' => 0, 'revenue' => 0.0];
try {
    $customers = $pdo->query('SELECT id, full_name, phone FROM customers ORDER BY id DESC')->fetchAll();
    $carsForSale = $pdo->query("SELECT id, name, price FROM cars WHERE status != 'sold' ORDER BY id DESC")->fetchAll();
    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $stats['pending'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
    $stats['revenue'] = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('confirmed','completed')")->fetchColumn();
} catch (Throwable $ignored) {
}

$alertMap = [
    'added' => ['success', 'Da tao don hang moi.'],
    'status_updated' => ['info', 'Da cap nhat trang thai don hang.'],
    'deleted' => ['warning', 'Da xoa don hang.'],
    'duplicate' => ['danger', 'Ma don hang da ton tai.'],
    'invalid_data' => ['danger', 'Du lieu khong hop le.'],
    'db_error' => ['danger', 'Co loi CSDL khi xu ly thao tac.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Don Hang - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }
  .table > :not(caption) > * > * { padding: 14px 12px; border-bottom-color: #f1f5f9; vertical-align: middle; }
  .mini-stat { background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
  .mini-stat h3 { margin:0; font-size:1.3rem; font-weight:800; }
  .mini-stat p { margin:2px 0 0; color:#64748b; font-size:.8rem; }
</style>
</head>
<body>

<div class="admin-wrapper" id="adminWrapper">
  <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <div class="admin-main">
    <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

    <main class="admin-content p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h4 fw-bold text-dark mb-1">Quan Ly Don Hang</h2>
          <p class="text-secondary mb-0 small">Tao don, cap nhat trang thai va quan ly giao dich.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['total']; ?></h3><p>Tong don hang</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['pending']; ?></h3><p>Cho xu ly</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3>$<?php echo number_format($stats['revenue'], 2); ?></h3><p>Doanh thu da xac nhan</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <?php if (!$customers || !$carsForSale): ?>
            <div class="alert alert-warning mb-0">Can co du lieu Customer va Car truoc khi tao don hang.</div>
          <?php else: ?>
            <form method="POST" class="row g-3 align-items-end">
              <input type="hidden" name="action" value="add">
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Ma don</label>
                <input type="text" name="order_no" class="form-control bg-light border-0" placeholder="<?php echo htmlspecialchars(nextOrderNo($pdo), ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Khach hang</label>
                <select name="customer_id" class="form-select bg-light border-0" required>
                  <option value="">Chon khach hang</option>
                  <?php foreach ($customers as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars((string)($c['full_name'] . ' - ' . $c['phone']), ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Xe</label>
                <select name="car_id" id="carIdSelect" class="form-select bg-light border-0" required>
                  <option value="">Chon xe</option>
                  <?php foreach ($carsForSale as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>" data-price="<?php echo (float)$c['price']; ?>">
                      <?php echo htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-1">
                <label class="form-label small fw-bold text-secondary">SL</label>
                <input type="number" min="1" value="1" name="quantity" id="qtyInput" class="form-control bg-light border-0" required>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Don gia ($)</label>
                <input type="number" min="0" step="0.01" name="unit_price" id="unitPriceInput" class="form-control bg-light border-0" required>
              </div>
              <div class="col-md-1">
                <label class="form-label small fw-bold text-secondary">Loai</label>
                <select name="order_type" class="form-select bg-light border-0">
                  <option value="purchase">Mua</option>
                  <option value="deposit">Dat coc</option>
                  <option value="installment">Tra gop</option>
                  <option value="consultation">Tu van</option>
                  <option value="test_drive">Lai thu</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Trang thai</label>
                <select name="status" class="form-select bg-light border-0">
                  <option value="pending">Cho duyet</option>
                  <option value="confirmed">Xac nhan</option>
                  <option value="completed">Hoan tat</option>
                  <option value="cancelled">Huy</option>
                </select>
              </div>
              <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-primary fw-bold px-4">Tao don</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-3">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Tim ma don, ten KH, sdt, ten xe..."
                     value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select bg-light border-0">
                <option value="">Tat ca trang thai</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Cho duyet</option>
                <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Xac nhan</option>
                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Hoan tat</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Huy</option>
              </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
              <button class="btn btn-outline-primary fw-semibold" type="submit">Loc</button>
              <a href="orders.php" class="btn btn-outline-secondary fw-semibold">Reset</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <th>Ma don</th>
                  <th>Khach hang</th>
                  <th>Xe</th>
                  <th>So luong</th>
                  <th>Tong tien</th>
                  <th>Trang thai</th>
                  <th class="text-end">Thao tac</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$orders): ?>
                  <tr><td colspan="7" class="text-center py-5 text-muted">Chua co don hang nao.</td></tr>
                <?php else: ?>
                  <?php foreach ($orders as $o): ?>
                    <tr>
                      <td class="fw-bold text-dark"><?php echo htmlspecialchars((string)$o['order_no'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)$o['full_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-secondary small"><?php echo htmlspecialchars((string)$o['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                      </td>
                      <td class="text-secondary fw-semibold"><?php echo htmlspecialchars((string)$o['car_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo (int)$o['quantity']; ?></td>
                      <td class="fw-bold">$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
                      <td>
                        <form method="POST" class="d-flex gap-2 align-items-center justify-content-start">
                          <input type="hidden" name="action" value="update_status">
                          <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                          <select name="status" class="form-select form-select-sm bg-light border-0" style="min-width:120px;">
                            <option value="pending" <?php echo ($o['status'] === 'pending') ? 'selected' : ''; ?>>Cho duyet</option>
                            <option value="confirmed" <?php echo ($o['status'] === 'confirmed') ? 'selected' : ''; ?>>Xac nhan</option>
                            <option value="completed" <?php echo ($o['status'] === 'completed') ? 'selected' : ''; ?>>Hoan tat</option>
                            <option value="cancelled" <?php echo ($o['status'] === 'cancelled') ? 'selected' : ''; ?>>Huy</option>
                          </select>
                          <button type="submit" class="btn btn-sm btn-outline-primary">Luu</button>
                        </form>
                      </td>
                      <td class="text-end">
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xoa don hang nay?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Xoa</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const carSelect = document.getElementById('carIdSelect');
  const unitPriceInput = document.getElementById('unitPriceInput');
  if (!carSelect || !unitPriceInput) return;

  carSelect.addEventListener('change', function () {
    const price = this.options[this.selectedIndex]?.getAttribute('data-price') || '0';
    unitPriceInput.value = price;
  });
})();
</script>
</body>
</html>

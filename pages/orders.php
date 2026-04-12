<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../bootstrap/shop.php';
require_once __DIR__ . '/../bootstrap/order.php';
require_once __DIR__ . '/../config/database.php';

ensureSessionStarted();

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

function countCustomerOrdersByStatuses(PDO $pdo, int $customerId, array $statuses): int
{
    if ($customerId <= 0 || !$statuses) {
        return 0;
    }
    $in = implode(',', array_fill(0, count($statuses), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status IN ($in)");
    $stmt->execute(array_merge([$customerId], $statuses));
    return (int)$stmt->fetchColumn();
}

function sumCustomerOrdersByStatuses(PDO $pdo, int $customerId, array $statuses): float
{
    if ($customerId <= 0 || !$statuses) {
        return 0.0;
    }
    $in = implode(',', array_fill(0, count($statuses), '?'));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE customer_id = ? AND status IN ($in)");
    $stmt->execute(array_merge([$customerId], $statuses));
    return (float)$stmt->fetchColumn();
}

$currentPage = 'orders';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();
ensureOrderStatusSchema($pdo);
$statusOptions = orderStatusMap();

$msg = (string)($_GET['msg'] ?? '');
$statusFilter = (string)($_GET['status'] ?? '');
if ($statusFilter !== '' && !isset($statusOptions[$statusFilter])) {
    $statusFilter = '';
}

$customer = shopGetCurrentCustomer($pdo, true);
$orders = [];
$stats = [
    'total' => 0,
    'pending' => 0,
    'spent' => 0.0,
];

if ($customer) {
    $customerId = (int)($customer['id'] ?? 0);

    $where = ['o.customer_id = :customer_id'];
    $params = [':customer_id' => $customerId];
    if ($statusFilter !== '') {
        $where[] = 'o.status = :status';
        $params[':status'] = $statusFilter;
    }

    $sql = "
        SELECT o.*, c.name AS car_name
        FROM orders o
        JOIN cars c ON c.id = o.car_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY o.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    $totalStmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE customer_id = ?');
    $totalStmt->execute([$customerId]);
    $stats['total'] = (int)$totalStmt->fetchColumn();
    $stats['pending'] = countCustomerOrdersByStatuses($pdo, $customerId, orderPendingStatuses());
    $stats['spent'] = sumCustomerOrdersByStatuses($pdo, $customerId, orderRevenueStatuses());
}

$alertMap = [
    'checkout_success' => ['success', 'Đặt hàng thành công. Đơn của bạn đã được tạo và đang chờ xử lý.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đơn Mua - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
<style>
  .mini-stat { background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
  .mini-stat h3 { margin:0; font-size:1.3rem; font-weight:800; }
  .mini-stat p { margin:2px 0 0; color:#64748b; font-size:.8rem; }
</style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <h1 class="display-5 fw-bold">Đơn Mua</h1>
    <p class="lead mb-0" style="opacity:.85">Theo dõi trạng thái đơn và lịch sử mua xe online</p>
  </div>
</section>

<section style="padding:60px 0; background:#f8fafc;">
  <div class="container">
    <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
      <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
    <?php endif; ?>

    <?php if (!$customer): ?>
      <div class="alert alert-warning border-0 rounded-3">Không thể xác định hồ sơ khách hàng của bạn.</div>
    <?php else: ?>
      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['total']; ?></h3><p>Tổng đơn</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['pending']; ?></h3><p>Đang chờ xử lý</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3>$<?php echo number_format($stats['spent'], 2); ?></h3><p>Giá trị đã xác nhận</p></div></div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-3">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
              <select name="status" class="form-select bg-light border-0">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($statusOptions as $statusKey => $statusMeta): ?>
                  <option value="<?php echo htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $statusFilter === $statusKey ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$statusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-8 d-flex gap-2">
              <button class="btn btn-outline-primary fw-semibold" type="submit">Lọc</button>
              <a href="orders.php" class="btn btn-outline-secondary fw-semibold">Đặt lại</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:0.75rem;letter-spacing:0.5px;">
                  <th>Mã đơn</th>
                  <th>Xe</th>
                  <th>Số lượng</th>
                  <th>Tổng tiền</th>
                  <th>Thanh toán</th>
                  <th>Trạng thái</th>
                  <th>Ngày tạo</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$orders): ?>
                  <tr><td colspan="7" class="text-center py-5 text-muted">Chưa có đơn mua nào.</td></tr>
                <?php else: ?>
                  <?php foreach ($orders as $o): ?>
                    <?php
                    $orderStatus = normalizeOrderStatus((string)$o['status']);
                    $paymentStatus = (string)($o['payment_status'] ?? 'unpaid');
                    ?>
                    <tr>
                      <td class="fw-bold"><?php echo htmlspecialchars((string)$o['order_no'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)$o['car_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo (int)$o['quantity']; ?></td>
                      <td class="fw-semibold">$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
                      <td>
                        <?php if ($paymentStatus === 'paid'): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle">Đã trả đủ</span>
                        <?php elseif ($paymentStatus === 'deposit_paid'): ?>
                          <span class="badge bg-info-subtle text-info border border-info-subtle">Đã đặt cọc</span>
                        <?php elseif ($paymentStatus === 'refunded'): ?>
                          <span class="badge bg-dark-subtle text-dark border border-dark-subtle">Đã hoàn tiền</span>
                        <?php else: ?>
                          <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Chưa trả</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge <?php echo htmlspecialchars(orderStatusBadgeClass($orderStatus), ENT_QUOTES, 'UTF-8'); ?>">
                          <?php echo htmlspecialchars(orderStatusLabel($orderStatus), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      </td>
                      <td class="small text-secondary"><?php echo htmlspecialchars((string)$o['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

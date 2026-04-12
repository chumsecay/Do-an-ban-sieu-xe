<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'order_details';
$pageTitle = 'Chi tiết hóa đơn';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectDetail(string $msg): void
{
    header('Location: order_details.php?msg=' . urlencode($msg));
    exit;
}

function normalizeMoney(float $value): float
{
    if ($value < 0) {
        return 0.0;
    }
    return round($value, 2);
}

$msg = (string)($_GET['msg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add') {
            $orderId = (int)($_POST['order_id'] ?? 0);
            $carId = (int)($_POST['car_id'] ?? 0);
            $qty = max(1, (int)($_POST['quantity'] ?? 1));
            $unitPrice = normalizeMoney((float)($_POST['unit_price'] ?? 0));
            $discount = normalizeMoney((float)($_POST['discount_amount'] ?? 0));
            $total = normalizeMoney(($qty * $unitPrice) - $discount);

            if ($orderId <= 0 || $carId <= 0) {
                redirectDetail('invalid_data');
            }

            $stmt = $pdo->prepare('INSERT INTO order_details (order_id, car_id, quantity, unit_price, discount_amount, total_price) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$orderId, $carId, $qty, $unitPrice, $discount, $total]);
            redirectDetail('added');
        }

        if ($action === 'edit') {
            $detailId = (int)($_POST['detail_id'] ?? 0);
            $orderId = (int)($_POST['order_id'] ?? 0);
            $carId = (int)($_POST['car_id'] ?? 0);
            $qty = max(1, (int)($_POST['quantity'] ?? 1));
            $unitPrice = normalizeMoney((float)($_POST['unit_price'] ?? 0));
            $discount = normalizeMoney((float)($_POST['discount_amount'] ?? 0));
            $total = normalizeMoney(($qty * $unitPrice) - $discount);

            if ($detailId <= 0 || $orderId <= 0 || $carId <= 0) {
                redirectDetail('invalid_data');
            }

            $stmt = $pdo->prepare('UPDATE order_details SET order_id = ?, car_id = ?, quantity = ?, unit_price = ?, discount_amount = ?, total_price = ? WHERE id = ?');
            $stmt->execute([$orderId, $carId, $qty, $unitPrice, $discount, $total, $detailId]);
            redirectDetail('updated');
        }

        if ($action === 'delete') {
            $detailId = (int)($_POST['detail_id'] ?? 0);
            if ($detailId <= 0) {
                redirectDetail('invalid_data');
            }
            $stmt = $pdo->prepare('DELETE FROM order_details WHERE id = ?');
            $stmt->execute([$detailId]);
            redirectDetail('deleted');
        }
    } catch (Throwable $e) {
        redirectDetail('db_error');
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$carFilter = (int)($_GET['car_id'] ?? 0);
$editId = (int)($_GET['edit'] ?? 0);

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(o.order_no LIKE :q OR c.name LIKE :q OR cu.full_name LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($carFilter > 0) {
    $where[] = 'od.car_id = :car_id';
    $params[':car_id'] = $carFilter;
}

$sql = '
    SELECT od.*, o.order_no, o.customer_id,
           c.name AS car_name,
           cu.full_name AS customer_name
    FROM order_details od
    JOIN orders o ON o.id = od.order_id
    JOIN cars c ON c.id = od.car_id
    LEFT JOIN customers cu ON cu.id = o.customer_id
';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY od.created_at DESC, od.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$details = $stmt->fetchAll();

$orders = [];
$cars = [];
try {
    $orders = $pdo->query('SELECT id, order_no FROM orders ORDER BY id DESC')->fetchAll();
    $cars = $pdo->query('SELECT id, name, price FROM cars ORDER BY id DESC')->fetchAll();
} catch (Throwable $ignored) {
}

$stats = ['lines' => 0, 'amount' => 0.0];
try {
    $stats['lines'] = (int)$pdo->query('SELECT COUNT(*) FROM order_details')->fetchColumn();
    $stats['amount'] = (float)$pdo->query('SELECT COALESCE(SUM(total_price),0) FROM order_details')->fetchColumn();
} catch (Throwable $ignored) {
}

$editDetail = null;
if ($editId > 0) {
    $s = $pdo->prepare('SELECT * FROM order_details WHERE id = ? LIMIT 1');
    $s->execute([$editId]);
    $editDetail = $s->fetch() ?: null;
}

$alertMap = [
    'added' => ['success', 'Đã thêm dòng chi tiết hóa đơn.'],
    'updated' => ['info', 'Đã cập nhật dòng chi tiết hóa đơn.'],
    'deleted' => ['warning', 'Đã xóa dòng chi tiết hóa đơn.'],
    'invalid_data' => ['danger', 'Dữ liệu không hợp lệ.'],
    'db_error' => ['danger', 'Có lỗi CSDL khi xử lý thao tác.'],
];
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
<style>
  body { font-family: 'Inter', sans-serif !important; background:#f8fafc; }
  .table > :not(caption) > * > * { padding: 14px 12px; vertical-align: middle; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Chi tiết hóa đơn</h2>
          <p class="text-secondary small mb-0">Quản lý các dòng sản phẩm trong hóa đơn.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="mini-stat"><h3><?php echo $stats['lines']; ?></h3><p>Tổng số dòng chi tiết</p></div></div>
        <div class="col-md-6"><div class="mini-stat"><h3>$<?php echo number_format($stats['amount'], 2); ?></h3><p>Tổng giá trị chi tiết</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <?php if (!$orders || !$cars): ?>
            <div class="alert alert-warning mb-0">Cần có dữ liệu Orders và Cars trước khi tạo chi tiết hóa đơn.</div>
          <?php else: ?>
            <form method="POST" class="row g-3 align-items-end">
              <input type="hidden" name="action" value="<?php echo $editDetail ? 'edit' : 'add'; ?>">
              <?php if ($editDetail): ?>
                <input type="hidden" name="detail_id" value="<?php echo (int)$editDetail['id']; ?>">
              <?php endif; ?>

              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Hóa đơn</label>
                <select name="order_id" class="form-select bg-light border-0" required>
                  <option value="">Chọn hóa đơn</option>
                  <?php foreach ($orders as $o): ?>
                    <option value="<?php echo (int)$o['id']; ?>" <?php echo ((int)($editDetail['order_id'] ?? 0) === (int)$o['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$o['order_no'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Xe</label>
                <select name="car_id" class="form-select bg-light border-0" required>
                  <option value="">Chọn xe</option>
                  <?php foreach ($cars as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)($editDetail['car_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Số lượng</label>
                <input type="number" min="1" name="quantity" class="form-control bg-light border-0" required
                       value="<?php echo htmlspecialchars((string)($editDetail['quantity'] ?? 1), ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Đơn giá ($)</label>
                <input type="number" min="0" step="0.01" name="unit_price" class="form-control bg-light border-0" required
                       value="<?php echo htmlspecialchars((string)($editDetail['unit_price'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Giảm giá ($)</label>
                <input type="number" min="0" step="0.01" name="discount_amount" class="form-control bg-light border-0"
                       value="<?php echo htmlspecialchars((string)($editDetail['discount_amount'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
              </div>

              <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-bold px-4">
                  <?php echo $editDetail ? 'Lưu cập nhật' : 'Thêm chi tiết'; ?>
                </button>
                <?php if ($editDetail): ?>
                  <a href="order_details.php" class="btn btn-light border fw-semibold">Hủy sửa</a>
                <?php endif; ?>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Tìm theo mã HĐ, tên xe, khách hàng..."
                     value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-4">
              <select name="car_id" class="form-select bg-light border-0">
                <option value="0">Tất cả xe</option>
                <?php foreach ($cars as $c): ?>
                  <option value="<?php echo (int)$c['id']; ?>" <?php echo $carFilter === (int)$c['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary fw-semibold">Lọc</button>
              <a href="order_details.php" class="btn btn-outline-secondary fw-semibold">Đặt lại</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                  <th>Mã HĐ</th>
                  <th>Khách hàng</th>
                  <th>Xe</th>
                  <th>Số lượng</th>
                  <th>Đơn giá</th>
                  <th>Giảm giá</th>
                  <th>Thành tiền</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$details): ?>
                  <tr><td colspan="8" class="text-center text-muted py-4">Không có dòng chi tiết nào.</td></tr>
                <?php else: ?>
                  <?php foreach ($details as $d): ?>
                    <tr>
                      <td class="fw-semibold"><?php echo htmlspecialchars((string)$d['order_no'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)($d['customer_name'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)$d['car_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo (int)$d['quantity']; ?></td>
                      <td>$<?php echo number_format((float)$d['unit_price'], 2); ?></td>
                      <td>$<?php echo number_format((float)$d['discount_amount'], 2); ?></td>
                      <td class="fw-semibold text-primary">$<?php echo number_format((float)$d['total_price'], 2); ?></td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="order_details.php?edit=<?php echo (int)$d['id']; ?>">Sửa</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xóa dòng chi tiết này?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="detail_id" value="<?php echo (int)$d['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
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
</body>
</html>


<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../config/database.php';

$adminPage = 'orders';
$appName = env('APP_NAME', 'FLCar');

$pdo = getDBConnection();
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = $_POST['order_id'] ?? 0;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
        }
        header("Location: orders.php?msg=deleted");
        exit;
    }
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO orders (order_no, customer_id, car_id, order_type, quantity, unit_price, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $qty = 1;
        $unit_price = $_POST['total_amount'] ?? 0;
        $stmt->execute([
            'DH-' . strtoupper(substr(md5(uniqid()), 0, 6)),
            $_POST['customer_id'] ?? 0,
            $_POST['car_id'] ?? 0,
            $_POST['order_type'] ?? 'purchase',
            $qty,
            $unit_price,
            $unit_price * $qty,
            $_POST['status'] ?? 'pending'
        ]);
        header("Location: orders.php?msg=added");
        exit;
    }
}

try {
    $orders = $pdo->query("
        SELECT o.*, c.full_name, c.phone, car.name as car_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        JOIN cars car ON o.car_id = car.id
        ORDER BY o.id DESC
    ")->fetchAll();
    
    // For Add Form Selects
    $customers = $pdo->query("SELECT id, full_name, phone FROM customers ORDER BY id DESC")->fetchAll();
    $carsForSale = $pdo->query("SELECT id, name, price FROM cars WHERE status != 'sold'")->fetchAll();
    
} catch (Exception $e) {
    $orders = []; $customers = []; $carsForSale = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đơn Hàng - <?php echo htmlspecialchars($appName); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }
  .table > :not(caption) > * > * { padding: 16px 12px; border-bottom-color: #f1f5f9; vertical-align: middle; }
  .btn-action { background: none; border: none; padding: 6px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; transition: 0.2s; }
  .btn-action.delete { color: #ef4444; background: #fee2e2; }
  .btn-action.delete:hover { background: #fecaca; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Giao Dịch Đơn Hàng</h2>
          <p class="text-secondary mb-0 small">Theo dõi toàn bộ lịch sử mua bán, đặt cọc xe</p>
        </div>
        <button class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addOrderModal">Tạo Hợp Đồng</button>
      </div>

      <?php if($msg === 'deleted'): ?>
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">Đã hủy và xóa hóa đơn.</div>
      <?php elseif($msg === 'added'): ?>
        <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">Đã đóng giao dịch thành công.</div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <th class="ps-4">Mã HĐ</th>
                  <th>Khách Hàng</th>
                  <th>Sản Phẩm</th>
                  <th>Tổng Chi Phí</th>
                  <th>Trạng Thái</th>
                  <th class="text-end pe-4">Thao Tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($orders) === 0): ?>
                  <tr><td colspan="6" class="text-center py-5 text-muted">Hệ thống chưa ghi nhận đơn giao dịch nào.</td></tr>
                <?php else: ?>
                  <?php foreach($orders as $o): ?>
                  <tr>
                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($o['order_no']); ?></td>
                    <td>
                      <div class="fw-bold text-dark"><?php echo htmlspecialchars($o['full_name']); ?></div>
                      <div class="text-secondary small fw-medium"><?php echo htmlspecialchars($o['phone']); ?></div>
                    </td>
                    <td class="text-secondary fw-bold"><?php echo htmlspecialchars($o['car_name']); ?></td>
                    <td class="fw-bold text-dark fs-6">$<?php echo number_format($o['total_amount']); ?></td>
                    <td>
                      <?php if($o['status'] === 'completed'): ?>
                        <span class="badge bg-success text-success px-3 py-2 rounded-pill bg-opacity-25 border border-success border-opacity-25">Đã Giao Xe</span>
                      <?php elseif($o['status'] === 'confirmed'): ?>
                        <span class="badge bg-info text-info px-3 py-2 rounded-pill bg-opacity-10 border border-info border-opacity-25">Chốt Cọc</span>
                      <?php elseif($o['status'] === 'cancelled'): ?>
                        <span class="badge bg-danger text-danger px-3 py-2 rounded-pill bg-opacity-10">Bị Hủy</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill bg-opacity-25">Chờ Duyệt</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                      <form method="POST" class="d-inline" onsubmit="return confirm('Thu hồi và xóa hợp đồng này khỏi CSDL?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                        <button type="submit" class="btn-action delete">Xóa HĐ</button>
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

<!-- Modal Thêm Đơn Hàng -->
<div class="modal fade" id="addOrderModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 16px;">
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-dark">Thêm Giao Dịch Mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
          <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Chọn Khách Hàng</label>
            <select name="customer_id" class="form-select bg-light border-0" required>
              <option value="" disabled selected>-- Chỉ lấy KH đã có trên hệ thống --</option>
              <?php foreach($customers as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['full_name'] . ' - ' . $c['phone']); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text small">Tìm trong "Khách Hàng" nếu chưa có.</div>
          </div>
          <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Chọn Siêu Xe</label>
            <select name="car_id" class="form-select bg-light border-0" onchange="document.getElementById('priceInput').value = this.options[this.selectedIndex].getAttribute('data-price');" required>
              <option value="" disabled selected>-- Chọn xe chưa bán --</option>
              <?php foreach($carsForSale as $c): ?>
                <option value="<?php echo $c['id']; ?>" data-price="<?php echo $c['price']; ?>"><?php echo htmlspecialchars($c['name']); ?> (Mã: <?php echo $c['id']; ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Tổng chi phí ($)</label>
              <input type="number" id="priceInput" name="total_amount" class="form-control bg-light border-0" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Loại HD</label>
              <select name="order_type" class="form-select bg-light border-0">
                <option value="purchase">Mua đứt</option>
                <option value="deposit">Đặt cọc</option>
                <option value="installment">Trả góp</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Trạng thái HĐ</label>
            <select name="status" class="form-select bg-light border-0">
              <option value="pending">Chờ Xử Lý</option>
              <option value="confirmed">Chốt Cọc (Xác nhận)</option>
              <option value="completed">Đã Giao Phiếu / Thành Công</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary fw-bold px-4">Lưu Hợp Đồng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('adminWrapper');
    if(toggle) toggle.addEventListener('click', () => wrapper.classList.toggle('sidebar-collapsed'));
  });
</script>
</body>
</html>

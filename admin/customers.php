<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'customers';
$appName = env('APP_NAME', 'FLCar');

$pdo = getDBConnection();
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = $_POST['customer_id'] ?? 0;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$id]);
        }
        header("Location: customers.php?msg=deleted");
        exit;
    }
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO customers (full_name, email, phone, tier) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['full_name'] ?? '',
            $_POST['email'] ?? '',
            $_POST['phone'] ?? '',
            $_POST['tier'] ?? 'new'
        ]);
        header("Location: customers.php?msg=added");
        exit;
    }
}

try {
    $customers = $pdo->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $customers = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hồ Sơ Khách Hàng - <?php echo htmlspecialchars($appName); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<!-- Gắn lại chính xác font, bootstrap, và CSS gốc -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }
  .table > :not(caption) > * > * { padding: 16px 12px; border-bottom-color: #f1f5f9; vertical-align: middle; }
  .btn-action { background: none; border: none; padding: 6px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; transition: 0.2s; }
  .btn-action.delete { color: #ef4444; background: #fee2e2; }
  .btn-action.delete:hover { background: #fecaca; }
  .avatar-placeholder { width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Quản Lý Người Dùng & Khách Hàng</h2>
          <p class="text-secondary mb-0 small">Hồ sơ khách hàng đã giao dịch và cần tư vấn</p>
        </div>
        <button class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Ghi Nhận Khách Mới</button>
      </div>

      <?php if($msg === 'deleted'): ?>
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">Đã xóa hồ sơ khách hàng.</div>
      <?php elseif($msg === 'added'): ?>
        <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">Thêm hồ sơ khách hàng thành công.</div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <th class="ps-4">Khách Hàng</th>
                  <th>Số Điện Thoại</th>
                  <th>Email Liên Hệ</th>
                  <th>Hạng Thẻ</th>
                  <th>Ngày Đăng Ký</th>
                  <th class="text-end pe-4">Thao Tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($customers) === 0): ?>
                  <tr><td colspan="6" class="text-center py-5 text-muted">Chưa có dữ liệu khách hàng.</td></tr>
                <?php else: ?>
                  <?php foreach($customers as $c): ?>
                  <tr>
                    <td class="ps-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-placeholder"><?php echo mb_strtoupper(mb_substr($c['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?></div>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($c['full_name']); ?></span>
                      </div>
                    </td>
                    <td class="text-secondary fw-medium"><?php echo htmlspecialchars($c['phone'] ?: '---'); ?></td>
                    <td class="text-secondary fw-medium"><?php echo htmlspecialchars($c['email'] ?: '---'); ?></td>
                    <td>
                      <?php if($c['tier'] === 'vip'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill bg-opacity-25 border border-warning">Khách VIP</span>
                      <?php elseif($c['tier'] === 'regular'): ?>
                        <span class="badge bg-success text-success px-3 py-2 rounded-pill bg-opacity-25">Thân thiết</span>
                      <?php else: ?>
                        <span class="badge bg-info text-info px-3 py-2 rounded-pill bg-opacity-10">Khách Mới</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-secondary small fw-medium"><?php echo date('d/m/Y', strtotime($c['created_at'])); ?></td>
                    <td class="text-end pe-4">
                      <form method="POST" class="d-inline" onsubmit="return confirm('Xóa bỏ hồ sơ của khách hàng này?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="customer_id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="btn-action delete">Xóa Hồ Sơ</button>
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

<!-- Modal Thêm Khách Hàng -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 16px;">
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-dark">Lưu Khách Hàng Mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
          <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Họ và tên</label>
            <input type="text" name="full_name" class="form-control bg-light border-0" required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Số điện thoại</label>
              <input type="text" name="phone" class="form-control bg-light border-0">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Xếp hạng</label>
              <select name="tier" class="form-select bg-light border-0">
                <option value="new">Khách Mới</option>
                <option value="regular">Thành viên Thân thiết</option>
                <option value="vip">Đối tác / VIP</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Email (không bắt buộc)</label>
            <input type="email" name="email" class="form-control bg-light border-0">
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary fw-bold px-4">Ghi Nhận</button>
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




<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'cars';
$appName = env('APP_NAME', 'FLCar');

$pdo = getDBConnection();
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = $_POST['car_id'] ?? 0;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$id]);
        }
        header("Location: cars.php?msg=deleted");
        exit;
    }
    
    if ($action === 'add') {
        $brand_id = $_POST['brand_id'] ?? 1;
        $category_id = $_POST['category_id'] ?? 1;
        
        $checkBrand = $pdo->prepare("SELECT id FROM brands WHERE id = ?");
        $checkBrand->execute([$brand_id]);
        if (!$checkBrand->fetch()) {
            $pdo->query("INSERT INTO brands (id, name, slug) VALUES (1, 'Khác', 'khac')");
            $brand_id = 1;
        }

        $checkCat = $pdo->prepare("SELECT id FROM car_categories WHERE id = ?");
        $checkCat->execute([$category_id]);
        if (!$checkCat->fetch()) {
            $pdo->query("INSERT INTO car_categories (id, name, slug) VALUES (1, 'Khác', 'khac')");
            $category_id = 1;
        }

        $stmt = $pdo->prepare("INSERT INTO cars (code, name, slug, brand_id, category_id, model_year, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'XE-' . strtoupper(substr(md5(uniqid()), 0, 5)), 
            $_POST['name'] ?? 'Tên xe mới',
            'xe-moi-' . time(),
            $brand_id,
            $category_id,
            $_POST['year'] ?? date('Y'),
            $_POST['price'] ?? 0,
            $_POST['status'] ?? 'available'
        ]);
        header("Location: cars.php?msg=added");
        exit;
    }

    if ($action === 'edit') {
        $id = $_POST['car_id'] ?? 0;
        if ($id) {
            $stmt = $pdo->prepare("UPDATE cars SET name = ?, model_year = ?, price = ?, brand_id = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'] ?? '',
                $_POST['year'] ?? date('Y'),
                $_POST['price'] ?? 0,
                $_POST['brand_id'] ?? 1,
                $_POST['status'] ?? 'available',
                $id
            ]);
            header("Location: cars.php?msg=edited");
            exit;
        }
    }
}

try {
    $cars = $pdo->query("
        SELECT c.*, b.name as brand_name, cat.name as category_name,
               (SELECT image_url FROM car_images ci WHERE ci.car_id = c.id AND ci.is_cover = 1 ORDER BY ci.id ASC LIMIT 1) as cover_image
        FROM cars c 
        LEFT JOIN brands b ON c.brand_id = b.id 
        LEFT JOIN car_categories cat ON c.category_id = cat.id 
        ORDER BY c.id DESC
    ")->fetchAll();
    $brands = $pdo->query("SELECT * FROM brands")->fetchAll();
    $categories = $pdo->query("SELECT * FROM car_categories")->fetchAll();
} catch (Exception $e) {
    $cars = []; $brands = []; $categories = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kho Xe - <?php echo htmlspecialchars($appName); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<!-- Gắn lại chính xác font, bootstrap, và CSS gốc -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }
  .table > :not(caption) > * > * { padding: 16px 12px; border-bottom-color: #f1f5f9; vertical-align: middle; }
  .btn-action { background: none; border: none; padding: 6px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; transition: 0.2s; margin-left: 4px; }
  .btn-action.edit { color: #0284c7; background: #e0f2fe; }
  .btn-action.edit:hover { background: #bae6fd; }
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
        <h2 class="h4 fw-bold text-dark mb-0">Quản Lý Kho Siêu Xe</h2>
        <button class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCarModal">Thêm Xe Mới</button>
      </div>

      <?php if($msg === 'deleted'): ?>
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">Đã xóa xe khỏi kho dữ liệu thành công!</div>
      <?php elseif($msg === 'added'): ?>
        <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">Đã nhập siêu xe mới thành công!</div>
      <?php elseif($msg === 'edited'): ?>
        <div class="alert alert-info border-0 shadow-sm" style="border-radius: 12px;">Đã cập nhật thông tin siêu xe thành công!</div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <th class="ps-4">Mã Xe</th>
                  <th>Hình Ảnh & Tên Xe</th>
                  <th>Thương Hiệu</th>
                  <th>Giá Bán</th>
                  <th>Trạng Thái</th>
                  <th class="text-end pe-4">Thao Tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($cars) === 0): ?>
                  <tr><td colspan="6" class="text-center py-5 text-muted">Chưa có siêu xe nào trong kho.</td></tr>
                <?php else: ?>
                  <?php foreach($cars as $c): 
                    $imgSrc = !empty($c['cover_image']) ? htmlspecialchars($c['cover_image']) : '../img/bmwx5.jpg'; 
                  ?>
                  <tr>
                    <td class="ps-4 fw-bold text-secondary"><?php echo htmlspecialchars($c['code']); ?></td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?php echo $imgSrc; ?>" alt="car" style="width: 56px; height: 42px; object-fit: cover; border-radius: 8px;">
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($c['name']); ?></span>
                      </div>
                    </td>
                    <td class="text-secondary fw-medium"><?php echo htmlspecialchars($c['brand_name'] ?? 'N/A'); ?></td>
                    <td class="fw-bold text-dark fs-6">$<?php echo number_format($c['price']); ?></td>
                    <td>
                      <?php if($c['status'] === 'sold'): ?>
                        <span class="badge bg-light text-secondary px-3 py-2 rounded-pill">Đã Bán</span>
                      <?php elseif($c['status'] === 'reserved'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill bg-opacity-25">Đặt Cọc</span>
                      <?php else: ?>
                        <span class="badge bg-success text-success px-3 py-2 rounded-pill bg-opacity-25">Sẵn Hàng</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                      <!-- Nút Sửa -->
                      <a href="car-edit.php?id=<?php echo $c['id']; ?>" class="btn-action edit">Sửa Cấu Hình</a>

                      <!-- Nút Xóa -->
                      <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa xe này vĩnh viễn?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="car_id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="btn-action delete">Xóa</button>
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

<!-- Modal Thêm Xe Nhanh -->
<div class="modal fade" id="addCarModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 16px;">
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-dark">Nhập Kho Siêu Xe Mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
          <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Tên Model Xe</label>
            <input type="text" name="name" class="form-control bg-light border-0" required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Năm sản xuất</label>
              <input type="number" name="year" class="form-control bg-light border-0" value="<?php echo date('Y'); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Giá niêm yết ($)</label>
              <input type="number" name="price" class="form-control bg-light border-0" value="0" required>
            </div>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Thương hiệu</label>
              <select name="brand_id" class="form-select bg-light border-0">
                <?php foreach($brands as $b): ?>
                  <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Trạng thái xe</label>
              <select name="status" class="form-select bg-light border-0">
                <option value="available">Sẵn Hàng</option>
                <option value="reserved">Khách Đặt Cọc</option>
                <option value="sold">Đã Bán</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary fw-bold px-4">Lưu Thông Tin</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Sửa Xe -->
<div class="modal fade" id="editCarModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 16px;">
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="car_id" id="edit_car_id" value="">
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-dark">Cập Nhật Thông Tin Xe</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
          <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Tên Model Xe</label>
            <input type="text" name="name" id="edit_name" class="form-control bg-light border-0" required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Năm sản xuất</label>
              <input type="number" name="year" id="edit_year" class="form-control bg-light border-0" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Giá niêm yết ($)</label>
              <input type="number" name="price" id="edit_price" class="form-control bg-light border-0" required>
            </div>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Thương hiệu</label>
              <select name="brand_id" id="edit_brand_id" class="form-select bg-light border-0">
                <?php foreach($brands as $b): ?>
                  <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-secondary small fw-bold">Trạng thái xe</label>
              <select name="status" id="edit_status" class="form-select bg-light border-0">
                <option value="available">Sẵn Hàng</option>
                <option value="reserved">Khách Đặt Cọc</option>
                <option value="sold">Đã Bán</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary fw-bold px-4">Lưu Chỉnh Sửa</button>
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

  // Hiển thị và gán dữ liệu vào Modal Sửa
  function openEditModal(car) {
    document.getElementById('edit_car_id').value = car.id;
    document.getElementById('edit_name').value = car.name;
    document.getElementById('edit_year').value = car.year;
    document.getElementById('edit_price').value = car.price;
    document.getElementById('edit_brand_id').value = car.brand_id;
    document.getElementById('edit_status').value = car.status;
    
    var editModal = new bootstrap.Modal(document.getElementById('editCarModal'));
    editModal.show();
  }
</script>
</body>
</html>




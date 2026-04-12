<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'cars';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();
$msg = $_GET['msg'] ?? '';

// Some environments were missing this table while UI already depends on it.
$pdo->exec("
    CREATE TABLE IF NOT EXISTS car_specs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        car_id BIGINT UNSIGNED NOT NULL,
        spec_key VARCHAR(120) NOT NULL,
        spec_value VARCHAR(255) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_car_specs_car_key (car_id, spec_key),
        KEY idx_car_specs_car (car_id),
        CONSTRAINT fk_car_specs_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
");

$carId = (int) ($_GET['id'] ?? 0);
if ($carId <= 0) {
    header('Location: cars.php');
    exit;
}

// Handle POST updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_info') {
        $stmt = $pdo->prepare("UPDATE cars SET name=?, model_year=?, price=?, status=?, is_featured=?, description=? WHERE id=?");
        $stmt->execute([
            $_POST['name'] ?? '',
            $_POST['year'] ?? date('Y'),
            $_POST['price'] ?? 0,
            $_POST['status'] ?? 'available',
            isset($_POST['is_featured']) ? 1 : 0,
            $_POST['description'] ?? '',
            $carId
        ]);
        header("Location: car-edit.php?id={$carId}&msg=info_saved");
        exit;
    }

    if ($action === 'upsert_spec') {
        $key = trim($_POST['spec_key'] ?? '');
        $val = trim($_POST['spec_value'] ?? '');
        if ($key !== '') {
            $check = $pdo->prepare("SELECT id FROM car_specs WHERE car_id=? AND spec_key=?");
            $check->execute([$carId, $key]);
            if ($check->fetch()) {
                $pdo->prepare("UPDATE car_specs SET spec_value=? WHERE car_id=? AND spec_key=?")->execute([$val, $carId, $key]);
            } else {
                $pdo->prepare("INSERT INTO car_specs (car_id, spec_key, spec_value) VALUES (?,?,?)")->execute([$carId, $key, $val]);
            }
        }
        header("Location: car-edit.php?id={$carId}&msg=spec_saved");
        exit;
    }

    if ($action === 'delete_spec') {
        $specId = (int) ($_POST['spec_id'] ?? 0);
        if ($specId) {
            $pdo->prepare("DELETE FROM car_specs WHERE id=? AND car_id=?")->execute([$specId, $carId]);
        }
        header("Location: car-edit.php?id={$carId}&msg=spec_deleted");
        exit;
    }

    if ($action === 'add_image') {
        $url = trim($_POST['image_url'] ?? '');
        $alt = trim($_POST['alt_text'] ?? '');
        $isCover = isset($_POST['is_cover']) ? 1 : 0;
        if ($url !== '') {
            if ($isCover) {
                $pdo->prepare("UPDATE car_images SET is_cover=0 WHERE car_id=?")->execute([$carId]);
            }
            $pdo->prepare("INSERT INTO car_images (car_id, image_url, alt_text, is_cover) VALUES (?,?,?,?)")->execute([$carId, $url, $alt, $isCover]);
        }
        header("Location: car-edit.php?id={$carId}&msg=img_added");
        exit;
    }

    if ($action === 'delete_image') {
        $imgId = (int) ($_POST['img_id'] ?? 0);
        if ($imgId) {
            $pdo->prepare("DELETE FROM car_images WHERE id=? AND car_id=?")->execute([$imgId, $carId]);
        }
        header("Location: car-edit.php?id={$carId}&msg=img_deleted");
        exit;
    }

    if ($action === 'set_cover') {
        $imgId = (int) ($_POST['img_id'] ?? 0);
        if ($imgId) {
            $pdo->prepare("UPDATE car_images SET is_cover=0 WHERE car_id=?")->execute([$carId]);
            $pdo->prepare("UPDATE car_images SET is_cover=1 WHERE id=? AND car_id=?")->execute([$imgId, $carId]);
        }
        header("Location: car-edit.php?id={$carId}&msg=cover_set");
        exit;
    }
}

// Load data
try {
    $stmt = $pdo->prepare("SELECT c.*, b.name AS brand_name FROM cars c LEFT JOIN brands b ON c.brand_id = b.id WHERE c.id=?");
    $stmt->execute([$carId]);
    $car = $stmt->fetch();
    if (!$car) { header('Location: cars.php'); exit; }

    $specs = $pdo->prepare("SELECT * FROM car_specs WHERE car_id=? ORDER BY id ASC");
    $specs->execute([$carId]);
    $specs = $specs->fetchAll();

    $images = $pdo->prepare("SELECT * FROM car_images WHERE car_id=? ORDER BY is_cover DESC, sort_order ASC, id ASC");
    $images->execute([$carId]);
    $images = $images->fetchAll();
} catch (Exception $e) {
    $car = null; $specs = []; $images = [];
    if (!$car) { header('Location: cars.php'); exit; }
}

$msgs = [
    'info_saved'   => ['success', 'Đã lưu thông tin xe thành công!'],
    'spec_saved'   => ['success', 'Đã cập nhật thông số kỹ thuật!'],
    'spec_deleted' => ['warning', 'Đã xóa thông số kỹ thuật.'],
    'img_added'    => ['success', 'Đã thêm hình ảnh mới!'],
    'img_deleted'  => ['warning', 'Đã xóa hình ảnh.'],
    'cover_set'    => ['info',    'Đã đặt lại ảnh bìa.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chỉnh sửa xe - <?php echo htmlspecialchars($appName); ?></title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background: #f8fafc; }
  .section-card { background:#fff; border-radius:16px; padding:28px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:24px; }
  .section-card h5 { font-weight:700; color:#0f172a; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; }
  .img-thumb { width:80px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #e2e8f0; }
  .img-thumb.is-cover { border-color:#2563eb; }
  .spec-row { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f8fafc; }
  .spec-key { font-weight:600; color:#0f172a; min-width:160px; }
  .spec-val { color:#475569; flex:1; }
  .btn-back { color:#64748b; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-weight:600; margin-bottom:20px; }
  .btn-back:hover { color:#0f172a; }
</style>
</head>
<body>
<div class="admin-wrapper" id="adminWrapper">
  <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <div class="admin-main">
    <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>
    <main class="admin-content p-4">

      <a href="cars.php" class="btn-back"><i class="bi bi-arrow-left"></i> Quay lại Kho Xe</a>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h4 fw-bold text-dark mb-1">Chỉnh Sửa: <?php echo htmlspecialchars($car['name']); ?></h2>
          <p class="text-secondary small mb-0">Mã xe: <strong><?php echo htmlspecialchars($car['code']); ?></strong> · Hãng: <?php echo htmlspecialchars($car['brand_name'] ?? 'N/A'); ?></p>
        </div>
        <a href="../pages/car-detail.php?id=<?php echo $carId; ?>" target="_blank" class="btn btn-light fw-semibold border">
          <i class="bi bi-eye me-2"></i>Xem ngoài web
        </a>
      </div>

      <?php if ($msg && isset($msgs[$msg])): $m = $msgs[$msg]; ?>
        <div class="alert alert-<?php echo $m[0]; ?> border-0 rounded-3 mb-4"><?php echo $m[1]; ?></div>
      <?php endif; ?>

      <!-- THÔNG TIN CƠ BẢN -->
      <div class="section-card">
        <h5><i class="bi bi-pencil-square text-primary me-2"></i>Thông Tin Cơ Bản</h5>
        <form method="POST">
          <input type="hidden" name="action" value="update_info">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label small fw-bold text-secondary">Tên Model Xe</label>
              <input type="text" name="name" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($car['name']); ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-secondary">Năm Sản Xuất</label>
              <input type="number" name="year" class="form-control bg-light border-0" value="<?php echo $car['model_year']; ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-secondary">Giá Niêm Yết ($)</label>
              <input type="number" name="price" class="form-control bg-light border-0" value="<?php echo $car['price']; ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-secondary">Trạng Thái</label>
              <select name="status" class="form-select bg-light border-0">
                <option value="available" <?php echo $car['status']==='available' ? 'selected':''; ?>>Sẵn Hàng</option>
                <option value="reserved"  <?php echo $car['status']==='reserved'  ? 'selected':''; ?>>Đang Đặt Cọc</option>
                <option value="sold"      <?php echo $car['status']==='sold'      ? 'selected':''; ?>>Đã Bán</option>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-check form-switch ms-2 mb-2">
                <input class="form-check-input" type="checkbox" name="is_featured" id="featuredToggle" <?php echo $car['is_featured'] ? 'checked' : ''; ?>>
                <label class="form-check-label fw-semibold" for="featuredToggle">Xe Nổi Bật (Trang Chủ)</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold text-secondary">Mô Tả Xe</label>
              <textarea name="description" class="form-control bg-light border-0" rows="4"><?php echo htmlspecialchars($car['description'] ?? ''); ?></textarea>
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary fw-bold px-5 rounded-3">Lưu Thông Tin</button>
          </div>
        </form>
      </div>

      <!-- THÔNG SỐ KỸ THUẬT -->
      <div class="section-card">
        <h5><i class="bi bi-gear text-primary me-2"></i>Thông Số Kỹ Thuật</h5>

        <?php if (!empty($specs)): ?>
        <div class="mb-4">
          <?php foreach ($specs as $sp): ?>
          <div class="spec-row">
            <span class="spec-key"><?php echo htmlspecialchars(ucfirst(str_replace('_',' ',$sp['spec_key']))); ?></span>
            <span class="spec-val"><?php echo htmlspecialchars($sp['spec_value']); ?></span>
            <form method="POST" class="ms-auto" onsubmit="return confirm('Xóa thông số này?');">
              <input type="hidden" name="action" value="delete_spec">
              <input type="hidden" name="spec_id" value="<?php echo $sp['id']; ?>">
              <button type="submit" class="btn btn-sm btn-light text-danger border-0"><i class="bi bi-trash3"></i></button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <p class="text-muted mb-4 small">Chưa có thông số nào. Thêm mới bên dưới.</p>
        <?php endif; ?>

        <form method="POST" class="row g-2 align-items-end">
          <input type="hidden" name="action" value="upsert_spec">
          <div class="col-md-4">
            <label class="form-label small fw-bold text-secondary">Tên thông số</label>
            <select name="spec_key" class="form-select bg-light border-0">
              <option value="power">Mã lực (power)</option>
              <option value="0_100">0 → 100 km/h</option>
              <option value="top_speed">Vận tốc tối đa</option>
              <option value="engine">Động cơ (engine)</option>
              <option value="drivetrain">Hệ dẫn động</option>
              <option value="transmission">Hộp số</option>
              <option value="seats">Số chỗ ngồi</option>
              <option value="fuel">Nhiên liệu</option>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label small fw-bold text-secondary">Giá trị</label>
            <input type="text" name="spec_value" class="form-control bg-light border-0" placeholder="Ví dụ: 375 HP" required>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-success fw-bold w-100 rounded-3">+ Thêm / Cập nhật</button>
          </div>
        </form>
      </div>

      <!-- QUẢN LÝ ẢNH -->
      <div class="section-card">
        <h5><i class="bi bi-images text-primary me-2"></i>Thư Viện Hình Ảnh</h5>

        <?php if (!empty($images)): ?>
        <div class="row g-3 mb-4">
          <?php foreach ($images as $img): ?>
          <div class="col-auto">
            <div class="position-relative d-inline-block">
              <img src="<?php echo htmlspecialchars($img['image_url']); ?>" class="img-thumb <?php echo $img['is_cover'] ? 'is-cover' : ''; ?>" alt="">
              <?php if ($img['is_cover']): ?>
                <span class="badge bg-primary position-absolute top-0 start-0" style="font-size:0.6rem;">Bìa</span>
              <?php endif; ?>
              <div class="d-flex gap-1 mt-1">
                <?php if (!$img['is_cover']): ?>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="set_cover">
                  <input type="hidden" name="img_id" value="<?php echo $img['id']; ?>">
                  <button type="submit" class="btn btn-xs btn-outline-primary border-0 p-1" title="Đặt làm ảnh bìa" style="font-size:0.7rem;"><i class="bi bi-star"></i></button>
                </form>
                <?php endif; ?>
                <form method="POST" class="d-inline" onsubmit="return confirm('Xóa ảnh này?');">
                  <input type="hidden" name="action" value="delete_image">
                  <input type="hidden" name="img_id" value="<?php echo $img['id']; ?>">
                  <button type="submit" class="btn btn-xs btn-outline-danger border-0 p-1" style="font-size:0.7rem;"><i class="bi bi-trash3"></i></button>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <p class="text-muted small mb-4">Chưa có ảnh nào. Thêm mới bên dưới.</p>
        <?php endif; ?>

        <form method="POST" class="row g-2 align-items-end">
          <input type="hidden" name="action" value="add_image">
          <div class="col-md-5">
            <label class="form-label small fw-bold text-secondary">URL Hình Ảnh</label>
            <input type="text" name="image_url" class="form-control bg-light border-0" placeholder="https:// hoặc /img/..." required>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-bold text-secondary">Alt text (mô tả)</label>
            <input type="text" name="alt_text" class="form-control bg-light border-0" placeholder="Tùy chọn">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <div class="form-check mb-2 ms-1">
              <input class="form-check-input" type="checkbox" name="is_cover" id="isCoverCheck">
              <label class="form-check-label small fw-semibold" for="isCoverCheck">Ảnh bìa</label>
            </div>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-success fw-bold w-100 rounded-3">+ Thêm Ảnh</button>
          </div>
        </form>
      </div>

    </main>
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




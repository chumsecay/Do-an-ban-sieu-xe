<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../bootstrap/shop.php';
require_once __DIR__ . '/../config/database.php';
ensureSessionStarted();

$currentPage = 'showroom';
$appName = env('APP_NAME', 'FLCar');

$pdo = getDBConnection();
shopEnsureCarsStockColumn($pdo);
$canShop = isUserLoggedIn() && currentUserRole() === 'user';

// Validate car ID
$carId = (int)($_GET['id'] ?? 0);
if ($carId <= 0) {
    header('Location: showroom.php');
    exit;
}

// Fetch xe chính
try {
    $stmt = $pdo->prepare("
        SELECT c.*, b.name AS brand_name, cat.name AS category_name
        FROM cars c
        LEFT JOIN brands b ON c.brand_id = b.id
        LEFT JOIN car_categories cat ON c.category_id = cat.id
        WHERE c.id = ?
        LIMIT 1
    ");
    $stmt->execute([$carId]);
    $car = $stmt->fetch();
} catch (Exception $e) {
    $car = null;
}

if (!$car) {
    header('Location: showroom.php');
    exit;
}

// Fetch hình ảnh gallery
try {
    $imgStmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY is_cover DESC, sort_order ASC");
    $imgStmt->execute([$carId]);
    $images = $imgStmt->fetchAll();
} catch (Exception $e) {
    $images = [];
}

// Xác định ảnh bìa
$heroImg = '../img/bmwx5.jpg';
foreach ($images as $img) {
    if ($img['is_cover']) {
        $heroImg = htmlspecialchars($img['image_url']);
        break;
    }
}
if ($heroImg === '../img/bmwx5.jpg' && !empty($images)) {
    $heroImg = htmlspecialchars($images[0]['image_url']);
}

// Fetch thông số kỹ thuật (car_specs)
try {
    $specStmt = $pdo->prepare("SELECT * FROM car_specs WHERE car_id = ? ORDER BY id ASC");
    $specStmt->execute([$carId]);
    $rawSpecs = $specStmt->fetchAll();
    $specs = [];
    foreach ($rawSpecs as $s) {
        $specs[$s['spec_key']] = $s['spec_value'];
    }
} catch (Exception $e) {
    $specs = [];
}

$stockQty = (int)($car['stock_quantity'] ?? 0);
$isPurchasable = ((string)($car['status'] ?? '') === 'available') && $stockQty > 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($car['name']); ?> - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
<style>
.car-hero {
  height: 90vh; position: relative;
  background-image: url('<?php echo $heroImg; ?>');
  background-size: cover; background-position: center;
  background-attachment: fixed;
  display: flex; align-items: flex-end; padding-bottom: 80px;
}
.car-hero::before {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.4) 50%, rgba(15,23,42,0.1) 100%);
}
.hero-content { position: relative; z-index: 2; width: 100%; }
.car-title { font-size: 4.5rem; font-weight: 900; letter-spacing: -1px; line-height: 1.1; color: #fff; text-shadow: 0 10px 30px rgba(0,0,0,0.5); }
.car-price-badge { display: inline-block; background: var(--gradient-primary); color: #fff; padding: 10px 24px; border-radius: 30px; font-size: 1.5rem; font-weight: 800; box-shadow: 0 10px 25px rgba(220,38,38,0.4); }
.spec-box { background: #fff; border: 1px solid rgba(0,0,0,0.05); border-radius: 16px; padding: 24px; text-align: center; transition: transform 0.3s, box-shadow 0.3s; height: 100%; }
.spec-box:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
.spec-icon { font-size: 2rem; color: var(--primary); margin-bottom: 12px; }
.spec-value { font-size: 1.25rem; font-weight: 800; color: var(--dark); margin-bottom: 4px; }
.spec-label { font-size: 0.85rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; }
.gallery-img { width: 100%; height: 350px; object-fit: cover; border-radius: 16px; transition: transform 0.5s; }
.gallery-wrap { overflow: hidden; border-radius: 16px; }
.gallery-wrap:hover .gallery-img { transform: scale(1.05); }
.sticky-sidebar { position: sticky; top: 100px; }
.contact-card { background: var(--dark); color: #fff; border-radius: 20px; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
@media (max-width: 768px) { .car-title { font-size: 2.8rem; } .car-hero { height: 70vh; } }
</style>
</head>
<body class="has-hero">
<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- HERO SECTION -->
<section class="car-hero">
  <div class="container hero-content reveal-item">
    <div class="row align-items-end">
      <div class="col-lg-8">
        <p class="mb-2" style="color:rgba(255,255,255,.6); font-size:0.9rem; font-weight:600; letter-spacing:2px; text-transform:uppercase;">
          <?php echo htmlspecialchars($car['brand_name'] ?? ''); ?> · <?php echo $car['model_year']; ?>
        </p>
        <h1 class="car-title mb-4"><?php echo htmlspecialchars($car['name']); ?></h1>
        <div class="car-price-badge">$<?php echo number_format($car['price']); ?></div>
      </div>
      <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
        <a href="#booking-section" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold" style="border-width: 2px;">Nhận tư vấn ngày</a>
      </div>
    </div>
  </div>
</section>

<div class="container py-5 mt-4">
  <div class="row g-5">

    <!-- MAIN CONTENT (LEFT) -->
    <div class="col-lg-8">

      <!-- Giới thiệu -->
      <div class="mb-5 reveal-item">
        <h3 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-info-circle text-primary me-2"></i> Tổng quan xe</h3>
        <p class="lead text-muted" style="line-height: 1.8; font-size: 1.1rem;">
          <?php echo nl2br(htmlspecialchars($car['description'] ?? 'Đây là một trong những chiếc xe cao cấp nhất tại FLCar Showroom, được nhập khẩu trực tiếp và bảo hành chính hãng.')); ?>
        </p>
        <?php if (!empty($car['brand_name'])): ?>
        <div class="d-flex gap-3 mt-4 flex-wrap">
          <span class="badge bg-light text-dark px-3 py-2 rounded-pill border fw-semibold"><?php echo htmlspecialchars($car['brand_name']); ?></span>
          <span class="badge bg-light text-dark px-3 py-2 rounded-pill border fw-semibold"><?php echo htmlspecialchars($car['category_name'] ?? ''); ?></span>
          <span class="badge bg-light text-dark px-3 py-2 rounded-pill border fw-semibold">Model <?php echo $car['model_year']; ?></span>
          <?php if ($car['status'] === 'available'): ?>
            <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill fw-semibold">Còn xe ngày</span>
          <?php elseif ($car['status'] === 'reserved'): ?>
            <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 rounded-pill fw-semibold">Đang đặt cọc</span>
          <?php else: ?>
            <span class="badge bg-secondary bg-opacity-25 text-secondary px-3 py-2 rounded-pill fw-semibold">Đã bán</span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Thông số kỹ thuật -->
      <div class="mb-5 reveal-item">
        <h3 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-gear-wide-connected text-primary me-2"></i> Thông số kỹ thuật</h3>
        <?php if (!empty($specs)): ?>
        <div class="row g-3">
          <?php
          $specIcons = [
            'power' => ['bi-lightning-charge', 'Mã lực (HP)'],
            '0_100' => ['bi-stopwatch', '0 → 100 km/h'],
            'top_speed' => ['bi-speedometer2', 'Vận tốc tối đa'],
            'engine' => ['bi-ev-front', 'Động cơ'],
            'drivetrain' => ['bi-bezier2', 'Hệ dẫn động'],
            'transmission' => ['bi-sliders', 'Hộp số'],
          ];
          foreach ($specs as $key => $value):
            $icon = $specIcons[$key][0] ?? 'bi-car-front';
            $label = $specIcons[$key][1] ?? ucfirst(str_replace('_', ' ', $key));
          ?>
          <div class="col-md-4 col-6">
            <div class="spec-box">
              <i class="bi <?php echo $icon; ?> spec-icon"></i>
              <div class="spec-value"><?php echo htmlspecialchars($value); ?></div>
              <div class="spec-label"><?php echo $label; ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <div class="alert alert-light border" style="border-radius:12px;">
            <i class="bi bi-info-circle me-2 text-primary"></i>
            Thông số kỹ thuật chi tiết đang được cập nhật. Vui lòng liên hệ để được tư vấn.
          </div>
        <?php endif; ?>
      </div>

      <!-- Thư viện ảnh -->
      <?php if (!empty($images)): ?>
      <div class="mb-5 reveal-item">
        <h3 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-images text-primary me-2"></i> Thư viện hình ảnh</h3>
        <div class="row g-3">
          <?php foreach ($images as $idx => $img): ?>
          <div class="<?php echo $idx === 0 ? 'col-12' : 'col-md-6'; ?>">
            <div class="gallery-wrap">
              <img src="<?php echo htmlspecialchars($img['image_url']); ?>" class="gallery-img"
                   alt="<?php echo htmlspecialchars($img['alt_text'] ?? $car['name']); ?>">
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- SIDEBAR (RIGHT) -->
    <div class="col-lg-4" id="booking-section">
      <div class="sticky-sidebar reveal-right">
        <div class="contact-card">
          <h4 class="fw-bold mb-1">Quan tâm xe này?</h4>
          <p class="text-white-50 mb-4" style="font-size: 0.9rem;">Để lại thông tin, chuyên viên FLCar sẽ liên hệ ngay.</p>

          <div class="mb-4">
            <div class="small text-white-50 mb-2">Tồn kho hiện tại: <strong class="text-white"><?php echo $stockQty; ?></strong></div>
            <?php if (!$canShop): ?>
              <a href="../login.php" class="btn btn-outline-light w-100 mb-2">Đăng nhập để mua xe</a>
            <?php elseif ($isPurchasable): ?>
              <div class="d-grid gap-2">
                <a href="cart.php?action=add&car_id=<?php echo (int)$car['id']; ?>&from=detail" class="btn btn-outline-light">Thêm vào giỏ hàng</a>
                <a href="cart.php?action=quick_buy&car_id=<?php echo (int)$car['id']; ?>" class="btn btn-primary fw-bold">Mua ngày</a>
              </div>
            <?php else: ?>
              <button type="button" class="btn btn-secondary w-100" disabled>Tạm hết hàng</button>
            <?php endif; ?>
          </div>

          <form action="../pages/contact.php" method="GET">
            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
            <input type="hidden" name="car_name" value="<?php echo htmlspecialchars($car['name']); ?>">
            <div class="mb-3">
              <label class="form-label text-white-50 small">Họ và tên</label>
              <input type="text" name="full_name" class="form-control bg-dark text-white border-secondary" placeholder="VD: Nguyễn Văn A">
            </div>
            <div class="mb-3">
              <label class="form-label text-white-50 small">Số điện thoại</label>
              <input type="tel" name="phone" class="form-control bg-dark text-white border-secondary" placeholder="0901 234 567">
            </div>
            <div class="mb-4">
              <label class="form-label text-white-50 small">Nhu cầu</label>
              <select name="need" class="form-select bg-dark text-white border-secondary">
                <option>Tư vấn trả góp</option>
                <option>Đăng ký lái thử</option>
                <option>Nhận báo giá lăn bánh</option>
                <option>Đặt cọc xe</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill text-uppercase" style="letter-spacing: 1px;">Yêu cầu hỗ trợ</button>
          </form>

          <hr class="border-secondary my-4">
          <div class="d-flex align-items-center mt-4">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:45px;height:45px;">
              <i class="bi bi-telephone-fill text-white"></i>
            </div>
            <div>
              <p class="mb-0 text-white-50 small">Hotline trực tiếp</p>
              <h5 class="mb-0 fw-bold">1900 8888</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/navbar-shrink.js"></script>
<script src="../js/content-reveal.js"></script>
</body>
</html>

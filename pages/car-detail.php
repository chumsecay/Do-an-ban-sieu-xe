<?php
require_once __DIR__ . '/../bootstrap/env.php';
$currentPage = 'showroom';
$appName = env('APP_NAME', 'FLCar');

// Mock Data (Replace with DB fetch later)
$car = [
    'name' => 'BMW X5 xDrive40i M Sport 2024',
    'price' => '$65,000',
    'hero_img' => '../img/bmwx5.jpg',
    'specs' => [
        'engine' => '3.0L I6 TwinPower Turbo',
        'power' => '375 HP',
        '0_100' => '5.3s',
        'top_speed' => '243 km/h',
        'transmission' => '8-cấp Steptronic',
        'drivetrain' => 'AWD (xDrive)'
    ],
    'description' => 'BMW X5 2024 mang đến một diện mạo thể thao sắc sảo hơn cùng khoang nội thất nâng cấp toàn diện với màn hình cong BMW Curved Display. Động cơ I6 thế hệ mới tích hợp công nghệ Mild Hybrid 48V cho hiệu suất vượt trội và khả năng tiết kiệm nhiên liệu tối ưu.',
    'gallery' => [
        '../img/mer amg suvs.jpg',
        '../img/mercedes-amg-63.jpg',
        '../img/porsche.jpg'
    ]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($car['name']); ?> - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="../css/flcar-common.css?v=8" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
<style>
/* Page Specific Styles */
.car-hero {
  height: 90vh;
  position: relative;
  background-image: url('<?php echo $car['hero_img']; ?>');
  background-size: cover;
  background-position: center;
  background-attachment: fixed; /* Parallax effect */
  display: flex;
  align-items: flex-end;
  padding-bottom: 80px;
}
.car-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.4) 50%, rgba(15, 23, 42, 0.1) 100%);
}
.hero-content {
  position: relative;
  z-index: 2;
  width: 100%;
}
.car-title {
  font-size: 4.5rem;
  font-weight: 900;
  letter-spacing: -1px;
  line-height: 1.1;
  color: #fff;
  text-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.car-price-badge {
  display: inline-block;
  background: var(--gradient-primary);
  color: #fff;
  padding: 10px 24px;
  border-radius: 30px;
  font-size: 1.5rem;
  font-weight: 800;
  box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
}
.spec-box {
  background: #fff;
  border: 1px solid rgba(0,0,0,0.05);
  border-radius: 16px;
  padding: 24px;
  text-align: center;
  transition: transform 0.3s, box-shadow 0.3s;
  height: 100%;
}
.spec-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0,0,0,0.08);
}
.spec-icon {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 12px;
}
.spec-value {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--dark);
  margin-bottom: 4px;
}
.spec-label {
  font-size: 0.85rem;
  color: var(--text-light);
  text-transform: uppercase;
  letter-spacing: 1px;
}
.gallery-img {
  width: 100%;
  height: 350px;
  object-fit: cover;
  border-radius: 16px;
  transition: transform 0.5s;
}
.gallery-wrap {
  overflow: hidden;
  border-radius: 16px;
}
.gallery-wrap:hover .gallery-img {
  transform: scale(1.05);
}
.sticky-sidebar {
  position: sticky;
  top: 100px;
}
.contact-card {
  background: var(--dark);
  color: #fff;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
@media (max-width: 768px) {
  .car-title { font-size: 2.8rem; }
  .car-hero { height: 70vh; }
}
</style>
</head>
<body class="has-hero">
<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- HERO SECTION -->
<section class="car-hero">
  <div class="container hero-content reveal-item">
    <div class="row align-items-end">
      <div class="col-lg-8">
        <h1 class="car-title mb-4"><?php echo $car['name']; ?></h1>
        <div class="car-price-badge"><?php echo $car['price']; ?></div>
      </div>
      <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
        <a href="#booking-section" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold" style="border-width: 2px;">Nhận Tư Vấn Ngay</a>
      </div>
    </div>
  </div>
</section>

<div class="container py-5 mt-4">
  <div class="row g-5">
    
    <!-- MAIN CONTENT (LEFT) -->
    <div class="col-lg-8">
      
      <!-- Giới thiệu chung -->
      <div class="mb-5 reveal-item">
        <h3 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-info-circle text-primary me-2"></i> Tổng quan xe</h3>
        <p class="lead text-muted" style="line-height: 1.8; font-size: 1.1rem;">
          <?php echo $car['description']; ?>
        </p>
      </div>

      <!-- Thông số nổi bật (Specs Grid) -->
      <div class="mb-5 reveal-item">
        <h3 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-gear-wide-connected text-primary me-2"></i> Thông số kỹ thuật</h3>
        <div class="row g-3">
          <div class="col-md-4 col-6">
            <div class="spec-box">
              <i class="bi bi-lightning-charge spec-icon"></i>
              <div class="spec-value"><?php echo $car['specs']['power']; ?></div>
              <div class="spec-label">Mã lực (HP)</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="spec-box">
              <i class="bi bi-stopwatch spec-icon"></i>
              <div class="spec-value"><?php echo $car['specs']['0_100']; ?></div>
              <div class="spec-label">0 - 100 km/h</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="spec-box">
              <i class="bi bi-speedometer2 spec-icon"></i>
              <div class="spec-value"><?php echo $car['specs']['top_speed']; ?></div>
              <div class="spec-label">Tốc độ tối đa</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="spec-box">
              <i class="bi bi-ev-front spec-icon"></i>
              <div class="spec-value"><?php echo $car['specs']['engine']; ?></div>
              <div class="spec-label">Động Cơ</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="spec-box">
              <i class="bi bi-bezier2 spec-icon"></i>
              <div class="spec-value"><?php echo $car['specs']['drivetrain']; ?></div>
              <div class="spec-label">Hệ dẫn động</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="spec-box">
              <i class="bi bi-sliders spec-icon"></i>
              <div class="spec-value"><?php echo $car['specs']['transmission']; ?></div>
              <div class="spec-label">Hộp số</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Thư viện ảnh -->
      <div class="mb-5 reveal-item">
        <h3 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-images text-primary me-2"></i> Thư viện Hình Ảnh</h3>
        <div class="row g-3">
          <?php foreach($car['gallery'] as $idx => $img): ?>
          <div class="<?php echo $idx === 0 ? 'col-12' : 'col-md-6'; ?>">
            <div class="gallery-wrap">
              <img src="<?php echo $img; ?>" class="gallery-img" alt="Gallery">
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <!-- SIDEBAR (RIGHT) -->
    <div class="col-lg-4" id="booking-section">
      <div class="sticky-sidebar reveal-right">
        <div class="contact-card">
          <h4 class="fw-bold mb-2">Quan tâm xe này?</h4>
          <p class="text-white-50 mb-4" style="font-size: 0.9rem;">Để lại thông tin, chuyên viên tư vấn của FLCar sẽ liên hệ bạn ngay lập tức.</p>
          
          <form>
            <div class="mb-3">
              <label class="form-label text-white-50 small">Họ và tên</label>
              <input type="text" class="form-control bg-dark text-white border-secondary" placeholder="Vd: Nguyễn Văn A">
            </div>
            <div class="mb-3">
              <label class="form-label text-white-50 small">Số điện thoại</label>
              <input type="tel" class="form-control bg-dark text-white border-secondary" placeholder="0901 234 567">
            </div>
            <div class="mb-4">
              <label class="form-label text-white-50 small">Nhu cầu</label>
              <select class="form-select bg-dark text-white border-secondary">
                <option>Tư vấn trả góp</option>
                <option>Đăng ký lái thử</option>
                <option>Nhận báo giá lăn bánh</option>
              </select>
            </div>
            <button type="button" class="btn btn-primary w-100 py-3 fw-bold rounded-pill text-uppercase" style="letter-spacing: 1px;">Yêu cầu hỗ trợ</button>
          </form>

          <hr class="border-secondary my-4">
          <div class="d-flex align-items-center mt-4">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
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

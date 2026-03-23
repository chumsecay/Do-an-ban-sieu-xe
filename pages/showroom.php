<?php
require_once __DIR__ . '/../bootstrap/env.php';
$currentPage = 'showroom';
$appName = env('APP_NAME', 'FLCar');

// Prepare array for DB extraction later
$cars = [
    ['id' => 'XE-001', 'name' => 'BMW X5 2024', 'type' => 'SUV', 'image' => '../img/bmwx5.jpg', 'desc' => 'Tự động - 5 chỗ - Turbo', 'price' => '$65,000'],
    ['id' => 'XE-002', 'name' => 'Mercedes AMG SUVs', 'type' => 'Sedan', 'image' => '../img/mer amg suvs.jpg', 'desc' => 'Hybrid - 4 chỗ', 'price' => '$55,000'],
    ['id' => 'XE-003', 'name' => 'Rolls Royce Phantom', 'type' => 'Luxury', 'image' => '../img/rollroyce phamtom viii.jpg', 'desc' => 'Nội thất thủ công - Signature', 'price' => '$320,000'],
    ['id' => 'XE-004', 'name' => 'Ford Mustang GT', 'type' => 'Sport', 'image' => '../img/ford mustang.jpg', 'desc' => 'V8 - Dẫn động cầu sau', 'price' => '$58,000'],
    ['id' => 'XE-005', 'name' => 'Mazda MX-5', 'type' => 'Roadster', 'image' => '../img/Mazda MX-5.jpg', 'desc' => '2 chỗ - Nhẹ và linh hoạt', 'price' => '$35,000'],
    ['id' => 'XE-006', 'name' => 'Lamborghini Veneno', 'type' => 'Hypercar', 'image' => '../img/lamborghini veneno roadster carbon.jpg', 'desc' => 'Carbon - Giới hạn toàn cầu', 'price' => '$3,500,000'],
    ['id' => 'XE-007', 'name' => 'BMW 430i Convertible', 'type' => 'Convertible', 'image' => '../img/bmw 430i converible.jpg', 'desc' => 'Mui trần - Phong cách trẻ', 'price' => '$62,000'],
    ['id' => 'XE-008', 'name' => 'MG Cyberster', 'type' => 'Electric', 'image' => '../img/mg cyberster.jpg', 'desc' => 'EV - Tăng tốc nhanh, vận hành êm', 'price' => '$48,000'],
    ['id' => 'XE-009', 'name' => 'Ferrari 488 Pista Spider', 'type' => 'Supercar', 'image' => '../img/ferrari 488 pista spider.jpg', 'desc' => 'Hiệu suất cao - Thiết kế đầy cảm xúc', 'price' => '$410,000']
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Showroom - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Khám phá bộ sưu tập xe cao cấp tại FLCar Showroom">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=3" rel="stylesheet">

<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <p class="section-label" style="color:rgba(255,255,255,.7);justify-content:center">
      <span style="background:rgba(255,255,255,.4)" class="d-inline-block" role="presentation"></span>
      Bộ sưu tập
    </p>
    <h1 class="display-4 fw-bold">Showroom Xe Cao Cấp</h1>
    <p class="lead" style="opacity:.85">Khám phá bộ sưu tập xe sang mới nhất</p>
  </div>
</section>

<div class="container" style="margin-top:-40px;position:relative;z-index:2;margin-bottom:32px">
  <div style="background:var(--white);padding:24px;border-radius:var(--radius);box-shadow:var(--shadow-md)">
    <form method="GET" action="showroom.php" class="row g-3 align-items-center">
      <div class="col-lg-3 col-md-6"><input type="text" name="q" class="form-control" placeholder="Tìm kiếm tên xe..." value="<?php echo htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px"></div>
      <div class="col-lg-3 col-md-6"><select name="brand" class="form-select" style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px"><option value="">Chọn hãng xe</option><option>BMW</option><option>Mercedes</option><option>Ford</option><option>Lamborghini</option><option>Ferrari</option><option>Rolls Royce</option></select></div>
      <div class="col-lg-3 col-md-6"><select name="price" class="form-select" style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px"><option value="">Mức giá</option><option>Dưới $40,000</option><option>$40,000 - $60,000</option><option>$60,000 - $100,000</option><option>Trên $100,000</option></select></div>
      <div class="col-lg-3 col-md-6"><button type="submit" class="btn btn-primary w-100" style="border-radius:var(--radius-sm);padding:10px;font-weight:600;background:var(--gradient-primary);border:none"><svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2" style="margin-right:6px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>Tìm kiếm</button></div>
    </form>
  </div>
</div>

<section style="padding:0 0 80px">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($cars as $car): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card car-card shadow-sm position-relative">
          <span class="badge-type"><?php echo $car['type']; ?></span>
          <img src="<?php echo $car['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($car['name']); ?>">
          <div class="card-body">
            <h5 class="fw-bold"><?php echo htmlspecialchars($car['name']); ?></h5>
            <p class="text-muted mb-2"><?php echo $car['desc']; ?></p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price-tag"><?php echo $car['price']; ?></span>
              <a href="../chitietxe.html?id=<?php echo urlencode($car['id']); ?>" class="btn btn-dark btn-sm px-3">Chi tiết</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/navbar-shrink.js"></script>
<script src="../js/content-reveal.js"></script>
</body>
</html>

<?php
require_once __DIR__ . '/../bootstrap/env.php';
$currentPage = 'showroom';
$appName = env('APP_NAME', 'FLCar');
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
    <div class="row g-3 align-items-center">
      <div class="col-md-4"><select class="form-select" style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px"><option>Chọn hãng xe</option><option>BMW</option><option>Mercedes</option><option>Ford</option><option>Lamborghini</option><option>Ferrari</option><option>Rolls Royce</option></select></div>
      <div class="col-md-4"><select class="form-select" style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px"><option>Mức giá</option><option>Dưới $40,000</option><option>$40,000 - $60,000</option><option>$60,000 - $100,000</option><option>Trên $100,000</option></select></div>
      <div class="col-md-4"><button class="btn btn-primary w-100" style="border-radius:var(--radius-sm);padding:10px;font-weight:600;background:var(--gradient-primary);border:none">Tìm kiếm</button></div>
    </div>
  </div>
</div>

<section style="padding:0 0 80px">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">SUV</span><img src="../img/bmwx5.jpg" class="card-img-top" alt="BMW X5"><div class="card-body"><h5 class="fw-bold">BMW X5 2024</h5><p class="text-muted mb-2">Tự động - 5 chỗ - Turbo</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$65,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Sedan</span><img src="../img/mer amg suvs.jpg" class="card-img-top" alt="Mercedes"><div class="card-body"><h5 class="fw-bold">Mercedes AMG SUVs</h5><p class="text-muted mb-2">Hybrid - 4 chỗ</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$55,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Luxury</span><img src="../img/rollroyce phamtom viii.jpg" class="card-img-top" alt="Rolls Royce"><div class="card-body"><h5 class="fw-bold">Rolls Royce Phantom</h5><p class="text-muted mb-2">Nội thất thủ công - Signature</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$320,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Sport</span><img src="../img/ford mustang.jpg" class="card-img-top" alt="Ford Mustang"><div class="card-body"><h5 class="fw-bold">Ford Mustang GT</h5><p class="text-muted mb-2">V8 - Dẫn động cầu sau</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$58,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Roadster</span><img src="../img/Mazda MX-5.jpg" class="card-img-top" alt="Mazda MX-5"><div class="card-body"><h5 class="fw-bold">Mazda MX-5</h5><p class="text-muted mb-2">2 chỗ - Nhẹ và linh hoạt</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$35,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Hypercar</span><img src="../img/lamborghini veneno roadster carbon.jpg" class="card-img-top" alt="Lamborghini Veneno"><div class="card-body"><h5 class="fw-bold">Lamborghini Veneno</h5><p class="text-muted mb-2">Carbon - Giới hạn toàn cầu</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$3,500,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Convertible</span><img src="../img/bmw 430i converible.jpg" class="card-img-top" alt="BMW 430i"><div class="card-body"><h5 class="fw-bold">BMW 430i Convertible</h5><p class="text-muted mb-2">Mui trần - Phong cách trẻ</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$62,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Electric</span><img src="../img/mg cyberster.jpg" class="card-img-top" alt="MG Cyberster"><div class="card-body"><h5 class="fw-bold">MG Cyberster</h5><p class="text-muted mb-2">EV - Tăng tốc nhanh, vận hành êm</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$48,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
      <div class="col-lg-4 col-md-6"><div class="card car-card shadow-sm position-relative"><span class="badge-type">Supercar</span><img src="../img/ferrari 488 pista spider.jpg" class="card-img-top" alt="Ferrari 488"><div class="card-body"><h5 class="fw-bold">Ferrari 488 Pista Spider</h5><p class="text-muted mb-2">Hiệu suất cao - Thiết kế đầy cảm xúc</p><div class="d-flex justify-content-between align-items-center"><span class="price-tag">$410,000</span><a href="../chitietxe.html" class="btn btn-dark btn-sm px-3">Chi tiết</a></div></div></div></div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/navbar-shrink.js"></script>
<script src="../js/content-reveal.js"></script>
</body>
</html>

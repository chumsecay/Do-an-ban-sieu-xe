<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
ensureSessionStarted();
$currentPage = 'about';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Giới Thiệu - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">

<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <p class="section-label" style="color:rgba(255,255,255,.7);justify-content:center">
      <span style="background:rgba(255,255,255,.4)" class="d-inline-block" role="presentation"></span>
      FLCar
    </p>
    <h1 class="display-4 fw-bold">Về Chúng Tôi</h1>
    <p class="lead" style="opacity:.85">Showroom xe cao cấp uy tín và minh bạch</p>
  </div>
</section>

<section style="padding:80px 0">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6 reveal-left">
        <img src="../img/about.jpg" class="img-fluid about-img" alt="About">
      </div>
      <div class="col-lg-6 reveal-right">
        <p class="section-label">Câu chuyện của chúng tôi</p>
        <h2 class="section-title" style="text-align:left"><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> - Showroom Ô Tô Cao Cấp</h2>
        <p style="line-height:1.8;font-size:.95rem">Chúng tôi chuyên cung cấp xe chính hãng, xe lướt chất lượng cao và các dòng xe sang nhập khẩu. Với hơn <strong>10 năm kinh nghiệm</strong> trong ngành, FLCar đã trở thành địa chỉ tin cậy của hàng nghìn khách hàng trên toàn quốc.</p>
        <p style="line-height:1.8;font-size:.95rem" class="mb-4">Cam kết rõ ràng về hồ sơ, hỗ trợ trả góp linh hoạt, bảo hành minh bạch và đồng hành hậu mãi lâu dài.</p>
        <div class="row g-3 mb-4">
          <div class="col-4 text-center">
            <h3 class="fw-bold" style="color:var(--primary)">10+</h3>
            <p class="mb-0" style="font-size:.85rem;color:var(--text-light)">Năm kinh nghiệm</p>
          </div>
          <div class="col-4 text-center">
            <h3 class="fw-bold" style="color:var(--primary)">1000+</h3>
            <p class="mb-0" style="font-size:.85rem;color:var(--text-light)">Khách hàng</p>
          </div>
          <div class="col-4 text-center">
            <h3 class="fw-bold" style="color:var(--primary)">500+</h3>
            <p class="mb-0" style="font-size:.85rem;color:var(--text-light)">Xe đã bán</p>
          </div>
        </div>
        <a href="showroom.php" class="btn btn-primary px-4" style="border-radius:var(--radius-sm);font-weight:600;background:var(--gradient-primary);border:none">Xem Showroom →</a>
      </div>
    </div>
  </div>
</section>

<!-- Tầm nhìn & Sứ mệnh -->
<section style="background:#f8fafc;padding:80px 0">
  <div class="container">
    <div class="text-center mb-5">
      <p class="section-label" style="justify-content:center">Giá trị cốt lõi</p>
      <h2 class="section-title">Tầm Nhìn & Sứ Mệnh</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card">
          <div class="feature-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
          </div>
          <h5>Tầm Nhìn</h5>
          <p>Trở thành showroom ô tô hàng đầu Việt Nam, mang đến trải nghiệm đẳng cấp quốc tế.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="feature-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <h5>Sứ Mệnh</h5>
          <p>Cung cấp xe chất lượng cao với giá tốt nhất, dịch vụ hậu mãi xuất sắc và minh bạch.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="feature-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          </div>
          <h5>Cam Kết</h5>
          <p>Mỗi chiếc xe đều được kiểm định nghiêm ngặt, đảm bảo 100% chất lượng cho khách hàng.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="container">
    <h2>Sẵn Sàng Trải Nghiệm?</h2>
    <p>Ghé thăm showroom hoặc liên hệ ngay để được tư vấn miễn phí.</p>
    <a href="contact.php" class="btn btn-light btn-lg me-3">Liên Hệ Ngay</a>
    <a href="showroom.php" class="btn btn-outline-light btn-lg">Xem Showroom</a>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/navbar-shrink.js"></script>
<script src="../js/content-reveal.js"></script>
</body>
</html>

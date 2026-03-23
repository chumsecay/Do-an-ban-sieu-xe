<?php
require_once __DIR__ . '/bootstrap/env.php';
$currentPage = 'home';
$appName = env('APP_NAME', 'FLCar');
$heroTitle = env('APP_HERO_TITLE', 'Premium Cars Collection');
$heroSubtitle = env('APP_HERO_SUBTITLE', 'Đẳng cấp - Chất lượng - Giá tốt nhất thị trường');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> - Showroom Ô Tô Cao Cấp</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="FLCar - Showroom ô tô cao cấp. Xe nhập khẩu chính hãng, giá tốt nhất thị trường.">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/flcar-common.css?v=3" rel="stylesheet">

<link rel="icon" href="img/logo.png" type="image/png">
</head>
<body class="has-hero">
<?php include __DIR__ . '/partials/header.php'; ?>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="hero-overlay">
    <div class="container text-center">
      <p class="section-label" style="color:rgba(255,255,255,.7);justify-content:center">
        <span style="background:rgba(255,255,255,.4)" class="d-inline-block" role="presentation"></span>
        Showroom Ô Tô Hàng Đầu Việt Nam
      </p>
      <h1 class="display-3 fw-bold mb-3"><?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
      <p class="lead mb-4"><?php echo htmlspecialchars($heroSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
      <div class="mb-4">
        <a href="pages/showroom.php" class="btn btn-primary btn-lg me-3">Khám Phá Ngay</a>
        <a href="pages/contact.php" class="btn btn-outline-light btn-lg">Tư Vấn Miễn Phí</a>
      </div>
      <div class="hero-stats">
        <div class="stat-item">
          <span class="stat-number">500+</span>
          <span class="stat-label">Xe đã bán</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">10+</span>
          <span class="stat-label">Năm kinh nghiệm</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">1000+</span>
          <span class="stat-label">Khách hàng</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">15+</span>
          <span class="stat-label">Hãng xe</span>
        </div>
      </div>
    </div>
    <a href="#featured-cars" class="scroll-indicator" style="color:inherit;text-decoration:none">
      <div class="scroll-mouse"></div>
      <span>Cuộn xuống</span>
    </a>
  </div>
</section>

<!-- ===== BRANDS BAR ===== -->
<section class="brands-section">
  <div class="brands-track" aria-hidden="true">
    <span>BMW</span><span>Mercedes-Benz</span><span>Rolls Royce</span><span>Lamborghini</span>
    <span>Ferrari</span><span>Ford</span><span>Mazda</span><span>Porsche</span>
    <span>Audi</span><span>Lexus</span><span>Bentley</span><span>McLaren</span>
    <span>BMW</span><span>Mercedes-Benz</span><span>Rolls Royce</span><span>Lamborghini</span>
    <span>Ferrari</span><span>Ford</span><span>Mazda</span><span>Porsche</span>
    <span>Audi</span><span>Lexus</span><span>Bentley</span><span>McLaren</span>
  </div>
</section>

<!-- ===== XE NỔI BẬT ===== -->
<section id="featured-cars" class="py-5" style="padding-top:80px!important;padding-bottom:80px!important">
  <div class="container">
    <div class="text-center">
      <p class="section-label" style="justify-content:center">Bộ sưu tập</p>
      <h2 class="section-title">Xe Nổi Bật</h2>
      <p class="section-subtitle">Khám phá những mẫu xe sang trọng nhất tại showroom của chúng tôi</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm car-card position-relative">
          <span class="badge-type">SUV</span>
          <img src="img/bmwx5.jpg" class="card-img-top" alt="BMW X5">
          <div class="card-body">
            <h5 class="fw-bold">BMW X5 2024</h5>
            <p class="text-muted mb-2">SUV hạng sang, nội thất cao cấp</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price-tag">$65,000</span>
              <a href="chitietxe.html" class="btn btn-dark btn-sm px-3">Xem Chi Tiết</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm car-card position-relative">
          <span class="badge-type">Sedan</span>
          <img src="img/mer amg suvs.jpg" class="card-img-top" alt="Mercedes AMG SUVs">
          <div class="card-body">
            <h5 class="fw-bold">Mercedes AMG SUVs</h5>
            <p class="text-muted mb-2">Sedan sang trọng, động cơ mạnh mẽ</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price-tag">$55,000</span>
              <a href="chitietxe.html" class="btn btn-dark btn-sm px-3">Xem Chi Tiết</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm car-card position-relative">
          <span class="badge-type">Luxury</span>
          <img src="img/rollroyce phamtom viii.jpg" class="card-img-top" alt="Rolls Royce Phantom">
          <div class="card-body">
            <h5 class="fw-bold">Rolls Royce Phantom</h5>
            <p class="text-muted mb-2">Siêu sang, nội thất thủ công tinh xảo</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price-tag">$320,000</span>
              <a href="chitietxe.html" class="btn btn-dark btn-sm px-3">Xem Chi Tiết</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm car-card position-relative">
          <span class="badge-type">Sport</span>
          <img src="img/ford mustang.jpg" class="card-img-top" alt="Ford Mustang">
          <div class="card-body">
            <h5 class="fw-bold">Ford Mustang GT</h5>
            <p class="text-muted mb-2">Coupe thể thao, động cơ V8 phấn khích</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price-tag">$58,000</span>
              <a href="chitietxe.html" class="btn btn-dark btn-sm px-3">Xem Chi Tiết</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm car-card position-relative">
          <span class="badge-type">Roadster</span>
          <img src="img/Mazda MX-5.jpg" class="card-img-top" alt="Mazda MX-5">
          <div class="card-body">
            <h5 class="fw-bold">Mazda MX-5</h5>
            <p class="text-muted mb-2">Roadster gọn nhẹ, cảm giác lái linh hoạt</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price-tag">$35,000</span>
              <a href="chitietxe.html" class="btn btn-dark btn-sm px-3">Xem Chi Tiết</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm car-card position-relative">
          <span class="badge-type">Hypercar</span>
          <img src="img/lamborghini veneno roadster carbon.jpg" class="card-img-top" alt="Lamborghini Veneno">
          <div class="card-body">
            <h5 class="fw-bold">Lamborghini Veneno</h5>
            <p class="text-muted mb-2">Hypercar hiếm, thiết kế khí động học</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price-tag">$3,500,000</span>
              <a href="chitietxe.html" class="btn btn-dark btn-sm px-3">Xem Chi Tiết</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-center mt-5">
      <a href="pages/showroom.php" class="btn btn-primary btn-lg px-5">Xem Tất Cả Xe →</a>
    </div>
  </div>
</section>

<!-- ===== TẠI SAO CHỌN CHÚNG TÔI ===== -->
<section style="background:#f8fafc;padding:80px 0">
  <div class="container">
    <div class="text-center">
      <p class="section-label" style="justify-content:center">Ưu điểm vượt trội</p>
      <h2 class="section-title">Tại Sao Chọn <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?>?</h2>
      <p class="section-subtitle">Chúng tôi cam kết mang đến trải nghiệm mua xe tốt nhất cho quý khách hàng</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M9 12l2 2 4-4"/></svg>
          </div>
          <h5>Xe Chính Hãng</h5>
          <p>100% xe nhập khẩu chính hãng, đầy đủ giấy tờ hải quan và hồ sơ pháp lý minh bạch.</p>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
          </div>
          <h5>Giá Tốt Nhất</h5>
          <p>Cam kết giá cạnh tranh nhất thị trường. Hỗ trợ trả góp lãi suất ưu đãi từ 6.5%/năm.</p>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11a8 8 0 0116 0c0 6.5-8 11-8 11z"/><circle cx="12" cy="11" r="3"/></svg>
          </div>
          <h5>Bảo Hành Toàn Diện</h5>
          <p>Bảo hành chính hãng lên đến 5 năm. Hệ thống xưởng dịch vụ đạt chuẩn quốc tế.</p>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <h5>Tư Vấn Tận Tâm</h5>
          <p>Đội ngũ chuyên viên nhiều năm kinh nghiệm, sẵn sàng hỗ trợ và đồng hành cùng bạn.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== DỊCH VỤ ===== -->
<section style="padding:80px 0">
  <div class="container">
    <div class="text-center">
      <p class="section-label" style="justify-content:center">Dịch vụ của chúng tôi</p>
      <h2 class="section-title">Dịch Vụ Chuyên Nghiệp</h2>
      <p class="section-subtitle">Đồng hành cùng bạn từ khi chọn xe đến suốt quá trình sử dụng</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-3 col-sm-6">
        <div class="service-card">
          <img src="img/service-1.jpg" alt="Tư vấn mua xe">
          <div class="service-card-overlay">
            <h4>Tư Vấn Mua Xe</h4>
            <p>Hỗ trợ chọn xe phù hợp nhu cầu và ngân sách</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6">
        <div class="service-card">
          <img src="img/service-2.jpg" alt="Bảo dưỡng định kỳ">
          <div class="service-card-overlay">
            <h4>Bảo Dưỡng Định Kỳ</h4>
            <p>Xưởng hiện đại, kỹ thuật viên tay nghề cao</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6">
        <div class="service-card">
          <img src="img/service-3.jpg" alt="Hỗ trợ tài chính">
          <div class="service-card-overlay">
            <h4>Hỗ Trợ Tài Chính</h4>
            <p>Trả góp linh hoạt, lãi suất ưu đãi hấp dẫn</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6">
        <div class="service-card">
          <img src="img/service-4.jpg" alt="Bảo hiểm xe">
          <div class="service-card-overlay">
            <h4>Bảo Hiểm Xe Hơi</h4>
            <p>Liên kết các hãng bảo hiểm uy tín trong nước</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== STATS ===== -->
<section class="stats-section">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3 col-6">
        <div class="counter-item">
          <div class="counter-number">500+</div>
          <div class="counter-label">Xe đã bán thành công</div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="counter-item">
          <div class="counter-number">1,000+</div>
          <div class="counter-label">Khách hàng hài lòng</div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="counter-item">
          <div class="counter-number">15+</div>
          <div class="counter-label">Hãng xe liên kết</div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="counter-item">
          <div class="counter-number">98%</div>
          <div class="counter-label">Tỉ lệ hài lòng</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== VỀ CHÚNG TÔI ===== -->
<section style="padding:80px 0">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6 reveal-left">
        <img src="img/about.jpg" class="img-fluid about-img" alt="About FLCar">
      </div>
      <div class="col-lg-6 reveal-right">
        <p class="section-label">Về chúng tôi</p>
        <h2 class="section-title" style="text-align:left"><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> – Showroom Uy Tín Hàng Đầu</h2>
        <p class="mb-3" style="line-height:1.8;font-size:.95rem;color:var(--text)">Với hơn <strong>10 năm kinh nghiệm</strong> trong lĩnh vực kinh doanh ô tô cao cấp, <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> tự hào là đối tác tin cậy của hàng nghìn khách hàng trên toàn quốc.</p>
        <p class="mb-4" style="line-height:1.8;font-size:.95rem;color:var(--text)">Chúng tôi chuyên cung cấp xe chính hãng, xe nhập khẩu chất lượng cao, đảm bảo hồ sơ minh bạch và hậu mãi tận tâm. Mỗi chiếc xe đều được kiểm định nghiêm ngặt trước khi đến tay khách hàng.</p>
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
        <a href="pages/about.php" class="btn btn-primary px-4">Tìm Hiểu Thêm →</a>
      </div>
    </div>
  </div>
</section>

<!-- ===== ĐÁNH GIÁ KHÁCH HÀNG ===== -->
<section style="background:#f8fafc;padding:80px 0">
  <div class="container">
    <div class="text-center">
      <p class="section-label" style="justify-content:center">Phản hồi khách hàng</p>
      <h2 class="section-title">Khách Hàng Nói Gì?</h2>
      <p class="section-subtitle">Những chia sẻ chân thật từ khách hàng đã tin tưởng và sử dụng dịch vụ của chúng tôi</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <div class="quote-icon">"</div>
          <p>Dịch vụ tuyệt vời! Tôi đã mua chiếc BMW X5 tại đây và rất hài lòng với quy trình tư vấn chuyên nghiệp. Xe đúng như mô tả, giấy tờ rõ ràng.</p>
          <div class="testimonial-author">
            <img src="img/testimonial-1.jpg" alt="Nguyễn Văn An">
            <div>
              <strong>Nguyễn Văn An</strong>
              <small>Doanh nhân · TP.HCM</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <div class="quote-icon">"</div>
          <p>Giá cả cạnh tranh, nhân viên nhiệt tình. Đặc biệt là hỗ trợ trả góp rất nhanh chóng và thuận tiện. Chắc chắn sẽ giới thiệu cho bạn bè!</p>
          <div class="testimonial-author">
            <img src="img/testimonial-2.jpg" alt="Trần Thị Bình">
            <div>
              <strong>Trần Thị Bình</strong>
              <small>Bác sĩ · Đà Nẵng</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <div class="quote-icon">"</div>
          <p>Đã mua 2 xe tại FLCar trong 3 năm. Chất lượng dịch vụ sau bán hàng rất tốt, luôn hỗ trợ khi cần. Showroom uy tín mà tôi tin tưởng hoàn toàn.</p>
          <div class="testimonial-author">
            <img src="img/testimonial-3.jpg" alt="Lê Hoàng Dũng">
            <div>
              <strong>Lê Hoàng Dũng</strong>
              <small>Giám đốc · Hà Nội</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== CTA ===== -->
<section class="cta-section">
  <div class="container">
    <h2>Bạn Đã Sẵn Sàng Sở Hữu Xe Mơ Ước?</h2>
    <p>Liên hệ ngay để được tư vấn miễn phí và nhận ưu đãi đặc biệt dành riêng cho bạn.</p>
    <a href="pages/contact.php" class="btn btn-light btn-lg me-3">Liên Hệ Ngay</a>
    <a href="pages/showroom.php" class="btn btn-outline-light btn-lg">Xem Showroom</a>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/navbar-shrink.js"></script>
<script src="js/content-reveal.js"></script>
</body>
</html>

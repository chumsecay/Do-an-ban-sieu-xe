<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
ensureSessionStarted();
$currentPage = 'contact';
$appName = env('APP_NAME', 'FLCar');
$contactEmail = env('CONTACT_EMAIL', 'info@flcar.vn');
$contactPhone = env('CONTACT_PHONE', '0900 000 000');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Liên Hệ - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<style>
  .contact-card { background:var(--white);border-radius:var(--radius);padding:32px;border:1px solid var(--border);transition:all var(--transition);height:100%; }
  .contact-card:hover { box-shadow:var(--shadow-lg);transform:translateY(-4px); }
  .contact-icon { width:52px;height:52px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px; }
  .contact-icon svg { width:24px;height:24px;color:#fff; }
  .contact-form input,.contact-form textarea,.contact-form select { border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;font-size:.9rem;transition:border-color .25s,box-shadow .25s;width:100%; }
  .contact-form input:focus,.contact-form textarea:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.1); }
  .contact-form textarea { min-height:140px;resize:vertical; }
  .contact-form .btn-submit { background:var(--gradient-primary);border:none;color:#fff;padding:14px 40px;border-radius:var(--radius-sm);font-weight:700;font-size:.95rem;transition:all var(--transition); }
  .contact-form .btn-submit:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.3); }
</style>

<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <p class="section-label" style="color:rgba(255,255,255,.7);justify-content:center">
      <span style="background:rgba(255,255,255,.4)" class="d-inline-block" role="presentation"></span>
      Liên hệ
    </p>
    <h1 class="display-4 fw-bold">Liên Hệ Với Chúng Tôi</h1>
    <p class="lead" style="opacity:.85">Nhận tư vấn nhanh từ đội ngũ <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></p>
  </div>
</section>

<section style="padding:80px 0">
  <div class="container">
    <!-- Info Cards -->
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="contact-card text-center">
          <div class="contact-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <h5 style="font-weight:700">Email</h5>
          <p style="color:var(--text-light);margin:0"><?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-card text-center">
          <div class="contact-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
          </div>
          <h5 style="font-weight:700">Hotline</h5>
          <p style="color:var(--text-light);margin:0"><?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-card text-center">
          <div class="contact-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <h5 style="font-weight:700">Showroom</h5>
          <p style="color:var(--text-light);margin:0">123 Đường ABC, TP.HCM</p>
        </div>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="row g-5">
      <div class="col-lg-7">
        <h3 class="fw-bold mb-4">Gửi Tin Nhắn</h3>
        <form class="contact-form">
          <div class="row g-3">
            <div class="col-md-6"><input type="text" placeholder="Họ và tên" required></div>
            <div class="col-md-6"><input type="email" placeholder="Email" required></div>
            <div class="col-md-6"><input type="tel" placeholder="Số điện thoại"></div>
            <div class="col-md-6"><input type="text" placeholder="Chủ đề"></div>
            <div class="col-12"><textarea placeholder="Nội dung tin nhắn..."></textarea></div>
            <div class="col-12"><button type="submit" class="btn-submit">Gửi Liên Hệ →</button></div>
          </div>
        </form>
      </div>
      <div class="col-lg-5">
        <h3 class="fw-bold mb-4">Bản Đồ</h3>
        <div style="background:var(--light);border-radius:var(--radius);height:380px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border)">
          <p style="color:var(--text-light);font-size:.9rem">Google Maps sẽ được tích hợp tại đây</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/navbar-shrink.js"></script>
<script src="../js/content-reveal.js"></script>
</body>
</html>

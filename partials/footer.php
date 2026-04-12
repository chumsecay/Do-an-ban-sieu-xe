<?php
require_once __DIR__ . '/../bootstrap/env.php';
$appName = env('APP_NAME', 'FLCar');
$scriptDir = dirname($_SERVER['SCRIPT_FILENAME'] ?? '');
$isSubPage = (basename($scriptDir) === 'pages');
$base = $isSubPage ? '../' : '';
$pageBase = $isSubPage ? '' : 'pages/';
$loginUrl = $base . 'login.php';
?>
<footer class="footer-main text-white">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-lg-6">
        <h5 class="fw-bold">Về <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></h5>
        <p class="mb-2">Chúng tôi mang đến trải nghiệm mua xe cao cấp với đội ngũ tư vấn tận tâm, kiểm định chất lượng nghiêm ngặt và hậu mãi minh bạch.</p>
        <p class="footer-contact mb-0">Showroom chính: 123 Đường ABC, TP.HCM · Hotline: 0900 000 000 · Email: info@FLCar.vn</p>
      </div>
      <div class="col-lg-3 col-6">
        <h5 class="fw-bold">Khám phá</h5>
        <ul class="footer-links list-unstyled mb-0">
          <li><a href="<?php echo $pageBase; ?>showroom.php">Showroom</a></li>
          <li><a href="<?php echo $pageBase; ?>about.php">Giới thiệu</a></li>
          <li><a href="<?php echo $pageBase; ?>contact.php">Liên hệ</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-6">
        <h5 class="fw-bold">Hỗ trợ</h5>
        <ul class="footer-links list-unstyled mb-0">
          <li><a href="<?php echo $loginUrl; ?>">Đăng nhập</a></li>
          <li><a href="<?php echo $pageBase; ?>contact.php">Tư vấn mua xe</a></li>
          <li><a href="<?php echo $pageBase; ?>about.php">Chính sách dịch vụ</a></li>
        </ul>
      </div>
    </div>
    <p class="mb-0 text-center footer-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?>. All Rights Reserved.</p>
  </div>
</footer>
<script src="https://unpkg.com/@studio-freight/lenis@1.0.34/dist/lenis.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Lenis !== 'undefined') {
    const lenis = new Lenis({
      duration: 1.2,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      smoothWheel: true,
      wheelMultiplier: 1.2
    });

    function raf(time) {
      lenis.raf(time);
      requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

    const scrollDownBtn = document.querySelector('a.scroll-indicator[href="#featured-cars"]');
    if (scrollDownBtn) {
      scrollDownBtn.addEventListener('click', (e) => {
        e.preventDefault();
        lenis.scrollTo('#featured-cars', { offset: -76 });
      });
    }
  }
});
</script>
<script src="<?php echo $base; ?>js/search-modal.js"></script>

<?php
require_once __DIR__ . '/../bootstrap/env.php';
$appName = env('APP_NAME', 'FLCar');
$currentPage = $currentPage ?? '';
// Detect if we're inside pages/ subdirectory
$inSubDir = (strpos(str_replace('\\','/',__DIR__), '/partials') !== false);
$scriptDir = dirname($_SERVER['SCRIPT_FILENAME'] ?? '');
$isSubPage = (basename($scriptDir) === 'pages');
$base = $isSubPage ? '../' : '';
$pageBase = $isSubPage ? '' : 'pages/';
?>
<nav id="siteNavbar" class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top site-navbar">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $base; ?>index.php">
      <img class="brand-logo" src="<?php echo $base; ?>img/logo.png" height="100" alt="<?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> Logo">
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="<?php echo $base; ?>index.php">Trang Chủ</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'showroom' ? 'active' : ''; ?>" href="<?php echo $pageBase; ?>showroom.php">Showroom</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="<?php echo $pageBase; ?>about.php">Giới Thiệu</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'news' ? 'active' : ''; ?>" href="<?php echo $pageBase; ?>news.php">Tin Tức</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="<?php echo $pageBase; ?>contact.php">Liên Hệ</a></li>
        <li class="nav-item ms-lg-2 d-flex align-items-center position-relative">
          <div class="nav-search-box" id="navSearchBox">
            <button class="nav-search-btn" id="navSearchBtn" aria-label="Tìm kiếm">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
              </svg>
            </button>
            <input type="text" id="navSearchInput" class="nav-search-input" placeholder="Tìm xe, tin tức..." autocomplete="off">
          </div>
          
          <!-- Dropdown Results -->
          <div class="nav-search-dropdown d-none" id="navSearchResults">
            <div class="dropdown-category">Xe Nổi Bật</div>
            <a href="<?php echo $pageBase; ?>car-detail.php" class="dropdown-item d-flex align-items-center text-decoration-none">
              <img src="<?php echo $base; ?>img/mercedes-amg-63.jpg" class="rounded me-3" alt="Mercedes">
              <div>
                <h6 class="mb-0 text-dark fw-bold" style="font-size:0.9rem">Mercedes AMG G63</h6>
                <small class="text-primary fw-bold">$160,000</small>
              </div>
            </a>
            <a href="<?php echo $pageBase; ?>car-detail.php" class="dropdown-item d-flex align-items-center text-decoration-none">
              <img src="<?php echo $base; ?>img/bmwx5.jpg" class="rounded me-3" alt="BMW">
              <div>
                <h6 class="mb-0 text-dark fw-bold" style="font-size:0.9rem">BMW X5 2024</h6>
                <small class="text-primary fw-bold">$65,000</small>
              </div>
            </a>
            <div class="dropdown-divider"></div>
            <div class="dropdown-category">Tin tức</div>
            <a href="<?php echo $pageBase; ?>news.php" class="dropdown-item d-flex align-items-center text-decoration-none">
              <img src="<?php echo $base; ?>img/hero.jpg" class="rounded me-3" alt="News">
              <div>
                <h6 class="mb-1 text-dark fw-bold" style="font-size:0.85rem; line-height: 1.3">Chương trình khuyến mãi mùa tựu trường</h6>
                <small class="text-muted" style="font-size:0.75rem">12/08/2026</small>
              </div>
            </a>
          </div>
        </li>
        <li class="nav-item ms-lg-2"><a class="nav-link nav-login <?php echo $currentPage === 'login' ? 'active' : ''; ?>" href="<?php echo $pageBase; ?>login.php">Đăng Nhập</a></li>
        <li class="nav-item ms-lg-2"><a class="nav-link nav-admin <?php echo $currentPage === 'admin' ? 'active' : ''; ?>" href="<?php echo $base; ?>admin/index.php">Quản Trị</a></li>
      </ul>
    </div>
  </div>
</nav>



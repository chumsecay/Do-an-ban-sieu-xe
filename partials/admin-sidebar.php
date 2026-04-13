<?php
require_once __DIR__ . '/../bootstrap/env.php';
$appName = env('APP_NAME', 'FLCar');
$adminPage = $adminPage ?? 'dashboard';
// Detect if we're inside admin/ directory
$adminBase = '';
if (strpos($_SERVER['SCRIPT_FILENAME'] ?? '', DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR) !== false) {
  $adminBase = '';       // already inside admin/
  $rootBase = '../';
} else {
  $adminBase = 'admin/'; // called from root
  $rootBase = '';
}
?>
<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="admin-sidebar" id="adminSidebar">
  <a class="sidebar-brand" href="<?php echo $rootBase; ?>index.php">
    <img src="<?php echo $rootBase; ?>img/logo.png" alt="<?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?>">
    <span><?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></span>
  </a>

  <ul class="sidebar-menu">
    <li class="sidebar-section-label">Menu chính</li>
    <li>
      <a href="<?php echo $adminBase; ?>index.php" class="<?php echo $adminPage === 'dashboard' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Tổng quan
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>cars.php" class="<?php echo $adminPage === 'cars' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2"/><path d="M9 17h6"/></svg>
        Quản lý xe & hãng
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>news.php" class="<?php echo $adminPage === 'news' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 1 1-4 0V6"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8z"/></svg>
        Tin tức
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>orders.php" class="<?php echo $adminPage === 'orders' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Đơn hàng
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>order_details.php" class="<?php echo $adminPage === 'order_details' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        Chi tiết hóa đơn
      </a>
    </li>

    <li class="sidebar-section-label">Hệ thống</li>
    <li>
      <a href="<?php echo $adminBase; ?>accounts.php" class="<?php echo $adminPage === 'accounts' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Quản lý tài khoản
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>warranties.php" class="<?php echo $adminPage === 'warranties' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Bảo hành
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>reports.php" class="<?php echo $adminPage === 'reports' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Báo cáo & Thống kê
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>settings.php" class="<?php echo $adminPage === 'settings' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        Cài đặt
      </a>
    </li>
    <li>
      <a href="<?php echo $adminBase; ?>support.php" class="<?php echo $adminPage === 'support' ? 'active' : ''; ?>">
        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Hỗ trợ khách hàng
      </a>
    </li>
  </ul>

  <div class="sidebar-footer">
    <a href="<?php echo $rootBase; ?>index.php">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Về trang chủ
    </a>
  </div>
</aside>

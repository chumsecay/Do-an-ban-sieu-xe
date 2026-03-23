<?php
require_once __DIR__ . '/../bootstrap/env.php';
$adminPage = 'dashboard';
$pageTitle = 'Dashboard';
$pageSubtitle = 'Tổng quan hoạt động showroom';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Quản Trị - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../css/admin.css" rel="stylesheet">

<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body class="admin-body">

<?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

  <main class="admin-content">

    <!-- Stat Cards -->
    <div class="stat-cards">
      <div class="stat-card">
        <div class="stat-info">
          <h3>24</h3>
          <p>Xe trong kho</p>
          <span class="stat-change up">↑ 12% tháng này</span>
        </div>
        <div class="stat-icon blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2"/><path d="M9 17h6"/></svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <h3>156</h3>
          <p>Xe đã bán</p>
          <span class="stat-change up">↑ 8% tháng này</span>
        </div>
        <div class="stat-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <h3>₫12.5B</h3>
          <p>Doanh thu</p>
          <span class="stat-change up">↑ 23% tháng này</span>
        </div>
        <div class="stat-icon amber">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <h3>89</h3>
          <p>Khách hàng</p>
          <span class="stat-change down">↓ 3% tháng này</span>
        </div>
        <div class="stat-icon cyan">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
      </div>
    </div>

    <!-- Car Inventory Table -->
    <div class="panel">
      <div class="panel-header">
        <div style="display:flex;align-items:center;gap:10px">
          <h2>Xe trong kho</h2>
          <span class="badge-count">24 xe</span>
        </div>
        <a href="cars.php" class="btn-add">Xem tất cả →</a>
      </div>
      <table class="admin-table">
        <thead><tr><th>Xe</th><th>Hãng</th><th>Giá</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
        <tbody>
          <tr>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/bmwx5.jpg" alt="BMW X5"><div><strong>BMW X5 2024</strong><small>SUV · Tự động</small></div></div></td>
            <td>BMW</td><td><strong>$65,000</strong></td>
            <td><span class="badge-status badge-available">Còn hàng</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/mer amg suvs.jpg" alt="Mercedes"><div><strong>Mercedes AMG SUVs</strong><small>Sedan · Hybrid</small></div></div></td>
            <td>Mercedes</td><td><strong>$55,000</strong></td>
            <td><span class="badge-status badge-reserved">Đặt cọc</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/ford mustang.jpg" alt="Ford Mustang"><div><strong>Ford Mustang GT</strong><small>Sport · V8</small></div></div></td>
            <td>Ford</td><td><strong>$58,000</strong></td>
            <td><span class="badge-status badge-sold">Đã bán</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Recent Orders -->
    <div class="panel">
      <div class="panel-header">
        <div style="display:flex;align-items:center;gap:10px">
          <h2>Đơn hàng gần đây</h2>
          <span class="badge-count">5 đơn</span>
        </div>
        <a href="orders.php" class="btn-add">Xem tất cả →</a>
      </div>
      <table class="admin-table">
        <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Xe</th><th>Ngày</th><th>Trạng thái</th></tr></thead>
        <tbody>
          <tr><td><strong>#DH-001</strong></td><td>Nguyễn Văn An</td><td>BMW X5 2024</td><td>18/03/2026</td><td><span class="badge-status badge-confirmed">Đã xác nhận</span></td></tr>
          <tr><td><strong>#DH-002</strong></td><td>Trần Thị Bình</td><td>Mercedes AMG SUVs</td><td>17/03/2026</td><td><span class="badge-status badge-pending">Chờ duyệt</span></td></tr>
          <tr><td><strong>#DH-003</strong></td><td>Lê Hoàng Dũng</td><td>Ford Mustang GT</td><td>15/03/2026</td><td><span class="badge-status badge-confirmed">Đã xác nhận</span></td></tr>
        </tbody>
      </table>
    </div>

  </main>
</div>

<script>
function toggleSidebar() {
  document.getElementById('adminSidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
</body>
</html>

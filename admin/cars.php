<?php
require_once __DIR__ . '/../bootstrap/env.php';
$adminPage = 'cars';
$pageTitle = 'Quản lý xe';
$pageSubtitle = 'Thêm, sửa, xoá xe trong kho';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Quản Lý Xe - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../css/admin.css" rel="stylesheet">

<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body class="admin-body">
<?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

  <main class="admin-content">

    <!-- Quick Stats -->
    <div class="stat-cards">
      <div class="stat-card">
        <div class="stat-info"><h3>24</h3><p>Tổng xe</p></div>
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2"/><path d="M9 17h6"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>15</h3><p>Còn hàng</p></div>
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>6</h3><p>Đặt cọc</p></div>
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>3</h3><p>Đã bán</p></div>
        <div class="stat-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="panel" style="margin-bottom:0;border-radius:var(--radius) var(--radius) 0 0">
      <div class="panel-header" style="border-bottom:none;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <select class="filter-select" style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
            <option>Tất cả hãng</option><option>BMW</option><option>Mercedes</option><option>Ford</option><option>Lamborghini</option><option>Ferrari</option><option>Rolls Royce</option><option>Mazda</option><option>MG</option>
          </select>
          <select class="filter-select" style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
            <option>Tất cả trạng thái</option><option>Còn hàng</option><option>Đặt cọc</option><option>Đã bán</option>
          </select>
          <select class="filter-select" style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
            <option>Sắp xếp: Mới nhất</option><option>Giá: Thấp → Cao</option><option>Giá: Cao → Thấp</option><option>Tên A → Z</option>
          </select>
        </div>
        <button class="btn-add">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Thêm xe mới
        </button>
      </div>
    </div>

    <!-- Full car table -->
    <div class="panel" style="border-radius:0 0 var(--radius) var(--radius)">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox"></th>
            <th>Xe</th>
            <th>Hãng</th>
            <th>Loại</th>
            <th>Năm</th>
            <th>Giá</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/bmwx5.jpg" alt="BMW X5"><div><strong>BMW X5 2024</strong><small>Mã: XE-001</small></div></div></td>
            <td>BMW</td><td>SUV</td><td>2024</td>
            <td><strong>$65,000</strong></td>
            <td><span class="badge-status badge-available">Còn hàng</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/mer amg suvs.jpg" alt="Mercedes"><div><strong>Mercedes AMG SUVs</strong><small>Mã: XE-002</small></div></div></td>
            <td>Mercedes</td><td>Sedan</td><td>2024</td>
            <td><strong>$55,000</strong></td>
            <td><span class="badge-status badge-reserved">Đặt cọc</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/rollroyce phamtom viii.jpg" alt="Rolls Royce"><div><strong>Rolls Royce Phantom</strong><small>Mã: XE-003</small></div></div></td>
            <td>Rolls Royce</td><td>Luxury</td><td>2023</td>
            <td><strong>$320,000</strong></td>
            <td><span class="badge-status badge-available">Còn hàng</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/ford mustang.jpg" alt="Ford Mustang"><div><strong>Ford Mustang GT</strong><small>Mã: XE-004</small></div></div></td>
            <td>Ford</td><td>Sport</td><td>2024</td>
            <td><strong>$58,000</strong></td>
            <td><span class="badge-status badge-sold">Đã bán</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/Mazda MX-5.jpg" alt="Mazda MX-5"><div><strong>Mazda MX-5</strong><small>Mã: XE-005</small></div></div></td>
            <td>Mazda</td><td>Roadster</td><td>2024</td>
            <td><strong>$35,000</strong></td>
            <td><span class="badge-status badge-available">Còn hàng</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/lamborghini veneno roadster carbon.jpg" alt="Lamborghini"><div><strong>Lamborghini Veneno</strong><small>Mã: XE-006</small></div></div></td>
            <td>Lamborghini</td><td>Hypercar</td><td>2023</td>
            <td><strong>$3,500,000</strong></td>
            <td><span class="badge-status badge-available">Còn hàng</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/ferrari 488 pista spider.jpg" alt="Ferrari"><div><strong>Ferrari 488 Pista Spider</strong><small>Mã: XE-007</small></div></div></td>
            <td>Ferrari</td><td>Supercar</td><td>2023</td>
            <td><strong>$410,000</strong></td>
            <td><span class="badge-status badge-reserved">Đặt cọc</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/mg cyberster.jpg" alt="MG Cyberster"><div><strong>MG Cyberster</strong><small>Mã: XE-008</small></div></div></td>
            <td>MG</td><td>Electric</td><td>2025</td>
            <td><strong>$48,000</strong></td>
            <td><span class="badge-status badge-available">Còn hàng</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="car-name-cell"><img class="car-thumb" src="../img/bmw 430i converible.jpg" alt="BMW 430i"><div><strong>BMW 430i Convertible</strong><small>Mã: XE-009</small></div></div></td>
            <td>BMW</td><td>Convertible</td><td>2024</td>
            <td><strong>$62,000</strong></td>
            <td><span class="badge-status badge-reserved">Đặt cọc</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Xem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
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

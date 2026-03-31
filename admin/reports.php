<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
$adminPage = 'reports';
$pageTitle = 'Báo cáo & Thống kê';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle; ?> - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../css/admin.css" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body class="admin-body">

<?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

  <main class="admin-content">

    <div class="admin-header d-flex justify-between align-items-center mb-4">
      <h1 class="admin-title" style="margin:0;"><?php echo $pageTitle; ?></h1>
      <div style="display:flex; gap:10px;">
        <input type="date" class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155;">
        <span style="display:flex; align-items:center;">-</span>
        <input type="date" class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155;">
        <button class="btn-add" style="background:#10b981; color:white; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Xuất PDF/Excel
        </button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="stat-cards" style="margin-bottom:24px;">
      <div class="stat-card">
        <div class="stat-info"><h3>$1,250,000</h3><p>Tổng doanh thu kỳ này</p></div>
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>12</h3><p>Xe đã bán</p></div>
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2"/><path d="M9 17h6"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>45</h3><p>Khách hàng mới</p></div>
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
      </div>
    </div>

    <!-- 2 Column Layout -->
    <div style="display:flex; gap:24px; flex-wrap:wrap;">
      
      <!-- Top Xe -->
      <div class="panel" style="flex:1; min-width:300px;">
        <div class="panel-header">
          <h2 style="font-size:1rem; margin:0; font-weight:600;">Top Xe Bán Chạy</h2>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Dòng xe</th>
              <th>Số lượng</th>
              <th>Doanh thu</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><div style="display:flex; align-items:center; gap:8px;"><img src="../img/bmwx5.jpg" style="width:30px; height:20px; object-fit:cover; border-radius:4px;"> <strong>BMW X5</strong></div></td>
              <td>4</td>
              <td><strong style="color:var(--primary)">$260,000</strong></td>
            </tr>
            <tr>
              <td><div style="display:flex; align-items:center; gap:8px;"><img src="../img/ford mustang.jpg" style="width:30px; height:20px; object-fit:cover; border-radius:4px;"> <strong>Ford Mustang GT</strong></div></td>
              <td>3</td>
              <td><strong style="color:var(--primary)">$174,000</strong></td>
            </tr>
            <tr>
              <td><div style="display:flex; align-items:center; gap:8px;"><img src="../img/ferrari 488 pista spider.jpg" style="width:30px; height:20px; object-fit:cover; border-radius:4px;"> <strong>Ferrari 488</strong></div></td>
              <td>1</td>
              <td><strong style="color:var(--primary)">$410,000</strong></td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Hiệu suất nhân viên -->
      <div class="panel" style="flex:1; min-width:300px;">
        <div class="panel-header">
          <h2 style="font-size:1rem; margin:0; font-weight:600;">Hiệu Suất Nhân Viên</h2>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Nhân viên</th>
              <th>Doanh số</th>
              <th>Đánh giá</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><div style="display:flex; align-items:center; gap:8px;"><div style="width:24px; height:24px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:10px; color:#64748b">T</div> <strong>Trần Văn A</strong></div></td>
              <td><strong style="color:var(--primary)">$844,000</strong></td>
              <td><span class="badge-status badge-available">Xuất sắc</span></td>
            </tr>
            <tr>
              <td><div style="display:flex; align-items:center; gap:8px;"><div style="width:24px; height:24px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:10px; color:#64748b">H</div> <strong>Lê Minh H</strong></div></td>
              <td><strong style="color:var(--primary)">$260,000</strong></td>
              <td><span class="badge-status badge-reserved">Tốt</span></td>
            </tr>
            <tr>
              <td><div style="display:flex; align-items:center; gap:8px;"><div style="width:24px; height:24px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:10px; color:#64748b">K</div> <strong>Phạm K</strong></div></td>
              <td><strong style="color:var(--primary)">$146,000</strong></td>
              <td><span class="badge-status badge-reserved">Tốt</span></td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

</body>
</html>




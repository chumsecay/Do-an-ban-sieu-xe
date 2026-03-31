<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
$adminPage = 'warranties';
$pageTitle = 'Bảo hành';
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

    <!-- Quick Stats -->
    <div class="stat-cards">
      <div class="stat-card">
        <div class="stat-info"><h3>45</h3><p>Tổng phiếu</p></div>
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 3v5h5M16 13H8M16 17H8M10 9H8"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>38</h3><p>Còn hiệu lực</p></div>
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>7</h3><p>Đã hết hạn / Vô hiệu</p></div>
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg></div>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="panel" style="margin-bottom:0; border-radius:var(--radius) var(--radius) 0 0">
      <div class="panel-header" style="border-bottom:none; flex-wrap:wrap; gap:12px">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap">
          <input type="text" class="filter-select" placeholder="Tra cứu Mã BH, Khách hàng..." style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155; width:220px;">
          <select class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155">
            <option>Tất cả trạng thái</option>
            <option>Còn hiệu lực</option>
            <option>Sắp hết hạn</option>
            <option>Đã hết hạn</option>
          </select>
        </div>
        <button class="btn-add" style="background:var(--primary); color:white; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tạo phiếu bảo hành
        </button>
      </div>
    </div>
    
    <!-- Table -->
    <div class="panel" style="border-radius:0 0 var(--radius) var(--radius)">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox"></th>
            <th>Mã BH</th>
            <th>Khách hàng</th>
            <th>Chi tiết Xe</th>
            <th>Bắt đầu</th>
            <th>Kết thúc</th>
            <th>Trạng thái</th>
            <th style="text-align:center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>BH-00101</strong></td>
            <td>Lê Quỳnh Mai<br><small style="color:#64748b;">0988777666</small></td>
            <td>BMW X5 2024<br><small style="color:#64748b;">DH-001</small></td>
            <td>01/01/2024</td>
            <td>01/01/2027</td>
            <td><span class="badge-status badge-available">Còn hiệu lực</span></td>
            <td><div class="action-btns" style="justify-content:center;">
              <button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
              <button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
            </div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>BH-00050</strong></td>
            <td>Trần Gia Bảo<br><small style="color:#64748b;">0912345678</small></td>
            <td>Mercedes AMG<br><small style="color:#64748b;">DH-030</small></td>
            <td>15/05/2020</td>
            <td>15/05/2023</td>
            <td><span class="badge-status badge-sold">Đã hết hạn</span></td>
            <td><div class="action-btns" style="justify-content:center;">
              <button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
              <button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
            </div></td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>
</div>

</body>
</html>




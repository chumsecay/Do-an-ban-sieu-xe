<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
$adminPage = 'employees';
$pageTitle = 'Nhân viên';
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
        <div class="stat-info"><h3>12</h3><p>Tổng NV</p></div>
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>10</h3><p>Đang hoạt động</p></div>
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>2</h3><p>Tạm nghỉ / Đã nghỉ</p></div>
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="panel" style="margin-bottom:0; border-radius:var(--radius) var(--radius) 0 0">
      <div class="panel-header" style="border-bottom:none; flex-wrap:wrap; gap:12px">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap">
          <input type="text" class="filter-select" placeholder="Tra cứu Tên, SĐT, Email..." style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155; width:220px;">
          <select class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155">
            <option>Tất cả bộ phận</option>
            <option>Bán hàng</option>
            <option>Kỹ thuật</option>
            <option>Thu ngân</option>
          </select>
        </div>
        <button class="btn-add" style="background:var(--primary); color:white; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Thêm nhân viên
        </button>
      </div>
    </div>
    
    <!-- Table -->
    <div class="panel" style="border-radius:0 0 var(--radius) var(--radius)">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox"></th>
            <th>Mã NV</th>
            <th>Họ Tên</th>
            <th>Liên hệ</th>
            <th>Vị trí</th>
            <th>Trạng thái</th>
            <th style="text-align:center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>NV-001</strong></td>
            <td><div style="display:flex; align-items:center; gap:10px;">
              <div style="width:32px; height:32px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#64748b">T</div>
              <span>Trần Văn A</span>
            </div></td>
            <td>0901234567<br><small style="color:#64748b;">a.tran@flcar.vn</small></td>
            <td>Bán hàng</td>
            <td><span class="badge-status badge-available">Đang hoạt động</span></td>
            <td><div class="action-btns" style="justify-content:center;">
              <button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
              <button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
            </div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>NV-002</strong></td>
            <td><div style="display:flex; align-items:center; gap:10px;">
              <div style="width:32px; height:32px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#64748b">B</div>
              <span>Nguyễn Thị B</span>
            </div></td>
            <td>0907654321<br><small style="color:#64748b;">b.nguyen@flcar.vn</small></td>
            <td>Thu ngân</td>
            <td><span class="badge-status badge-sold">Đã nghỉ</span></td>
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




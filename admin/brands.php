<?php
require_once __DIR__ . '/../bootstrap/env.php';
$adminPage = 'brands';
$pageTitle = 'Quản lý hãng';
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
    </div>

    <!-- Filter bar -->
    <div class="panel" style="margin-bottom:0; border-radius:var(--radius) var(--radius) 0 0">
      <div class="panel-header" style="border-bottom:none; flex-wrap:wrap; gap:12px">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap">
          <input type="text" class="filter-select" placeholder="Tìm kiếm tên hãng..." style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155; width:250px;">
          <select class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155">
            <option value="">Tất cả trạng thái</option>
            <option value="1">Đang hoạt động</option>
            <option value="0">Tạm dừng</option>
          </select>
        </div>
        <button class="btn-add" style="background:var(--primary); color:white; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Thêm hãng mới
        </button>
      </div>
    </div>
    
    <!-- Table -->
    <div class="panel" style="border-radius:0 0 var(--radius) var(--radius)">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox"></th>
            <th>Tên hãng</th>
            <th>Mã (Slug)</th>
            <th>Quốc gia</th>
            <th>Trạng thái</th>
            <th style="text-align:center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>BMW</strong></td>
            <td>bmw</td>
            <td>Germany</td>
            <td><span class="badge-status badge-available">Đang hoạt động</span></td>
            <td><div class="action-btns" style="justify-content:center;">
              <button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
              <button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
            </div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>Mercedes-Benz</strong></td>
            <td>mercedes</td>
            <td>Germany</td>
            <td><span class="badge-status badge-available">Đang hoạt động</span></td>
            <td><div class="action-btns" style="justify-content:center;">
              <button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
              <button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
            </div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>Ford</strong></td>
            <td>ford</td>
            <td>USA</td>
            <td><span class="badge-status badge-sold">Tạm dừng</span></td>
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

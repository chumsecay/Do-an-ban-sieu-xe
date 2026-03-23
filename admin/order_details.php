<?php
require_once __DIR__ . '/../bootstrap/env.php';
$adminPage = 'order_details';
$pageTitle = 'Chi tiết hóa đơn';
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
          <input type="text" class="filter-select" placeholder="Tra cứu Mã Hóa Đơn..." style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155; width:220px;">
          <select class="filter-select" style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:.82rem; background:#f8fafc; color:#334155">
            <option>Tất cả xe</option>
            <option>BMW X5 2024</option>
            <option>Ford Mustang GT</option>
          </select>
        </div>
        <button class="btn-add" style="background:var(--primary); color:white; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px;">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Thêm dòng xe vào HĐ
        </button>
      </div>
    </div>
    
    <!-- Table -->
    <div class="panel" style="border-radius:0 0 var(--radius) var(--radius)">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox"></th>
            <th>Mã HĐ</th>
            <th>Chi tiết xe</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Giảm giá</th>
            <th>Thành tiền</th>
            <th style="text-align:center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>DH-001</strong></td>
            <td><div style="display:flex; align-items:center; gap:8px;"><img src="../img/bmwx5.jpg" style="width:40px; height:24px; object-fit:cover; border-radius:4px;"> <span>BMW X5 2024</span></div></td>
            <td>1</td>
            <td>$65,000.00</td>
            <td>$0.00</td>
            <td><strong style="color:var(--primary)">$65,000.00</strong></td>
            <td><div class="action-btns" style="justify-content:center;">
              <button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
              <button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
            </div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>DH-002</strong></td>
            <td><div style="display:flex; align-items:center; gap:8px;"><img src="../img/ford mustang.jpg" style="width:40px; height:24px; object-fit:cover; border-radius:4px;"> <span>Ford Mustang GT</span></div></td>
            <td>2</td>
            <td>$58,000.00</td>
            <td>$2,000.00</td>
            <td><strong style="color:var(--primary)">$114,000.00</strong></td>
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

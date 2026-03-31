<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
$adminPage = 'settings';
$pageTitle = 'Cài đặt';
$pageSubtitle = 'Cấu hình hệ thống showroom';
$appName = env('APP_NAME', 'FLCar');
$contactEmail = env('CONTACT_EMAIL', 'info@flcar.vn');
$contactPhone = env('CONTACT_PHONE', '0900 000 000');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Cài Đặt - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../css/admin.css" rel="stylesheet">
<style>
  .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
  .form-group { margin-bottom: 20px; }
  .form-group label { display: block; font-size: .82rem; font-weight: 600; color: #334155; margin-bottom: 6px; }
  .form-group input,
  .form-group textarea,
  .form-group select {
    width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px;
    font-size: .85rem; background: #f8fafc; color: #0f172a;
    transition: border-color .25s ease, box-shadow .25s ease;
  }
  .form-group input:focus,
  .form-group textarea:focus,
  .form-group select:focus {
    outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12);
  }
  .form-group textarea { resize: vertical; min-height: 100px; }
  .form-group .hint { font-size: .75rem; color: #94a3b8; margin-top: 4px; }
  .btn-save {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 24px; border: none; background: #2563eb; color: #fff;
    border-radius: 10px; font-size: .85rem; font-weight: 600; cursor: pointer;
    transition: background .25s ease, transform .25s ease, box-shadow .25s ease;
  }
  .btn-save:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,.3); }
  .btn-outline {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 24px; border: 1px solid #e2e8f0; background: #fff; color: #334155;
    border-radius: 10px; font-size: .85rem; font-weight: 600; cursor: pointer;
    transition: all .25s ease;
  }
  .btn-outline:hover { border-color: #2563eb; color: #2563eb; background: #eff6ff; }
  .btn-danger {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 24px; border: 1px solid #fecaca; background: #fff; color: #dc2626;
    border-radius: 10px; font-size: .85rem; font-weight: 600; cursor: pointer;
    transition: all .25s ease;
  }
  .btn-danger:hover { background: #fef2f2; border-color: #ef4444; }
  .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
  .toggle-switch input { opacity: 0; width: 0; height: 0; }
  .toggle-slider {
    position: absolute; cursor: pointer; inset: 0;
    background: #cbd5e1; border-radius: 24px; transition: .3s;
  }
  .toggle-slider::before {
    content: ''; position: absolute; height: 18px; width: 18px;
    left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: .3s;
  }
  .toggle-switch input:checked + .toggle-slider { background: #2563eb; }
  .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
  .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #f1f5f9; }
  .toggle-row:last-child { border-bottom: none; }
  .toggle-row .toggle-label strong { font-size: .85rem; font-weight: 600; }
  .toggle-row .toggle-label p { font-size: .75rem; color: #64748b; margin: 2px 0 0; }
  @media (max-width: 768px) { .settings-grid { grid-template-columns: 1fr; } }
</style>

<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body class="admin-body">
<?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

  <main class="admin-content">

    <div class="settings-grid">

      <!-- General Settings -->
      <div class="panel">
        <div class="panel-header"><h2>Thông tin chung</h2></div>
        <div style="padding:24px">
          <div class="form-group">
            <label>Tên showroom</label>
            <input type="text" value="<?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="form-group">
            <label>Tiêu đề trang chủ</label>
            <input type="text" value="Premium Cars Collection">
          </div>
          <div class="form-group">
            <label>Mô tả ngắn</label>
            <textarea>Đẳng cấp - Chất lượng - Giá tốt nhất thị trường</textarea>
          </div>
          <div class="form-group">
            <label>Logo</label>
            <div style="display:flex;align-items:center;gap:14px">
              <img src="../img/logo.png" style="height:48px;border-radius:8px;border:1px solid #e2e8f0" alt="Logo">
              <button class="btn-outline">Đổi logo</button>
            </div>
          </div>
          <button class="btn-save">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Lưu thay đổi
          </button>
        </div>
      </div>

      <!-- Contact Settings -->
      <div class="panel">
        <div class="panel-header"><h2>Thông tin liên hệ</h2></div>
        <div style="padding:24px">
          <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="form-group">
            <label>Hotline</label>
            <input type="tel" value="<?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="form-group">
            <label>Địa chỉ showroom</label>
            <input type="text" value="123 Đường ABC, TP.HCM">
          </div>
          <div class="form-group">
            <label>Google Maps embed URL</label>
            <input type="url" placeholder="https://maps.google.com/...">
            <p class="hint">Dán URL embed từ Google Maps để hiển thị bản đồ trên trang Liên Hệ</p>
          </div>
          <button class="btn-save">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Lưu thay đổi
          </button>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="panel">
        <div class="panel-header"><h2>Thông báo</h2></div>
        <div style="padding:20px 24px">
          <div class="toggle-row">
            <div class="toggle-label">
              <strong>Email đơn hàng mới</strong>
              <p>Nhận email khi có đơn hàng mới</p>
            </div>
            <label class="toggle-switch"><input type="checkbox" checked><span class="toggle-slider"></span></label>
          </div>
          <div class="toggle-row">
            <div class="toggle-label">
              <strong>Email khách hàng đăng ký</strong>
              <p>Nhận email khi có khách hàng mới</p>
            </div>
            <label class="toggle-switch"><input type="checkbox" checked><span class="toggle-slider"></span></label>
          </div>
          <div class="toggle-row">
            <div class="toggle-label">
              <strong>Email liên hệ từ form</strong>
              <p>Chuyển tiếp tin nhắn từ form liên hệ</p>
            </div>
            <label class="toggle-switch"><input type="checkbox" checked><span class="toggle-slider"></span></label>
          </div>
          <div class="toggle-row">
            <div class="toggle-label">
              <strong>Báo cáo tuần</strong>
              <p>Gửi tổng kết hoạt động hàng tuần</p>
            </div>
            <label class="toggle-switch"><input type="checkbox"><span class="toggle-slider"></span></label>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="panel">
        <div class="panel-header"><h2>Bảo trì hệ thống</h2></div>
        <div style="padding:24px">
          <div class="form-group">
            <label>Chế độ bảo trì</label>
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
              <label class="toggle-switch"><input type="checkbox"><span class="toggle-slider"></span></label>
              <span style="font-size:.82rem;color:#64748b">Bật chế độ bảo trì sẽ hiển thị trang thông báo cho khách truy cập</span>
            </div>
          </div>
          <div class="form-group">
            <label>Xoá cache</label>
            <button class="btn-outline">Xoá cache hệ thống</button>
            <p class="hint">Xoá các file tạm để cải thiện hiệu suất</p>
          </div>
          <div class="form-group" style="margin-top:24px;padding-top:20px;border-top:1px solid #fee2e2">
            <label style="color:#dc2626">Vùng nguy hiểm</label>
            <button class="btn-danger">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
              Reset toàn bộ dữ liệu
            </button>
            <p class="hint">Thao tác này không thể hoàn tác. Mọi dữ liệu xe, đơn hàng, khách hàng sẽ bị xoá.</p>
          </div>
        </div>
      </div>

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




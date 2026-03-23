<?php
require_once __DIR__ . '/../bootstrap/env.php';
$adminPage = 'orders';
$pageTitle = 'Đơn hàng';
$pageSubtitle = 'Quản lý đơn hàng và giao dịch';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Đơn Hàng - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
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
        <div class="stat-info"><h3>42</h3><p>Tổng đơn</p><span class="stat-change up">↑ 15% tháng này</span></div>
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>28</h3><p>Đã xác nhận</p></div>
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>9</h3><p>Chờ duyệt</p></div>
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>5</h3><p>Đã huỷ</p></div>
        <div class="stat-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
      </div>
    </div>

    <!-- Filter -->
    <div class="panel" style="margin-bottom:0;border-radius:var(--radius) var(--radius) 0 0">
      <div class="panel-header" style="border-bottom:none;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <select style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
            <option>Tất cả trạng thái</option><option>Đã xác nhận</option><option>Chờ duyệt</option><option>Đã huỷ</option>
          </select>
          <select style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
            <option>Sắp xếp: Mới nhất</option><option>Cũ nhất</option><option>Giá trị: Cao → Thấp</option>
          </select>
          <input type="date" style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
        </div>
        <button class="btn-add">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tạo đơn mới
        </button>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="panel" style="border-radius:0 0 var(--radius) var(--radius)">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox"></th>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Xe</th>
            <th>Giá trị</th>
            <th>Ngày tạo</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>#DH-001</strong></td>
            <td><div><strong>Nguyễn Văn An</strong><br><small style="color:#64748b">an.nguyen@email.com</small></div></td>
            <td>BMW X5 2024</td>
            <td><strong>$65,000</strong></td>
            <td>18/03/2026</td>
            <td><span class="badge-status badge-confirmed">Đã xác nhận</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>#DH-002</strong></td>
            <td><div><strong>Trần Thị Bình</strong><br><small style="color:#64748b">binh.tran@email.com</small></div></td>
            <td>Mercedes AMG SUVs</td>
            <td><strong>$55,000</strong></td>
            <td>17/03/2026</td>
            <td><span class="badge-status badge-pending">Chờ duyệt</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>#DH-003</strong></td>
            <td><div><strong>Lê Hoàng Dũng</strong><br><small style="color:#64748b">dung.le@email.com</small></div></td>
            <td>Ford Mustang GT</td>
            <td><strong>$58,000</strong></td>
            <td>15/03/2026</td>
            <td><span class="badge-status badge-confirmed">Đã xác nhận</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>#DH-004</strong></td>
            <td><div><strong>Phạm Minh Châu</strong><br><small style="color:#64748b">chau.pham@email.com</small></div></td>
            <td>Ferrari 488 Pista</td>
            <td><strong>$410,000</strong></td>
            <td>14/03/2026</td>
            <td><span class="badge-status badge-pending">Chờ duyệt</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>#DH-005</strong></td>
            <td><div><strong>Hoàng Đức Thịnh</strong><br><small style="color:#64748b">thinh.hoang@email.com</small></div></td>
            <td>Mazda MX-5</td>
            <td><strong>$35,000</strong></td>
            <td>12/03/2026</td>
            <td><span class="badge-status badge-cancelled">Đã huỷ</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>#DH-006</strong></td>
            <td><div><strong>Võ Thanh Hải</strong><br><small style="color:#64748b">hai.vo@email.com</small></div></td>
            <td>Rolls Royce Phantom</td>
            <td><strong>$320,000</strong></td>
            <td>10/03/2026</td>
            <td><span class="badge-status badge-confirmed">Đã xác nhận</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><strong>#DH-007</strong></td>
            <td><div><strong>Đặng Quốc Bảo</strong><br><small style="color:#64748b">bao.dang@email.com</small></div></td>
            <td>Lamborghini Veneno</td>
            <td><strong>$3,500,000</strong></td>
            <td>08/03/2026</td>
            <td><span class="badge-status badge-pending">Chờ duyệt</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
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

<?php
require_once __DIR__ . '/../bootstrap/env.php';
$adminPage = 'customers';
$pageTitle = 'Khách hàng';
$pageSubtitle = 'Quản lý thông tin khách hàng';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Khách Hàng - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../css/admin.css" rel="stylesheet">
<style>
  .customer-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem; color: #fff; flex-shrink: 0;
  }
  .customer-cell { display: flex; align-items: center; gap: 12px; }
  .customer-cell strong { font-weight: 600; }
  .customer-cell small { display: block; color: #64748b; font-size: .75rem; }
  .tag { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: .72rem; font-weight: 600; }
  .tag-vip { background: #fef3c7; color: #92400e; }
  .tag-new { background: #dbeafe; color: #1e40af; }
  .tag-regular { background: #f1f5f9; color: #475569; }
</style>

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
        <div class="stat-info"><h3>89</h3><p>Tổng khách hàng</p><span class="stat-change up">↑ 5% tháng này</span></div>
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>12</h3><p>VIP</p></div>
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>23</h3><p>Khách mới (tháng)</p></div>
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-info"><h3>₫2.1B</h3><p>Tổng chi tiêu</p></div>
        <div class="stat-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
      </div>
    </div>

    <!-- Filter -->
    <div class="panel" style="margin-bottom:0;border-radius:var(--radius) var(--radius) 0 0">
      <div class="panel-header" style="border-bottom:none;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <select style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
            <option>Tất cả phân loại</option><option>VIP</option><option>Khách mới</option><option>Thường</option>
          </select>
          <select style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:.82rem;background:#f8fafc;color:#334155">
            <option>Sắp xếp: Mới nhất</option><option>Tên A → Z</option><option>Chi tiêu: Cao → Thấp</option>
          </select>
        </div>
        <button class="btn-add">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Thêm khách hàng
        </button>
      </div>
    </div>

    <!-- Customers Table -->
    <div class="panel" style="border-radius:0 0 var(--radius) var(--radius)">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox"></th>
            <th>Khách hàng</th>
            <th>Điện thoại</th>
            <th>Xe đã mua</th>
            <th>Tổng chi tiêu</th>
            <th>Phân loại</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="customer-cell"><div class="customer-avatar" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">NA</div><div><strong>Nguyễn Văn An</strong><small>an.nguyen@email.com</small></div></div></td>
            <td>0901 234 567</td>
            <td>3</td>
            <td><strong>$188,000</strong></td>
            <td><span class="tag tag-vip">⭐ VIP</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="customer-cell"><div class="customer-avatar" style="background:linear-gradient(135deg,#ec4899,#db2777)">TB</div><div><strong>Trần Thị Bình</strong><small>binh.tran@email.com</small></div></div></td>
            <td>0912 345 678</td>
            <td>1</td>
            <td><strong>$55,000</strong></td>
            <td><span class="tag tag-regular">Thường</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="customer-cell"><div class="customer-avatar" style="background:linear-gradient(135deg,#22c55e,#16a34a)">LD</div><div><strong>Lê Hoàng Dũng</strong><small>dung.le@email.com</small></div></div></td>
            <td>0923 456 789</td>
            <td>2</td>
            <td><strong>$123,000</strong></td>
            <td><span class="tag tag-vip">⭐ VIP</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="customer-cell"><div class="customer-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706)">MC</div><div><strong>Phạm Minh Châu</strong><small>chau.pham@email.com</small></div></div></td>
            <td>0934 567 890</td>
            <td>1</td>
            <td><strong>$410,000</strong></td>
            <td><span class="tag tag-vip">⭐ VIP</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="customer-cell"><div class="customer-avatar" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">DT</div><div><strong>Hoàng Đức Thịnh</strong><small>thinh.hoang@email.com</small></div></div></td>
            <td>0945 678 901</td>
            <td>0</td>
            <td><strong>$0</strong></td>
            <td><span class="tag tag-new">🆕 Mới</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="customer-cell"><div class="customer-avatar" style="background:linear-gradient(135deg,#06b6d4,#0891b2)">VH</div><div><strong>Võ Thanh Hải</strong><small>hai.vo@email.com</small></div></div></td>
            <td>0956 789 012</td>
            <td>1</td>
            <td><strong>$320,000</strong></td>
            <td><span class="tag tag-vip">⭐ VIP</span></td>
            <td><div class="action-btns"><button class="action-btn" title="Chi tiết"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="action-btn" title="Sửa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="action-btn delete" title="Xoá"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button></div></td>
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

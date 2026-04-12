<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'dashboard';
$appName = env('APP_NAME', 'FLCar');

$pdo = getDBConnection();
try {
    $totalCars = (int) $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
    $totalSold = (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'sold'")->fetchColumn();
    $totalCustomers = (int) $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $totalRevenue = (float) $pdo->query("SELECT SUM(price) FROM cars WHERE status = 'sold'")->fetchColumn();
    $recentActivity = $pdo->query("SELECT c.*, b.name as brand_name FROM cars c LEFT JOIN brands b ON c.brand_id = b.id ORDER BY c.id DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $totalCars = 0; $totalSold = 0; $totalCustomers = 0; $totalRevenue = 0; $recentActivity = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tổng Quan - <?php echo htmlspecialchars($appName); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<!-- Gắn lại chính xác font, bootstrap, và CSS gốc -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }
  .stat-card { background: #fff; padding: 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
  .stat-label { color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
  .stat-value { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1; }
  .stat-icon { width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
  .table > :not(caption) > * > * { padding: 16px 12px; border-bottom-color: #f1f5f9; }
</style>
</head>
<body>

<div class="admin-wrapper" id="adminWrapper">
  <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>
  
  <div class="admin-main">
    <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

    <main class="admin-content p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold text-dark mb-0">Dashboard Toàn Cảnh</h2>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
          <div class="stat-card">
            <div>
              <span class="stat-label">Tổng Lượng Xe</span>
              <div class="stat-value"><?php echo number_format($totalCars); ?></div>
            </div>
            <div class="stat-icon" style="background:#e0f2fe; color:#0284c7;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-sm-6">
          <div class="stat-card">
            <div>
              <span class="stat-label">Xe Đã Bán</span>
              <div class="stat-value"><?php echo number_format($totalSold); ?></div>
            </div>
            <div class="stat-icon" style="background:#fef3c7; color:#d97706;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="stat-card">
            <div>
              <span class="stat-label">Doanh thu dự tính</span>
              <div class="stat-value">$<?php echo number_format($totalRevenue); ?></div>
            </div>
            <div class="stat-icon" style="background:#ede9fe; color:#7c3aed;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="stat-card">
            <div>
              <span class="stat-label">Khách hàng VIP</span>
              <div class="stat-value"><?php echo number_format($totalCustomers); ?></div>
            </div>
            <div class="stat-icon" style="background:#fce7f3; color:#be185d;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4 text-dark">Danh Sách Xe Nhập Khẩu Gần Đây</h5>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <th>Mã Xe</th>
                  <th>Tên Siêu Xe</th>
                  <th>Thương Hiệu</th>
                  <th>Trạng Thái</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($recentActivity) === 0): ?>
                  <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có kết quả</td></tr>
                <?php else: ?>
                  <?php foreach($recentActivity as $r): ?>
                  <tr>
                    <td class="fw-bold text-secondary"><?php echo htmlspecialchars($r['code']); ?></td>
                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($r['name']); ?></td>
                    <td class="text-secondary fw-medium"><?php echo htmlspecialchars($r['brand_name'] ?? 'N/A'); ?></td>
                    <td>
                      <?php if($r['status'] === 'sold'): ?>
                        <span class="badge bg-light text-secondary px-3 py-2 rounded-pill">Đã Bán</span>
                      <?php elseif($r['status'] === 'reserved'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill bg-opacity-25">Đặt Cọc</span>
                      <?php else: ?>
                        <span class="badge bg-success text-success px-3 py-2 rounded-pill bg-opacity-25">Sẵn Hàng</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Simple sidebar toggle logic
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('adminWrapper');
    if(toggle) toggle.addEventListener('click', () => wrapper.classList.toggle('sidebar-collapsed'));
  });
</script>
</body>
</html>




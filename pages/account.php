<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../config/database.php';

ensureSessionStarted();

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentPage = 'account';
$appName = env('APP_NAME', 'FLCar');
$userName = (string)($_SESSION['user_name'] ?? 'Tài khoản');
$userEmail = (string)($_SESSION['user_email'] ?? 'Chưa có email');
$userRole = (string)($_SESSION['user_role'] ?? 'user');
$provider = (string)($_SESSION['auth_provider'] ?? 'password');
$userBalance = (float)($_SESSION['user_balance'] ?? 0);
$msg = (string)($_GET['msg'] ?? '');

try {
    $pdo = getDBConnection();
    $customerId = (int)($_SESSION['customer_id'] ?? 0);
    if ($customerId > 0) {
        $stmt = $pdo->prepare('SELECT id, balance FROM customers WHERE id = ? LIMIT 1');
        $stmt->execute([$customerId]);
        $row = $stmt->fetch();
        if ($row) {
            $userBalance = (float)($row['balance'] ?? 0);
            $_SESSION['user_balance'] = $userBalance;
        }
    } elseif ($userEmail !== '' && $userRole === 'user') {
        $stmt = $pdo->prepare('SELECT id, balance FROM customers WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([$userEmail]);
        $row = $stmt->fetch();
        if ($row) {
            $_SESSION['customer_id'] = (int)$row['id'];
            $userBalance = (float)($row['balance'] ?? 0);
            $_SESSION['user_balance'] = $userBalance;
        }
    }
} catch (Throwable $ignored) {
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tài Khoản - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <h1 class="display-5 fw-bold">Tài Khoản Của Tôi</h1>
    <p class="lead mb-0" style="opacity:.85">Quản lý thông tin tài khoản và tùy chọn mua sắm</p>
  </div>
</section>

<section style="padding:70px 0; background:#f8fafc;">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
          <div class="card-body p-4 p-lg-5">
            <h3 class="h4 fw-bold mb-4">Thông Tin Cơ Bản</h3>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-white">
                  <div class="small text-muted">Họ tên</div>
                  <div class="fw-semibold"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-white">
                  <div class="small text-muted">Email</div>
                  <div class="fw-semibold"><?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-white">
                  <div class="small text-muted">Vai trò</div>
                  <div class="fw-semibold text-capitalize"><?php echo htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-white">
                  <div class="small text-muted">Đăng nhập bằng</div>
                  <div class="fw-semibold text-capitalize"><?php echo htmlspecialchars($provider, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-white">
                  <div class="small text-muted">Số dư tài khoản (mua online)</div>
                  <div class="fw-semibold">$<?php echo number_format($userBalance, 2); ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
          <div class="card-body p-4 p-lg-5">
            <h3 class="h4 fw-bold mb-2">Nạp Tiền Tài Khoản</h3>
            <p class="text-muted mb-4">Giao diện nạp tiền đã sẵn sàng. Bạn nạp online sẽ được kích hoạt ở bước tiếp theo.</p>
            <?php if ($msg === 'topup_ui_ready'): ?>
              <div class="alert alert-info border-0 rounded-3">Đã mở giao diện nạp tiền (demo). Chức năng xử lý thanh toán sẽ được bật sau.</div>
            <?php endif; ?>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary">Số tiền nạp</label>
                <select class="form-select bg-light border-0">
                  <option>$500</option>
                  <option>$1,000</option>
                  <option>$2,000</option>
                  <option>$5,000</option>
                  <option>Tùy chỉnh...</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary">Phương thức</label>
                <select class="form-select bg-light border-0">
                  <option>Bank Transfer</option>
                  <option>Visa / MasterCard</option>
                  <option>Momo / ZaloPay</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary">Trạng thái</label>
                <input type="text" class="form-control bg-light border-0" value="Demo UI - chưa xử lý thanh toán" disabled>
              </div>
              <div class="col-12 d-flex gap-2">
                <a href="account.php?msg=topup_ui_ready" class="btn btn-outline-primary">Thử giao diện nạp tiền</a>
                <button type="button" class="btn btn-primary" disabled>Nạp tiền (sắp hoạt động)</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
          <div class="card-body p-4">
            <h4 class="h5 fw-bold mb-3">Truy Cập Nhanh</h4>
            <a href="orders.php" class="btn btn-outline-dark w-100 mb-2">Đơn mua</a>
            <a href="cart.php" class="btn btn-outline-dark w-100 mb-2">Giỏ hàng</a>
            <?php if (isAdminLoggedIn()): ?>
              <a href="../admin/index.php" class="btn btn-success w-100 mb-2">Quản trị</a>
            <?php endif; ?>
            <a href="../logout.php" class="btn btn-danger w-100">Đăng xuất</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

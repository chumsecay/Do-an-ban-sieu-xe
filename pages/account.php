<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';

ensureSessionStarted();

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentPage = 'account';
$appName = env('APP_NAME', 'FLCar');
$userName = (string)($_SESSION['user_name'] ?? 'Tai khoan');
$userEmail = (string)($_SESSION['user_email'] ?? 'Chua co email');
$userRole = (string)($_SESSION['user_role'] ?? 'user');
$provider = (string)($_SESSION['auth_provider'] ?? 'password');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tai Khoan - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <h1 class="display-5 fw-bold">Tai Khoan Cua Toi</h1>
    <p class="lead mb-0" style="opacity:.85">Quan ly thong tin tai khoan va tuy chon mua sam</p>
  </div>
</section>

<section style="padding:70px 0; background:#f8fafc;">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
          <div class="card-body p-4 p-lg-5">
            <h3 class="h4 fw-bold mb-4">Thong Tin Co Ban</h3>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-white">
                  <div class="small text-muted">Ho ten</div>
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
                  <div class="small text-muted">Vai tro</div>
                  <div class="fw-semibold text-capitalize"><?php echo htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-white">
                  <div class="small text-muted">Dang nhap bang</div>
                  <div class="fw-semibold text-capitalize"><?php echo htmlspecialchars($provider, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
          <div class="card-body p-4">
            <h4 class="h5 fw-bold mb-3">Truy Cap Nhanh</h4>
            <a href="orders.php" class="btn btn-outline-dark w-100 mb-2">Don mua</a>
            <a href="cart.php" class="btn btn-outline-dark w-100 mb-2">Gio hang</a>
            <?php if (isAdminLoggedIn()): ?>
              <a href="../admin/index.php" class="btn btn-success w-100 mb-2">Quan tri</a>
            <?php endif; ?>
            <a href="../logout.php" class="btn btn-danger w-100">Dang xuat</a>
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

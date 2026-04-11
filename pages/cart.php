<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';

ensureSessionStarted();

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentPage = 'cart';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gio Hang - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <h1 class="display-5 fw-bold">Gio Hang</h1>
    <p class="lead mb-0" style="opacity:.85">Danh sach xe ban dang quan tam</p>
  </div>
</section>

<section style="padding:70px 0; background:#f8fafc;">
  <div class="container">
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="card-body p-5 text-center">
        <h3 class="h4 fw-bold mb-3">Gio hang dang trong</h3>
        <p class="text-muted mb-4">Ban co the them xe tu trang Showroom vao gio de theo doi va so sanh.</p>
        <a href="showroom.php" class="btn btn-primary px-4">Di den Showroom</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

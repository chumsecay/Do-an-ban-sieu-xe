<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap/env.php';
require_once __DIR__ . '/bootstrap/auth.php';
require_once __DIR__ . '/config/database.php';

ensureSessionStarted();

function ensureCustomerSchema(PDO $pdo): void
{
    $checkBalance = $pdo->query("SHOW COLUMNS FROM customers LIKE 'balance'");
    if (!$checkBalance->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER tier");
    }

    $checkPassword = $pdo->query("SHOW COLUMNS FROM customers LIKE 'password_hash'");
    if (!$checkPassword->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255) NULL AFTER email");
    }

    $checkActive = $pdo->query("SHOW COLUMNS FROM customers LIKE 'is_active'");
    if (!$checkActive->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER balance");
    }

    $checkLastLogin = $pdo->query("SHOW COLUMNS FROM customers LIKE 'last_login_at'");
    if (!$checkLastLogin->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN last_login_at DATETIME NULL AFTER is_active");
    }
}

function findCustomerByEmail(PDO $pdo, string $email): ?array
{
    ensureCustomerSchema($pdo);

    $stmt = $pdo->prepare('SELECT id, full_name, email, phone, balance, password_hash, is_active FROM customers WHERE LOWER(email) = LOWER(?) LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function loginCustomer(PDO $pdo, array $customer): void
{
    loginAsUser([
        'id' => (int)$customer['id'],
        'full_name' => (string)$customer['full_name'],
        'email' => (string)$customer['email'],
        'balance' => (float)($customer['balance'] ?? 0),
    ], null, 'password');

    try {
        $update = $pdo->prepare('UPDATE customers SET last_login_at = NOW() WHERE id = ?');
        $update->execute([(int)$customer['id']]);
    } catch (Throwable $ignored) {
    }
}

$appName = env('APP_NAME', 'FLCar');
$error = '';

$submittedName = '';
$submittedEmail = '';
$submittedPhone = '';

if (isUserLoggedIn()) {
    if (isAdminLoggedIn()) {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedName = trim((string)($_POST['full_name'] ?? ''));
    $submittedEmail = strtolower(trim((string)($_POST['email'] ?? '')));
    $submittedPhone = trim((string)($_POST['phone'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    if ($submittedName === '' || $submittedEmail === '' || $password === '') {
        $error = 'Vui long nhap day du thong tin bat buoc.';
    } elseif (!filter_var($submittedEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email khong hop le.';
    } elseif (strlen($password) < 6) {
        $error = 'Mat khau can toi thieu 6 ky tu.';
    } elseif (!hash_equals($password, $passwordConfirm)) {
        $error = 'Xac nhan mat khau khong khop.';
    } else {
        try {
            $pdo = getDBConnection();
            ensureCustomerSchema($pdo);

            $existing = findCustomerByEmail($pdo, $submittedEmail);
            if ($existing) {
                if ((int)($existing['is_active'] ?? 1) !== 1) {
                    $error = 'Tai khoan dang bi khoa.';
                } elseif (trim((string)($existing['password_hash'] ?? '')) !== '') {
                    $error = 'Email nay da duoc dang ky. Vui long dang nhap.';
                } else {
                    $update = $pdo->prepare('UPDATE customers SET full_name = ?, phone = ?, password_hash = ? WHERE id = ?');
                    $update->execute([
                        $submittedName,
                        $submittedPhone !== '' ? $submittedPhone : null,
                        password_hash($password, PASSWORD_DEFAULT),
                        (int)$existing['id'],
                    ]);

                    $customer = findCustomerByEmail($pdo, $submittedEmail);
                    if ($customer) {
                        loginCustomer($pdo, $customer);
                        header('Location: index.php?register=success');
                        exit;
                    }

                    $error = 'Khong the tao tai khoan. Vui long thu lai.';
                }
            } else {
                $insert = $pdo->prepare('INSERT INTO customers (full_name, email, phone, tier, balance, password_hash, is_active) VALUES (?, ?, ?, ?, 0, ?, 1)');
                $insert->execute([
                    $submittedName,
                    $submittedEmail,
                    $submittedPhone !== '' ? $submittedPhone : null,
                    'new',
                    password_hash($password, PASSWORD_DEFAULT),
                ]);

                $customer = [
                    'id' => (int)$pdo->lastInsertId(),
                    'full_name' => $submittedName,
                    'email' => $submittedEmail,
                    'balance' => 0,
                ];
                loginCustomer($pdo, $customer);
                header('Location: index.php?register=success');
                exit;
            }
        } catch (Throwable $e) {
            $error = 'Khong the ket noi CSDL de dang ky. Vui long thu lai.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dang Ky - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" href="img/logo.png" type="image/png">
<style>
  body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    padding: 24px 12px;
  }
  .register-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 36px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
  .logo {
    height: 54px;
    margin-bottom: 20px;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
  }
  .form-control {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    padding: 13px 16px;
    border-radius: 12px;
  }
  .form-control:focus {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255,255,255,0.3);
    color: #fff;
    box-shadow: none;
  }
  .form-control::placeholder { color: rgba(255, 255, 255, 0.42); }
  .btn-main {
    background: linear-gradient(to right, #3b82f6, #6366f1);
    color: #fff;
    border: none;
    padding: 13px;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  .btn-main:hover { color:#fff; filter: brightness(1.05); }
</style>
</head>
<body>
<div class="container">
  <div class="d-flex justify-content-center">
    <div class="register-card text-center">
      <img src="img/logo.png" alt="FLCar" class="logo">
      <h3 class="fw-bold mb-1">Dang Ky Tai Khoan</h3>
      <p class="text-white-50 mb-4" style="font-size:0.9rem;">Tao tai khoan khach hang de dat hang tren website.</p>

      <?php if ($error !== ''): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; font-size: 0.9rem; padding: 10px; border-radius: 8px;">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" novalidate>
        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Ho ten</label>
          <input type="text" name="full_name" class="form-control" placeholder="Nhap ho ten"
                 value="<?php echo htmlspecialchars($submittedName, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Nhap email"
                 value="<?php echo htmlspecialchars($submittedEmail, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">So dien thoai (tuy chon)</label>
          <input type="text" name="phone" class="form-control" placeholder="Nhap so dien thoai"
                 value="<?php echo htmlspecialchars($submittedPhone, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Mat khau</label>
          <input type="password" name="password" class="form-control" placeholder="Toi thieu 6 ky tu" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Xac nhan mat khau</label>
          <input type="password" name="password_confirm" class="form-control" placeholder="Nhap lai mat khau" required>
        </div>

        <button type="submit" class="btn btn-main w-100">Dang Ky</button>
      </form>

      <div class="text-white-50 mt-4" style="font-size: 0.86rem;">
        Da co tai khoan? <a href="login.php" class="text-white text-decoration-none fw-semibold">Dang nhap</a>
      </div>

      <div class="text-white-50 mt-3" style="font-size:0.82rem;">
        <a href="index.php" class="text-white-50 text-decoration-none">&larr; Ve trang chu</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
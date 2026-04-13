<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap/env.php';
require_once __DIR__ . '/bootstrap/auth.php';
require_once __DIR__ . '/config/database.php';

ensureSessionStarted();

if ((string)($_GET['tab'] ?? '') === 'register') {
    header('Location: register.php');
    exit;
}

function fetchJsonFromUrl(string $url): ?array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($response) || $response === '' || $httpCode >= 400) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    $context = stream_context_create([
        'http' => ['timeout' => 8],
    ]);

    $response = @file_get_contents($url, false, $context);
    if (!is_string($response) || $response === '') {
        return null;
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}

function verifyGoogleIdToken(string $idToken, string $expectedClientId): ?array
{
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($idToken);
    $tokenInfo = fetchJsonFromUrl($url);

    if (!is_array($tokenInfo)) {
        return null;
    }

    $aud = (string)($tokenInfo['aud'] ?? '');
    $email = strtolower(trim((string)($tokenInfo['email'] ?? '')));
    $emailVerified = (string)($tokenInfo['email_verified'] ?? 'false');

    if ($aud !== $expectedClientId) {
        return null;
    }
    if ($email === '' || $emailVerified !== 'true') {
        return null;
    }

    $name = trim((string)($tokenInfo['name'] ?? 'Google User'));

    return [
        'email' => $email,
        'name' => $name,
    ];
}

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

function findOrCreateCustomerByEmail(PDO $pdo, string $email, string $fullName): array
{
    $existing = findCustomerByEmail($pdo, $email);
    if ($existing) {
        return $existing;
    }

    $insert = $pdo->prepare('INSERT INTO customers (full_name, email, tier, balance, is_active) VALUES (?, ?, ?, 0, 1)');
    $insert->execute([$fullName, $email, 'new']);

    return [
        'id' => (int)$pdo->lastInsertId(),
        'full_name' => $fullName,
        'email' => $email,
        'phone' => null,
        'balance' => 0,
        'password_hash' => null,
        'is_active' => 1,
    ];
}

function loginCustomer(PDO $pdo, array $customer, string $provider = 'password'): void
{
    loginAsUser([
        'id' => (int)$customer['id'],
        'full_name' => (string)$customer['full_name'],
        'email' => (string)$customer['email'],
        'balance' => (float)($customer['balance'] ?? 0),
    ], null, $provider);

    try {
        $update = $pdo->prepare('UPDATE customers SET last_login_at = NOW() WHERE id = ?');
        $update->execute([(int)$customer['id']]);
    } catch (Throwable $ignored) {
    }
}

function passwordMatchedWithLegacy(string $inputPassword, string $storedPassword, bool &$needsUpgrade): bool
{
    $needsUpgrade = false;

    if ($storedPassword === '') {
        return false;
    }

    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    if ((password_get_info($storedPassword)['algo'] ?? 0) === 0 && hash_equals($storedPassword, $inputPassword)) {
        $needsUpgrade = true;
        return true;
    }

    return false;
}

$appName = env('APP_NAME', 'FLCar');
$googleClientId = trim((string)env('GOOGLE_CLIENT_ID', ''));
$error = '';
$submittedLoginId = '';

if (isUserLoggedIn()) {
    if (isAdminLoggedIn()) {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? 'password_login'));

    try {
        $pdo = getDBConnection();
        ensureCustomerSchema($pdo);

        if ($action === 'google_login') {
            $credential = trim((string)($_POST['credential'] ?? ''));

            if ($googleClientId === '') {
                $error = 'Google login chua duoc cau hinh. Them GOOGLE_CLIENT_ID trong file .env.';
            } elseif ($credential === '') {
                $error = 'Khong nhan duoc token dang nhap Google.';
            } else {
                $profile = verifyGoogleIdToken($credential, $googleClientId);
                if (!is_array($profile)) {
                    $error = 'Xac thuc Google that bai. Vui long thu lai.';
                } else {
                    $adminStmt = $pdo->prepare(
                        'SELECT id, username, email, password_hash, full_name, role, is_active
                         FROM admins
                         WHERE LOWER(email) = LOWER(?)
                         LIMIT 1'
                    );
                    $adminStmt->execute([(string)$profile['email']]);
                    $admin = $adminStmt->fetch();

                    if ($admin) {
                        if ((int)$admin['is_active'] !== 1) {
                            $error = 'Tai khoan dang bi khoa.';
                        } else {
                            loginAsAdmin($admin);
                            try {
                                $update = $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?');
                                $update->execute([(int)$admin['id']]);
                            } catch (Throwable $ignored) {
                            }

                            header('Location: admin/index.php?login=success');
                            exit;
                        }
                    } else {
                        $customer = findOrCreateCustomerByEmail($pdo, (string)$profile['email'], (string)$profile['name']);
                        if ((int)($customer['is_active'] ?? 1) !== 1) {
                            $error = 'Tai khoan dang bi khoa.';
                        } else {
                            loginCustomer($pdo, $customer, 'google');
                            header('Location: index.php?login=success');
                            exit;
                        }
                    }
                }
            }
        } elseif ($action === 'password_login') {
            $submittedLoginId = trim((string)($_POST['login_id'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if ($submittedLoginId === '' || $password === '') {
                $error = 'Vui long nhap day du tai khoan va mat khau.';
            } else {
                $adminStmt = $pdo->prepare(
                    'SELECT id, username, email, password_hash, full_name, role, is_active
                     FROM admins
                     WHERE username = ? OR LOWER(email) = LOWER(?)
                     LIMIT 1'
                );
                $adminStmt->execute([$submittedLoginId, $submittedLoginId]);
                $admin = $adminStmt->fetch();

                $adminPasswordMatched = false;
                if ($admin) {
                    $adminStored = (string)($admin['password_hash'] ?? '');
                    $adminNeedsUpgrade = false;
                    $adminPasswordMatched = passwordMatchedWithLegacy($password, $adminStored, $adminNeedsUpgrade);

                    if ($adminPasswordMatched) {
                        if ((int)$admin['is_active'] !== 1) {
                            $error = 'Tai khoan dang bi khoa.';
                        } else {
                            if ($adminNeedsUpgrade || password_needs_rehash($adminStored, PASSWORD_DEFAULT)) {
                                try {
                                    $rehash = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
                                    $rehash->execute([password_hash($password, PASSWORD_DEFAULT), (int)$admin['id']]);
                                } catch (Throwable $ignored) {
                                }
                            }

                            loginAsAdmin($admin);
                            try {
                                $update = $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?');
                                $update->execute([(int)$admin['id']]);
                            } catch (Throwable $ignored) {
                            }

                            header('Location: admin/index.php?login=success');
                            exit;
                        }
                    }
                }

                if ($error === '' && filter_var($submittedLoginId, FILTER_VALIDATE_EMAIL)) {
                    $customerEmail = strtolower($submittedLoginId);
                    $customer = findCustomerByEmail($pdo, $customerEmail);

                    if ($customer) {
                        if ((int)($customer['is_active'] ?? 1) !== 1) {
                            $error = 'Tai khoan dang bi khoa.';
                        } else {
                            $customerStored = (string)($customer['password_hash'] ?? '');
                            if ($customerStored === '') {
                                $error = 'Tai khoan nay chua dat mat khau. Vui long dang ky.';
                            } else {
                                $customerNeedsUpgrade = false;
                                $customerPasswordMatched = passwordMatchedWithLegacy($password, $customerStored, $customerNeedsUpgrade);

                                if ($customerPasswordMatched) {
                                    if ($customerNeedsUpgrade || password_needs_rehash($customerStored, PASSWORD_DEFAULT)) {
                                        try {
                                            $rehash = $pdo->prepare('UPDATE customers SET password_hash = ? WHERE id = ?');
                                            $rehash->execute([password_hash($password, PASSWORD_DEFAULT), (int)$customer['id']]);
                                        } catch (Throwable $ignored) {
                                        }
                                    }

                                    loginCustomer($pdo, $customer, 'password');
                                    header('Location: index.php?login=success');
                                    exit;
                                }
                            }
                        }
                    }
                }

                if ($error === '') {
                    $error = 'Tai khoan hoac mat khau khong chinh xac.';
                }
            }
        } else {
            $error = 'Yeu cau dang nhap khong hop le.';
        }
    } catch (Throwable $e) {
        $error = 'Khong the ket noi CSDL de dang nhap. Vui long thu lai.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dang Nhap - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
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
  .login-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 36px;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
  .login-logo {
    height: 54px;
    margin-bottom: 20px;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
  }
  .form-control {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    padding: 14px 18px;
    border-radius: 12px;
  }
  .form-control:focus {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255,255,255,0.3);
    color: #fff;
    box-shadow: none;
  }
  .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }
  .btn-login {
    background: linear-gradient(to right, #3b82f6, #6366f1);
    color: #fff;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  .btn-login:hover { color:#fff; filter: brightness(1.05); }
  .btn-show-pass {
    border-color: rgba(255, 255, 255, 0.2);
    color: #e2e8f0;
    background: rgba(255, 255, 255, 0.07);
    min-width: 68px;
  }
  .btn-show-pass:hover {
    color: #fff;
    border-color: rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.14);
  }
  .input-group .form-control {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
  .input-group .btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }
  .divider {
    position: relative;
    text-align: center;
    margin: 16px 0 14px;
    font-size: 12px;
    color: rgba(255,255,255,.55);
    text-transform: uppercase;
    letter-spacing: 0.12em;
  }
  .divider::before,
  .divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 38%;
    height: 1px;
    background: rgba(255,255,255,.18);
  }
  .divider::before { left: 0; }
  .divider::after { right: 0; }
  .g-holder { display:flex; justify-content:center; min-height:44px; }
</style>
</head>
<body>
<div class="container">
  <div class="d-flex justify-content-center">
    <div class="login-card text-center">
      <img src="img/logo.png" alt="FLCar" class="login-logo">
      <h3 class="fw-bold mb-1">Dang Nhap</h3>
      <p class="text-white-50 mb-4" style="font-size: 0.9rem;">Dang nhap 1 form chung. He thong tu xac dinh quyen admin/khach hang theo tai khoan.</p>

      <?php if ($error !== ''): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; font-size: 0.9rem; padding: 10px; border-radius: 8px;">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <input type="hidden" name="action" value="password_login">
        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Tai khoan / Email</label>
          <input type="text" name="login_id" class="form-control" placeholder="Nhap username admin hoac email"
                 value="<?php echo htmlspecialchars($submittedLoginId, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Mat khau</label>
          <div class="input-group">
            <input type="password" id="passwordInput" name="password" class="form-control" placeholder="Nhap mat khau" required>
            <button type="button" id="togglePasswordBtn" class="btn btn-show-pass">Hien</button>
          </div>
        </div>

        <button type="submit" class="btn btn-login w-100">Dang Nhap</button>
      </form>

      <div class="divider">hoac</div>

      <?php if ($googleClientId !== ''): ?>
        <div id="g_id_onload"
             data-client_id="<?php echo htmlspecialchars($googleClientId, ENT_QUOTES, 'UTF-8'); ?>"
             data-callback="handleGoogleCredentialResponse"
             data-auto_prompt="false"></div>

        <div class="g-holder">
          <div class="g_id_signin"
               data-type="standard"
               data-shape="pill"
               data-theme="filled_blue"
               data-size="large"
               data-text="continue_with"
               data-logo_alignment="left"></div>
        </div>

        <form id="googleLoginForm" method="POST" action="login.php" class="d-none">
          <input type="hidden" name="action" value="google_login">
          <input type="hidden" name="credential" id="googleCredentialInput" value="">
        </form>
      <?php else: ?>
        <div class="alert alert-warning mb-0" style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.5); color: #fcd34d; font-size: 0.85rem;">
          Google login chua duoc cau hinh. Them <code>GOOGLE_CLIENT_ID</code> trong file <code>.env</code>.
        </div>
      <?php endif; ?>

      <div class="text-white-50 mt-4" style="font-size: 0.86rem;">
        Chua co tai khoan? <a href="register.php" class="text-white text-decoration-none fw-semibold">Dang ky ngay</a>
      </div>

      <div class="text-white-50 mt-3" style="font-size: 0.82rem;">
        <a href="index.php" class="text-white-50 text-decoration-none">&larr; Ve trang chu</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($googleClientId !== ''): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function handleGoogleCredentialResponse(response) {
  if (!response || !response.credential) {
    alert('Khong nhan duoc thong tin tu Google.');
    return;
  }

  const credentialInput = document.getElementById('googleCredentialInput');
  const googleForm = document.getElementById('googleLoginForm');
  if (!credentialInput || !googleForm) {
    return;
  }

  credentialInput.value = response.credential;
  googleForm.submit();
}
</script>
<?php endif; ?>
<script>
(function () {
  const passwordInput = document.getElementById('passwordInput');
  const toggleBtn = document.getElementById('togglePasswordBtn');

  if (!passwordInput || !toggleBtn) {
    return;
  }

  toggleBtn.addEventListener('click', function () {
    const showing = passwordInput.type === 'text';
    passwordInput.type = showing ? 'password' : 'text';
    toggleBtn.textContent = showing ? 'Hien' : 'An';
  });
})();
</script>
</body>
</html>
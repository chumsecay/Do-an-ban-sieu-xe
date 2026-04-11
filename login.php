<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap/env.php';
require_once __DIR__ . '/bootstrap/auth.php';
require_once __DIR__ . '/config/database.php';

ensureSessionStarted();

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

$appName = env('APP_NAME', 'FLCar');
$googleClientId = trim((string)env('GOOGLE_CLIENT_ID', ''));
$error = '';
$submittedUsername = '';

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

        if ($action === 'password_login') {
            $submittedUsername = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if ($submittedUsername === '' || $password === '') {
                $error = 'Vui long nhap day du tai khoan va mat khau.';
            } else {
                $stmt = $pdo->prepare(
                    'SELECT id, username, email, password_hash, full_name, role, is_active
                     FROM admins
                     WHERE username = ?
                     LIMIT 1'
                );
                $stmt->execute([$submittedUsername]);
                $admin = $stmt->fetch();

                if ($admin && (string)$admin['password_hash'] === $password) {
                    if ((int)$admin['is_active'] !== 1) {
                        $error = 'Tai khoan admin dang bi khoa.';
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
                    $error = 'Tai khoan hoac mat khau khong chinh xac.';
                }
            }
        } elseif ($action === 'google_login') {
            $credential = trim((string)($_POST['credential'] ?? ''));

            if ($googleClientId === '') {
                $error = 'Dang nhap Google chua duoc cau hinh. Vui long them GOOGLE_CLIENT_ID vao file .env.';
            } elseif ($credential === '') {
                $error = 'Khong nhan duoc token dang nhap Google.';
            } else {
                $profile = verifyGoogleIdToken($credential, $googleClientId);

                if (!is_array($profile)) {
                    $error = 'Xac thuc Google that bai. Vui long thu lai.';
                } else {
                    $stmt = $pdo->prepare(
                        'SELECT id, username, email, password_hash, full_name, role, is_active
                         FROM admins
                         WHERE LOWER(email) = LOWER(?)
                         LIMIT 1'
                    );
                    $stmt->execute([$profile['email']]);
                    $adminByEmail = $stmt->fetch();

                    if ($adminByEmail && (int)$adminByEmail['is_active'] === 1) {
                        loginAsAdmin($adminByEmail);
                        try {
                            $update = $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?');
                            $update->execute([(int)$adminByEmail['id']]);
                        } catch (Throwable $ignored) {
                        }

                        header('Location: admin/index.php?login=success');
                        exit;
                    }

                    if ($adminByEmail && (int)$adminByEmail['is_active'] !== 1) {
                        $error = 'Tai khoan admin lien ket email Google dang bi khoa.';
                    } else {
                        loginAsUser($profile['name'], $profile['email'], 'google');
                        header('Location: index.php?login=success');
                        exit;
                    }
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
  .form-control::placeholder {
    color: rgba(255, 255, 255, 0.4);
  }
  .btn-login {
    background: linear-gradient(to right, #3b82f6, #6366f1);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s;
  }
  .btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(59, 130, 246, 0.4);
    color: white;
  }
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
    margin: 18px 0 16px;
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
  .g-holder {
    display: flex;
    justify-content: center;
    min-height: 44px;
  }
</style>
</head>
<body>
<div class="container">
  <div class="d-flex justify-content-center">
    <div class="login-card text-center">
      <img src="img/logo.png" alt="FLCar" class="login-logo">
      <h3 class="fw-bold mb-1">Dang Nhap</h3>
      <p class="text-white-50 mb-4" style="font-size: 0.9rem;">Admin dang nhap bang tai khoan noi bo. Nguoi dung co the dang nhap bang Google.</p>

      <?php if ($error !== ''): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; font-size: 0.9rem; padding: 10px; border-radius: 8px;">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <input type="hidden" name="action" value="password_login">

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Tai khoan admin</label>
          <input type="text" name="username" class="form-control" placeholder="Nhap ten dang nhap admin" value="<?php echo htmlspecialchars($submittedUsername, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Mat khau</label>
          <div class="input-group">
            <input type="password" id="passwordInput" name="password" class="form-control" placeholder="Nhap mat khau" required>
            <button type="button" id="togglePasswordBtn" class="btn btn-show-pass">Hien</button>
          </div>
        </div>

        <button type="submit" class="btn btn-login w-100">Dang Nhap Admin</button>
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

      <div class="text-white-50 mt-4" style="font-size: 0.82rem;">
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

<?php
session_start();
require_once __DIR__ . '/bootstrap/env.php';
require_once __DIR__ . '/config/database.php';

$error = '';
$appName = env('APP_NAME', 'FLCar');

// Nếu đã đăng nhập, chuyển thẳng vào admin
if (isset($_SESSION['is_admin_logged_in']) && $_SESSION['is_admin_logged_in'] === true) {
    header('Location: admin/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $pdo = getDBConnection();
        // Lấy thông tin user
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        // Kiểm tra mật khẩu (Dùng dạng đơn giản, KHÔNG mã hóa theo yêu cầu của User)
        if ($admin && $admin['password'] === $password) {
            if ($admin['status'] === 'active') {
                $_SESSION['is_admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                
                header('Location: admin/index.php?login=success');
                exit;
            } else {
                $error = 'Tài khoản của bạn đã bị khóa.';
            }
        } else {
            $error = 'Tài khoản hoặc mật khẩu không chính xác.';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ Tài khoản và Mật khẩu.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng Nhập Quản Trị - <?php echo htmlspecialchars($appName); ?></title>
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
  }
  .login-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
  .login-logo {
    height: 50px;
    margin-bottom: 24px;
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
</style>
</head>
<body>

<div class="container">
  <div class="d-flex justify-content-center">
    <div class="login-card text-center">
      <img src="img/logo.png" alt="FLCar" class="login-logo">
      <h3 class="fw-bold mb-1">Đăng Nhập Quản Trị</h3>
      <p class="text-white-50 mb-4" style="font-size: 0.9rem;">Nhập tài khoản để truy cập hệ thống FLCar Admin</p>
      
      <?php if ($error): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; font-size: 0.9rem; padding: 10px; border-radius: 8px;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Tài khoản</label>
          <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập" required>
        </div>
        <div class="mb-4 text-start">
          <label class="form-label text-white-50 small fw-bold mb-1">Mật khẩu</label>
          <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
        </div>
        <button type="submit" class="btn btn-login w-100 mb-3">Đăng Nhập</button>
      </form>
      <div class="text-white-50 mt-4" style="font-size: 0.8rem;">
        &copy; 2026 FLCar - Showroom Siêu Xe
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

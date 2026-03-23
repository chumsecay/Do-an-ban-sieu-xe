<?php
require_once __DIR__ . '/../bootstrap/env.php';
$currentPage = 'login';
$appName = env('APP_NAME', 'FLCar');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Đăng Nhập - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=3" rel="stylesheet">
<style>
  .login-page { min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(15,23,42,.88),rgba(30,41,59,.75)),url('../img/hero.jpg') center/cover no-repeat;padding:40px 20px; }
  .login-box { background:var(--white);padding:48px 40px;width:100%;max-width:440px;border-radius:var(--radius);box-shadow:var(--shadow-xl); }
  .login-box h2 { text-align:center;font-weight:800;margin-bottom:8px;color:var(--dark); }
  .login-box .subtitle { text-align:center;color:var(--text-light);font-size:.9rem;margin-bottom:32px; }
  .login-box label { font-size:.85rem;font-weight:600;color:var(--text);margin-bottom:6px;display:block; }
  .login-box input { width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;font-size:.9rem;transition:border-color .25s,box-shadow .25s;margin-bottom:20px; }
  .login-box input:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.1); }
  .btn-login { width:100%;background:var(--gradient-primary);border:none;color:#fff;padding:14px;border-radius:var(--radius-sm);font-weight:700;font-size:.95rem;cursor:pointer;transition:all var(--transition); }
  .btn-login:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.3); }
  .login-footer { text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-light); }
  .login-footer a { color:var(--primary);font-weight:600;text-decoration:none; }
  .login-footer a:hover { text-decoration:underline; }
  .login-logo { text-align:center;margin-bottom:24px; }
  .login-logo img { height:72px; }
</style>

<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body style="padding-top:0">

<div class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="../img/logo.png" alt="<?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <h2>Đăng Nhập</h2>
    <p class="subtitle">Chào mừng bạn quay lại</p>

    <form onsubmit="return loginUser()">
      <label>Email</label>
      <input type="email" id="email" placeholder="name@example.com" required>

      <label>Mật khẩu</label>
      <input type="password" id="password" placeholder="••••••••" required>

      <button type="submit" class="btn-login">Đăng Nhập</button>

      <div class="login-footer">
        Chưa có tài khoản? <a href="contact.php">Liên hệ</a>
      </div>
      <div class="login-footer" style="margin-top:12px">
        <a href="../index.php" style="color:var(--text-light)">← Về trang chủ</a>
      </div>
    </form>
  </div>
</div>

<script>
function loginUser(){
  var email = document.getElementById("email").value;
  var password = document.getElementById("password").value;
  if(email === "admin@gmail.com" && password === "123456"){
    alert("Đăng nhập thành công!");
    window.location.href = "../index.php";
    return false;
  } else {
    alert("Sai email hoặc mật khẩu!");
    return false;
  }
}
</script>
</body>
</html>

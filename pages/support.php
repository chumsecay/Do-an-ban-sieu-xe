<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../bootstrap/shop.php';
require_once __DIR__ . '/../bootstrap/support.php';
require_once __DIR__ . '/../config/database.php';

ensureSessionStarted();

if (!isUserLoggedIn() || currentUserRole() !== 'user') {
    header('Location: ../login.php');
    exit;
}

$currentPage = 'support';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();
supportEnsureSchema($pdo);

$customer = shopGetCurrentCustomer($pdo, true);
$msg = (string)($_GET['msg'] ?? '');

function redirectSupport(string $msg = ''): void
{
    $url = 'support.php';
    if ($msg !== '') {
        $url .= '?msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'create_ticket') {
        $subject = trim((string)($_POST['subject'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));

        $fullName = trim((string)($_SESSION['user_name'] ?? ($customer['full_name'] ?? '')));
        $email = trim((string)($_SESSION['user_email'] ?? ($customer['email'] ?? '')));
        $customerId = (int)($customer['id'] ?? 0);

        if ($fullName === '' || $email === '' || $message === '') {
            redirectSupport('invalid_data');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectSupport('invalid_data');
        }
        if ($subject !== '' && mb_strlen($subject) > 180) {
            $subject = mb_substr($subject, 0, 180);
        }

        try {
            $stmt = $pdo->prepare('
                INSERT INTO contact_messages (customer_id, full_name, email, phone, subject, message, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $customerId > 0 ? $customerId : null,
                $fullName,
                $email,
                $phone !== '' ? $phone : null,
                $subject !== '' ? $subject : null,
                $message,
                'new',
            ]);
            redirectSupport('sent');
        } catch (Throwable $e) {
            redirectSupport('db_error');
        }
    }
}

$tickets = [];
try {
    $customerId = (int)($customer['id'] ?? 0);
    $customerEmail = trim((string)($customer['email'] ?? ($_SESSION['user_email'] ?? '')));

    if ($customerId > 0) {
        $stmt = $pdo->prepare('
            SELECT id, subject, message, status, admin_reply, replied_at, created_at
            FROM contact_messages
            WHERE customer_id = ?
            ORDER BY id DESC
        ');
        $stmt->execute([$customerId]);
    } else {
        $stmt = $pdo->prepare('
            SELECT id, subject, message, status, admin_reply, replied_at, created_at
            FROM contact_messages
            WHERE LOWER(email) = LOWER(?)
            ORDER BY id DESC
        ');
        $stmt->execute([$customerEmail]);
    }
    $tickets = $stmt->fetchAll();
} catch (Throwable $ignored) {
    $tickets = [];
}

$alertMap = [
    'sent' => ['success', 'Yeu cau ho tro da duoc gui.'],
    'invalid_data' => ['danger', 'Vui long nhap day du noi dung yeu cau.'],
    'db_error' => ['danger', 'Co loi CSDL khi gui yeu cau.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ho Tro - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
<style>
  .support-card { border-radius: 16px; border: 1px solid #e2e8f0; background: #fff; }
  .ticket-reply { white-space: pre-line; color: #334155; }
</style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <h1 class="display-5 fw-bold">Ho Tro Khach Hang</h1>
    <p class="lead mb-0" style="opacity:.85">Gui yeu cau va theo doi phan hoi tu quan tri vien</p>
  </div>
</section>

<section style="padding:60px 0;background:#f8fafc;">
  <div class="container">
    <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
      <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="support-card shadow-sm p-4">
          <h3 class="h5 fw-bold mb-3">Gui yeu cau moi</h3>
          <form method="POST" class="d-grid gap-3">
            <input type="hidden" name="action" value="create_ticket">
            <div>
              <label class="form-label small fw-bold text-secondary">Chu de</label>
              <input type="text" name="subject" class="form-control bg-light border-0" placeholder="Vi du: Ho tro don hang DH-000123">
            </div>
            <div>
              <label class="form-label small fw-bold text-secondary">So dien thoai lien he</label>
              <input type="text" name="phone" class="form-control bg-light border-0" placeholder="0900 xxx xxx">
            </div>
            <div>
              <label class="form-label small fw-bold text-secondary">Noi dung yeu cau</label>
              <textarea name="message" rows="5" class="form-control bg-light border-0" placeholder="Mo ta chi tiet van de cua ban..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary fw-bold">Gui yeu cau</button>
          </form>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="support-card shadow-sm p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 fw-bold mb-0">Yeu cau cua ban</h3>
            <span class="text-secondary small">Tong: <?php echo count($tickets); ?></span>
          </div>

          <?php if (!$tickets): ?>
            <div class="alert alert-light border mb-0">Ban chua gui yeu cau nao.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                    <th>#</th>
                    <th>Chu de</th>
                    <th>Trang thai</th>
                    <th>Ngay gui</th>
                    <th>Phan hoi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tickets as $t): ?>
                    <?php
                    $status = supportNormalizeStatus((string)($t['status'] ?? 'new'));
                    $reply = trim((string)($t['admin_reply'] ?? ''));
                    ?>
                    <tr>
                      <td class="fw-semibold">#<?php echo (int)$t['id']; ?></td>
                      <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($t['subject'] ?: 'Khong co chu de'), ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string)$t['message'], ENT_QUOTES, 'UTF-8'); ?></small>
                      </td>
                      <td>
                        <span class="badge <?php echo htmlspecialchars(supportStatusBadgeClass($status), ENT_QUOTES, 'UTF-8'); ?>">
                          <?php echo htmlspecialchars(supportStatusLabel($status), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      </td>
                      <td class="small text-secondary"><?php echo htmlspecialchars((string)$t['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="min-width:220px;">
                        <?php if ($reply === ''): ?>
                          <span class="text-muted small">Dang cho phan hoi</span>
                        <?php else: ?>
                          <div class="ticket-reply small"><?php echo nl2br(htmlspecialchars($reply, ENT_QUOTES, 'UTF-8')); ?></div>
                          <?php if (!empty($t['replied_at'])): ?>
                            <div class="text-secondary small mt-1">Luc: <?php echo htmlspecialchars((string)$t['replied_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                          <?php endif; ?>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


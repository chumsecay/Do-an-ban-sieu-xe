<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../bootstrap/support.php';
require_once __DIR__ . '/../config/database.php';

$adminPage = 'support';
$pageTitle = 'Ho tro khach hang';
$pageSubtitle = 'Xu ly yeu cau va phan hoi cho khach hang';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();
supportEnsureSchema($pdo);

function redirectSupportAdmin(string $msg = ''): void
{
    $url = 'support.php';
    if ($msg !== '') {
        $url .= '?msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit;
}

$msg = (string)($_GET['msg'] ?? '');
$statusFilter = (string)($_GET['status'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$editId = (int)($_GET['edit'] ?? 0);

if ($statusFilter !== '' && !isset(supportStatusMap()[$statusFilter])) {
    $statusFilter = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'update_ticket') {
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $status = supportNormalizeStatus((string)($_POST['status'] ?? 'read'));
        $reply = trim((string)($_POST['admin_reply'] ?? ''));

        if ($ticketId <= 0) {
            redirectSupportAdmin('invalid_data');
        }

        try {
            if ($reply !== '' && in_array($status, ['new', 'read'], true)) {
                $status = 'replied';
            }

            if ($reply !== '') {
                $stmt = $pdo->prepare('
                    UPDATE contact_messages
                    SET status = ?, admin_reply = ?, replied_at = NOW(), replied_by_admin_id = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $status,
                    $reply,
                    (int)($_SESSION['admin_id'] ?? 0) ?: null,
                    $ticketId,
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
                $stmt->execute([$status, $ticketId]);
            }
            redirectSupportAdmin('updated');
        } catch (Throwable $e) {
            redirectSupportAdmin('db_error');
        }
    }
}

$where = ['1=1'];
$params = [];

if ($statusFilter !== '') {
    $where[] = 'cm.status = :status';
    $params[':status'] = $statusFilter;
}

if ($q !== '') {
    $where[] = '(cm.full_name LIKE :q OR cm.email LIKE :q OR cm.phone LIKE :q OR cm.subject LIKE :q OR cm.message LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}

$tickets = [];
$stats = [
    'total' => 0,
    'new' => 0,
    'replied' => 0,
    'spam' => 0,
];
$editTicket = null;

try {
    $sql = '
        SELECT cm.*, a.full_name AS replied_by_name
        FROM contact_messages cm
        LEFT JOIN admins a ON a.id = cm.replied_by_admin_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY
            CASE cm.status
                WHEN "new" THEN 0
                WHEN "read" THEN 1
                WHEN "replied" THEN 2
                ELSE 3
            END,
            cm.id DESC
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();

    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    $stats['new'] = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
    $stats['replied'] = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'")->fetchColumn();
    $stats['spam'] = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'spam'")->fetchColumn();

    if ($editId > 0) {
        $s = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ? LIMIT 1');
        $s->execute([$editId]);
        $editTicket = $s->fetch() ?: null;
    }
} catch (Throwable $ignored) {
    $tickets = [];
}

$alertMap = [
    'updated' => ['success', 'Da cap nhat yeu cau ho tro.'],
    'invalid_data' => ['danger', 'Du lieu khong hop le.'],
    'db_error' => ['danger', 'Co loi CSDL khi xu ly yeu cau.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ho tro - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background:#f8fafc; }
  .mini-stat { background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
  .mini-stat h3 { margin:0; font-size:1.3rem; font-weight:800; }
  .mini-stat p { margin:2px 0 0; color:#64748b; font-size:.8rem; }
  .table > :not(caption) > * > * { padding: 14px 12px; border-bottom-color:#f1f5f9; vertical-align: middle; }
</style>
</head>
<body>
<div class="admin-wrapper" id="adminWrapper">
  <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <div class="admin-main">
    <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

    <main class="admin-content p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h4 fw-bold text-dark mb-1">Yeu cau ho tro</h2>
          <p class="text-secondary small mb-0">Xem, cap nhat trang thai va phan hoi khach hang.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['total']; ?></h3><p>Tong yeu cau</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['new']; ?></h3><p>Yeu cau moi</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['replied']; ?></h3><p>Da phan hoi</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['spam']; ?></h3><p>Spam</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>

      <?php if ($editTicket): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
          <div class="card-body">
            <h5 class="fw-bold mb-3">Tra loi yeu cau #<?php echo (int)$editTicket['id']; ?></h5>
            <div class="mb-3">
              <div class="fw-semibold"><?php echo htmlspecialchars((string)($editTicket['subject'] ?: 'Khong co chu de'), ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="text-secondary small mt-1">
                <?php echo htmlspecialchars((string)$editTicket['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                (<?php echo htmlspecialchars((string)$editTicket['email'], ENT_QUOTES, 'UTF-8'); ?>)
              </div>
              <div class="small mt-2"><?php echo nl2br(htmlspecialchars((string)$editTicket['message'], ENT_QUOTES, 'UTF-8')); ?></div>
            </div>

            <form method="POST" class="row g-3">
              <input type="hidden" name="action" value="update_ticket">
              <input type="hidden" name="ticket_id" value="<?php echo (int)$editTicket['id']; ?>">
              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Trang thai</label>
                <select name="status" class="form-select bg-light border-0">
                  <?php foreach (supportStatusMap() as $statusKey => $statusMeta): ?>
                    <option value="<?php echo htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo supportNormalizeStatus((string)$editTicket['status']) === $statusKey ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$statusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-9">
                <label class="form-label small fw-bold text-secondary">Noi dung phan hoi</label>
                <textarea name="admin_reply" rows="4" class="form-control bg-light border-0" placeholder="Nhap noi dung phan hoi..."><?php echo htmlspecialchars((string)($editTicket['admin_reply'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
              </div>
              <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-bold px-4">Luu cap nhat</button>
                <a href="support.php" class="btn btn-light border fw-semibold">Huy</a>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-3">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Tim theo ten, email, SDT, chu de..."
                     value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select bg-light border-0">
                <option value="">Tat ca trang thai</option>
                <?php foreach (supportStatusMap() as $statusKey => $statusMeta): ?>
                  <option value="<?php echo htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $statusFilter === $statusKey ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$statusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
              <button class="btn btn-outline-primary fw-semibold" type="submit">Loc</button>
              <a href="support.php" class="btn btn-outline-secondary fw-semibold">Dat lai</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                  <th>#</th>
                  <th>Khach hang</th>
                  <th>Noi dung</th>
                  <th>Trang thai</th>
                  <th>Phan hoi</th>
                  <th class="text-end">Thao tac</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$tickets): ?>
                  <tr><td colspan="6" class="text-center py-4 text-muted">Chua co yeu cau ho tro.</td></tr>
                <?php else: ?>
                  <?php foreach ($tickets as $t): ?>
                    <?php
                    $status = supportNormalizeStatus((string)($t['status'] ?? 'new'));
                    $reply = trim((string)($t['admin_reply'] ?? ''));
                    ?>
                    <tr>
                      <td class="fw-semibold">#<?php echo (int)$t['id']; ?></td>
                      <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)$t['full_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string)$t['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                        <?php if (!empty($t['phone'])): ?>
                          <div class="small text-secondary"><?php echo htmlspecialchars((string)$t['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                      </td>
                      <td style="min-width:280px;">
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($t['subject'] ?: 'Khong co chu de'), ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string)$t['message'], ENT_QUOTES, 'UTF-8'); ?></small>
                        <div class="small text-secondary mt-1">Luc: <?php echo htmlspecialchars((string)$t['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                      </td>
                      <td>
                        <span class="badge <?php echo htmlspecialchars(supportStatusBadgeClass($status), ENT_QUOTES, 'UTF-8'); ?>">
                          <?php echo htmlspecialchars(supportStatusLabel($status), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      </td>
                      <td style="min-width:220px;">
                        <?php if ($reply === ''): ?>
                          <span class="text-muted small">Chua phan hoi</span>
                        <?php else: ?>
                          <div class="small"><?php echo nl2br(htmlspecialchars($reply, ENT_QUOTES, 'UTF-8')); ?></div>
                          <div class="text-secondary small mt-1">
                            <?php echo !empty($t['replied_at']) ? htmlspecialchars((string)$t['replied_at'], ENT_QUOTES, 'UTF-8') : ''; ?>
                            <?php if (!empty($t['replied_by_name'])): ?>
                              - <?php echo htmlspecialchars((string)$t['replied_by_name'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a href="support.php?edit=<?php echo (int)$t['id']; ?>" class="btn btn-sm btn-outline-primary">Xu ly</a>
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
</body>
</html>


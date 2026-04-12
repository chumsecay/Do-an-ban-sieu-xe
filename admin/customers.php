<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'customers';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectCustomers(string $msg): void
{
    header('Location: customers.php?msg=' . urlencode($msg));
    exit;
}

$msg = (string)($_GET['msg'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add') {
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $tier = (string)($_POST['tier'] ?? 'new');

            if ($fullName === '') {
                redirectCustomers('invalid_data');
            }
            if ($tier !== 'new' && $tier !== 'regular' && $tier !== 'vip') {
                $tier = 'new';
            }

            $stmt = $pdo->prepare('INSERT INTO customers (full_name, email, phone, tier) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $fullName,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $tier,
            ]);
            redirectCustomers('added');
        }

        if ($action === 'edit') {
            $id = (int)($_POST['customer_id'] ?? 0);
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $tier = (string)($_POST['tier'] ?? 'new');

            if ($id <= 0 || $fullName === '') {
                redirectCustomers('invalid_data');
            }
            if ($tier !== 'new' && $tier !== 'regular' && $tier !== 'vip') {
                $tier = 'new';
            }

            $stmt = $pdo->prepare('UPDATE customers SET full_name = ?, email = ?, phone = ?, tier = ? WHERE id = ?');
            $stmt->execute([
                $fullName,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $tier,
                $id,
            ]);
            redirectCustomers('edited');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['customer_id'] ?? 0);
            if ($id <= 0) {
                redirectCustomers('invalid_data');
            }
            $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
            $stmt->execute([$id]);
            redirectCustomers('deleted');
        }
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            redirectCustomers('duplicate');
        }
        redirectCustomers('db_error');
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$tierFilter = (string)($_GET['tier'] ?? '');

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(full_name LIKE :q OR email LIKE :q OR phone LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if (in_array($tierFilter, ['new', 'regular', 'vip'], true)) {
    $where[] = 'tier = :tier';
    $params[':tier'] = $tierFilter;
}

$sql = 'SELECT * FROM customers';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$stats = ['total' => 0, 'vip' => 0, 'new' => 0];
try {
    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    $stats['vip'] = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE tier='vip'")->fetchColumn();
    $stats['new'] = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE tier='new'")->fetchColumn();
} catch (Throwable $ignored) {
}

$editCustomer = null;
if ($editId > 0) {
    $s = $pdo->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
    $s->execute([$editId]);
    $editCustomer = $s->fetch() ?: null;
}

$alertMap = [
    'added' => ['success', 'Da them khach hang moi.'],
    'edited' => ['info', 'Da cap nhat ho so khach hang.'],
    'deleted' => ['warning', 'Da xoa ho so khach hang.'],
    'duplicate' => ['danger', 'Email hoac so dien thoai da ton tai.'],
    'invalid_data' => ['danger', 'Du lieu khong hop le.'],
    'db_error' => ['danger', 'Co loi CSDL khi xu ly thao tac.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Khach Hang - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }
  .table > :not(caption) > * > * { padding: 14px 12px; border-bottom-color: #f1f5f9; vertical-align: middle; }
  .avatar-placeholder { width: 36px; height: 36px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .9rem; }
  .mini-stat { background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
  .mini-stat h3 { margin:0; font-size:1.3rem; font-weight:800; }
  .mini-stat p { margin:2px 0 0; color:#64748b; font-size:.8rem; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Quan Ly Khach Hang</h2>
          <p class="text-secondary mb-0 small">Them, sua, xoa va theo doi ho so khach hang.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['total']; ?></h3><p>Tong khach hang</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['vip']; ?></h3><p>Khach VIP</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['new']; ?></h3><p>Khach moi</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <form method="POST" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="<?php echo $editCustomer ? 'edit' : 'add'; ?>">
            <?php if ($editCustomer): ?>
              <input type="hidden" name="customer_id" value="<?php echo (int)$editCustomer['id']; ?>">
            <?php endif; ?>

            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Ho ten</label>
              <input type="text" name="full_name" class="form-control bg-light border-0" required
                     value="<?php echo htmlspecialchars((string)($editCustomer['full_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Email</label>
              <input type="email" name="email" class="form-control bg-light border-0"
                     value="<?php echo htmlspecialchars((string)($editCustomer['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">So dien thoai</label>
              <input type="text" name="phone" class="form-control bg-light border-0"
                     value="<?php echo htmlspecialchars((string)($editCustomer['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Hang</label>
              <select name="tier" class="form-select bg-light border-0">
                <option value="new" <?php echo (!$editCustomer || ($editCustomer['tier'] ?? '') === 'new') ? 'selected' : ''; ?>>Khach moi</option>
                <option value="regular" <?php echo (($editCustomer['tier'] ?? '') === 'regular') ? 'selected' : ''; ?>>Than thiet</option>
                <option value="vip" <?php echo (($editCustomer['tier'] ?? '') === 'vip') ? 'selected' : ''; ?>>VIP</option>
              </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary fw-bold px-3"><?php echo $editCustomer ? 'Luu' : 'Them'; ?></button>
              <?php if ($editCustomer): ?>
                <a class="btn btn-light border fw-semibold" href="customers.php">Huy</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-3">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Tim theo ten, email, sdt..."
                     value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <select name="tier" class="form-select bg-light border-0">
                <option value="">Tat ca hang</option>
                <option value="new" <?php echo $tierFilter === 'new' ? 'selected' : ''; ?>>Khach moi</option>
                <option value="regular" <?php echo $tierFilter === 'regular' ? 'selected' : ''; ?>>Than thiet</option>
                <option value="vip" <?php echo $tierFilter === 'vip' ? 'selected' : ''; ?>>VIP</option>
              </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
              <button class="btn btn-outline-primary fw-semibold" type="submit">Loc</button>
              <a href="customers.php" class="btn btn-outline-secondary fw-semibold">Reset</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <th>Khach hang</th>
                  <th>Dien thoai</th>
                  <th>Email</th>
                  <th>Hang</th>
                  <th>Ngay tao</th>
                  <th class="text-end">Thao tac</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$customers): ?>
                  <tr><td colspan="6" class="text-center py-5 text-muted">Chua co du lieu khach hang.</td></tr>
                <?php else: ?>
                  <?php foreach ($customers as $c): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="avatar-placeholder"><?php echo htmlspecialchars(strtoupper(substr((string)$c['full_name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></div>
                          <span class="fw-bold text-dark"><?php echo htmlspecialchars((string)$c['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                      </td>
                      <td class="text-secondary fw-medium"><?php echo htmlspecialchars((string)($c['phone'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td class="text-secondary fw-medium"><?php echo htmlspecialchars((string)($c['email'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <?php if (($c['tier'] ?? '') === 'vip'): ?>
                          <span class="badge bg-warning text-dark px-3 py-2 rounded-pill bg-opacity-25 border border-warning">VIP</span>
                        <?php elseif (($c['tier'] ?? '') === 'regular'): ?>
                          <span class="badge bg-success text-success px-3 py-2 rounded-pill bg-opacity-25">Than thiet</span>
                        <?php else: ?>
                          <span class="badge bg-info text-info px-3 py-2 rounded-pill bg-opacity-10">Khach moi</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-secondary small fw-medium"><?php echo htmlspecialchars(date('d/m/Y', strtotime((string)$c['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td class="text-end">
                        <a href="customers.php?edit=<?php echo (int)$c['id']; ?>" class="btn btn-sm btn-outline-primary">Sua</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xoa ho so khach hang nay?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="customer_id" value="<?php echo (int)$c['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Xoa</button>
                        </form>
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

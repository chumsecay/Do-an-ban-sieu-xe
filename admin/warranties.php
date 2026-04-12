<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'warranties';
$pageTitle = 'Bao hanh';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectWarranty(string $msg): void
{
    header('Location: warranties.php?msg=' . urlencode($msg));
    exit;
}

function nextWarrantyCode(PDO $pdo): string
{
    $row = $pdo->query('SELECT warranty_code FROM warranties WHERE warranty_code LIKE "BH-%" ORDER BY id DESC LIMIT 1')->fetch();
    if (!$row || empty($row['warranty_code'])) {
        return 'BH-000001';
    }
    $num = (int)preg_replace('/[^0-9]/', '', (string)$row['warranty_code']);
    return 'BH-' . str_pad((string)($num + 1), 6, '0', STR_PAD_LEFT);
}

$msg = (string)($_GET['msg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add' || $action === 'edit') {
            $id = (int)($_POST['warranty_id'] ?? 0);
            $warrantyCode = trim((string)($_POST['warranty_code'] ?? ''));
            $orderId = (int)($_POST['order_id'] ?? 0);
            $carId = (int)($_POST['car_id'] ?? 0);
            $customerId = (int)($_POST['customer_id'] ?? 0);
            $startDate = (string)($_POST['start_date'] ?? '');
            $endDate = (string)($_POST['end_date'] ?? '');
            $terms = trim((string)($_POST['terms'] ?? ''));
            $status = (string)($_POST['status'] ?? 'active');

            if ($warrantyCode === '') {
                $warrantyCode = nextWarrantyCode($pdo);
            }

            if ($carId <= 0 || $customerId <= 0 || $startDate === '' || $endDate === '') {
                redirectWarranty('invalid_data');
            }
            if ($status !== 'active' && $status !== 'expired' && $status !== 'void') {
                $status = 'active';
            }
            if (strtotime($endDate) < strtotime($startDate)) {
                redirectWarranty('invalid_dates');
            }

            if ($action === 'add') {
                $stmt = $pdo->prepare(
                    'INSERT INTO warranties (warranty_code, order_id, car_id, customer_id, start_date, end_date, terms, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $warrantyCode,
                    $orderId > 0 ? $orderId : null,
                    $carId,
                    $customerId,
                    $startDate,
                    $endDate,
                    $terms !== '' ? $terms : null,
                    $status,
                ]);
                redirectWarranty('added');
            }

            if ($id <= 0) {
                redirectWarranty('invalid_data');
            }

            $stmt = $pdo->prepare(
                'UPDATE warranties
                 SET warranty_code = ?, order_id = ?, car_id = ?, customer_id = ?, start_date = ?, end_date = ?, terms = ?, status = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $warrantyCode,
                $orderId > 0 ? $orderId : null,
                $carId,
                $customerId,
                $startDate,
                $endDate,
                $terms !== '' ? $terms : null,
                $status,
                $id,
            ]);
            redirectWarranty('updated');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['warranty_id'] ?? 0);
            if ($id <= 0) {
                redirectWarranty('invalid_data');
            }
            $stmt = $pdo->prepare('DELETE FROM warranties WHERE id = ?');
            $stmt->execute([$id]);
            redirectWarranty('deleted');
        }
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            redirectWarranty('duplicate');
        }
        redirectWarranty('db_error');
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = (string)($_GET['status'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(w.warranty_code LIKE :q OR c.name LIKE :q OR cu.full_name LIKE :q OR o.order_no LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if (in_array($statusFilter, ['active', 'expired', 'void'], true)) {
    $where[] = 'w.status = :status';
    $params[':status'] = $statusFilter;
}

$sql = '
    SELECT w.*, o.order_no, c.name AS car_name, cu.full_name AS customer_name, cu.phone AS customer_phone
    FROM warranties w
    LEFT JOIN orders o ON o.id = w.order_id
    JOIN cars c ON c.id = w.car_id
    JOIN customers cu ON cu.id = w.customer_id
';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY w.created_at DESC, w.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$warranties = $stmt->fetchAll();

$orders = [];
$cars = [];
$customers = [];
try {
    $orders = $pdo->query('SELECT id, order_no FROM orders ORDER BY id DESC')->fetchAll();
    $cars = $pdo->query('SELECT id, name FROM cars ORDER BY id DESC')->fetchAll();
    $customers = $pdo->query('SELECT id, full_name FROM customers ORDER BY id DESC')->fetchAll();
} catch (Throwable $ignored) {
}

$stats = ['total' => 0, 'active' => 0, 'expired' => 0];
try {
    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM warranties')->fetchColumn();
    $stats['active'] = (int)$pdo->query("SELECT COUNT(*) FROM warranties WHERE status='active'")->fetchColumn();
    $stats['expired'] = (int)$pdo->query("SELECT COUNT(*) FROM warranties WHERE status='expired'")->fetchColumn();
} catch (Throwable $ignored) {
}

$editWarranty = null;
if ($editId > 0) {
    $s = $pdo->prepare('SELECT * FROM warranties WHERE id = ? LIMIT 1');
    $s->execute([$editId]);
    $editWarranty = $s->fetch() ?: null;
}

$alertMap = [
    'added' => ['success', 'Da tao phieu bao hanh moi.'],
    'updated' => ['info', 'Da cap nhat phieu bao hanh.'],
    'deleted' => ['warning', 'Da xoa phieu bao hanh.'],
    'duplicate' => ['danger', 'Ma bao hanh da ton tai.'],
    'invalid_data' => ['danger', 'Du lieu khong hop le.'],
    'invalid_dates' => ['danger', 'Ngay ket thuc phai lon hon hoac bang ngay bat dau.'],
    'db_error' => ['danger', 'Co loi CSDL khi xu ly thao tac.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
<style>
  body { font-family: 'Inter', sans-serif !important; background:#f8fafc; }
  .table > :not(caption) > * > * { padding: 14px 12px; vertical-align: middle; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Quan Ly Bao Hanh</h2>
          <p class="text-secondary small mb-0">Quan ly phieu bao hanh theo xe va khach hang.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['total']; ?></h3><p>Tong phieu</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['active']; ?></h3><p>Con hieu luc</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['expired']; ?></h3><p>Da het han</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <?php if (!$cars || !$customers): ?>
            <div class="alert alert-warning mb-0">Can co du lieu Cars va Customers truoc khi tao bao hanh.</div>
          <?php else: ?>
            <form method="POST" class="row g-3 align-items-end">
              <input type="hidden" name="action" value="<?php echo $editWarranty ? 'edit' : 'add'; ?>">
              <?php if ($editWarranty): ?>
                <input type="hidden" name="warranty_id" value="<?php echo (int)$editWarranty['id']; ?>">
              <?php endif; ?>

              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Ma BH</label>
                <input type="text" name="warranty_code" class="form-control bg-light border-0"
                       placeholder="<?php echo htmlspecialchars(nextWarrantyCode($pdo), ENT_QUOTES, 'UTF-8'); ?>"
                       value="<?php echo htmlspecialchars((string)($editWarranty['warranty_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Hoa don (tuy chon)</label>
                <select name="order_id" class="form-select bg-light border-0">
                  <option value="0">Khong gan hoa don</option>
                  <?php foreach ($orders as $o): ?>
                    <option value="<?php echo (int)$o['id']; ?>" <?php echo ((int)($editWarranty['order_id'] ?? 0) === (int)$o['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$o['order_no'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Xe</label>
                <select name="car_id" class="form-select bg-light border-0" required>
                  <option value="">Chon xe</option>
                  <?php foreach ($cars as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)($editWarranty['car_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Khach hang</label>
                <select name="customer_id" class="form-select bg-light border-0" required>
                  <option value="">Chon khach hang</option>
                  <?php foreach ($customers as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)($editWarranty['customer_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$c['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Trang thai</label>
                <select name="status" class="form-select bg-light border-0">
                  <option value="active" <?php echo (!$editWarranty || ($editWarranty['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Con hieu luc</option>
                  <option value="expired" <?php echo (($editWarranty['status'] ?? '') === 'expired') ? 'selected' : ''; ?>>Da het han</option>
                  <option value="void" <?php echo (($editWarranty['status'] ?? '') === 'void') ? 'selected' : ''; ?>>Vo hieu</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Ngay bat dau</label>
                <input type="date" name="start_date" class="form-control bg-light border-0" required
                       value="<?php echo htmlspecialchars((string)($editWarranty['start_date'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Ngay ket thuc</label>
                <input type="date" name="end_date" class="form-control bg-light border-0" required
                       value="<?php echo htmlspecialchars((string)($editWarranty['end_date'] ?? date('Y-m-d', strtotime('+3 years'))), ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary">Dieu khoan bao hanh</label>
                <input type="text" name="terms" class="form-control bg-light border-0" placeholder="Ghi chu / dieu khoan"
                       value="<?php echo htmlspecialchars((string)($editWarranty['terms'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
              </div>

              <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-bold px-4"><?php echo $editWarranty ? 'Luu cap nhat' : 'Tao phieu bao hanh'; ?></button>
                <?php if ($editWarranty): ?>
                  <a href="warranties.php" class="btn btn-light border fw-semibold">Huy sua</a>
                <?php endif; ?>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Tim ma BH, ma HD, xe, khach hang..."
                     value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-4">
              <select name="status" class="form-select bg-light border-0">
                <option value="">Tat ca trang thai</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Con hieu luc</option>
                <option value="expired" <?php echo $statusFilter === 'expired' ? 'selected' : ''; ?>>Da het han</option>
                <option value="void" <?php echo $statusFilter === 'void' ? 'selected' : ''; ?>>Vo hieu</option>
              </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary fw-semibold">Loc</button>
              <a href="warranties.php" class="btn btn-outline-secondary fw-semibold">Reset</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                  <th>Ma BH</th>
                  <th>Khach hang</th>
                  <th>Xe</th>
                  <th>Ma HD</th>
                  <th>Hieu luc</th>
                  <th>Trang thai</th>
                  <th class="text-end">Thao tac</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$warranties): ?>
                  <tr><td colspan="7" class="text-center text-muted py-4">Khong co du lieu bao hanh.</td></tr>
                <?php else: ?>
                  <?php foreach ($warranties as $w): ?>
                    <tr>
                      <td class="fw-semibold"><?php echo htmlspecialchars((string)$w['warranty_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <div><?php echo htmlspecialchars((string)$w['customer_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string)($w['customer_phone'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></small>
                      </td>
                      <td><?php echo htmlspecialchars((string)$w['car_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)($w['order_no'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)$w['start_date'], ENT_QUOTES, 'UTF-8'); ?> -> <?php echo htmlspecialchars((string)$w['end_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <?php if (($w['status'] ?? '') === 'active'): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle">Con hieu luc</span>
                        <?php elseif (($w['status'] ?? '') === 'expired'): ?>
                          <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Het han</span>
                        <?php else: ?>
                          <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Vo hieu</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="warranties.php?edit=<?php echo (int)$w['id']; ?>">Sua</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xoa phieu bao hanh nay?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="warranty_id" value="<?php echo (int)$w['id']; ?>">
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

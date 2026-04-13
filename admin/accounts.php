<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'accounts';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectAccounts(string $msg = ''): void
{
    $url = 'accounts.php';
    if ($msg !== '') {
        $url .= '?msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit;
}

function nextEmployeeCode(PDO $pdo): string
{
    $row = $pdo->query('SELECT code FROM employees WHERE code LIKE "NV-%" ORDER BY id DESC LIMIT 1')->fetch();
    if (!$row || empty($row['code'])) {
        return 'NV-001';
    }
    $num = (int)preg_replace('/[^0-9]/', '', (string)$row['code']);
    return 'NV-' . str_pad((string)($num + 1), 3, '0', STR_PAD_LEFT);
}

function ensureCustomerBalanceColumn(PDO $pdo): void
{
    $check = $pdo->query("SHOW COLUMNS FROM customers LIKE 'balance'");
    if (!$check->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER tier");
    }
}

function ensureCustomerAuthColumns(PDO $pdo): void
{
    $passwordCol = $pdo->query("SHOW COLUMNS FROM customers LIKE 'password_hash'");
    if (!$passwordCol->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255) NULL AFTER email");
    }

    $activeCol = $pdo->query("SHOW COLUMNS FROM customers LIKE 'is_active'");
    if (!$activeCol->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER balance");
    }

    $lastLoginCol = $pdo->query("SHOW COLUMNS FROM customers LIKE 'last_login_at'");
    if (!$lastLoginCol->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN last_login_at DATETIME NULL AFTER is_active");
    }
}

function ensureAdminEmployeeColumn(PDO $pdo): void
{
    $col = $pdo->query("SHOW COLUMNS FROM admins LIKE 'employee_id'");
    if (!$col->fetch()) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN employee_id BIGINT UNSIGNED NULL AFTER id");
    }

    $idx = $pdo->query("SHOW INDEX FROM admins WHERE Key_name='idx_admins_employee'");
    if (!$idx->fetch()) {
        $pdo->exec("ALTER TABLE admins ADD INDEX idx_admins_employee (employee_id)");
    }
}

function normalizeTier(string $tier): string
{
    return in_array($tier, ['new', 'regular', 'vip'], true) ? $tier : 'new';
}

function normalizeEmployeeStatus(string $status): string
{
    return in_array($status, ['active', 'inactive'], true) ? $status : 'active';
}

function normalizeAdminRole(string $role): string
{
    return in_array($role, ['super_admin', 'editor', 'sales'], true) ? $role : 'sales';
}

$schemaError = '';
try {
    ensureCustomerBalanceColumn($pdo);
    ensureCustomerAuthColumns($pdo);
    ensureAdminEmployeeColumn($pdo);
} catch (Throwable $e) {
    $schemaError = 'Không thể cập nhật CSDL cho module tài khoản.';
}

$msg = (string)($_GET['msg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add_customer') {
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $tier = normalizeTier((string)($_POST['tier'] ?? 'new'));
            $balance = (float)($_POST['balance'] ?? 0);

            if ($fullName === '') {
                redirectAccounts('invalid_data');
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                redirectAccounts('invalid_email');
            }
            if ($balance < 0) {
                $balance = 0;
            }

            $stmt = $pdo->prepare('INSERT INTO customers (full_name, email, phone, tier, balance) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $fullName,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $tier,
                $balance,
            ]);
            redirectAccounts('customer_added');
        }

        if ($action === 'add_employee') {
            $code = trim((string)($_POST['code'] ?? ''));
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $position = trim((string)($_POST['position'] ?? ''));
            $status = normalizeEmployeeStatus((string)($_POST['status'] ?? 'active'));

            $adminUsername = trim((string)($_POST['admin_username'] ?? ''));
            $adminPassword = (string)($_POST['admin_password'] ?? '');
            $adminRole = normalizeAdminRole((string)($_POST['admin_role'] ?? 'sales'));
            $adminIsActive = isset($_POST['admin_is_active']) ? 1 : 0;

            if ($fullName === '' || $email === '') {
                redirectAccounts('invalid_data');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                redirectAccounts('invalid_email');
            }
            if ($code === '') {
                $code = nextEmployeeCode($pdo);
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO employees (code, full_name, email, phone, position, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $code,
                $fullName,
                $email,
                $phone !== '' ? $phone : null,
                $position !== '' ? $position : null,
                $status,
            ]);
            $employeeId = (int)$pdo->lastInsertId();

            if ($adminUsername !== '') {
                if (strlen($adminPassword) < 6) {
                    $pdo->rollBack();
                    redirectAccounts('admin_password_short');
                }

                $adminStmt = $pdo->prepare('INSERT INTO admins (employee_id, username, email, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $adminStmt->execute([
                    $employeeId,
                    $adminUsername,
                    $email,
                    password_hash($adminPassword, PASSWORD_DEFAULT),
                    $fullName,
                    $adminRole,
                    $adminIsActive,
                ]);
            }

            $pdo->commit();
            redirectAccounts('employee_added');
        }

        if ($action === 'adjust_balance') {
            $customerId = (int)($_POST['customer_id'] ?? 0);
            $delta = (float)($_POST['delta'] ?? 0);
            if ($customerId <= 0 || abs($delta) < 0.00001) {
                redirectAccounts('balance_invalid');
            }

            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT balance FROM customers WHERE id = ? FOR UPDATE');
            $lock->execute([$customerId]);
            $row = $lock->fetch();
            if (!$row) {
                $pdo->rollBack();
                redirectAccounts('invalid_data');
            }

            $after = (float)$row['balance'] + $delta;
            if ($after < 0) {
                $pdo->rollBack();
                redirectAccounts('balance_not_enough');
            }

            $upd = $pdo->prepare('UPDATE customers SET balance = ? WHERE id = ?');
            $upd->execute([$after, $customerId]);
            $pdo->commit();
            redirectAccounts('balance_updated');
        }

        if ($action === 'delete_customer') {
            $customerId = (int)($_POST['customer_id'] ?? 0);
            if ($customerId <= 0) {
                redirectAccounts('invalid_data');
            }

            try {
                $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
                $stmt->execute([$customerId]);
                redirectAccounts('customer_deleted');
            } catch (PDOException $e) {
                if (str_starts_with((string)$e->getCode(), '23')) {
                    redirectAccounts('customer_delete_blocked');
                }
                throw $e;
            }
        }

        if ($action === 'set_customer_active') {
            $customerId = (int)($_POST['customer_id'] ?? 0);
            $isActive = (int)($_POST['is_active'] ?? 1) === 1 ? 1 : 0;
            if ($customerId <= 0) {
                redirectAccounts('invalid_data');
            }

            $stmt = $pdo->prepare('UPDATE customers SET is_active = ? WHERE id = ?');
            $stmt->execute([$isActive, $customerId]);
            redirectAccounts($isActive === 1 ? 'customer_unlocked' : 'customer_locked');
        }

        if ($action === 'delete_employee') {
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            if ($employeeId <= 0) {
                redirectAccounts('invalid_data');
            }

            $pdo->beginTransaction();
            $pdo->prepare('UPDATE admins SET employee_id = NULL, is_active = 0 WHERE employee_id = ?')->execute([$employeeId]);
            $pdo->prepare('DELETE FROM employees WHERE id = ?')->execute([$employeeId]);
            $pdo->commit();
            redirectAccounts('employee_deleted');
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            redirectAccounts('duplicate');
        }
        redirectAccounts('db_error');
    }
}

$customers = [];
$employees = [];
$stats = [
    'customers_total' => 0,
    'employees_total' => 0,
    'employees_with_admin' => 0,
    'customers_balance' => 0.0,
];

try {
    $customers = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) AS order_count FROM customers c ORDER BY c.id DESC')->fetchAll();

    $employees = $pdo->query('SELECT e.*, a.id AS admin_id, a.username AS admin_username, a.role AS admin_role, a.is_active AS admin_is_active FROM employees e LEFT JOIN admins a ON a.employee_id = e.id ORDER BY e.id DESC')->fetchAll();

    $stats['customers_total'] = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    $stats['employees_total'] = (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
    $stats['employees_with_admin'] = (int)$pdo->query('SELECT COUNT(*) FROM admins WHERE employee_id IS NOT NULL')->fetchColumn();
    $stats['customers_balance'] = (float)$pdo->query('SELECT COALESCE(SUM(balance), 0) FROM customers')->fetchColumn();
} catch (Throwable $e) {
}

$alerts = [
    'customer_added' => ['success', 'Đã thêm tài khoản khách hàng.'],
    'customer_deleted' => ['warning', 'Đã xóa khách hàng.'],
    'customer_locked' => ['warning', 'Đã khóa tài khoản khách hàng.'],
    'customer_unlocked' => ['success', 'Đã mở khóa tài khoản khách hàng.'],
    'customer_delete_blocked' => ['danger', 'Không thể xóa khách hàng này vì đã có dữ liệu liên quan.'],
    'employee_added' => ['success', 'Đã thêm tài khoản nhân viên.'],
    'employee_deleted' => ['warning', 'Đã xóa nhân viên.'],
    'balance_updated' => ['success', 'Đã cập nhật số dư khách hàng.'],
    'balance_invalid' => ['danger', 'Số tien dieu chinh không hợp lệ.'],
    'balance_not_enough' => ['danger', 'Không đủ số dư để trừ.'],
    'admin_password_short' => ['danger', 'Mật khẩu admin phải từ 6 ký tự trở lên.'],
    'invalid_email' => ['danger', 'Email không hợp lệ.'],
    'invalid_data' => ['danger', 'Dữ liệu không hợp lệ.'],
    'duplicate' => ['danger', 'Dữ liệu bị trùng (email, mã NV, username admin).'],
    'db_error' => ['danger', 'Có lỗi CSDL khi xử lý thao tác.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý tài khoản - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background:#f8fafc; }
  .mini-stat { background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
  .mini-stat h3 { margin:0; font-size:1.25rem; font-weight:800; }
  .mini-stat p { margin:2px 0 0; color:#64748b; font-size:.8rem; }
  .table > :not(caption) > * > * { padding: 13px 12px; border-bottom-color:#f1f5f9; vertical-align: middle; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Quản Lý Tài Khoản</h2>
          <p class="text-secondary mb-0 small">Gộp khách hàng và nhân viên trên 1 trang. Có thể tạo tài khoản nhân viên và quản lý số dư KH để mua online.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['customers_total']; ?></h3><p>Tổng khách hàng</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['employees_total']; ?></h3><p>Tổng nhân viên</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['employees_with_admin']; ?></h3><p>NV co login admin</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3>$<?php echo number_format((float)$stats['customers_balance'], 2); ?></h3><p>Tổng số đủ KH</p></div></div>
      </div>

      <?php if ($schemaError !== ''): ?>
        <div class="alert alert-warning border-0 rounded-3"><?php echo htmlspecialchars($schemaError, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <?php if ($msg !== '' && isset($alerts[$msg])): $a = $alerts[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Thêm khách hàng</h5>
          <form method="POST" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="add_customer">
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Họ tên</label>
              <input type="text" name="full_name" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Email</label>
              <input type="email" name="email" class="form-control bg-light border-0">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Số điện thoại</label>
              <input type="text" name="phone" class="form-control bg-light border-0">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Hang KH</label>
              <select name="tier" class="form-select bg-light border-0">
                <option value="new">Mới</option>
                <option value="regular">Thuong</option>
                <option value="vip">VIP</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Số dư ($)</label>
              <input type="number" name="balance" min="0" step="0.01" value="0" class="form-control bg-light border-0">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary fw-bold px-4">Thêm khách hàng</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Thêm nhân viên</h5>
          <form method="POST" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="add_employee">
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Ma NV</label>
              <input type="text" name="code" class="form-control bg-light border-0" placeholder="<?php echo htmlspecialchars(nextEmployeeCode($pdo), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Họ tên</label>
              <input type="text" name="full_name" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Email</label>
              <input type="email" name="email" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Số điện thoại</label>
              <input type="text" name="phone" class="form-control bg-light border-0">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Vị trí</label>
              <input type="text" name="position" class="form-control bg-light border-0">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Trạng thái</label>
              <select name="status" class="form-select bg-light border-0">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Username admin (tùy chọn)</label>
              <input type="text" name="admin_username" class="form-control bg-light border-0" placeholder="Nhập nếu cần login admin">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Mật khẩu admin</label>
              <input type="text" name="admin_password" class="form-control bg-light border-0" placeholder="Tối thiểu 6 ký tự">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Role admin</label>
              <select name="admin_role" class="form-select bg-light border-0">
                <option value="sales">sales</option>
                <option value="editor">editor</option>
                <option value="super_admin">super_admin</option>
              </select>
            </div>
            <div class="col-md-2">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="admin_is_active" id="adminIsActive" checked>
                <label class="form-check-label small fw-semibold" for="adminIsActive">Admin active</label>
              </div>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary fw-bold px-4">Thêm nhân viên</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Danh sach khách hàng</h5>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                  <th>ID</th>
                  <th>Họ tên</th>
                  <th>Liên hệ</th>
                  <th>Tier</th>
                  <th>Trạng thái</th>
                  <th>Số dư</th>
                  <th>Đơn hàng</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$customers): ?>
                  <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có khách hàng.</td></tr>
                <?php else: ?>
                  <?php foreach ($customers as $c): ?>
                    <tr>
                      <td><?php echo (int)$c['id']; ?></td>
                      <td class="fw-semibold"><?php echo htmlspecialchars((string)$c['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <div><?php echo htmlspecialchars((string)($c['email'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string)($c['phone'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></small>
                      </td>
                      <td><?php echo htmlspecialchars((string)($c['tier'] ?? 'new'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <?php if ((int)($c['is_active'] ?? 1) === 1): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle">Đang mở</span>
                        <?php else: ?>
                          <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Đã khóa</span>
                        <?php endif; ?>
                      </td>
                      <td class="fw-bold">$<?php echo number_format((float)($c['balance'] ?? 0), 2); ?></td>
                      <td><?php echo (int)($c['order_count'] ?? 0); ?></td>
                      <td class="text-end">
                        <form method="POST" class="d-inline-flex align-items-center gap-1">
                          <input type="hidden" name="action" value="adjust_balance">
                          <input type="hidden" name="customer_id" value="<?php echo (int)$c['id']; ?>">
                          <input type="number" name="delta" step="0.01" class="form-control form-control-sm" style="width:100px" placeholder="+/-">
                          <button type="submit" class="btn btn-sm btn-outline-success">Số dư</button>
                        </form>
                        <form method="POST" class="d-inline">
                          <input type="hidden" name="action" value="set_customer_active">
                          <input type="hidden" name="customer_id" value="<?php echo (int)$c['id']; ?>">
                          <input type="hidden" name="is_active" value="<?php echo (int)($c['is_active'] ?? 1) === 1 ? '0' : '1'; ?>">
                          <button type="submit" class="btn btn-sm <?php echo (int)($c['is_active'] ?? 1) === 1 ? 'btn-outline-warning' : 'btn-outline-primary'; ?>">
                            <?php echo (int)($c['is_active'] ?? 1) === 1 ? 'Khóa' : 'Mở khóa'; ?>
                          </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xóa khách hàng nay?');">
                          <input type="hidden" name="action" value="delete_customer">
                          <input type="hidden" name="customer_id" value="<?php echo (int)$c['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
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
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Danh sach nhân viên</h5>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                  <th>Ma NV</th>
                  <th>Họ tên</th>
                  <th>Liên hệ</th>
                  <th>Vị trí</th>
                  <th>Status</th>
                  <th>Login admin</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$employees): ?>
                  <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có nhân viên.</td></tr>
                <?php else: ?>
                  <?php foreach ($employees as $e): ?>
                    <tr>
                      <td class="fw-semibold"><?php echo htmlspecialchars((string)$e['code'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td class="fw-semibold"><?php echo htmlspecialchars((string)$e['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <div><?php echo htmlspecialchars((string)$e['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string)($e['phone'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></small>
                      </td>
                      <td><?php echo htmlspecialchars((string)($e['position'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)($e['status'] ?? 'active'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <?php if ((int)($e['admin_id'] ?? 0) > 0): ?>
                          <div class="fw-semibold"><?php echo htmlspecialchars((string)$e['admin_username'], ENT_QUOTES, 'UTF-8'); ?></div>
                          <small class="text-secondary"><?php echo htmlspecialchars((string)($e['admin_role'] ?? 'sales'), ENT_QUOTES, 'UTF-8'); ?></small>
                        <?php else: ?>
                          <span class="text-muted">Chưa tạo</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xóa nhân viên nay?');">
                          <input type="hidden" name="action" value="delete_employee">
                          <input type="hidden" name="employee_id" value="<?php echo (int)$e['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
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
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('adminWrapper');
    if (toggle && wrapper) {
      toggle.addEventListener('click', () => wrapper.classList.toggle('sidebar-collapsed'));
    }
  });
</script>
</body>
</html>


<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'employees';
$pageTitle = 'Nhân viên';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectEmployee(string $msg): void
{
    header('Location: employees.php?msg=' . urlencode($msg));
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

$msg = (string)($_GET['msg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add') {
            $code = trim((string)($_POST['code'] ?? ''));
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $position = trim((string)($_POST['position'] ?? ''));
            $status = (string)($_POST['status'] ?? 'active');

            if ($fullName === '' || $email === '') {
                redirectEmployee('invalid_data');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                redirectEmployee('invalid_email');
            }
            if ($status !== 'active' && $status !== 'inactive') {
                $status = 'active';
            }
            if ($code === '') {
                $code = nextEmployeeCode($pdo);
            }

            $stmt = $pdo->prepare('INSERT INTO employees (code, full_name, email, phone, position, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$code, $fullName, $email, $phone !== '' ? $phone : null, $position !== '' ? $position : null, $status]);
            redirectEmployee('added');
        }

        if ($action === 'edit') {
            $id = (int)($_POST['employee_id'] ?? 0);
            $code = trim((string)($_POST['code'] ?? ''));
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $position = trim((string)($_POST['position'] ?? ''));
            $status = (string)($_POST['status'] ?? 'active');

            if ($id <= 0 || $fullName === '' || $email === '' || $code === '') {
                redirectEmployee('invalid_data');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                redirectEmployee('invalid_email');
            }
            if ($status !== 'active' && $status !== 'inactive') {
                $status = 'active';
            }

            $stmt = $pdo->prepare('UPDATE employees SET code = ?, full_name = ?, email = ?, phone = ?, position = ?, status = ? WHERE id = ?');
            $stmt->execute([$code, $fullName, $email, $phone !== '' ? $phone : null, $position !== '' ? $position : null, $status, $id]);
            redirectEmployee('updated');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['employee_id'] ?? 0);
            if ($id <= 0) {
                redirectEmployee('invalid_data');
            }
            $stmt = $pdo->prepare('DELETE FROM employees WHERE id = ?');
            $stmt->execute([$id]);
            redirectEmployee('deleted');
        }
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            redirectEmployee('duplicate');
        }
        redirectEmployee('db_error');
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = (string)($_GET['status'] ?? '');
$positionFilter = trim((string)($_GET['position'] ?? ''));
$editId = (int)($_GET['edit'] ?? 0);

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(full_name LIKE :q OR code LIKE :q OR email LIKE :q OR phone LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($statusFilter === 'active' || $statusFilter === 'inactive') {
    $where[] = 'status = :status';
    $params[':status'] = $statusFilter;
}
if ($positionFilter !== '') {
    $where[] = 'position LIKE :position';
    $params[':position'] = '%' . $positionFilter . '%';
}

$sql = 'SELECT * FROM employees';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC, id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

$stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
try {
    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
    $stats['active'] = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='active'")->fetchColumn();
    $stats['inactive'] = $stats['total'] - $stats['active'];
} catch (Throwable $ignored) {
}

$positions = [];
try {
    $positions = $pdo->query("SELECT DISTINCT COALESCE(position, '') AS position FROM employees ORDER BY position ASC")->fetchAll();
} catch (Throwable $ignored) {
}

$editEmployee = null;
if ($editId > 0) {
    $s = $pdo->prepare('SELECT * FROM employees WHERE id = ? LIMIT 1');
    $s->execute([$editId]);
    $editEmployee = $s->fetch() ?: null;
}

$alertMap = [
    'added' => ['success', 'Đã thêm nhân viên mới.'],
    'updated' => ['info', 'Đã cập nhật thông tin nhân viên.'],
    'deleted' => ['warning', 'Đã xóa nhân viên.'],
    'duplicate' => ['danger', 'Ma nhân viên hoặc email đã tồn tại.'],
    'invalid_email' => ['danger', 'Email không hợp lệ.'],
    'invalid_data' => ['danger', 'Dữ liệu không hợp lệ.'],
    'db_error' => ['danger', 'Có lỗi CSDL khi xử lý thao tác.'],
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
  .avatar { width:34px; height:34px; border-radius:50%; background:#e2e8f0; color:#334155; display:flex; align-items:center; justify-content:center; font-size:.85rem; font-weight:700; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Quản Lý Nhân Viên</h2>
          <p class="text-secondary small mb-0">Quản lý thông tin, trạng thái và vị trí nhân sự.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['total']; ?></h3><p>Tổng nhân viên</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['active']; ?></h3><p>Đang hoạt động</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['inactive']; ?></h3><p>Tạm nghỉ / đã nghỉ</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <form method="POST" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="<?php echo $editEmployee ? 'edit' : 'add'; ?>">
            <?php if ($editEmployee): ?>
              <input type="hidden" name="employee_id" value="<?php echo (int)$editEmployee['id']; ?>">
            <?php endif; ?>

            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Ma NV</label>
              <input type="text" name="code" class="form-control bg-light border-0"
                     value="<?php echo htmlspecialchars((string)($editEmployee['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                     placeholder="<?php echo htmlspecialchars(nextEmployeeCode($pdo), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Họ tên</label>
              <input type="text" name="full_name" class="form-control bg-light border-0" required
                     value="<?php echo htmlspecialchars((string)($editEmployee['full_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Email</label>
              <input type="email" name="email" class="form-control bg-light border-0" required
                     value="<?php echo htmlspecialchars((string)($editEmployee['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Số điện thoại</label>
              <input type="text" name="phone" class="form-control bg-light border-0"
                     value="<?php echo htmlspecialchars((string)($editEmployee['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold text-secondary">Trạng thái</label>
              <select name="status" class="form-select bg-light border-0">
                <option value="active" <?php echo (!$editEmployee || ($editEmployee['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Hoat động</option>
                <option value="inactive" <?php echo ($editEmployee && ($editEmployee['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Không hoạt động</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-secondary">Vị trí</label>
              <input type="text" name="position" class="form-control bg-light border-0" placeholder="Ví dụ: Bán hàng, Kỹ thuật"
                     value="<?php echo htmlspecialchars((string)($editEmployee['position'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-12 d-flex gap-2">
              <button type="submit" class="btn btn-primary fw-bold px-4"><?php echo $editEmployee ? 'Lưu cập nhật' : 'Thêm nhân viên'; ?></button>
              <?php if ($editEmployee): ?>
                <a href="employees.php" class="btn btn-light border fw-semibold">Hủy sửa</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Tìm theo mã, tên, email, SĐT..."
                     value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select bg-light border-0">
                <option value="">Tất cả trạng thái</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Hoat động</option>
                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
              </select>
            </div>
            <div class="col-md-3">
              <select name="position" class="form-select bg-light border-0">
                <option value="">Tất cả vị trí</option>
                <?php foreach ($positions as $pos): $p = trim((string)$pos['position']); if ($p === '') { continue; } ?>
                  <option value="<?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $positionFilter === $p ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary fw-semibold">Lọc</button>
              <a href="employees.php" class="btn btn-outline-secondary fw-semibold">Đặt lại</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                  <th>Ma NV</th>
                  <th>Họ tên</th>
                  <th>Liên hệ</th>
                  <th>Vị trí</th>
                  <th>Trạng thái</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$employees): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu nhân viên.</td></tr>
                <?php else: ?>
                  <?php foreach ($employees as $e): ?>
                    <tr>
                      <td class="fw-semibold"><?php echo htmlspecialchars((string)$e['code'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="avatar"><?php echo htmlspecialchars(strtoupper(substr((string)$e['full_name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></div>
                          <span class="fw-semibold"><?php echo htmlspecialchars((string)$e['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                      </td>
                      <td>
                        <div><?php echo htmlspecialchars((string)$e['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string)($e['phone'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></small>
                      </td>
                      <td><?php echo htmlspecialchars((string)($e['position'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <?php if (($e['status'] ?? '') === 'active'): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle">Hoat động</span>
                        <?php else: ?>
                          <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Không hoạt động</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="employees.php?edit=<?php echo (int)$e['id']; ?>">Sửa</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xóa nhân viên nay?');">
                          <input type="hidden" name="action" value="delete">
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
</body>
</html>


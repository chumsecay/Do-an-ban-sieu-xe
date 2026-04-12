<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'brands';
$pageTitle = 'Quan ly hang';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectWithMsg(string $msg): void
{
    header('Location: brands.php?msg=' . urlencode($msg));
    exit;
}

function slugifyBrand(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return 'brand-' . time();
    }

    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if (is_string($converted) && $converted !== '') {
        $value = $converted;
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'brand-' . time();
}

function uniqueBrandSlug(PDO $pdo, string $baseSlug, ?int $excludeId = null): string
{
    $slug = $baseSlug;
    $i = 1;
    while (true) {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT id FROM brands WHERE slug = ? AND id <> ? LIMIT 1');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM brands WHERE slug = ? LIMIT 1');
            $stmt->execute([$slug]);
        }
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $i;
        $i++;
    }
}

$msg = (string)($_GET['msg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add') {
            $name = trim((string)($_POST['name'] ?? ''));
            $country = trim((string)($_POST['country'] ?? ''));
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $rawSlug = trim((string)($_POST['slug'] ?? ''));

            if ($name === '') {
                redirectWithMsg('invalid_name');
            }

            $baseSlug = slugifyBrand($rawSlug !== '' ? $rawSlug : $name);
            $slug = uniqueBrandSlug($pdo, $baseSlug);

            $stmt = $pdo->prepare('INSERT INTO brands (name, slug, country, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $slug, $country !== '' ? $country : null, $isActive]);
            redirectWithMsg('added');
        }

        if ($action === 'edit') {
            $id = (int)($_POST['brand_id'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            $country = trim((string)($_POST['country'] ?? ''));
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $rawSlug = trim((string)($_POST['slug'] ?? ''));

            if ($id <= 0 || $name === '') {
                redirectWithMsg('invalid_data');
            }

            $baseSlug = slugifyBrand($rawSlug !== '' ? $rawSlug : $name);
            $slug = uniqueBrandSlug($pdo, $baseSlug, $id);

            $stmt = $pdo->prepare('UPDATE brands SET name = ?, slug = ?, country = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $slug, $country !== '' ? $country : null, $isActive, $id]);
            redirectWithMsg('updated');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['brand_id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM brands WHERE id = ?');
                $stmt->execute([$id]);
                redirectWithMsg('deleted');
            }
            redirectWithMsg('invalid_data');
        }
    } catch (Throwable $e) {
        if ($action === 'delete') {
            redirectWithMsg('delete_blocked');
        }
        redirectWithMsg('db_error');
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$status = (string)($_GET['status'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(b.name LIKE :q OR b.slug LIKE :q OR b.country LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($status === '1' || $status === '0') {
    $where[] = 'b.is_active = :status';
    $params[':status'] = (int)$status;
}

$sql = '
    SELECT b.*,
           (SELECT COUNT(*) FROM cars c WHERE c.brand_id = b.id) AS car_count
    FROM brands b
';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY b.created_at DESC, b.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$brands = $stmt->fetchAll();

$stats = [
    'total' => 0,
    'active' => 0,
    'inactive' => 0,
];
try {
    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM brands')->fetchColumn();
    $stats['active'] = (int)$pdo->query('SELECT COUNT(*) FROM brands WHERE is_active = 1')->fetchColumn();
    $stats['inactive'] = $stats['total'] - $stats['active'];
} catch (Throwable $ignored) {
}

$editBrand = null;
if ($editId > 0) {
    $s = $pdo->prepare('SELECT * FROM brands WHERE id = ? LIMIT 1');
    $s->execute([$editId]);
    $editBrand = $s->fetch() ?: null;
}

$alertMap = [
    'added' => ['success', 'Da them hang moi.'],
    'updated' => ['info', 'Da cap nhat thong tin hang.'],
    'deleted' => ['warning', 'Da xoa hang xe.'],
    'delete_blocked' => ['danger', 'Khong the xoa hang dang duoc gan cho xe.'],
    'invalid_name' => ['danger', 'Ten hang khong hop le.'],
    'invalid_data' => ['danger', 'Du lieu gui len khong hop le.'],
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
  body { font-family: 'Inter', sans-serif !important; background: #f8fafc; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Quan Ly Hang Xe</h2>
          <p class="text-secondary small mb-0">Them, sua, xoa va quan ly trang thai hang xe.</p>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['total']; ?></h3><p>Tong so hang</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['active']; ?></h3><p>Dang hoat dong</p></div></div>
        <div class="col-md-4"><div class="mini-stat"><h3><?php echo $stats['inactive']; ?></h3><p>Tam dung</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body">
          <form method="POST" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="<?php echo $editBrand ? 'edit' : 'add'; ?>">
            <?php if ($editBrand): ?>
              <input type="hidden" name="brand_id" value="<?php echo (int)$editBrand['id']; ?>">
            <?php endif; ?>

            <div class="col-md-4">
              <label class="form-label small fw-bold text-secondary">Ten hang</label>
              <input type="text" name="name" class="form-control bg-light border-0" required
                     value="<?php echo htmlspecialchars((string)($editBrand['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Slug (tuy chon)</label>
              <input type="text" name="slug" class="form-control bg-light border-0"
                     value="<?php echo htmlspecialchars((string)($editBrand['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-secondary">Quoc gia</label>
              <input type="text" name="country" class="form-control bg-light border-0"
                     value="<?php echo htmlspecialchars((string)($editBrand['country'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="activeCheck"
                       <?php echo $editBrand ? ((int)$editBrand['is_active'] === 1 ? 'checked' : '') : 'checked'; ?>>
                <label class="form-check-label small fw-semibold" for="activeCheck">Dang hoat dong</label>
              </div>
            </div>

            <div class="col-12 d-flex gap-2">
              <button type="submit" class="btn btn-primary fw-bold px-4">
                <?php echo $editBrand ? 'Luu cap nhat' : 'Them hang moi'; ?>
              </button>
              <?php if ($editBrand): ?>
                <a href="brands.php" class="btn btn-light border fw-semibold">Huy sua</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Tim theo ten, slug, quoc gia..."
                     value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select bg-light border-0">
                <option value="">Tat ca trang thai</option>
                <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Dang hoat dong</option>
                <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Tam dung</option>
              </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
              <button class="btn btn-outline-primary fw-semibold" type="submit">Loc</button>
              <a href="brands.php" class="btn btn-outline-secondary fw-semibold">Xoa loc</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                  <th>Ten hang</th>
                  <th>Slug</th>
                  <th>Quoc gia</th>
                  <th>So xe</th>
                  <th>Trang thai</th>
                  <th class="text-end">Thao tac</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$brands): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">Khong co du lieu hang xe.</td></tr>
                <?php else: ?>
                  <?php foreach ($brands as $b): ?>
                    <tr>
                      <td class="fw-semibold"><?php echo htmlspecialchars((string)$b['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><code><?php echo htmlspecialchars((string)$b['slug'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                      <td><?php echo htmlspecialchars((string)($b['country'] ?? '---'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo (int)$b['car_count']; ?></td>
                      <td>
                        <?php if ((int)$b['is_active'] === 1): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle">Hoat dong</span>
                        <?php else: ?>
                          <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Tam dung</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="brands.php?edit=<?php echo (int)$b['id']; ?>">Sua</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xoa hang nay?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="brand_id" value="<?php echo (int)$b['id']; ?>">
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

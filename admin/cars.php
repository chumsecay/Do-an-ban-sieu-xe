<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'cars';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();
$msg = (string)($_GET['msg'] ?? '');

function redirectCars(string $msg = ''): void
{
    $url = 'cars.php';
    if ($msg !== '') {
        $url .= '?msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit;
}

function slugifyText(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return 'item-' . time();
    }

    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if (is_string($converted) && $converted !== '') {
        $value = $converted;
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'item-' . time();
}

function uniqueSlug(PDO $pdo, string $table, string $baseSlug, ?int $excludeId = null): string
{
    $slug = $baseSlug;
    $i = 1;

    while (true) {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug = ? AND id <> ? LIMIT 1");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
        }
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $i;
        $i++;
    }
}

function normalizeImageSource(string $value): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $value)) {
        return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }
    if (str_starts_with($value, '/')) {
        return $value;
    }
    return null;
}

function uploadCarImage(array $file): array
{
    $uploadError = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'code' => 'no_file', 'web' => null, 'fs' => null];
    }
    if ($uploadError !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'code' => 'upload_failed', 'web' => null, 'fs' => null];
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return ['ok' => false, 'code' => 'upload_failed', 'web' => null, 'fs' => null];
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > 5 * 1024 * 1024) {
        return ['ok' => false, 'code' => 'too_large', 'web' => null, 'fs' => null];
    }

    $mimeMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($tmpName);
    if (!isset($mimeMap[$mime]) || @getimagesize($tmpName) === false) {
        return ['ok' => false, 'code' => 'invalid_type', 'web' => null, 'fs' => null];
    }

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cars';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return ['ok' => false, 'code' => 'dir_unavailable', 'web' => null, 'fs' => null];
    }
    if (!is_writable($uploadDir)) {
        return ['ok' => false, 'code' => 'dir_not_writable', 'web' => null, 'fs' => null];
    }

    try {
        $rand = bin2hex(random_bytes(8));
    } catch (Throwable $ignored) {
        $rand = substr(md5(uniqid('', true)), 0, 16);
    }

    $filename = 'car-' . date('YmdHis') . '-' . $rand . '.' . $mimeMap[$mime];
    $targetFs = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $targetFs)) {
        return ['ok' => false, 'code' => 'move_failed', 'web' => null, 'fs' => null];
    }

    return ['ok' => true, 'code' => '', 'web' => '/uploads/cars/' . $filename, 'fs' => $targetFs];
}

function removeLocalCarImage(string $url): void
{
    if (!str_starts_with($url, '/uploads/cars/')) {
        return;
    }

    $relative = str_replace('/', DIRECTORY_SEPARATOR, ltrim($url, '/'));
    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relative;
    if (is_file($file)) {
        @unlink($file);
    }
}

function ensureExistingBrand(PDO $pdo, int $brandId): int
{
    $check = $pdo->prepare('SELECT id FROM brands WHERE id = ? LIMIT 1');
    $check->execute([$brandId]);
    if ($check->fetch()) {
        return $brandId;
    }

    $fallback = (int)$pdo->query('SELECT id FROM brands ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($fallback > 0) {
        return $fallback;
    }

    $slug = uniqueSlug($pdo, 'brands', 'khac');
    $pdo->prepare('INSERT INTO brands (name, slug, is_active) VALUES (?, ?, 1)')->execute(['Khac', $slug]);
    return (int)$pdo->lastInsertId();
}

function ensureExistingCategory(PDO $pdo, int $categoryId): int
{
    $check = $pdo->prepare('SELECT id FROM car_categories WHERE id = ? LIMIT 1');
    $check->execute([$categoryId]);
    if ($check->fetch()) {
        return $categoryId;
    }

    $fallback = (int)$pdo->query('SELECT id FROM car_categories ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($fallback > 0) {
        return $fallback;
    }

    $pdo->prepare('INSERT INTO car_categories (name, slug, is_active) VALUES (?, ?, 1)')->execute(['Khac', 'khac']);
    return (int)$pdo->lastInsertId();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'delete_car') {
            $id = (int)($_POST['car_id'] ?? 0);
            if ($id <= 0) {
                redirectCars('car_invalid_data');
            }

            $imgStmt = $pdo->prepare('SELECT image_url FROM car_images WHERE car_id = ?');
            $imgStmt->execute([$id]);
            $images = $imgStmt->fetchAll();

            $pdo->prepare('DELETE FROM cars WHERE id = ?')->execute([$id]);
            foreach ($images as $img) {
                $url = (string)($img['image_url'] ?? '');
                if ($url !== '') {
                    removeLocalCarImage($url);
                }
            }
            redirectCars('car_deleted');
        }

        if ($action === 'add_car') {
            $name = trim((string)($_POST['name'] ?? ''));
            $year = (int)($_POST['year'] ?? date('Y'));
            $price = (float)($_POST['price'] ?? 0);
            $status = (string)($_POST['status'] ?? 'available');
            $brandId = ensureExistingBrand($pdo, (int)($_POST['brand_id'] ?? 0));
            $categoryId = ensureExistingCategory($pdo, (int)($_POST['category_id'] ?? 0));
            $description = trim((string)($_POST['description'] ?? ''));
            $imageUrlInput = trim((string)($_POST['image_url'] ?? ''));

            if ($name === '') {
                redirectCars('car_invalid_name');
            }
            if (!in_array($status, ['available', 'reserved', 'sold'], true)) {
                $status = 'available';
            }
            if ($year < 1900 || $year > ((int)date('Y') + 2)) {
                $year = (int)date('Y');
            }
            if ($price < 0) {
                $price = 0;
            }

            $uploaded = uploadCarImage($_FILES['image_file'] ?? []);
            if (!$uploaded['ok'] && $uploaded['code'] !== 'no_file') {
                redirectCars('car_' . $uploaded['code']);
            }

            $urlImage = null;
            if ($imageUrlInput !== '') {
                $urlImage = normalizeImageSource($imageUrlInput);
                if ($urlImage === null && !$uploaded['ok']) {
                    redirectCars('car_invalid_image_url');
                }
            }
            $image = $uploaded['ok'] ? (string)$uploaded['web'] : $urlImage;

            do {
                try {
                    $rand = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
                } catch (Throwable $ignored) {
                    $rand = strtoupper(substr(md5(uniqid('', true)), 0, 6));
                }
                $code = 'XE-' . $rand;
                $checkCode = $pdo->prepare('SELECT id FROM cars WHERE code = ? LIMIT 1');
                $checkCode->execute([$code]);
            } while ($checkCode->fetch());
            $slug = uniqueSlug($pdo, 'cars', slugifyText($name));

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('INSERT INTO cars (code, name, slug, brand_id, category_id, model_year, price, status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $code,
                    $name,
                    $slug,
                    $brandId,
                    $categoryId,
                    $year,
                    $price,
                    $status,
                    $description !== '' ? $description : null,
                ]);
                $carId = (int)$pdo->lastInsertId();

                if ($image !== null && $image !== '') {
                    $imgStmt = $pdo->prepare('INSERT INTO car_images (car_id, image_url, alt_text, is_cover) VALUES (?, ?, ?, 1)');
                    $imgStmt->execute([$carId, $image, $name]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($uploaded['ok'] && is_string($uploaded['fs']) && is_file($uploaded['fs'])) {
                    @unlink($uploaded['fs']);
                }
                throw $e;
            }

            redirectCars($image ? 'car_added_with_image' : 'car_added');
        }

        if ($action === 'add_brand') {
            $name = trim((string)($_POST['brand_name'] ?? ''));
            $slugRaw = trim((string)($_POST['brand_slug'] ?? ''));
            $country = trim((string)($_POST['brand_country'] ?? ''));
            $isActive = isset($_POST['brand_is_active']) ? 1 : 0;
            if ($name === '') {
                redirectCars('brand_invalid_name');
            }

            $baseSlug = slugifyText($slugRaw !== '' ? $slugRaw : $name);
            $slug = uniqueSlug($pdo, 'brands', $baseSlug);
            $stmt = $pdo->prepare('INSERT INTO brands (name, slug, country, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $slug, $country !== '' ? $country : null, $isActive]);
            redirectCars('brand_added');
        }

        if ($action === 'edit_brand') {
            $brandId = (int)($_POST['brand_id'] ?? 0);
            $name = trim((string)($_POST['brand_name'] ?? ''));
            $slugRaw = trim((string)($_POST['brand_slug'] ?? ''));
            $country = trim((string)($_POST['brand_country'] ?? ''));
            $isActive = isset($_POST['brand_is_active']) ? 1 : 0;
            if ($brandId <= 0 || $name === '') {
                redirectCars('brand_invalid_data');
            }

            $baseSlug = slugifyText($slugRaw !== '' ? $slugRaw : $name);
            $slug = uniqueSlug($pdo, 'brands', $baseSlug, $brandId);
            $stmt = $pdo->prepare('UPDATE brands SET name = ?, slug = ?, country = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $slug, $country !== '' ? $country : null, $isActive, $brandId]);
            redirectCars('brand_updated');
        }

        if ($action === 'delete_brand') {
            $brandId = (int)($_POST['brand_id'] ?? 0);
            if ($brandId <= 0) {
                redirectCars('brand_invalid_data');
            }
            try {
                $stmt = $pdo->prepare('DELETE FROM brands WHERE id = ?');
                $stmt->execute([$brandId]);
                redirectCars('brand_deleted');
            } catch (Throwable $e) {
                redirectCars('brand_delete_blocked');
            }
        }
    } catch (Throwable $e) {
        redirectCars('db_error');
    }
}

$filterQ = trim((string)($_GET['q'] ?? ''));
$filterStatus = (string)($_GET['status'] ?? '');
$filterBrand = (int)($_GET['brand'] ?? 0);
$filterCategory = (int)($_GET['category'] ?? 0);
$filterYearFrom = ($_GET['year_from'] ?? '') !== '' ? (int)$_GET['year_from'] : null;
$filterYearTo = ($_GET['year_to'] ?? '') !== '' ? (int)$_GET['year_to'] : null;
$filterPriceMin = ($_GET['price_min'] ?? '') !== '' ? (float)$_GET['price_min'] : null;
$filterPriceMax = ($_GET['price_max'] ?? '') !== '' ? (float)$_GET['price_max'] : null;
$brandEdit = (int)($_GET['brand_edit'] ?? 0);

if (!in_array($filterStatus, ['available', 'reserved', 'sold'], true)) {
    $filterStatus = '';
}
if ($filterYearFrom !== null && $filterYearTo !== null && $filterYearFrom > $filterYearTo) {
    [$filterYearFrom, $filterYearTo] = [$filterYearTo, $filterYearFrom];
}
if ($filterPriceMin !== null && $filterPriceMax !== null && $filterPriceMin > $filterPriceMax) {
    [$filterPriceMin, $filterPriceMax] = [$filterPriceMax, $filterPriceMin];
}
$where = ['1=1'];
$params = [];
if ($filterQ !== '') {
    $where[] = '(c.code LIKE :q OR c.name LIKE :q OR b.name LIKE :q OR cat.name LIKE :q)';
    $params[':q'] = '%' . $filterQ . '%';
}
if ($filterStatus !== '') {
    $where[] = 'c.status = :status';
    $params[':status'] = $filterStatus;
}
if ($filterBrand > 0) {
    $where[] = 'c.brand_id = :brand_id';
    $params[':brand_id'] = $filterBrand;
}
if ($filterCategory > 0) {
    $where[] = 'c.category_id = :category_id';
    $params[':category_id'] = $filterCategory;
}
if ($filterYearFrom !== null) {
    $where[] = 'c.model_year >= :year_from';
    $params[':year_from'] = $filterYearFrom;
}
if ($filterYearTo !== null) {
    $where[] = 'c.model_year <= :year_to';
    $params[':year_to'] = $filterYearTo;
}
if ($filterPriceMin !== null) {
    $where[] = 'c.price >= :price_min';
    $params[':price_min'] = $filterPriceMin;
}
if ($filterPriceMax !== null) {
    $where[] = 'c.price <= :price_max';
    $params[':price_max'] = $filterPriceMax;
}

try {
    $carsSql = "
        SELECT c.*, b.name as brand_name, cat.name as category_name,
               (SELECT image_url FROM car_images ci WHERE ci.car_id = c.id AND ci.is_cover = 1 ORDER BY ci.id ASC LIMIT 1) as cover_image
        FROM cars c
        LEFT JOIN brands b ON c.brand_id = b.id
        LEFT JOIN car_categories cat ON c.category_id = cat.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY c.updated_at DESC, c.id DESC
    ";
    $carsStmt = $pdo->prepare($carsSql);
    $carsStmt->execute($params);
    $cars = $carsStmt->fetchAll();

    $brands = $pdo->query('SELECT * FROM brands ORDER BY name ASC')->fetchAll();
    $categories = $pdo->query('SELECT * FROM car_categories ORDER BY name ASC')->fetchAll();

    $brandRows = $pdo->query("
        SELECT b.*, (SELECT COUNT(*) FROM cars c WHERE c.brand_id = b.id) AS car_count
        FROM brands b
        ORDER BY b.name ASC
    ")->fetchAll();

    $stats = [
        'cars_total' => (int)$pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn(),
        'cars_available' => (int)$pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'available'")->fetchColumn(),
        'cars_reserved' => (int)$pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'reserved'")->fetchColumn(),
        'cars_sold' => (int)$pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'sold'")->fetchColumn(),
        'brands_total' => (int)$pdo->query('SELECT COUNT(*) FROM brands')->fetchColumn(),
    ];

    $editingBrand = null;
    if ($brandEdit > 0) {
        $s = $pdo->prepare('SELECT * FROM brands WHERE id = ? LIMIT 1');
        $s->execute([$brandEdit]);
        $editingBrand = $s->fetch() ?: null;
    }
} catch (Throwable $e) {
    $cars = [];
    $brands = [];
    $categories = [];
    $brandRows = [];
    $editingBrand = null;
    $stats = ['cars_total' => 0, 'cars_available' => 0, 'cars_reserved' => 0, 'cars_sold' => 0, 'brands_total' => 0];
}

$alertMap = [
    'car_added' => ['success', 'Da them xe moi.'],
    'car_added_with_image' => ['success', 'Da them xe moi va da gan anh.'],
    'car_deleted' => ['warning', 'Da xoa xe.'],
    'car_invalid_name' => ['danger', 'Ten xe khong hop le.'],
    'car_invalid_data' => ['danger', 'Du lieu xe khong hop le.'],
    'car_invalid_image_url' => ['danger', 'Link anh khong hop le.'],
    'car_upload_failed' => ['danger', 'Upload anh that bai.'],
    'car_too_large' => ['danger', 'Anh upload vuot qua 5MB.'],
    'car_invalid_type' => ['danger', 'File anh khong dung dinh dang cho phep.'],
    'car_dir_unavailable' => ['danger', 'Khong tao duoc thu muc uploads/cars.'],
    'car_dir_not_writable' => ['danger', 'Thu muc uploads/cars khong co quyen ghi.'],
    'car_move_failed' => ['danger', 'Khong luu duoc file anh.'],
    'brand_added' => ['success', 'Da them hang xe moi.'],
    'brand_updated' => ['info', 'Da cap nhat hang xe.'],
    'brand_deleted' => ['warning', 'Da xoa hang xe.'],
    'brand_invalid_name' => ['danger', 'Ten hang khong hop le.'],
    'brand_invalid_data' => ['danger', 'Du lieu hang xe khong hop le.'],
    'brand_delete_blocked' => ['danger', 'Khong the xoa hang vi dang co xe su dung.'],
    'db_error' => ['danger', 'Co loi CSDL khi xu ly thao tac.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quan ly xe va hang - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?> Admin</title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }
  .table > :not(caption) > * > * { padding: 14px 12px; border-bottom-color: #f1f5f9; vertical-align: middle; }
  .mini-stat { background:#fff; border-radius:12px; padding:14px 16px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
  .mini-stat h3 { margin:0; font-size:1.2rem; font-weight:800; }
  .mini-stat p { margin:2px 0 0; color:#64748b; font-size:.78rem; }
  .btn-action { background: none; border: none; padding: 5px 9px; border-radius: 6px; font-weight: 600; font-size: 0.82rem; transition: 0.2s; margin-left: 4px; text-decoration: none; display: inline-block; }
  .btn-action.edit { color: #0284c7; background: #e0f2fe; }
  .btn-action.edit:hover { background: #bae6fd; color: #075985; }
  .btn-action.delete { color: #ef4444; background: #fee2e2; }
  .btn-action.delete:hover { background: #fecaca; }
  .thumb { width:60px; height:44px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0; }
</style>
</head>
<body>

<div class="admin-wrapper" id="adminWrapper">
  <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

    <main class="admin-content p-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h2 class="h4 fw-bold text-dark mb-1">Quan ly xe va hang xe</h2>
          <p class="text-secondary small mb-0">Them/sua/xoa xe, quan ly hang xe, loc thong tin xe va them hinh qua CDN URL hoac upload.</p>
        </div>
        <button class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCarModal">
          <i class="bi bi-plus-circle me-1"></i> Them xe moi
        </button>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['cars_total']; ?></h3><p>Tong so xe</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['cars_available']; ?></h3><p>San hang</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['cars_reserved']; ?></h3><p>Dang dat coc</p></div></div>
        <div class="col-md-3 col-6"><div class="mini-stat"><h3><?php echo $stats['brands_total']; ?></h3><p>So hang xe</p></div></div>
      </div>

      <?php if ($msg !== '' && isset($alertMap[$msg])): $alert = $alertMap[$msg]; ?>
        <div class="alert alert-<?php echo $alert[0]; ?> border-0 shadow-sm" style="border-radius: 12px;"><?php echo $alert[1]; ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body">
          <form method="GET" class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
              <label class="form-label text-secondary small fw-bold">Tu khoa</label>
              <input type="text" name="q" class="form-control bg-light border-0" placeholder="Ma xe, ten xe, hang, dong xe..." value="<?php echo htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-lg-2 col-md-6">
              <label class="form-label text-secondary small fw-bold">Trang thai</label>
              <select name="status" class="form-select bg-light border-0">
                <option value="">Tat ca</option>
                <option value="available" <?php echo $filterStatus === 'available' ? 'selected' : ''; ?>>San hang</option>
                <option value="reserved" <?php echo $filterStatus === 'reserved' ? 'selected' : ''; ?>>Dat coc</option>
                <option value="sold" <?php echo $filterStatus === 'sold' ? 'selected' : ''; ?>>Da ban</option>
              </select>
            </div>
            <div class="col-lg-2 col-md-6">
              <label class="form-label text-secondary small fw-bold">Hang xe</label>
              <select name="brand" class="form-select bg-light border-0">
                <option value="">Tat ca hang</option>
                <?php foreach ($brands as $b): ?>
                  <option value="<?php echo (int)$b['id']; ?>" <?php echo $filterBrand === (int)$b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$b['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-lg-2 col-md-6">
              <label class="form-label text-secondary small fw-bold">Dong xe</label>
              <select name="category" class="form-select bg-light border-0">
                <option value="">Tat ca dong</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo (int)$cat['id']; ?>" <?php echo $filterCategory === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$cat['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-lg-1 col-md-6">
              <label class="form-label text-secondary small fw-bold">Nam tu</label>
              <input type="number" name="year_from" class="form-control bg-light border-0" value="<?php echo $filterYearFrom !== null ? (int)$filterYearFrom : ''; ?>">
            </div>
            <div class="col-lg-1 col-md-6">
              <label class="form-label text-secondary small fw-bold">Nam den</label>
              <input type="number" name="year_to" class="form-control bg-light border-0" value="<?php echo $filterYearTo !== null ? (int)$filterYearTo : ''; ?>">
            </div>
            <div class="col-lg-2 col-md-6">
              <label class="form-label text-secondary small fw-bold">Gia tu ($)</label>
              <input type="number" step="0.01" min="0" name="price_min" class="form-control bg-light border-0" value="<?php echo $filterPriceMin !== null ? htmlspecialchars((string)$filterPriceMin, ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>
            <div class="col-lg-2 col-md-6">
              <label class="form-label text-secondary small fw-bold">Gia den ($)</label>
              <input type="number" step="0.01" min="0" name="price_max" class="form-control bg-light border-0" value="<?php echo $filterPriceMax !== null ? htmlspecialchars((string)$filterPriceMax, ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>
            <div class="col-lg-4 col-md-12 d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary fw-semibold"><i class="bi bi-funnel me-1"></i> Loc du lieu</button>
              <a href="cars.php" class="btn btn-outline-secondary fw-semibold">Xoa loc</a>
            </div>
          </form>
        </div>
      </div>
      <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <th class="ps-4">Ma xe</th>
                  <th>Xe</th>
                  <th>Hang</th>
                  <th>Dong</th>
                  <th>Nam</th>
                  <th>Gia</th>
                  <th>Trang thai</th>
                  <th class="text-end pe-4">Thao tac</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($cars) === 0): ?>
                  <tr><td colspan="8" class="text-center py-5 text-muted">Khong co xe nao phu hop bo loc.</td></tr>
                <?php else: ?>
                  <?php foreach ($cars as $c):
                    $imgSrc = !empty($c['cover_image']) ? htmlspecialchars((string)$c['cover_image'], ENT_QUOTES, 'UTF-8') : '../img/bmwx5.jpg';
                  ?>
                  <tr>
                    <td class="ps-4 fw-bold text-secondary"><?php echo htmlspecialchars((string)$c['code'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?php echo $imgSrc; ?>" alt="car" class="thumb">
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                      </div>
                    </td>
                    <td class="text-secondary fw-medium"><?php echo htmlspecialchars((string)($c['brand_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="text-secondary fw-medium"><?php echo htmlspecialchars((string)($c['category_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="fw-semibold"><?php echo (int)$c['model_year']; ?></td>
                    <td class="fw-bold text-dark fs-6">$<?php echo number_format((float)$c['price']); ?></td>
                    <td>
                      <?php if ($c['status'] === 'sold'): ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Da ban</span>
                      <?php elseif ($c['status'] === 'reserved'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Dat coc</span>
                      <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">San hang</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                      <a href="car-edit.php?id=<?php echo (int)$c['id']; ?>" class="btn-action edit">Sua</a>
                      <form method="POST" class="d-inline" onsubmit="return confirm('Ban chac chan muon xoa xe nay?');">
                        <input type="hidden" name="action" value="delete_car">
                        <input type="hidden" name="car_id" value="<?php echo (int)$c['id']; ?>">
                        <button type="submit" class="btn-action delete">Xoa</button>
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

      <div id="brands-section" class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h5 class="mb-1 fw-bold">Quan ly hang xe</h5>
              <p class="text-secondary small mb-0">Them, sua, xoa hang xe ngay tai trang quan ly xe.</p>
            </div>
          </div>

          <form method="POST" class="row g-3 align-items-end mb-4">
            <input type="hidden" name="action" value="<?php echo $editingBrand ? 'edit_brand' : 'add_brand'; ?>">
            <?php if ($editingBrand): ?>
              <input type="hidden" name="brand_id" value="<?php echo (int)$editingBrand['id']; ?>">
            <?php endif; ?>

            <div class="col-lg-4 col-md-6">
              <label class="form-label text-secondary small fw-bold">Ten hang</label>
              <input type="text" name="brand_name" class="form-control bg-light border-0" required value="<?php echo htmlspecialchars((string)($editingBrand['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-lg-3 col-md-6">
              <label class="form-label text-secondary small fw-bold">Slug (tuy chon)</label>
              <input type="text" name="brand_slug" class="form-control bg-light border-0" value="<?php echo htmlspecialchars((string)($editingBrand['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-lg-3 col-md-6">
              <label class="form-label text-secondary small fw-bold">Quoc gia</label>
              <input type="text" name="brand_country" class="form-control bg-light border-0" value="<?php echo htmlspecialchars((string)($editingBrand['country'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-lg-2 col-md-6">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="brand_is_active" id="brandActiveCheck" <?php echo $editingBrand ? ((int)$editingBrand['is_active'] === 1 ? 'checked' : '') : 'checked'; ?>>
                <label class="form-check-label small fw-semibold" for="brandActiveCheck">Dang hoat dong</label>
              </div>
            </div>
            <div class="col-12 d-flex gap-2">
              <button type="submit" class="btn btn-primary fw-bold px-4"><?php echo $editingBrand ? 'Luu cap nhat hang' : 'Them hang moi'; ?></button>
              <?php if ($editingBrand): ?>
                <a href="cars.php#brands-section" class="btn btn-light border fw-semibold">Huy sua</a>
              <?php endif; ?>
            </div>
          </form>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem; letter-spacing:.5px;">
                  <th>Ten hang</th>
                  <th>Slug</th>
                  <th>Quoc gia</th>
                  <th>So xe</th>
                  <th>Trang thai</th>
                  <th class="text-end">Thao tac</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$brandRows): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">Chua co du lieu hang xe.</td></tr>
                <?php else: ?>
                  <?php foreach ($brandRows as $b): ?>
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
                        <a href="cars.php?brand_edit=<?php echo (int)$b['id']; ?>#brands-section" class="btn btn-sm btn-outline-primary">Sua</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Xoa hang nay?');">
                          <input type="hidden" name="action" value="delete_brand">
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

<div class="modal fade" id="addCarModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 16px;">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_car">
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-dark">Them xe moi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label text-secondary small fw-bold">Ten model xe</label>
              <input type="text" name="name" class="form-control bg-light border-0" required>
            </div>
            <div class="col-md-4">
              <label class="form-label text-secondary small fw-bold">Nam san xuat</label>
              <input type="number" name="year" class="form-control bg-light border-0" value="<?php echo date('Y'); ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label text-secondary small fw-bold">Gia niem yet ($)</label>
              <input type="number" name="price" min="0" step="0.01" class="form-control bg-light border-0" value="0" required>
            </div>
            <div class="col-md-4">
              <label class="form-label text-secondary small fw-bold">Hang xe</label>
              <select name="brand_id" class="form-select bg-light border-0">
                <?php foreach ($brands as $b): ?>
                  <option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars((string)$b['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label text-secondary small fw-bold">Dong xe</label>
              <select name="category_id" class="form-select bg-light border-0">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars((string)$cat['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label text-secondary small fw-bold">Trang thai xe</label>
              <select name="status" class="form-select bg-light border-0">
                <option value="available">San hang</option>
                <option value="reserved">Dat coc</option>
                <option value="sold">Da ban</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label text-secondary small fw-bold">Link anh CDN (tuy chon)</label>
              <input type="url" name="image_url" class="form-control bg-light border-0" placeholder="https://cdn.example.com/car.jpg">
            </div>
            <div class="col-md-4">
              <label class="form-label text-secondary small fw-bold">Hoac upload anh</label>
              <input type="file" name="image_file" class="form-control bg-light border-0" accept="image/*">
            </div>
            <div class="col-12">
              <small class="text-muted">Ban co the dung URL CDN hoac upload file anh. Neu nhap ca hai, he thong uu tien file upload.</small>
            </div>
            <div class="col-12">
              <label class="form-label text-secondary small fw-bold">Mo ta xe</label>
              <textarea name="description" class="form-control bg-light border-0" rows="3" placeholder="Mo ta ngan ve xe..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Huy</button>
          <button type="submit" class="btn btn-primary fw-bold px-4">Luu thong tin</button>
        </div>
      </form>
    </div>
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

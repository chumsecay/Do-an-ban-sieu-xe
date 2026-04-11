<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../config/database.php';
ensureSessionStarted();

$currentPage = 'showroom';
$appName = env('APP_NAME', 'FLCar');

$pdo = getDBConnection();

// Lấy danh sách hãng xe cho dropdown
try {
    $brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $brands = [];
}

// Xây dựng câu lệnh tìm kiếm động
$q       = trim($_GET['q'] ?? '');
$brandId = (int) ($_GET['brand'] ?? 0);
$price   = $_GET['price'] ?? '';

$sql    = "SELECT c.*, b.name AS brand_name, cat.name AS category_name,
           (SELECT image_url FROM car_images ci WHERE ci.car_id = c.id AND ci.is_cover = 1 ORDER BY ci.id ASC LIMIT 1) AS cover_image
           FROM cars c
           LEFT JOIN brands b ON c.brand_id = b.id
           LEFT JOIN car_categories cat ON c.category_id = cat.id
           WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (c.name LIKE ? OR b.name LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($brandId > 0) {
    $sql .= " AND c.brand_id = ?";
    $params[] = $brandId;
}
if ($price === 'under40') {
    $sql .= " AND c.price < 40000";
} elseif ($price === '40to60') {
    $sql .= " AND c.price BETWEEN 40000 AND 60000";
} elseif ($price === '60to100') {
    $sql .= " AND c.price BETWEEN 60000 AND 100000";
} elseif ($price === 'above100') {
    $sql .= " AND c.price > 100000";
}
$sql .= " ORDER BY c.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();
} catch (Exception $e) {
    $cars = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Showroom - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Khám phá bộ sưu tập xe cao cấp tại FLCar Showroom">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <p class="section-label" style="color:rgba(255,255,255,.7);justify-content:center">
      <span style="background:rgba(255,255,255,.4)" class="d-inline-block" role="presentation"></span>
      Bộ sưu tập
    </p>
    <h1 class="display-4 fw-bold">Showroom Xe Cao Cấp</h1>
    <p class="lead" style="opacity:.85">Khám phá bộ sưu tập xe sang mới nhất</p>
  </div>
</section>

<!-- Bộ lọc tìm kiếm -->
<div class="container" style="margin-top:-40px;position:relative;z-index:2;margin-bottom:32px">
  <div style="background:var(--white);padding:24px;border-radius:var(--radius);box-shadow:var(--shadow-md)">
    <form method="GET" action="showroom.php" class="row g-3 align-items-center">
      <div class="col-lg-3 col-md-6">
        <input type="text" name="q" class="form-control" placeholder="Tìm kiếm tên xe..."
          value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"
          style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px">
      </div>
      <div class="col-lg-3 col-md-6">
        <select name="brand" class="form-select" style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px">
          <option value="">Chọn hãng xe</option>
          <?php foreach ($brands as $b): ?>
            <option value="<?php echo $b['id']; ?>" <?php echo $brandId === (int)$b['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($b['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-3 col-md-6">
        <select name="price" class="form-select" style="border-radius:var(--radius-sm);border-color:var(--border);padding:10px 14px">
          <option value="">Mức giá</option>
          <option value="under40"  <?php echo $price === 'under40'  ? 'selected' : ''; ?>>Dưới $40,000</option>
          <option value="40to60"   <?php echo $price === '40to60'   ? 'selected' : ''; ?>>$40,000 - $60,000</option>
          <option value="60to100"  <?php echo $price === '60to100'  ? 'selected' : ''; ?>>$60,000 - $100,000</option>
          <option value="above100" <?php echo $price === 'above100' ? 'selected' : ''; ?>>Trên $100,000</option>
        </select>
      </div>
      <div class="col-lg-3 col-md-6">
        <button type="submit" class="btn btn-primary w-100" style="border-radius:var(--radius-sm);padding:10px;font-weight:600;background:var(--gradient-primary);border:none">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2" style="margin-right:6px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>Tìm kiếm
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Danh sách xe -->
<section style="padding:0 0 80px">
  <div class="container">
    <?php if (count($cars) === 0): ?>
      <div class="text-center py-5">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" class="mb-3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <h4 class="text-muted fw-semibold">Không tìm thấy xe phù hợp</h4>
        <p class="text-secondary">Thử thay đổi bộ lọc hoặc <a href="showroom.php" class="text-primary">xem tất cả xe</a>.</p>
      </div>
    <?php else: ?>
      <p class="text-secondary mb-4 fw-medium">Tìm thấy <strong><?php echo count($cars); ?></strong> chiếc xe</p>
      <div class="row g-4">
        <?php foreach ($cars as $car):
          $img = !empty($car['cover_image']) ? htmlspecialchars($car['cover_image']) : '../img/bmwx5.jpg';
        ?>
        <div class="col-lg-4 col-md-6">
          <div class="card car-card shadow-sm position-relative">
            <span class="badge-type"><?php echo htmlspecialchars($car['category_name'] ?? 'Xe'); ?></span>
            <img src="<?php echo $img; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($car['name']); ?>">
            <div class="card-body">
              <h5 class="fw-bold"><?php echo htmlspecialchars($car['name']); ?></h5>
              <p class="text-muted mb-2"><?php echo htmlspecialchars($car['brand_name'] ?? ''); ?> · <?php echo $car['model_year']; ?></p>
              <div class="d-flex justify-content-between align-items-center">
                <span class="price-tag">$<?php echo number_format($car['price']); ?></span>
                <a href="car-detail.php?id=<?php echo $car['id']; ?>" class="btn btn-dark btn-sm px-3">Chi tiết</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/navbar-shrink.js"></script>
<script src="../js/content-reveal.js"></script>
</body>
</html>

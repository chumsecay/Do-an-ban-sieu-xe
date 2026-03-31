<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../config/database.php';
$currentPage = 'news';
$appName = env('APP_NAME', 'FLCar');

$q = trim($_GET['q'] ?? '');
$newsItems = [];

try {
    $pdo = getDBConnection();
    $sql = "
        SELECT np.id, np.title, np.slug, np.excerpt, np.featured_image, np.published_at, nc.name AS category_name
        FROM news_posts np
        LEFT JOIN news_categories nc ON nc.id = np.category_id
        WHERE np.status = 'published' AND np.published_at IS NOT NULL AND np.published_at <= NOW()
    ";
    $params = [];

    if ($q !== '') {
        $sql .= " AND (np.title LIKE :q OR np.excerpt LIKE :q OR np.content LIKE :q)";
        $params[':q'] = '%' . $q . '%';
    }

    $sql .= " ORDER BY np.published_at DESC, np.id DESC LIMIT 30";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $newsItems = $stmt->fetchAll();
} catch (Throwable $e) {
    $newsItems = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tin Tức & Sự Kiện - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container text-center">
    <p class="section-label" style="justify-content:center; color:rgba(255,255,255,.9)">Tạp Chí Điện Tử</p>
    <h1 class="display-3 fw-bold mb-3">Tin Tức & Sự Kiện</h1>
    <p class="lead opacity-75 mx-auto" style="max-width: 600px;">Cập nhật những thông tin thị trường xe hơi, các bài đánh giá chi tiết và ưu đãi đặc biệt mới nhất từ FLCar.</p>
  </div>
</section>

<section class="py-5" style="background-color: #f8fafc; min-height: 60vh;">
  <div class="container py-4">
    <div class="row justify-content-center mb-5">
      <div class="col-md-8 col-lg-6">
        <form method="GET" action="news.php" class="d-flex gap-2 shadow-sm rounded-3 p-2 bg-white">
          <input type="text" name="q" class="form-control border-0 shadow-none" placeholder="Tìm kiếm tin bài, sự kiện..." value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" style="padding:10px 16px;">
          <button type="submit" class="btn btn-primary px-4" style="border-radius:var(--radius-sm);font-weight:600;background:var(--gradient-primary);border:none">
            <svg viewBox="0 0 24 24" fill="none" width="16" height="16" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            TÌM KIẾM
          </button>
        </form>
      </div>
    </div>

    <div class="row g-4">
      <?php if (empty($newsItems)): ?>
        <div class="col-12">
          <div class="alert alert-light border text-center py-4">Chưa có bài viết phù hợp. Vui lòng thử từ khóa khác hoặc quay lại sau.</div>
        </div>
      <?php else: ?>
        <?php foreach ($newsItems as $news): ?>
        <div class="col-lg-4 col-md-6 mb-2">
          <a href="#" class="text-decoration-none">
            <div class="card news-card h-100 position-relative">
              <span class="news-category"><?php echo htmlspecialchars($news['category_name'] ?: 'Tin tức', ENT_QUOTES, 'UTF-8'); ?></span>
              <img src="<?php echo htmlspecialchars(($news['featured_image'] ?: '../img/hero.jpg'), ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top news-img" alt="<?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?>">
              <div class="news-card-body d-flex flex-column h-100">
                <span class="news-date mb-2 d-inline-block">🗓️ <?php echo !empty($news['published_at']) ? date('d/m/Y', strtotime($news['published_at'])) : '--/--/----'; ?></span>
                <h5 class="news-title"><?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.6;"><?php echo htmlspecialchars($news['excerpt'] ?: 'Đang cập nhật nội dung mô tả...', ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="mt-auto pt-4">
                  <span class="text-primary fw-bold" style="font-size: 0.95rem;">Đọc chi tiết &rarr;</span>
                </div>
              </div>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/navbar-shrink.js"></script>
</body>
</html>

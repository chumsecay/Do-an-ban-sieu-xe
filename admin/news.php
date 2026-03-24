<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../config/database.php';

$adminPage = 'news';
$appName   = env('APP_NAME', 'FLCar');
$pdo       = getDBConnection();
$msg       = $_GET['msg'] ?? '';
$editId    = (int) ($_GET['edit'] ?? 0);

// ── POST HANDLERS ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($_POST['title'] ?? 'bai-viet'));
        $slug .= '-' . time();
        $status = isset($_POST['is_published']) ? 'published' : 'draft';
        $stmt = $pdo->prepare("INSERT INTO news_posts (title, slug, excerpt, content, featured_image, status, published_at) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $_POST['title']   ?? '',
            $slug,
            $_POST['excerpt'] ?? '',
            $_POST['content'] ?? '',
            $_POST['cover']   ?? '',
            $status,
            $status === 'published' ? date('Y-m-d H:i:s') : null
        ]);
        header('Location: news.php?msg=created');
        exit;
    }

    if ($action === 'update') {
        $id = (int) ($_POST['post_id'] ?? 0);
        if ($id) {
            $status = isset($_POST['is_published']) ? 'published' : 'draft';
            $stmt = $pdo->prepare("UPDATE news_posts SET title=?, excerpt=?, content=?, featured_image=?, status=? WHERE id=?");
            $stmt->execute([
                $_POST['title']   ?? '',
                $_POST['excerpt'] ?? '',
                $_POST['content'] ?? '',
                $_POST['cover']   ?? '',
                $status,
                $id
            ]);
        }
        header('Location: news.php?msg=updated');
        exit;
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['post_id'] ?? 0);
        if ($id) { $pdo->prepare("DELETE FROM news_posts WHERE id=?")->execute([$id]); }
        header('Location: news.php?msg=deleted');
        exit;
    }
}

// ── LOAD DATA ─────────────────────────────────────────────────────────────────
try {
    $posts = $pdo->query("SELECT * FROM news_posts ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $posts = [];
}

$editPost = null;
if ($editId > 0) {
    foreach ($posts as $p) { if ((int)$p['id'] === $editId) { $editPost = $p; break; } }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản Lý Tin Tức - <?php echo htmlspecialchars($appName); ?></title>
<link rel="icon" href="../img/logo.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="../css/admin.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif !important; background: #f8fafc; }
  .table > :not(caption) > * > * { padding: 14px 12px; border-bottom-color: #f1f5f9; vertical-align: middle; }
  .btn-action { border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.82rem; transition: 0.2s; margin-left: 4px; }
  .btn-action.edit   { color: #0284c7; background: #e0f2fe; }
  .btn-action.edit:hover { background: #bae6fd; }
  .btn-action.delete { color: #ef4444; background: #fee2e2; }
  .btn-action.delete:hover { background: #fecaca; }
  .news-editor { background:#fff; border-radius:16px; padding:28px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:24px; }
  .news-editor h5 { font-weight:700; color:#0f172a; margin-bottom:20px; }
  .cover-preview { width:100%; height:160px; object-fit:cover; border-radius:10px; margin-top:8px; display:none; }
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
          <h2 class="h4 fw-bold text-dark mb-1">Quản Lý Tin Tức</h2>
          <p class="text-secondary small mb-0">Đăng bài, chỉnh sửa và xóa tin tức trên website</p>
        </div>
        <button class="btn btn-primary fw-bold px-4 rounded-3" onclick="showEditor()">+ Viết Bài Mới</button>
      </div>

      <?php
        $alertMap = ['created' => ['success','Đã đăng bài mới thành công!'], 'updated' => ['info','Đã cập nhật bài viết!'], 'deleted' => ['warning','Đã xóa bài viết.']];
        if ($msg && isset($alertMap[$msg])): $al = $alertMap[$msg];
      ?>
        <div class="alert alert-<?php echo $al[0]; ?> border-0 rounded-3 mb-4"><?php echo $al[1]; ?></div>
      <?php endif; ?>

      <!-- EDITOR PANEL (Write / Edit) -->
      <div class="news-editor" id="newsEditor" style="<?php echo ($editPost || !empty($_GET['new'])) ? '' : 'display:none;'; ?>">
        <h5><i class="bi bi-pencil-square text-primary me-2"></i><?php echo $editPost ? 'Chỉnh Sửa Bài Viết' : 'Viết Bài Mới'; ?></h5>
        <form method="POST">
          <input type="hidden" name="action" value="<?php echo $editPost ? 'update' : 'create'; ?>">
          <?php if ($editPost): ?>
            <input type="hidden" name="post_id" value="<?php echo $editPost['id']; ?>">
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small fw-bold text-secondary">Tiêu Đề Bài Viết</label>
              <input type="text" name="title" class="form-control bg-light border-0 fw-semibold"
                     value="<?php echo htmlspecialchars($editPost['title'] ?? ''); ?>" placeholder="Ví dụ: BMW M3 2025 ra mắt toàn cầu" required>
            </div>
            <div class="col-md-8">
              <label class="form-label small fw-bold text-secondary">URL Ảnh Bìa</label>
              <input type="text" name="cover" id="coverInput" class="form-control bg-light border-0"
                     value="<?php echo htmlspecialchars($editPost['featured_image'] ?? ''); ?>"
                     placeholder="https://... hoặc /img/..." oninput="previewCover(this.value)">
              <img id="coverPreview" class="cover-preview" src="" alt="Preview"
                   style="<?php echo !empty($editPost['featured_image']) ? 'display:block;' : 'display:none;'; ?>"
                   <?php if (!empty($editPost['featured_image'])): ?>src="<?php echo htmlspecialchars($editPost['featured_image']); ?>"<?php endif; ?>>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-check form-switch mb-2 ms-2">
                <input class="form-check-input" type="checkbox" name="is_published" id="pubToggle"
                       <?php echo (!$editPost || $editPost['status'] === 'published') ? 'checked' : ''; ?>>
                <label class="form-check-label fw-semibold" for="pubToggle">Đăng tải ngay</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold text-secondary">Tóm Tắt (Excerpt)</label>
              <textarea name="excerpt" class="form-control bg-light border-0" rows="2"
                        placeholder="1-2 câu tóm tắt nội dung bài..."><?php echo htmlspecialchars($editPost['excerpt'] ?? ''); ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold text-secondary">Nội Dung Bài Viết</label>
              <textarea name="content" class="form-control bg-light border-0" rows="14"
                        placeholder="Viết nội dung bài báo tại đây. Hỗ trợ HTML cơ bản như &lt;b&gt;, &lt;p&gt;, &lt;ul&gt;..."><?php echo htmlspecialchars($editPost['content'] ?? ''); ?></textarea>
            </div>
          </div>
          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary fw-bold px-5 rounded-3">
              <i class="bi bi-cloud-upload me-2"></i><?php echo $editPost ? 'Lưu Chỉnh Sửa' : 'Đăng Bài'; ?>
            </button>
            <a href="news.php" class="btn btn-light fw-bold rounded-3">Hủy</a>
          </div>
        </form>
      </div>

      <!-- DANH SÁCH BÀI VIẾT -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-uppercase text-secondary bg-light" style="font-size:0.75rem;letter-spacing:0.5px;">
                  <th class="ps-4">Ảnh Bìa</th>
                  <th>Tiêu Đề</th>
                  <th>Tóm Tắt</th>
                  <th>Trạng Thái</th>
                  <th class="text-end pe-4">Thao Tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($posts)): ?>
                  <tr><td colspan="5" class="text-center py-5 text-muted">Chưa có bài viết nào. Hãy viết bài đầu tiên!</td></tr>
                <?php else: ?>
                  <?php foreach ($posts as $p): ?>
                  <tr>
                    <td class="ps-4">
                      <?php if (!empty($p['cover_image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($p['cover_image_url']); ?>" alt=""
                             style="width:60px;height:45px;object-fit:cover;border-radius:8px;">
                      <?php else: ?>
                        <div style="width:60px;height:45px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                          <i class="bi bi-image text-secondary"></i>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($p['title']); ?></td>
                    <td class="text-secondary small" style="max-width:260px;">
                      <?php echo htmlspecialchars(mb_substr($p['excerpt'] ?? '', 0, 80)); ?>…
                    </td>
                    <td>
                      <?php if ($p['status'] === 'published'): ?>
                        <span class="badge bg-success text-success bg-opacity-25 px-3 py-2 rounded-pill fw-semibold">Đã Đăng</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-warning bg-opacity-25 px-3 py-2 rounded-pill fw-semibold">Nháp</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                      <a href="news.php?edit=<?php echo $p['id']; ?>" class="btn-action edit">Sửa Bài</a>
                      <form method="POST" class="d-inline" onsubmit="return confirm('Xóa bài viết này?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="btn-action delete">Xóa</button>
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
  function showEditor() {
    const ed = document.getElementById('newsEditor');
    ed.style.display = 'block';
    ed.scrollIntoView({ behavior: 'smooth' });
  }
  function previewCover(url) {
    const img = document.getElementById('coverPreview');
    if (url.trim() !== '') {
      img.src = url;
      img.style.display = 'block';
    } else {
      img.style.display = 'none';
    }
  }
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('adminWrapper');
    if(toggle) toggle.addEventListener('click', () => wrapper.classList.toggle('sidebar-collapsed'));
  });
</script>
</body>
</html>

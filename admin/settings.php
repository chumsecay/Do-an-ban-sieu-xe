<?php
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
requireAdminOrRedirect('../index.php?forbidden=1');
require_once __DIR__ . '/../config/database.php';

$adminPage = 'settings';
$pageTitle = 'Cai dat';
$pageSubtitle = 'Cau hinh he thong showroom';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

function redirectSettings(string $msg): void
{
    header('Location: settings.php?msg=' . urlencode($msg));
    exit;
}

function normalizeBoolFlag(mixed $value): string
{
    return ($value === '1' || $value === 1 || $value === true) ? '1' : '0';
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

$msg = (string)($_GET['msg'] ?? '');
$adminId = (int)($_SESSION['admin_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    try {
        if (in_array($action, ['save_general', 'save_contact', 'save_notifications', 'save_maintenance'], true)) {
            $pairs = [];

            if ($action === 'save_general') {
                $pairs = [
                    'APP_NAME' => trim((string)($_POST['app_name'] ?? '')),
                    'APP_HERO_TITLE' => trim((string)($_POST['hero_title'] ?? '')),
                    'APP_META_DESCRIPTION' => trim((string)($_POST['meta_description'] ?? '')),
                ];
            } elseif ($action === 'save_contact') {
                $pairs = [
                    'CONTACT_EMAIL' => trim((string)($_POST['contact_email'] ?? '')),
                    'CONTACT_PHONE' => trim((string)($_POST['contact_phone'] ?? '')),
                    'CONTACT_ADDRESS' => trim((string)($_POST['contact_address'] ?? '')),
                    'GOOGLE_MAPS_EMBED' => trim((string)($_POST['google_maps_embed'] ?? '')),
                ];
            } elseif ($action === 'save_notifications') {
                $pairs = [
                    'NOTIFY_NEW_ORDER' => normalizeBoolFlag($_POST['notify_new_order'] ?? '0'),
                    'NOTIFY_NEW_CUSTOMER' => normalizeBoolFlag($_POST['notify_new_customer'] ?? '0'),
                    'NOTIFY_CONTACT_FORM' => normalizeBoolFlag($_POST['notify_contact_form'] ?? '0'),
                    'NOTIFY_WEEKLY_REPORT' => normalizeBoolFlag($_POST['notify_weekly_report'] ?? '0'),
                ];
            } elseif ($action === 'save_maintenance') {
                $pairs = [
                    'MAINTENANCE_MODE' => normalizeBoolFlag($_POST['maintenance_mode'] ?? '0'),
                ];
            }

            $stmt = $pdo->prepare(
                'INSERT INTO app_settings (setting_key, setting_value, updated_by_admin_id)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by_admin_id = VALUES(updated_by_admin_id)'
            );
            foreach ($pairs as $key => $value) {
                $stmt->execute([$key, $value, $adminId > 0 ? $adminId : null]);
            }
            redirectSettings('saved');
        }

        if ($action === 'clear_cache') {
            $cacheDirs = [
                __DIR__ . '/../storage/cache',
                __DIR__ . '/../cache',
                __DIR__ . '/../tmp/cache',
            ];
            foreach ($cacheDirs as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                $files = @scandir($dir);
                if (!is_array($files)) {
                    continue;
                }
                foreach ($files as $f) {
                    if ($f === '.' || $f === '..') {
                        continue;
                    }
                    $path = $dir . DIRECTORY_SEPARATOR . $f;
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
            redirectSettings('cache_cleared');
        }

        if ($action === 'reset_data') {
            $confirm = trim((string)($_POST['confirm_reset'] ?? ''));
            if ($confirm !== 'RESET') {
                redirectSettings('reset_confirm_failed');
            }

            $tables = [
                'order_status_logs',
                'order_details',
                'warranties',
                'car_inquiries',
                'contact_messages',
                'orders',
                'news_posts',
                'employees',
                'customers',
                'car_images',
                'cars',
                'brands',
                'car_categories',
            ];

            $pdo->beginTransaction();
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($tables as $table) {
                if (!tableExists($pdo, $table)) {
                    continue;
                }
                $pdo->exec('DELETE FROM `' . $table . '`');
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            $pdo->commit();

            redirectSettings('reset_done');
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            } catch (Throwable $ignored) {
            }
        }
        redirectSettings('db_error');
    }
}

$settings = [];
try {
    $rows = $pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
    foreach ($rows as $r) {
        $settings[(string)$r['setting_key']] = (string)($r['setting_value'] ?? '');
    }
} catch (Throwable $ignored) {
}

$setting = static function (string $key, string $default = '') use ($settings): string {
    $value = $settings[$key] ?? '';
    return $value !== '' ? $value : $default;
};

$appNameSetting = $setting('APP_NAME', (string)env('APP_NAME', 'FLCar'));
$heroTitle = $setting('APP_HERO_TITLE', (string)env('APP_HERO_TITLE', 'Premium Cars Collection'));
$metaDescription = $setting('APP_META_DESCRIPTION', (string)env('APP_META_DESCRIPTION', ''));
$contactEmail = $setting('CONTACT_EMAIL', (string)env('CONTACT_EMAIL', 'info@flcar.vn'));
$contactPhone = $setting('CONTACT_PHONE', (string)env('CONTACT_PHONE', '0900 000 000'));
$contactAddress = $setting('CONTACT_ADDRESS', '');
$googleMapsEmbed = $setting('GOOGLE_MAPS_EMBED', '');

$notifyNewOrder = $setting('NOTIFY_NEW_ORDER', '1') === '1';
$notifyNewCustomer = $setting('NOTIFY_NEW_CUSTOMER', '1') === '1';
$notifyContactForm = $setting('NOTIFY_CONTACT_FORM', '1') === '1';
$notifyWeeklyReport = $setting('NOTIFY_WEEKLY_REPORT', '0') === '1';
$maintenanceMode = $setting('MAINTENANCE_MODE', '0') === '1';

$alertMap = [
    'saved' => ['success', 'Da luu cai dat thanh cong.'],
    'cache_cleared' => ['info', 'Da xoa cache he thong.'],
    'reset_done' => ['warning', 'Da reset du lieu nghiep vu.'],
    'reset_confirm_failed' => ['danger', 'Can nhap dung chu RESET de xac nhan.'],
    'db_error' => ['danger', 'Co loi CSDL khi xu ly thao tac.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Cai Dat - <?php echo htmlspecialchars($appNameSetting, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/admin.css" rel="stylesheet">
<style>
  .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
  .form-group { margin-bottom: 18px; }
  .form-group label { display: block; font-size: .82rem; font-weight: 600; color: #334155; margin-bottom: 6px; }
  .form-group input,
  .form-group textarea,
  .form-group select {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .85rem;
    background: #f8fafc;
    color: #0f172a;
  }
  .form-group textarea { resize: vertical; min-height: 100px; }
  .hint { font-size: .75rem; color: #94a3b8; margin-top: 4px; }
  .btn-save {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border: none; background: #2563eb; color: #fff;
    border-radius: 10px; font-size: .85rem; font-weight: 600; cursor: pointer;
  }
  .btn-outline {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border: 1px solid #e2e8f0; background: #fff; color: #334155;
    border-radius: 10px; font-size: .85rem; font-weight: 600; cursor: pointer;
  }
  .btn-danger {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border: 1px solid #fecaca; background: #fff; color: #dc2626;
    border-radius: 10px; font-size: .85rem; font-weight: 600; cursor: pointer;
  }
  .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #f1f5f9; }
  .toggle-row:last-child { border-bottom: none; }
  .toggle-row .toggle-label strong { font-size: .85rem; font-weight: 600; }
  .toggle-row .toggle-label p { font-size: .75rem; color: #64748b; margin: 2px 0 0; }
  @media (max-width: 900px) { .settings-grid { grid-template-columns: 1fr; } }
</style>
<link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body class="admin-body">
<?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>

  <main class="admin-content">
    <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
      <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3 mb-4"><?php echo $a[1]; ?></div>
    <?php endif; ?>

    <div class="settings-grid">
      <div class="panel">
        <div class="panel-header"><h2>Thong tin chung</h2></div>
        <div style="padding:24px">
          <form method="POST">
            <input type="hidden" name="action" value="save_general">
            <div class="form-group">
              <label>Ten showroom</label>
              <input type="text" name="app_name" value="<?php echo htmlspecialchars($appNameSetting, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Tieu de trang chu</label>
              <input type="text" name="hero_title" value="<?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Meta description</label>
              <textarea name="meta_description"><?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <button class="btn-save" type="submit">Luu thay doi</button>
          </form>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header"><h2>Thong tin lien he</h2></div>
        <div style="padding:24px">
          <form method="POST">
            <input type="hidden" name="action" value="save_contact">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="contact_email" value="<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Hotline</label>
              <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Dia chi showroom</label>
              <input type="text" name="contact_address" value="<?php echo htmlspecialchars($contactAddress, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Google Maps embed URL</label>
              <input type="text" name="google_maps_embed" value="<?php echo htmlspecialchars($googleMapsEmbed, ENT_QUOTES, 'UTF-8'); ?>">
              <p class="hint">Dan URL embed de hien thi ban do o trang lien he.</p>
            </div>
            <button class="btn-save" type="submit">Luu thay doi</button>
          </form>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header"><h2>Thong bao he thong</h2></div>
        <div style="padding:20px 24px">
          <form method="POST">
            <input type="hidden" name="action" value="save_notifications">

            <div class="toggle-row">
              <div class="toggle-label"><strong>Email don hang moi</strong><p>Thong bao khi co don moi.</p></div>
              <input type="hidden" name="notify_new_order" value="0">
              <input type="checkbox" name="notify_new_order" value="1" <?php echo $notifyNewOrder ? 'checked' : ''; ?>>
            </div>

            <div class="toggle-row">
              <div class="toggle-label"><strong>Email khach hang moi</strong><p>Thong bao khi them khach hang.</p></div>
              <input type="hidden" name="notify_new_customer" value="0">
              <input type="checkbox" name="notify_new_customer" value="1" <?php echo $notifyNewCustomer ? 'checked' : ''; ?>>
            </div>

            <div class="toggle-row">
              <div class="toggle-label"><strong>Email lien he tu form</strong><p>Thong bao message moi tu contact form.</p></div>
              <input type="hidden" name="notify_contact_form" value="0">
              <input type="checkbox" name="notify_contact_form" value="1" <?php echo $notifyContactForm ? 'checked' : ''; ?>>
            </div>

            <div class="toggle-row">
              <div class="toggle-label"><strong>Bao cao tuan</strong><p>Gui tong hop tuan cho admin.</p></div>
              <input type="hidden" name="notify_weekly_report" value="0">
              <input type="checkbox" name="notify_weekly_report" value="1" <?php echo $notifyWeeklyReport ? 'checked' : ''; ?>>
            </div>

            <div style="margin-top:18px">
              <button class="btn-save" type="submit">Luu thong bao</button>
            </div>
          </form>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header"><h2>Bao tri he thong</h2></div>
        <div style="padding:24px">
          <form method="POST" class="mb-4">
            <input type="hidden" name="action" value="save_maintenance">
            <div class="form-group">
              <label>Che do bao tri</label>
              <div style="display:flex;align-items:center;gap:12px">
                <input type="hidden" name="maintenance_mode" value="0">
                <input type="checkbox" name="maintenance_mode" value="1" <?php echo $maintenanceMode ? 'checked' : ''; ?>>
                <span class="hint">Bat de ngan truy cap tu ben ngoai trong luc bao tri.</span>
              </div>
            </div>
            <button class="btn-save" type="submit">Luu che do bao tri</button>
          </form>

          <form method="POST" class="mb-4">
            <input type="hidden" name="action" value="clear_cache">
            <button class="btn-outline" type="submit">Xoa cache he thong</button>
          </form>

          <form method="POST" onsubmit="return confirm('Ban chac chan muon reset du lieu nghiep vu?');">
            <input type="hidden" name="action" value="reset_data">
            <div class="form-group" style="margin-bottom:10px">
              <label style="color:#dc2626">Vung nguy hiem</label>
              <input type="text" name="confirm_reset" class="form-control" placeholder="Nhap RESET de xac nhan">
              <p class="hint">Thao tac se xoa du lieu xe, don hang, khach hang, bao hanh, nhan vien, tin tuc.</p>
            </div>
            <button class="btn-danger" type="submit">Reset du lieu nghiep vu</button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

</body>
</html>

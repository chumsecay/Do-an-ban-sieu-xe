<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';

/**
 * Connect without requiring an existing database.
 * Useful for running schema.sql that contains CREATE DATABASE / USE.
 */
function getServerConnection(): PDO
{
    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $user = env('DB_USERNAME', 'root');
    $pass = env('DB_PASSWORD', '');
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;charset=$charset;connect_timeout=5";
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);
}

/**
 * Execute SQL file by splitting on semicolon line endings.
 * Works with this project's schema.sql style (no custom DELIMITER blocks).
 */
function runSqlFile(PDO $pdo, string $sqlFile): array
{
    if (!is_file($sqlFile)) {
        throw new RuntimeException('Khong tim thay file schema: ' . $sqlFile);
    }

    $lines = file($sqlFile, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('Khong doc duoc file schema.sql');
    }

    $buffer = '';
    $executed = 0;

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }

        $buffer .= $line . "\n";
        if (str_ends_with(rtrim($line), ';')) {
            $statement = trim($buffer);
            $buffer = '';
            if ($statement !== '') {
                $pdo->exec($statement);
                $executed++;
            }
        }
    }

    if (trim($buffer) !== '') {
        $pdo->exec($buffer);
        $executed++;
    }

    return ['executed' => $executed];
}

$action = $_POST['action'] ?? 'test';
$resultClass = 'success';
$resultTitle = 'Ket noi Database thanh cong';
$resultBody = '';
$extraInfo = '';

try {
    if ($action === 'apply_schema') {
        set_time_limit(120);
        $pdoServer = getServerConnection();
        $schemaPath = __DIR__ . '/database/schema.sql';
        $summary = runSqlFile($pdoServer, $schemaPath);

        $resultTitle = 'Cap nhat DB thanh cong';
        $resultBody = 'Da chay schema.sql thanh cong. Ban co the tiep tuc test chuc nang tren web.';
        $extraInfo = 'So cau lenh da thuc thi: ' . (int)$summary['executed'];
    } else {
        $pdo = getDBConnection();
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        $resultBody = 'He thong da ket noi duoc toi database theo cau hinh trong file .env.';
        $extraInfo = 'MySQL Version: ' . htmlspecialchars((string)$version, ENT_QUOTES, 'UTF-8');
    }
} catch (Throwable $e) {
    $resultClass = 'error';
    $resultTitle = 'Xu ly that bai';
    $resultBody = 'Khong the ket noi hoac cap nhat DB. Vui long kiem tra lai cau hinh .env va quyen tai khoan DB.';
    $extraInfo = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DB Utility</title>
  <style>
    body { font-family: "Segoe UI", Arial, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #0f172a; }
    .wrap { max-width: 760px; margin: 0 auto; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); padding: 24px; }
    h1 { margin: 0 0 8px; font-size: 24px; }
    .muted { margin: 0 0 20px; color: #475569; }
    .result { border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; }
    .result.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .result.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .meta { background: #f1f5f9; border-radius: 10px; padding: 12px; font-family: Consolas, monospace; font-size: 13px; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
    button { border: 0; border-radius: 8px; padding: 10px 14px; cursor: pointer; font-weight: 600; }
    .btn-primary { background: #2563eb; color: #fff; }
    .btn-secondary { background: #334155; color: #fff; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>DB Utility</h1>
      <p class="muted">Kiem tra ket noi va cap nhat CSDL tu <code>database/schema.sql</code> ngay tren trang nay.</p>

      <div class="result <?php echo $resultClass; ?>">
        <strong><?php echo htmlspecialchars($resultTitle, ENT_QUOTES, 'UTF-8'); ?></strong>
        <div><?php echo htmlspecialchars($resultBody, ENT_QUOTES, 'UTF-8'); ?></div>
      </div>

      <div class="meta">
        <div><strong>Host:</strong> <?php echo htmlspecialchars((string)env('DB_HOST', ''), ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Port:</strong> <?php echo htmlspecialchars((string)env('DB_PORT', ''), ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Database:</strong> <?php echo htmlspecialchars((string)env('DB_DATABASE', ''), ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Thong tin:</strong> <?php echo $extraInfo; ?></div>
      </div>

      <div class="actions">
        <form method="post">
          <input type="hidden" name="action" value="test">
          <button class="btn-secondary" type="submit">Kiem tra ket noi</button>
        </form>
        <form method="post" onsubmit="return confirm('Chay lai database/schema.sql?');">
          <input type="hidden" name="action" value="apply_schema">
          <button class="btn-primary" type="submit">Cap nhat DB tu schema.sql</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

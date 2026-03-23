<?php
require_once __DIR__ . '/config/database.php';

// CSS cơ bản để hiển thị cho đẹp mắt dù là trang phụ
echo '<style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); text-align: center; max-width: 500px; }
    .success { color: #10b981; }
    .error { color: #ef4444; }
    .icon { font-size: 48px; margin-bottom: 20px; }
    h1 { margin-top: 0; font-size: 24px; color: #1e293b; }
    p { color: #64748b; line-height: 1.6; }
    .env-info { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: left; font-family: monospace; font-size: 14px; color: #334155; }
</style>';

echo '<body><div class="card" id="status-card">';
echo '<div class="icon">⏳</div>';
echo '<h1>Đang kiểm tra kết nối Database...</h1>';
echo '<p>Vui lòng chờ trong giây lát. Nếu cấu hình sai, hệ thống sẽ hiện lỗi rõ ràng (tối đa khoảng 5 giây).</p>';
echo '</div>';

if (function_exists('ob_flush')) {
    @ob_flush();
}
flush();

try {
    // Thử gọi hàm kết nối
    $pdo = getDBConnection();
    
    // Thu thập thêm thông tin Version của MySQL nếu kết nối thành công
    $version = $pdo->query('select version()')->fetchColumn();
    
    $resultHtml  = '<div class="icon success">✅</div>';
    $resultHtml .= '<h1>Kết nối Database Thành Công!</h1>';
    $resultHtml .= '<p>Hệ thống đã nhận diện chính xác cấu hình từ file <b>.env</b> và kết nối trơn tru với cơ sở dữ liệu.</p>';
    $resultHtml .= '<div class="env-info">';
    $resultHtml .= '<strong>Host:</strong> ' . htmlspecialchars(env('DB_HOST')) . '<br>';
    $resultHtml .= '<strong>Database:</strong> ' . htmlspecialchars(env('DB_DATABASE')) . '<br>';
    $resultHtml .= '<strong>MySQL Version:</strong> ' . htmlspecialchars($version);
    $resultHtml .= '</div>';

} catch (\Exception $e) {
    $resultHtml  = '<div class="icon error">❌</div>';
    $resultHtml .= '<h1>Kết nối Database Thất Bại!</h1>';
    $resultHtml .= '<p>Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra lại thông tin Host, Username, Password và Tên DB trong file <b>.env</b>.</p>';
    $resultHtml .= '<div class="env-info" style="color: #ef4444; background: #fef2f2;">';
    $resultHtml .= '<strong>Lỗi chi tiết:</strong><br>';
    $resultHtml .= htmlspecialchars($e->getMessage());
    $resultHtml .= '</div>';
}

echo '<script>';
echo 'document.getElementById("status-card").innerHTML = ' . json_encode($resultHtml, JSON_UNESCAPED_UNICODE) . ';';
echo '</script>';
echo '</body>';
?>

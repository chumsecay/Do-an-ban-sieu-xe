<?php
require_once __DIR__ . '/../bootstrap/env.php';

/**
 * Hàm khởi tạo kết nối cơ sở dữ liệu PDO an toàn
 * Sử dụng thông tin cấu hình từ file .env
 */
function getDBConnection(): PDO {
    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $db   = env('DB_DATABASE', 'flcar_db');
    $user = env('DB_USERNAME', 'root');
    $pass = env('DB_PASSWORD', '');
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset;connect_timeout=5";
    
    // Cấu hình PDO tối ưu & bảo mật
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ném ra Exception khi có lỗi SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Trả về mảng kết hợp (key-value)
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Vô hiệu hoá mô phỏng Prepare Statement (bảo mật hơn)
        PDO::ATTR_TIMEOUT            => 5,                      // Tránh treo quá lâu khi DB không phản hồi
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Không dừng script tại đây để caller có thể xử lý và hiển thị UI thân thiện.
        throw new RuntimeException(
            "Kết nối cơ sở dữ liệu thất bại. Vui lòng kiểm tra cấu hình DB trong file .env. Chi tiết: " . $e->getMessage(),
            0,
            $e
        );
    }
}

# FLCar - Hệ Thống Showroom Ô Tô Cao Cấp

FLCar là một nền tảng quản lý và giới thiệu xe hơi hạng sang, được thiết kế với giao diện chuẩn mực, tối ưu hóa trải nghiệm người dùng với các hiệu ứng hoạt ảnh mượt mà (60FPS) theo phong cách hiện đại.

## 🌟 Các tính năng nổi bật:

### 1. Giao diện Người Dùng (Frontend)
- **Thiết kế Premium**: Giao diện sang trọng, sử dụng Font chữ Inter linh hoạt.
- **Hoạt ảnh mượt mà 60FPS**:
  - Cuộn trang quán tính cực êm ái bằng thư viện **Lenis Smooth Scroll**.
  - Hiệu ứng xuất hiện linh hoạt kết hợp làm mờ (Blur) và thu phóng (Scale) tạo cảm giác cao cấp.
- **Tìm kiếm trực tiếp (Inline Search)**: Thanh tìm kiếm mở rộng mượt mà ngay trên menu (Navbar), hiển thị dropdown kết quả tức thì.
- **Trang Chi Tiết Xe (car-detail.php)**: Giao diện chi tiết xe với ảnh nền toàn màn hình, thanh bên form liên hệ (Sticky CTA), và lưới thông số kỹ thuật hiện đại.
- **Trang Tin Tức (
ews.php)**: Giao diện lưới đơn giản nhưng đạt hiệu năng tải trang tối đa (lược bớt các hiệu ứng đồ họa nặng).

### 2. Giao diện Quản Trị (Admin Dashboard)
- Quản lý Kho xe, Khách hàng, và Đơn hàng.
- Bố cục Sidebar Dashboard trực quan, thống kê dữ liệu đầy đủ.
- Được bảo mật và tối ưu để móc nối với cơ sở dữ liệu dễ dàng.

## 🚀 Cài Đặt (Local Development)

### Yêu cầu hệ thống:
- PHP 8.x
- MySQL (XAMPP, Laragon, v.v.)
- Trình duyệt hiện đại (Chrome, Safari, Edge)
- Môi trường Docker (Tùy chọn)

### CÁCH 1: Hướng dẫn chạy nhanh bằng XAMPP
1. Sao chép thư mục dự án vào thư mục htdocs của XAMPP.
2. Tạo file .env từ mẫu .env.example và thiết lập thông tin Database:
   - DB_HOST=127.0.0.1
   - DB_PORT=3306
   - DB_DATABASE=flcar_db (hoặc tên db bạn thích)
   - DB_USERNAME=root
3. Import cấu trúc Database tại database/schema.sql vào MySQL bằng PHPMyAdmin.
4. Mở trình duyệt và truy cập: http://localhost/carserv-1.0.0/index.php.
5. Truy cập http://localhost/carserv-1.0.0/test_db.php để kiểm tra kết nối với DB.

### CÁCH 2: Hướng dẫn chạy tự động bằng Docker Compose
1. Tạo file cấu hình bảo mật .env nếu bạn dùng file .env.docker.example gốc:
   `ash
   copy .env.docker.example .env
   `
2. Khởi chạy toàn bộ hệ thống bằng một dòng lệnh duy nhất (Docker sẽ tự động cài Apache, PHP và MySQL):
   `ash
   docker compose up -d --build
   `
3. Import dữ liệu:
   - Truy cập trang quản trị PHPMyAdmin tĩnh tại: http://localhost:8081
   - Đăng nhập (với Server/Host là db, User/Pass cấu hình trong file .env)
   - Import file database/schema.sql vào database lcar_db.
4. Xem trang web tại: http://localhost:8080/
5. Khi cần dừng hệ thống, chỉ việc dùng lệnh:
   `ash
   docker compose down
   `

*Lưu ý khi chạy Docker: Hệ thống giả lập Database cổng 3306 ra ngoài. Vui lòng tắt MySQL của XAMPP để không bị xung đột cổng.*

---
© 2026 FLCar. Phát triển dự án Showroom Xe Cao Cấp.

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

### Hướng dẫn chạy bằng XAMPP:
1. Sao chép thư mục dự án vào thư mục htdocs của XAMPP.
2. Tạo file .env từ file mẫu .env.example:
   - Điền thông tin kết nối cơ sở dữ liệu (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD).
3. Import cấu trúc Database tại database/schema.sql vào MySQL.
4. Truy cập Web.
5. Truy cập 	est_db.php để kiểm tra kết nối với DB.

---
© 2026 FLCar. Phát triển dự án Showroom Xe Cao Cấp.

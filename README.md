# Hotel Reservation Service 🏨

[![PHP](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com/)

Dịch vụ đặt phòng khách sạn. Sản là một nền tảng trực tuyến giúp khách hàng tìm kiếm, so sánh và đặt phòng khách sạn một cách nhanh chóng, an toàn và tiện lợi.

## ✨ Tính năng chính

Dự án được xây dựng theo mô hình MVC và phân chia rõ ràng vai trò người dùng và quản trị viên.

### Chức năng cho Người dùng (User)
-   **Đăng ký / Đăng nhập:** Quản lý tài khoản cá nhân.
-   **Tìm kiếm thông minh:** Tìm kiếm khách sạn theo tỉnh/thành phố.
-   **Xem chi tiết:** Xem thông tin chi tiết về khách sạn, các loại phòng có sẵn và đánh giá từ người dùng khác.
-   **Đặt phòng:** Thực hiện quy trình đặt phòng với ngày nhận, ngày trả.
-   **Lịch sử đặt phòng:** Xem lại các booking đã thực hiện.
-   **Hủy phòng:** Cho phép hủy các booking chưa đến ngày nhận phòng.
-   **Để lại đánh giá:** Đánh giá và bình luận về khách sạn đã trải nghiệm.

### Chức năng cho Quản trị viên (Admin)
-   **Dashboard Quản trị:** Giao diện quản lý tập trung.
-   **Quản lý Thành phố:** Thêm, sửa, xóa các tỉnh/thành phố.
-   **Quản lý Khách sạn:** Thêm, sửa, xóa thông tin khách sạn.
-   **Quản lý Phòng:** Thêm, sửa, xóa các loại phòng cho từng khách sạn.
-   **Quản lý Đặt phòng:** Xem tất cả các booking của người dùng và cập nhật trạng thái (Xác nhận, Hủy, Đã nhận phòng...).

## 💻 Công nghệ sử dụng

-   **Backend:** PHP thuần (Lập trình hướng đối tượng, mô hình MVC).
-   **Frontend:** HTML, CSS, JavaScript.
-   **Database:** MySQL / MariaDB.
-   **Thư viện:** Bootstrap 5, FontAwesome, Flatpickr.
-   **Web Server:** Apache (sử dụng trong môi trường Laragon).

## 🚀 Hướng dẫn cài đặt

Làm theo các bước sau để chạy dự án trên máy local của bạn (sử dụng Laragon).

### 1. Clone Repository

```bash
git clone [https://github.com/VanTra-ai/Hotel-Reservation-Service.git](https://github.com/VanTra-ai/Hotel-Reservation-Service.git)
```

### 2. Cấu hình Web Server (Laragon)

1.  Mở Laragon, nhấn vào **Menu > Apache > Site Configuration > httpd-vhosts.conf**.
2.  Thêm một Virtual Host mới trỏ đến thư mục con `hotelreservationservice` bên trong project của bạn.

    ```apache
    <VirtualHost *:80> 
        DocumentRoot "C:/laragon/www/Hotel-Reservation-Service/hotelreservationservice"
        ServerName hotel.test
    </VirtualHost>
    ```
    *(Lưu ý: Thay `C:/laragon/www/Hotel-Reservation-Service` bằng đường dẫn thực tế đến dự án của bạn)*.

3.  Khởi động lại Apache.

### 3. Thiết lập Cơ sở dữ liệu

1.  Mở Laragon và nhấn nút **"Database"** để truy cập HeidiSQL (hoặc sử dụng phpMyAdmin).
2.  Tạo một cơ sở dữ liệu mới với tên là `hotel_reservation`.
3.  Chọn cơ sở dữ liệu `hotel_reservation` vừa tạo, sau đó vào **File > Run SQL file...** và chọn file `database.sql` từ thư mục gốc của dự án để import.

### 4. Cấu hình Kết nối

1.  Trong dự án, tìm đến thư mục `hotelreservationservice/app/config/`.
2.  Tạo một file mới tên là `database.php`.
3.  Sao chép nội dung dưới đây và dán vào file `database.php` vừa tạo. **Lưu ý:** file này nằm trong `.gitignore` và sẽ không được đẩy lên Git.

    ```php
    <?php
    class Database
    {
        private $host = "localhost";
        private $db_name = "hotel_reservation";
        private $username = "root"; // Thay đổi nếu cần
        private $password = "";     // Thay đổi nếu cần
        public $conn;
        public function getConnection()
        {
            $this->conn = null;
            try {
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->exec("set names utf8");
            } catch (PDOException $exception) {
                echo "Connection error: " . $exception->getMessage();
            }
            return $this->conn;
        }
    }
    ```

### 5. Chạy dự án

Truy cập vào địa chỉ bạn đã cấu hình ở Bước 2 (ví dụ: `http://hotel.test`). Trang chủ sẽ hiện ra.

## 🔑 Tài khoản mặc định

Sau khi import database, bạn có thể sử dụng tài khoản sau để truy cập vào các chức năng quản trị:
-   **Username:** `admin`
-   **Password:** `admin123`

## 📂 Cấu trúc thư mục

Dự án được tổ chức theo mô hình MVC:
-   `app/controllers/`: Chứa logic xử lý yêu cầu (request). Các controller cho admin nằm trong thư mục con `admin`.
-   `app/models/`: Chứa logic tương tác với cơ sở dữ liệu.
-   `app/views/`: Chứa các file giao diện người dùng. Các view cho admin nằm trong thư mục con `admin`.
-   `public/`: Chứa các tài sản công khai như CSS, JavaScript, hình ảnh.
-   `index.php`: File điều hướng (Router) chính của ứng dụng.

## 👥 Thành viên đóng góp

-   [@VanTra-ai](https://github.com/VanTra-ai)
-   [@2280603697NguyenQuangVinh](https://github.com/2280603697NguyenQuangVinh)
-   [@LBT-123-ux](https://github.com/LBT-123-ux)

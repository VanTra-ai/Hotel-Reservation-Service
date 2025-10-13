# Hotel Reservation Service 🏨

[![PHP](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com/)

Dịch vụ đặt phòng khách sạn. Đây là một nền tảng trực tuyến giúp khách hàng tìm kiếm, so sánh và đặt phòng khách sạn một cách nhanh chóng, an toàn và tiện lợi, đồng thời cung cấp công cụ quản lý mạnh mẽ cho các đối tác và quản trị viên.

## ✨ Tính năng chính

Dự án được xây dựng theo mô hình MVC và phân chia rõ ràng thành 3 vai trò: Người dùng, Đối tác (Partner), và Quản trị viên (Admin).

### Chức năng cho Người dùng (User)
-   **Tài khoản:** Đăng ký, đăng nhập.
-   **Tìm kiếm & Khám phá:** Tìm kiếm khách sạn theo tỉnh/thành phố, xem danh sách phòng.
-   **Xem chi tiết:** Xem thông tin chi tiết về khách sạn, phòng, và các đánh giá từ người dùng khác.
-   **Đặt phòng:** Thực hiện quy trình đặt phòng với ngày nhận, ngày trả.
-   **Quản lý cá nhân:** Xem lịch sử đặt phòng và thực hiện hủy phòng.
-   **Đánh giá:** Để lại đánh giá và bình luận về khách sạn đã trải nghiệm.

### Chức năng cho Đối tác (Partner)
-   **Đăng nhập:** Sử dụng tài khoản đã được Admin cấp quyền Partner.
-   **Dashboard riêng:** Truy cập "Kênh Đối tác" với trang tổng quan riêng.
-   **Báo cáo & Thống kê:** Xem các chỉ số (tổng lượt đặt, tổng doanh thu), biểu đồ doanh thu và trạng thái booking **chỉ của các khách sạn mình sở hữu**.
-   **Quản lý Đặt phòng:** Xem và cập nhật trạng thái (Xác nhận, Hủy, Đã nhận phòng...) cho các booking thuộc khách sạn của mình.

### Chức năng cho Quản trị viên (Admin)
-   **Dashboard Toàn hệ thống:** Xem báo cáo và thống kê tổng quan của toàn bộ trang web.
-   **Quản lý Thành viên:**
    - Xem danh sách tất cả tài khoản.
    - Sửa thông tin và thay đổi vai trò (`user`, `partner`, `admin`).
    - Gán một khách sạn cụ thể cho một tài khoản `partner`.
    - Xóa tài khoản người dùng.
-   **Quản lý CRUD:** Toàn quyền Thêm, Sửa, Xóa đối với **Thành phố**, **Khách sạn**, và **Phòng**.
-   **Quản lý Đặt phòng:** Xem và quản lý tất cả các booking trong hệ thống.

## 💻 Công nghệ sử dụng

-   **Backend:** PHP thuần (Lập trình hướng đối tượng, mô hình MVC).
-   **Frontend:** HTML, CSS, JavaScript.
-   **Database:** MySQL / MariaDB.
-   **Thư viện:** Bootstrap 5, FontAwesome, Flatpickr, Chart.js.
-   **Môi trường phát triển:** Laragon (Apache + MySQL).

## 🚀 Hướng dẫn cài đặt

Làm theo các bước sau để chạy dự án trên máy local (khuyến khích sử dụng Laragon).

### 1. Clone Repository

```bash
git clone [https://github.com/VanTra-ai/Hotel-Reservation-Service.git](https://github.com/VanTra-ai/Hotel-Reservation-Service.git)
```

### 2. Thiết lập Cơ sở dữ liệu

1.  Mở Laragon và nhấn nút **"Start All"**.
2.  Nhấn nút **"Database"** để mở HeidiSQL (hoặc công cụ quản lý DB của bạn).
3.  Tạo một cơ sở dữ liệu mới với tên là `hotel_reservation` (sử dụng `utf8mb4_unicode_ci`).
4.  Chọn cơ sở dữ liệu `hotel_reservation` vừa tạo, sau đó vào **File > Run SQL file...** và chọn file `database.sql` từ thư mục gốc của dự án để import. Quá trình này sẽ tạo tất cả bảng và dữ liệu mẫu.

### 3. Cấu hình Kết nối

1.  Trong dự án, tìm đến thư mục `hotelreservationservice/app/config/`.
2.  Tạo một file mới tên là **`database.php`**.
3.  Sao chép nội dung dưới đây và dán vào file `database.php` vừa tạo. File này nằm trong `.gitignore` và sẽ không được đẩy lên Git.

    ```php
    <?php
    class Database
    {
        private $host = "localhost";
        private $db_name = "hotel_reservation";
        private $username = "root"; // Mặc định của Laragon
        private $password = "";     // Mặc định của Laragon
        public $conn;
        
        public function getConnection()
        {
            $this->conn = null;
            try {
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->exec("set names utf8");
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $exception) {
                echo "Connection error: " . $exception->getMessage();
            }
            return $this->conn;
        }
    }
    ```

### 4. Chạy dự án

Truy cập vào dự án thông qua URL của Laragon (ví dụ: `http://hotel-reservation-service.test/hotelreservationservice`). Trang chủ sẽ hiện ra.

## 🔑 Tài khoản mặc định

Sau khi import database, bạn có thể sử dụng các tài khoản sau để kiểm tra:
-   **Admin:**
    -   **Username:** `admin`
    -   **Password:** `admin123`
-   **User (ví dụ):**
    -   **Username:** `vantra`
    -   **Password:** `123456` *(Bạn có thể tạo thêm user tùy ý)*

## 📂 Cấu trúc thư mục

Dự án được tổ chức theo mô hình MVC:
-   `hotelreservationservice/app/controllers/`: Chứa logic xử lý yêu cầu. Các controller cho admin/partner có tiền tố `Admin`/`Partner`.
-   `hotelreservationservice/app/models/`: Chứa logic tương tác với cơ sở dữ liệu.
-   `hotelreservationservice/app/views/`: Chứa các file giao diện. Giao diện admin/partner nằm trong thư mục con tương ứng.
-   `hotelreservationservice/public/`: Chứa các nội dung công khai
## 👥 Thành viên đóng góp

-   [@VanTra-ai](https://github.com/VanTra-ai)
-   [@2280603697NguyenQuangVinh](https://github.com/2280603697NguyenQuangVinh)
-   [@LBT-123-ux](https://github.com/LBT-123-ux)

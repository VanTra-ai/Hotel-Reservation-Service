# Hotel Reservation Service 🏨✨

[![PHP](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://www.php.net/)
[![Python](https://img.shields.io/badge/Python-3.x-yellow.svg)](https://www.python.org/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com/)

Dịch vụ đặt phòng khách sạn là một nền tảng web toàn diện, giúp khách hàng tìm kiếm, so sánh và đặt phòng khách sạn. Điểm nổi bật của dự án là việc tích hợp **mô hình Trí tuệ Nhân tạo (AI)** để tự động phân tích và chấm điểm các bình luận của khách hàng, cung cấp một cái nhìn khách quan về chất lượng dịch vụ.

## ⭐ Tính năng Nổi bật: Tích hợp AI

Dự án sử dụng một mô hình học sâu (Deep Learning) dựa trên **BERT** để phân tích cảm xúc và nội dung của bình luận tiếng Việt.

- **Đầu vào đa yếu tố:** Mô hình không chỉ phân tích văn bản bình luận mà còn kết hợp các thông tin khác như điểm chi tiết của khách sạn (sạch sẽ, nhân viên...), loại phòng, và đối tượng khách hàng để đưa ra dự đoán.
- **Dự đoán điểm số:** Tự động chấm điểm cho một bình luận trên thang điểm 10.
- **Chuyển đổi thành nhãn:** Chuyển đổi điểm số thành các nhãn văn bản thân thiện ("Tuyệt vời", "Rất tốt"...) để thống nhất giao diện.
- **"AI Playground":** Một giao diện chuyên dụng cho phép thử nghiệm mô hình với đầy đủ các tham số đầu vào.

---

## ✨ Các Chức năng Chính

Dự án được phân chia thành 3 vai trò: **Người dùng (User)**, **Đối tác (Partner)**, và **Quản trị viên (Admin)**.

### Chức năng cho Người dùng (User) 🙋

- **Tài khoản:** Đăng ký, đăng nhập.
- **Tìm kiếm & Khám phá:** Tìm kiếm khách sạn, xem chi tiết phòng và các đánh giá.
- **Xem đánh giá AI:** Xem điểm số tổng hợp và các bình luận đã được AI phân tích.
- **Đặt phòng & Quản lý:** Thực hiện đặt phòng, xem lịch sử và hủy các booking.

### Chức năng cho Đối tác (Partner) 🧑‍💼

- **Dashboard riêng:** Truy cập "Kênh Đối tác" với trang tổng quan về các chỉ số kinh doanh.
- **Báo cáo & Thống kê:** Xem báo cáo doanh thu, lượt đặt phòng, biểu đồ... **chỉ của các khách sạn mình sở hữu**.
- **Quản lý Đặt phòng:** Cập nhật trạng thái cho các booking thuộc khách sạn của mình.

### Chức năng cho Quản trị viên (Admin) 👑

- **Dashboard Toàn hệ thống:** Xem báo cáo và thống kê của toàn bộ trang web.
- **Quản lý Thành viên:** Quản lý tất cả tài khoản, phân quyền `user`, `partner`, `admin` và gán khách sạn cho đối tác.
- **Quản lý Nội dung (CRUD):** Toàn quyền Thêm, Sửa, Xóa đối với **Thành phố**, **Khách sạn** (bao gồm 7 điểm đặc trưng cho AI), và **Phòng**.
- **Quản lý Đặt phòng:** Quản lý tất cả các booking trong hệ thống.

---

## 💻 Công nghệ sử dụng

- **Backend (Web App) 🐘:** PHP thuần (OOP, MVC), Apache.
- **Backend (AI Service) 🐍:** Python, Flask, PyTorch, Transformers (BERT), Scikit-learn.
- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5, FontAwesome, Flatpickr, Chart.js.
- **Database:** MySQL / MariaDB.
- **Môi trường phát triển:** Laragon.

---

## 🚀 Hướng dẫn cài đặt

Để chạy dự án, bạn cần thiết lập cả **Web App (PHP)** và **Dịch vụ AI (Python API)**.

### Yêu cầu

- [Git](https://git-scm.com/)
- [Laragon](https://laragon.org/download/) (hoặc môi trường PHP/MySQL tương tự)
- [Python](https://www.python.org/downloads/) (phiên bản 3.9+)

### 1. Clone Repository

```bash
git clone [https://github.com/VanTra-ai/Hotel-Reservation-Service.git](https://github.com/VanTra-ai/Hotel-Reservation-Service.git)
cd Hotel-Reservation-Service
```

### 2. Cài đặt Web App (PHP)

1.  **Thiết lập Cơ sở dữ liệu:**

    - Mở Laragon, nhấn **"Start All"**.
    - Nhấn **"Database"** để mở HeidiSQL.
    - Tạo một database mới tên là `hotel_reservation` (sử dụng `utf8mb4_unicode_ci`).
    - Chọn database `hotel_reservation`, vào **File > Run SQL file...** và chọn file `hotelreservationservice.sql` ở thư mục gốc của dự án để import.

2.  **Cấu hình Kết nối:**

    - Sao chép file `hotelreservationservice/app/config/database.example.php` và đổi tên thành `database.php`.
    - Mở file `database.php` và chỉnh sửa thông tin `username` và `password` nếu cần.

3.  **Chạy dự án:** Truy cập dự án qua URL của Laragon (ví dụ: `http://hotel-reservation-service.test/hotelreservationservice`).

### 3. Cài đặt Dịch vụ AI (Python API)

1.  **Mở Terminal:** Mở một cửa sổ terminal mới và di chuyển vào thư mục `HotelRatingAPI`:

    ```bash
    cd HotelRatingAPI
    ```

2.  **Tạo và Kích hoạt Môi trường ảo:**

    ```bash
    # Tạo môi trường ảo
    python -m venv venv

    # Kích hoạt (Windows)
    .\venv\Scripts\activate

    # Kích hoạt (macOS/Linux)
    # source venv/bin/activate
    ```

3.  **Cài đặt Thư viện:**

    ```bash
    pip install -r requirements.txt
    ```

4.  **Chạy API Server:**
    ```bash
    python api.py
    ```
    Giữ cửa sổ terminal này mở. API sẽ chạy ở địa chỉ `http://127.0.0.1:5000`.

### 🔑 Tài khoản mặc định

- **Admin:** `admin` / `admin123`
- **Partner:** `partner` / `partner123`
- **User:** `user` / `user123`

---

## 📂 Cấu trúc thư mục

```
.
├── HotelRatingAPI/             # Dự án API Python
│   ├── production_model/       # Các file mô hình, tokenizer, scaler đã huấn luyện
│   ├── venv/                   # Thư mục môi trường ảo (bị bỏ qua bởi Git)
│   ├── api.py                  # File chính của Flask API
│   └── requirements.txt        # Danh sách các thư viện Python
│
├── hotelreservationservice/    # Dự án Web PHP
│   ├── app/
│   │   ├── controllers/
│   │   ├── models/
│   │   └── views/
│   ├── public/
│   └── index.php
│
├── hotelreservationservice.sql # File khởi tạo cơ sở dữ liệu
└── README.md
```

---

## 👥 Thành viên đóng góp

- [@VanTra-ai](https://github.com/VanTra-ai)
- [@2280603697NguyenQuangVinh](https://github.com/2280603697NguyenQuangVinh)
- [@LBT-123-ux](https://github.com/LBT-123-ux)

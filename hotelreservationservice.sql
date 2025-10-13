-- Xóa database cũ nếu đã tồn tại
DROP DATABASE IF EXISTS hotel_reservation;

-- Tạo mới database và sử dụng nó
CREATE DATABASE hotel_reservation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_reservation;

-- Tạo bảng account 
CREATE TABLE account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE oauth_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  provider VARCHAR(50) NOT NULL,
  provider_user_id VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_provider_user (provider, provider_user_id),
  FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng tỉnh, thành phố
CREATE TABLE city (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng khách sạn
CREATE TABLE hotel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    description TEXT,
    rating DECIMAL(10,2) DEFAULT 0,
    total_rating INT DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    service_staff DECIMAL(10,2) DEFAULT 0,
    amenities DECIMAL(10,2) DEFAULT 0,
    cleanliness DECIMAL(10,2) DEFAULT 0,
    comfort DECIMAL(10,2) DEFAULT 0,
    value_for_money DECIMAL(10,2) DEFAULT 0,
    location DECIMAL(10,2) DEFAULT 0,
    free_wifi DECIMAL(10,2) DEFAULT 0,
    city_id INT,
    FOREIGN KEY (city_id) REFERENCES city(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng phòng (room)
CREATE TABLE room (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (hotel_id) REFERENCES hotel(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng đặt phòng (booking)
CREATE TABLE booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
	 status ENUM('pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES room(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4;

-- Tạo bảng đánh giá (review)
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    account_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    category VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotel(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4;

-- Chèn tài khoản admin mặc định
INSERT INTO account (username, fullname, email, password, role) VALUES
('admin', 'Quản Trị Viên', 'admin@gmail.com', '$2y$10$wyGTVCICfuAUG/jQQTlb.e4wU3LxRrPXnwOtBRcSVbc5YGb6iwxKi', 'admin');

-- Chèn dữ liệu mẫu vào bảng city
INSERT INTO city (name, image) VALUES
('Bà Rịa Vũng Tàu', 'public/images/cityimages/ba-ria-vung-tau.jpg'),
('Bình Dương', 'public/images/cityimages/binh-duong.jpg'),
('Cà Mau', 'public/images/cityimages/ca-mau.jpg'),
('Cần Thơ', 'public/images/cityimages/can-tho.jpg'),
('Đăk Lăk', 'public/images/cityimages/dak-lak.jpg'),
('Đà Lạt', 'public/images/cityimages/da-lat.jpg'),
('Đà Nẵng', 'public/images/cityimages/da-nang.jpg'),
('Gia Lai', 'public/images/cityimages/gia-lai.jpg'),
('Hà Giang', 'public/images/cityimages/ha-giang.jpg'),
('Hải Phòng', 'public/images/cityimages/hai-phong.jpg'),
('Hạ Long', 'public/images/cityimages/ha-long.jpg'),
('Hà Nội', 'public/images/cityimages/ha-noi.jpg'),
('Hồ Chí Minh', 'public/images/cityimages/ho-chi-minh.jpg'),
('Hội An', 'public/images/cityimages/hoi-an.jpg'),
('Huế', 'public/images/cityimages/hue.jpg'),
('Mũi Né', 'public/images/cityimages/mui-ne.jpg'),
('Nghệ An', 'public/images/cityimages/nghe-an.jpg'),
('Nha Trang', 'public/images/cityimages/nha-trang.jpg'),
('Ninh Thuận', 'public/images/cityimages/ninh-thuan.jpg'),
('Phan Thiết', 'public/images/cityimages/phan-thiet.jpg'),
('Phú Quốc', 'public/images/cityimages/phu-quoc.jpg'),
('Phú Yên', 'public/images/cityimages/phu-yen.jpg'),
('Quảng Bình', 'public/images/cityimages/quang-binh.jpg'),
('Sa Pa', 'public/images/cityimages/sa-pa.jpg'),
('Thanh Hoá', 'public/images/cityimages/thanh-hoa.jpg'),
('Trà Vinh', 'public/images/cityimages/tra-vinh.jpg'),
('Vĩnh Long', 'public/images/cityimages/vinh-long.jpg');

-- Chèn dữ liệu mẫu vào bảng hotel
INSERT INTO hotel (name, address, DESCRIPTION, city_id) VALUES
('3H Grand Hotel', '274 Phan Chu Trinh, Phường 2, Thành Phố Vũng Tàu, Vũng Tàu, Việt Nam', 'Tọa lạc ở Vũng Tàu, cách Bãi Sau 5 phút đi bộ và Bãi Dứa 1.9 km, 3H Grand Hotel cung cấp chỗ nghỉ có sân hiên và Wi-Fi miễn phí cũng như chỗ đậu xe riêng miễn phí cho khách lái xe.', 1),
('Summer Beach Hotel Vung Tau', 'Hẻm 45 Thùy Vân 45/18 Thùy Vân, phường 2, thành phố Vũng Tàu, Vũng Tàu, Việt Nam', 'Nằm giáp biển, Summer Beach Hotel Vung Tau cung cấp chỗ nghỉ 3 sao ở Vũng Tàu, đồng thời có phòng chờ chung, nhà hàng và quầy bar.', 1),
('Fairfield by Marriott South Binh Duong', 'NO. 5 Huu Nghi Avenue, Vsip Binh Hoa Ward, 820000 Thuận An, Việt Nam', 'Nằm trong khu công nghiệp lớn nhất của khu vực, Fairfield by Marriott South Binh Duong có hồ bơi ngoài trời, trung tâm thể dục 24 giờ và WiFi miễn phí.', 2),
('Song Hung Hotel', '28 Phan Ngọc Hien Street, Ward 2, Cà Mau, Việt Nam', 'Song Hung Hotel tọa lạc ở Cà Mau.', 3),
('khách sạn tina 5', 'F8-7 -8 -9 nguyễn thị sáu, phú thứ, cái răng, Cần Thơ, Việt Nam', 'Nằm ở Cần Thơ, cách Trung tâm thương mại Vincom Xuân Khánh 5.8 km, khách sạn tina 5 cung cấp chỗ nghỉ có sân hiên, chỗ đậu xe riêng miễn phí và quầy bar. Chỗ nghỉ này có Wi-Fi miễn phí, nằm cách Vincom Plaza Hùng Vương 8.6 km và Bảo tàng Cần Thơ 6.3 km. Đây là chỗ nghỉ không gây dị ứng và tọa lạc cách Bến Ninh Kiều 6.4 km.', 4);

-- Tạo index để tối ưu truy vấn
CREATE INDEX idx_room_hotel ON room(hotel_id);
CREATE INDEX idx_booking_account ON booking(account_id);
CREATE INDEX idx_booking_room ON booking(room_id);
CREATE INDEX idx_review_hotel ON review(hotel_id);

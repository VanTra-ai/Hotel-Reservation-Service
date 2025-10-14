-- =================================================================
-- Hotel Reservation Service - Database Initialization Script
-- Phiên bản: Hoàn thiện (Đã bao gồm AI features & Trigger)
-- =================================================================

-- Xóa database cũ nếu đã tồn tại
DROP DATABASE IF EXISTS hotel_reservation;

-- Tạo mới database và sử dụng nó
CREATE DATABASE hotel_reservation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_reservation;

-- ------------------------------------------------------------------
-- Bảng 1: `account`
-- ------------------------------------------------------------------
CREATE TABLE `account` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `fullname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT NULL,
  `role` ENUM('admin', 'user', 'partner') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- Bảng 2: `oauth_accounts`
-- ------------------------------------------------------------------
CREATE TABLE `oauth_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `provider` VARCHAR(50) NOT NULL,
  `provider_user_id` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_provider_user` (`provider`, `provider_user_id`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- Bảng 3: `city`
-- ------------------------------------------------------------------
CREATE TABLE `city` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- Bảng 4: `hotel`
-- ------------------------------------------------------------------
CREATE TABLE `hotel` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `address` TEXT,
  `description` TEXT,
  `rating` DECIMAL(3,1) DEFAULT 0.0,
  `total_rating` INT DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `service_staff` DECIMAL(3,1) DEFAULT 8.0,
  `amenities` DECIMAL(3,1) DEFAULT 8.0,
  `cleanliness` DECIMAL(3,1) DEFAULT 8.0,
  `comfort` DECIMAL(3,1) DEFAULT 8.0,
  `value_for_money` DECIMAL(3,1) DEFAULT 8.0,
  `location` DECIMAL(3,1) DEFAULT 8.0,
  `free_wifi` DECIMAL(3,1) DEFAULT 8.0,
  `city_id` INT,
  `owner_id` INT NULL,
  FOREIGN KEY (`city_id`) REFERENCES `city`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`owner_id`) REFERENCES `account`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- Bảng 5: `room`
-- ------------------------------------------------------------------
CREATE TABLE `room` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hotel_id` INT NOT NULL,
  `room_number` VARCHAR(50) NOT NULL,
  `room_type` VARCHAR(50) NOT NULL,
  `capacity` INT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`hotel_id`) REFERENCES `hotel`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- Bảng 6: `booking`
-- ------------------------------------------------------------------
CREATE TABLE `booking` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `room_id` INT NOT NULL,
  `check_in_date` DATE NOT NULL,
  `check_out_date` DATE NOT NULL,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `room`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------
-- Bảng 7: `review`
-- ------------------------------------------------------------------
CREATE TABLE `review` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hotel_id` INT NOT NULL,
  `account_id` INT NOT NULL,
  `rating` INT NOT NULL,
  `ai_rating` DECIMAL(3, 1) NULL DEFAULT NULL,
  `rating_text` VARCHAR(50) NULL DEFAULT NULL,
  `comment` TEXT,
  `category` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`hotel_id`) REFERENCES `hotel`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- =================================================================
-- Dữ liệu mẫu (Sample Data)
-- =================================================================

-- Chèn tài khoản mẫu
-- Mật khẩu admin: admin123
-- Mật khẩu partner: partner123
-- Mật khẩu user: user123
INSERT INTO `account` (`id`, `username`, `fullname`, `email`, `password`, `role`) VALUES
(1, 'admin', 'Quản Trị Viên', 'admin@gmail.com', '$2y$10$wyGTVCICfuAUG/jQQTlb.e4wU3LxRrPXnwOtBRcSVbc5YGb6iwxKi', 'admin'),
(2, 'partner', 'Chủ Khách Sạn A', 'partner@example.com', '$2y$10$e.L6..sgg7Xg4wJ5Yx39UeOqR7D3jf2g2F2.8.zWbvc3BH3L2aYvK', 'partner'),
(3, 'user', 'Người Dùng Test', 'user@example.com', '$2y$10$wE2/dG2.4.eL/C6YAbwY3uLpU8f0..3zF/PzV3i5f.21/e.pQYy1u', 'user');

-- Chèn dữ liệu thành phố
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

-- Chèn dữ liệu khách sạn (đã có owner_id và 7 điểm đặc trưng)
INSERT INTO `hotel` (`name`, `address`, `description`, `city_id`, `owner_id`, `service_staff`, `amenities`, `cleanliness`, `comfort`, `value_for_money`, `location`, `free_wifi`) VALUES
('3H Grand Hotel', '274 Phan Chu Trinh, Phường 2, Vũng Tàu', 'Mô tả về 3H Grand Hotel.', 1, NULL, 9.1, 8.8, 9.2, 9.0, 8.5, 8.9, 8.7),
('Summer Beach Hotel', 'Hẻm 45 Thùy Vân, Vũng Tàu', 'Mô tả về Summer Beach Hotel.', 1, 2, 9.5, 9.3, 9.5, 9.5, 9.2, 9.0, 8.6),
('Fairfield by Marriott', 'NO. 5 Huu Nghi Avenue, Thuận An', 'Mô tả về Fairfield by Marriott.', 5, NULL, 9.0, 9.1, 9.3, 9.2, 8.8, 8.5, 9.4);

-- Chèn dữ liệu phòng
INSERT INTO `room` (`hotel_id`, `room_number`, `room_type`, `capacity`, `price`, `description`) VALUES
(1, '101', 'Phòng Tiêu Chuẩn', 2, 500000, 'Phòng tiêu chuẩn giường đôi.'),
(1, '102', 'Phòng Superior', 2, 750000, 'Phòng superior có view đẹp hơn.'),
(2, 'A201', 'Phòng Deluxe', 3, 1200000, 'Phòng deluxe hướng biển.');

-- =================================================================
-- Chỉ mục (Indexes) và Trigger
-- =================================================================

-- Tạo index để tối ưu truy vấn
CREATE INDEX `idx_room_hotel` ON `room`(`hotel_id`);
CREATE INDEX `idx_booking_account` ON `booking`(`account_id`);
CREATE INDEX `idx_booking_room` ON `booking`(`room_id`);
CREATE INDEX `idx_review_hotel` ON `review`(`hotel_id`);

-- Tạo Trigger để tự động cập nhật rating tổng của khách sạn
DELIMITER $$
CREATE TRIGGER `after_review_insert`
AFTER INSERT ON `review`
FOR EACH ROW
BEGIN
    DECLARE current_hotel_id INT;
    SET current_hotel_id = NEW.hotel_id;
    
    UPDATE hotel
    SET 
        rating = (SELECT AVG(rating) FROM review WHERE hotel_id = current_hotel_id),
        total_rating = (SELECT COUNT(*) FROM review WHERE hotel_id = current_hotel_id)
    WHERE id = current_hotel_id;
END$$
DELIMITER ;

-- Hoàn tất!
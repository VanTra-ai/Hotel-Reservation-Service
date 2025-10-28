-- =================================================================
-- Hotel Reservation Service - Database Initialization Script
-- Phiên bản: Hoàn thiện (Đã bao gồm AI features & Trigger)
-- =================================================================

-- Xóa database cũ nếu đã tồn tại
DROP DATABASE IF EXISTS hotel_reservation;

-- Tạo mới database và sử dụng nó
CREATE DATABASE hotel_reservation CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
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
  `country` VARCHAR(100) NULL DEFAULT NULL,
  `role` ENUM('admin', 'user', 'partner') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------
-- Bảng 3: `city`
-- ------------------------------------------------------------------
CREATE TABLE `city` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------
-- Bảng 6: `booking`
-- ------------------------------------------------------------------
CREATE TABLE `booking` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `room_id` INT NOT NULL,
  `check_in_date` DATE NOT NULL,
  `check_out_date` DATE NOT NULL,
  `group_type` VARCHAR(50) NULL DEFAULT NULL,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `room`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------
-- Bảng 7: `review`
-- ------------------------------------------------------------------
CREATE TABLE `review` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hotel_id` INT NOT NULL,
  `account_id` INT NOT NULL,
  `booking_id` INT NULL, -- Liên kết với booking đã hoàn thành
  
  -- 7 điểm chi tiết do người dùng chấm
  `rating_staff` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  `rating_amenities` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  `rating_cleanliness` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  `rating_comfort` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  `rating_value` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  `rating_location` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  `rating_wifi` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  
  -- Điểm từ AI và bình luận
  `ai_rating` DECIMAL(3, 1) NULL DEFAULT NULL,
  `rating_text` VARCHAR(50) NULL DEFAULT NULL,
  `comment` TEXT,
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`hotel_id`) REFERENCES `hotel`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`booking_id`) REFERENCES `booking`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `uq_booking_review` (`booking_id`) -- Đảm bảo 1 booking chỉ có 1 review
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `hotel_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hotel_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_thumbnail` BOOLEAN DEFAULT FALSE,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`hotel_id`) REFERENCES `hotel`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- =================================================================
-- Dữ liệu mẫu (Sample Data)
-- =================================================================

-- Chèn tài khoản mẫu
-- Mật khẩu admin: admin123
-- Mật khẩu partner: partner123
-- Mật khẩu user: user123
INSERT INTO `account` (`id`, `username`, `fullname`, `email`, `password`, `role`) VALUES
(1, 'admin', 'Quản Trị Viên', 'admin@gmail.com', '$2y$10$wyGTVCICfuAUG/jQQTlb.e4wU3LxRrPXnwOtBRcSVbc5YGb6iwxKi', 'admin');

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
        -- Tính điểm trung bình tổng (dựa trên điểm AI)
        rating = (SELECT AVG(ai_rating) FROM review WHERE hotel_id = current_hotel_id AND ai_rating IS NOT NULL),
        total_rating = (SELECT COUNT(*) FROM review WHERE hotel_id = current_hotel_id),

        -- Tính điểm trung bình cho 7 tiêu chí (dựa trên điểm người dùng chấm)
        service_staff = (SELECT AVG(rating_staff) FROM review WHERE hotel_id = current_hotel_id),
        amenities = (SELECT AVG(rating_amenities) FROM review WHERE hotel_id = current_hotel_id),
        cleanliness = (SELECT AVG(rating_cleanliness) FROM review WHERE hotel_id = current_hotel_id),
        comfort = (SELECT AVG(rating_comfort) FROM review WHERE hotel_id = current_hotel_id),
        value_for_money = (SELECT AVG(rating_value) FROM review WHERE hotel_id = current_hotel_id),
        location = (SELECT AVG(rating_location) FROM review WHERE hotel_id = current_hotel_id),
        free_wifi = (SELECT AVG(rating_wifi) FROM review WHERE hotel_id = current_hotel_id)
    WHERE id = current_hotel_id;
END$$
DELIMITER ;

-- Hoàn tất!
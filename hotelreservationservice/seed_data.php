<?php
// seed_data.php
ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1024M');

echo "<pre>";

// --- 1. Setup ---
define('BASE_URL', '/Hotel-Reservation-Service/hotelreservationservice');
require_once 'app/config/database.php';
require_once 'app/config/constants.php';
require_once 'app/models/CityModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/models/AccountModel.php';
require_once 'app/models/ReviewModel.php';
require_once 'app/models/RoomModel.php';

// Kết nối DB
try {
    $db = (new Database())->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// Khởi tạo Models
$cityModel = new CityModel($db);
$hotelModel = new HotelModel($db);
$accountModel = new AccountModel($db);
$reviewModel = new ReviewModel($db);
$roomModel = new RoomModel($db);

try {
    // Chuẩn bị các câu lệnh INSERT
    $imageInsertStmt = $db->prepare("INSERT INTO hotel_images (hotel_id, image_path, is_thumbnail, display_order) VALUES (:hotel_id, :image_path, :is_thumbnail, :display_order)");

    $bookingInsertStmt = $db->prepare(
        "INSERT INTO booking (account_id, room_id, check_in_date, check_out_date, group_type, total_price, status, created_at) 
         VALUES (:account_id, :room_id, :check_in_date, :check_out_date, :group_type, :total_price, :status, :created_at)"
    );
} catch (PDOException $e) {
    die("Lỗi chuẩn bị câu lệnh SQL: " . $e->getMessage());
}

// Đường dẫn đến thư mục chứa dữ liệu JSON và Ảnh
$jsonBaseDir = __DIR__ . '/../Hotel information'; // Thư mục chứa các tỉnh JSON (thư mục gốc)
$imageBaseDir = 'public/images/hotelimages'; // Thư mục ảnh đã copy vào public (đường dẫn tương đối)

echo "========================================\n";
echo "BẮT ĐẦU IMPORT DỮ LIỆU\n";
echo "Kiểm tra thư mục JSON tại: " . realpath($jsonBaseDir) . "\n";
echo "========================================\n";


$cityFolderNameMapping = [
    'ba-ria-vung-tau' => 'Bà Rịa Vũng Tàu',
    'binh-duong' => 'Bình Dương',
    'ca-mau' => 'Cà Mau',
    'can-tho' => 'Cần Thơ',
    'dak-lak' => 'Đăk Lăk', // Lưu ý dấu
    'da-lat' => 'Đà Lạt',
    'da-nang' => 'Đà Nẵng',
    'gia-lai' => 'Gia Lai',
    'ha-giang' => 'Hà Giang',
    'hai-phong' => 'Hải Phòng',
    'ha-long' => 'Hạ Long',
    'ha-noi' => 'Hà Nội',
    'ho-chi-minh' => 'Hồ Chí Minh',
    'hoi-an' => 'Hội An',
    'hue' => 'Huế',
    'mui-ne' => 'Mũi Né',
    'nghe-an' => 'Nghệ An',
    'nha-trang' => 'Nha Trang',
    'ninh-thuan' => 'Ninh Thuận',
    'phan-thiet' => 'Phan Thiết',
    'phu-quoc' => 'Phú Quốc',
    'phu-yen' => 'Phú Yên',
    'quang-binh' => 'Quảng Bình',
    'sa-pa' => 'Sa Pa',
    'thanh-hoa' => 'Thanh Hoá',
    'tra-vinh' => 'Trà Vinh',
    'vinh-long' => 'Vĩnh Long',
];
// --- 2. Quét thư mục Tỉnh/Thành phố ---
$cityFolders = glob($jsonBaseDir . '/*', GLOB_ONLYDIR);

if (empty($cityFolders)) {
    die("Lỗi: Không tìm thấy thư mục tỉnh/thành phố nào trong '$jsonBaseDir'\n");
}
foreach ($cityFolders as $cityFolder) {
    $cityFolderName = basename($cityFolder);
    $cityName = $cityFolderNameMapping[$cityFolderName] ?? ucwords(str_replace(['-', '_'], ' ', $cityFolderName));

    echo "\n---> Đang xử lý Thành phố: " . $cityName . " (Thư mục: " . $cityFolderName . ")\n";
    // --- 3. Xử lý City ---
    $city = $cityModel->getCityByName($cityName); // Dùng tên đã chuẩn hóa
    if (!$city) {
        // Ảnh đại diện thành phố (lấy từ thư mục cityimages nếu có)
        $cityImagePath = "public/images/cityimages/" . $cityFolderName . ".jpg"; // Giả sử tên file ảnh khớp
        if (!file_exists($cityImagePath)) {
            $cityImagePath = null; // Hoặc đường dẫn ảnh mặc định
            echo "    Cảnh báo: Không tìm thấy ảnh cho thành phố: " . $cityImagePath . "\n";
        } else {
            echo "    Tìm thấy ảnh thành phố: " . $cityImagePath . "\n";
        }

        if ($cityModel->addCity($cityName, $cityImagePath)) {
            $city_id = $db->lastInsertId();
            echo "    + Đã thêm thành phố mới vào CSDL với ID: " . $city_id . "\n";
        } else {
            echo "    !!! LỖI: Không thể thêm thành phố: " . $cityName . "\n";
            continue; // Bỏ qua thành phố này nếu lỗi
        }
    } else {
        $city_id = $city->id;
        echo "    -> Thành phố đã tồn tại trong CSDL với ID: " . $city_id . "\n";
    }
    // --- 4. Quét thư mục Khách sạn trong thành phố ---
    $hotelJsonFiles = glob($cityFolder . '/*.json');

    if (empty($hotelJsonFiles)) {
        echo "    Thông báo: Không tìm thấy file JSON khách sạn nào trong thư mục " . $cityFolderName . "\n";
        continue;
    }

    foreach ($hotelJsonFiles as $jsonFile) {
        $hotelJsonFileName = basename($jsonFile);
        $hotelFolderName = basename($jsonFile, '.json'); // Tên thư mục ảnh/json
        echo "\n  --> Đang xử lý Khách sạn JSON: " . $hotelJsonFileName . "\n";

        $jsonData = json_decode(file_get_contents($jsonFile));

        if (!$jsonData || json_last_error() !== JSON_ERROR_NONE || !isset($jsonData->name)) {
            echo "    !!! LỖI: File JSON không hợp lệ hoặc thiếu tên khách sạn: " . $jsonFile . ". Lỗi JSON: " . json_last_error_msg() . "\n";
            continue; // Bỏ qua file lỗi
        }

        $hotelName = $jsonData->name; // Lấy tên từ JSON

        // --- KIỂM TRA KHÁCH SẠN ĐÃ TỒN TẠI CHƯA (Dựa vào tên và city_id) ---
        // Cần thêm hàm getHotelByNameAndCity vào HotelModel
        // $existingHotel = $hotelModel->getHotelByNameAndCity($hotelName, $city_id); // Tạm thời bỏ qua kiểm tra trùng

        // --- 5. Xử lý Ảnh Khách sạn ---
        $hotelImageFolderPath_FS = __DIR__ . '/' . $imageBaseDir . '/' . $cityFolderName . '/' . $hotelFolderName; // Đường dẫn vật lý
        $hotelImageFolderPath_Web = $imageBaseDir . '/' . $cityFolderName . '/' . $hotelFolderName; // Đường dẫn lưu CSDL

        $hotelRepresentativeImage = null; // Đường dẫn ảnh đại diện (web path)
        $allHotelImages = []; // Mảng chứa TẤT CẢ ảnh (web path)
        if (is_dir($hotelImageFolderPath_FS)) {
            $images_fs = glob($hotelImageFolderPath_FS . '/*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);

            if (!empty($images_fs)) {
                // Lấy ảnh đầu tiên làm đại diện, lưu đường dẫn WEB
                $hotelRepresentativeImage = $hotelImageFolderPath_Web . '/' . basename($images_fs[0]);

                // Lưu tất cả đường dẫn WEB
                foreach ($images_fs as $img_fs) {
                    $allHotelImages[] = $hotelImageFolderPath_Web . '/' . basename($img_fs);
                }

                echo "Ảnh đại diện: " . $hotelRepresentativeImage . "\n";
                echo "Tìm thấy tổng cộng " . count($allHotelImages) . " ảnh.\n";
            } else {
                echo "Cảnh báo: Không tìm thấy file ảnh nào trong thư mục: " . $hotelImageFolderPath_FS . "\n";
            }
        } else {
            echo "Cảnh báo: Không tìm thấy thư mục ảnh: " . $hotelImageFolderPath_FS . "\n";
        }

        // --- 6. Xử lý Hotel ---
        $eval = $jsonData->evaluation_categories ?? (object)[];
        $hotelAdded = $hotelModel->addHotel(
            $hotelName,
            $jsonData->address ?? '',
            $jsonData->description ?? '',
            $city_id,
            $hotelRepresentativeImage, // Chỉ lưu ảnh đại diện vào bảng hotel
            (float)($eval->service_staff ?? 8.0),
            (float)($eval->amenities ?? 8.0),
            (float)($eval->cleanliness ?? 8.0),
            (float)($eval->comfort ?? 8.0),
            (float)($eval->value_for_money ?? 8.0),
            (float)($eval->location ?? 8.0),
            (float)($eval->free_wifi ?? 8.0)
        );


        if ($hotelAdded === true) {
            $hotel_id = $db->lastInsertId();
            echo "      + Đã thêm khách sạn mới vào CSDL với ID: " . $hotel_id . "\n";

            // <<< === THÊM MỚI: LƯU TẤT CẢ ẢNH VÀO hotel_images === >>>
            $imageOrder = 0;
            $thumbnailSet = false;
            foreach ($allHotelImages as $imagePath) {
                $isThumbnail = false;
                // Đánh dấu ảnh đầu tiên là thumbnail (hoặc ảnh đại diện đã chọn)
                if ($imagePath === $hotelRepresentativeImage && !$thumbnailSet) {
                    $isThumbnail = true;
                    $thumbnailSet = true;
                }

                try {
                    $imageInsertStmt->execute([
                        ':hotel_id' => $hotel_id,
                        ':image_path' => $imagePath,
                        ':is_thumbnail' => (int)$isThumbnail,
                        ':display_order' => $imageOrder++
                    ]);
                } catch (PDOException $e) {
                    echo "    !!! LỖI: Không thể lưu ảnh '$imagePath' vào hotel_images: " . $e->getMessage() . "\n";
                }
            }
            echo "      + Đã lưu " . $imageOrder . " ảnh vào bảng hotel_images.\n";
            // <<< === KẾT THÚC THÊM MỚI === >>>


            // <<< === LOGIC TẠO PHÒNG TỰ ĐỘNG === >>>
            echo "        -> Bắt đầu tạo phòng mẫu...\n";
            // ... (Code tạo phòng mẫu giữ nguyên) ...
            $roomCounter = 1;
            $roomsCreatedCount = 0;
            foreach (ALLOWED_ROOM_TYPES as $roomType) {
                for ($i = 1; $i <= 5; $i++) {
                    // ... (Code tạo $roomNumber, $randomPrice, $capacity) ...
                    $roomNumber = 'P' . ($roomCounter++);
                    $randomPrice = mt_rand(100, 700) * 1000;
                    $capacity = 2;
                    if (str_contains(strtolower($roomType), 'gia đình')) $capacity = 4;
                    elseif (str_contains(strtolower($roomType), 'superior') || str_contains(strtolower($roomType), 'deluxe')) $capacity = 3;

                    $roomAddResult = $roomModel->addRoom(
                        $hotel_id,
                        $roomNumber,
                        $roomType,
                        $capacity,
                        $randomPrice,
                        "Phòng " . $roomType . " tại " . $hotelName,
                        null
                    );
                    if ($roomAddResult === true) $roomsCreatedCount++;
                }
            }
            echo "        -> Đã tạo thành công " . $roomsCreatedCount . " phòng mẫu.\n";
            // <<< === KẾT THÚC LOGIC TẠO PHÒNG === >>>

            // --- 7. Xử lý Reviews ---
            $reviewCount = 0;
            if (isset($jsonData->reviews) && is_array($jsonData->reviews)) {
                foreach ($jsonData->reviews as $reviewData) {
                    if (!isset($reviewData->reviewer) || !isset($reviewData->review)) continue; // Bỏ qua nếu cấu trúc sai

                    $reviewerName = trim($reviewData->reviewer->name ?? 'Userẩn danh');
                    if (empty($reviewerName)) {
                        $reviewerName = 'UserẨnDanh' . uniqid();
                    }
                    $reviewerAvatar = $reviewData->reviewer->avatar ?? null;
                    $reviewerCountry = $reviewData->reviewer->country ?? null; // <<< LẤY COUNTRY >>>

                    $reviewInfo = $reviewData->review;

                    // --- 7a. Tìm hoặc Tạo Account ---
                    $account_id = null; // Khởi tạo là null
                    $account = $accountModel->getAccountByUsername($reviewerName); // Thử tìm bằng tên (có thể đã được tạo trước đó)

                    if (!$account) {
                        // <<< SỬA: Logic tạo username/email duy nhất >>>
                        $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($reviewerName));
                        if (empty($baseUsername)) $baseUsername = 'user';
                        $usernameToSave = $baseUsername . '_' . uniqid();
                        $emailToSave = $usernameToSave . '@dummy-hrs.com';
                        if ($accountModel->existsByUsername($usernameToSave) || $accountModel->existsByEmail($emailToSave)) {
                            continue;
                        }

                        // <<< TRUYỀN COUNTRY VÀO SAVE >>>
                        $saveResult = $accountModel->save($usernameToSave, $reviewerName, $emailToSave, 'password123', ROLE_USER, $reviewerAvatar, $reviewerCountry);

                        if ($saveResult) {
                            $account_id = $db->lastInsertId();
                            echo "        + Tạo tài khoản giả: '$usernameToSave' (ID: $account_id) Country: " . ($reviewerCountry ?: 'NULL') . "\n";
                        } else {
                            echo "        !!! LỖI: AccountModel::save() thất bại cho '$usernameToSave'\n";
                            continue;
                        }
                    } else {
                        $account_id = $account->id;
                        // <<< LOGIC CẬP NHẬT COUNTRY CHO TÀI KHOẢN ĐÃ CÓ (OPTIONAL) >>>
                        if (empty($account->country) && !empty($reviewerCountry)) {
                            // Cập nhật country nếu tài khoản cũ chưa có
                            if ($accountModel->updateAccountInfo($account_id, $account->fullname, $account->email, $account->role, $reviewerCountry)) {
                                echo "        * Cập nhật country '$reviewerCountry' cho tài khoản ID: $account_id\n";
                            } else {
                                echo "        !!! LỖI: Không thể cập nhật country cho tài khoản ID: $account_id\n";
                            }
                        }
                    }
                    // --- 7b. TẠO BOOKING GIẢ LẬP ---
                    $booking_id_to_save = null; // Khởi tạo
                    $reviewDateStr = $reviewInfo->date ?? null; // "24/09/2023"
                    $stayDurationStr = $reviewInfo->stay_duration ?? '1 đêm'; // "1 đêm · tháng 9/2023"

                    // 1. Trích xuất số đêm
                    $nights = 1; // Mặc định
                    if (preg_match('/(\d+)\s*đêm/', $stayDurationStr, $matches)) {
                        $nights = (int)$matches[1];
                    }

                    // 2. Phân tích ngày (dùng $createdAtTimestamp đã có)
                    $createdAtTimestamp = null; // Dùng cho review
                    $checkOutDateObj = null; // Dùng cho booking
                    if ($reviewDateStr) {
                        $normalizedDateStr = preg_replace('#/0(\d{2})/#', '/$1/', $reviewDateStr);
                        $timestamp = strtotime(str_replace('/', '-', $normalizedDateStr));
                        if ($timestamp !== false) {
                            $checkOutDateObj = new DateTime(date('Y-m-d H:i:s', $timestamp));
                            $createdAtTimestamp = $checkOutDateObj->format('Y-m-d H:i:s'); // Ngày tạo review = ngày checkout
                        }
                    }

                    // 3. Nếu ngày hợp lệ, tạo booking
                    if ($checkOutDateObj) {
                        $checkOutDateStr = $checkOutDateObj->format('Y-m-d');

                        // Tính ngày nhận phòng
                        $checkInDateObj = clone $checkOutDateObj;
                        $checkInDateObj->modify("-$nights days");
                        $checkInDateStr = $checkInDateObj->format('Y-m-d');

                        // Ngày đặt (created_at) = ngày nhận phòng (theo yêu cầu)
                        $bookingCreatedAtStr = $checkInDateStr . ' 12:00:00'; // Giả sử đặt lúc 12h trưa

                        // Lấy 1 room_id ngẫu nhiên thuộc khách sạn này
                        $roomId = $roomModel->getRandomRoomIdForHotel($hotel_id);

                        if ($roomId) {
                            $groupType = $reviewInfo->group_type ?? null; // "Cặp đôi"
                            $randomPricePerNight = mt_rand(100, 700) * 1000; // Giá ngẫu nhiên 100k-700k
                            $totalPrice = $randomPricePerNight * $nights;

                            try {
                                $bookingInsertStmt->execute([
                                    ':account_id' => $account_id,
                                    ':room_id' => $roomId,
                                    ':check_in_date' => $checkInDateStr,
                                    ':check_out_date' => $checkOutDateStr,
                                    ':group_type' => $groupType,
                                    ':total_price' => $totalPrice,
                                    ':status' => BOOKING_STATUS_CHECKED_OUT, // Đã check out vì có review
                                    ':created_at' => $bookingCreatedAtStr
                                ]);
                                $booking_id_to_save = $db->lastInsertId(); // Lấy ID booking vừa tạo
                            } catch (PDOException $e) {
                                echo "        !!! LỖI: Không thể tạo booking giả: " . $e->getMessage() . "\n";
                            }
                        } else {
                            echo "        !!! LỖI: Không tìm thấy phòng nào cho hotel ID $hotel_id để tạo booking giả.\n";
                        }
                    } else {
                        echo "        !!! Cảnh báo: Không thể phân tích ngày '$reviewDateStr' để tạo booking giả. Review sẽ không được liên kết.\n";
                    }
                    // --- KẾT THÚC 7b ---

                    // --- 7c. Chuẩn bị dữ liệu Review ---
                    if ($account_id === null || $account_id <= 0) {
                        echo "        !!! LỖI: account_id không hợp lệ ($account_id) trước khi lưu review cho '$reviewerName'\n";
                        continue; // Bỏ qua nếu không có account_id
                    }
                    // Kết hợp comment positive và negative
                    $commentParts = [];
                    // Chỉ lấy comment_positive nếu nó không phải là null hoặc chuỗi rỗng
                    if (!empty(trim($reviewInfo->comment_positive ?? ''))) {
                        $commentParts[] = trim($reviewInfo->comment_positive);
                    }
                    // Bỏ qua comment_negative theo yêu cầu
                    // if (!empty(trim($reviewInfo->comment_negative ?? ''))) {
                    //     $commentParts[] = "Nhược điểm: " . trim($reviewInfo->comment_negative);
                    // }
                    // Nếu cả hai đều rỗng, comment sẽ là chuỗi rỗng ''
                    $comment = implode("\n", $commentParts);
                    if (empty($comment)) {
                        echo "        Thông báo: Bỏ qua review không có bình luận.\n";
                        continue; // Bỏ qua review nếu không có bình luận nào
                    }

                    $aiRating = isset($reviewInfo->score) && is_numeric($reviewInfo->score) ? (float)$reviewInfo->score : null;
                    $ratingText = $reviewInfo->rating ?? RatingHelper::getTextFromScore($aiRating);

                    // <<< SỬA: Lấy 7 điểm chi tiết từ evaluation_categories của khách sạn >>>
                    $eval = $jsonData->evaluation_categories ?? (object)[]; // Lấy điểm trung bình KS
                    // Gán điểm trung bình KS làm điểm chi tiết cho review này
                    $ratingStaff =       max(1.0, min(10.0, (float)($eval->service_staff ?? 5.0)));
                    $ratingAmenities =   max(1.0, min(10.0, (float)($eval->amenities ?? 5.0)));
                    $ratingCleanliness = max(1.0, min(10.0, (float)($eval->cleanliness ?? 5.0)));
                    $ratingComfort =     max(1.0, min(10.0, (float)($eval->comfort ?? 5.0)));
                    $ratingValue =       max(1.0, min(10.0, (float)($eval->value_for_money ?? 5.0))); // Đổi key
                    $ratingLocation =    max(1.0, min(10.0, (float)($eval->location ?? 5.0)));
                    $ratingWifi =        max(1.0, min(10.0, (float)($eval->free_wifi ?? 5.0))); // Đổi key

                    // <<< SỬA: Xử lý Ngày tạo Review >>>
                    $reviewDateStr = $reviewInfo->date ?? null;
                    $createdAtTimestamp = null;
                    if ($reviewDateStr) {
                        // BƯỚC CHUẨN HÓA: Loại bỏ số 0 thừa ở đầu tháng (ví dụ: 4/012/2022 -> 4/12/2022)
                        $normalizedDateStr = preg_replace('#/0(\d{2})/#', '/$1/', $reviewDateStr);
                        // Giải thích regex: Tìm /0 theo sau bởi 2 chữ số rồi /, thay thế bằng /2 chữ số/

                        // Cố gắng chuyển đổi nhiều định dạng phổ biến sang timestamp
                        $timestamp = strtotime(str_replace('/', '-', $normalizedDateStr)); // Dùng ngày đã chuẩn hóa

                        if ($timestamp !== false) {
                            // Format lại thành 'Y-m-d H:i:s'
                            $createdAtTimestamp = date('Y-m-d H:i:s', $timestamp);
                        } else {
                            // Ghi log lỗi rõ hơn
                            error_log("Cannot parse review date (after normalization): Original='$reviewDateStr', Normalized='$normalizedDateStr' in JSON file for hotel ID: " . $hotel_id);
                            echo "        !!! Cảnh báo: Không thể phân tích ngày review (kể cả sau chuẩn hóa): '$reviewDateStr'\n";
                            // Vẫn tiếp tục xử lý review, CSDL sẽ dùng NOW()
                        }
                    }
                    $jsonReviewRoomType = $reviewInfo->room_type ?? null;
                    $jsonReviewGroupType = $reviewInfo->group_type ?? null;
                    $jsonStayDuration = $reviewInfo->stay_duration ?? '1 đêm';

                    // Trích xuất số đêm
                    $jsonReviewNights = 1;
                    if (preg_match('/(\d+)\s*đêm/', $jsonStayDuration, $matches)) {
                        $jsonReviewNights = (int)$matches[1];
                    }

                    // Lưu review (Truyền thêm $createdAtTimestamp)
                    $reviewAdded = $reviewModel->addReview(
                        $hotel_id,
                        $account_id,
                        null, // booking_id null
                        $comment,
                        $ratingStaff,
                        $ratingAmenities,
                        $ratingCleanliness,
                        $ratingComfort,
                        $ratingValue,
                        $ratingLocation,
                        $ratingWifi,
                        $aiRating,
                        $ratingText,
                        $createdAtTimestamp,
                        $jsonReviewRoomType,
                        $jsonReviewGroupType,
                        $jsonReviewNights
                    );
                    if ($reviewAdded) {
                        $reviewCount++;
                    } else {
                        echo "        !!! LỖI: ReviewModel::addReview() thất bại cho tài khoản ID: $account_id\n";
                    }
                } // End foreach review
                echo "      -> Đã xử lý " . $reviewCount . " reviews.\n";
            } else {
                echo "      Không có reviews nào trong file JSON.\n";
            }
        } else { // Nếu $hotelAdded không phải true
            echo "    !!! LỖI: Không thể thêm khách sạn '$hotelName'. ";
            if (is_array($hotelAdded)) { // Nếu hàm addHotel trả về mảng lỗi validation
                echo "Lỗi validation: " . print_r($hotelAdded, true) . "\n";
            } else {
                echo "Lỗi không xác định từ Model.\n";
            }
        }
    } // End foreach hotel json
} // End foreach city folder

echo "\n========================================\n";
echo "IMPORT HOÀN TẤT!\n";
echo "========================================\n";

echo "</pre>";

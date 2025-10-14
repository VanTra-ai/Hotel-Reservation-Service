<?php
require_once 'app/models/ReviewModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/config/database.php';
require_once 'app/helpers/RatingHelper.php';

class ReviewController
{
    private $reviewModel;
    private $hotelModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->reviewModel = new ReviewModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
    }

    /**
     * Thêm review và gọi AI để chấm điểm
     */
    public function add()
    {
        SessionHelper::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. Lấy dữ liệu từ FORM
            $hotelId  = (int)($_POST['hotel_id'] ?? 0);
            $accountId = SessionHelper::getAccountId();
            $rating   = (int)($_POST['rating'] ?? 5);
            $comment  = trim($_POST['comment'] ?? '');

            // --- BẮT ĐẦU GỌI AI ---
            $predicted_score = null;
            try {
                // 2. Chuẩn bị dữ liệu cho AI
                $hotel = $this->hotelModel->getHotelById($hotelId);
                $hotel_info = [];
                if ($hotel) {
                    $hotel_info = [
                        (float)$hotel->service_staff,
                        (float)$hotel->amenities,
                        (float)$hotel->cleanliness,
                        (float)$hotel->comfort,
                        (float)$hotel->value_for_money,
                        (float)$hotel->location,
                        (float)$hotel->free_wifi
                    ];
                }

                // Tạm thời dùng giá trị mặc định để test
                $room_type_text = 'Phòng Giường Đôi';
                $group_type_text = 'Cặp đôi/Hai người';
                $stay_duration_text = '1 đêm';

                $mapping_file = file_get_contents('app/config/metadata_mapping.json');
                $mappings = json_decode($mapping_file, true);

                $review_info = [
                    $mappings['room_type_mapping'][$room_type_text] ?? 0,
                    $mappings['stay_duration_mapping'][$stay_duration_text] ?? 1,
                    $mappings['group_type_mapping'][$group_type_text] ?? 0
                ];

                // 3. Gọi API để lấy điểm dự đoán
                if ($comment && !empty($hotel_info)) {
                    $predicted_score = $this->get_ai_rating($comment, $hotel_info, $review_info);
                }
            } catch (Exception $e) {
                error_log("AI Prediction Error: " . $e->getMessage());
                $predicted_score = null;
            }

            $rating_text = RatingHelper::getTextFromScore($predicted_score);

            // 4. Lưu vào Database (cả điểm user, điểm AI, và VĂN BẢN ĐÁNH GIÁ)
            $reviewAdded = $this->reviewModel->addReview(
                $hotelId,
                $accountId,
                $rating,
                $comment,
                $_POST['category'] ?? null,
                $predicted_score,
                $rating_text
            );
            if ($reviewAdded) {
                $this->hotelModel->recalculateHotelRating($hotelId);
            }

            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã gửi đánh giá!'];
            header("Location: " . BASE_URL . "/hotel/show/" . $hotelId);
            exit();
        }
    }

    public function listByHotel($hotelId)
    {
        $reviews = $this->reviewModel->getReviewsByHotelId($hotelId);
        include 'app/views/review/list.php';
    }
    /**
     * Gửi yêu cầu đến API Python để lấy điểm số dự đoán từ AI.
     */
    private function get_ai_rating($comment, $hotel_info, $review_info)
    {
        // Địa chỉ của API Flask đang chạy trên máy của bạn
        $apiUrl = 'http://127.0.0.1:5000/predict';

        $postData = [
            'comment' => $comment,
            'hotel_info' => $hotel_info,
            'review_info' => $review_info
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        // Đặt timeout để không phải chờ quá lâu
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $result = json_decode($response, true);
            return $result['predicted_score'] ?? null;
        } else {
            error_log("AI API call failed: " . $response);
            return null;
        }
    }
}

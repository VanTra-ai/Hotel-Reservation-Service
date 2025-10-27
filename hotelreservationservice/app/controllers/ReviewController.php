<?php
// app/controllers/ReviewController.php
require_once 'app/models/ReviewModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/config/database.php';
require_once 'app/helpers/RatingHelper.php';
require_once 'app/helpers/AiApiService.php';

class ReviewController
{
    private $reviewModel;
    private $hotelModel;
    private $db;
    private AiApiService $aiService;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->reviewModel = new ReviewModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
        $this->aiService = new AiApiService();
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
                    $predicted_score = $this->aiService->getPredictedRating($comment, $hotel_info, $review_info); // <--- Sử dụng service
                }
            } catch (Exception $e) {
                error_log("AI Data Preparation Error: " . $e->getMessage());
                $predicted_score = null; // Đảm bảo null nếu có lỗi chuẩn bị dữ liệu
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
}

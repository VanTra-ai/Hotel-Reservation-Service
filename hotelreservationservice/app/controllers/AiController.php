<?php
// app/controllers/AiController.php
require_once 'app/models/HotelModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/helpers/RatingHelper.php';
require_once 'app/helpers/AiApiService.php';

class AiController
{
    private $db;
    private $hotelModel;
    private AiApiService $aiService;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->hotelModel = new HotelModel($this->db);
        $this->aiService = new AiApiService();
    }

    /**
     * Hiển thị trang giao diện dự đoán AI
     */
    public function index()
    {
        $data['hotels'] = $this->hotelModel->getHotels();
        $data['mappings'] = json_decode(file_get_contents('app/config/metadata_mapping.json'), true);

        // Sắp xếp lại stay_duration cho UI
        $stay_duration_mapping = $data['mappings']['stay_duration_mapping'];
        uasort($stay_duration_mapping, function ($a, $b) {
            return $a <=> $b;
        });
        $data['mappings']['stay_duration_mapping_sorted'] = $stay_duration_mapping;

        $data['known_room_types'] = [
            'Phòng Tiêu Chuẩn Giường Đôi',
            'Phòng Superior Giường Đôi',
            'Phòng Giường Đôi',
            'Phòng Deluxe Giường Đôi Có Ban Công',
            'Phòng Deluxe Giường Đôi',
            'Phòng Giường Đôi Có Ban Công',
            'Phòng Superior Giường Đôi Có Ban Công',
            'Phòng Gia Đình',
            'Phòng Deluxe Gia đình',
            'Phòng Superior Giường Đôi/2 Giường Đơn',
        ];

        include 'app/views/ai/predict.php';
    }

    /**
     * API nội bộ: Lấy 7 điểm đặc trưng của một khách sạn
     */
    public function getHotelInfo($hotelId)
    {
        header('Content-Type: application/json');
        $hotel = $this->hotelModel->getHotelById((int)$hotelId);

        if ($hotel) {
            // Trả về một đối tượng JSON chứa 7 điểm số
            echo json_encode([
                'service_staff' => (float)$hotel->service_staff,
                'amenities' => (float)$hotel->amenities,
                'cleanliness' => (float)$hotel->cleanliness,
                'comfort' => (float)$hotel->comfort,
                'value_for_money' => (float)$hotel->value_for_money,
                'location' => (float)$hotel->location,
                'free_wifi' => (float)$hotel->free_wifi
            ]);
        } else {
            echo json_encode(null);
        }
        exit;
    }

    /**
     * API nội bộ: Nhận dữ liệu từ form, gọi API Python và trả về kết quả
     */
    public function performPrediction()
    {
        header('Content-Type: application/json');
        $postData = json_decode(file_get_contents('php://input'), true);

        // 1. Gọi API Python để lấy điểm số dạng số
        $predicted_score = $this->aiService->getPredictedRating(
            $postData['comment'] ?? '',
            $postData['hotel_info'] ?? [],
            $postData['review_info'] ?? []
        );

        // 2. Gọi "Người Phiên Dịch" để lấy văn bản
        $rating_text = RatingHelper::getTextFromScore($predicted_score);

        // 3. Trả về một đối tượng JSON chứa cả hai thông tin
        echo json_encode([
            'predicted_score' => $predicted_score,
            'rating_text' => $rating_text
        ]);
        exit;
    }
}

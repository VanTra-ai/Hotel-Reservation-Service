<?php
// app/controllers/ReviewController.php
require_once 'app/models/ReviewModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/config/database.php';
require_once 'app/helpers/RatingHelper.php';
require_once 'app/helpers/AiApiService.php';
require_once 'app/models/BookingModel.php';

class ReviewController
{
    private $reviewModel;
    private $hotelModel;
    private $bookingModel;
    private $db;
    private AiApiService $aiService;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->reviewModel = new ReviewModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
        $this->bookingModel = new BookingModel($this->db);
        $this->aiService = new AiApiService();
    }

    /**
     * Thêm review và gọi AI để chấm điểm
     */
    public function add()
    {
        SessionHelper::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. Lấy dữ liệu thô từ FORM
            $bookingId = (int)($_POST['booking_id'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');
            $accountId = SessionHelper::getAccountId();
            if (empty($comment)) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Vui lòng nhập bình luận để gửi đánh giá.'];
                // Do form không có dữ liệu để giữ lại, chúng ta redirect về trang booking history
                header('Location: ' . BASE_URL . '/booking/history');
                exit();
            }

            // 7 điểm chi tiết do người dùng chấm
            $ratingStaff = (float)($_POST['rating_staff'] ?? 1.0);
            $ratingAmenities = (float)($_POST['rating_amenities'] ?? 1.0);
            $ratingCleanliness = (float)($_POST['rating_cleanliness'] ?? 1.0);
            $ratingComfort = (float)($_POST['rating_comfort'] ?? 1.0);
            $ratingValue = (float)($_POST['rating_value'] ?? 1.0);
            $ratingLocation = (float)($_POST['rating_location'] ?? 1.0);
            $ratingWifi = (float)($_POST['rating_wifi'] ?? 1.0);

            // 2. Lấy thông tin booking để xác định ngữ cảnh AI (review_info) và hotel_info
            $booking = $this->bookingModel->getBookingByIdForReview($bookingId, $accountId);
            $hotel = $this->hotelModel->getHotelById($booking->hotel_id);

            if (!$booking || !$hotel) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Booking hoặc khách sạn không tồn tại/không hợp lệ.'];
                header('Location: ' . BASE_URL . '/booking/history');
                exit();
            }

            // --- BẮT ĐẦU GỌI AI ---
            $predicted_score = null;
            $rating_text = 'Chưa có đánh giá';
            try {
                // 3. Chuẩn bị dữ liệu cho AI (review_info)
                // Lấy thông tin từ booking/hotel
                $room_type_text = $booking->room_type ?? 'Phòng Giường Đôi';
                // Tạm thời hardcode group_type/stay_duration nếu CSDL chưa có cột đó trong booking
                $group_type_text = 'Cặp đôi/Hai người';
                $stay_duration_text = $booking->nights . ' đêm'; // Dùng số đêm tính được

                $mapping_file = file_get_contents('app/config/metadata_mapping.json');
                $mappings = json_decode($mapping_file, true);

                // Chuyển đổi sang ID số cho AI
                $review_info = [
                    $mappings['room_type_mapping'][$room_type_text] ?? 0,
                    $mappings['stay_duration_mapping'][$stay_duration_text] ?? 1,
                    $mappings['group_type_mapping'][$group_type_text] ?? 0
                ];

                // 4. Chuẩn bị Hotel Info (7 điểm tĩnh) cho AI (CẦN THIẾT vì mô hình được huấn luyện với nó)
                $hotel_info = [
                    (float)$hotel->service_staff,
                    (float)$hotel->amenities,
                    (float)$hotel->cleanliness,
                    (float)$hotel->comfort,
                    (float)$hotel->value_for_money,
                    (float)$hotel->location,
                    (float)$hotel->free_wifi
                ];

                // 5. Gọi API để lấy điểm dự đoán
                if ($comment && !empty($hotel_info)) {
                    $predicted_score = $this->aiService->getPredictedRating($comment, $hotel_info, $review_info);
                    $rating_text = RatingHelper::getTextFromScore($predicted_score);
                }
            } catch (Exception $e) {
                error_log("AI Process Error: " . $e->getMessage());
                // Giữ predicted_score là null, rating_text là 'Chưa có đánh giá'
            }

            // 6. Lưu vào Database
            $reviewAdded = $this->reviewModel->addReview(
                $hotel->id,
                $accountId,
                $bookingId,
                $comment,
                $ratingStaff,
                $ratingAmenities,
                $ratingCleanliness,
                $ratingComfort,
                $ratingValue,
                $ratingLocation,
                $ratingWifi,
                $predicted_score,
                $rating_text
            );

            if ($reviewAdded) {
                // Trigger tự cập nhật rating của hotel.
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã gửi đánh giá!'];
            } else {
                // Nếu xảy ra lỗi CSDL (ví dụ: duplicate key)
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Đã xảy ra lỗi khi lưu đánh giá. Booking này có thể đã được đánh giá trước đó.']; // Thông báo lỗi rõ ràng hơn
            }

            header("Location: " . BASE_URL . "/hotel/show/" . $hotel->id);
            exit();
        }

        // Nếu không phải POST, redirect về trang chủ
        header("Location: " . BASE_URL . "/home");
        exit();
    }

    public function listByHotel($hotelId)
    {
        $reviews = $this->reviewModel->getReviewsByHotelId($hotelId);
        include 'app/views/review/list.php';
    }
    /**
     * Hiển thị form để user viết đánh giá cho một booking cụ thể.
     */
    public function showForm($bookingId)
    {
        SessionHelper::requireLogin();
        $accountId = SessionHelper::getAccountId();
        $errors = [];

        $booking = $this->bookingModel->getBookingByIdForReview($bookingId, $accountId);

        if (!$booking) {
            // Lỗi: Booking không hợp lệ
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Booking không hợp lệ hoặc không phải của bạn.'];
            header('Location: ' . BASE_URL . '/booking/history');
            exit();
        }

        // KIỂM TRA MỚI: Booking đã được đánh giá chưa?
        if (($booking->review_count ?? 0) > 0) {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Bạn đã hoàn tất đánh giá cho chuyến đi này.'];
            header('Location: ' . BASE_URL . '/hotel/show/' . $booking->hotel_id); // Chuyển về trang chi tiết khách sạn
            exit();
        }

        $data = [
            'booking' => $booking,
            'errors' => $errors
        ];

        include 'app/views/review/form.php';
    }
}

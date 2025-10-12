<?php
require_once 'app/models/ReviewModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/config/database.php';

class ReviewController
{
    private $reviewModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->reviewModel = new ReviewModel($this->db);
    }

    // Thêm review từ form
    public function add()
    {
        SessionHelper::startSession();

        if (!SessionHelper::isLoggedIn()) {
            $_SESSION['error_message'] = "Bạn cần đăng nhập để đánh giá.";
            header("Location: /Hotel-Reservation-Service/hotelreservationservice/Account/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Trim tất cả input
            $hotelId   = trim($_POST['hotel_id'] ?? '');
            $rating    = trim($_POST['rating'] ?? '');
            $comment   = trim($_POST['comment'] ?? '');
            $category  = trim($_POST['category'] ?? '');
            $accountId = $_SESSION['user_id'];

            // Validate dữ liệu
            $errors = [];

            if (empty($hotelId) || !is_numeric($hotelId)) {
                $errors[] = "Khách sạn không hợp lệ.";
            }

            if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
                $errors[] = "Đánh giá phải từ 1 đến 5.";
            }

            if (empty($category)) {
                $errors[] = "Hạng mục đánh giá không được để trống.";
            }

            if (count($errors) > 0) {
                $_SESSION['error_message'] = implode(', ', $errors);
                header("Location: /Hotel-Reservation-Service/hotelreservationservice/Hotel/show/$hotelId");
                exit();
            }

            // Gọi model để thêm review
            $result = $this->reviewModel->addReview($hotelId, $accountId, $rating, $comment, $category);

            // Xử lý kết quả trả về
            if (is_array($result)) {
                // Nếu model trả về lỗi validate
                $_SESSION['error_message'] = implode(', ', $result);
            } elseif ($result === true) {
                $_SESSION['success_message'] = "Đánh giá đã được gửi thành công!";
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi gửi đánh giá.";
            }

            header("Location: /Hotel-Reservation-Service/hotelreservationservice/Hotel/show/$hotelId");
            exit();
        }
    }

    // Lấy danh sách review theo hotel và render ra view
    public function listByHotel($hotelId)
    {
        $reviews = $this->reviewModel->getReviewsByHotelId($hotelId);

        // Truyền vào view
        include 'app/views/review/list.php';
    }
}

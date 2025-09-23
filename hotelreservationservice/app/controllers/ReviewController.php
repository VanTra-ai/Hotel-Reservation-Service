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
            header("Location: /hotelreservationservice/Account/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotelId  = $_POST['hotel_id'] ?? '';
    $rating   = $_POST['rating'] ?? '';
    $comment  = $_POST['comment'] ?? '';
    $category = $_POST['category'] ?? '';   // ✅ lấy thêm category
    $accountId = $_SESSION['user_id'];

    if ($this->reviewModel->addReview($hotelId, $accountId, $rating, $comment, $category)) {
        $_SESSION['success_message'] = "Đánh giá đã được gửi thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi gửi đánh giá.";
    }

    header("Location: /hotelreservationservice/Hotel/show/$hotelId");
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

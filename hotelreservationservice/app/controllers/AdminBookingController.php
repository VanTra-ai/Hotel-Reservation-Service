<?php
// app/controllers/AdminBookingController.php

require_once 'app/controllers/BaseAdminController.php'; // Sử dụng BaseAdminController
require_once 'app/models/BookingModel.php';

class AdminBookingController extends BaseAdminController // Kế thừa BaseAdminController
{
    private $bookingModel;

    public function __construct()
    {
        parent::__construct(); // Tự động kiểm tra quyền admin và kết nối DB
        $this->bookingModel = new BookingModel($this->db);
    }

    /**
     * Hiển thị trang danh sách booking
     */
    public function index()
    {
        // <<< THÊM: Lấy từ khóa tìm kiếm từ URL >>>
        $searchTerm = trim($_GET['search'] ?? '');

        // 1. Cấu hình Phân trang
        $limit = 10; // 10 đặt phòng mỗi trang
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;

        // 2. Lấy dữ liệu
        $total_bookings = $this->bookingModel->getBookingCount($searchTerm); // <<< TRUYỀN $searchTerm
        $data['bookings'] = $this->bookingModel->getAllBookings($limit, $offset, $searchTerm); // <<< TRUYỀN $searchTerm

        // 3. Tính toán thông tin phân trang
        $total_pages = (int)ceil($total_bookings / $limit);

        $data['searchTerm'] = $searchTerm; // <<< TRUYỀN $searchTerm SANG VIEW

        $data['pagination'] = [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_bookings,
            'base_url' => BASE_URL . '/admin/booking/index'
        ];

        include 'app/views/admin/bookings/list.php';
    }

    /**
     * Cập nhật trạng thái từ form
     * Hàm này giờ sẽ nhận ID từ URL
     */
    public function updateStatus($id)
    {
        parent::__construct(); // Đảm bảo là Admin

        $bookingId = (int)$id;
        $newStatus = $_POST['status'] ?? ''; // Lấy trạng thái từ form POST

        // Kiểm tra xem trạng thái mới có hợp lệ không
        $allowedStatus = [
            BOOKING_STATUS_PENDING,
            BOOKING_STATUS_CONFIRMED,
            BOOKING_STATUS_CHECKED_IN,
            BOOKING_STATUS_CHECKED_OUT,
            BOOKING_STATUS_CANCELLED
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($newStatus, $allowedStatus)) {

            // <<< SỬA LỖI: Gọi hàm adminUpdateBookingStatus mới (không cần accountId) >>>
            $success = $this->bookingModel->adminUpdateBookingStatus($bookingId, $newStatus);

            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật trạng thái booking #' . $bookingId . ' thành công!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Không thể cập nhật trạng thái.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Yêu cầu không hợp lệ hoặc trạng thái không được phép.'];
        }

        header('Location: ' . BASE_URL . '/admin/booking'); // Quay lại trang danh sách booking
        exit;
    }

    /**
     * Hàm này sẽ xử lý link từ nút "Hủy"
     */
    public function cancel($id)
    {
        $bookingId = (int)$id;
        $newStatus = $_POST['status'] ?? ''; // Lấy trạng thái từ form POST
        if ($bookingId > 0) {
            $this->bookingModel->adminUpdateBookingStatus($bookingId, $newStatus);
        }

        // Chuyển hướng về trang danh sách sau khi hủy
        header('Location: ' . BASE_URL . '/admin/booking?success=cancelled');
        exit;
    }
}

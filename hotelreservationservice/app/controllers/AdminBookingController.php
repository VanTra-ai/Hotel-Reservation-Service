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
    public function updateStatus($id) // Thêm tham số $id
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Nếu không phải POST, chuyển hướng về trang danh sách
            header('Location: ' . BASE_URL . '/admin/booking');
            exit;
        }

        // Lấy ID từ tham số URL, không cần lấy từ $_POST nữa
        $bookingId = (int)$id;
        $status = $_POST['status'] ?? '';

        $allowed = ['pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out'];
        if ($bookingId <= 0 || !in_array($status, ALLOWED_BOOKING_STATUSES, true)) {
            header('Location: ' . BASE_URL . '/admin/booking?error=invalid_data');
            exit;
        }
        $result = $this->bookingModel->updateBookingStatus($bookingId, $status);

        // Chuyển hướng về trang danh sách sau khi cập nhật
        header('Location: ' . BASE_URL . '/admin/booking?success=updated');
        exit;
    }

    /**
     * Hàm này sẽ xử lý link từ nút "Hủy"
     */
    public function cancel($id)
    {
        $bookingId = (int)$id;
        if ($bookingId > 0) {
            $this->bookingModel->updateBookingStatus($bookingId, BOOKING_STATUS_CANCELLED);
        }

        // Chuyển hướng về trang danh sách sau khi hủy
        header('Location: ' . BASE_URL . '/admin/booking?success=cancelled');
        exit;
    }
}

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
        $bookings = $this->bookingModel->getAllBookingsWithInfo();
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
        if ($bookingId <= 0 || !in_array($status, $allowed, true)) {
            // Nếu dữ liệu không hợp lệ, chuyển hướng về với thông báo lỗi
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
            $this->bookingModel->updateBookingStatus($bookingId, 'cancelled');
        }

        // Chuyển hướng về trang danh sách sau khi hủy
        header('Location: ' . BASE_URL . '/admin/booking?success=cancelled');
        exit;
    }
}

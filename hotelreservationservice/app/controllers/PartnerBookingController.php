<?php
// app/controllers/PartnerBookingController.php

require_once 'app/controllers/BasePartnerController.php'; // Kế thừa từ BasePartnerController
require_once 'app/models/BookingModel.php';

class PartnerBookingController extends BasePartnerController
{
    private $bookingModel;

    public function __construct()
    {
        parent::__construct(); // Tự động kiểm tra quyền Partner và kết nối DB
        $this->bookingModel = new BookingModel($this->db);
    }

    /**
     * Hiển thị danh sách booking CỦA RIÊNG PARTNER
     */
    public function index()
    {
        $partnerId = SessionHelper::getAccountId();
        $data['bookings'] = $this->bookingModel->getAllBookingsWithInfo($partnerId);

        // Tái sử dụng view của admin, nhưng chúng ta sẽ tạo bản sao cho partner
        include 'app/views/partner/bookings/list.php';
    }

    /**
     * Cập nhật trạng thái (giống hệt admin, chỉ khác URL redirect)
     */
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/partner/booking');
            exit;
        }

        $bookingId = (int)$id;
        $status = $_POST['status'] ?? '';

        // Thêm kiểm tra: Partner có thực sự sở hữu booking này không? (Nâng cao, tùy chọn)

        $allowed = ['pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out'];
        if ($bookingId <= 0 || !in_array($status, ALLOWED_BOOKING_STATUSES, true)) {
            header('Location: ' . BASE_URL . '/partner/booking?error=invalid_data');
            exit;
        }

        $this->bookingModel->updateBookingStatus($bookingId, $status);
        header('Location: ' . BASE_URL . '/partner/booking?success=updated');
        exit;
    }

    /**
     * Hủy booking (giống hệt admin, chỉ khác URL redirect)
     */
    public function cancel($id)
    {
        $bookingId = (int)$id;
        if ($bookingId > 0) {
            $this->bookingModel->updateBookingStatus($bookingId, BOOKING_STATUS_CANCELLED);
        }

        header('Location: ' . BASE_URL . '/partner/booking?success=cancelled');
        exit;
    }
}

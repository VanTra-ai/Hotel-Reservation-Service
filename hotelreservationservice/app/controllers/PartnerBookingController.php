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
        $searchTerm = trim($_GET['search'] ?? '');

        // 1. Cấu hình Phân trang
        $limit = 10;
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;

        // 2. Lấy dữ liệu
        $total_bookings = $this->bookingModel->getPartnerBookingCount($partnerId, $searchTerm);
        $bookings_raw = $this->bookingModel->getAllPartnerBookings($partnerId, $limit, $offset, $searchTerm);

        // 3. Ánh xạ dữ liệu để View sử dụng tên thuộc tính cũ
        $data['bookings'] = array_map(function ($b) {
            // Khắc phục lỗi: View đang gọi $b->username, nhưng Model trả về $b->customer_name
            $b->username = $b->customer_name;
            return $b;
        }, $bookings_raw);

        // 4. Tính toán thông tin phân trang
        $total_pages = (int)ceil($total_bookings / $limit);

        $data['searchTerm'] = $searchTerm;

        $data['pagination'] = [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_bookings,
            'base_url' => BASE_URL . '/partner/booking/index'
        ];

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

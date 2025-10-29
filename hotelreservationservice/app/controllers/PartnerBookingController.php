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
        // Đảm bảo đã đăng nhập và là Partner
        parent::__construct();

        $bookingId = (int)$id;
        $partnerId = SessionHelper::getAccountId(); // Lấy ID của Partner đang đăng nhập
        $newStatus = $_POST['status'] ?? '';

        // Kiểm tra xem trạng thái mới có hợp lệ không
        $allowedStatus = [
            BOOKING_STATUS_PENDING,
            BOOKING_STATUS_CONFIRMED,
            BOOKING_STATUS_CHECKED_IN,
            BOOKING_STATUS_CHECKED_OUT,
            BOOKING_STATUS_CANCELLED
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($newStatus, $allowedStatus)) {

            $success = $this->bookingModel->updateBookingStatusByPartner($bookingId, $partnerId, $newStatus);

            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật trạng thái booking #' . $bookingId . ' thành công!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Không thể cập nhật trạng thái.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Yêu cầu không hợp lệ hoặc trạng thái không được phép.'];
        }

        // Quay lại trang danh sách booking của partner
        header('Location: ' . BASE_URL . '/partner/booking');
        exit;
    }

    /**
     * Hủy booking (giống hệt admin, chỉ khác URL redirect)
     */
    /**
     * Hủy booking (dành cho Partner)
     */
    public function cancel($id)
    {
        // 1. Xác thực Partner và lấy $partnerId
        parent::__construct();
        $partnerId = SessionHelper::getAccountId();

        $bookingId = (int)$id;

        if ($bookingId > 0) {

            // 2. Gọi hàm Model đúng (kiểm tra owner_id)
            $success = $this->bookingModel->updateBookingStatusByPartner(
                $bookingId,
                $partnerId,
                BOOKING_STATUS_CANCELLED // Truyền trạng thái Hủy
            );

            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã hủy booking #' . $bookingId . ' thành công.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Không thể hủy booking. Booking không tồn tại hoặc bạn không có quyền.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: ID booking không hợp lệ.'];
        }

        header('Location: ' . BASE_URL . '/partner/booking');
        exit;
    }
}

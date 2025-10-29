<?php
// app/controllers/BookingController.php
require_once 'app/models/BookingModel.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/config/database.php';
require_once 'app/config/constants.php';

class BookingController
{
    private $bookingModel;
    private $roomModel;
    private $accountModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->bookingModel = new BookingModel($this->db);
        $this->roomModel = new RoomModel($this->db);
        $this->accountModel = new AccountModel($this->db);
        SessionHelper::startSession();
    }

    private function getAccountId()
    {
        $accountId = SessionHelper::getAccountId();
        if (!empty($accountId)) return $accountId;

        $username = SessionHelper::getUsername();
        if (!empty($username)) {
            $account = $this->accountModel->getAccountByUsername($username);
            if ($account) {
                $_SESSION['account_id'] = $account->id;
                return $account->id;
            }
        }
        return null;
    }

    public function bookRoom()
    {
        SessionHelper::requireLogin();
        $accountId = $this->getAccountId();
        if (!$accountId) {
            header('Location: ' . BASE_URL . '/account/login');
            exit;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $roomId = $_POST['room_id'] ?? $_GET['room_id'] ?? '';
        $checkInDate = $_POST['check_in_date'] ?? $_GET['check_in'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? $_GET['check_out'] ?? '';

        $data = [];
        $data['room'] = $this->roomModel->getRoomById($roomId);
        if (!$data['room']) {
            die("Không tìm thấy phòng.");
        }

        $nights = 0;
        $totalPrice = 0;
        if (!empty($checkInDate) && !empty($checkOutDate) && strtotime($checkOutDate) > strtotime($checkInDate)) {
            $nights = max(1, (strtotime($checkOutDate) - strtotime($checkInDate)) / (60 * 60 * 24));
            $totalPrice = $nights * (float)$data['room']->price;
        }

        $data['nights'] = $nights;
        $data['total_price'] = $totalPrice;
        $data['check_in'] = $checkInDate;
        $data['check_out'] = $checkOutDate;

        if ($method === 'POST') {
            // Lấy lại giá trị từ POST (vì $checkInDate, $checkOutDate ở trên có thể là từ GET)
            $checkInDate = $_POST['check_in_date'] ?? '';
            $checkOutDate = $_POST['check_out_date'] ?? '';
            $guests = max(1, (int)($_POST['guests'] ?? 1));
            $groupType = $_POST['group_type'] ?? null;

            // (Validation ngày, phòng trống, và groupType giữ nguyên như code của bạn)
            $today = date('Y-m-d');
            if (strtotime($checkInDate) < strtotime($today)) {
                $data['error'] = "Ngày nhận phòng không được là ngày trong quá khứ.";
                include 'app/views/booking/book.php';
                return;
            }

            if (strtotime($checkOutDate) <= strtotime($checkInDate)) {
                $data['error'] = "Ngày trả phòng phải sau ngày nhận phòng.";
                $data['check_in'] = $checkInDate;
                $data['check_out'] = $checkOutDate;
                include 'app/views/booking/book.php';
                return;
            }

            if (!$this->bookingModel->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) {
                $data['error'] = "Phòng đã được đặt trong khoảng thời gian này. Vui lòng chọn ngày khác.";
                include 'app/views/booking/book.php';
                return;
            }

            $allowedGroupTypes = ['Cặp đôi', 'Phòng gia đình', 'Nhóm', 'Khách lẻ'];
            if (empty($groupType) || !in_array($groupType, $allowedGroupTypes, true)) {
                $data['error'] = "Vui lòng chọn loại nhóm khách hợp lệ.";
                include 'app/views/booking/book.php';
                return;
            }


            $nights = max(1, (strtotime($checkOutDate) - strtotime($checkInDate)) / (60 * 60 * 24));
            $totalPrice = $nights * (float)$data['room']->price;

            // Truyền $groupType (VD: "Phòng gia đình") vào hàm createBooking
            $bookingId = $this->bookingModel->createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice, $groupType);

            if ($bookingId) {
                // THÀNH CÔNG: Chuyển đến trang thanh toán
                header('Location: ' . BASE_URL . '/booking/payment?id=' . $bookingId);
                exit;
            }

            $data['error'] = "Có lỗi xảy ra khi lưu đặt phòng.";
            include 'app/views/booking/book.php';
            return;
        }

        $data['error'] = null;
        include 'app/views/booking/book.php';
    }

    public function confirmation()
    {
        include 'app/views/booking/confirmation.php';
    }

    public function history()
    {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/account/login');
            exit;
        }

        $accountId = $this->getAccountId();
        $data = [
            'bookings' => $this->bookingModel->getBookingsByAccountId($accountId),
            'STATUS_CHECKED_OUT' => BOOKING_STATUS_CHECKED_OUT
        ];
        include 'app/views/booking/history.php';
    }

    public function cancel()
    {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/account/login');
            exit;
        }

        $accountId = $this->getAccountId();
        $bookingId = $_POST['booking_id'] ?? 0;

        if ($bookingId && $accountId) {
            $this->bookingModel->cancelBooking($bookingId, $accountId);
        }

        header('Location: ' . BASE_URL . '/booking/history');
        exit;
    }
    /**
     * Hiển thị trang thanh toán
     */
    public function payment()
    {
        SessionHelper::requireLogin();
        $accountId = $this->getAccountId();
        $bookingId = (int)($_GET['id'] ?? 0);

        $booking = $this->bookingModel->getBookingById($bookingId, $accountId);

        if (!$booking || $booking->status !== BOOKING_STATUS_PENDING) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đơn hàng không hợp lệ hoặc đã được thanh toán.']; // <<< Thêm =>
            header('Location: ' . BASE_URL . '/booking/history');
            exit;
        }

        $data['booking'] = $booking;
        include 'app/views/booking/payment.php'; // Tạo file view này
    }

    /**
     * Xử lý thanh toán (Mô phỏng)
     */
    public function processPayment()
    {
        SessionHelper::requireLogin();
        $accountId = $this->getAccountId();
        $bookingId = (int)($_POST['booking_id'] ?? 0);

        // (Code thực tế sẽ gọi API NAPAS ở đây)

        // Mô phỏng thành công:
        $success = $this->bookingModel->updateBookingStatus($bookingId, $accountId, BOOKING_STATUS_CONFIRMED);

        if ($success) {
            header('Location: ' . BASE_URL . '/booking/confirmation');
            exit;
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Xử lý thanh toán thất bại.'];
            header('Location: ' . BASE_URL . '/booking/payment?id=' . $bookingId);
            exit;
        }
    }
}

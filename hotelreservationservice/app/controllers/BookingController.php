<?php
require_once 'app/models/BookingModel.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/SessionHelper.php';

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

    /**
     * Lấy accountId từ session hoặc từ username
     */
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

    /**
     * Xử lý đặt phòng
     */
    public function bookRoom()
    {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /hotelreservationservice/account/login');
            exit;
        }

        $accountId = $this->getAccountId();
        if (empty($accountId)) {
            header('Location: /hotelreservationservice/account/login');
            exit;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_POST['action'] ?? '';

        // --------------------------
        // POST: preview booking
        // --------------------------
        if ($method === 'POST' && $action === 'preview') {
            $roomId = $_POST['room_id'] ?? '';
            $checkInDate = $_POST['check_in_date'] ?? '';
            $checkOutDate = $_POST['check_out_date'] ?? '';
            $guests = max(1, (int)($_POST['guests'] ?? 1));

            $room = $this->roomModel->getRoomById($roomId);
            if (!$room) {
                $error = "Không tìm thấy phòng.";
                include 'app/views/booking/book.php';
                return;
            }

            $nights = max(1, (strtotime($checkOutDate) - strtotime($checkInDate)) / (60*60*24));
            $totalPrice = $nights * (float)$room->price;

            if (!$this->bookingModel->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) {
                $error = "Phòng đã được đặt trong khoảng thời gian này. Vui lòng chọn ngày khác.";
                include 'app/views/booking/book.php';
                return;
            }

            include 'app/views/booking/confirmation.php';
            return;
        }

        // --------------------------
        // POST: confirm booking
        // --------------------------
        if ($method === 'POST' && $action === 'confirm') {
            $roomId = $_POST['room_id'] ?? '';
            $checkInDate = $_POST['check_in_date'] ?? '';
            $checkOutDate = $_POST['check_out_date'] ?? '';
            $totalPrice = (float)($_POST['total_price'] ?? 0);

            $room = $this->roomModel->getRoomById($roomId);
            if (!$room) {
                $error = "Không tìm thấy phòng.";
                include 'app/views/booking/book.php';
                return;
            }

            if (!$this->bookingModel->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) {
                $error = "Phòng đã được đặt trong khoảng thời gian này. Vui lòng chọn ngày khác.";
                include 'app/views/booking/book.php';
                return;
            }

            if ($this->bookingModel->createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice)) {
                header('Location: /hotelreservationservice/booking/confirmation?success=1');
                exit;
            }

            $error = "Có lỗi xảy ra khi lưu đặt phòng.";
            include 'app/views/booking/book.php';
            return;
        }

        // --------------------------
        // GET: hiển thị form đặt phòng
        // --------------------------
        $roomId = $_GET['room_id'] ?? '';
        $room = $roomId ? $this->roomModel->getRoomById($roomId) : null;
        include 'app/views/booking/book.php';
    }

    /**
     * Trang xác nhận booking
     */
    public function confirmation()
    {
        include 'app/views/booking/confirmation.php';
    }

    public function history()
    {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /hotelreservationservice/account/login');
            exit;
        }

        $accountId = $this->getAccountId();
        $bookings = $this->bookingModel->getBookingsByAccountId($accountId);
        include 'app/views/booking/history.php';
    }

    public function cancel()
    {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /hotelreservationservice/account/login');
            exit;
        }

        $accountId = $this->getAccountId();
        $bookingId = $_POST['booking_id'] ?? 0;

        if ($bookingId && $accountId) {
            $this->bookingModel->cancelBooking($bookingId, $accountId);
        }

        header('Location: /hotelreservationservice/booking/history');
        exit;
    }
}

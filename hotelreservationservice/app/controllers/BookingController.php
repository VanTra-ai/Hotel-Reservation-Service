<?php
// app/controllers/BookingController.php
require_once 'app/models/BookingModel.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/config/database.php';

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

        $data = [];
        $data['room'] = $this->roomModel->getRoomById($roomId);
        if (!$data['room']) {
            die("Không tìm thấy phòng.");
        }

        if ($method === 'POST') {
            $roomId = $_POST['room_id'] ?? '';
            $checkInDate = $_POST['check_in_date'] ?? '';
            $checkOutDate = $_POST['check_out_date'] ?? '';
            $guests = max(1, (int)($_POST['guests'] ?? 1));
            $groupType = $_POST['group_type'] ?? null; // Lấy giá trị (VD: "Phòng gia đình")

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

            // <<< SỬA ĐỔI: Đảm bảo mảng này KHỚP VỚI VIEW book.php >>>
            $allowedGroupTypes = ['Cặp đôi', 'Phòng gia đình', 'Nhóm', 'Khách lẻ'];

            if (empty($groupType) || !in_array($groupType, $allowedGroupTypes, true)) {
                $data['error'] = "Vui lòng chọn loại nhóm khách hợp lệ."; // Đây là lỗi bạn thấy

                // Giữ lại các giá trị đã nhập để hiển thị lại form
                $data['check_in'] = $checkInDate;
                $data['check_out'] = $checkOutDate;

                include 'app/views/booking/book.php'; // Trả về form với lỗi
                return;
            }
            // <<< KẾT THÚC SỬA ĐỔI >>>


            $nights = max(1, (strtotime($checkOutDate) - strtotime($checkInDate)) / (60 * 60 * 24));
            $totalPrice = $nights * (float)$data['room']->price;

            // Truyền $groupType (VD: "Phòng gia đình") vào hàm createBooking
            if ($this->bookingModel->createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice, $groupType)) {
                header('Location: ' . BASE_URL . '/booking/confirmation');
                exit;
            }

            $data['error'] = "Có lỗi xảy ra khi lưu đặt phòng.";
            $data['check_in'] = $checkInDate;
            $data['check_out'] = $checkOutDate;
            include 'app/views/booking/book.php';
            return;
        }

        $data['error'] = null;
        $data['check_in'] = $_GET['check_in'] ?? '';
        $data['check_out'] = $_GET['check_out'] ?? '';

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
}

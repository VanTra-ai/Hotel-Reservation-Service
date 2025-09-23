<?php
require_once 'app/models/BookingModel.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/SessionHelper.php';

class BookingController
{
    private $bookingModel;
    private $roomModel;
    private $db;
    private $accountModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->bookingModel = new BookingModel($this->db);
        $this->roomModel = new RoomModel($this->db);
        $this->accountModel = new AccountModel($this->db);
    }

    public function bookRoom()
    {
        SessionHelper::startSession();

        if (!SessionHelper::isLoggedIn()) {
            // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
            header('Location: /hotelreservationservice/account/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'preview') {
            $accountId = SessionHelper::getAccountId();
            if (empty($accountId)) {
                // Fallback: cố gắng lấy lại account_id từ username đang đăng nhập
                $username = SessionHelper::getUsername();
                if (!empty($username)) {
                    $account = $this->accountModel->getAccountByUsername($username);
                    if ($account) {
                        $_SESSION['account_id'] = $account->id;
                        $accountId = $account->id;
                    }
                }
            }
            $roomId = $_POST['room_id'] ?? '';
            $checkInDate = $_POST['check_in_date'] ?? '';
            $checkOutDate = $_POST['check_out_date'] ?? '';
            $guests = max(1, (int)($_POST['guests'] ?? 1));

            // Lấy thông tin phòng theo ID để lấy giá
            $room = $this->roomModel->getRoomById($roomId);
            if (!$room || empty($accountId)) {
                $error = "Không tìm thấy phòng.";
                include_once 'app/views/booking/book.php';
                return;
            }

            // Tính tổng tiền theo số đêm
            $nights = (strtotime($checkOutDate) - strtotime($checkInDate)) / (60 * 60 * 24);
            if ($nights <= 0) { $nights = 1; }
            $totalPrice = $nights * (float)$room->price;

            // Nếu phòng bận, báo lỗi, quay lại form đặt phòng
            if (!$this->bookingModel->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) {
                $error = "Phòng đã được đặt trong khoảng thời gian này. Vui lòng chọn ngày khác.";
                include_once 'app/views/booking/book.php';
                return;
            }

            // Hiển thị trang xác nhận
            include_once 'app/views/booking/confirmation.php';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
            $accountId = SessionHelper::getAccountId();
            if (empty($accountId)) {
                $username = SessionHelper::getUsername();
                if (!empty($username)) {
                    $account = $this->accountModel->getAccountByUsername($username);
                    if ($account) {
                        $_SESSION['account_id'] = $account->id;
                        $accountId = $account->id;
                    }
                }
            }
            $roomId = $_POST['room_id'] ?? '';
            $checkInDate = $_POST['check_in_date'] ?? '';
            $checkOutDate = $_POST['check_out_date'] ?? '';
            $totalPrice = (float)($_POST['total_price'] ?? 0);

            // Debug: kiểm tra giá trị
            if (empty($roomId)) {
                $error = "Thiếu thông tin phòng (room_id: '$roomId').";
                include_once 'app/views/booking/book.php';
                return;
            }

            if (empty($accountId)) {
                $error = "Chưa đăng nhập (account_id: '$accountId').";
                include_once 'app/views/booking/book.php';
                return;
            }

            // Lấy lại thông tin phòng để kiểm tra
            $room = $this->roomModel->getRoomById($roomId);
            if (!$room) {
                $error = "Không tìm thấy phòng với ID: '$roomId'.";
                include_once 'app/views/booking/book.php';
                return;
            }

            // Kiểm tra lại trước khi lưu, tránh race condition
            if (!$this->bookingModel->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) {
                $error = "Phòng đã được đặt trong khoảng thời gian này. Vui lòng chọn ngày khác.";
                include_once 'app/views/booking/book.php';
                return;
            }

            if ($this->bookingModel->createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice)) {
                header('Location: /hotelreservationservice/booking/confirmation?success=1');
                exit;
            }

            $error = "Có lỗi xảy ra khi lưu đặt phòng.";
            include_once 'app/views/booking/book.php';
        } else {
            // Hiển thị form đặt phòng, nhận room_id từ query
            $roomId = $_GET['room_id'] ?? '';
            $room = null;
            if ($roomId) {
                $room = $this->roomModel->getRoomById($roomId);
            }
            include_once 'app/views/booking/book.php';
        }
    }

    public function confirmation()
    {
        include_once 'app/views/booking/confirmation.php';
    }
}

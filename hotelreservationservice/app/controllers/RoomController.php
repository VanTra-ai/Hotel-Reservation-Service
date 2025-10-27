<?php
// app/controllers/RoomController.php

require_once('app/config/database.php');
require_once('app/models/RoomModel.php');
require_once('app/helpers/SessionHelper.php');

class RoomController
{
    private $roomModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->roomModel = new RoomModel($this->db);
        SessionHelper::startSession(); // Vẫn giữ để đảm bảo session luôn có
    }

    /**
     * Hiển thị danh sách phòng cho người dùng.
     * Người dùng có thể lọc phòng theo khách sạn hoặc theo ngày trống.
     */
    public function list()
    {
        $hotelId = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
        $onlyAvailable = !empty($_GET['available']);
        $checkInDate = $_GET['check_in'] ?? null;
        $checkOutDate = $_GET['check_out'] ?? null;

        if ($onlyAvailable && $checkInDate && $checkOutDate) {
            $rooms = $this->roomModel->getRooms($hotelId, true, $checkInDate, $checkOutDate);
        } else {
            $rooms = $this->roomModel->getRooms($hotelId, false);
        }

        include 'app/views/room/list.php';
    }

    /**
     * Hiển thị chi tiết một phòng cho người dùng.
     */
    public function show($id)
    {
        $room = $this->roomModel->getRoomById($id);
        if (!$room) {
            http_response_code(404);
            echo "Không tìm thấy phòng";
            return;
        }
        include 'app/views/room/show.php';
    }
    /**
     * API nội bộ để lấy các phòng trống qua AJAX
     */
    public function getAvailableRoomsAjax()
    {
        header('Content-Type: application/json');

        // Lấy dữ liệu ngày tháng từ yêu cầu POST (gửi bằng JavaScript)
        $hotelId = $_POST['hotel_id'] ?? 0;
        $checkIn = $_POST['check_in'] ?? null;
        $checkOut = $_POST['check_out'] ?? null;

        if (!$hotelId || !$checkIn || !$checkOut) {
            echo json_encode(['error' => 'Thông tin không hợp lệ.']);
            exit;
        }

        $availableRooms = $this->roomModel->getAvailableRooms($hotelId, $checkIn, $checkOut);

        // Trả về dữ liệu dưới dạng JSON
        echo json_encode($availableRooms);
        exit;
    }
}

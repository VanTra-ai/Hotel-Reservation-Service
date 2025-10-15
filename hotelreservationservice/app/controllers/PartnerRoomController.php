<?php
// app/controllers/PartnerRoomController.php

require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/HotelModel.php';

class PartnerRoomController extends BasePartnerController
{
    private $roomModel;
    private $hotelModel;
    private $partnerHotel;

    public function __construct()
    {
        parent::__construct(); // Tự động kiểm tra quyền Partner
        $this->roomModel = new RoomModel($this->db);
        $this->hotelModel = new HotelModel($this->db);

        // Lấy thông tin khách sạn của partner một lần và tái sử dụng
        $partnerId = SessionHelper::getAccountId();
        $this->partnerHotel = $this->hotelModel->getHotelByOwnerId($partnerId);

        // Nếu partner không sở hữu khách sạn nào, chặn truy cập ngay lập tức
        if (!$this->partnerHotel) {
            // Có thể chuyển hướng đến trang thông báo
            die("Lỗi: Bạn chưa được gán quyền quản lý cho bất kỳ khách sạn nào.");
        }
    }

    /**
     * Hiển thị danh sách phòng của khách sạn mà Partner sở hữu
     */
    public function index()
    {
        $data['rooms'] = $this->roomModel->getRoomsByHotelId($this->partnerHotel->id);
        $data['hotel_name'] = $this->partnerHotel->name;
        include 'app/views/partner/rooms/list.php';
    }

    /**
     * Hiển thị form thêm phòng mới
     */
    public function add()
    {
        $data['hotel'] = $this->partnerHotel;
        include 'app/views/partner/rooms/add.php';
    }

    /**
     * Xử lý lưu phòng mới
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $this->partnerHotel->id != $_POST['hotel_id']) {
            die("Lỗi bảo mật hoặc yêu cầu không hợp lệ.");
        }

        $hotel_id = (int)$_POST['hotel_id'];
        $room_number = $_POST['room_number'] ?? '';
        $room_type = $_POST['room_type'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';
        $errors = [];
        $image = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $image = $this->uploadImage($_FILES['image']);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $this->roomModel->addRoom($hotel_id, $room_number, $room_type, $capacity, $price, $description, $image);

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm phòng mới thành công!'];
        header('Location: ' . BASE_URL . '/partner/room');
        exit;
    }

    /**
     * Hiển thị form sửa phòng
     */
    public function edit($roomId)
    {
        $room = $this->roomModel->getRoomById((int)$roomId);

        // Bảo mật: Kiểm tra xem phòng này có thuộc khách sạn của partner không
        if (!$room || $room->hotel_id != $this->partnerHotel->id) {
            die("Lỗi bảo mật: Bạn không có quyền sửa phòng này.");
        }

        $data['room'] = $room;
        $data['hotel'] = $this->partnerHotel; // Gửi thông tin khách sạn sang view
        include 'app/views/partner/rooms/edit.php';
    }

    /**
     * Xử lý cập nhật phòng
     */
    public function update($roomId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $room = $this->roomModel->getRoomById((int)$roomId);
        if (!$room || $room->hotel_id != $this->partnerHotel->id) {
            die("Lỗi bảo mật: Bạn không có quyền cập nhật phòng này.");
        }

        $hotel_id = (int)$_POST['hotel_id'];
        $room_number = $_POST['room_number'] ?? '';
        $room_type = $_POST['room_type'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';
        $imagePath = $room->image ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $newImage = $this->uploadImage($_FILES['image']);
                if ($newImage) {
                    if (!empty($imagePath) && file_exists($imagePath)) @unlink($imagePath);
                    $imagePath = $newImage;
                }
            } catch (Exception $e) {
                // Xử lý lỗi upload
            }
        }

        $this->roomModel->updateRoom($roomId, $hotel_id, $room_number, $room_type, $capacity, $price, $description, $imagePath);

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật phòng thành công!'];
        header('Location: ' . BASE_URL . '/partner/room');
        exit;
    }

    /**
     * Xử lý xóa phòng
     */
    public function delete($roomId)
    {
        $room = $this->roomModel->getRoomById((int)$roomId);
        if (!$room || $room->hotel_id != $this->partnerHotel->id) {
            die("Lỗi bảo mật: Bạn không có quyền xóa phòng này.");
        }

        if ($room && !empty($room->image) && file_exists($room->image)) {
            @unlink($room->image);
        }

        $this->roomModel->deleteRoom((int)$roomId);

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Xóa phòng thành công!'];
        header('Location: ' . BASE_URL . '/partner/room');
        exit;
    }

    /**
     * Hàm private hỗ trợ upload ảnh (tái sử dụng từ Admin)
     */
    private function uploadImage($file)
    {
        $target_dir = "public/images/room/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed, true)) throw new Exception("Chỉ cho phép JPG/PNG/GIF.");
        if ($file['size'] > 10 * 1024 * 1024) throw new Exception("Kích thước ảnh vượt quá 10MB.");

        $filename = uniqid('room_', true) . '.' . $ext;
        $target = $target_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new Exception("Lỗi khi tải ảnh lên.");
        }
        return $target;
    }
}

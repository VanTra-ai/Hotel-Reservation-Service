<?php
// app/controllers/AdminRoomController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/HotelModel.php';
class AdminRoomController extends BaseAdminController
{
    private $roomModel;
    private $hotelModel;

    public function __construct()
    {
        // Gọi hàm __construct của cha (BaseAdminController) để kiểm tra quyền và kết nối DB
        parent::__construct();
        $this->roomModel = new RoomModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
    }
    /**
     * Hiển thị danh sách phòng cho Admin
     */
    public function index()
    {
        $rooms = $this->roomModel->getRooms();
        include 'app/views/admin/rooms/list.php';
    }

    /**
     * Hiển thị form thêm phòng
     */
    public function add()
    {
        $hotels = $this->hotelModel->getHotels();
        $errors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);
        include 'app/views/admin/rooms/add.php';
    }

    /**
     * Lưu dữ liệu từ form thêm mới
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/room');
            exit;
        }

        $hotel_id = $_POST['hotel_id'] ?? '';
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

        if (empty($errors)) {
            $result = $this->roomModel->addRoom($hotel_id, $room_number, $room_type, $capacity, $price, $description, $image);
            if ($result === true) {
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/room');
                exit;
            } elseif (is_array($result)) {
                $errors = array_merge($errors, $result);
            } else {
                $errors[] = "Có lỗi khi thêm phòng.";
            }
        }

        $_SESSION['form_errors'] = $errors;
        header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/room/add');
        exit;
    }

    /**
     * Hiển thị form sửa phòng
     */
    public function edit($id)
    {
        $room = $this->roomModel->getRoomById($id);
        if (!$room) {
            http_response_code(404);
            echo "Không tìm thấy phòng";
            return;
        }
        $hotels = $this->hotelModel->getHotels();
        include 'app/views/admin/rooms/edit.php';
    }

    /**
     * Cập nhật dữ liệu từ form sửa
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/room');
            exit;
        }

        $hotel_id = $_POST['hotel_id'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $room_type = $_POST['room_type'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';
        $errors = [];
        $room = $this->roomModel->getRoomById($id);

        if (!$room) {
            $errors[] = "Phòng không tồn tại.";
        }

        $imagePath = $room->image ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $newImage = $this->uploadImage($_FILES['image']);
                if ($newImage) {
                    if (!empty($imagePath) && file_exists($imagePath)) @unlink($imagePath);
                    $imagePath = $newImage;
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            $res = $this->roomModel->updateRoom($id, $hotel_id, $room_number, $room_type, $capacity, $price, $description, $imagePath);
            if ($res === true) {
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/room');
                exit;
            }
            $errors[] = "Cập nhật thất bại.";
        }

        $hotels = $this->hotelModel->getHotels();
        include 'app/views/admin/rooms/edit.php';
    }

    /**
     * Xóa phòng (hành động từ form/link)
     */
    public function delete($id)
    {
        $room = $this->roomModel->getRoomById($id);
        if ($room && !empty($room->image) && file_exists($room->image)) {
            @unlink($room->image);
        }

        $this->roomModel->deleteRoom($id);
        header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/room');
        exit;
    }

    /**
     * Hàm private hỗ trợ upload ảnh
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
    /**
     * Xử lý yêu cầu cập nhật một trường qua AJAX
     */
    public function updateFieldAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';

        $allowedFields = ['room_number', 'room_type', 'capacity', 'price', 'description'];

        if ($id <= 0 || !in_array($field, $allowedFields, true)) {
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }

        $result = $this->roomModel->updateRoomField($id, $field, $value);

        echo json_encode($result ? ['success' => true] : ['error' => 'Update failed']);
        exit;
    }

    /**
     * Xử lý yêu cầu xóa một phòng qua AJAX
     */
    public function deleteAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid room id']);
            exit;
        }

        $result = $this->roomModel->deleteRoom($id);
        echo json_encode($result ? ['success' => true] : ['error' => 'Delete failed']);
        exit;
    }
}

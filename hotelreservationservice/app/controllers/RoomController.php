<?php
// app/controllers/RoomController.php

require_once('app/config/database.php');
require_once('app/models/RoomModel.php');
require_once('app/models/HotelModel.php');
require_once('app/helpers/SessionHelper.php');

class RoomController
{
    private $roomModel;
    private $hotelModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->roomModel = new RoomModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
    }

    public function list()
    {
        $rooms = $this->roomModel->getRooms();
        include 'app/views/room/list.php';
    }

    public function add()
    {
        $hotels = $this->hotelModel->getHotels();
        include 'app/views/room/add.php';
    }

    public function save()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $hotel_id = $_POST['hotel_id'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $room_type = $_POST['room_type'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';

        $errors = [];

        // RÀNG BUỘC: room_number chỉ được số
        if (!preg_match('/^\d+$/', $room_number)) {
            $errors[] = "Số phòng chỉ được chứa ký tự số.";
        }

        if (empty($errors)) {
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = "";
            }

            $result = $this->roomModel->addRoom($hotel_id, $room_number, $room_type, $capacity, $price, $description, $image);
            if (is_array($result)) {
                $errors = $result;
            }
        }

        if (!empty($errors)) {
            $hotels = $this->hotelModel->getHotels();
            include 'app/views/room/add.php';
        } else {
            header('Location: /hotelreservationservice/Room/list');
        }
    }
}


    public function edit($id)
    {
        $room = $this->roomModel->getRoomById($id);
        $hotels = $this->hotelModel->getHotels();
        if ($room) {
            include 'app/views/room/edit.php';
        } else {
            echo "Không tìm thấy phòng";
        }
    }

    public function update($id)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $hotel_id = $_POST['hotel_id'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $room_type = $_POST['room_type'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';

        $errors = [];

        // RÀNG BUỘC: room_number chỉ được số
        if (!preg_match('/^\d+$/', $room_number)) {
            $errors[] = "Số phòng chỉ được chứa ký tự số.";
        }

        if (!empty($errors)) {
            $room = $this->roomModel->getRoomById($id);
            $hotels = $this->hotelModel->getHotels();
            include 'app/views/room/edit.php';
            return;
        }

        // ... phần xử lý update ảnh giữ nguyên ...
        $oldRoom = $this->roomModel->getRoomById($id);

            $imagePath = $oldRoom->image; // Giữ ảnh cũ mặc định

            // Xử lý upload ảnh mới
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                try {
                    $newImagePath = $this->uploadImage($_FILES['image']);
                    if ($newImagePath) {
                        // Xóa ảnh cũ nếu tồn tại
                        if (!empty($imagePath) && file_exists($imagePath)) {
                            @unlink($imagePath);
                        }
                        $imagePath = $newImagePath;
                    }
                } catch (Exception $e) {
                    echo "Lỗi: " . $e->getMessage();
                    return;
                }
            }

            $result = $this->roomModel->updateRoom($id, $hotel_id, $room_number, $room_type, $capacity, $price, $description, $imagePath);

            if ($result) {
                header('Location: /hotelreservationservice/Room/list');
            } else {
                echo "Đã xảy ra lỗi khi cập nhật phòng.";
            }
        }
    }

    public function delete($id)
    {
        // Lấy thông tin phòng để xóa ảnh
        $room = $this->roomModel->getRoomById($id);
        if ($room && !empty($room->image) && file_exists($room->image)) {
            @unlink($room->image);
        }

        if ($this->roomModel->deleteRoom($id)) {
            header('Location: /hotelreservationservice/Room/list');
        } else {
            echo "Đã xảy ra lỗi khi xóa phòng.";
        }
    }

    public function show($id)
    {
        $room = $this->roomModel->getRoomById($id);
        if ($room) {
            include 'app/views/room/show.php';
        } else {
            echo "Không tìm thấy phòng";
        }
    }

    private function uploadImage($file)
    {
        $target_dir = "public/images/room/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Kiểm tra xem file có phải là hình ảnh không
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            throw new Exception("File không phải là hình ảnh.");
        }
        
        // Kiểm tra kích thước file (10 MB = 10 * 1024 * 1024 bytes)
        if ($file["size"] > 10 * 1024 * 1024) {
            throw new Exception("Hình ảnh có kích thước quá lớn.");
        }
        
        // Chỉ cho phép một số định dạng hình ảnh nhất định
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType !=
            "jpeg" && $imageFileType != "gif"
        ) {
            throw new Exception("Chỉ cho phép các định dạng JPG, JPEG, PNG và GIF.");
        }
        
        // Lưu file
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Có lỗi xảy ra khi tải lên hình ảnh.");
        }
        return $target_file;
    }
}

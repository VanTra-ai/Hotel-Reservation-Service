<?php
// app/controllers/PartnerRoomController.php

require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/helpers/ImageUploader.php';

class PartnerRoomController extends BasePartnerController
{
    private $roomModel;
    private $hotelModel;
    private $partnerHotel;
    private ImageUploader $roomImageUploader;

    public function __construct()
    {
        parent::__construct(); // Tự động kiểm tra quyền Partner
        $this->roomModel = new RoomModel($this->db);
        $this->hotelModel = new HotelModel($this->db);

        $partnerId = SessionHelper::getAccountId();
        $this->partnerHotel = $this->hotelModel->getHotelByOwnerId($partnerId);
        $this->roomImageUploader = new ImageUploader('public/images/room/');

        if (!$this->partnerHotel) {
            // Chuyển hướng nếu không có khách sạn nào
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Bạn chưa được gán quyền quản lý cho khách sạn nào.'];
            header('Location: ' . BASE_URL . '/account/login'); // Hoặc trang thông báo khác
            exit();
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
        // Lấy lỗi từ session (nếu có redirect từ hàm save)
        $data['errors'] = $_SESSION['form_errors'] ?? []; // Gửi $errors vào $data
        unset($_SESSION['form_errors']);
        // Lấy dữ liệu cũ từ session
        $data['old_input'] = $_SESSION['old_input'] ?? []; // Gửi $old_input vào $data
        unset($_SESSION['old_input']);

        include 'app/views/partner/rooms/add.php'; // View cần đọc $data['errors'] và $data['old_input']
    }

    /**
     * Xử lý lưu phòng mới
     */
    public function save()
    {
        // Kiểm tra bảo mật và phương thức
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['hotel_id']) || $this->partnerHotel->id != (int)$_POST['hotel_id']) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi bảo mật hoặc yêu cầu không hợp lệ.'];
            header('Location: ' . BASE_URL . '/partner/dashboard');
            exit();
        }

        // Lưu input để hiển thị lại nếu lỗi
        $_SESSION['old_input'] = $_POST;

        $hotel_id = (int)$_POST['hotel_id'];
        $room_number = $_POST['room_number'] ?? '';
        $room_type = $_POST['room_type'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';
        $errors = [];
        $imagePath = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $this->roomImageUploader->upload($_FILES['image'], 'room_'); // Gán vào imagePath
            } catch (Exception $e) {
                $errors['image'] = "Lỗi upload ảnh: " . $e->getMessage(); // Gán lỗi cụ thể
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $errors['image'] = "Có lỗi xảy ra khi upload file (Mã lỗi: " . $_FILES['image']['error'] . ").";
        }


        // Chỉ gọi model nếu không có lỗi upload
        if (empty($errors)) {
            // Gọi model để thêm phòng
            $result = $this->roomModel->addRoom($hotel_id, $room_number, $room_type, $capacity, $price, $description, $imagePath); // Truyền imagePath

            if ($result === true) {
                unset($_SESSION['old_input']); // Xóa input cũ khi thành công
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm phòng mới thành công!'];
                header('Location: ' . BASE_URL . '/partner/room'); // Redirect về trang list của partner
                exit;
            } elseif (is_array($result)) {
                // Lỗi validation từ model
                $errors = array_merge($errors, $result);
            } else {
                // Lỗi DB không xác định
                $errors['database'] = "Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.";
            }
        }

        // Nếu có lỗi (upload hoặc validation hoặc DB), lưu lỗi và redirect lại form add
        $_SESSION['form_errors'] = $errors;
        header('Location: ' . BASE_URL . '/partner/room/add'); // Redirect về form add của partner
        exit;
    }


    /**
     * Hiển thị form sửa phòng
     */
    public function edit($id) // Thống nhất dùng $id
    {
        $id = (int)$id; // Ép kiểu
        $room = $this->roomModel->getRoomById($id);

        // Bảo mật: Kiểm tra phòng tồn tại VÀ thuộc khách sạn của partner
        if (!$room || $room->hotel_id != $this->partnerHotel->id) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Bạn không có quyền sửa phòng này hoặc phòng không tồn tại.'];
            header('Location: ' . BASE_URL . '/partner/room'); // Redirect về trang list của partner
            exit();
        }

        $data['room'] = $room;
        $data['hotel'] = $this->partnerHotel; // Gửi thông tin khách sạn sang view
        // Lấy lỗi từ session (nếu có redirect từ hàm update)
        $data['errors'] = $_SESSION['form_errors'] ?? []; // Gửi $errors vào $data
        unset($_SESSION['form_errors']);
        // Có thể lấy input cũ từ session nếu cần

        include 'app/views/partner/rooms/edit.php'; // View cần đọc $data['errors']
    }


    /**
     * Xử lý cập nhật phòng
     */
    public function update($id) // Thống nhất dùng $id
    {
        $id = (int)$id; // Ép kiểu ID
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/partner/room/edit/' . $id); // Chuyển hướng về form edit
            exit;
        }

        $room = $this->roomModel->getRoomById($id);
        // Bảo mật: Kiểm tra phòng tồn tại VÀ thuộc khách sạn của partner
        if (!$room || $room->hotel_id != $this->partnerHotel->id) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Bạn không có quyền cập nhật phòng này hoặc phòng không tồn tại.'];
            header('Location: ' . BASE_URL . '/partner/room'); // Redirect về trang list của partner
            exit();
        }

        // Lấy dữ liệu POST hoặc giữ lại dữ liệu cũ nếu không có
        $hotel_id = $room->hotel_id; // Partner không đổi được hotel_id của phòng
        $room_number = $_POST['room_number'] ?? $room->room_number;
        $room_type = $_POST['room_type'] ?? $room->room_type;
        $capacity = $_POST['capacity'] ?? $room->capacity;
        $price = $_POST['price'] ?? $room->price;
        $description = $_POST['description'] ?? $room->description;
        $errors = [];

        $imagePath = $room->image ?? ''; // Giữ ảnh cũ mặc định
        $oldImagePath = $room->image ?? ''; // Lưu lại để xóa

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $newImage = $this->roomImageUploader->upload($_FILES['image'], 'room_');
                if ($newImage) {
                    $this->roomImageUploader->delete($oldImagePath); // Xóa ảnh cũ
                    $imagePath = $newImage; // Cập nhật đường dẫn mới
                }
            } catch (Exception $e) {
                $errors['image'] = "Lỗi upload ảnh: " . $e->getMessage();
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $errors['image'] = "Có lỗi xảy ra khi upload file (Mã lỗi: " . $_FILES['image']['error'] . ").";
        }


        // Chỉ gọi model nếu không có lỗi upload
        if (empty($errors)) {
            $res = $this->roomModel->updateRoom($id, $hotel_id, $room_number, $room_type, $capacity, $price, $description, $imagePath);

            if ($res === true) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật phòng thành công!'];
                header('Location: ' . BASE_URL . '/partner/room'); // Redirect về trang list của partner
                exit;
            } elseif (is_array($res)) {
                // Lỗi validation từ model
                $errors = array_merge($errors, $res);
            } else {
                // Lỗi DB không xác định
                $errors['database'] = "Có lỗi xảy ra khi cập nhật cơ sở dữ liệu.";
            }
        }

        $_SESSION['form_errors'] = $errors;
        header('Location: ' . BASE_URL . '/partner/room/edit/' . $id); // Redirect về form edit của partner
        exit;
    }


    /**
     * Xử lý xóa phòng
     */
    public function delete($id) // Thống nhất dùng $id
    {
        $id = (int)$id; // Ép kiểu
        $room = $this->roomModel->getRoomById($id);

        // Bảo mật: Kiểm tra phòng tồn tại VÀ thuộc khách sạn của partner
        if (!$room || $room->hotel_id != $this->partnerHotel->id) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Bạn không có quyền xóa phòng này hoặc phòng không tồn tại.'];
            header('Location: ' . BASE_URL . '/partner/room'); // Redirect về trang list của partner
            exit();
        }
        $this->roomImageUploader->delete($room->image);


        if ($this->roomModel->deleteRoom($id)) { // Đã ép kiểu $id ở trên
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Xóa phòng thành công!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đã xảy ra lỗi khi xóa phòng. Có thể phòng này đang có booking.'];
        }
        header('Location: ' . BASE_URL . '/partner/room'); // Redirect về trang list của partner
        exit;
    }
}

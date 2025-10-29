<?php
// app/controllers/AdminRoomController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/helpers/ImageUploader.php';

class AdminRoomController extends BaseAdminController
{
    private $roomModel;
    private $hotelModel;
    private ImageUploader $roomImageUploader;

    public function __construct()
    {
        parent::__construct();
        $this->roomModel = new RoomModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
        $this->roomImageUploader = new ImageUploader('public/images/room/');
    }

    /**
     * Hiển thị danh sách phòng cho Admin
     */
    public function index()
    {
        // <<< THÊM: Lấy từ khóa tìm kiếm từ URL >>>
        $searchTerm = trim($_GET['search'] ?? '');

        // 1. Cấu hình Phân trang
        $limit = 10; // 10 phòng mỗi trang
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;

        // 2. Lấy dữ liệu
        // Cần đảm bảo hàm getRoomCount nhận $searchTerm
        $total_rooms = $this->roomModel->getRoomCount($searchTerm);

        // Lấy phòng đã JOIN dữ liệu và LỌC
        // SỬA: Hàm getRoomsWithRelatedData không có tham số search, nên dùng getAllRooms đã sửa
        $data['rooms'] = $this->roomModel->getAllRooms($limit, $offset, $searchTerm);

        // 3. Tính toán thông tin phân trang
        $total_pages = (int)ceil($total_rooms / $limit);

        $data['searchTerm'] = $searchTerm; // <<< TRUYỀN $searchTerm SANG VIEW

        $data['pagination'] = [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_rooms,
            'base_url' => BASE_URL . '/admin/room/index'
        ];

        include 'app/views/admin/rooms/list.php';
    }

    /**
     * Hiển thị form thêm phòng
     */
    public function add()
    {
        // Lấy tất cả khách sạn (không cần phân trang)
        $data['hotels'] = $this->hotelModel->getHotels();
        // Lấy lỗi từ session (giữ nguyên)
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
            header('Location: ' . BASE_URL . '/admin/room/add'); // Chuyển hướng về form add
            exit;
        }

        // Lưu lại input để hiển thị lại nếu có lỗi
        $_SESSION['old_input'] = $_POST;

        $hotel_id = $_POST['hotel_id'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $room_type = $_POST['room_type'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';
        $errors = [];
        $imagePath = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $this->roomImageUploader->upload($_FILES['image'], 'room_');
            } catch (Exception $e) {
                $errors['image'] = "Lỗi upload ảnh: " . $e->getMessage(); // Gán lỗi cụ thể
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $errors['image'] = "Có lỗi xảy ra khi upload file (Mã lỗi: " . $_FILES['image']['error'] . ").";
        }


        // Chỉ gọi model addRoom nếu không có lỗi upload
        if (empty($errors)) {
            $result = $this->roomModel->addRoom($hotel_id, $room_number, $room_type, $capacity, $price, $description, $imagePath); // Truyền imagePath

            if ($result === true) {
                unset($_SESSION['old_input']);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm phòng thành công!'];
                header('Location: ' . BASE_URL . '/admin/room');
                exit;
            } elseif (is_array($result)) {
                // Lỗi validation từ model
                $errors = array_merge($errors, $result);
            } else {
                // Lỗi DB không xác định
                $errors['database'] = "Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.";
            }
        }

        // Nếu có lỗi (upload hoặc validation hoặc DB), lưu lỗi vào session và redirect lại form add
        $_SESSION['form_errors'] = $errors;
        header('Location: ' . BASE_URL . '/admin/room/add');
        exit;
    }


    /**
     * Hiển thị form sửa phòng
     */
    public function edit($id)
    {
        $room = $this->roomModel->getRoomById($id);
        if (!$room) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy phòng ID: ' . $id];
            header('Location: ' . BASE_URL . '/admin/room');
            exit;
        }

        // Lấy tất cả khách sạn (không cần phân trang)
        $hotels = $this->hotelModel->getHotels();
        // Lấy lỗi từ session (giữ nguyên)
        $errors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);

        include 'app/views/admin/rooms/edit.php';
    }


    /**
     * Cập nhật dữ liệu từ form sửa
     */
    public function update($id)
    {
        $id = (int)$id; // Ép kiểu ID
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/room/edit/' . $id); // Chuyển hướng về form edit
            exit;
        }

        $room = $this->roomModel->getRoomById($id);
        if (!$room) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Phòng không tồn tại để cập nhật.'];
            header('Location: ' . BASE_URL . '/admin/room');
            exit();
        }

        $hotel_id = $_POST['hotel_id'] ?? $room->hotel_id; // Giữ giá trị cũ nếu không có post
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


        // Chỉ gọi model updateRoom nếu không có lỗi upload
        if (empty($errors)) {
            $res = $this->roomModel->updateRoom($id, $hotel_id, $room_number, $room_type, $capacity, $price, $description, $imagePath);

            if ($res === true) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật phòng thành công!'];
                header('Location: ' . BASE_URL . '/admin/room');
                exit;
            } elseif (is_array($res)) {
                // Lỗi validation từ model
                $errors = array_merge($errors, $res);
            } else {
                // Lỗi DB không xác định
                $errors['database'] = "Có lỗi xảy ra khi cập nhật cơ sở dữ liệu.";
            }
        }

        // Nếu có lỗi (upload hoặc validation hoặc DB), lưu lỗi vào session và redirect lại form edit
        $_SESSION['form_errors'] = $errors;
        // Có thể lưu lại input vào session nếu muốn giữ giá trị người dùng vừa nhập
        // $_SESSION['old_input'] = $_POST;
        header('Location: ' . BASE_URL . '/admin/room/edit/' . $id);
        exit;
    }


    /**
     * Xóa phòng (hành động từ form/link)
     */
    public function delete($id)
    {
        $id = (int)$id; // Ép kiểu ID
        $room = $this->roomModel->getRoomById($id);

        if ($room) {
            // Chỉ gọi helper để xóa ảnh
            $this->roomImageUploader->delete($room->image); // <<< SỬA: Chỉ dùng helper, bỏ unlink
        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Không tìm thấy phòng để xóa.'];
            header('Location: ' . BASE_URL . '/admin/room');
            exit();
        }


        if ($this->roomModel->deleteRoom($id)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Xóa phòng thành công!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đã xảy ra lỗi khi xóa phòng. Có thể phòng này đang có booking liên quan.'];
        }
        header('Location: ' . BASE_URL . '/admin/room');
        exit;
    }


    /**
     * Xử lý yêu cầu cập nhật một trường qua AJAX
     */
    public function updateFieldAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']); // Trả về JSON chuẩn
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';

        $allowedFields = ['room_number', 'room_type', 'capacity', 'price', 'description'];

        if ($id <= 0 || !in_array($field, $allowedFields, true)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data provided']); // Trả về JSON chuẩn
            exit;
        }

        // Thêm validate cho giá trị dựa trên field (nếu cần thiết chặt chẽ hơn)
        // Ví dụ: kiểm tra capacity, price phải là số > 0

        $result = $this->roomModel->updateRoomField($id, $field, $value);

        echo json_encode($result ? ['success' => true] : ['success' => false, 'error' => 'Update failed in database']); // Trả về JSON chuẩn
        exit;
    }

    /**
     * Xử lý yêu cầu xóa một phòng qua AJAX
     */
    public function deleteAjax()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }


        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid room id']);
            exit;
        }

        // Thêm bước xóa ảnh khi xóa qua AJAX
        $room = $this->roomModel->getRoomById($id);
        if ($room) {
            $this->roomImageUploader->delete($room->image);
        } else {
            // Có thể không cần báo lỗi nếu phòng đã bị xóa trước đó
            // echo json_encode(['success' => false, 'error' => 'Room not found']);
            // exit;
        }


        $result = $this->roomModel->deleteRoom($id);
        echo json_encode($result ? ['success' => true] : ['success' => false, 'error' => 'Delete failed in database']);
        exit;
    }

    // Hàm uploadImage() cũ không còn cần thiết, đã xóa.
}

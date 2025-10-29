<?php
// app/controllers/AdminHotelController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/HotelModel.php';
require_once 'app/models/CityModel.php';
require_once 'app/helpers/ImageUploader.php';
class AdminHotelController extends BaseAdminController
{
    private $hotelModel;
    private $cityModel;
    private ImageUploader $hotelImageUploader;

    public function __construct()
    {
        // Gọi hàm __construct của cha (BaseAdminController)
        // Dòng này sẽ tự động: 1. Bắt đầu session, 2. Kiểm tra quyền admin, 3. Kết nối DB
        parent::__construct();
        $this->hotelModel = new HotelModel($this->db);
        $this->cityModel = new CityModel($this->db);
        $this->hotelImageUploader = new ImageUploader('public/images/hotel/');
    }

    /**
     * Hiển thị danh sách khách sạn cho Admin quản lý
     */
    public function index()
    {
        $searchTerm = trim($_GET['search'] ?? '');

        // 1. Cấu hình Phân trang
        $limit = 10; // 10 khách sạn mỗi trang
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;

        // 2. Lấy dữ liệu
        $total_hotels = $this->hotelModel->getHotelCount($searchTerm); // <<< TRUYỀN $searchTerm
        $data['hotels'] = $this->hotelModel->getHotels($limit, $offset, $searchTerm); // <<< TRUYỀN $searchTerm

        // 3. Tính toán thông tin phân trang
        $total_pages = (int)ceil($total_hotels / $limit);

        $data['searchTerm'] = $searchTerm; // <<< TRUYỀN $searchTerm SANG VIEW

        $data['pagination'] = [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_hotels,
            'base_url' => BASE_URL . '/admin/hotel/index'
        ];

        include 'app/views/admin/hotels/list.php';
    }
    /**
     * Hiển thị form thêm mới khách sạn
     */
    public function add()
    {
        $data['cities'] = $this->cityModel->getCities();
        include 'app/views/admin/hotels/add.php';
    }

    /**
     * Lưu khách sạn mới từ form
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $description = $_POST['description'] ?? '';
            $city_id = $_POST['city_id'] ?? null;
            $imagePath = "";

            $service_staff = (float)($_POST['service_staff'] ?? 8.0);
            $amenities = (float)($_POST['amenities'] ?? 8.0);
            $cleanliness = (float)($_POST['cleanliness'] ?? 8.0);
            $comfort = (float)($_POST['comfort'] ?? 8.0);
            $value_for_money = (float)($_POST['value_for_money'] ?? 8.0);
            $location = (float)($_POST['location'] ?? 8.0);
            $free_wifi = (float)($_POST['free_wifi'] ?? 8.0);

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    // Sử dụng ImageUploader để upload
                    $imagePath = $this->hotelImageUploader->upload($_FILES['image'], 'hotel_'); // <--- Sử dụng helper
                } catch (Exception $e) {
                    // Xử lý lỗi upload (ví dụ: lưu vào session, hiển thị lại form)
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi upload ảnh: ' . $e->getMessage()];
                    // Chuyển hướng lại form add kèm thông báo lỗi (cần điều chỉnh)
                    header('Location: ' . BASE_URL . '/admin/hotel/add');
                    exit;
                }
            }

            $result = $this->hotelModel->addHotel(
                $name,
                $address,
                $description,
                $city_id,
                $imagePath,
                $service_staff,
                $amenities,
                $cleanliness,
                $comfort,
                $value_for_money,
                $location,
                $free_wifi
            );

            if ($result) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm khách sạn thành công!'];
                header('Location: ' . BASE_URL . '/admin/hotel');
                exit();
            } else {
                $data['errors'] = ['db_error' => 'Thêm khách sạn thất bại. Vui lòng thử lại.'];
                $data['cities'] = $this->cityModel->getCities();
                include 'app/views/admin/hotels/add.php';
            }
        }
    }

    /**
     * Hiển thị form sửa khách sạn
     */
    public function edit($id)
    {
        $data['hotel'] = $this->hotelModel->getHotelById($id);
        $data['cities'] = $this->cityModel->getCities();
        include 'app/views/admin/hotels/edit.php';
    }

    /**
     * Cập nhật thông tin khách sạn
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $description = $_POST['description'] ?? '';
            $city_id = $_POST['city_id'] ?? null;
            $image = $_POST['existing_image'] ?? '';

            $hotel = $this->hotelModel->getHotelById($id); // Lấy thông tin cũ
            if (!$hotel) { /* xử lý lỗi */
            }

            $imagePath = $hotel->image; // Giữ ảnh cũ mặc định
            $oldImagePath = $hotel->image; // Lưu lại để xóa nếu có ảnh mới

            $service_staff = (float)($_POST['service_staff'] ?? 8.0);
            $amenities = (float)($_POST['amenities'] ?? 8.0);
            $cleanliness = (float)($_POST['cleanliness'] ?? 8.0);
            $comfort = (float)($_POST['comfort'] ?? 8.0);
            $value_for_money = (float)($_POST['value_for_money'] ?? 8.0);
            $location = (float)($_POST['location'] ?? 8.0);
            $free_wifi = (float)($_POST['free_wifi'] ?? 8.0);

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    // Upload ảnh mới
                    $newImagePath = $this->hotelImageUploader->upload($_FILES['image'], 'hotel_'); // <--- Upload ảnh mới
                    if ($newImagePath) {
                        // Xóa ảnh cũ thành công trước khi gán ảnh mới
                        $this->hotelImageUploader->delete($oldImagePath); // <--- Xóa ảnh cũ
                        $imagePath = $newImagePath; // Cập nhật đường dẫn ảnh mới
                    }
                } catch (Exception $e) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi upload ảnh: ' . $e->getMessage()];
                    header('Location: ' . BASE_URL . '/admin/hotel/edit/' . $id);
                    exit;
                }
            }

            $edit = $this->hotelModel->updateHotel(
                $id,
                $name,
                $address,
                $description,
                $city_id,
                $imagePath,
                $service_staff,
                $amenities,
                $cleanliness,
                $comfort,
                $value_for_money,
                $location,
                $free_wifi
            );

            if ($edit) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật khách sạn thành công!'];
                header('Location: ' . BASE_URL . '/admin/hotel');
                exit();
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Cập nhật khách sạn thất bại.'];
                header('Location: ' . BASE_URL . '/admin/hotel/edit/' . $id);
                exit();
            }
        }
    }

    /**
     * Xóa khách sạn
     */
    public function delete($id)
    {
        $hotel = $this->hotelModel->getHotelById($id);
        if ($hotel) {
            // Xóa ảnh liên quan trước khi xóa hotel khỏi DB
            $this->hotelImageUploader->delete($hotel->image); // <--- Xóa ảnh khi xóa hotel
        }
        if ($this->hotelModel->deleteHotel($id)) {
            header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/hotel');
        } else {
            echo "Đã xảy ra lỗi khi xóa khách sạn.";
        }
    }

    /**
     * Hàm hỗ trợ upload ảnh (giữ private)
     */
    private function uploadImage($file)
    {
        $target_dir = "public/images/hotel/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . uniqid('hotel_', true) . '_' . basename($file["name"]);
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        }
        return "";
    }
}

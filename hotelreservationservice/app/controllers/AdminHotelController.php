<?php
// app/controllers/AdminHotelController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/HotelModel.php';
require_once 'app/models/CityModel.php';
class AdminHotelController extends BaseAdminController
{
    private $hotelModel;
    private $cityModel;

    public function __construct()
    {
        // Gọi hàm __construct của cha (BaseAdminController)
        // Dòng này sẽ tự động: 1. Bắt đầu session, 2. Kiểm tra quyền admin, 3. Kết nối DB
        parent::__construct();
        $this->hotelModel = new HotelModel($this->db);
        $this->cityModel = new CityModel($this->db);
    }

    /**
     * Hiển thị danh sách khách sạn cho Admin quản lý
     */
    public function index()
    {
        $data['hotels'] = $this->hotelModel->getHotelsWithCityName();
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
            $image = "";

            $service_staff = (float)($_POST['service_staff'] ?? 8.0);
            $amenities = (float)($_POST['amenities'] ?? 8.0);
            $cleanliness = (float)($_POST['cleanliness'] ?? 8.0);
            $comfort = (float)($_POST['comfort'] ?? 8.0);
            $value_for_money = (float)($_POST['value_for_money'] ?? 8.0);
            $location = (float)($_POST['location'] ?? 8.0);
            $free_wifi = (float)($_POST['free_wifi'] ?? 8.0);

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            }

            $result = $this->hotelModel->addHotel(
                $name,
                $address,
                $description,
                $city_id,
                $image,
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

            $service_staff = (float)($_POST['service_staff'] ?? 8.0);
            $amenities = (float)($_POST['amenities'] ?? 8.0);
            $cleanliness = (float)($_POST['cleanliness'] ?? 8.0);
            $comfort = (float)($_POST['comfort'] ?? 8.0);
            $value_for_money = (float)($_POST['value_for_money'] ?? 8.0);
            $location = (float)($_POST['location'] ?? 8.0);
            $free_wifi = (float)($_POST['free_wifi'] ?? 8.0);

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $newImagePath = $this->uploadImage($_FILES['image']);
                if ($newImagePath) {
                    // Xóa ảnh cũ nếu có
                    if (!empty($image) && file_exists($image)) {
                        @unlink($image);
                    }
                    $image = $newImagePath;
                }
            }

            $edit = $this->hotelModel->updateHotel(
                $id,
                $name,
                $address,
                $description,
                $city_id,
                $image,
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

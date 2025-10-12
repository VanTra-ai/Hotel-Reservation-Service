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
        $hotels = $this->hotelModel->getHotels();
        include 'app/views/admin/hotels/list.php';
    }
    /**
     * Hiển thị form thêm mới khách sạn
     */
    public function add()
    {
        $cities = $this->cityModel->getCities();
        include_once 'app/views/admin/hotels/add.php';
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

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            }

            $result = $this->hotelModel->addHotel($name, $address, $description, $city_id, $image);

            if (is_array($result)) {
                $errors = $result;
                $cities = $this->cityModel->getCities();
                include 'app/views/admin/hotels/add.php';
            } else {
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/hotel');
                exit();
            }
        }
    }

    /**
     * Hiển thị form sửa khách sạn
     */
    public function edit($id)
    {
        $hotel = $this->hotelModel->getHotelById($id);
        $cities = $this->cityModel->getCities();
        include 'app/views/admin/hotels/edit.php';
    }

    /**
     * Cập nhật thông tin khách sạn
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $address = $_POST['address'];
            $description = $_POST['description'];
            $city_id = $_POST['city_id'];
            $oldHotel = $this->hotelModel->getHotelById($id);
            $image = $oldHotel ? $oldHotel->image : '';

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $newImagePath = $this->uploadImage($_FILES['image']);
                if ($newImagePath) {
                    if (!empty($image) && file_exists($image)) {
                        @unlink($image);
                    }
                    $image = $newImagePath;
                }
            }

            $edit = $this->hotelModel->updateHotel($id, $name, $address, $description, $city_id, $image);
            if ($edit) {
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/hotel');
            } else {
                echo "Đã xảy ra lỗi khi lưu khách sạn.";
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

<?php
// app/controllers/AdminCityController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/CityModel.php';
class AdminCityController extends BaseAdminController
{
    private $cityModel;

    public function __construct()
    {
        // Gọi hàm __construct của cha (BaseAdminController) để kiểm tra quyền và kết nối DB
        parent::__construct();
        $this->cityModel = new CityModel($this->db);
    }
    /**
     * Hiển thị danh sách thành phố cho Admin
     */
    public function index()
    {
        $cities = $this->cityModel->getCities();
        include 'app/views/admin/cities/list.php';
    }

    /**
     * Hiển thị form thêm mới
     */
    public function add()
    {
        include 'app/views/admin/cities/add.php';
    }

    /**
     * Lưu dữ liệu từ form thêm mới
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $image = "";

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                try {
                    $image = $this->uploadImage($_FILES['image']);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    include 'app/views/admin/cities/add.php';
                    return;
                }
            }

            if ($this->cityModel->addCity($name, $image)) {
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/city');
                exit();
            } else {
                $errors[] = "Có lỗi xảy ra khi lưu.";
                include 'app/views/admin/cities/add.php';
            }
        }
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id)
    {
        $city = $this->cityModel->getCityById($id);
        if ($city) {
            include 'app/views/admin/cities/edit.php';
        } else {
            echo "Không thấy tỉnh thành";
        }
    }

    /**
     * Cập nhật dữ liệu từ form sửa
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? '');
            $oldCity = $this->cityModel->getCityById($id);

            if (!$oldCity) {
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/city?error=notfound');
                exit();
            }

            $imagePath = $oldCity->image;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                try {
                    $newImagePath = $this->uploadImage($_FILES['image']);
                    if ($newImagePath && $oldCity->image && file_exists($oldCity->image)) {
                        unlink($oldCity->image);
                    }
                    $imagePath = $newImagePath;
                } catch (Exception $e) {
                    echo "Lỗi: " . $e->getMessage();
                    return;
                }
            }
            
            if ($this->cityModel->updateCity($id, $name, $imagePath)) {
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/city');
            } else {
                echo "Đã xảy ra lỗi khi cập nhật tỉnh thành.";
            }
        }
    }

    /**
     * Xóa một thành phố
     */
    public function delete($id)
    {
        $city = $this->cityModel->getCityById($id);
        if ($city && !empty($city->image) && file_exists($city->image)) {
            @unlink($city->image);
        }

        if ($this->cityModel->deleteCity($id)) {
            header('Location: /Hotel-Reservation-Service/hotelreservationservice/admin/city');
        } else {
            echo "Đã xảy ra lỗi khi xóa.";
        }
    }

    /**
     * Hàm private hỗ trợ upload ảnh
     */
    private function uploadImage($file)
    {
        $target_dir = "public/images/cityimages/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed, true)) throw new Exception("Chỉ cho phép JPG/PNG/GIF.");
        if ($file['size'] > 10 * 1024 * 1024) throw new Exception("Kích thước ảnh vượt quá 10MB.");

        $filename = uniqid('city_', true) . '.' . $ext;
        $target_file = $target_dir . $filename;

        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Có lỗi xảy ra khi tải lên hình ảnh.");
        }
        return $target_file;
    }
}

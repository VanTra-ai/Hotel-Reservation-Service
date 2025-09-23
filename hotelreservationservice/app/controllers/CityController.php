<?php
// app/controllers/CityController.php

require_once('app/config/database.php');
require_once('app/models/CityModel.php');
require_once('app/helpers/SessionHelper.php');

class CityController
{
    private $cityModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->cityModel = new CityModel($this->db);
    }

    public function list()
    {
        $cities = $this->cityModel->getCities();
        include 'app/views/city/list.php';
    }

    public function add()
    {
        include 'app/views/city/add.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = "";
            }

            $result = $this->cityModel->addCity($name, $image);
            if (is_array($result)) {
                $errors = $result;
                include 'app/views/city/add.php';
            } else {
                header('Location: /hotelreservationservice/City/list');
            }
        }
    }

    public function edit($id)
    {
        $city = $this->cityModel->getCityById($id);
        if ($city) {
            include 'app/views/city/edit.php';
        } else {
            echo "Không thấy tỉnh thành";
        }
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? '');

            // 1. Fetch the existing city data from the database
            $oldCity = $this->cityModel->getCityById($id);
            if (!$oldCity) {
                // Redirect if city not found
                header('Location: /hotelreservationservice/city/list?error_message=Không tìm thấy tỉnh thành!');
                exit();
            }

            $imagePath = $oldCity->image; // Default to the existing image path

            // 2. Handle new image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                try {
                    // Upload the new image and get its path
                    $newImagePath = $this->uploadImage($_FILES['image']);

                    // If upload is successful, delete the old image file
                    if ($newImagePath && $oldCity->image && file_exists($oldCity->image)) {
                        unlink($oldCity->image);
                    }
                    $imagePath = $newImagePath;
                } catch (Exception $e) {
                    echo "Lỗi: " . $e->getMessage();
                    return;
                }
            }

            // 3. Update the database with both name and the determined image path
            $edit = $this->cityModel->updateCity($id, $name, $imagePath);

            if ($edit) {
                header('Location: /hotelreservationservice/city/list');
            } else {
                echo "Đã xảy ra lỗi khi lưu tỉnh thành.";
            }
        }
    }

    public function delete($id)
    {
        if ($this->cityModel->deleteCity($id)) {
            header('Location: /hotelreservationservice/city/list');
        } else {
            echo "Đã xảy ra lỗi khi xóa sản phẩm.";
        }
    }

    // API: Trả về danh sách tỉnh/thành dạng JSON cho autocomplete ở trang chủ
    public function getCitiesJson()
    {
        try {
            $cities = $this->cityModel->getCities();
            // Chỉ trả về các trường cần thiết
            $data = array_map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                ];
            }, $cities);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Không thể tải danh sách tỉnh thành']);
        }
    }

    private function uploadImage($file)
    {
        $target_dir = "public/images/cityimages/";
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

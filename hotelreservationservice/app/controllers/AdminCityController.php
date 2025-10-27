<?php
// app/controllers/AdminCityController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/CityModel.php';
require_once 'app/helpers/ImageUploader.php';

class AdminCityController extends BaseAdminController
{
    private $cityModel;
    private ImageUploader $cityImageUploader;

    public function __construct()
    {
        parent::__construct();
        $this->cityModel = new CityModel($this->db);
        // Khởi tạo ImageUploader với đúng thư mục và gán vào thuộc tính đã sửa tên
        $this->cityImageUploader = new ImageUploader('public/images/cityimages/');
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
        // Khởi tạo $errors để tránh lỗi undefined variable trong view nếu truy cập trực tiếp
        $errors = [];
        include 'app/views/admin/cities/add.php';
    }

    /**
     * Lưu dữ liệu từ form thêm mới
     */
    public function save()
    {
        $errors = []; // Luôn khởi tạo mảng lỗi ở đầu hàm
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $imagePath = ""; // Khởi tạo

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    // Sử dụng đúng thuộc tính cityImageUploader
                    $imagePath = $this->cityImageUploader->upload($_FILES['image'], 'city_'); // <<< SỬA TÊN THUỘC TÍNH
                } catch (Exception $e) {
                    $errors[] = "Lỗi upload ảnh: " . $e->getMessage();
                    // Hiển thị lại form add với lỗi
                    include 'app/views/admin/cities/add.php'; // Truyền $errors vào view
                    return;
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                // Xử lý các lỗi upload khác nếu có file nhưng bị lỗi
                $errors[] = "Có lỗi xảy ra trong quá trình upload file (Mã lỗi: " . $_FILES['image']['error'] . ").";
                include 'app/views/admin/cities/add.php';
                return;
            }


            // Kiểm tra tên thành phố không được trống
            if (empty(trim($name))) {
                $errors[] = "Tên thành phố không được để trống.";
            }

            // Chỉ lưu nếu không có lỗi
            if (empty($errors)) {
                if ($this->cityModel->addCity($name, $imagePath)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm thành phố thành công!'];
                    header('Location: ' . BASE_URL . '/admin/city');
                    exit();
                } else {
                    $errors[] = "Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.";
                }
            }

            // Nếu có lỗi (kể cả lỗi DB), hiển thị lại form add
            if (!empty($errors)) {
                include 'app/views/admin/cities/add.php'; // Truyền $errors vào view
                return; // Dừng thực thi sau khi include view
            }
        } else {
            // Nếu không phải POST, chuyển hướng về trang add
            header('Location: ' . BASE_URL . '/admin/city/add');
            exit();
        }
    }


    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id)
    {
        $city = $this->cityModel->getCityById((int)$id); // Ép kiểu ID sang int
        if ($city) {
            include 'app/views/admin/cities/edit.php';
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy thành phố ID: ' . $id];
            header('Location: ' . BASE_URL . '/admin/city');
            exit();
        }
    }

    /**
     * Cập nhật dữ liệu từ form sửa
     */
    public function update($id)
    {
        $id = (int)$id; // Ép kiểu ID
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? '');
            $oldCity = $this->cityModel->getCityById($id);

            if (!$oldCity) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy thành phố để cập nhật.'];
                header('Location: ' . BASE_URL . '/admin/city');
                exit();
            }

            // Kiểm tra tên không được trống
            if (empty($name)) {
                // Hiển thị lại form edit với lỗi
                $error = "Tên thành phố không được để trống.";
                $city = $oldCity; // Cần $city để view edit hiển thị lại
                include 'app/views/admin/cities/edit.php';
                return;
            }

            $imagePath = $oldCity->image; // Giữ ảnh cũ mặc định
            $oldImagePath = $oldCity->image; // Lưu lại để xóa

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    // Sử dụng đúng thuộc tính cityImageUploader
                    $newImagePath = $this->cityImageUploader->upload($_FILES['image'], 'city_'); // <<< SỬA TÊN THUỘC TÍNH
                    if ($newImagePath) {
                        $this->cityImageUploader->delete($oldImagePath); // Xóa ảnh cũ
                        $imagePath = $newImagePath; // Cập nhật đường dẫn mới
                    }
                } catch (Exception $e) {
                    // Hiển thị lại form edit với lỗi upload
                    $error = "Lỗi upload ảnh: " . $e->getMessage();
                    $city = $oldCity; // Cần $city để view edit hiển thị lại
                    include 'app/views/admin/cities/edit.php';
                    return;
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                // Xử lý các lỗi upload khác
                $error = "Có lỗi xảy ra trong quá trình upload file (Mã lỗi: " . $_FILES['image']['error'] . ").";
                $city = $oldCity;
                include 'app/views/admin/cities/edit.php';
                return;
            }


            if ($this->cityModel->updateCity($id, $name, $imagePath)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật thành phố thành công!'];
                header('Location: ' . BASE_URL . '/admin/city');
                exit();
            } else {
                // Hiển thị lại form edit với lỗi DB
                $error = "Đã xảy ra lỗi khi cập nhật vào cơ sở dữ liệu.";
                $city = $oldCity; // Nạp lại dữ liệu cũ
                $city->name = $name; // Giữ lại tên người dùng vừa nhập
                include 'app/views/admin/cities/edit.php';
                return;
            }
        } else {
            // Nếu không phải POST, chuyển hướng về trang edit
            header('Location: ' . BASE_URL . '/admin/city/edit/' . $id);
            exit();
        }
    }


    /**
     * Xóa một thành phố
     */
    public function delete($id)
    {
        $id = (int)$id; // Ép kiểu ID
        $city = $this->cityModel->getCityById($id);

        if ($city) {
            // Chỉ gọi helper để xóa ảnh
            $this->cityImageUploader->delete($city->image);
        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Không tìm thấy thành phố để xóa.'];
            header('Location: ' . BASE_URL . '/admin/city');
            exit();
        }


        if ($this->cityModel->deleteCity($id)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Xóa thành phố thành công!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đã xảy ra lỗi khi xóa thành phố. Có thể thành phố này đang được sử dụng.'];
        }
        header('Location: ' . BASE_URL . '/admin/city');
        exit();
    }

    // Hàm uploadImage() cũ không còn cần thiết, đã xóa.
}

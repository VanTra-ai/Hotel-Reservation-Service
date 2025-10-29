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
        $searchTerm = trim($_GET['search'] ?? '');

        // 1. Cấu hình Phân trang
        $limit = 10; // 10 thành phố mỗi trang
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;

        // 2. Lấy dữ liệu
        $total_cities = $this->cityModel->getCityCount($searchTerm); // <<< TRUYỀN $searchTerm
        $cities = $this->cityModel->getCities($limit, $offset, $searchTerm); // <<< TRUYỀN $searchTerm

        // 3. Tính toán thông tin phân trang
        $total_pages = (int)ceil($total_cities / $limit);

        $data = [
            'cities' => $cities,
            'searchTerm' => $searchTerm, // <<< TRUYỀN $searchTerm SANG VIEW
            'pagination' => [
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'total_items' => $total_cities,
                'base_url' => BASE_URL . '/admin/city/index'
            ]
        ];

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
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $imagePath = "";

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    $imagePath = $this->cityImageUploader->upload($_FILES['image'], 'city_');
                } catch (Exception $e) {
                    $errors[] = "Lỗi upload ảnh: " . $e->getMessage();
                    include 'app/views/admin/cities/add.php';
                    return;
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                $errors[] = "Có lỗi xảy ra trong quá trình upload file (Mã lỗi: " . $_FILES['image']['error'] . ").";
                include 'app/views/admin/cities/add.php';
                return;
            }

            if (empty(trim($name))) {
                $errors[] = "Tên thành phố không được để trống.";
            }

            if (empty($errors)) {
                if ($this->cityModel->addCity($name, $imagePath)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm thành phố thành công!'];
                    header('Location: ' . BASE_URL . '/admin/city');
                    exit();
                } else {
                    $errors[] = "Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.";
                }
            }

            if (!empty($errors)) {
                include 'app/views/admin/cities/add.php';
                return;
            }
        } else {
            header('Location: ' . BASE_URL . '/admin/city/add');
            exit();
        }
    }


    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id)
    {
        $city = $this->cityModel->getCityById((int)$id);
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
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? '');
            $oldCity = $this->cityModel->getCityById($id);

            if (!$oldCity) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy thành phố để cập nhật.'];
                header('Location: ' . BASE_URL . '/admin/city');
                exit();
            }

            if (empty($name)) {
                $error = "Tên thành phố không được để trống.";
                $city = $oldCity;
                include 'app/views/admin/cities/edit.php';
                return;
            }

            $imagePath = $oldCity->image;
            $oldImagePath = $oldCity->image;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    $newImagePath = $this->cityImageUploader->upload($_FILES['image'], 'city_');
                    if ($newImagePath) {
                        $this->cityImageUploader->delete($oldImagePath);
                        $imagePath = $newImagePath;
                    }
                } catch (Exception $e) {
                    $error = "Lỗi upload ảnh: " . $e->getMessage();
                    $city = $oldCity;
                    include 'app/views/admin/cities/edit.php';
                    return;
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
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
                $error = "Đã xảy ra lỗi khi cập nhật vào cơ sở dữ liệu.";
                $city = $oldCity;
                $city->name = $name;
                include 'app/views/admin/cities/edit.php';
                return;
            }
        } else {
            header('Location: ' . BASE_URL . '/admin/city/edit/' . $id);
            exit();
        }
    }


    /**
     * Xóa một thành phố
     */
    public function delete($id)
    {
        $id = (int)$id;
        $city = $this->cityModel->getCityById($id);

        if ($city) {
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

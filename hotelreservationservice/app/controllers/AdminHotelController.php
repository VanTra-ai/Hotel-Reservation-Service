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
    private ImageUploader $galleryImageUploader;

    public function __construct()
    {
        // Gọi hàm __construct của cha (BaseAdminController)
        // Dòng này sẽ tự động: 1. Bắt đầu session, 2. Kiểm tra quyền admin, 3. Kết nối DB
        parent::__construct();
        $this->hotelModel = new HotelModel($this->db);
        $this->cityModel = new CityModel($this->db);
        $this->hotelImageUploader = new ImageUploader('public/images/hotel/');
        $this->galleryImageUploader = new ImageUploader('public/images/hotels/gallery/');
    }

    /**
     * Hiển thị danh sách khách sạn cho Admin quản lý
     */
    public function index()
    {
        $searchTerm = trim($_GET['search'] ?? '');
        $limit = 10;
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;

        $total_hotels = $this->hotelModel->getHotelCount($searchTerm);
        $data['hotels'] = $this->hotelModel->getHotels($limit, $offset, $searchTerm);
        $total_pages = (int)ceil($total_hotels / $limit);
        $data['searchTerm'] = $searchTerm;

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
        $data['cities'] = $this->cityModel->getCities(null, null); // Lấy tất cả city
        include 'app/views/admin/hotels/add.php';
    }

    /**
     * Lưu khách sạn mới từ form
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Lấy thông tin cơ bản
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $description = $_POST['description'] ?? '';
            $city_id = $_POST['city_id'] ?? null;

            $imagePath = ""; // Ảnh đại diện chính
            $galleryPaths = [];

            // 2. Xử lý Ảnh Đại diện 
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    $imagePath = $this->hotelImageUploader->upload($_FILES['image'], 'hotel_main_');
                    $galleryPaths[] = $imagePath;
                } catch (Exception $e) { /* ... (xử lý lỗi) ... */
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Khách sạn phải có ảnh đại diện.'];
                header('Location: ' . BASE_URL . '/admin/hotel/add');
                exit;
            }

            // 3. Xử lý Gallery
            if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                $additionalPaths = $this->uploadMultipleImages($_FILES['gallery_images']);
                $galleryPaths = array_merge($galleryPaths, $additionalPaths);
            }

            // 4. Lưu vào bảng hotel
            // Model sẽ tự động dùng giá trị DEFAULT (8.0)
            $result = $this->hotelModel->addHotel(
                $name,
                $address,
                $description,
                $city_id,
                $imagePath
            );

            if ($result === true) {
                $hotel_id = $this->db->lastInsertId();

                // 5. Lưu ảnh gallery (Giữ nguyên)
                if (!empty($galleryPaths)) {
                    $this->hotelModel->saveHotelImages($hotel_id, $galleryPaths, $imagePath);
                }

                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm khách sạn thành công!'];
                header('Location: ' . BASE_URL . '/admin/hotel');
                exit();
            } else {
                $data['errors'] = is_array($result) ? $result : ['db_error' => 'Thêm khách sạn thất bại. Vui lòng thử lại.'];
                $data['cities'] = $this->cityModel->getCities(null, null);
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
        if (!$data['hotel']) { /* xử lý lỗi 404 */
        }

        $data['cities'] = $this->cityModel->getCities(null, null);

        // Lấy danh sách ảnh gallery hiện tại
        $data['gallery_images'] = $this->hotelModel->getHotelImages($id);

        include 'app/views/admin/hotels/edit.php';
    }

    /**
     * Cập nhật thông tin khách sạn
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hotel_id = (int)$id;
            $oldHotel = $this->hotelModel->getHotelById($hotel_id);
            if (!$oldHotel) { /* xử lý lỗi */
            }

            // 1. Lấy thông tin cơ bản và 7 điểm
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $description = $_POST['description'] ?? '';
            $city_id = $_POST['city_id'] ?? null;
            $service_staff = (float)($_POST['service_staff'] ?? 8.0);
            $amenities = (float)($_POST['amenities'] ?? 8.0);
            $cleanliness = (float)($_POST['cleanliness'] ?? 8.0);
            $comfort = (float)($_POST['comfort'] ?? 8.0);
            $value_for_money = (float)($_POST['value_for_money'] ?? 8.0);
            $location = (float)($_POST['location'] ?? 8.0);
            $free_wifi = (float)($_POST['free_wifi'] ?? 8.0);

            // 2. Xử lý ảnh đại diện
            $imagePath = $oldHotel->image; // Giữ ảnh cũ
            $newMainImageUploaded = false;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    $newImagePath = $this->hotelImageUploader->upload($_FILES['image'], 'hotel_main_');
                    if ($newImagePath) {
                        $this->hotelImageUploader->delete($oldHotel->image); // Xóa file ảnh đại diện cũ
                        $imagePath = $newImagePath; // Cập nhật ảnh đại diện mới
                        $newMainImageUploaded = true;
                    }
                } catch (Exception $e) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi upload ảnh chính: ' . $e->getMessage()];
                    header('Location: ' . BASE_URL . '/admin/hotel/edit/' . $id);
                    exit;
                }
            }

            // 3. Cập nhật bảng hotel
            $edit = $this->hotelModel->updateHotel(
                $hotel_id,
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
                // 4. Xử lý Gallery

                // a. Xóa các ảnh được đánh dấu
                $imagesToDelete = $_POST['delete_images'] ?? [];
                if (!empty($imagesToDelete)) {
                    $existingImages = $this->hotelModel->getHotelImages($hotel_id);
                    foreach ($imagesToDelete as $imageIdToDelete) {
                        $imageObj = null;
                        foreach ($existingImages as $img) {
                            if ($img->id == $imageIdToDelete) {
                                $imageObj = $img;
                                break;
                            }
                        }

                        if ($imageObj) {
                            // Phải kiểm tra xem ảnh này thuộc uploader nào
                            if (str_starts_with($imageObj->image_path, 'public/images/hotel/')) {
                                $this->hotelImageUploader->delete($imageObj->image_path);
                            } else {
                                $this->galleryImageUploader->delete($imageObj->image_path);
                            }

                            // <<< SỬA LỖI: Gọi hàm trong Model >>>
                            $this->hotelModel->deleteHotelImageById((int)$imageIdToDelete);
                        }
                    }
                }

                // b. Thêm ảnh gallery mới
                $newGalleryPaths = [];
                if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                    $newGalleryPaths = $this->uploadMultipleImages($_FILES['gallery_images']);
                }

                // c. Nếu ảnh đại diện mới được upload, thêm nó vào gallery (nếu chưa có)
                if ($newMainImageUploaded) {
                    if (!$this->hotelModel->checkImageExists($hotel_id, $imagePath)) { // Cần tạo hàm checkImageExists
                        $newGalleryPaths[] = $imagePath;
                    }
                }

                // d. Lưu các ảnh mới vào CSDL
                if (!empty($newGalleryPaths)) {
                    $this->hotelModel->saveHotelImages($hotel_id, $newGalleryPaths, $imagePath);
                }

                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật khách sạn thành công!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Cập nhật khách sạn thất bại.'];
            }

            header('Location: ' . BASE_URL . '/admin/hotel/edit/' . $id); // Quay lại trang edit
            exit();
        }
    }

    /**
     * Xóa khách sạn
     */
    public function delete($id)
    {
        $hotel_id = (int)$id;
        $hotel = $this->hotelModel->getHotelById($hotel_id);

        if ($hotel) {
            // 1. Lấy tất cả ảnh gallery
            $galleryImages = $this->hotelModel->getHotelImages($hotel_id);

            // 2. Xóa file ảnh đại diện chính
            $this->hotelImageUploader->delete($hotel->image);

            // 3. Xóa tất cả file ảnh gallery (dùng đúng uploader)
            foreach ($galleryImages as $img) {
                // Kiểm tra ảnh nằm ở thư mục nào
                if (str_starts_with($img->image_path, 'public/images/hotel/')) {
                    $this->hotelImageUploader->delete($img->image_path);
                } else {
                    $this->galleryImageUploader->delete($img->image_path);
                }
            }

            // 4. Xóa khách sạn (ON DELETE CASCADE sẽ tự xóa bản ghi trong hotel_images)
            if ($this->hotelModel->deleteHotel($hotel_id)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Xóa khách sạn và tất cả ảnh thành công!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Không thể xóa khách sạn.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Không tìm thấy khách sạn để xóa.'];
        }

        header('Location: ' . BASE_URL . '/admin/hotel');
        exit;
    }
    /**
     * Hàm helper xử lý upload NHIỀU FILE
     * @param array $fileArray $_FILES['multi_images']
     * @return array Mảng các đường dẫn ảnh đã upload thành công
     */
    private function uploadMultipleImages(array $fileArray): array
    {
        $uploadedPaths = [];
        $totalFiles = count($fileArray['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            if ($fileArray['error'][$i] === UPLOAD_ERR_OK) {
                try {
                    // Tạo mảng file tạm thời cho uploader
                    $tempFile = [
                        'name' => $fileArray['name'][$i],
                        'type' => $fileArray['type'][$i],
                        'tmp_name' => $fileArray['tmp_name'][$i],
                        'error' => $fileArray['error'][$i],
                        'size' => $fileArray['size'][$i]
                    ];
                    $uploadedPaths[] = $this->galleryImageUploader->upload($tempFile, 'hotel_gallery_');
                } catch (Exception $e) {
                    error_log("Multi-upload error: " . $e->getMessage());
                }
            }
        }
        return $uploadedPaths;
    }
}

<?php
// app/controllers/PartnerHotelController.php

require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/HotelModel.php';
require_once 'app/models/CityModel.php';
require_once 'app/helpers/ImageUploader.php';

class PartnerHotelController extends BasePartnerController
{
    private $hotelModel;
    private $cityModel;
    private $partnerHotel; // Khách sạn của partner
    private ImageUploader $hotelImageUploader;
    private ImageUploader $galleryImageUploader;

    public function __construct()
    {
        parent::__construct(); // Tự động kiểm tra quyền Partner
        $this->hotelModel = new HotelModel($this->db);
        $this->cityModel = new CityModel($this->db);
        $partnerId = SessionHelper::getAccountId();
        $this->partnerHotel = $this->hotelModel->getHotelByOwnerId($partnerId);

        // Khởi tạo Uploader
        $this->hotelImageUploader = new ImageUploader('public/images/hotel/');
        $this->galleryImageUploader = new ImageUploader('public/images/hotels/gallery/');
    }

    /**
     * Hiển thị trang quản lý khách sạn của Partner
     */
    public function index()
    {
        if (!$this->partnerHotel) {
            // Xử lý trường hợp partner chưa được gán khách sạn
            $data['error'] = "Bạn chưa được gán quyền quản lý cho khách sạn nào.";
            include 'app/views/partner/hotels/no_hotel.php'; // Tạo view này nếu cần
            return;
        }

        $data['hotel'] = $this->partnerHotel;
        $data['cities'] = $this->cityModel->getCities(null, null); // Lấy tất cả thành phố

        $data['gallery_images'] = $this->hotelModel->getHotelImages($this->partnerHotel->id);

        include 'app/views/partner/hotels/edit.php';
    }
    /**
     * Xử lý cập nhật thông tin khách sạn
     */
    public function update($id)
    {
        // Đảm bảo partner chỉ cập nhật khách sạn của họ
        if (!$this->partnerHotel || $this->partnerHotel->id != $id) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Bạn không có quyền truy cập.'];
            header('Location: ' . BASE_URL . '/partner/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hotel_id = (int)$id;

            // 1. Lấy thông tin
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $description = $_POST['description'] ?? '';
            $city_id = $_POST['city_id'] ?? null;

            // 2. Xử lý ảnh đại diện
            $imagePath = $this->partnerHotel->image; // Giữ ảnh cũ
            $newMainImageUploaded = false;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                try {
                    $newImagePath = $this->hotelImageUploader->upload($_FILES['image'], 'hotel_main_');
                    if ($newImagePath) {
                        $this->hotelImageUploader->delete($this->partnerHotel->image); // Xóa file cũ
                        $imagePath = $newImagePath;
                        $newMainImageUploaded = true;
                    }
                } catch (Exception $e) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi upload ảnh chính: ' . $e->getMessage()];
                    header('Location: ' . BASE_URL . '/partner/hotel'); // Dùng /partner/hotel (là hàm index)
                    exit;
                }
            }

            // 3. Cập nhật bảng hotel (Partner chỉ cập nhật thông tin cơ bản)
            $edit = $this->hotelModel->updateHotelBasicInfo($hotel_id, $name, $address, $description, $city_id, $imagePath);

            if ($edit) {
                // 4. Xử lý Gallery

                // a. Xóa các ảnh được đánh dấu
                $imagesToDelete = $_POST['delete_images'] ?? [];
                if (!empty($imagesToDelete)) {
                    $existingImages = $this->hotelModel->getHotelImages($hotel_id);
                    foreach ($imagesToDelete as $imageIdToDelete) {
                        $imageObj = null;
                        foreach ($existingImages as $img) {
                            if ($img->id == $imageIdToDelete) $imageObj = $img;
                        }

                        if ($imageObj) {
                            if (str_starts_with($imageObj->image_path, 'public/images/hotel/')) {
                                $this->hotelImageUploader->delete($imageObj->image_path);
                            } else {
                                $this->galleryImageUploader->delete($imageObj->image_path);
                            }
                            $this->hotelModel->deleteHotelImageById((int)$imageIdToDelete);
                        }
                    }
                }

                // b. Thêm ảnh gallery mới
                $newGalleryPaths = [];
                if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                    $newGalleryPaths = $this->uploadMultipleImages($_FILES['gallery_images']);
                }

                // c. Nếu ảnh đại diện mới được upload, thêm nó vào gallery
                if ($newMainImageUploaded) {
                    if (!$this->hotelModel->checkImageExists($hotel_id, $imagePath)) {
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

            header('Location: ' . BASE_URL . '/partner/hotel'); // Quay lại trang edit
            exit();
        }
    }
    /**
     * Hàm helper xử lý upload NHIỀU FILE
     */
    private function uploadMultipleImages(array $fileArray): array
    {
        $uploadedPaths = [];
        $totalFiles = count($fileArray['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            if ($fileArray['error'][$i] === UPLOAD_ERR_OK) {
                try {
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

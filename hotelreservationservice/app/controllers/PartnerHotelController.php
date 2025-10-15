<?php
// app/controllers/PartnerHotelController.php

require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/HotelModel.php';
require_once 'app/models/CityModel.php';

class PartnerHotelController extends BasePartnerController
{
    private $hotelModel;
    private $cityModel;

    public function __construct()
    {
        parent::__construct(); // Tự động kiểm tra quyền Partner
        $this->hotelModel = new HotelModel($this->db);
        $this->cityModel = new CityModel($this->db);
    }

    /**
     * Hiển thị trang quản lý khách sạn của Partner
     */
    public function index()
    {
        $partnerId = SessionHelper::getAccountId();
        $hotel = $this->hotelModel->getHotelByOwnerId($partnerId);

        // Nếu partner chưa được gán khách sạn nào, có thể hiển thị thông báo
        if (!$hotel) {
            // Bạn có thể tạo một view riêng để thông báo cho partner
            echo "Bạn chưa được gán quyền quản lý cho bất kỳ khách sạn nào.";
            exit;
        }

        $cities = $this->cityModel->getCities();

        // Truyền dữ liệu tới view
        $data['hotel'] = $hotel;
        $data['cities'] = $cities;

        include 'app/views/partner/hotels/edit.php';
    }

    /**
     * Xử lý cập nhật thông tin khách sạn
     */
    public function update($hotelId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/partner/hotel');
            exit;
        }

        $partnerId = SessionHelper::getAccountId();
        $hotel = $this->hotelModel->getHotelById((int)$hotelId);

        // Lớp bảo mật: Đảm bảo partner chỉ có thể sửa khách sạn của chính họ
        if (!$hotel || $hotel->owner_id != $partnerId) {
            // Chuyển hướng hoặc báo lỗi nếu cố tình sửa khách sạn của người khác
            header('Location: ' . BASE_URL . '/partner/dashboard');
            exit;
        }

        // Lấy các thông tin được phép sửa
        $name = $_POST['name'] ?? $hotel->name;
        $address = $_POST['address'] ?? $hotel->address;
        $description = $_POST['description'] ?? $hotel->description;
        $city_id = $_POST['city_id'] ?? $hotel->city_id;
        $image = $hotel->image;

        // Xử lý ảnh mới (nếu có)
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // (Thêm code upload và xóa ảnh cũ ở đây)
        }

        // Gọi hàm update trong model
        // LƯU Ý: Chúng ta chỉ truyền các trường được phép sửa. 7 điểm đặc trưng sẽ được giữ nguyên giá trị cũ trong DB.
        $this->hotelModel->updateHotelBasicInfo($hotelId, $name, $address, $description, $city_id, $image);

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật thông tin khách sạn thành công!'];
        header('Location: ' . BASE_URL . '/partner/hotel');
        exit;
    }
}

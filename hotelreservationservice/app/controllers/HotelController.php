<?php
// Require SessionHelper and other necessary files
require_once('app/config/database.php');
require_once 'app/models/HotelModel.php';
require_once 'app/models/CityModel.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/ReviewModel.php';
require_once('app/helpers/SessionHelper.php');
class HotelController
{
    private $hotelModel;
    private $cityModel;
    private $roomModel;
    private $reviewModel;
    private $db;
    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->hotelModel = new HotelModel($this->db);
        $this->cityModel = new CityModel($this->db);
        $this->roomModel = new RoomModel($this->db);
        $this->reviewModel = new ReviewModel($this->db);
    }

    public function list()
    {
        $provinceName = $_GET['province'] ?? '';
        $hotels = [];

        if (!empty($provinceName)) {
            $city = $this->cityModel->getCityByName($provinceName);

            if ($city) {
                $hotels = $this->hotelModel->getHotelsByCityId($city->id);
            }
        }

        include_once 'app/views/hotel/list.php';
    }

    public function show($id)
{
    if (!is_numeric($id) || $id <= 0) {
        http_response_code(404);
        echo "Không tìm thấy khách sạn.";
        return;
    }

    $hotel = $this->hotelModel->getHotelById($id);
    $rooms = $this->roomModel->getRoomsByHotelId($id);
    $reviews = $this->reviewModel->getReviewsByHotelId($id);

    // 🔹 Thêm dòng này để lấy trung bình theo hạng mục
    $averageRatings = $this->reviewModel->getAverageRatingsByCategory($id);

    if (!$hotel) {
        http_response_code(404);
        echo "Không tìm thấy khách sạn.";
        return;
    }

    // Truyền thêm $averageRatings sang view
    include_once 'app/views/hotel/show.php';
}
    public function index()
    {
        $hotels = $this->hotelModel->getHotels();
        include 'app/views/hotel/list.php';
    }
    public function add()
    {
        $cities = (new CityModel($this->db))->getCities();
        include_once 'app/views/hotel/add.php';
    }
    public function save()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $description = $_POST['description'] ?? '';
            $city_id = $_POST['city_id'] ?? null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = "";
            }

            $result = $this->hotelModel->addHotel($name, $address, $description, $city_id, $image);

            if (is_array($result)) {
                $errors = $result;
                $cities = (new CityModel($this->db))->getCities();
                include 'app/views/hotel/add.php';
            } else {
                header('Location: /hotelreservationservice/Hotel');
                exit();
            }
        }
    }
    public function edit($id)
    {
        $hotel = $this->hotelModel->getHotelById($id);
        $cities = (new cityModel($this->db))->getCities();
        include 'app/views/hotel/edit.php';
    }
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $address = $_POST['address'];
            $description = $_POST['description'];
            $city_id = $_POST['city_id'];
            $existingImage = $_POST['existing_image'] ?? '';

            // Lấy dữ liệu hiện tại để có đường dẫn ảnh cũ an toàn
            $oldHotel = $this->hotelModel->getHotelById($id);
            $image = $oldHotel ? ($oldHotel->image ?? '') : $existingImage;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $newImagePath = $this->uploadImage($_FILES['image']);
                if ($newImagePath) {
                    // Xóa ảnh cũ nếu tồn tại file
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
                $image
            );
            if ($edit) {
                header('Location: /hotelreservationservice/Hotel');
            } else {
                echo "Đã xảy ra lỗi khi lưu khách sạn.";
            }
        }
    }
    public function delete($id)
    {
        if ($this->hotelModel->deleteHotel($id)) {
            header('Location: /hotelreservationservice/Hotel');
        } else {
            echo "Đã xảy ra lỗi khi xóa khách sạn.";
        }
    }
    private function uploadImage($file)
    {
        $target_dir = "public/images/hotel/";
        // Kiểm tra và tạo thư mục nếu chưa tồn tại
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
    public function orderConfirmation()
    {
        include 'app/views/hotel/orderConfirmation.php';
    }
}

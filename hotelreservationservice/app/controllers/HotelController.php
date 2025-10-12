<?php
// app/controllers/HotelController.php
// FILE NÀY BÂY GIỜ CHỈ DÀNH CHO USER XEM

require_once('app/config/database.php');
require_once 'app/models/HotelModel.php';
require_once 'app/models/CityModel.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/ReviewModel.php';

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

    /**
     * Hiển thị danh sách khách sạn theo tỉnh thành cho người dùng
     */
    public function list()
    {
        $provinceName = $_GET['province'] ?? '';
        $hotels = [];

        if (!empty($provinceName)) {
            $city = $this->cityModel->getCityByName($provinceName);
            if ($city) {
                $hotels = $this->hotelModel->getHotelsByCityId($city->id);
            }
        } else {
            // Mặc định, có thể hiển thị tất cả khách sạn nếu không có tỉnh
            $hotels = $this->hotelModel->getHotels();
        }
        
        include_once 'app/views/hotel/list.php';
    }

    /**
     * Hiển thị chi tiết một khách sạn cho người dùng
     */
    public function show($id)
    {
        $hotel = $this->hotelModel->getHotelById($id);
        if (!$hotel) {
            http_response_code(404);
            echo "Không tìm thấy khách sạn.";
            return;
        }

        $rooms = $this->roomModel->getRoomsByHotelId($id); // Bạn cần đảm bảo model có hàm này
        $reviews = $this->reviewModel->getReviewsByHotelId($id);
        $averageRatings = $this->reviewModel->getAverageRatingsByCategory($id);
        
        include_once 'app/views/hotel/show.php';
    }
}
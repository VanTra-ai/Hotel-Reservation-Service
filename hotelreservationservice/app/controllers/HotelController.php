<?php
// app/controllers/HotelController.php

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

    /**
     * Hiển thị danh sách khách sạn theo tỉnh thành cho người dùng
     */
    public function list()
    {
        // 1. Lấy tham số Filter và Sort từ URL
        $provinceName = $_GET['province'] ?? '';
        $dates_raw = $_GET['dates'] ?? '';

        $sortBy = $_GET['sort_by'] ?? 'rating'; // Mặc định: điểm cao nhất
        $order = $_GET['order'] ?? 'DESC';

        // 2. Xử lý City ID (nếu có)
        $cityId = null;
        if (!empty($provinceName)) {
            $city = $this->cityModel->getCityByName($provinceName);
            if ($city) {
                $cityId = $city->id;
            }
        }

        // 3. Cấu hình Phân trang
        $limit = 9; // Ví dụ: 9 khách sạn mỗi trang (để chia đẹp 3 cột)
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;

        // 4. Lấy dữ liệu
        $total_hotels = $this->hotelModel->getHotelCount($cityId);
        $offset = ($current_page - 1) * $limit;

        $data['hotels'] = $this->hotelModel->getHotelsPaginated($limit, $offset, $cityId, $sortBy, $order);
        // 5. Tính toán thông tin phân trang
        $total_pages = (int)ceil($total_hotels / $limit);

        $data['pagination'] = [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_hotels,
            'base_url' => BASE_URL . '/hotel/list'
        ];

        // 6. Gửi các giá trị lọc/sắp xếp sang View (để giữ trạng thái)
        $data['filters'] = [
            'province' => $provinceName,
            'dates' => $dates_raw,
            'sort_by' => $sortBy,
            'order' => $order
        ];

        include_once 'app/views/hotel/list.php';
    }

    /**
     * Hiển thị chi tiết một khách sạn cho người dùng
     */
    public function show($id)
    {
        $data = [];
        $dates_raw = $_GET['dates'] ?? '';
        $check_in = '';
        $check_out = '';

        if (!empty($dates_raw)) {
            $date_parts = explode(' đến ', $dates_raw);
            if (count($date_parts) == 2) {
                $check_in_dt = DateTime::createFromFormat('d/m/Y', $date_parts[0]);
                $check_out_dt = DateTime::createFromFormat('d/m/Y', $date_parts[1]);
                if ($check_in_dt) $check_in = $check_in_dt->format('Y-m-d');
                if ($check_out_dt) $check_out = $check_out_dt->format('Y-m-d');
            }
        }
        $data['check_in'] = $check_in;
        $data['check_out'] = $check_out;

        // Lấy thông tin khách sạn
        $data['hotel'] = $this->hotelModel->getHotelById($id);
        if (!$data['hotel']) {
            http_response_code(404);
            echo "Không tìm thấy khách sạn.";
            return;
        }

        $hotelImages = $this->hotelModel->getHotelImages($id);

        // Logic dự phòng: Nếu không có ảnh trong bảng mới, 
        // hãy dùng ảnh đại diện cũ (hotel.image)
        if (empty($hotelImages) && !empty($data['hotel']->image)) {
            $fallbackImage = new stdClass(); // Tạo đối tượng rỗng
            $fallbackImage->image_path = $data['hotel']->image;
            $fallbackImage->is_thumbnail = true;
            $hotelImages[] = $fallbackImage; // Thêm ảnh cũ vào mảng
        }

        $data['hotelImages'] = $hotelImages; // <<< Truyền mảng ảnh vào $data >>>

        $reviews_per_page = 10;
        $current_review_page = (int)($_GET['review_page'] ?? 1);
        if ($current_review_page < 1) $current_review_page = 1;
        $total_reviews = $this->reviewModel->getReviewCountByHotelId($id);
        $total_review_pages = (int)ceil($total_reviews / $reviews_per_page);
        if ($current_review_page > $total_review_pages && $total_reviews > 0) $current_review_page = $total_review_pages;
        $offset = ($current_review_page - 1) * $reviews_per_page;
        $data['reviews'] = $this->reviewModel->getReviewsByHotelId($id, $reviews_per_page, $offset);
        $data['review_pagination'] = [
            'current_page' => $current_review_page,
            'total_pages'  => $total_review_pages,
            'total_reviews' => $total_reviews
        ];
        $data['roomTypes'] = $this->roomModel->getUniqueRoomTypesByHotelId($id);

        // Tải View và truyền $data
        include_once 'app/views/hotel/show.php';
    }
}

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
        $provinceName = $_GET['province'] ?? '';
        $dates_raw = $_GET['dates'] ?? '';
        $hotels = [];

        if (!empty($provinceName)) {
            $city = $this->cityModel->getCityByName($provinceName);
            if ($city) {
                $hotels = $this->hotelModel->getHotelsByCityId($city->id);
            }
        } else {
            $hotels = $this->hotelModel->getHotels();
        }

        $data['hotels'] = $hotels;
        $data['provinceName'] = $provinceName;
        $data['dates_raw'] = $dates_raw; // Gửi nguyên chuỗi ngày tháng sang view

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
            // Tách chuỗi ngày (ví dụ: "25/10/2025 đến 27/10/2025")
            $date_parts = explode(' đến ', $dates_raw);
            if (count($date_parts) == 2) {
                // Chuyển đổi định dạng "d/m/Y" sang "Y-m-d" mà flatpickr hiểu
                $check_in_dt = DateTime::createFromFormat('d/m/Y', $date_parts[0]);
                $check_out_dt = DateTime::createFromFormat('d/m/Y', $date_parts[1]);

                if ($check_in_dt) $check_in = $check_in_dt->format('Y-m-d');
                if ($check_out_dt) $check_out = $check_out_dt->format('Y-m-d');
            }
        }

        // Gửi ngày tháng đã xử lý sang view
        $data['check_in'] = $check_in;
        $data['check_out'] = $check_out;

        $data['hotel'] = $this->hotelModel->getHotelById($id);
        if (!$data['hotel']) {
            http_response_code(404);
            echo "Không tìm thấy khách sạn.";
            return;
        }

        $data['rooms'] = $this->roomModel->getRoomsByHotelId($id);
        $data['reviews'] = $this->reviewModel->getReviewsByHotelId($id);

        include_once 'app/views/hotel/show.php';
    }
}

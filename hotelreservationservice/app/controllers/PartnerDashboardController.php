<?php
// app/controllers/PartnerDashboardController.php

require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/ReportModel.php';
require_once 'app/models/HotelModel.php'; // Cần để lấy hotel_id
require_once 'app/models/CityModel.php'; // Cần cho hàm getHotels (nếu cần)

class PartnerDashboardController extends BasePartnerController
{
    private $reportModel;
    private $hotelModel;
    private $cityModel;
    private $partnerHotel; // Biến lưu khách sạn của partner

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new ReportModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
        $this->cityModel = new CityModel($this->db);

        // Lấy thông tin khách sạn mà partner này sở hữu
        $partnerId = SessionHelper::getAccountId();
        $this->partnerHotel = $this->hotelModel->getHotelByOwnerId($partnerId);
    }

    public function index()
    {
        // Nếu partner không sở hữu khách sạn nào
        if (!$this->partnerHotel) {
            $data['stats'] = (object)['total_bookings' => 0, 'total_revenue' => 0, 'total_hotels' => 0];
            $data['bookingStatusDistribution'] = [];
            $data['dailyRevenueData'] = [];
            $data['filters'] = [];
            include 'app/views/partner/dashboard/index.php';
            return;
        }

        $hotelId = $this->partnerHotel->id; // ID khách sạn của Partner

        // 1. Lấy tham số lọc (Tương tự Admin, nhưng bỏ $cityId)
        $groupBy = $_GET['group_by'] ?? 'day'; 
        $filterMonth = !empty($_GET['month']) ? (int)$_GET['month'] : null;
        $filterYear = !empty($_GET['year']) ? (int)$_GET['year'] : null;
        
        $queryMonth = $filterMonth;
        $queryYear = $filterYear;

        if ($groupBy === 'year') {
            $queryMonth = null; 
            $queryYear = null;
        } else if ($groupBy === 'month') {
            $queryMonth = null; 
            if ($queryYear === null) $queryYear = date('Y');
        } else { // 'day'
            if ($queryMonth === null) $queryMonth = date('m');
            if ($queryYear === null) $queryYear = date('Y');
        }
        
        // 2. Lấy dữ liệu cho các Card (Truyền $hotelId)
        $data['totalBookings'] = $this->reportModel->getBookingCount(null, $hotelId, $queryMonth, $queryYear);
        $data['totalRevenue'] = $this->reportModel->getTotalRevenue(null, $hotelId, $queryMonth, $queryYear);
        $data['totalHotels'] = 1; // Partner chỉ có 1 khách sạn
        
        // 3. Lấy dữ liệu cho Biểu đồ (Truyền $hotelId)
        $data['dailyRevenueData'] = $this->reportModel->getDailyRevenueData(null, $hotelId, $queryMonth, $queryYear, $groupBy);
        $data['bookingStatusData'] = $this->reportModel->getBookingStatusData(null, $hotelId, $queryMonth, $queryYear);

        // 4. Gửi lại các giá trị filter
        $data['filters'] = [
            'month' => $filterMonth ?? date('m'), 
            'year' => $filterYear ?? date('Y'), 
            'group_by' => $groupBy
        ];

        include 'app/views/partner/dashboard/index.php';
    }
}
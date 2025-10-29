<?php
// app/controllers/AdminDashboardController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/ReportModel.php';
require_once 'app/models/CityModel.php';
require_once 'app/models/HotelModel.php';

class AdminDashboardController extends BaseAdminController
{
    private $reportModel;
    private $cityModel;
    private $hotelModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new ReportModel($this->db);
        $this->cityModel = new CityModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
    }

    /**
     * Hiển thị trang dashboard chính (Đã sửa lỗi)
     */
    public function index()
    {
        // 1. Lấy tham số lọc từ URL (GET)
        $cityId = !empty($_GET['city_id']) ? (int)$_GET['city_id'] : null;
        $hotelId = !empty($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;

        $groupBy = $_GET['group_by'] ?? 'day';

        $filterMonth = !empty($_GET['month']) ? (int)$_GET['month'] : null;
        $filterYear = !empty($_GET['year']) ? (int)$_GET['year'] : null;

        // Xử lý logic lọc dựa trên group_by
        $queryMonth = $filterMonth;
        $queryYear = $filterYear;

        if ($groupBy === 'year') {
            // Xem theo năm: Lấy TẤT CẢ doanh thu, không lọc theo tháng/năm
            $queryMonth = null;
            $queryYear = null;
        } else if ($groupBy === 'month') {
            // Xem theo tháng: Lọc theo NĂM, nhưng lấy TẤT CẢ các tháng
            $queryMonth = null;
            if ($queryYear === null) $queryYear = date('Y');
        } else { // 'day'
            // Xem theo ngày: Lọc theo NĂM và THÁNG
            if ($queryMonth === null) $queryMonth = date('m');
            if ($queryYear === null) $queryYear = date('Y');
        }

        // 2. Lấy dữ liệu cho các Card (đã lọc)
        // Lọc theo TẤT CẢ filters:
        $data['totalBookings'] = $this->reportModel->getBookingCount($cityId, $hotelId, $queryMonth, $queryYear);
        $data['totalRevenue'] = $this->reportModel->getTotalRevenue($cityId, $hotelId, $queryMonth, $queryYear);

        // <<< SỬA LỖI: THÊM LẠI CÁC HÀM GỌI ĐẾM THÀNH VIÊN VÀ KHÁCH SẠN >>>
        // Lọc CHỈ theo $cityId:
        $data['totalHotels'] = $this->reportModel->getHotelCount($cityId);
        // KHÔNG lọc:
        $data['totalMembers'] = $this->reportModel->getMemberCount();
        // <<< KẾT THÚC SỬA LỖI >>>

        // 3. Lấy dữ liệu cho Biểu đồ
        $data['dailyRevenueData'] = $this->reportModel->getDailyRevenueData($cityId, $hotelId, $queryMonth, $queryYear, $groupBy);
        $data['bookingStatusData'] = $this->reportModel->getBookingStatusData($cityId, $hotelId, $queryMonth, $queryYear);

        // 4. Lấy dữ liệu cho các ô Filter Dropdown
        $data['allCities'] = $this->cityModel->getCities(null, null);
        $data['allHotels'] = $this->hotelModel->getHotels(null, null, null);

        // 5. Gửi lại các giá trị filter GỐC (để hiển thị trên dropdown)
        $data['filters'] = [
            'city_id' => $cityId,
            'hotel_id' => $hotelId,
            'month' => $filterMonth ?? date('m'), // Gửi giá trị gốc
            'year' => $filterYear ?? date('Y'),   // Gửi giá trị gốc
            'group_by' => $groupBy
        ];

        include 'app/views/admin/dashboard/index.php';
    }

    /**
     * Cung cấp dữ liệu cho biểu đồ doanh thu qua AJAX (Hàm này có thể không cần thiết nữa)
     */
    public function getRevenueChartData()
    {
        header('Content-Type: application/json');

        // Lấy filter từ GET (Logic mới)
        $cityId = !empty($_GET['city_id']) ? (int)$_GET['city_id'] : null;
        $hotelId = !empty($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
        $groupBy = $_GET['group_by'] ?? 'day';
        $month = !empty($_GET['month']) ? (int)$_GET['month'] : date('m');
        $year = !empty($_GET['year']) ? (int)$_GET['year'] : date('Y');

        // Logic xử lý $queryMonth/$queryYear (tương tự hàm index)
        $queryMonth = $month;
        $queryYear = $year;
        if ($groupBy === 'year') {
            $queryMonth = null;
            $queryYear = null;
        }
        if ($groupBy === 'month') {
            $queryMonth = null;
        }

        $revenueData = $this->reportModel->getDailyRevenueData($cityId, $hotelId, $queryMonth, $queryYear, $groupBy);

        echo json_encode($revenueData);
        exit;
    }
}

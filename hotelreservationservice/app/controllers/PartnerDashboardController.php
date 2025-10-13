<?php
// app/controllers/PartnerDashboardController.php

// Kế thừa từ BasePartnerController để tự động bảo vệ
require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/ReportModel.php';

class PartnerDashboardController extends BasePartnerController
{
    private $reportModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new ReportModel($this->db);
    }

    /**
     * Hiển thị trang dashboard chính cho Partner
     */
    public function index()
    {
        $data = [];
        $partnerId = SessionHelper::getAccountId(); // Lấy ID của partner đang đăng nhập

        // 1. Lấy các chỉ số thống kê CHỈ cho khách sạn của partner này
        $data['stats'] = $this->reportModel->getOverallStats($partnerId);

        // 2. Lấy dữ liệu biểu đồ tròn CHỈ cho khách sạn của partner này
        $data['bookingStatusDistribution'] = $this->reportModel->getBookingStatusDistribution($partnerId);

        // 3. Tải view và truyền dữ liệu vào
        include 'app/views/partner/dashboard/index.php'; // Chú ý đường dẫn view mới
    }

    /**
     * Cung cấp dữ liệu doanh thu qua AJAX cho Partner
     * Route: /partner/dashboard/getRevenueChartData
     */
    public function getRevenueChartData()
    {
        header('Content-Type: application/json');
        $partnerId = SessionHelper::getAccountId(); // Lấy ID của partner đang đăng nhập
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        $revenueData = $this->reportModel->getDailyRevenueForMonth($year, $month, $partnerId);

        echo json_encode($revenueData);
        exit;
    }
}

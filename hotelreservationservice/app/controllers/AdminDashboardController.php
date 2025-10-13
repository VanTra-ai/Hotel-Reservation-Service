<?php
// app/controllers/AdminDashboardController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/ReportModel.php'; // Model mới chúng ta sẽ tạo ở bước sau

class AdminDashboardController extends BaseAdminController
{
    private $reportModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new ReportModel($this->db);
    }

    /**
     * Hiển thị trang dashboard chính
     */
    public function index()
    {
        $data = []; // Tạo mảng để chứa tất cả dữ liệu gửi đến view

        // 1. Lấy các chỉ số cho các thẻ thống kê
        $data['stats'] = $this->reportModel->getOverallStats();
        
        // 2. Lấy dữ liệu cho biểu đồ tròn (Trạng thái đơn hàng)
        $data['bookingStatusDistribution'] = $this->reportModel->getBookingStatusDistribution();
        
        // 3. Tải view và truyền mảng $data vào
        include 'app/views/admin/dashboard/index.php';
    }

    /**
     * Cung cấp dữ liệu cho biểu đồ doanh thu qua AJAX
     * Route: /admin/dashboard/getRevenueChartData
     */
    public function getRevenueChartData()
    {
        header('Content-Type: application/json');
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        
        $revenueData = $this->reportModel->getDailyRevenueForMonth($year, $month);
        
        echo json_encode($revenueData);
        exit;
    }
}
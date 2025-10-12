<?php
// app/controllers/BaseAdminController.php

require_once 'app/config/database.php';
require_once 'app/helpers/SessionHelper.php';

class BaseAdminController
{

    protected $db; // `protected` để các class con (AdminHotelController, etc.) có thể truy cập

    public function __construct()
    {
        // Bắt đầu session nếu chưa có
        SessionHelper::startSession();

        // Dòng quan trọng nhất: Yêu cầu quyền admin!
        // Nếu người dùng không phải là admin, hàm này sẽ tự động dừng và chuyển hướng họ về trang đăng nhập.
        SessionHelper::requireAdmin();

        // Nếu qua được vòng kiểm tra, tiếp tục kết nối database
        $this->db = (new Database())->getConnection();
    }
}

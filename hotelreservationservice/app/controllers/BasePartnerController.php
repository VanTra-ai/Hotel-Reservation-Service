<?php
// app/controllers/BasePartnerController.php
require_once 'app/config/database.php';
require_once 'app/helpers/SessionHelper.php';

class BasePartnerController
{
    protected $db;
    public function __construct()
    {
        SessionHelper::startSession();
        // Yêu cầu phải là Partner để truy cập
        if (!SessionHelper::isPartner()) {
            // Có thể chuyển hướng về trang chủ hoặc trang đăng nhập
            header('Location: ' . BASE_URL . '/account/login');
            exit();
        }
        $this->db = (new Database())->getConnection();
    }
}

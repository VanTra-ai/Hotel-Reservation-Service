<?php
// Tải các file cần thiết
require_once 'app/config/database.php';
require_once 'app/models/CityModel.php';

// app/controllers/HomeController.php

class HomeController
{
    private $CityModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->CityModel = new CityModel($this->db);
    }

    public function index()
    {
        // Lấy danh sách các tỉnh thành từ model
        $provinces = $this->CityModel->getCities();

        // Tải view và truyền biến $provinces
        include_once 'app/views/home/index.php';
    }
}

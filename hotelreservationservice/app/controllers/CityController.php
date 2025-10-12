<?php
// app/controllers/CityController.php

require_once('app/config/database.php');
require_once('app/models/CityModel.php');

class CityController
{
    private $cityModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->cityModel = new CityModel($this->db);
    }

    /**
     * API trả về danh sách tỉnh/thành dạng JSON cho autocomplete ở trang chủ
     */
    public function getCitiesJson()
    {
        try {
            $cities = $this->cityModel->getCities();
            // Chỉ trả về các trường cần thiết
            $data = array_map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                ];
            }, $cities);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Không thể tải danh sách tỉnh thành']);
        }
    }
}

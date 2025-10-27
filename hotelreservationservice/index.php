<?php
define('BASE_URL', '/Hotel-Reservation-Service/hotelreservationservice');
// Bắt đầu session
session_start();

// Tải các file cần thiết cho toàn bộ ứng dụng
require_once 'app/config/database.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/config/constants.php';

// Tải tất cả các model và controller
require_once 'app/models/AccountModel.php';
require_once 'app/models/CityModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/models/RoomModel.php';

// Phân tích URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerName = 'HomeController';
$action = 'index';
$params = [];
$controllerPathPrefix = 'app/controllers/';

// Kiểm tra route ADMIN
if (isset($url[0]) && $url[0] == 'admin' && isset($url[1])) {
    $controllerName = 'Admin' . ucfirst($url[1]) . 'Controller';
    $action = isset($url[2]) ? $url[2] : 'index';
    $params = array_slice($url, 3);
}
// Kiểm tra route PARTNER DASHBOARD
elseif (isset($url[0]) && $url[0] == 'partner' && isset($url[1])) {
    $controllerName = 'Partner' . ucfirst($url[1]) . 'Controller';
    $action = isset($url[2]) ? $url[2] : 'index';
    $params = array_slice($url, 3);
}
// Kiểm tra các route CÔNG KHAI khác
elseif (isset($url[0]) && !empty($url[0])) {
    $controllerName = ucfirst($url[0]) . 'Controller';
    $action = isset($url[1]) ? $url[1] : 'index';
    $params = array_slice($url, 2);
}

// Tải và chạy controller
$controllerFile = $controllerPathPrefix . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $params);
        } else {
            die("Action '{$action}' not found in controller '{$controllerName}'");
        }
    } else {
        die("Class '{$controllerName}' not found in file '{$controllerFile}'");
    }
} else {
    // Controller mặc định nếu không tìm thấy, hoặc trang 404
    require_once 'app/controllers/HomeController.php';
    $controller = new HomeController();
    $controller->index();
}

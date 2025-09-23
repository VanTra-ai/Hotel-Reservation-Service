<?php
// Bắt đầu session
session_start();

// Tải các file cần thiết cho toàn bộ ứng dụng
require_once 'app/config/database.php';
require_once 'app/helpers/SessionHelper.php';

// Tải tất cả các model và controller
require_once 'app/models/AccountModel.php';
require_once 'app/models/CityModel.php';
require_once 'app/models/HotelModel.php';
require_once 'app/models/RoomModel.php';
require_once 'app/controllers/AccountController.php';
require_once 'app/controllers/CityController.php';
require_once 'app/controllers/DefaultController.php';
require_once 'app/controllers/HotelController.php';
require_once 'app/controllers/HomeController.php';
require_once 'app/controllers/BookingController.php';
require_once 'app/controllers/RoomController.php';

// Phân tích URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Xác định Controller và Action
$controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) . 'Controller' : 'HomeController';
$action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';
$params = array_slice($url, 2);

// Định nghĩa đường dẫn file controller
$controllerPath = 'app/controllers/' . $controllerName . '.php';

// Kiểm tra và tải controller
if (file_exists($controllerPath)) {
    // Tải và khởi tạo controller tương ứng
    require_once $controllerPath;
    $controller = new $controllerName();
} else {
    // Nếu controller không tồn tại, hiển thị lỗi 404
    http_response_code(404);
    die('404 Not Found: Controller not found');
}

// Kiểm tra và gọi action
if (method_exists($controller, $action)) {
    // Sử dụng Reflection để lấy thông tin về phương thức
    $reflectionMethod = new ReflectionMethod($controller, $action);
    $numberOfRequiredParameters = $reflectionMethod->getNumberOfRequiredParameters();

    // So sánh số lượng tham số có sẵn với số lượng yêu cầu
    if (count($params) >= $numberOfRequiredParameters) {
        // Gọi phương thức với các tham số
        call_user_func_array([$controller, $action], $params);
    } else {
        // Nếu thiếu tham số
        http_response_code(404);
        die('404 Not Found: Missing parameters');
    }
} else {
    // Nếu action không tồn tại
    http_response_code(404);
    die('404 Not Found: Action not found');
}

<?php
// Nạp SessionHelper và khởi động session để có thể truy cập $_SESSION
require_once 'app/helpers/SessionHelper.php';

// Tính toán tổng số lượng khách sạn trong giỏ hàng
$cartItemCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        // Đảm bảo item là một mảng và có key 'quantity'
        if (is_array($item) && isset($item['quantity'])) {
            $cartItemCount += $item['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reservation Service</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="/hotelreservationservice/public/css/main.css" rel="stylesheet">
    <script src="/hotelreservationservice/public/js/search_form.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-subtle border-bottom shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-dark" href="/hotelreservationservice/home/">
                <i class="fas fa-hotel me-2"></i>Hotel Reservation Service
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/hotelreservationservice/Hotel/">Danh sách khách sạn</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/hotelreservationservice/Hotel/add">Thêm khách sạn</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/hotelreservationservice/Room/list">Danh sách phòng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/hotelreservationservice/Room/add">Thêm phòng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/hotelreservationservice/City/list">Danh sách tỉnh thành</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/hotelreservationservice/City/add">Thêm tỉnh thành</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if (!SessionHelper::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="/hotelreservationservice/account/login">Đăng nhập</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Đăng xuất</a>
                        </li>
                        <form id="logout-form" action="/hotelreservationservice/account/logout" method="POST" style="display: none;">
                            <input type="hidden" name="action" value="logout">
                        </form>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
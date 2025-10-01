<?php
require_once 'app/helpers/SessionHelper.php';
SessionHelper::startSession();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reservation Service</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/hotelreservationservice/public/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-subtle border-bottom shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-dark" href="/hotelreservationservice/home/">
            <i class="fas fa-hotel me-2"></i>Hotel Reservation Service
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Menu bên trái -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link text-dark" href="/hotelreservationservice/hotel/">Danh sách khách sạn</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="/hotelreservationservice/hotel/add">Thêm khách sạn</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="/hotelreservationservice/room/list">Danh sách phòng</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="/hotelreservationservice/room/add">Thêm phòng</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="/hotelreservationservice/city/list">Danh sách tỉnh thành</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="/hotelreservationservice/city/add"> tỉnh thành</a></li>

                <?php if (SessionHelper::isLoggedIn()): ?>
                    <?php if (SessionHelper::isUser()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="/hotelreservationservice/booking/history">
                                Lịch sử đặt phòng
                            </a>
                        </li>
                    <?php elseif (SessionHelper::isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="/hotelreservationservice/admin/booking">
                                Quản lý đặt phòng
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Menu bên phải -->
            <ul class="navbar-nav ms-auto">
                <?php if (!SessionHelper::isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/hotelreservationservice/account/login">Đăng nhập</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars(SessionHelper::getUsername()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Đăng xuất
                                </a>
                                <form id="logout-form" action="/hotelreservationservice/account/logout" method="POST" style="display:none;">
                                    <input type="hidden" name="action" value="logout">
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

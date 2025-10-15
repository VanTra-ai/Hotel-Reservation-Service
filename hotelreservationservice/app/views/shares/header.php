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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/main.css">
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-subtle border-bottom shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-dark" href="<?= BASE_URL ?>/home">
                <i class="fas fa-hotel me-2"></i>Hotel Reservation Service
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= BASE_URL ?>/hotel/list">Khách sạn</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= BASE_URL ?>/room/list">Phòng</a></li>

                    <li class="nav-item">
                        <a class="nav-link text-info fw-bold" href="<?= BASE_URL ?>/ai">
                            <i class="fas fa-magic me-1"></i>AI Playground
                        </a>
                    </li>
                    <?php if (SessionHelper::isPartner()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-success fw-bold" href="#" id="partnerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-store me-1"></i>Kênh Đối tác
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="partnerDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/partner/dashboard">
                                        <i class="fas fa-tachometer-alt me-2"></i>Tổng quan
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/partner/booking">
                                        <i class="fas fa-book me-2"></i>Quản lý Booking
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/partner/hotel">
                                        <i class="fas fa-hotel me-2"></i>Quản lý Khách sạn
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/partner/room">
                                        <i class="fas fa-bed me-2"></i>Quản lý Phòng
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (SessionHelper::isAdmin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-primary fw-bold" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cogs me-1"></i>Quản trị
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard">Tổng quan</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/account">Quản lý Thành viên</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/hotel">Quản lý Khách sạn</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/room">Quản lý Phòng</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/city">Quản lý Thành phố</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/booking">Quản lý Đặt phòng</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if (!SessionHelper::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= BASE_URL ?>/account/login">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark fw-semibold" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= htmlspecialchars(SessionHelper::getUsername()) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userDropdown">
                                <?php if (SessionHelper::isUser()): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/booking/history">
                                            <i class="fas fa-clock-rotate-left me-2 text-primary"></i>Lịch sử đặt phòng
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </a>
                                    <form id="logout-form" action="<?= BASE_URL ?>/account/logout" method="POST" style="display:none;"></form>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
<?php
// app/config/constants.php

/**
 * ==================================
 * USER ROLES
 * ==================================
 */
define('ROLE_ADMIN', 'admin');
define('ROLE_PARTNER', 'partner');
define('ROLE_USER', 'user');

// Mảng chứa các vai trò hợp lệ (dùng cho validation)
define('ALLOWED_ROLES', [
    ROLE_ADMIN,
    ROLE_PARTNER,
    ROLE_USER
]);


/**
 * ==================================
 * BOOKING STATUSES
 * ==================================
 */
define('BOOKING_STATUS_PENDING', 'pending');
define('BOOKING_STATUS_CONFIRMED', 'confirmed');
define('BOOKING_STATUS_CANCELLED', 'cancelled');
define('BOOKING_STATUS_CHECKED_IN', 'checked_in');
define('BOOKING_STATUS_CHECKED_OUT', 'checked_out');

// Mảng chứa các trạng thái booking hợp lệ (dùng cho validation)
define('ALLOWED_BOOKING_STATUSES', [
    BOOKING_STATUS_PENDING,
    BOOKING_STATUS_CONFIRMED,
    BOOKING_STATUS_CANCELLED,
    BOOKING_STATUS_CHECKED_IN,
    BOOKING_STATUS_CHECKED_OUT
]);


/**
 * ==================================
 * ROOM TYPES
 * ==================================
 * Sử dụng mảng thay vì nhiều defines cho dễ quản lý và lặp trong view
 */
define('ALLOWED_ROOM_TYPES', [
    'Phòng Tiêu Chuẩn Giường Đôi',
    'Phòng Superior Giường Đôi',
    'Phòng Giường Đôi',
    'Phòng Deluxe Giường Đôi Có Ban Công',
    'Phòng Deluxe Giường Đôi',
    'Phòng Giường Đôi Có Ban Công',
    'Phòng Superior Giường Đôi Có Ban Công',
    'Phòng Gia Đình',
    'Phòng Deluxe Gia đình',
    'Phòng Superior Giường Đôi/2 Giường Đơn',
]);


/**
 * ==================================
 * CÁC HẰNG SỐ KHÁC (NẾU CẦN)
 * ==================================
 */
// Ví dụ: Số lượng item trên mỗi trang phân trang
// define('ITEMS_PER_PAGE', 10);

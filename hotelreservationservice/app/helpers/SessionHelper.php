<?php
class SessionHelper
{
    private static function ensureSessionStarted()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Phương thức này có thể được gọi để khởi tạo session ở đầu mọi request
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn()
    {
        self::startSession();
        return isset($_SESSION['username']);
    }

    public static function isAdmin()
    {
        self::startSession();
        return self::isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function isUser()
    {
        self::ensureSessionStarted();
        // Kiểm tra đúng tên biến session là 'role'
        return isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'user';
    }

    public static function getUsername()
    {
        self::ensureSessionStarted();
        return $_SESSION['username'] ?? null;
    }

    public static function getUserRole()
    {
        self::ensureSessionStarted();
        return $_SESSION['role'] ?? null; // Sửa tên biến thành 'role'
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: /Hotel-Reservation-Service/hotelreservationservice/account/login');
            exit;
        }
    }

    public static function requireAdmin()
    {
        if (!self::isAdmin()) {
            http_response_code(403);
            echo "<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập vào trang này.</p>";
            exit;
        }
    }

    public static function destroySession()
    {
        self::startSession();
        session_unset();
        session_destroy();
    }

    // Thêm getter cho fullname
    public static function getFullname()
    {
        self::startSession();
        return $_SESSION['fullname'] ?? null;
    }

    // Getter cho account_id
    public static function getAccountId()
    {
        self::startSession();
        return $_SESSION['account_id'] ?? null;
    }
    public static function isPartner()
    {
        self::startSession();
        return self::isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'partner';
    }
}

<?php
// app/controllers/AccountController.php

require_once('app/config/database.php');
require_once('app/models/AccountModel.php');
require_once('app/helpers/SessionHelper.php');

class AccountController
{
    private AccountModel $accountModel;
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        SessionHelper::startSession();
    }

    // Hiển thị trang đăng ký
    public function register(): void
    {
        $errors = [];
        include_once 'app/views/account/register.php';
    }

    // Hiển thị trang login
    public function login(): void
    {
        $error = '';
        include_once 'app/views/account/login.php';
    }

    // Xử lý lưu tài khoản mới
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Hotel-Reservation-Service/hotelreservationservice/account/register');
            exit;
        }

        // Lấy & sanitize input
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmpassword'] ?? '';
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleFromForm = $_POST['role'] ?? null;

        $errors = [];

        // Validation cơ bản
        if ($username === '') $errors['username'] = "Vui lòng nhập username!";
        if ($fullname === '') $errors['fullname'] = "Vui lòng nhập họ và tên!";
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Vui lòng nhập email hợp lệ!";
        if ($password === '') $errors['password'] = "Vui lòng nhập password!";
        if ($password !== $confirmPassword) $errors['confirmPass'] = "Mật khẩu và xác nhận chưa khớp!";
        if (strlen($username) > 100) $errors['username_len'] = "Username quá dài (tối đa 100 ký tự).";
        if (strlen($fullname) > 255) $errors['fullname_len'] = "Họ tên quá dài.";

        // Ngăn role escalation: chỉ admin hiện tại mới có thể tạo admin
        $role = ROLE_USER; // Mặc định là user
        if (SessionHelper::isLoggedIn() && SessionHelper::isAdmin() && $roleFromForm) {
            // Sử dụng mảng hằng số ALLOWED_ROLES
            if (in_array($roleFromForm, ALLOWED_ROLES, true)) {
                $role = $roleFromForm;
            }
        }

        // Kiểm tra tồn tại username/email trước (thêm check để trả nhanh)
        if ($username !== '' && $this->accountModel->getAccountByUsername($username)) {
            $errors['account'] = "Tài khoản này đã được đăng ký!";
        }
        if ($email !== '' && $this->accountModel->getAccountByEmail($email)) {
            $errors['email_taken'] = "Email này đã được sử dụng.";
        }

        // Nếu có lỗi validation, render form lại với $errors và dữ liệu hiện có
        if (count($errors) > 0) {
            // truyền các biến để view hiển thị lại
            $errors = $errors;
            $username = htmlspecialchars($username);
            $fullName = htmlspecialchars($fullname);
            $role = htmlspecialchars($role);
            include_once 'app/views/account/register.php';
            return;
        }

        // Thực hiện lưu — bắt PDOException để xử lý duplicate key
        try {
            $result = $this->accountModel->save(
                $username,
                $fullname,
                $email,
                $password,
                $role
            );

            if ($result) {
                // Sau đăng ký, redirect tới login
                header('Location: /Hotel-Reservation-Service/hotelreservationservice/account/login');
                exit;
            } else {
                $errors['db_error'] = "Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại.";
                // truyền lại biến cho view
                $username = htmlspecialchars($username);
                $fullName = htmlspecialchars($fullname);
                $role = htmlspecialchars($role);
                include_once 'app/views/account/register.php';
                return;
            }
        } catch (PDOException $e) {
            // Chuẩn hoá các thông báo duplicate mà model ném: 'duplicate:username' hoặc 'duplicate:email'
            $msg = $e->getMessage();
            if (stripos($msg, 'duplicate:username') !== false || stripos($msg, 'duplicate') !== false && stripos($msg, 'username') !== false) {
                $errors['account'] = "Username đã tồn tại. Vui lòng chọn tên khác.";
            } elseif (stripos($msg, 'duplicate:email') !== false || stripos($msg, 'duplicate') !== false && stripos($msg, 'email') !== false) {
                $errors['email_taken'] = "Email đã được sử dụng. Nếu bạn quên mật khẩu hãy dùng chức năng quên mật khẩu.";
            } else {
                // không rõ lỗi => log và thông báo chung
                // nếu bạn có hệ thống log, log $e->getMessage() ở đây
                $errors['db_error'] = "Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.";
            }

            // giữ lại dữ liệu người dùng để render form
            $username = htmlspecialchars($username);
            $fullName = htmlspecialchars($fullname);
            $role = htmlspecialchars($role);
            include_once 'app/views/account/register.php';
            return;
        }
    }



    // Đăng xuất
    public function logout(): void
    {
        SessionHelper::destroySession();
        header('Location: /Hotel-Reservation-Service/hotelreservationservice/home');
        exit;
    }

    // Xử lý đăng nhập
    public function checkLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Hotel-Reservation-Service/hotelreservationservice/account/login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $account = $this->accountModel->getAccountByUsername($username);
        $error = "";

        if ($account && password_verify($password, $account->password)) {
            // Hardening session
            SessionHelper::startSession();
            session_regenerate_id(true);

            $_SESSION['account_id'] = $account->id;
            $_SESSION['username'] = $account->username;
            $_SESSION['fullname'] = $account->fullname;
            $_SESSION['role'] = $account->role;

            header('Location: /Hotel-Reservation-Service/hotelreservationservice/home');
            exit;
        } else {
            $error = $account ? "Mật khẩu không đúng!" : "Không tìm thấy tài khoản!";
            include_once 'app/views/account/login.php';
            exit;
        }
    }

    // Hiển thị form đổi mật khẩu
    public function changePassword(): void
    {
        SessionHelper::requireLogin();
        $errors = [];
        $success_message = '';
        include_once 'app/views/account/change_password.php';
    }

    // Xử lý đổi mật khẩu
    public function processChangePassword(): void
    {
        SessionHelper::requireLogin();
        $errors = [];
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = SessionHelper::getUsername();
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_new_password = $_POST['confirm_new_password'] ?? '';

            $account = $this->accountModel->getAccountByUsername($username);

            if ($account && password_verify($current_password, $account->password)) {
                if ($new_password !== $confirm_new_password) {
                    $errors['confirm_new_password'] = 'Mật khẩu mới và xác nhận không khớp.';
                } elseif (empty($new_password)) {
                    $errors['new_password'] = 'Mật khẩu mới không được để trống.';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $result = $this->accountModel->updatePassword($username, $hashed_password);

                    if ($result) {
                        $success_message = 'Mật khẩu của bạn đã được thay đổi thành công.';
                    } else {
                        $errors['db_error'] = 'Đã xảy ra lỗi khi cập nhật mật khẩu. Vui lòng thử lại.';
                    }
                }
            } else {
                $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
            }
        }
        include_once 'app/views/account/change_password.php';
    }
}

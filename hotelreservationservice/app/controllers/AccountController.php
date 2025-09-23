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
    }

    public function register(): void
    {
        $errors = [];
        include_once 'app/views/account/register.php';
    }

    public function login(): void
    {
        $error = '';
        include_once 'app/views/account/login.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmpassword'] ?? '';
            $fullname = $_POST['fullname'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $errors = [];

            if (empty($username)) {
                $errors['username'] = "Vui lòng nhập username!";
            }
            if (empty($password)) {
                $errors['password'] = "Vui lòng nhập password!";
            }
            if (empty($fullname)) {
                $errors['fullname'] = "Vui lòng nhập họ và tên!";
            }
            if ($password !== $confirmPassword) {
                $errors['confirmPass'] = "Mật khẩu và xác nhận chưa khớp!";
            }
            if (!in_array($role, ['admin', 'user'])) {
                $role = 'user';
            }
            if ($this->accountModel->getAccountByUsername($username)) {
                $errors['account'] = "Tài khoản này đã được đăng ký!";
            }

            if (count($errors) > 0) {
                include_once 'app/views/account/register.php';
            } else {
                $result = $this->accountModel->save(
                    $username,
                    $fullname,
                    $password,
                    $role
                );
                if ($result) {
                    header('Location: /hotelreservationservice/account/login');
                    exit;
                } else {
                    $errors['db_error'] = "Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại.";
                    include_once 'app/views/account/register.php';
                }
            }
        }
    }

    public function logout(): void
    {
        SessionHelper::destroySession();
        header('Location: /hotelreservationservice/home');
        exit;
    }

    public function checkLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Sửa lại dòng này để tránh cảnh báo
            $account = $this->accountModel->getAccountByUsername($username);
            $error = "";

            if ($account && password_verify($password, $account->password)) {
                SessionHelper::startSession();
                $_SESSION['account_id'] = $account->id;
                $_SESSION['username'] = $account->username;
                $_SESSION['fullname'] = $account->fullname;
                $_SESSION['role'] = $account->role;
                header('Location: /hotelreservationservice/home');
                exit;
            } else {
                $error = $account ? "Mật khẩu không đúng!" : "Không tìm thấy tài khoản!";
                include_once 'app/views/account/login.php';
                exit;
            }
        }
    }

    public function changePassword(): void
    {
        SessionHelper::requireLogin();
        $errors = [];
        $success_message = '';
        include_once 'app/views/account/change_password.php';
    }

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

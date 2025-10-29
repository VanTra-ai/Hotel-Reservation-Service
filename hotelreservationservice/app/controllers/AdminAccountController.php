<?php
// app/controllers/AdminAccountController.php

require_once 'app/controllers/BaseAdminController.php';
require_once 'app/models/AccountModel.php';
require_once 'app/models/HotelModel.php';

class AdminAccountController extends BaseAdminController
{
    private $accountModel;
    private $hotelModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new AccountModel($this->db);
        $this->hotelModel = new HotelModel($this->db);
    }

    /**
     * Hiển thị danh sách tất cả tài khoản
     */
    public function index()
    {
        $searchTerm = trim($_GET['search'] ?? '');

        // 1. Cấu hình Phân trang
        $limit = 10;
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;

        // 2. Lấy dữ liệu
        $total_accounts = $this->accountModel->getAccountCount($searchTerm); // <<< TRUYỀN $searchTerm
        $data['accounts'] = $this->accountModel->getAllAccounts($limit, $offset, $searchTerm); // <<< TRUYỀN $searchTerm

        // 3. Tính toán thông tin phân trang
        $total_pages = (int)ceil($total_accounts / $limit);

        $data['searchTerm'] = $searchTerm; // <<< TRUYỀN $searchTerm SANG VIEW

        $data['pagination'] = [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_items' => $total_accounts,
            'base_url' => BASE_URL . '/admin/account/index'
        ];

        include 'app/views/admin/accounts/list.php';
    }

    /**
     * Hiển thị form chỉnh sửa, tải thêm danh sách khách sạn VÀ khách sạn hiện tại của partner
     */
    public function edit($id)
    {
        $accountId = (int)$id;
        $data['account'] = $this->accountModel->getAccountById($accountId);
        if (!$data['account']) {
            header('Location: ' . BASE_URL . '/admin/account');
            exit;
        }

        // Lấy danh sách khách sạn chưa có chủ
        $data['unassigned_hotels'] = $this->hotelModel->getUnassignedHotels();

        // Lấy khách sạn mà partner này đang sở hữu (nếu có)
        $data['current_hotel'] = null;
        if ($data['account']->role === 'partner') {
            $data['current_hotel'] = $this->hotelModel->getHotelByOwnerId($accountId);
        }

        include 'app/views/admin/accounts/edit.php';
    }

    /**
     * Xử lý việc cập nhật thông tin và gán/bỏ gán khách sạn
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountId = (int)$id;
            $fullname = $_POST['fullname'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? 'user';

            $country = $_POST['country'] ?? null;

            $hotel_id = !empty($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : null;

            if (!in_array($role, ['admin', 'user', 'partner'])) {
                $role = 'user';
            }

            // Cập nhật thông tin tài khoản (fullname, email, role, country)
            $accountUpdated = $this->accountModel->updateAccountInfo($accountId, $fullname, $email, $role, $country);

            if ($accountUpdated) {
                $this->hotelModel->unassignOwnerFromAllHotels($accountId);

                if ($role === 'partner' && $hotel_id !== null) {
                    $this->hotelModel->assignOwnerToHotel($hotel_id, $accountId);
                }

                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật tài khoản thành công!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Cập nhật tài khoản thất bại. Vui lòng thử lại.'];
            }

            header('Location: ' . BASE_URL . '/admin/account');
            exit;
        }
    }

    /**
     * Xóa một tài khoản
     */
    public function delete($id)
    {
        $accountIdToDelete = (int)$id;
        $currentAdminId = SessionHelper::getAccountId();

        if ($accountIdToDelete === $currentAdminId) {
            header('Location: ' . BASE_URL . '/admin/account?error=self_delete');
            exit;
        }

        $this->accountModel->deleteAccount($accountIdToDelete);
        header('Location: ' . BASE_URL . '/admin/account?success=deleted');
        exit;
    }
}

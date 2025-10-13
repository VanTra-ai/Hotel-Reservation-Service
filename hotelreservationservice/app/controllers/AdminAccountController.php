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
        $data['accounts'] = $this->accountModel->getAllAccounts();
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
            // Lấy hotel_id, nếu không có thì là null
            $hotel_id = !empty($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : null;

            if (!in_array($role, ['admin', 'user', 'partner'])) {
                $role = 'user';
            }

            // Cập nhật thông tin tài khoản trước (fullname, email, role)
            $accountUpdated = $this->accountModel->updateAccountInfo($accountId, $fullname, $email, $role);

            if ($accountUpdated) {
                // Luôn luôn dọn dẹp các gán cũ của tài khoản này
                $this->hotelModel->unassignOwnerFromAllHotels($accountId);

                // Nếu vai trò mới là 'partner' VÀ admin đã chọn một khách sạn hợp lệ
                if ($role === 'partner' && $hotel_id !== null) {
                    // Thì thực hiện gán mới
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

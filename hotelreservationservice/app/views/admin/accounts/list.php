<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Quản lý Thành viên</h2>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_message']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']);
        ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Quản lý Khách sạn</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['accounts'] as $account): ?>
                    <tr>
                        <td><?= $account->id ?></td>
                        <td><?= htmlspecialchars($account->username) ?></td>
                        <td><?= htmlspecialchars($account->fullname) ?></td>
                        <td><?= htmlspecialchars($account->email) ?></td>
                        <td>
                            <?php
                            $roleClass = 'bg-secondary';
                            if ($account->role == 'admin') $roleClass = 'bg-danger';
                            if ($account->role == 'partner') $roleClass = 'bg-success';
                            ?>
                            <span class="badge <?= $roleClass ?>"><?= ucfirst($account->role) ?></span>
                        </td>
                        <td>
                            <?php if ($account->role == 'partner' && !empty($account->hotel_name)): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($account->hotel_name) ?></span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($account->created_at)) ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="<?= BASE_URL ?>/admin/account/edit/<?= $account->id ?>" class="btn btn-sm btn-outline-warning" title="Sửa vai trò">
                                    <i class="fas fa-user-edit"></i>
                                </a>
                                <?php if ($account->id != SessionHelper::getAccountId() && $account->id != 1): ?>
                                    <a href="<?= BASE_URL ?>/admin/account/delete/<?= $account->id ?>" class="btn btn-sm btn-outline-danger" title="Xóa tài khoản" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này? Hành động này không thể hoàn tác!');">
                                        <i class="fas fa-user-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
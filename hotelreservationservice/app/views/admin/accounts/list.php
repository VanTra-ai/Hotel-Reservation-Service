<?php include 'app/views/shares/header.php';
//app/views/admin/accounts/list.php
$accounts = $data['accounts'] ?? [];
$pagination = $data['pagination'] ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$searchTerm = $data['searchTerm'] ?? '';
// Helper để tạo URL giữ lại tất cả các tham số GET hiện tại (bao gồm cả 'search') trừ 'page'
function buildUrlWithPage($page, $currentParams)
{
    $params = array_merge($currentParams, ['page' => $page]);
    // Loại bỏ tham số 'review_page' nếu có
    unset($params['review_page']);
    // Nếu không có search, bỏ search khỏi URL
    if (empty($params['search'])) unset($params['search']);

    return BASE_URL . '/admin/account?' . http_build_query($params);
}

// Lấy tham số GET hiện tại (trừ page) để tái sử dụng trong phân trang
$currentGetParams = $_GET;
unset($currentGetParams['page']);
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Quản lý Thành viên</h2>
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="<?= BASE_URL ?>/admin/account" method="GET" class="d-flex">
                <input type="search" name="search" class="form-control me-2"
                    placeholder="Tìm theo Tên, Email, Username..."
                    value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-secondary" type="submit">Tìm</button>
            </form>
        </div>
        <?php if (!empty($searchTerm)): ?>
            <div class="col-md-6 text-muted pt-2">
                Kết quả tìm kiếm cho: <strong><?= htmlspecialchars($searchTerm) ?></strong>
            </div>
        <?php endif; ?>
    </div>

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
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
        <nav aria-label="Phân trang thành viên" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                $currentPage = $pagination['current_page'];
                $totalPages = $pagination['total_pages'];
                $window = 2;
                ?>

                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <?php $prevParams = array_merge($_GET, ['page' => $currentPage - 1]); ?>
                    <a class="page-link" href="?<?= http_build_query($prevParams) ?>">Trước</a>
                </li>

                <?php $pageParams = array_merge($_GET, ['page' => 1]); ?>
                <li class="page-item <?= (1 == $currentPage) ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query($pageParams) ?>">1</a>
                </li>

                <?php if (max(2, $currentPage - $window) > 2): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>

                <?php
                $start = max(2, $currentPage - $window);
                $end = min($totalPages - 1, $currentPage + $window);

                for ($i = $start; $i <= $end; $i++):
                    if ($i <= 1 || $i >= $totalPages) continue;
                    $pageParams = array_merge($_GET, ['page' => $i]);
                ?>
                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if (min($totalPages - 1, $currentPage + $window) < $totalPages - 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>

                <?php if ($totalPages > 1): ?>
                    <?php $pageParams = array_merge($_GET, ['page' => $totalPages]); ?>
                    <li class="page-item <?= ($totalPages == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>

                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <?php $nextParams = array_merge($_GET, ['page' => $currentPage + 1]); ?>
                    <a class="page-link" href="?<?= http_build_query($nextParams) ?>">Sau</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
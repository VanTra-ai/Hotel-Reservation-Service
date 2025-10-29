<?php include 'app/views/shares/header.php';
//app/views/admin/hotels/list.php
$hotels = $data['hotels'] ?? [];
$pagination = $data['pagination'] ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$searchTerm = $data['searchTerm'] ?? ''; // <<< LẤY SEARCH TERM

// Helper để tạo URL giữ lại tất cả các tham số GET hiện tại (bao gồm cả 'search') trừ 'page'
function buildHotelUrlWithPage($page, $currentParams)
{
    $params = array_merge($currentParams, ['page' => $page]);
    if (empty($params['search'])) unset($params['search']); // Loại bỏ search nếu trống
    return BASE_URL . '/admin/hotel?' . http_build_query($params);
}

// Lấy tham số GET hiện tại (trừ page) để tái sử dụng trong phân trang
$currentGetParams = $_GET;
unset($currentGetParams['page']);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Quản lý Khách sạn</h2>
        <a href="<?= BASE_URL ?>/admin/hotel/add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm khách sạn mới
        </a>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="<?= BASE_URL ?>/admin/hotel" method="GET" class="d-flex">
                <input type="search" name="search" class="form-control me-2"
                    placeholder="Tìm theo ID, Tên Khách sạn, Thành phố..."
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

    <?php if (isset($_SESSION['flash_message'])): /* ... (code flash message giữ nguyên) ... */ endif; ?>

    <?php if (empty($data['hotels'])): ?>
        <div class="alert alert-info text-center" role="alert">
            Hiện tại chưa có khách sạn nào được thêm.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Khách sạn</th>
                        <th>Địa chỉ</th>
                        <th>Thành phố</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['hotels'] as $hotel): ?>
                        <tr>
                            <td><?= htmlspecialchars($hotel->id) ?></td>
                            <td><?= htmlspecialchars($hotel->name) ?></td>
                            <td><?= htmlspecialchars($hotel->address) ?></td>
                            <td><?= htmlspecialchars($hotel->city_name ?? 'N/A') ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>/admin/hotel/edit/<?= $hotel->id ?>" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/hotel/delete/<?= $hotel->id ?>" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa khách sạn này không?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <nav aria-label="Phân trang khách sạn" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    $currentPage = $pagination['current_page'];
                    $totalPages = $pagination['total_pages'];
                    $window = 2; // Số trang hiển thị ở mỗi bên (VD: 1, 2, [3], 4, 5)
                    $start = max(2, $currentPage - $window);
                    $end = min($totalPages - 1, $currentPage + $window);
                    ?>

                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <?php $prevParams = array_merge($_GET, ['page' => $currentPage - 1]); ?>
                        <a class="page-link" href="?<?= http_build_query($prevParams) ?>">Trước</a>
                    </li>

                    <?php $pageParams = array_merge($_GET, ['page' => 1]); ?>
                    <li class="page-item <?= (1 == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query($pageParams) ?>">1</a>
                    </li>

                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>

                    <?php
                    $rangeEnd = ($totalPages == 2) ? 1 : $end; // Xử lý trường hợp chỉ có 2 trang
                    for ($i = $start; $i <= $rangeEnd; $i++):
                        if ($i <= 1 || $i >= $totalPages) continue; // Tránh lặp lại trang 1 và trang cuối
                        $pageParams = array_merge($_GET, ['page' => $i]);
                    ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $totalPages - 1): ?>
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
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
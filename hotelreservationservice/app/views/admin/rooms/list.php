<?php include 'app/views/shares/header.php';
//app/views/admin/rooms/list.php
$rooms = $data['rooms'] ?? [];
$pagination = $data['pagination'] ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$searchTerm = $data['searchTerm'] ?? ''; // <<< LẤY SEARCH TERM

// Helper để tạo URL giữ lại tất cả các tham số GET hiện tại (bao gồm cả 'search') trừ 'page'
function buildRoomUrlWithPage($page, $currentParams)
{
    $params = array_merge($currentParams, ['page' => $page]);
    if (empty($params['search'])) unset($params['search']); // Loại bỏ search nếu trống
    return BASE_URL . '/admin/room?' . http_build_query($params);
}

// Lấy tham số GET hiện tại (trừ page) để tái sử dụng trong phân trang
$currentGetParams = $_GET;
unset($currentGetParams['page']);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Quản lý Phòng</h2>
        <a href="<?= BASE_URL ?>/admin/room/add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm phòng mới
        </a>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="<?= BASE_URL ?>/admin/room" method="GET" class="d-flex">
                <input type="search" name="search" class="form-control me-2"
                    placeholder="Tìm theo Số phòng, Loại phòng, Tên khách sạn..."
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

    <?php if (isset($_GET['error_message'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($_GET['error_message']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($rooms)): ?>
        <div class="alert alert-info text-center" role="alert">
            Hiện tại chưa có phòng nào được thêm.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Số phòng</th>
                        <th>Loại phòng</th>
                        <th>Khách sạn</th>
                        <th>Giá (VNĐ)</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= htmlspecialchars($room->id) ?></td>
                            <td><?= htmlspecialchars($room->room_number) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($room->room_type) ?></span></td>
                            <td><?= htmlspecialchars($room->hotel_name ?? 'N/A') ?></td>
                            <td><span class="fw-bold text-success"><?= number_format($room->price, 0, ',', '.') ?></span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>/room/show/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/room/edit/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/room/delete/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-danger" title="Xóa"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phòng này?')">
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
            <nav aria-label="Phân trang phòng" class="mt-4">
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
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
<?php include 'app/views/shares/header.php';
//app/views/partner/bookings/list.php
$bookings = $data['bookings'] ?? [];
$pagination = $data['pagination'] ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$searchTerm = $data['searchTerm'] ?? '';

// Helper để tạo URL giữ lại tham số GET hiện tại (search) trừ 'page'
function buildPartnerBookingUrlWithPage($page, $currentParams)
{
    $params = array_merge($currentParams, ['page' => $page]);
    if (empty($params['search'])) unset($params['search']);
    return BASE_URL . '/partner/booking?' . http_build_query($params);
}

$currentGetParams = $_GET;
unset($currentGetParams['page']);
?>

<div class="container my-5">
    <h3 class="mb-4">Quản lý booking cho khách sạn của bạn</h3>
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="<?= BASE_URL ?>/partner/booking" method="GET" class="d-flex">
                <input type="search" name="search" class="form-control me-2"
                    placeholder="Tìm theo ID, Tên khách hàng, Phòng..."
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
    <?php if (!empty($data['bookings'])): ?>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Người đặt</th>
                    <th>Phòng</th>
                    <th>Khách sạn</th>
                    <th>Ngày nhận/trả</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['bookings'] as $b): ?>
                    <tr>
                        <td><?= $b->id ?></td>
                        <td><?= htmlspecialchars($b->username) ?></td>
                        <td><?= htmlspecialchars($b->room_number . ' - ' . $b->room_type) ?></td>
                        <td><?= htmlspecialchars($b->hotel_name) ?></td>
                        <td><?= htmlspecialchars($b->check_in_date) ?> -> <?= htmlspecialchars($b->check_out_date) ?></td>
                        <td><?= number_format($b->total_price, 0, ',', '.') ?> VNĐ</td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($b->status ?? 'pending') ?></span></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/partner/booking/updateStatus/<?= $b->id ?>" class="d-flex gap-1 mb-2">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="pending" <?= ($b->status == 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= ($b->status == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="checked_in" <?= ($b->status == 'checked_in') ? 'selected' : '' ?>>Checked In</option>
                                    <option value="checked_out" <?= ($b->status == 'checked_out') ? 'selected' : '' ?>>Checked Out</option>
                                    <option value="cancelled" <?= ($b->status == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Cập nhật</button>
                            </form>
                            <a href="<?= BASE_URL ?>/partner/booking/cancel/<?= $b->id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn hủy booking này?')">Hủy</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <nav aria-label="Phân trang đặt phòng" class="mt-4">
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
    <?php else: ?>
        <div class="alert alert-info">Chưa có booking nào cho khách sạn của bạn.</div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
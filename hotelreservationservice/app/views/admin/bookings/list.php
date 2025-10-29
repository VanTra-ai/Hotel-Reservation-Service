<?php include 'app/views/shares/header.php';
//app/views/admin/bookings/list.php
$bookings = $data['bookings'] ?? [];
$pagination = $data['pagination'] ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$searchTerm = $data['searchTerm'] ?? '';

// Helper để tạo URL giữ lại tất cả các tham số GET hiện tại (bao gồm cả 'search') trừ 'page'
function buildBookingUrlWithPage($page, $currentParams)
{
    $params = array_merge($currentParams, ['page' => $page]);
    if (empty($params['search'])) unset($params['search']); // Loại bỏ search nếu trống
    return BASE_URL . '/admin/booking?' . http_build_query($params);
}

// Lấy tham số GET hiện tại (trừ page) để tái sử dụng trong phân trang
$currentGetParams = $_GET;
unset($currentGetParams['page']);
?>

<div class="container my-5">
    <h3>Quản lý booking</h3>
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="<?= BASE_URL ?>/admin/booking" method="GET" class="d-flex">
                <input type="search" name="search" class="form-control me-2"
                    placeholder="Tìm theo ID Đặt phòng, Tên khách hàng, Email..."
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
    <?php if (!empty($bookings)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người đặt</th>
                    <th>Phòng</th>
                    <th>Khách sạn</th>
                    <th>Ngày nhận</th>
                    <th>Ngày trả</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= $b->id ?></td>
                        <td><?= htmlspecialchars($b->username) ?></td>
                        <td><?= htmlspecialchars($b->room_number . ' - ' . $b->room_type) ?></td>
                        <td><?= htmlspecialchars($b->hotel_name) ?></td>
                        <td><?= htmlspecialchars($b->check_in_date) ?></td>
                        <td><?= htmlspecialchars($b->check_out_date) ?></td>
                        <td><?= number_format($b->total_price, 0, ',', '.') ?> VNĐ</td>
                        <td><?= htmlspecialchars($b->status ?? 'pending') ?></td>
                        <td>
                            <form method="POST" action="/Hotel-Reservation-Service/hotelreservationservice/admin/booking/updateStatus/<?= $b->id ?>" class="d-flex gap-1">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="pending" <?= ($b->status == 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= ($b->status == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="checked_in" <?= ($b->status == 'checked_in') ? 'selected' : '' ?>>Checked In</option>
                                    <option value="checked_out" <?= ($b->status == 'checked_out') ? 'selected' : '' ?>>Checked Out</option>
                                    <option value="cancelled" <?= ($b->status == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Cập nhật</button>
                            </form>
                            <a href="/Hotel-Reservation-Service/hotelreservationservice/admin/booking/cancel/<?= $b->id ?>" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Bạn có chắc muốn hủy booking này?')">Hủy</a>
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
                    $window = 2;
                    ?>

                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildBookingUrlWithPage($currentPage - 1, $currentGetParams) ?>">Trước</a>
                    </li>

                    <li class="page-item <?= (1 == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= buildBookingUrlWithPage(1, $currentGetParams) ?>">1</a>
                    </li>

                    <?php if (max(2, $currentPage - $window) > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>

                    <?php
                    $start = max(2, $currentPage - $window);
                    $end = min($totalPages - 1, $currentPage + $window);

                    for ($i = $start; $i <= $end; $i++):
                        if ($i <= 1 || $i >= $totalPages) continue;
                    ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= buildBookingUrlWithPage($i, $currentGetParams) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if (min($totalPages - 1, $currentPage + $window) < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>

                    <?php if ($totalPages > 1): ?>
                        <li class="page-item <?= ($totalPages == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= buildBookingUrlWithPage($totalPages, $currentGetParams) ?>"><?= $totalPages ?></a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildBookingUrlWithPage($currentPage + 1, $currentGetParams) ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">Chưa có booking nào.</div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
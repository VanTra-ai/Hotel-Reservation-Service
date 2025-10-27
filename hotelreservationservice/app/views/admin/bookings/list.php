<?php include 'app/views/shares/header.php';
//app/views/admin/bookings/list.php
?>

<div class="container my-5">
    <h3>Quản lý booking</h3>
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
    <?php else: ?>
        <div class="alert alert-info">Chưa có booking nào.</div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
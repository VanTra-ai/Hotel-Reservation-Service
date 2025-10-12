<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <h3 class="mb-4">Lịch sử đặt phòng</h3>

    <?php if (!empty($bookings)): ?>
        <div class="list-group">
            <?php foreach ($bookings as $b): ?>
                <div class="list-group-item mb-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5><?= htmlspecialchars($b->hotel_name) ?> - <?= htmlspecialchars($b->room_number . ' | ' . $b->room_type) ?></h5>
                        <span class="badge 
                            <?= $b->status == 'cancelled' ? 'bg-danger' : ($b->status == 'confirmed' ? 'bg-success' : 'bg-warning text-dark') ?>">
                            <?= ucfirst($b->status) ?>
                        </span>
                    </div>
                    <p>Ngày nhận: <?= htmlspecialchars($b->check_in_date) ?> | Ngày trả: <?= htmlspecialchars($b->check_out_date) ?></p>
                    <p>Thành tiền: <?= number_format($b->total_price, 0, ',', '.') ?> VNĐ</p>

                    <?php if ($b->status != 'cancelled' && strtotime($b->check_in_date) > time()): ?>
                        <form method="POST" action="/Hotel-Reservation-Service/hotelreservationservice/booking/cancel" class="text-end">
                            <input type="hidden" name="booking_id" value="<?= $b->id ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn hủy phòng này?')">
                                Hủy phòng
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">Bạn chưa có lịch sử đặt phòng nào.</p>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
<?php
// app/views/booking/history.php

include 'app/views/shares/header.php';

// Khởi tạo biến từ Controller
$bookings = $data['bookings'] ?? [];
$STATUS_CHECKED_OUT = $data['STATUS_CHECKED_OUT'] ?? 'checked_out'; // Lấy hằng số từ controller
?>

<div class="container my-5">
    <h3 class="mb-4">Lịch sử đặt phòng</h3>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_message']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php if (!empty($bookings)): ?>
        <div class="list-group">
            <?php foreach ($bookings as $b): ?>
                <?php
                // Tính số đêm để hiển thị
                $check_in = strtotime($b->check_in_date);
                $check_out = strtotime($b->check_out_date);
                $nights = max(1, round(($check_out - $check_in) / (60 * 60 * 24)));

                // Trạng thái cho mục đích hiển thị
                $status_class = 'bg-secondary';
                if ($b->status == 'cancelled') $status_class = 'bg-danger';
                if ($b->status == 'confirmed') $status_class = 'bg-success';
                if ($b->status == $STATUS_CHECKED_OUT) $status_class = 'bg-primary';
                ?>
                <div class="list-group-item mb-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5><?= htmlspecialchars($b->hotel_name) ?> - <?= htmlspecialchars($b->room_type) ?></h5>
                        <span class="badge <?= $status_class ?>">
                            <?= ucfirst($b->status) ?>
                        </span>
                    </div>

                    <p class="mb-1">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?= htmlspecialchars($b->check_in_date) ?> (Nhận) | <?= htmlspecialchars($b->check_out_date) ?> (Trả)
                        <span class="ms-3 badge bg-info text-dark"><?= $nights ?> đêm</span>
                    </p>
                    <p class="mb-0">Thành tiền: <span class="fw-bold text-success"><?= number_format($b->total_price, 0, ',', '.') ?> VNĐ</span></p>

                    <div class="text-end mt-2">
                        <?php if ($b->status == $STATUS_CHECKED_OUT && ($b->review_count ?? 0) == 0): ?>
                            <a href="<?= BASE_URL ?>/review/showForm/<?= $b->id ?>" class="btn btn-sm btn-info text-dark">
                                <i class="fas fa-star me-1"></i> Viết đánh giá
                            </a>
                        <?php elseif ($b->status != 'cancelled' && strtotime($b->check_in_date) > time()): ?>
                            <form method="POST" action="<?= BASE_URL ?>/booking/cancel" class="d-inline">
                                <input type="hidden" name="booking_id" value="<?= $b->id ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn hủy phòng này?')">
                                    <i class="fas fa-times me-1"></i> Hủy phòng
                                </button>
                            </form>
                        <?php elseif (($b->review_count ?? 0) > 0): ?>
                            <span class="badge bg-secondary">Đã đánh giá</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">Bạn chưa có lịch sử đặt phòng nào.</p>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
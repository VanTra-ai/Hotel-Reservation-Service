<?php
// app/views/review/form.php
include 'app/views/shares/header.php';

// Khởi tạo các biến từ Controller
$booking = $data['booking'] ?? null;
$errors = $data['errors'] ?? [];

// Mảng 7 tiêu chí hiển thị
$criteria = [
    'rating_staff'      => 'Nhân viên',
    'rating_amenities'  => 'Tiện nghi',
    'rating_cleanliness' => 'Sạch sẽ',
    'rating_comfort'    => 'Thoải mái',
    'rating_value'      => 'Đáng giá tiền',
    'rating_location'   => 'Địa điểm',
    'rating_wifi'       => 'WiFi',
];

// Dừng nếu không có thông tin booking hợp lệ
if (!$booking): ?>
    <div class="container my-5">
        <div class="alert alert-danger text-center">Booking không hợp lệ hoặc đã được đánh giá.</div>
        <div class="text-center"><a href="<?= BASE_URL ?>/booking/history" class="btn btn-primary">Về lịch sử đặt phòng</a></div>
    </div>
<?php
    include 'app/views/shares/footer.php';
    return;
endif;
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4 text-center text-primary">Viết Đánh giá về Chuyến đi của bạn 📝</h2>

    <form method="POST" action="<?= BASE_URL ?>/review/add">
        <div class="row g-4">

            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">1. Thông tin chuyến đi & Bình luận</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking->id) ?>">
                        <input type="hidden" name="hotel_id" value="<?= htmlspecialchars($booking->hotel_id) ?>">

                        <div class="alert alert-info py-2">
                            <p class="mb-1">Khách sạn: <strong><?= htmlspecialchars($booking->hotel_name) ?></strong></p>
                            <p class="mb-1">Phòng đã ở: <strong><?= htmlspecialchars($booking->room_type) ?></strong></p>
                            <p class="mb-0">Thời gian: <strong><?= htmlspecialchars($booking->check_in_date) ?></strong> đến <strong><?= htmlspecialchars($booking->check_out_date) ?></strong> (<?= htmlspecialchars($booking->nights ?? '?') ?> đêm)</p>
                        </div>

                        <div class="mb-3">
                            <label for="comment" class="form-label fw-bold">Bình luận của bạn:</label>
                            <textarea id="comment" name="comment" class="form-control" rows="5" placeholder="Chia sẻ trải nghiệm của bạn về khách sạn... (Đây sẽ là input chính cho AI)"></textarea>
                        </div>

                        <?php if (isset($errors['comment'])): ?>
                            <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['comment']) ?></div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">2. Chấm điểm Chi tiết (Từ 1.0 đến 10.0)</h5>
                    </div>
                    <div class="card-body">

                        <?php foreach ($criteria as $name => $label): ?>
                            <div class="mb-3">
                                <label for="<?= $name ?>" class="form-label">
                                    <?= htmlspecialchars($label) ?>: <span id="<?= $name ?>-value" class="fw-bold text-success">9.0</span>
                                </label>
                                <input type="range" class="form-range" id="<?= $name ?>" name="<?= $name ?>"
                                    min="1.0" max="10.0" step="0.1" value="9.0">
                                <?php if (isset($errors[$name])): ?>
                                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errors[$name]) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Hoàn tất đánh giá và Gửi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sliders = document.querySelectorAll('.form-range');
        sliders.forEach(slider => {
            const valueSpan = document.getElementById(slider.id + '-value');

            // Hàm cập nhật giá trị hiển thị
            const updateValue = () => {
                valueSpan.textContent = parseFloat(slider.value).toFixed(1);
            };

            // Gán sự kiện cho mỗi lần người dùng kéo
            slider.addEventListener('input', updateValue);

            // Khởi tạo giá trị khi tải trang
            updateValue();
        });
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
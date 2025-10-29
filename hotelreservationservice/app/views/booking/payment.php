<?php
include 'app/views/shares/header.php';
//app/views/booking/payment.php

$booking = $data['booking'] ?? null;
if (!$booking) {
    echo "<div class='container my-5 alert alert-danger'>Đơn hàng không hợp lệ.</div>";
    include 'app/views/shares/footer.php';
    exit;
}
?>

<div class="container my-5" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light text-center p-3">
            <img src="<?= BASE_URL ?>/public/images/book/vnpay.png" alt="VNPAY" style="max-height: 60px;">

            <h5 class="mb-0 mt-2">CỔNG THANH TOÁN VNPAY</h5>
        </div>
        <div class="card-body p-4">

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message']['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/booking/processPayment" method="POST">
                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking->id) ?>">

                <div class="mb-3">
                    <label for="card_name" class="form-label">Tên chủ thẻ</label>
                    <input type="text" id="card_name" class="form-control" value="NGUYEN VAN A" readonly>
                </div>
                <div class="mb-3">
                    <label for="card_number" class="form-label">Số thẻ</label>
                    <input type="text" id="card_number" class="form-control" value="9704111122223333" readonly>
                </div>
                <div class="mb-3">
                    <label for="card_date" class="form-label">Ngày phát hành</label>
                    <input type="text" id="card_date" class="form-control" value="10/25" readonly>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Nhà cung cấp:</span>
                    <span>CTCP PHAN MEM VA TRUYEN THONG GMOB VIET NAM</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Đơn hàng:</span>
                    <span class="fw-bold">#<?= htmlspecialchars($booking->id) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="fw-bold fs-5">Số tiền:</span>
                    <span class="fw-bold fs-4 text-danger">
                        <?= number_format($booking->total_price, 0, ',', '.') ?> VNĐ
                    </span>
                </div>

                <p class="text-center text-muted small mt-3">
                    (Đây là giao diện mô phỏng. Không cần nhập thông tin thẻ thật.)
                </p>

                <div class="d-grid gap-2 mt-4">

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-credit-card me-2"></i>Thanh toán bằng thẻ (Mô phỏng)
                    </button>
                </div>

                <hr class="my-3">

                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/booking/confirmation?id=<?= htmlspecialchars($booking->id) ?>&status=pending" class="btn btn-outline-success btn-lg">
                        <i class="fas fa-store me-2"></i>Thanh toán tại quầy/sau
                    </a>

                    <a href="<?= BASE_URL ?>/booking/history" class="btn btn-light text-center">
                        <i class="fas fa-times me-2"></i>Hủy đơn hàng
                    </a>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="small text-muted mb-1">Chấp nhận thanh toán các của các ngân hàng</p>
                <img src="<?= BASE_URL ?>/public/images/book/bank.png" alt="Các ngân hàng" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
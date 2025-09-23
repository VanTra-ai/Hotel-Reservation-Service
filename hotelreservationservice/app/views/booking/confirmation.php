<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <?php if (!isset($_GET['success'])): ?>
        <?php
            // Các biến mong đợi: $room, $checkInDate, $checkOutDate, $nights, $totalPrice, $guests
            // Nếu đến từ POST preview trong controller, các biến này đã có sẵn trong scope
        ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">Xác nhận đặt phòng</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3"><strong>Phòng:</strong> <?= htmlspecialchars(($room->room_number ?? '') . ' - ' . ($room->room_type ?? '')) ?></div>
                        <div class="mb-3"><strong>Ngày nhận:</strong> <?= htmlspecialchars($checkInDate) ?></div>
                        <div class="mb-3"><strong>Ngày trả:</strong> <?= htmlspecialchars($checkOutDate) ?></div>
                        <div class="mb-3"><strong>Số đêm:</strong> <?= htmlspecialchars($nights ?? 1) ?></div>
                        <div class="mb-3"><strong>Số người:</strong> <?= htmlspecialchars($guests ?? 1) ?></div>
                        <div class="mb-3"><strong>Thành tiền:</strong> <span class="text-success fw-bold"><?= number_format($totalPrice ?? 0, 0, ',', '.') ?> VNĐ</span></div>

                        <form method="POST" action="/hotelreservationservice/booking/bookRoom">
                            <input type="hidden" name="action" value="confirm">
                            <input type="hidden" name="room_id" value="<?= htmlspecialchars($room->id ?? $_POST['room_id'] ?? '') ?>">
                            <input type="hidden" name="check_in_date" value="<?= htmlspecialchars($checkInDate) ?>">
                            <input type="hidden" name="check_out_date" value="<?= htmlspecialchars($checkOutDate) ?>">
                            <input type="hidden" name="total_price" value="<?= htmlspecialchars($totalPrice ?? 0) ?>">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="javascript:history.back()" class="btn btn-secondary">
                                    Chỉnh sửa
                                </a>
                                <button type="submit" class="btn btn-success">
                                    Xác nhận và thanh toán sau
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4 text-success"><i class="fas fa-check-circle fa-3x"></i></div>
                        <h3 class="fw-bold mb-3">Đặt phòng thành công!</h3>
                        <p class="text-muted mb-4">Cảm ơn bạn đã đặt phòng. Chúng tôi đã ghi nhận yêu cầu của bạn.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="/hotelreservationservice/Home" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Về trang chủ
                            </a>
                            <a href="/hotelreservationservice/Hotel" class="btn btn-outline-secondary">
                                <i class="fas fa-hotel me-2"></i>Tiếp tục xem khách sạn
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>



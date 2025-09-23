<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Đặt phòng</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/hotelreservationservice/booking/bookRoom">
                        <input type="hidden" name="action" value="preview">
                        <input type="hidden" name="room_id" value="<?= htmlspecialchars($room->id ?? ($_POST['room_id'] ?? '')) ?>">

                        <div class="mb-3">
                            <label class="form-label">Phòng</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars(($room->room_number ?? '') . ' - ' . ($room->room_type ?? '')) ?>" disabled>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="check_in_date" class="form-label">Ngày nhận phòng</label>
                                <input type="date" id="check_in_date" name="check_in_date" class="form-control" value="<?= htmlspecialchars($_POST['check_in_date'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="check_out_date" class="form-label">Ngày trả phòng</label>
                                <input type="date" id="check_out_date" name="check_out_date" class="form-control" value="<?= htmlspecialchars($_POST['check_out_date'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="guests" class="form-label">Số người</label>
                            <input type="number" id="guests" name="guests" class="form-control" value="<?= htmlspecialchars($_POST['guests'] ?? '1') ?>" min="1" max="10">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/hotelreservationservice/Hotel/show/<?= htmlspecialchars($room->hotel_id ?? '') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>Xác nhận đặt phòng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>



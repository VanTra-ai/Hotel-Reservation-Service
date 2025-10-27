<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Đặt phòng</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($data['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($data['error']) ?></div>
                    <?php endif; ?>

                    <?php
                    // Lấy các giá trị từ controller
                    $room = $data['room'] ?? null;
                    $check_in_value = htmlspecialchars($data['check_in'] ?? '');
                    $check_out_value = htmlspecialchars($data['check_out'] ?? '');
                    ?>

                    <form method="POST" action="<?= BASE_URL ?>/booking/bookRoom">
                        <input type="hidden" name="room_id" value="<?= $room->id ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label">Phòng</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars(($room->room_number ?? '') . ' - ' . ($room->room_type ?? '')) ?>" disabled>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="check_in_date" class="form-label">Ngày nhận phòng</label>
                                <input type="text" id="check_in_date" name="check_in_date" class="form-control"
                                    value="<?= $check_in_value ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="check_out_date" class="form-label">Ngày trả phòng</label>
                                <input type="text" id="check_out_date" name="check_out_date" class="form-control"
                                    value="<?= $check_out_value ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="guests" class="form-label">Số người (Tối đa: <?= htmlspecialchars($room->capacity ?? 2) ?>)</label>
                            <input type="number" id="guests" name="guests" class="form-control"
                                value="1" min="1" max="<?= htmlspecialchars($room->capacity ?? 2) ?>">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check me-2"></i>Xác nhận đặt phòng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkInInput = document.getElementById('check_in_date');
        const checkOutInput = document.getElementById('check_out_date');

        const checkInPicker = flatpickr(checkInInput, {
            minDate: "today",
            dateFormat: "Y-m-d", // Đảm bảo định dạng Y-m-d
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    checkOutPicker.set('minDate', new Date(selectedDates[0]).fp_incr(1));
                    // Nếu ngày trả phòng cũ không hợp lệ, xóa nó
                    if (checkOutPicker.input.value && checkOutPicker.input.value <= selectedDates[0]) {
                        checkOutPicker.clear();
                    }
                }
            }
        });

        const checkOutPicker = flatpickr(checkOutInput, {
            dateFormat: "Y-m-d",
            minDate: checkInInput.value ? new Date(checkInInput.value).fp_incr(1) : new Date().fp_incr(1)
        });
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
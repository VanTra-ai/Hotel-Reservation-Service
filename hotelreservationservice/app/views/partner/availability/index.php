<?php
include 'app/views/shares/header.php';

// Lấy dữ liệu từ Controller
$roomTypes = $data['room_types'] ?? []; // (VD: ['Phòng Deluxe' => 10])
$matrix = $data['matrix'] ?? [];
$startDate = $data['start_date'] ?? date('d-m-Y');
$endDate = $data['end_date'] ?? date('d-m-Y');

// Tính toán dải ngày cho tiêu đề cột
$dateRange = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), new DateTime($endDate));

// Helper lấy màu dựa trên số lượng phòng trống
function getAvailabilityClass(int $availableCount)
{
    if ($availableCount >= 5) return 'bg-success-subtle'; // Còn nhiều
    if ($availableCount > 0) return 'bg-warning-subtle'; // Sắp hết
    return 'bg-danger-subtle'; // Hết phòng
}
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Bảng điều khiển Phòng trống (Theo Loại phòng): <?= htmlspecialchars($data['hotel_name'] ?? '') ?></h2>

    <!-- KHỐI CHỌN NGÀY -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Chọn Phạm vi Lịch</h5>
            <form action="<?= BASE_URL ?>/partner/availability" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Ngày bắt đầu</label>
                    <input type="text" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Ngày kết thúc</label>
                    <input type="text" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Xem Bảng Ma Trận</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($roomTypes)): ?>
        <div class="alert alert-info text-center">Khách sạn chưa có phòng nào.</div>
    <?php else: ?>
        <div class="table-responsive shadow-lg rounded-3">
            <table class="table table-bordered table-sm align-middle text-center" style="min-width: 1000px;">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th style="width: 200px;">Loại phòng</th>
                        <?php foreach ($dateRange as $date): ?>
                            <th style="width: 40px;">
                                <div class="text-uppercase small"><?= $date->format('D') ?></div>
                                <div class="fw-bold"><?= $date->format('d/m') ?></div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roomTypes as $roomType => $totalCount): ?>
                        <tr>
                            <td class="text-start bg-light fw-bold p-2" style="width: 200px;">
                                <div class="fw-bold text-primary"><?= htmlspecialchars($roomType) ?></div>
                                <div class="small text-muted">Tổng phòng: <?= $totalCount ?></div>
                            </td>

                            <?php foreach ($dateRange as $date):
                                $dateKey = $date->format('Y-m-d');
                                $slot = $matrix[$roomType][$dateKey] ?? ['total' => $totalCount, 'booked' => 0];
                                $bookedCount = (int)$slot['booked'];
                                $availableCount = $totalCount - $bookedCount;

                                $statusClass = getAvailabilityClass($availableCount);

                                // Chuẩn bị Tooltip (Nâng cao)
                                $tooltip = "Trống: $availableCount / $totalCount\n";
                                $tooltip .= "Đã đặt (Tổng): $bookedCount\n";
                                if (!empty($slot['status_details'])) {
                                    if (isset($slot['status_details']['confirmed'])) $tooltip .= " - Xác nhận: " . $slot['status_details']['confirmed'] . "\n";
                                    if (isset($slot['status_details']['pending'])) $tooltip .= " - Chờ duyệt: " . $slot['status_details']['pending'] . "\n";
                                    if (isset($slot['status_details']['checked_in'])) $tooltip .= " - Đã check-in: " . $slot['status_details']['checked_in'] . "\n";
                                }
                            ?>
                                <td class="<?= $statusClass ?> fw-bold"
                                    title="<?= htmlspecialchars(trim($tooltip)) ?>"
                                    data-bs-toggle="tooltip">
                                    <?= $availableCount ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 small">
            <span class="badge bg-success-subtle border border-success text-dark">Còn nhiều phòng</span>
            <span class="badge bg-warning-subtle border border-warning text-dark">Sắp hết phòng</span>
            <span class="badge bg-danger-subtle border border-danger text-dark">Hết phòng</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        flatpickr(startDateInput, {
            dateFormat: "d-m-Y",
            defaultDate: "<?= htmlspecialchars($startDate) ?>",
            minDate: "today",
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    // Đặt ngày kết thúc tối thiểu là ngày sau ngày bắt đầu
                    flatpickr(endDateInput).set('minDate', new Date(selectedDates[0]).fp_incr(1));
                }
            }
        });

        flatpickr(endDateInput, {
            dateFormat: "d-m-Y",
            defaultDate: "<?= htmlspecialchars($endDate) ?>",
            minDate: startDateInput.value ? new Date(startDateInput.value).fp_incr(1) : new Date().fp_incr(1)
        });

        // Kích hoạt tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
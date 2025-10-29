<?php
include 'app/views/shares/header.php';
//app/views/partner/dashboard/index.php

// Lấy các biến từ $data (đã được Controller mới gửi)
$filters = $data['filters'] ?? [];
$totalBookings = $data['totalBookings'] ?? 0;
$totalRevenue = $data['totalRevenue'] ?? 0;
$totalHotels = $data['totalHotels'] ?? 0;
$dailyRevenueData = $data['dailyRevenueData'] ?? [];
$bookingStatusData = $data['bookingStatusData'] ?? [];
$groupBy = $filters['group_by'] ?? 'day';
?>

<div class="container my-5">

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Bộ lọc Tổng quan</h5>
            <form action="<?= BASE_URL ?>/partner/dashboard" method="GET" class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label for="filter_group_by" class="form-label">Xem theo</label>
                    <select name="group_by" id="filter_group_by" class="form-select">
                        <option value="day" <?= ($groupBy == 'day') ? 'selected' : '' ?>>Theo ngày</option>
                        <option value="month" <?= ($groupBy == 'month') ? 'selected' : '' ?>>Theo tháng</option>
                        <option value="year" <?= ($groupBy == 'year') ? 'selected' : '' ?>>Theo năm</option>
                    </select>
                </div>

                <div class="col-md-3" id="month_filter_container">
                    <label for="filter_month" class="form-label">Tháng</label>
                    <select name="month" id="filter_month" class="form-select">
                        <option value="" <?= (empty($filters['month'])) ? 'selected' : '' ?>>-- Tất cả Tháng --</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (($filters['month'] ?? 0) == $m) ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3" id="year_filter_container">
                    <label for="filter_year" class="form-label">Năm</label>
                    <select name="year" id="filter_year" class="form-select">
                        <option value="" <?= (empty($filters['year'])) ? 'selected' : '' ?>>-- Tất cả Năm --</option>
                        <?php for ($y = 2020; $y <= 2025; $y++): ?>
                            <option value="<?= $y ?>" <?= (($filters['year'] ?? 0) == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </form>
        </div>
    </div>
    <h2 class="fw-bold mb-4">Tổng quan Kênh Đối tác
        <span class="fs-5 text-muted">(
            <?php
            if ($groupBy == 'year') echo 'Toàn bộ thời gian';
            elseif ($groupBy == 'month') echo 'Năm ' . htmlspecialchars($filters['year'] ?? date('Y'));
            else echo 'Tháng ' . htmlspecialchars($filters['month'] ?? date('m')) . '/' . htmlspecialchars($filters['year'] ?? date('Y'));
            ?>
            )</span>
    </h2>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="card text-white bg-primary mb-3 shadow">
                <div class="card-header"><i class="fas fa-shopping-cart me-2"></i>TỔNG SỐ ĐẶT PHÒNG</div>
                <div class="card-body">
                    <h4 class="card-title"><?= $totalBookings ?></h4>
                    <p class="card-text">Tổng số booking cho khách sạn của bạn.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card text-white bg-success mb-3 shadow">
                <div class="card-header"><i class="fas fa-dollar-sign me-2"></i>TỔNG DOANH THU</div>
                <div class="card-body">
                    <h4 class="card-title"><?= number_format($totalRevenue, 0, ',', '.') ?> VNĐ</h4>
                    <p class="card-text">Dựa trên các booking đã hoàn tất.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card text-white bg-danger mb-3 shadow">
                <div class="card-header"><i class="fas fa-hotel me-2"></i>SỐ KHÁCH SẠN</div>
                <div class="card-body">
                    <h4 class="card-title"><?= $totalHotels ?></h4>
                    <p class="card-text">Tổng số khách sạn bạn đang quản lý.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title" id="revenueChartTitle">Biểu đồ doanh thu</h5>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Thống kê trạng thái đặt phòng</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- Code cho Biểu đồ tròn (Đã sửa) ---
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusDataRaw = <?= json_encode($bookingStatusData) ?>;

        const statusLabels = Object.keys(statusDataRaw).map(status => status.charAt(0).toUpperCase() + status.slice(1));
        const statusCounts = Object.values(statusDataRaw);

        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#ffc107', '#dc3545', '#198754', '#0dcaf0', '#6c757d'],
                }]
            },
            options: {
                responsive: true
            }
        });

        // --- Code cho Biểu đồ đường (Đã sửa: Đọc từ PHP) ---
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const apiData = <?= json_encode($dailyRevenueData) ?>;
        const groupBy = <?= json_encode($groupBy) ?>;

        const selectedMonth = <?= json_encode($filters['month'] ?? null) ?>;
        const selectedYear = <?= json_encode($filters['year'] ?? date('Y')) ?>;

        let labels = [];
        let revenueValues = [];
        let chartTitle = 'Biểu đồ doanh thu';

        if (groupBy === 'month') {
            chartTitle = `Doanh thu theo tháng (Năm ${selectedYear})`;
            labels = ['Thg 1', 'Thg 2', 'Thg 3', 'Thg 4', 'Thg 5', 'Thg 6', 'Thg 7', 'Thg 8', 'Thg 9', 'Thg 10', 'Thg 11', 'Thg 12'];
            revenueValues = Array(12).fill(0);
            apiData.forEach(item => {
                revenueValues[item.month - 1] = item.revenue;
            });
        } else if (groupBy === 'year') {
            chartTitle = 'Doanh thu theo năm (Toàn bộ thời gian)';
            apiData.forEach(item => {
                labels.push(item.year);
                revenueValues.push(item.revenue);
            });
        } else { // 'day'
            chartTitle = `Doanh thu theo ngày (Tháng ${selectedMonth}/${selectedYear})`;
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            labels = Array.from({
                length: daysInMonth
            }, (_, i) => i + 1);
            revenueValues = Array(daysInMonth).fill(0);

            apiData.forEach(item => {
                const day = parseInt(new Date(item.booking_date).getDate());
                revenueValues[day - 1] = item.daily_revenue;
            });
        }

        document.getElementById('revenueChartTitle').textContent = chartTitle;

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenueValues,
                    borderColor: 'rgb(25, 135, 84)', // Màu xanh lá (success)
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true
            }
        });

        // <<< THÊM: JS ĐỂ ẨN/HIỆN DROPDOWN THỜI GIAN >>>
        const groupBySelect = document.getElementById('filter_group_by');
        const monthContainer = document.getElementById('month_filter_container');
        const yearContainer = document.getElementById('year_filter_container');

        function toggleFilters() {
            if (groupBySelect.value === 'year') {
                monthContainer.style.display = 'none';
                yearContainer.style.display = 'none';
            } else if (groupBySelect.value === 'month') {
                monthContainer.style.display = 'none';
                yearContainer.style.display = 'block'; // Cần chọn năm
            } else { // 'day'
                monthContainer.style.display = 'block';
                yearContainer.style.display = 'block';
            }
        }

        toggleFilters();
        groupBySelect.addEventListener('change', toggleFilters);

    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
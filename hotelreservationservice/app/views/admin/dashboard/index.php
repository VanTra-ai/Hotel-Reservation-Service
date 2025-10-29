<?php
include 'app/views/shares/header.php';
//app/views/admin/dashboard/index.php

// Lấy các biến từ $data
$filters = $data['filters'] ?? [];
$allCities = $data['allCities'] ?? [];
$allHotels = $data['allHotels'] ?? [];

// Lấy các biến thống kê
$totalBookings = $data['totalBookings'] ?? 0;
$totalRevenue = $data['totalRevenue'] ?? 0;
$totalMembers = $data['totalMembers'] ?? 0;
$totalHotels = $data['totalHotels'] ?? 0;

// Lấy dữ liệu biểu đồ
$dailyRevenueData = $data['dailyRevenueData'] ?? [];
$bookingStatusData = $data['bookingStatusData'] ?? [];

// Lấy bộ lọc group_by
$groupBy = $filters['group_by'] ?? 'day';
?>

<div class="container-fluid my-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Bộ lọc Tổng quan</h5>
            <form action="<?= BASE_URL ?>/admin/dashboard" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label for="filter_city" class="form-label">Tỉnh/Thành phố</label>
                    <select name="city_id" id="filter_city" class="form-select">
                        <option value="">-- Tất cả Tỉnh/Thành --</option>
                        <?php foreach ($allCities as $city): ?>
                            <option value="<?= $city->id ?>" <?= (($filters['city_id'] ?? null) == $city->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($city->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label for="filter_hotel" class="form-label">Khách sạn</label>
                    <select name="hotel_id" id="filter_hotel" class="form-select">
                        <option value="">-- Tất cả Khách sạn --</option>
                        <?php foreach ($allHotels as $hotel): ?>
                            <option value="<?= $hotel->id ?>" <?= (($filters['hotel_id'] ?? null) == $hotel->id) ? 'selected' : '' ?> data-city-id="<?= $hotel->city_id ?>">
                                <?= htmlspecialchars($hotel->name) ?> (<?= htmlspecialchars($hotel->city_name) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label for="filter_group_by" class="form-label">Xem theo</label>
                    <select name="group_by" id="filter_group_by" class="form-select">
                        <option value="day" <?= ($groupBy == 'day') ? 'selected' : '' ?>>Theo ngày</option>
                        <option value="month" <?= ($groupBy == 'month') ? 'selected' : '' ?>>Theo tháng</option>
                        <option value="year" <?= ($groupBy == 'year') ? 'selected' : '' ?>>Theo năm</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-4" id="month_filter_container">
                    <label for="filter_month" class="form-label">Tháng</label>
                    <select name="month" id="filter_month" class="form-select">
                        <option value="" <?= (empty($filters['month'])) ? 'selected' : '' ?>>-- Tất cả Tháng --</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (($filters['month'] ?? 0) == $m) ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-xl-2 col-md-4" id="year_filter_container">
                    <label for="filter_year" class="form-label">Năm</label>
                    <select name="year" id="filter_year" class="form-select">
                        <option value="" <?= (empty($filters['year'])) ? 'selected' : '' ?>>-- Tất cả Năm --</option>
                        <?php for ($y = 2020; $y <= 2025; $y++): ?>
                            <option value="<?= $y ?>" <?= (($filters['year'] ?? 0) == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-xl-1 col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <h2 class="mb-4">Tổng quan hệ thống
        <span class="fs-5 text-muted">(
            <?php
            $filterName = '';
            if (!empty($filters['hotel_id'])) {
                foreach ($allHotels as $h) {
                    if ($h->id == $filters['hotel_id']) $filterName = $h->name;
                }
            } elseif (!empty($filters['city_id'])) {
                foreach ($allCities as $c) {
                    if ($c->id == $filters['city_id']) $filterName = $c->name;
                }
            }
            echo htmlspecialchars($filterName);

            // Hiển thị thời gian
            if ($groupBy == 'year') echo ' (Toàn bộ thời gian)';
            elseif ($groupBy == 'month') echo ' (Năm ' . htmlspecialchars($filters['year'] ?? date('Y')) . ')';
            else echo ' (Tháng ' . htmlspecialchars($filters['month'] ?? date('m')) . '/' . htmlspecialchars($filters['year'] ?? date('Y')) . ')';
            ?>
            )</span>
    </h2>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-primary mb-3 shadow">
                <div class="card-header"><i class="fas fa-shopping-cart me-2"></i>TỔNG SỐ ĐẶT PHÒNG</div>
                <div class="card-body">
                    <h4 class="card-title"><?= $totalBookings ?></h4>
                    <p class="card-text">Tổng số booking trong hệ thống.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-success mb-3 shadow">
                <div class="card-header"><i class="fas fa-dollar-sign me-2"></i>TỔNG DOANH THU</div>
                <div class="card-body">
                    <h4 class="card-title"><?= number_format($totalRevenue, 0, ',', '.') ?> VNĐ</h4>
                    <p class="card-text">Dựa trên các booking đã hoàn tất.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-warning mb-3 shadow">
                <div class="card-header"><i class="fas fa-users me-2"></i>THÀNH VIÊN</div>
                <div class="card-body">
                    <h4 class="card-title"><?= $totalMembers ?></h4>
                    <p class="card-text">Tổng số tài khoản đã đăng ký.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-danger mb-3 shadow">
                <div class="card-header"><i class="fas fa-hotel me-2"></i>KHÁCH SẠN</div>
                <div class="card-body">
                    <h4 class="card-title"><?= $totalHotels ?></h4>
                    <p class="card-text">Tổng số khách sạn đang hoạt động.</p>
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

        // --- Code Biểu đồ tròn (Đã sửa) ---
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

        // --- Code Biểu đồ đường (Đã sửa) ---
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const apiData = <?= json_encode($dailyRevenueData) ?>;
        const groupBy = <?= json_encode($groupBy) ?>;

        const selectedMonth = <?= json_encode($filters['month'] ?? null) ?>;
        // Mặc định năm hiện tại nếu không có
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
            // Đảm bảo month và year có giá trị cho 'Theo ngày'
            const yearForDay = selectedYear ? selectedYear : <?= date('Y') ?>;
            const monthForDay = selectedMonth ? selectedMonth : <?= date('m') ?>;
            chartTitle = `Doanh thu theo ngày (Tháng ${monthForDay}/${yearForDay})`;
            const daysInMonth = new Date(yearForDay, monthForDay, 0).getDate();
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
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true
            }
        });

        // <<< BẮT ĐẦU: JS ĐỂ ẨN/HIỆN DROPDOWN THỜI GIAN >>>
        const groupBySelect = document.getElementById('filter_group_by');
        const monthContainer = document.getElementById('month_filter_container');
        const yearContainer = document.getElementById('year_filter_container');

        function toggleFilters() {
            if (groupBySelect.value === 'year') {
                monthContainer.style.display = 'none';
                yearContainer.style.display = 'none';
            } else if (groupBySelect.value === 'month') {
                monthContainer.style.display = 'none'; // Ẩn Tháng
                yearContainer.style.display = 'block'; // Hiện Năm
            } else { // 'day'
                monthContainer.style.display = 'block'; // Hiện Tháng
                yearContainer.style.display = 'block'; // Hiện Năm
            }
        }
        toggleFilters(); // Chạy lần đầu
        groupBySelect.addEventListener('change', toggleFilters);
        // <<< KẾT THÚC: JS ẨN/HIỆN DROPDOWN >>>


        // <<< BẮT ĐẦU: JS LỌC KHÁCH SẠN THEO THÀNH PHỐ >>>
        const citySelect = document.getElementById('filter_city');
        const hotelSelect = document.getElementById('filter_hotel');
        const allHotelOptions = Array.from(hotelSelect.options);

        citySelect.addEventListener('change', function() {
            const selectedCityId = this.value;
            hotelSelect.innerHTML = '';
            hotelSelect.appendChild(allHotelOptions[0]); // Thêm lại "Tất cả"

            allHotelOptions.forEach(option => {
                if (option.value === "" || option.dataset.cityId === selectedCityId) {
                    hotelSelect.appendChild(option.cloneNode(true));
                }
            });

            const currentSelectedHotelId = '<?= $filters['hotel_id'] ?? '' ?>';
            if (hotelSelect.querySelector(`option[value="${currentSelectedHotelId}"]`)) {
                hotelSelect.value = currentSelectedHotelId;
            } else {
                hotelSelect.value = "";
            }
        });

        if (citySelect.value) {
            citySelect.dispatchEvent(new Event('change'));
        }
        // <<< KẾT THÚC: JS LỌC KHÁCH SẠN >>>

    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
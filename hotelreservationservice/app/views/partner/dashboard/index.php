<?php include 'app/views/shares/header.php';
//app/views/partner/dashboard/index.php
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Tổng quan Kênh Đối tác</h2>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="card text-white bg-primary mb-3 shadow">
                <div class="card-header"><i class="fas fa-shopping-cart me-2"></i>TỔNG SỐ ĐẶT PHÒNG</div>
                <div class="card-body">
                    <h4 class="card-title"><?= $data['stats']->total_bookings ?? 0 ?></h4>
                    <p class="card-text">Tổng số booking cho khách sạn của bạn.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card text-white bg-success mb-3 shadow">
                <div class="card-header"><i class="fas fa-dollar-sign me-2"></i>TỔNG DOANH THU</div>
                <div class="card-body">
                    <h4 class="card-title"><?= number_format($data['stats']->total_revenue ?? 0, 0, ',', '.') ?> VNĐ</h4>
                    <p class="card-text">Dựa trên các booking đã hoàn tất.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card text-white bg-danger mb-3 shadow">
                <div class="card-header"><i class="fas fa-hotel me-2"></i>SỐ KHÁCH SẠN</div>
                <div class="card-body">
                    <h4 class="card-title"><?= $data['stats']->total_hotels ?? 0 ?></h4>
                    <p class="card-text">Tổng số khách sạn bạn đang quản lý.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Biểu đồ doanh thu theo ngày (Tháng <?= date('m/Y') ?>)</h5>
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
        // --- Code cho Biểu đồ tròn (Không thay đổi) ---
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = <?= json_encode($data['bookingStatusDistribution']) ?>;
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: ['#ffc107', '#dc3545', '#198754', '#0dcaf0', '#6c757d'],
                }]
            },
            options: {
                responsive: true
            }
        });

        // --- Code cho Biểu đồ đường (SỬA LẠI ĐƯỜNG DẪN AJAX) ---
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        // Trỏ đến action của PartnerDashboardController
        fetch('<?= BASE_URL ?>/partner/dashboard/getRevenueChartData')
            .then(response => response.json())
            .then(apiData => {
                const daysInMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).getDate();
                const labels = Array.from({
                    length: daysInMonth
                }, (_, i) => i + 1);
                const revenueValues = Array(daysInMonth).fill(0);
                apiData.forEach(item => {
                    revenueValues[item.day - 1] = item.revenue;
                });

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
            });
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
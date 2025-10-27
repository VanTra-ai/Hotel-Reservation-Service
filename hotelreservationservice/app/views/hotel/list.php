<?php include 'app/views/shares/header.php';
//app/views/hotel/list.php
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4 text-center">
        Danh sách Khách sạn tại <?= htmlspecialchars($data['provinceName'] ?? 'Tất cả') ?>
    </h2>
    <p class="text-muted mb-4 text-center">
        Tìm kiếm chỗ ở hoàn hảo cho chuyến đi của bạn.
    </p>

    <?php if (empty($data['hotels'])): ?>
        <div class="alert alert-info text-center" role="alert">
            Hiện tại chưa có khách sạn nào được thêm cho tỉnh thành này.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($data['hotels'] as $hotel): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($hotel->image ?? 'public/images/placeholder.png') ?>"
                            class="card-img-top" style="height: 200px; object-fit: cover;"
                            alt="<?= htmlspecialchars($hotel->name) ?>">

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($hotel->name) ?></h5>
                            <p class="card-text text-muted flex-grow-1">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                <?= htmlspecialchars($hotel->address) ?>
                            </p>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning text-dark">
                                    <?= number_format($hotel->rating ?? 0, 1) ?>/10
                                </span>

                                <a href="<?= BASE_URL ?>/hotel/show/<?= $hotel->id ?>?dates=<?= urlencode($data['dates_raw']) ?>"
                                    class="btn btn-primary btn-sm">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
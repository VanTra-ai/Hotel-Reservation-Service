<?php
include 'app/views/shares/header.php';
// Đảm bảo đã gọi helper để sử dụng hàm getTextFromScore
require_once 'app/helpers/RatingHelper.php';
?>

<div class="container my-5">
    <?php if ($hotel): ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <?php if (!empty($hotel->image)): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($hotel->image) ?>" class="img-fluid rounded mb-3" alt="<?= htmlspecialchars($hotel->name) ?>">
                        <?php endif; ?>
                        <h2 class="card-title fw-bold"><?= htmlspecialchars($hotel->name) ?></h2>
                        <p class="card-text text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($hotel->address) ?></p>
                        <p class="card-text text-muted"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($hotel->phone ?? 'Chưa có số điện thoại') ?></p>
                        <hr>
                        <h5 class="fw-bold">Mô tả</h5>
                        <p><?= nl2br(htmlspecialchars($hotel->description)) ?></p>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Các loại phòng có sẵn</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($room->room_type) ?></strong>
                                        <p class="mb-0 text-muted">Sức chứa: <?= htmlspecialchars($room->capacity) ?> người</p>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-success d-block mb-1"><?= number_format($room->price, 0, ',', '.') ?> VNĐ/đêm</span>
                                        <a href="<?= BASE_URL ?>/booking/bookRoom?room_id=<?= $room->id ?>" class="btn btn-primary btn-sm">Đặt ngay</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-info">Hiện tại không có phòng trống.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="mt-4">
                    <h4 class="mb-3">Khách lưu trú ở đây thích điều gì?</h4>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0 me-3 text-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;"><?= strtoupper(substr($review->username, 0, 1)) ?></div>
                                </div>
                                <div class="flex-grow-1 border-start ps-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="fw-bold"><?= htmlspecialchars($review->username) ?></span>
                                            <small class="text-muted">• <?= htmlspecialchars($review->country ?? 'Việt Nam') ?></small>
                                        </div>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($review->created_at)) ?></small>
                                    </div>
                                    <h5 class="fw-bold my-1"><?= htmlspecialchars($review->rating_text ?? $review->rating); ?></h5>
                                    <?php if (!empty($review->comment)): ?>
                                        <p class="mb-0">"<?= nl2br(htmlspecialchars($review->comment, ENT_QUOTES, 'UTF-8')) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-info">Chưa có đánh giá nào cho khách sạn này.</p>
                    <?php endif; ?>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Đánh giá của khách</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <h3 class="mb-0 fw-bold"><?= number_format((float)($hotel->rating ?? 0), 1) ?></h3>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0"><?= RatingHelper::getTextFromScore($hotel->rating) ?></h6>
                                <span class="text-muted" style="font-size: 0.9em;">Dựa trên <?= $hotel->total_rating ?? 0 ?> đánh giá</span>
                            </div>
                        </div>
                        <?php
                        $categories = [
                            'service_staff' => 'Nhân viên',
                            'amenities' => 'Tiện nghi',
                            'cleanliness' => 'Sạch sẽ',
                            'comfort' => 'Thoải mái',
                            'value_for_money' => 'Đáng giá tiền',
                            'location' => 'Địa điểm',
                            'free_wifi' => 'WiFi miễn phí'
                        ];
                        ?>
                        <?php foreach ($categories as $key => $label): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between" style="font-size: 0.9em;">
                                    <span><?= $label ?></span>
                                    <span class="fw-bold"><?= number_format((float)($hotel->$key ?? 0), 1) ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= (($hotel->$key ?? 0) * 10) ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card shadow-sm mt-4 bg-light-subtle border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Thử nghiệm với AI 🤖</h5>
                        <p class="card-text">Dự đoán điểm số cho một bình luận về khách sạn này.</p>
                        <a href="<?= BASE_URL ?>/ai?hotel_id=<?= $hotel->id ?>" class="btn btn-info text-dark fw-bold">
                            <i class="fas fa-magic me-2"></i>Thử nghiệm Điểm AI
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-danger text-center" role="alert">Không tìm thấy khách sạn này.</div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
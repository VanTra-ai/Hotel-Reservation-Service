<?php
// app/views/hotel/show.php
include 'app/views/shares/header.php';
require_once 'app/helpers/RatingHelper.php';

$hotel = $data['hotel'] ?? null;
$rooms = $data['rooms'] ?? [];
$reviews = $data['reviews'] ?? [];
$check_in = $data['check_in'] ?? '';
$check_out = $data['check_out'] ?? '';

// Định nghĩa 7 tiêu chí đánh giá (Dùng cho hiển thị cột phải)
$criteria_map = [
    'service_staff' => 'Nhân viên',
    'amenities' => 'Tiện nghi',
    'cleanliness' => 'Sạch sẽ',
    'comfort' => 'Thoải mái',
    'value_for_money' => 'Đáng giá tiền',
    'location' => 'Địa điểm',
    'free_wifi' => 'WiFi miễn phí'
];
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

                <div class="card mb-4 shadow-sm bg-light-subtle">
                    <div class="card-body">
                        <h5 class="card-title">Kiểm tra phòng trống</h5>
                        <input type="hidden" id="hotel_id_for_ajax" value="<?= $hotel->id ?>">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label for="ajax_check_in" class="form-label">Ngày nhận phòng</label>
                                <input type="text" id="ajax_check_in" class="form-control" placeholder="Chọn ngày"
                                    value="<?= htmlspecialchars($check_in) ?>">
                            </div>
                            <div class="col-md-5">
                                <label for="ajax_check_out" class="form-label">Ngày trả phòng</label>
                                <input type="text" id="ajax_check_out" class="form-control" placeholder="Chọn ngày"
                                    value="<?= htmlspecialchars($check_out) ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button id="filter-rooms-btn" class="btn btn-primary w-100"
                                    <?= (!empty($check_in)) ? 'data-autorun="true"' : '' ?>>
                                    Kiểm tra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Các loại phòng có sẵn</h5>
                    </div>
                    <ul class="list-group list-group-flush" id="room-list-container">
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($room->room_type) ?></strong>
                                        <p class="mb-0 text-muted">Sức chứa: <?= htmlspecialchars($room->capacity) ?> người</p>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-success d-block mb-1"><?= number_format($room->price, 0, ',', '.') ?> VNĐ/đêm</span>
                                        <?php
                                        $date_query = '';
                                        if (!empty($check_in) && !empty($check_out)) {
                                            $date_query = '&check_in=' . htmlspecialchars($check_in) . '&check_out=' . htmlspecialchars($check_out);
                                        }
                                        ?>
                                        <a href="<?= BASE_URL ?>/booking/bookRoom?room_id=<?= $room->id ?><?= $date_query ?>" class="btn btn-primary btn-sm">Đặt ngay</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-info">Hiện tại không có phòng trống.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="mt-4">
                    <h4 class="mb-3">Khách lưu trú ở đây thích điều gì? (<?= count($reviews) ?> đánh giá)</h4>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="d-flex mb-4 p-3 border rounded shadow-sm bg-white">
                                <div class="flex-shrink-0 me-3 text-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                        <?= strtoupper(substr($review->username, 0, 1)) ?>
                                    </div>
                                    <small class="d-block mt-1 text-muted" style="font-size: 0.8em;"><?= htmlspecialchars($review->country ?? 'Việt Nam') ?></small>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-bold"><?= htmlspecialchars($review->username) ?></span>
                                            <small class="text-muted">• <?= date('d/m/Y', strtotime($review->created_at)) ?></small>

                                            <?php if (isset($review->ai_rating) && $review->ai_rating !== null): // Kiểm tra null rõ ràng 
                                            ?>
                                                <span class="badge bg-primary ms-2 fs-6">
                                                    <?= number_format((float)$review->ai_rating, 1) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <h5 class="fw-bold mt-1 mb-2"><?= htmlspecialchars($review->rating_text ?? 'Chưa có đánh giá') ?></h5>

                                    <?php if ($review->booking_id): // Kiểm tra có booking_id không 
                                    ?>
                                        <p class="mb-1 text-muted" style="font-size: 0.9em;">
                                            <i class="fas fa-bed me-1"></i> Phòng: <?= htmlspecialchars($review->room_type ?? 'N/A') ?>
                                            <?php if (isset($review->nights) && $review->nights !== null): ?>
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-clock me-1"></i> Lưu trú: <?= htmlspecialchars($review->nights) ?> đêm
                                            <?php endif; ?>
                                            <?php if (!empty($review->group_type)): ?>
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-users me-1"></i> Nhóm: <?= htmlspecialchars($review->group_type) ?>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($review->comment)): ?>
                                        <p class="mb-0 fst-italic">"<?= nl2br(htmlspecialchars($review->comment, ENT_QUOTES, 'UTF-8')) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-info">Chưa có đánh giá nào cho khách sạn này. Hãy là người đầu tiên!</p>
                    <?php endif; ?>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Điểm đánh giá trung bình</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <h3 class="mb-0 fw-bold"><?= number_format((float)($hotel->rating ?? 0), 1) ?></h3>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0"><?= RatingHelper::getTextFromScore($hotel->rating) ?></h6>
                                <span class="text-muted" style="font-size: 0.9em;">Dựa trên <?= $hotel->total_rating ?? 0 ?> đánh giá</span>
                            </div>
                        </div>

                        <?php foreach ($criteria_map as $key => $label): ?>
                            <?php $score = $hotel->$key ?? 0; // Lấy điểm từ thuộc tính của $hotel 
                            ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between" style="font-size: 0.9em;">
                                    <span><?= $label ?></span>
                                    <span class="fw-bold"><?= number_format((float)$score, 1) ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= (($score / 10) * 100) ?>%;"></div>
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

<script src="<?= BASE_URL ?>/public/js/hotel_detail.js"></script>

<?php include 'app/views/shares/footer.php'; ?>
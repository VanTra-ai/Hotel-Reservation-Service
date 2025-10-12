<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <?php if ($hotel): ?>
        <div class="row">
            <?php if (!empty($hotel->image)): ?>
                <img src="/Hotel-Reservation-Service/hotelreservationservice/<?= htmlspecialchars($hotel->image) ?>"
                    class="card-img-top hotel-detail-image"
                    alt="<?= htmlspecialchars($hotel->name) ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/1080x720?text=No+Image+Available"
                    class="card-img-top hotel-detail-image" alt="No image available">
            <?php endif; ?>

            <div class="card-body">
                <h2 class="card-title fw-bold"><?= htmlspecialchars($hotel->name) ?></h2>
                <p class="card-text text-muted mb-1">
                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($hotel->address) ?>
                </p>
                <p class="card-text text-muted">
                    <i class="fas fa-phone"></i> <?= htmlspecialchars($hotel->phone ?? 'Chưa có số điện thoại') ?>
                </p>


                <?php if (!empty($hotel->category_name)): ?>
                    <span class="badge bg-info text-dark">
                        <i class="fas fa-hotel"></i> <?= htmlspecialchars($hotel->category_name) ?>
                    </span>
                <?php endif; ?>

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
                                <p class="mb-0 text-muted">Số phòng: <?= htmlspecialchars($room->room_number) ?></p>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-success d-block mb-1">
                                    <?= number_format($room->price, 0, ',', '.') ?> VNĐ/đêm
                                </span>
                                <a href="/Hotel-Reservation-Service/hotelreservationservice/booking/bookRoom?room_id=<?= $room->id ?>"
                                    class="btn btn-primary btn-sm">Đặt ngay</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-center text-info">
                        Hiện tại không có phòng trống.
                    </li>
                <?php endif; ?>
            </ul>
        </div>


        <?php if (!empty($averageRatings)): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Hạng mục đánh giá</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($averageRatings as $avg): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><?= htmlspecialchars($avg->category) ?></span>
                                    <span class="fw-bold"><?= number_format($avg->avg_rating, 1) ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: <?= $avg->avg_rating * 10 ?>%;"
                                        aria-valuenow="<?= $avg->avg_rating ?>" aria-valuemin="0" aria-valuemax="10"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Đánh giá của khách hàng</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="mb-3 border-bottom pb-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($review->username) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($review->created_at) ?></small>
                            </div>

                            <p class="mb-1">
                                <strong>Điểm: </strong>
                                <span class="text-primary"><?= number_format($review->rating, 1) ?>/10</span>
                            </p>


                            <?php if (!empty($review->comment)): ?>
                                <p class="mb-0">
                                    <?= nl2br(htmlspecialchars($review->comment, ENT_QUOTES, 'UTF-8')) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted fst-italic mb-0">Người dùng không để lại bình luận.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-info">Chưa có đánh giá nào cho khách sạn này. Hãy là người đầu tiên!</p>
                <?php endif; ?>
            </div>
        </div>


        <!-- Form thêm đánh giá -->
        <div class="card-footer">
            <form id="addReviewForm" method="POST" action="/Hotel-Reservation-Service/hotelreservationservice/Review/add">
                <input type="hidden" name="hotel_id" value="<?= htmlspecialchars($hotel->id) ?>">

                <!-- Rating -->
                <div class="mb-3">
                    <label for="rating" class="form-label">Đánh giá của bạn</label>
                    <div class="rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" data-rating="<?= $i ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" required>
                </div>

                <!-- Comment -->
                <div class="mb-3">
                    <label for="comment" class="form-label">Bình luận</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
            </form>
        </div>
</div>
</div>


<div class="col-md-4">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Tổng quan</h5>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <strong>Đánh giá:</strong>
                <span class="badge bg-warning text-dark">
                    <?= number_format($hotel->rating, 1) ?>/10
                </span>
                (<?= $hotel->total_rating ?> đánh giá)
            </li>
            <?php if (!empty($hotel->category_name)): ?>
                <li class="list-group-item">
                    <strong>Hạng mục:</strong> <?= htmlspecialchars($hotel->category_name) ?>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</div>
<?php else: ?>
    <div class="alert alert-danger text-center" role="alert">
        Không tìm thấy khách sạn này.
    </div>
<?php endif; ?>
</div>

<!-- Script chọn sao -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.rating-stars .fa-star');
        const ratingInput = document.getElementById('ratingInput');

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const ratingValue = star.getAttribute('data-rating');
                ratingInput.value = ratingValue;

                stars.forEach(s => {
                    s.classList.remove('text-warning');
                    if (s.getAttribute('data-rating') <= ratingValue) {
                        s.classList.add('text-warning');
                    }
                });
            });
        });
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <!-- TÌM KHÁCH SẠN -->
    <div class="search-section bg-primary text-white p-4 rounded-3 shadow-lg">
        <h2 class="fw-bold mb-4 text-center">Tìm khách sạn lý tưởng của bạn</h2>
        <form id="searchForm" method="get" action="/Hotel-Reservation-Service/hotelreservationservice/Hotel/list">
            <div class="row g-3 justify-content-center align-items-center">

                <!-- TỈNH / THÀNH -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-white rounded-start-pill">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                        </span>
                        <input type="text" id="provinceInput" name="province"
                            class="form-control border-start-0 ps-0 rounded-end-pill"
                            placeholder="Chọn tỉnh thành...">
                    </div>
                    <div id="provinceList" class="dropdown-menu"></div>
                </div>

                <!-- NGÀY NHẬN - TRẢ -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-white rounded-start-pill">
                            <i class="fas fa-calendar-alt text-muted"></i>
                        </span>
                        <input type="text" id="dateRangeInput" name="dates"
                            class="form-control border-start-0 ps-0 rounded-end-pill"
                            placeholder="Ngày nhận phòng - Ngày trả phòng">
                    </div>
                </div>

                <!-- KHÁCH & PHÒNG -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-white rounded-start-pill">
                            <i class="fas fa-user-friends text-muted"></i>
                        </span>
                        <button type="button" class="form-control text-start border-start-0 ps-0 rounded-end-pill"
                            data-bs-toggle="modal" data-bs-target="#guestsModal">
                            <span id="guestsSummary" class="text-muted">1 người lớn, 0 trẻ em, 1 phòng</span>
                        </button>
                    </div>
                </div>

                <!-- NÚT TÌM -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning btn-lg w-100 rounded-pill fw-bold">Tìm</button>
                </div>
            </div>
        </form>
    </div>

    <!-- CAROUSEL ẢNH CHÍNH -->
    <div id="mainCarousel" class="carousel slide my-5" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="/Hotel-Reservation-Service/hotelreservationservice/public/images/carousel/carousel1.jfif"
                    class="d-block w-100 rounded-3 shadow-sm main-carousel-img" alt="carousel1">
            </div>
            <div class="carousel-item">
                <img src="/Hotel-Reservation-Service/hotelreservationservice/public/images/carousel/carousel2.jpg"
                    class="d-block w-100 rounded-3 shadow-sm main-carousel-img" alt="carousel2">
            </div>
            <div class="carousel-item">
                <img src="/Hotel-Reservation-Service/hotelreservationservice/public/images/carousel/carousel3.jpg"
                    class="d-block w-100 rounded-3 shadow-sm main-carousel-img" alt="carousel3">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- KHÁM PHÁ VIỆT NAM -->
    <div class="explore-section mt-5">
        <h3 class="fw-bold mb-4 text-center">Khám phá Việt Nam</h3>
        <p class="text-muted mb-4 text-center">Các điểm đến phổ biến này có nhiều điều chờ đón bạn</p>

        <div id="provinceCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php if (isset($provinces) && is_array($provinces)):
                    $chunks = array_chunk($provinces, 6);
                    foreach ($chunks as $index => $chunk): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="row g-3">
                                <?php foreach ($chunk as $p):
                                    $provinceName = $p->name ?? '';
                                    $provinceImage = $p->image ?? '';
                                ?>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <a href="/Hotel-Reservation-Service/hotelreservationservice/Hotel/list?province=<?= urlencode($provinceName) ?>"
                                            class="image-link">
                                            <?php if (!empty($provinceImage)): ?>
                                                <img src="/Hotel-Reservation-Service/hotelreservationservice/<?= htmlspecialchars($provinceImage) ?>"
                                                    class="d-block w-100 rounded-3 shadow-sm explore-image"
                                                    alt="<?= htmlspecialchars($provinceName) ?>">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/150"
                                                    class="d-block w-100 rounded-3 shadow-sm explore-image"
                                                    alt="No image available">
                                            <?php endif; ?>
                                            <div class="image-text"><?= htmlspecialchars($provinceName) ?></div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#provinceCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#provinceCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL CHỌN KHÁCH & PHÒNG -->
<div class="modal fade" id="guestsModal" tabindex="-1" aria-labelledby="guestsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="guestsModalLabel">Khách & Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php
                $fields = [
                    ['label' => 'Người lớn', 'id' => 'adultsCount', 'value' => 1],
                    ['label' => 'Trẻ em', 'id' => 'childrenCount', 'value' => 0],
                    ['label' => 'Phòng', 'id' => 'roomsCount', 'value' => 1]
                ];
                foreach ($fields as $f): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><?= $f['label'] ?></span>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="updateGuests('<?= strtolower(explode(' ', $f['label'])[0]) ?>', -1)">-</button>
                            <span id="<?= $f['id'] ?>" class="mx-2 fw-bold"><?= $f['value'] ?></span>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="updateGuests('<?= strtolower(explode(' ', $f['label'])[0]) ?>', 1)">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Xong</button>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
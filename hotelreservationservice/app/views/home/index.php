<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="search-section bg-primary text-white p-4 rounded-3 shadow-lg">
        <h2 class="fw-bold mb-4 text-center">Tìm khách sạn lý tưởng của bạn</h2>
        <form id="searchForm" method="get" action="/hotelreservationservice/Hotel/list">
            <div class="row g-3 justify-content-center align-items-center">

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-white rounded-start-pill"><i class="fas fa-map-marker-alt text-muted"></i></span>
                        <input type="text" id="provinceInput" name="province" class="form-control border-start-0 ps-0 rounded-end-pill" placeholder="Chọn tỉnh thành...">
                    </div>
                    <div id="provinceList" class="dropdown-menu">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-white rounded-start-pill"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <input type="text" id="dateRangeInput" name="dates" class="form-control border-start-0 ps-0 rounded-end-pill" placeholder="Ngày nhận phòng - Ngày trả phòng">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-white rounded-start-pill"><i class="fas fa-user-friends text-muted"></i></span>
                        <button type="button" class="form-control text-start border-start-0 ps-0 rounded-end-pill" data-bs-toggle="modal" data-bs-target="#guestsModal">
                            <span id="guestsSummary" class="text-muted">1 người lớn, 0 trẻ em, 1 phòng</span>
                        </button>
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning btn-lg w-100 rounded-pill fw-bold">Tìm</button>
                </div>
            </div>
        </form>
    </div>

    <div id="carouselExampleIndicators" class="carousel slide my-5" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div id="carouselExampleIndicators" class="carousel slide my-5" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="/hotelreservationservice/public/images/carousel/carousel1.jfif" class="d-block w-100 rounded-3 shadow-sm main-carousel-img" alt="carousel1">
                </div>
                <div class="carousel-item">
                    <img src="/hotelreservationservice/public/images/carousel/carousel2.jpg" class="d-block w-100 rounded-3 shadow-sm main-carousel-img" alt="carousel2">
                </div>
                <div class="carousel-item">
                    <img src="/hotelreservationservice/public/images/carousel/carousel3.jpg" class="d-block w-100 rounded-3 shadow-sm main-carousel-img" alt="carousel3">
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="explore-section mt-5">
        <h3 class="fw-bold mb-4 text-center">Khám phá Việt Nam</h3>
        <p class="text-muted mb-4 text-center">Các điểm đến phổ biến này có nhiều điều chờ đón bạn</p>

        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Kiểm tra xem biến $provinces có tồn tại và là mảng không
                if (isset($provinces) && is_array($provinces)) {
                    $chunked_provinces = array_chunk($provinces, 6);
                    foreach ($chunked_provinces as $index => $chunk):
                ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="row g-3">
                                <?php foreach ($chunk as $p): ?>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <?php
                                        // Sử dụng toán tử null coalescing (??) để đảm bảo không có giá trị null
                                        $provinceName = $p->name ?? '';
                                        $provinceImage = $p->image ?? '';
                                        ?>
                                        <a href="/hotelreservationservice/Hotel/list?province=<?= urlencode($provinceName) ?>" class="image-link">
                                            <?php if (!empty($provinceImage)): ?>
                                                <img src="/hotelreservationservice/<?= htmlspecialchars($provinceImage) ?>" class="d-block w-100 rounded-3 shadow-sm explore-image" alt="<?= htmlspecialchars($provinceName) ?>">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/100" class="d-block w-100 rounded-3 shadow-sm explore-image" alt="No image available">
                                            <?php endif; ?>
                                            <div class="image-text"><?= htmlspecialchars($provinceName) ?></div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                <?php
                    endforeach;
                }
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="guestsModal" tabindex="-1" aria-labelledby="guestsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="guestsModalLabel">Khách & Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Người lớn</span>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateGuests('adults', -1)">-</button>
                        <span id="adultsCount" class="mx-2 fw-bold">1</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateGuests('adults', 1)">+</button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Trẻ em</span>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateGuests('children', -1)">-</button>
                        <span id="childrenCount" class="mx-2 fw-bold">0</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateGuests('children', 1)">+</button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Phòng</span>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateGuests('rooms', -1)">-</button>
                        <span id="roomsCount" class="mx-2 fw-bold">1</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateGuests('rooms', 1)">+</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Xong</button>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
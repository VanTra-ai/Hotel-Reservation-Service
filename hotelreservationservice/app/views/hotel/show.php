<?php
// app/views/hotel/show.php
include 'app/views/shares/header.php';
require_once 'app/helpers/RatingHelper.php';

// Lấy các biến từ mảng $data mà Controller đã gửi
$hotel = $data['hotel'] ?? null;
$roomTypes = $data['roomTypes'] ?? []; // Dùng biến này
$reviews = $data['reviews'] ?? [];
$check_in = $data['check_in'] ?? '';
$check_out = $data['check_out'] ?? '';
$pagination = $data['review_pagination'] ?? [ // Lấy thông tin phân trang
    'current_page' => 1,
    'total_pages' => 1,
    'total_reviews' => 0
];

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

            <!-- CỘT NỘI DUNG CHÍNH (BÊN TRÁI) -->
            <div class="col-lg-8">
                <!-- 1. THÔNG TIN KHÁCH SẠN (Giữ nguyên) -->
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

                <!-- 2. KHỐI TÌM PHÒNG TRỐNG (Giữ nguyên) -->
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

                <!-- 3. DANH SÁCH CÁC LOẠI PHÒNG (Giữ nguyên) -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Các loại phòng có sẵn</h5>
                    </div>
                    <ul class="list-group list-group-flush" id="room-list-container">
                        <?php if (!empty($roomTypes)): ?>
                            <?php foreach ($roomTypes as $roomTypeInfo): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($roomTypeInfo->room_type) ?></strong>
                                        <p class="mb-0 text-muted">Sức chứa: <?= htmlspecialchars($roomTypeInfo->capacity) ?> người</p>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted d-block mb-1" style="font-size: 0.8em;">Giá chỉ từ</span>
                                        <span class="fw-bold text-success d-block mb-1">
                                            <?= number_format($roomTypeInfo->min_price, 0, ',', '.') ?> VNĐ/đêm
                                        </span>
                                        <button class="btn btn-secondary btn-sm check-availability-btn"
                                            data-room-type="<?= htmlspecialchars($roomTypeInfo->room_type) ?>">
                                            Chọn phòng
                                        </button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-info">Hiện tại khách sạn này chưa có phòng nào.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- 3.5 HIỂN THỊ CÁC PHÒNG TRỐNG CHI TIẾT (Giữ nguyên) -->
                <div id="available-rooms-details" class="mt-4" style="display: none;">
                    <h5 class="mb-3">Phòng trống chi tiết cho ngày đã chọn:</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item text-center text-muted">Vui lòng chọn ngày và nhấn "Kiểm tra" để xem phòng trống.</li>
                    </ul>
                    <!-- Container cho phân trang phòng trống -->
                    <div id="available-rooms-pagination" class="mt-4"></div>
                </div>

                <!-- 4. KHỐI BÌNH LUẬN ĐÃ ĐƯỢC DI CHUYỂN SANG CỘT PHẢI -->

            </div> <!-- <<< Kết thúc col-lg-8 (cột trái) -->

            <!-- CỘT BÊN PHẢI (THÔNG TIN ĐÁNH GIÁ) -->
            <div class="col-lg-4">

                <!-- 5. KHU VỰC ĐÁNH GIÁ TỔNG HỢP (Giữ nguyên) -->
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
                                <span class="text-muted" style="font-size: 0.9em;">Dựa trên <?= $pagination['total_reviews'] ?? 0 ?> đánh giá</span> <!-- Sửa: dùng $pagination -->
                            </div>
                        </div>
                        <?php foreach ($criteria_map as $key => $label): ?>
                            <?php $score = $hotel->$key ?? 0; ?>
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

                <!-- 6. NÚT DẪN ĐẾN AI PLAYGROUND (Giữ nguyên) -->
                <div class="card shadow-sm mt-4 bg-light-subtle border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Thử nghiệm với AI 🤖</h5>
                        <p class="card-text">Dự đoán điểm số cho một bình luận về khách sạn này.</p>
                        <a href="<?= BASE_URL ?>/ai?hotel_id=<?= $hotel->id ?>" class="btn btn-info text-dark fw-bold">
                            <i class="fas fa-magic me-2"></i>Thử nghiệm Điểm AI
                        </a>
                    </div>
                </div>

                <!-- <<< 4. DANH SÁCH BÌNH LUẬN (ĐÃ DI CHUYỂN ĐẾN ĐÂY) >>> -->
                <div class="mt-5">
                    <h4 class="mb-3">Khách lưu trú ở đây thích điều gì? (<?= $pagination['total_reviews'] ?> đánh giá)</h4> <!-- Sửa: dùng $pagination -->
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <!-- ... (Code hiển thị từng review card giữ nguyên) ... -->
                            <div class="d-flex mb-4 p-3 border rounded shadow-sm bg-white">
                                <div class="flex-shrink-0 me-3 text-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                        <?= strtoupper(substr($review->fullname, 0, 1)) ?>
                                    </div>
                                    <small class="d-block mt-1 text-muted" style="font-size: 0.8em;"><?= htmlspecialchars($review->country ?? 'Việt Nam') ?></small>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-bold"><?= htmlspecialchars($review->fullname) ?></span> <!-- ĐÃ SỬA: fullname -->
                                            <small class="text-muted">• <?= date('d/m/Y', strtotime($review->created_at)) ?></small>
                                            <?php if (isset($review->ai_rating) && $review->ai_rating !== null): ?>
                                                <span class="badge bg-primary ms-2 fs-6">
                                                    <?= number_format((float)$review->ai_rating, 1) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold mt-1 mb-2"><?= htmlspecialchars($review->rating_text ?? 'Chưa có đánh giá') ?></h5>
                                    <?php if ($review->booking_id): ?>
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

                        <!-- <<< KHỐI PHÂN TRANG ĐÃ SỬA (SLIDING WINDOW) >>> -->
                        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                            <nav aria-label="Trang bình luận">
                                <ul class="pagination pagination-sm justify-content-center mt-4">

                                    <?php
                                    $currentPage = $pagination['current_page'];
                                    $totalPages = $pagination['total_pages'];
                                    $window = 1; // Số trang hiển thị ở mỗi bên (VD: 12, [13], 14)
                                    ?>

                                    <!-- Nút Trang trước -->
                                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                        <?php
                                        $prevParams = array_merge($_GET, ['review_page' => $currentPage - 1]);
                                        ?>
                                        <a class="page-link" href="?<?= http_build_query($prevParams) ?>">Trước</a>
                                    </li>

                                    <!-- Hiển thị trang 1 -->
                                    <?php
                                    $pageParams = array_merge($_GET, ['review_page' => 1]);
                                    ?>
                                    <li class="page-item <?= (1 == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query($pageParams) ?>">1</a>
                                    </li>

                                    <!-- Dấu ... (bên trái) -->
                                    <?php if ($currentPage > $window + 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>

                                    <!-- Các trang ở giữa (cửa sổ trượt) -->
                                    <?php
                                    $start = max(2, $currentPage - $window);
                                    $end = min($totalPages - 1, $currentPage + $window);

                                    for ($i = $start; $i <= $end; $i++):
                                        $pageParams = array_merge($_GET, ['review_page' => $i]);
                                    ?>
                                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Dấu ... (bên phải) -->
                                    <?php if ($currentPage < $totalPages - $window - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>

                                    <!-- Hiển thị trang cuối (nếu không phải là trang 1) -->
                                    <?php if ($totalPages > 1): ?>
                                        <?php
                                        $pageParams = array_merge($_GET, ['review_page' => $totalPages]);
                                        ?>
                                        <li class="page-item <?= ($totalPages == $currentPage) ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $totalPages ?></a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Nút Trang sau -->
                                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                                        <?php
                                        $nextParams = array_merge($_GET, ['review_page' => $currentPage + 1]);
                                        ?>
                                        <a class="page-link" href="?<?= http_build_query($nextParams) ?>">Sau</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        <!-- <<< KẾT THÚC KHỐI PHÂN TRANG >>> -->

                    <?php else: ?>
                        <p class="text-info">Chưa có đánh giá nào cho khách sạn này. Hãy là người đầu tiên!</p>
                    <?php endif; ?>
                </div>
                <!-- <<< KẾT THÚC KHỐI BÌNH LUẬN ĐÃ DI CHUYỂN >>> -->

            </div> <!-- Kết thúc col-lg-4 (cột phải) -->
        </div> <!-- Kết thúc row -->

    <?php else: ?>
        <div class="alert alert-danger text-center" role="alert">Không tìm thấy khách sạn này.</div>
    <?php endif; ?>
</div>

<!-- Include script cho trang chi tiết khách sạn -->
<script src="<?= BASE_URL ?>/public/js/hotel_detail.js"></script>

<?php include 'app/views/shares/footer.php'; ?>
<?php
// app/views/hotel/show.php
include 'app/views/shares/header.php';
require_once 'app/helpers/RatingHelper.php';

// Lấy các biến từ mảng $data mà Controller đã gửi
$hotel = $data['hotel'] ?? null;
$hotelImages = $data['hotelImages'] ?? []; // Lấy mảng hình ảnh
$roomTypes = $data['roomTypes'] ?? [];
$reviews = $data['reviews'] ?? [];
$check_in = $data['check_in'] ?? '';
$check_out = $data['check_out'] ?? '';
$pagination = $data['review_pagination'] ?? [
    'current_page' => 1,
    'total_pages' => 1,
    'total_reviews' => 0
];

$criteria_map = [
    'service_staff' => 'Nhân viên',
    'amenities' => 'Tiện nghi',
    'cleanliness' => 'Sạch sẽ',
    'comfort' => 'Thoải mái',
    'value_for_money' => 'Đáng giá tiền',
    'location' => 'Địa điểm',
    'free_wifi' => 'WiFi miễn phí'
];

// Tìm ảnh thumbnail (ảnh chính) và index của nó
$thumbnailImage = null;
$thumbnailIndex = 0; // Mặc định là ảnh đầu tiên (index 0)
if (!empty($hotelImages)) {
    foreach ($hotelImages as $index => $img) { // Thêm $index
        if (isset($img->is_thumbnail) && $img->is_thumbnail) {
            $thumbnailImage = $img;
            $thumbnailIndex = $index; // Lưu lại index của ảnh thumbnail
            break;
        }
    }
    // Nếu không có ảnh nào được đánh dấu, lấy ảnh đầu tiên
    if (!$thumbnailImage && isset($hotelImages[0])) { // Kiểm tra $hotelImages[0] tồn tại
        $thumbnailImage = $hotelImages[0];
        $thumbnailIndex = 0;
    }
}
?>

<div class="container my-5">
    <?php if ($hotel): ?>
        <div class="row g-4">

            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">

                        <h2 class="card-title fw-bold"><?= htmlspecialchars($hotel->name) ?></h2>
                        <p class="card-text text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($hotel->address) ?></p>
                        <p class="card-text text-muted"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($hotel->phone ?? 'Chưa có số điện thoại') ?></p>

                        <?php if (!empty($hotelImages) && $thumbnailImage): ?>
                            <div class="my-4">
                                <div class="main-image-display position-relative mb-2 rounded overflow-hidden">
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($thumbnailImage->image_path) ?>"
                                        class="img-fluid w-100 rounded"
                                        alt="<?= htmlspecialchars($hotel->name) ?>"
                                        style="max-height: 500px; object-fit: cover; cursor: pointer;"
                                        data-bs-toggle="modal" data-bs-target="#imageGalleryModal"
                                        data-image-index="<?= $thumbnailIndex ?>"
                                        id="currentHotelImage"> <span class="position-absolute top-0 end-0 bg-dark text-white px-2 py-1 rounded-bottom-start"
                                        style="font-size: 0.8em; opacity: 0.8;">
                                        Click để xem toàn bộ ảnh
                                    </span>
                                </div>

                                <div class="thumbnail-gallery d-flex flex-wrap gap-2 justify-content-start">
                                    <?php foreach ($hotelImages as $index => $img): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img->image_path) ?>"
                                            class="img-thumbnail rounded"
                                            alt="Ảnh khách sạn <?= $index + 1 ?>"
                                            style="width: 100px; height: 75px; object-fit: cover; cursor: pointer;"
                                            data-bs-toggle="modal" data-bs-target="#imageGalleryModal"
                                            data-image-index="<?= $index ?>">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php elseif (!empty($hotel->image)): // Fallback nếu chỉ có 1 ảnh cũ 
                        ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($hotel->image) ?>" class="img-fluid rounded mb-3" alt="<?= htmlspecialchars($hotel->name) ?>">
                        <?php endif; ?>

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

                <div id="available-rooms-details" class="mt-4" style="display: none;">
                    <h5 class="mb-3">Phòng trống chi tiết cho ngày đã chọn:</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item text-center text-muted">Vui lòng chọn ngày và nhấn "Kiểm tra" để xem phòng trống.</li>
                    </ul>
                    <div id="available-rooms-pagination" class="mt-4"></div>
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
                                <span class="text-muted" style="font-size: 0.9em;">Dựa trên <?= $pagination['total_reviews'] ?? 0 ?> đánh giá</span>
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

                <div class="card shadow-sm mt-4 bg-light-subtle border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Thử nghiệm với AI 🤖</h5>
                        <p class="card-text">Dự đoán điểm số cho một bình luận về khách sạn này.</p>
                        <a href="<?= BASE_URL ?>/ai?hotel_id=<?= $hotel->id ?>" class="btn btn-info text-dark fw-bold">
                            <i class="fas fa-magic me-2"></i>Thử nghiệm Điểm AI
                        </a>
                    </div>
                </div>

                <div class="mt-5">
                    <h4 class="mb-3">Khách lưu trú ở đây thích điều gì? (<?= $pagination['total_reviews'] ?> đánh giá)</h4>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="d-flex mb-4 p-3 border rounded shadow-sm bg-white">

                                <div class="flex-shrink-0 me-3 text-center">
                                    <?php
                                    $avatarLetter = strtoupper(substr($review->fullname, 0, 1));
                                    $avatarImg = $review->profile_picture ?? null;
                                    ?>

                                    <?php if ($avatarImg): // 1. Nếu có link ảnh 
                                    ?>
                                        <img src="<?= htmlspecialchars($avatarImg) ?>"
                                            class="rounded-circle"
                                            style="width: 48px; height: 48px; object-fit: cover;"
                                            alt="<?= htmlspecialchars($review->fullname) ?>"
                                            onerror="this.style.display='none'; this.parentElement.querySelector('.avatar-fallback').style.display='flex';">

                                    <?php else: // 2. Nếu không có link ảnh (NULL) 
                                    ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                            style="width: 48px; height: 48px; font-size: 1.5rem;">
                                            <?= $avatarLetter ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-1 text-muted" style="font-size: 0.8em; display: flex; align-items: center; justify-content: center; gap: 4px;">

                                        <span><?= htmlspecialchars($review->country ?? 'Việt Nam') ?></span>
                                    </div>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-bold"><?= htmlspecialchars($review->fullname) ?></span>
                                            <small class="text-muted">• <?= date('d/m/Y', strtotime($review->created_at)) ?></small>
                                            <?php if (isset($review->ai_rating) && $review->ai_rating !== null): ?>
                                                <span class="badge bg-primary ms-2 fs-6"><?= number_format((float)$review->ai_rating, 1) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold mt-1 mb-2"><?= htmlspecialchars($review->rating_text ?? 'Chưa có đánh giá') ?></h5>

                                    <?php
                                    // Ưu tiên dữ liệu từ Booking (JOIN), nếu không có thì lấy từ cột review (Imported)
                                    $displayRoomType = $review->room_type ?? ($review->review_room_type ?? null);
                                    $displayNights = $review->nights ?? ($review->review_nights ?? null);
                                    $displayGroupType = $review->group_type ?? ($review->review_group_type ?? null);
                                    ?>

                                    <?php if ($displayRoomType || $displayNights || $displayGroupType): // Chỉ hiển thị nếu có ít nhất 1 thông tin 
                                    ?>
                                        <div class="mb-1 text-muted" style="font-size: 0.9em;">

                                            <?php if ($displayRoomType): ?>
                                                <p class="mb-0"> <i class="fas fa-bed me-1" style="width: 1.25em;"></i> <?= htmlspecialchars($displayRoomType) ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if ($displayNights): ?>
                                                <p class="mb-0">
                                                    <i class="fas fa-clock me-1" style="width: 1.25em;"></i>
                                                    <?= htmlspecialchars($displayNights) ?> đêm
                                                </p>
                                            <?php endif; ?>

                                            <?php if ($displayGroupType): ?>
                                                <p class="mb-0">
                                                    <i class="fas fa-users me-1" style="width: 1.25em;"></i>
                                                    <?= htmlspecialchars($displayGroupType) ?>
                                                </p>
                                            <?php endif; ?>

                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($review->comment)): ?>
                                        <p class="mb-0 fst-italic">"<?= nl2br(htmlspecialchars($review->comment, ENT_QUOTES, 'UTF-8')) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                            <nav aria-label="Trang bình luận">
                                <ul class="pagination pagination-sm justify-content-center mt-4">

                                    <?php
                                    $currentPage = $pagination['current_page'];
                                    $totalPages = $pagination['total_pages'];
                                    $window = 1;
                                    ?>

                                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                        <?php $prevParams = array_merge($_GET, ['review_page' => $currentPage - 1]); ?>
                                        <a class="page-link" href="?<?= http_build_query($prevParams) ?>">Trước</a>
                                    </li>

                                    <?php $pageParams = array_merge($_GET, ['review_page' => 1]); ?>
                                    <li class="page-item <?= (1 == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query($pageParams) ?>">1</a>
                                    </li>

                                    <?php if ($currentPage > $window + 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>

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

                                    <?php if ($currentPage < $totalPages - $window - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>

                                    <?php if ($totalPages > 1): ?>
                                        <?php $pageParams = array_merge($_GET, ['review_page' => $totalPages]); ?>
                                        <li class="page-item <?= ($totalPages == $currentPage) ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $totalPages ?></a>
                                        </li>
                                    <?php endif; ?>

                                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                                        <?php $nextParams = array_merge($_GET, ['review_page' => $currentPage + 1]); ?>
                                        <a class="page-link" href="?<?= http_build_query($nextParams) ?>">Sau</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <p class="text-info">Chưa có đánh giá nào cho khách sạn này. Hãy là người đầu tiên!</p>
                    <?php endif; ?>
                </div>

            </div>
        </div> <?php else: ?>
        <div class="alert alert-danger text-center" role="alert">Không tìm thấy khách sạn này.</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-labelledby="imageGalleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div id="hotelImageCarousel" class="carousel slide" data-bs-interval="false">
                    <div class="carousel-inner">
                        <?php if (empty($hotelImages)): // Fallback 
                        ?>
                            <div class="carousel-item active">
                                <img src="<?= BASE_URL ?>/public/images/placeholder.png" class="d-block w-100 rounded" alt="Không có ảnh" style="max-height: 80vh; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <?php
                            // Xác định ảnh placeholder
                            $placeholderImg = BASE_URL . '/public/images/placeholder.png';
                            ?>
                            <?php foreach ($hotelImages as $index => $img): ?>
                                <div class="carousel-item"> <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img->image_path) ?>"
                                        class="d-block w-100 rounded"
                                        alt="Hotel Image <?= $index + 1 ?>"
                                        style="max-height: 80vh; object-fit: contain;"
                                        onerror="this.onerror=null; this.src='<?= $placeholderImg ?>';"> </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (count($hotelImages) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#hotelImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Trước</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#hotelImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Sau</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/public/js/hotel_detail.js"></script>
<?php include 'app/views/shares/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageGalleryModal = document.getElementById('imageGalleryModal');
        if (imageGalleryModal) {

            imageGalleryModal.addEventListener('show.bs.modal', function(event) { // Dùng 'show.bs.modal'
                try {
                    const button = event.relatedTarget;
                    const imageIndex = parseInt(button.dataset.imageIndex || 0, 10);

                    const carouselElement = document.getElementById('hotelImageCarousel');
                    if (carouselElement) {

                        // Logic JS mới để đặt 'active'
                        const allItems = carouselElement.querySelectorAll('.carousel-item');
                        if (allItems.length > 0) {
                            allItems.forEach(item => item.classList.remove('active'));

                            if (allItems[imageIndex]) {
                                allItems[imageIndex].classList.add('active');
                            } else {
                                allItems[0].classList.add('active');
                            }
                        }

                        // Khởi tạo hoặc lấy instance và nhảy đến slide
                        const carousel = bootstrap.Carousel.getOrCreateInstance(carouselElement);
                        carousel.to(imageIndex);
                    }
                } catch (e) {
                    console.error("Lỗi khi khởi tạo carousel gallery:", e);
                }
            });
        }
    });
</script>
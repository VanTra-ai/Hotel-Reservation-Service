<?php include 'app/views/shares/header.php';
//app/views/hotel/list.php
$hotels = $data['hotels'] ?? [];
$pagination = $data['pagination'] ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$filters = $data['filters'] ?? []; // $filters['province'] sẽ chứa tên tỉnh

// Helper để tạo URL giữ lại các tham số GET hiện tại
function buildHotelListUrl($paramsToMerge)
{
    $currentParams = $_GET;
    $params = array_merge($currentParams, $paramsToMerge);
    if (isset($paramsToMerge['sort_by'])) unset($params['page']);

    return BASE_URL . '/hotel/list?' . http_build_query($params);
}
?>

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
    <hr>

    <h2 class="fw-bold mb-4 text-center">
        Danh sách Khách sạn tại <?= htmlspecialchars(!empty($filters['province']) ? $filters['province'] : 'tất cả tỉnh thành') ?>
        (<?= $pagination['total_items'] ?>)
    </h2>
    <p class="text-muted mb-4 text-center">
        Tìm kiếm chỗ ở hoàn hảo cho chuyến đi của bạn.
    </p>
    <div class="d-flex justify-content-end mb-3">
        <form action="<?= BASE_URL ?>/hotel/list" method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="province" value="<?= htmlspecialchars($filters['province'] ?? '') ?>">
            <input type="hidden" name="dates" value="<?= htmlspecialchars($filters['dates'] ?? '') ?>">

            <label for="sort_select" class="form-label mb-0 me-2">Sắp xếp:</label>
            <select id="sort_select" class="form-select form-select-sm" style="width: auto;"
                onchange="sortHotels(this.value)">
                <option value="rating-DESC" <?= ($filters['sort_by'] == 'rating' && $filters['order'] == 'DESC') ? 'selected' : '' ?>>
                    Điểm cao nhất
                </option>
                <option value="min_price-ASC" <?= ($filters['sort_by'] == 'min_price' && $filters['order'] == 'ASC') ? 'selected' : '' ?>>
                    Giá thấp nhất
                </option>
                <option value="name-ASC" <?= ($filters['sort_by'] == 'name' && $filters['order'] == 'ASC') ? 'selected' : '' ?>>
                    Theo tên (A-Z)
                </option>
            </select>
        </form>
    </div>

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
                            <div>
                                <?php if (isset($hotel->min_price) && $hotel->min_price > 0): ?>
                                    <span class="text-muted d-block" style="font-size: 0.8em;">Giá chỉ từ</span>
                                    <span class="fw-bold fs-5 text-success">
                                        <?= number_format($hotel->min_price, 0, ',', '.') ?> VNĐ/đêm
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted d-block" style="font-size: 0.9em;">Chưa có giá</span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning text-dark">
                                    <?= number_format($hotel->rating ?? 0, 1) ?>/10
                                </span>
                                <?php
                                $dateQuery = '';
                                if (!empty($filters['dates'])) {
                                    $dateQuery = 'dates=' . urlencode($filters['dates']);
                                }
                                ?>
                                <a href="<?= BASE_URL ?>/hotel/show/<?= $hotel->id ?>?<?= $dateQuery ?>"
                                    class="btn btn-primary btn-sm">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <nav aria-label="Phân trang khách sạn" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php
                    $currentPage = $pagination['current_page'];
                    $totalPages = $pagination['total_pages'];
                    $window = 2;
                    ?>

                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildHotelListUrl(['page' => $currentPage - 1]) ?>">Trước</a>
                    </li>

                    <li class="page-item <?= (1 == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= buildHotelListUrl(['page' => 1]) ?>">1</a>
                    </li>

                    <?php if (max(2, $currentPage - $window) > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>

                    <?php
                    $start = max(2, $currentPage - $window);
                    $end = min($totalPages - 1, $currentPage + $window);

                    for ($i = $start; $i <= $end; $i++):
                        if ($i <= 1 || $i >= $totalPages) continue;
                    ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= buildHotelListUrl(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if (min($totalPages - 1, $currentPage + $window) < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>

                    <?php if ($totalPages > 1): ?>
                        <li class="page-item <?= ($totalPages == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= buildHotelListUrl(['page' => $totalPages]) ?>"><?= $totalPages ?></a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildHotelListUrl(['page' => $currentPage + 1]) ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script src="<?= BASE_URL ?>/public/js/search_form.js"></script>
<?php include 'app/views/shares/footer.php'; ?>
<script>
    function sortHotels(value) {
        const [sortBy, order] = value.split('-'); // Tách "rating-DESC" thành "rating" và "DESC"
        const url = new URL(window.location.href);

        url.searchParams.set('sort_by', sortBy);
        url.searchParams.set('order', order);
        url.searchParams.delete('page'); // Quay về trang 1 khi sắp xếp

        window.location.href = url.href;
    }
</script>
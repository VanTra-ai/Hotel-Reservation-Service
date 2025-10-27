<?php 
include 'app/views/shares/header.php'; 
//app/views/ai/predict.php
// Lấy ID khách sạn được truyền qua URL (nếu có) để chọn sẵn
$preselected_hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4 text-center">Sân chơi AI - Dự đoán Điểm Đánh giá 🤖</h2>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">1. Nhập thông tin đánh giá</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="hotel-select" class="form-label fw-bold">Chọn khách sạn:</label>
                        <select id="hotel-select" class="form-select">
                            <option value="" <?= !$preselected_hotel_id ? 'selected' : '' ?> disabled>-- Vui lòng chọn một khách sạn --</option>
                            <?php foreach($data['hotels'] as $hotel): ?>
                                <option value="<?= $hotel->id ?>" <?= ($hotel->id == $preselected_hotel_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($hotel->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="combined_comment" class="form-label fw-bold">Bình luận tổng hợp:</label>
                        <textarea id="combined_comment" class="form-control" rows="5" placeholder="VD: Tuyệt vời! Khách sạn sạch sẽ, nhân viên thân thiện..."></textarea>
                    </div>

                    <h5 class="card-title mt-4">Thông tin Đánh giá khác (Review Info):</h5>
                    <div class="mb-3">
                        <label for="stay_duration" class="form-label">Thời gian lưu trú:</label>
                        <select id="stay_duration" class="form-select">
                            <?php foreach($data['mappings']['stay_duration_mapping_sorted'] as $key => $val): ?>
                                <option value="<?= $val ?>"><?= htmlspecialchars($key) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="room_type" class="form-label">Loại phòng:</label>
                        <select id="room_type" class="form-select">
                           <?php foreach($data['known_room_types'] as $room_type_name): ?>
                                <?php $room_type_id = $data['mappings']['room_type_mapping'][$room_type_name] ?? null; ?>
                                <?php if ($room_type_id !== null): ?>
                                    <option value="<?= $room_type_id ?>"><?= htmlspecialchars($room_type_name) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="group_type" class="form-label">Loại nhóm khách:</label>
                        <select id="group_type" class="form-select">
                           <?php foreach($data['mappings']['group_type_mapping'] as $key => $val): ?>
                                <option value="<?= $val ?>"><?= htmlspecialchars($key) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">2. Tinh chỉnh thông số (nếu cần)</h5>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Thông tin Khách sạn (Hotel Info):</h5>
                    <div id="hotel-info-sliders">
                        <p class="text-muted">Vui lòng chọn một khách sạn để tải thông số.</p>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <button id="predict-btn" class="btn btn-success btn-lg w-100 mb-3">
                            <i class="fas fa-brain me-2"></i>Dự đoán điểm đánh giá
                        </button>
                        <h5 class="card-title mt-2">Kết quả dự đoán:</h5>
                        <div id="result-text-display" class="fs-4 fw-bold text-primary mb-2" style="min-height: 32px;"></div>
                        <div id="result-display" style="font-size: 4rem; font-weight: bold; color: #198754;">?.?</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/ai_playground.js"></script>

<?php include 'app/views/shares/footer.php'; ?>
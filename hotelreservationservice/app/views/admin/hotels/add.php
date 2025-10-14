<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Thêm Khách sạn mới</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($data['errors'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($data['errors']['db_error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/admin/hotel/save" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên khách sạn:</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ:</label>
                            <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả:</label>
                            <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="city_id" class="form-label">Tỉnh thành:</label>
                            <select id="city_id" name="city_id" class="form-select" required>
                                <option value="" selected disabled>-- Chọn một tỉnh thành --</option>
                                <?php foreach ($data['cities'] as $city): ?>
                                    <option value="<?= $city->id; ?>"><?= htmlspecialchars($city->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh:</label>
                            <input type="file" id="image" name="image" class="form-control">
                        </div>

                        <hr>
                        <h5 class="mt-4">Điểm đánh giá chi tiết (cho AI)</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="service_staff" class="form-label">Nhân viên:</label>
                                <input type="number" step="0.1" max="10" min="1" class="form-control" name="service_staff" value="8.0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="amenities" class="form-label">Tiện nghi:</label>
                                <input type="number" step="0.1" max="10" min="1" class="form-control" name="amenities" value="8.0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cleanliness" class="form-label">Sạch sẽ:</label>
                                <input type="number" step="0.1" max="10" min="1" class="form-control" name="cleanliness" value="8.0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="comfort" class="form-label">Thoải mái:</label>
                                <input type="number" step="0.1" max="10" min="1" class="form-control" name="comfort" value="8.0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="value_for_money" class="form-label">Đáng giá tiền:</label>
                                <input type="number" step="0.1" max="10" min="1" class="form-control" name="value_for_money" value="8.0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="location" class="form-label">Địa điểm:</label>
                                <input type="number" step="0.1" max="10" min="1" class="form-control" name="location" value="8.0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="free_wifi" class="form-label">WiFi miễn phí:</label>
                                <input type="number" step="0.1" max="10" min="1" class="form-control" name="free_wifi" value="8.0">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/admin/hotel" class="btn btn-secondary">Quay lại</a>
                            <button type="submit" class="btn btn-primary">Thêm khách sạn</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
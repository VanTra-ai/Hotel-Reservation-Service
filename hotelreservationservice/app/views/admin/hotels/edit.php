<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Sửa thông tin khách sạn</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($data['hotel']): ?>
                        <form action="<?= BASE_URL ?>/admin/hotel/update/<?= $data['hotel']->id ?>" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên khách sạn:</label>
                                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($data['hotel']->name) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Địa chỉ:</label>
                                        <textarea id="address" name="address" class="form-control" rows="3" required><?= htmlspecialchars($data['hotel']->address) ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Mô tả:</label>
                                        <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($data['hotel']->description) ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="city_id" class="form-label">Tỉnh thành:</label>
                                        <select id="city_id" name="city_id" class="form-select" required>
                                            <?php foreach ($data['cities'] as $city): ?>
                                                <option value="<?= $city->id ?>" <?= ($data['hotel']->city_id == $city->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($city->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Ảnh hiện tại:</label>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($data['hotel']->image ?? 'public/images/placeholder.png') ?>" alt="Ảnh khách sạn" class="img-fluid rounded mb-2">
                                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($data['hotel']->image) ?>">

                                        <label for="image" class="form-label">Tải lên ảnh mới:</label>
                                        <input type="file" id="image" name="image" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mt-4">Điểm đánh giá chi tiết (cho AI)</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="service_staff" class="form-label">Nhân viên:</label>
                                    <input type="number" step="0.1" max="10" min="1" class="form-control" name="service_staff" value="<?= htmlspecialchars($data['hotel']->service_staff ?? 8.0) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="amenities" class="form-label">Tiện nghi:</label>
                                    <input type="number" step="0.1" max="10" min="1" class="form-control" name="amenities" value="<?= htmlspecialchars($data['hotel']->amenities ?? 8.0) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cleanliness" class="form-label">Sạch sẽ:</label>
                                    <input type="number" step="0.1" max="10" min="1" class="form-control" name="cleanliness" value="<?= htmlspecialchars($data['hotel']->cleanliness ?? 8.0) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="comfort" class="form-label">Thoải mái:</label>
                                    <input type="number" step="0.1" max="10" min="1" class="form-control" name="comfort" value="<?= htmlspecialchars($data['hotel']->comfort ?? 8.0) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="value_for_money" class="form-label">Đáng giá tiền:</label>
                                    <input type="number" step="0.1" max="10" min="1" class="form-control" name="value_for_money" value="<?= htmlspecialchars($data['hotel']->value_for_money ?? 8.0) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="location" class="form-label">Địa điểm:</label>
                                    <input type="number" step="0.1" max="10" min="1" class="form-control" name="location" value="<?= htmlspecialchars($data['hotel']->location ?? 8.0) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="free_wifi" class="form-label">WiFi miễn phí:</label>
                                    <input type="number" step="0.1" max="10" min="1" class="form-control" name="free_wifi" value="<?= htmlspecialchars($data['hotel']->free_wifi ?? 8.0) ?>">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= BASE_URL ?>/admin/hotel" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">Không tìm thấy khách sạn.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
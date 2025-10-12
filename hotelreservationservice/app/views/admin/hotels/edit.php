<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Sửa thông tin khách sạn</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($hotel): ?>
                        <form action="<?= BASE_URL ?>/admin/hotel/update/<?= htmlspecialchars($hotel->id) ?>" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên khách sạn:</label>
                                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($hotel->name) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Địa chỉ:</label>
                                        <textarea id="address" name="address" class="form-control" rows="3" required><?= htmlspecialchars($hotel->address) ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Mô tả:</label>
                                        <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($hotel->description) ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="city_id" class="form-label">Tỉnh thành:</label>
                                        <select id="city_id" name="city_id" class="form-select" required>
                                            <option value="">-- Chọn tỉnh thành --</option>
                                            <?php foreach ($cities as $city): ?>
                                                <?php $isSelected = ($hotel->city_id == $city->id) ? 'selected' : ''; ?>
                                                <option value="<?= $city->id ?>" <?= $isSelected ?>>
                                                    <?= htmlspecialchars($city->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Ảnh hiện tại:</label>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($hotel->image ?? 'public/images/placeholder.png') ?>" alt="Ảnh khách sạn" class="img-fluid rounded mb-2">

                                        <label for="image" class="form-label">Tải lên ảnh mới:</label>
                                        <input type="file" id="image" name="image" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <hr>
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
<?php include 'app/views/shares/header.php';
//app/views/admin/hotels/add.php
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Thêm Khách sạn mới</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($data['errors']) && !empty($data['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($data['errors'] as $error): ?>
                                    <li><?= htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
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
                                <?php if (!empty($data['cities'])): ?>
                                    <?php foreach ($data['cities'] as $city): ?>
                                        <option value="<?= $city->id; ?>"><?= htmlspecialchars($city->name); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh đại diện (Bắt buộc):</label>
                            <input type="file" id="image" name="image" class="form-control" required accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label for="gallery_images" class="form-label">Hình ảnh Gallery (Tùy chọn):</label>
                            <input type="file" id="gallery_images" name="gallery_images[]" class="form-control" multiple accept="image/*">
                            <small class="form-text text-muted">Bạn có thể chọn nhiều ảnh.</small>
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
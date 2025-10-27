<?php include 'app/views/shares/header.php';
//app/views/partner/hotels/edit.php
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-hotel me-2"></i>Quản lý Khách sạn của bạn</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show" role="alert">
                            <?= $_SESSION['flash_message']['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <?php if ($data['hotel']): ?>
                        <form action="<?= BASE_URL ?>/partner/hotel/update/<?= $data['hotel']->id ?>" method="POST" enctype="multipart/form-data">

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
                            <h5 class="mt-4 text-muted">Điểm đánh giá chi tiết (Tự động cập nhật bởi hệ thống)</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nhân viên:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['hotel']->service_staff ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tiện nghi:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['hotel']->amenities ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sạch sẽ:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['hotel']->cleanliness ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Thoải mái:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['hotel']->comfort ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Đáng giá tiền:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['hotel']->value_for_money ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Địa điểm:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['hotel']->location ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">WiFi miễn phí:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['hotel']->free_wifi ?? 0.0) ?>" disabled readonly>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="<?= BASE_URL ?>/partner/dashboard" class="btn btn-secondary">Quay lại Dashboard</a>
                                <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
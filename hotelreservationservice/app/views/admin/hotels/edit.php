<?php include 'app/views/shares/header.php';
//app/views/admin/hotels/edit.php
?>

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
                                            <?php if (!empty($data['cities'])): ?>
                                                <?php foreach ($data['cities'] as $city): ?>
                                                    <option value="<?= $city->id ?>" <?= ($data['hotel']->city_id == $city->id) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($city->name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Ảnh đại diện hiện tại:</label>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($data['hotel']->image ?? 'public/images/placeholder.png') ?>" alt="Ảnh khách sạn" class="img-fluid rounded mb-2">
                                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($data['hotel']->image) ?>">

                                        <label for="image" class="form-label">Tải lên ảnh đại diện mới:</label>
                                        <input type="file" id="image" name="image" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mt-4">Quản lý Ảnh Gallery</h5>
                            <div class="mb-3">
                                <label for="gallery_images" class="form-label">Thêm ảnh mới vào gallery:</label>
                                <input type="file" id="gallery_images" name="gallery_images[]" class="form-control" multiple accept="image/*">
                                <small class="form-text text-muted">Bạn có thể chọn nhiều ảnh mới để tải lên.</small>
                            </div>

                            <?php if (!empty($data['gallery_images'])): ?>
                                <label class="form-label">Các ảnh hiện có:</label>
                                <div class="d-flex flex-wrap gap-2 border p-2 rounded bg-light">
                                    <?php foreach ($data['gallery_images'] as $img): ?>
                                        <div class="position-relative">
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img->image_path) ?>" class="img-thumbnail" style="width: 100px; height: 75px; object-fit: cover;">
                                            <div class="form-check position-absolute top-0 end-0 p-1" style="background: rgba(255,255,255,0.7);">
                                                <input class="form-check-input" type="checkbox" name="delete_images[]" value="<?= $img->id ?>" id="del_img_<?= $img->id ?>">
                                                <label class="form-check-label small" for="del_img_<?= $img->id ?>" title="Đánh dấu để xóa">Xóa</label>
                                            </div>
                                            <?php if ($img->is_thumbnail): ?>
                                                <span class="badge bg-primary position-absolute bottom-0 start-0 m-1" style="font-size: 0.7em;">Đại diện</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted">Đánh dấu vào các ảnh bạn muốn xóa khi Lưu thay đổi.</small>
                            <?php endif; ?>
                            <hr>

                            <h5 class="mt-4">Điểm đánh giá chi tiết (Tính tự động từ Review)</h5>
                            <small class="form-text text-muted d-block mb-2">Các điểm này được tính tự động khi khách hàng đánh giá. Admin không thể sửa trực tiếp.</small>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nhân viên:</label>
                                    <input type="number" step="0.1" class="form-control" value="<?= htmlspecialchars($data['hotel']->service_staff ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tiện nghi:</label>
                                    <input type="number" step="0.1" class="form-control" value="<?= htmlspecialchars($data['hotel']->amenities ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sạch sẽ:</label>
                                    <input type="number" step="0.1" class="form-control" value="<?= htmlspecialchars($data['hotel']->cleanliness ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Thoải mái:</label>
                                    <input type="number" step="0.1" class="form-control" value="<?= htmlspecialchars($data['hotel']->comfort ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Đáng giá tiền:</label>
                                    <input type="number" step="0.1" class="form-control" value="<?= htmlspecialchars($data['hotel']->value_for_money ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Địa điểm:</label>
                                    <input type="number" step="0.1" class="form-control" value="<?= htmlspecialchars($data['hotel']->location ?? 0.0) ?>" disabled readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">WiFi miễn phí:</label>
                                    <input type="number" step="0.1" class="form-control" value="<?= htmlspecialchars($data['hotel']->free_wifi ?? 0.0) ?>" disabled readonly>
                                </div>
                                <input type="hidden" name="service_staff" value="<?= htmlspecialchars($data['hotel']->service_staff ?? 8.0) ?>">
                                <input type="hidden" name="amenities" value="<?= htmlspecialchars($data['hotel']->amenities ?? 8.0) ?>">
                                <input type="hidden" name="cleanliness" value="<?= htmlspecialchars($data['hotel']->cleanliness ?? 8.0) ?>">
                                <input type="hidden" name="comfort" value="<?= htmlspecialchars($data['hotel']->comfort ?? 8.0) ?>">
                                <input type="hidden" name="value_for_money" value="<?= htmlspecialchars($data['hotel']->value_for_money ?? 8.0) ?>">
                                <input type="hidden" name="location" value="<?= htmlspecialchars($data['hotel']->location ?? 8.0) ?>">
                                <input type="hidden" name="free_wifi" value="<?= htmlspecialchars($data['hotel']->free_wifi ?? 8.0) ?>">
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
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
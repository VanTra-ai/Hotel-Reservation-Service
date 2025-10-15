<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa phòng: #<?= htmlspecialchars($data['room']->room_number) ?></h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($data['room']): ?>
                        <form action="<?= BASE_URL ?>/partner/room/update/<?= htmlspecialchars($data['room']->id) ?>" method="POST" enctype="multipart/form-data">

                            <input type="hidden" name="hotel_id" value="<?= $data['room']->hotel_id ?>">

                            <div class="alert alert-info">
                                Đang chỉnh sửa phòng cho khách sạn: <strong><?= htmlspecialchars($data['hotel']->name) ?></strong>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_number" class="form-label">Số phòng <span class="text-danger">*</span></label>
                                        <input type="text" id="room_number" name="room_number" class="form-control"
                                            value="<?= htmlspecialchars($data['room']->room_number) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">Sức chứa (người) <span class="text-danger">*</span></label>
                                        <input type="number" id="capacity" name="capacity" class="form-control"
                                            value="<?= htmlspecialchars($data['room']->capacity) ?>" min="1" max="10" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_type" class="form-label">Loại phòng <span class="text-danger">*</span></label>
                                        <select id="room_type" name="room_type" class="form-select" required>
                                            <?php
                                            $roomTypes = [
                                                'Phòng Tiêu Chuẩn Giường Đôi',
                                                'Phòng Superior Giường Đôi',
                                                'Phòng Giường Đôi',
                                                'Phòng Deluxe Giường Đôi Có Ban Công',
                                                'Phòng Deluxe Giường Đôi',
                                                'Phòng Giường Đôi Có Ban Công',
                                                'Phòng Superior Giường Đôi Có Ban Công',
                                                'Phòng Gia Đình',
                                                'Phòng Deluxe Gia đình',
                                                'Phòng Superior Giường Đôi/2 Giường Đơn',
                                            ];
                                            foreach ($roomTypes as $type): ?>
                                                <option value="<?= htmlspecialchars($type) ?>" <?= ($data['room']->room_type === $type) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($type) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                                        <input type="number" id="price" name="price" class="form-control"
                                            value="<?= htmlspecialchars($data['room']->price) ?>" min="0" step="1000" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả phòng</label>
                                <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($data['room']->description) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hình ảnh hiện tại</label>
                                <?php if (!empty($data['room']->image)): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($data['room']->image) ?>" class="img-thumbnail mb-2" style="width: 200px;">
                                <?php else: ?>
                                    <p class="text-muted">Không có ảnh hiện tại.</p>
                                <?php endif; ?>
                                <label for="image" class="form-label">Tải lên ảnh mới</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= BASE_URL ?>/partner/room" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-warning">Cập nhật phòng</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">Không tìm thấy phòng để chỉnh sửa.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
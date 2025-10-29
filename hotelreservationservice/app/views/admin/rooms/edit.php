<?php
// app/views/admin/rooms/edit.php
include 'app/views/shares/header.php';

// Lấy các biến từ $data
$room = $data['room'] ?? null;
$hotels = $data['hotels'] ?? [];
$errors = $data['errors'] ?? [];
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa phòng
                        <?= $room ? ': #' . htmlspecialchars($room->room_number) : '' ?>
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!$room): ?>
                        <div class="alert alert-danger">
                            Không tìm thấy phòng để chỉnh sửa.
                            <a href="<?= BASE_URL ?>/admin/room" class="btn btn-secondary btn-sm ms-3">Quay lại danh sách</a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $field => $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="<?= BASE_URL ?>/admin/room/update/<?= htmlspecialchars($room->id) ?>" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="hotel_id_disabled" class="form-label">Khách sạn (Không thể thay đổi)</label>

                                        <select id="hotel_id_disabled" name="hotel_id_disabled" class="form-select" required disabled>
                                            <option value="">-- Chọn khách sạn --</option>
                                            <?php foreach ($hotels as $hotel): ?>
                                                <option value="<?= $hotel->id ?>" <?= ($room->hotel_id == $hotel->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($hotel->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <input type="hidden" name="hotel_id" value="<?= htmlspecialchars($room->hotel_id) ?>">
                                        <?php if (isset($errors['hotel_id'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['hotel_id']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_number" class="form-label">Số phòng <span class="text-danger">*</span></label>
                                        <input type="text" id="room_number" name="room_number" class="form-control <?= isset($errors['room_number']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($room->room_number) ?>" required>
                                        <?php if (isset($errors['room_number'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['room_number']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_type" class="form-label">Loại phòng <span class="text-danger">*</span></label>
                                        <select id="room_type" name="room_type" class="form-select <?= isset($errors['room_type']) ? 'is-invalid' : '' ?>" required>
                                            <option value="">-- Chọn loại phòng --</option>
                                            <?php
                                            foreach (ALLOWED_ROOM_TYPES as $type): ?>
                                                <option value="<?= htmlspecialchars($type) ?>" <?= ($room->room_type === $type) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($type) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['room_type'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['room_type']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">Sức chứa (người) <span class="text-danger">*</span></label>
                                        <input type="number" id="capacity" name="capacity" class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($room->capacity) ?>" min="1" max="10" required>
                                        <?php if (isset($errors['capacity'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['capacity']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" id="price" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($room->price) ?>" min="0" step="1000" required>
                                <?php if (isset($errors['price'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả phòng</label>
                                <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($room->description) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hình ảnh hiện tại</label>
                                <?php if (!empty($room->image)): ?>
                                    <div class="mb-2">
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($room->image) ?>"
                                            alt="Phòng <?= htmlspecialchars($room->room_number) ?>"
                                            class="img-thumbnail" style="width: 200px; height: 150px; object-fit: cover;">
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Không có ảnh hiện tại.</p>
                                <?php endif; ?>

                                <label for="image" class="form-label mt-2">Tải lên ảnh mới (để trống nếu không muốn đổi)</label>
                                <input type="file" id="image" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" accept="image/*">
                                <?php if (isset($errors['image'])): ?>
                                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['image']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= BASE_URL ?>/admin/room" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Cập nhật phòng
                                </button>
                            </div>
                        </form>
                    <?php endif; // End if ($room) 
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
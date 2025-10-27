<?php
// app/views/partner/rooms/edit.php
include 'app/views/shares/header.php';

// Khởi tạo các biến từ controller (giả định controller đã truyền $data)
$room = $data['room'] ?? null; // Dữ liệu phòng cần sửa
$hotel = $data['hotel'] ?? null; // Thông tin khách sạn của partner
$errors = $data['errors'] ?? []; // Mảng lỗi validation (từ session)

// Kiểm tra xem có khách sạn và phòng hợp lệ không
if (!$hotel || !$room) {
    // Hiển thị thông báo lỗi và dừng lại
    echo '<div class="container my-5"><div class="alert alert-danger">Lỗi: Không tìm thấy thông tin khách sạn hoặc phòng cần sửa.</div></div>';
    include 'app/views/shares/footer.php';
    exit(); // Dừng thực thi script
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa phòng: #<?= htmlspecialchars($room->room_number) ?>
                        <span class="fs-6">(Khách sạn: <?= htmlspecialchars($hotel->name) ?>)</span>
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $field => $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/partner/room/update/<?= htmlspecialchars($room->id) ?>" method="POST" enctype="multipart/form-data">

                        <!-- Hidden input chứa hotel_id (Partner không đổi được) -->
                        <input type="hidden" name="hotel_id" value="<?= htmlspecialchars($room->hotel_id) ?>">

                        <div class="row">
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_type" class="form-label">Loại phòng <span class="text-danger">*</span></label>
                                    <select id="room_type" name="room_type" class="form-select <?= isset($errors['room_type']) ? 'is-invalid' : '' ?>" required>
                                        <option value="">-- Chọn loại phòng --</option>
                                        <?php
                                        // Sử dụng hằng số ALLOWED_ROOM_TYPES
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
                                    <label for="price" class="form-label">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" id="price" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($room->price) ?>" min="0" step="1000" required>
                                    <?php if (isset($errors['price'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
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
                                <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['image']) ?></div> <!-- d-block for file input -->
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/partner/room" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-warning"> <!-- Nút màu vàng cho edit -->
                                <i class="fas fa-save me-2"></i>Cập nhật phòng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
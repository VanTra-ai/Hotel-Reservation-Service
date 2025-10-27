<?php
// app/views/admin/rooms/add.php
include 'app/views/shares/header.php';

// Khởi tạo các biến từ controller (giả định controller đã truyền vào)
$hotels = $hotels ?? []; // Danh sách khách sạn
$errors = $errors ?? []; // Mảng lỗi validation
$old_input = $old_input ?? []; // Mảng chứa input cũ
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Thêm phòng mới</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $field => $error): // Hiển thị cả key (tên field) nếu có 
                                ?>
                                    <li>
                                        <!-- Optional: Thêm tên field lỗi -->
                                        <?php // if (!is_int($field)) echo "<strong>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $field))) . ":</strong> "; 
                                        ?>
                                        <?= htmlspecialchars($error) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/admin/room/save" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hotel_id" class="form-label">Khách sạn <span class="text-danger">*</span></label>
                                    <select id="hotel_id" name="hotel_id" class="form-select <?= isset($errors['hotel_id']) ? 'is-invalid' : '' ?>" required>
                                        <option value="">-- Chọn khách sạn --</option>
                                        <?php foreach ($hotels as $hotel): ?>
                                            <option value="<?= $hotel->id ?>" <?= (($old_input['hotel_id'] ?? '') == $hotel->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($hotel->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['hotel_id'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['hotel_id']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_number" class="form-label">Số phòng <span class="text-danger">*</span></label>
                                    <input type="text" id="room_number" name="room_number" class="form-control <?= isset($errors['room_number']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($old_input['room_number'] ?? '') ?>" required>
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
                                        $selectedType = $old_input['room_type'] ?? '';
                                        foreach (ALLOWED_ROOM_TYPES as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>" <?= ($selectedType === $type) ? 'selected' : '' ?>>
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
                                        value="<?= htmlspecialchars($old_input['capacity'] ?? '1') ?>" min="1" max="10" required>
                                    <?php if (isset($errors['capacity'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['capacity']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" id="price" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($old_input['price'] ?? '') ?>" min="0" step="1000" required>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả phòng</label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                placeholder="Mô tả chi tiết về phòng, tiện nghi, dịch vụ..."><?= htmlspecialchars($old_input['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh phòng</label>
                            <input type="file" id="image" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" accept="image/*">
                            <div class="form-text">Chỉ chấp nhận file JPG, PNG, JPEG, GIF. Kích thước tối đa 10MB.</div>
                            <?php if (isset($errors['image'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/admin/room" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu phòng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
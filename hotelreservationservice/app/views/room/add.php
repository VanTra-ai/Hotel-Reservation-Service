<?php include 'app/views/shares/header.php'; ?>

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
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="/hotelreservationservice/Room/save" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hotel_id" class="form-label">Khách sạn <span class="text-danger">*</span></label>
                                    <select id="hotel_id" name="hotel_id" class="form-control" required>
                                        <option value="">-- Chọn khách sạn --</option>
                                        <?php foreach ($hotels as $hotel): ?>
                                            <option value="<?= $hotel->id ?>" <?= (isset($_POST['hotel_id']) && $_POST['hotel_id'] == $hotel->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($hotel->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_number" class="form-label">Số phòng <span class="text-danger">*</span></label>
                                    <input type="number" id="room_number" name="room_number" class="form-control" 
       value="<?= htmlspecialchars($_POST['room_number'] ?? '') ?>" 
       min="1" step="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_type" class="form-label">Loại phòng <span class="text-danger">*</span></label>
                                    <select id="room_type" name="room_type" class="form-control" required>
                                        <option value="">-- Chọn loại phòng --</option>
                                        <?php
                                        $roomTypes = [
                                            'Phòng Tiêu Chuẩn Giường Đôi',
                                            'Phòng Superior Giường Đôi',
                                            'Phòng Giường Đôi',
                                            'Phòng Deluxe Giường Đôi Có Ban Công',
                                            'Phòng Deluxe Giường Đôi',
                                            'Phòng Giường Đôi Có Ban Công',
                                            'Phòng Superior Giường Đôi Có Ban Công',
                                            'Phòng Gia Đình',
                                            'Phòng Deluxe Gia đình',
                                            'Phòng Superior Giường Đôi/2 Giường Đơn',
                                        ];
                                        $selectedType = $_POST['room_type'] ?? '';
                                        foreach ($roomTypes as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>" <?= ($selectedType === $type) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($type) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Sức chứa (người) <span class="text-danger">*</span></label>
                                    <input type="number" id="capacity" name="capacity" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>" 
                                           min="1" max="10" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" 
                                   min="0" step="1000" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả phòng</label>
                            <textarea id="description" name="description" class="form-control" rows="4" 
                                      placeholder="Mô tả chi tiết về phòng, tiện nghi, dịch vụ..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh phòng</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            <div class="form-text">Chỉ chấp nhận file JPG, PNG, JPEG, GIF. Kích thước tối đa 10MB.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/hotelreservationservice/Room/list" class="btn btn-secondary">
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

<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa phòng</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($room): ?>
                        <form action="/hotelreservationservice/Room/update/<?= htmlspecialchars($room->id) ?>" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="hotel_id" class="form-label">Khách sạn <span class="text-danger">*</span></label>
                                        <select id="hotel_id" name="hotel_id" class="form-control" required>
                                            <option value="">-- Chọn khách sạn --</option>
                                            <?php foreach ($hotels as $hotel): ?>
                                                <option value="<?= $hotel->id ?>" <?= ($room->hotel_id == $hotel->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($hotel->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_number" class="form-label">Số phòng <span class="text-danger">*</span></label>
                                        <input type="text" id="room_number" name="room_number" class="form-control" 
                                               value="<?= htmlspecialchars($room->room_number) ?>" required>
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
                                            foreach ($roomTypes as $type): ?>
                                                <option value="<?= htmlspecialchars($type) ?>" <?= ($room->room_type === $type) ? 'selected' : '' ?>>
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
                                               value="<?= htmlspecialchars($room->capacity) ?>" 
                                               min="1" max="10" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" id="price" name="price" class="form-control" 
                                       value="<?= htmlspecialchars($room->price) ?>" 
                                       min="0" step="1000" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả phòng</label>
                                <textarea id="description" name="description" class="form-control" rows="4" 
                                          placeholder="Mô tả chi tiết về phòng, tiện nghi, dịch vụ..."><?= htmlspecialchars($room->description) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hình ảnh hiện tại</label>
                                <?php if (!empty($room->image)): ?>
                                    <div class="mb-2">
                                        <img src="/hotelreservationservice/<?= htmlspecialchars($room->image) ?>" 
                                             alt="Phòng <?= htmlspecialchars($room->room_number) ?>" 
                                             class="img-thumbnail" style="width: 200px; height: 150px; object-fit: cover;">
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Không có ảnh hiện tại.</p>
                                <?php endif; ?>
                                
                                <label for="image" class="form-label">Tải lên ảnh mới (để trống nếu không muốn đổi)</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <div class="form-text">Chỉ chấp nhận file JPG, PNG, JPEG, GIF. Kích thước tối đa 10MB.</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="/hotelreservationservice/Room/list" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Cập nhật phòng
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            Không tìm thấy phòng để chỉnh sửa.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>

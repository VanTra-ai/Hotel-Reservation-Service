<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-bed me-2"></i>Chi tiết phòng</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($room): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <?php if (!empty($room->image)): ?>
                                        <img src="/hotelreservationservice/<?= htmlspecialchars($room->image) ?>" 
                                             alt="Phòng <?= htmlspecialchars($room->room_number) ?>" 
                                             class="img-fluid rounded shadow-sm">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                             style="height: 300px;">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-image fa-3x mb-3"></i>
                                                <p>Không có hình ảnh</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h5 class="text-primary">Thông tin cơ bản</h5>
                                    <hr>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Số phòng:</strong></div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-primary fs-6"><?= htmlspecialchars($room->room_number) ?></span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Loại phòng:</strong></div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-info fs-6"><?= htmlspecialchars($room->room_type) ?></span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Sức chứa:</strong></div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-secondary fs-6">
                                            <i class="fas fa-users me-1"></i><?= htmlspecialchars($room->capacity) ?> người
                                        </span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Giá phòng:</strong></div>
                                    <div class="col-sm-8">
                                        <span class="fw-bold text-success fs-5">
                                            <?= number_format($room->price, 0, ',', '.') ?> VNĐ
                                        </span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Khách sạn:</strong></div>
                                    <div class="col-sm-8"><?= htmlspecialchars($room->hotel_name ?? 'N/A') ?></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Tỉnh thành:</strong></div>
                                    <div class="col-sm-8"><?= htmlspecialchars($room->city_name ?? 'N/A') ?></div>
                                </div>

                                <?php if (!empty($room->description)): ?>
                                    <div class="mb-3">
                                        <h6 class="text-primary">Mô tả phòng</h6>
                                        <p class="text-muted"><?= nl2br(htmlspecialchars($room->description)) ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex gap-2 mt-4">
                                    <a href="/hotelreservationservice/Room/edit/<?= $room->id ?>" class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                    </a>
                                    <a href="/hotelreservationservice/Room/list" class="btn btn-secondary">
                                        <i class="fas fa-list me-2"></i>Danh sách phòng
                                    </a>
                                    <a href="/hotelreservationservice/Room/delete/<?= $room->id ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa phòng này?')">
                                        <i class="fas fa-trash me-2"></i>Xóa phòng
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            Không tìm thấy thông tin phòng.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>

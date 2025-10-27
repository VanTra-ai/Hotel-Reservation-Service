<?php include 'app/views/shares/header.php';
//app/views/admin/rooms/list.php
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Quản lý Phòng</h2>
        <a href="<?= BASE_URL ?>/admin/room/add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm phòng mới
        </a>
    </div>

    <?php if (isset($_GET['error_message'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($_GET['error_message']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($rooms)): ?>
        <div class="alert alert-info text-center" role="alert">
            Hiện tại chưa có phòng nào được thêm.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Số phòng</th>
                        <th>Loại phòng</th>
                        <th>Khách sạn</th>
                        <th>Giá (VNĐ)</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= htmlspecialchars($room->id) ?></td>
                            <td>
                                <?php if (!empty($room->image)): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($room->image) ?>"
                                        alt="Phòng <?= htmlspecialchars($room->room_number) ?>"
                                        class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center"
                                        style="width: 60px; height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($room->room_number) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($room->room_type) ?></span></td>
                            <td><?= htmlspecialchars($room->hotel_name ?? 'N/A') ?></td>
                            <td><span class="fw-bold text-success"><?= number_format($room->price, 0, ',', '.') ?></span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>/room/show/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/room/edit/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/room/delete/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-danger" title="Xóa"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phòng này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
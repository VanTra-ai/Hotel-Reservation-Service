<?php include 'app/views/shares/header.php';
//app/views/room/list.php
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Danh sách Phòng</h2>

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
                        <th>Tỉnh thành</th>
                        <th>Sức chứa</th>
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
                                    <img src="/Hotel-Reservation-Service/hotelreservationservice/<?= htmlspecialchars($room->image) ?>"
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
                            <td><?= htmlspecialchars($room->city_name ?? 'N/A') ?></td>
                            <td><span class="badge bg-secondary"><i class="fas fa-users me-1"></i><?= htmlspecialchars($room->capacity) ?> người</span></td>
                            <td><span class="fw-bold text-success"><?= number_format($room->price, 0, ',', '.') ?> VNĐ</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/Hotel-Reservation-Service/hotelreservationservice/Room/show/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/Hotel-Reservation-Service/hotelreservationservice/Booking/book/<?= $room->id ?>"
                                        class="btn btn-sm btn-outline-success" title="Đặt phòng">
                                        <i class="fas fa-bed"></i>
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
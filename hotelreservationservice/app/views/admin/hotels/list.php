<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Quản lý Khách sạn</h2>
        <a href="<?= BASE_URL ?>/admin/hotel/add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm khách sạn mới
        </a>
    </div>

    <?php if (empty($hotels)): ?>
        <div class="alert alert-info text-center" role="alert">
            Hiện tại chưa có khách sạn nào được thêm.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Khách sạn</th>
                        <th>Địa chỉ</th>
                        <th>Thành phố</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hotels as $hotel): ?>
                        <tr>
                            <td><?= htmlspecialchars($hotel->id) ?></td>
                            <td><?= htmlspecialchars($hotel->name) ?></td>
                            <td><?= htmlspecialchars($hotel->address) ?></td>
                            <td><?= htmlspecialchars($hotel->city_name ?? 'N/A') ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>/admin/hotel/edit/<?= $hotel->id ?>" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/hotel/delete/<?= $hotel->id ?>" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa khách sạn này không?');">
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
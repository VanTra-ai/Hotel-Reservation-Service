<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Quản lý Tỉnh/Thành phố</h2>
        <a href="<?= BASE_URL ?>/admin/city/add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm mới
        </a>
    </div>

    <?php if (isset($cities) && is_array($cities) && count($cities) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Tỉnh/Thành phố</th>
                        <th>Hình ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cities as $city): ?>
                        <tr>
                            <td><?= htmlspecialchars($city->id) ?></td>
                            <td><?= htmlspecialchars($city->name) ?></td>
                            <td>
                                <?php if (!empty($city->image)): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($city->image) ?>?v=<?= time() ?>" class="img-thumbnail" style="width: 100px; object-fit: cover; aspect-ratio: 16/9;">
                                <?php else: ?>
                                    <span class="text-muted">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>/admin/city/edit/<?= htmlspecialchars($city->id) ?>" class="btn btn-warning btn-sm" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/city/delete/<?= htmlspecialchars($city->id) ?>" class="btn btn-danger btn-sm" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa không?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            Hiện chưa có tỉnh/thành phố nào được thêm.
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
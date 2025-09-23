<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Danh sách Tỉnh/Thành phố</h2>

    <a href="/hotelreservationservice/city/add" class="btn btn-primary mb-3">Thêm mới Tỉnh/Thành phố</a>

    <?php if (isset($cities) && is_array($cities) && count($cities) > 0): ?>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
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
                                <img src="/hotelreservationservice/<?= htmlspecialchars($city->image) ?>?v=<?= time() ?>" class="img-thumbnail" style="width: 100px;">
                            <?php else: ?>
                                <span class="text-muted">Không có ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/hotelreservationservice/city/edit/<?= htmlspecialchars($city->id) ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="/hotelreservationservice/city/delete/<?= htmlspecialchars($city->id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            Hiện chưa có tỉnh/thành phố nào được thêm.
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
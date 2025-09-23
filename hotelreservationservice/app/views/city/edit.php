<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Chỉnh sửa Tỉnh/Thành phố</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($city)): ?>
        <form action="/hotelreservationservice/city/update/<?= htmlspecialchars($city->id) ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Tên Tỉnh/Thành phố</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($city->name) ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Hình ảnh hiện tại</label>
                <?php if (!empty($city->image)): ?>
                    <img src="/hotelreservationservice/<?= htmlspecialchars($city->image) ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($city->name) ?>" class="img-thumbnail d-block mb-2" style="width: 150px;">
                <?php else: ?>
                    <p class="text-muted">Không có ảnh hiện tại.</p>
                <?php endif; ?>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="/hotelreservationservice/city/list" class="btn btn-secondary">Hủy</a>
        </form>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Không tìm thấy tỉnh/thành phố để chỉnh sửa.
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>
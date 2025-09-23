<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Thêm Tỉnh/Thành phố mới</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="/hotelreservationservice/city/save" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Tên Tỉnh/Thành phố</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Hình ảnh</label>
            <input type="file" class="form-control" id="image" name="image">
        </div>
        <button type="submit" class="btn btn-primary">Lưu</button>
        <a href="/hotelreservationservice/city/list" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<?php include 'app/views/shares/footer.php'; ?>
<?php include 'app/views/shares/header.php'; 
//app/views/admin/cities/edit.php
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa Tỉnh/Thành phố</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($city)): ?>
                        <form action="<?= BASE_URL ?>/admin/city/update/<?= htmlspecialchars($city->id) ?>" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên Tỉnh/Thành phố</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($city->name) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hình ảnh hiện tại</label>
                                <?php if (!empty($city->image)): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($city->image) ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($city->name) ?>" class="img-thumbnail d-block mb-2" style="width: 150px;">
                                <?php else: ?>
                                    <p class="text-muted">Không có ảnh hiện tại.</p>
                                <?php endif; ?>
                                <label for="image" class="form-label mt-2">Tải lên ảnh mới</label>
                                <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= BASE_URL ?>/admin/city" class="btn btn-secondary">Hủy</a>
                                <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            Không tìm thấy tỉnh/thành phố để chỉnh sửa.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
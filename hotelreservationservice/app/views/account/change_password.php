<?php include 'app/views/shares/header.php'; ?>
<section class="vh-100 gradient-custom">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow">
                    <h1>Đổi Mật Khẩu</h1>
                    <div class="card-body p-4">

                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0" style="padding-left: 20px;">
                                    <?php foreach ($errors as $errorMsg): ?>
                                        <li><?php echo htmlspecialchars($errorMsg); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="/hotelreservationservice/account/processChangePassword" method="post">
                            <div class="form-group">
                                <label for="current_password">Mật khẩu hiện tại:</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['current_password']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="new_password">Mật khẩu mới:</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <?php if (isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['new_password']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="confirm_new_password">Xác nhận mật khẩu mới:</label>
                                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                                <?php if (isset($errors['confirm_new_password'])): ?>
                                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['confirm_new_password']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-block">Cập Nhật Mật Khẩu</button>
                            </div>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a href="/hotelreservationservice/home">Quay lại trang chủ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'app/views/shares/footer.php'; ?>
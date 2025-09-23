<?php include 'app/views/shares/header.php';
// Khởi tạo các biến để tránh lỗi undefined
$errors = $errors ?? [];
$username = $username ?? '';
$fullName = $fullName ?? '';
$role = $role ?? 'user';
?>
<section class="vh-100 gradient-custom">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow">
                    <div class="text-center">
                        <h1 class="mb-0">Đăng Ký Tài Khoản</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0" style="padding-left: 20px;">
                                    <?php foreach ($errors as $err): ?>
                                        <li><?php echo htmlspecialchars($err); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <form action="/hotelreservationservice/account/save" method="post">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="username">Tên đăng nhập:</label>
                                    <input type="text" class="form-control"
                                        id="username" name="username" placeholder="Nhập tên đăng nhập"
                                        value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="fullname">Họ và tên:</label>
                                    <input type="text" class="form-control"
                                        id="fullname" name="fullname" placeholder="Nhập họ và tên"
                                        value="<?php echo htmlspecialchars($fullName); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="password">Mật khẩu:</label>
                                    <input type="password" class="form-control"
                                        id="password" name="password" placeholder="Nhập mật khẩu" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="confirmpassword">Xác nhận mật khẩu:</label>
                                    <input type="password" class="form-control"
                                        id="confirmpassword" name="confirmpassword" placeholder="Nhập lại mật khẩu" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="role">Loại tài khoản:</label>
                                <select class="form-control" id="role" name="role">
                                    <option value="user" <?php echo ($role == 'user') ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Admin (Test)</option>
                                </select>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    Đăng Ký
                                </button>
                            </div>
                        </form>
                        <hr>
                        <div class="text-center">
                            <p class="mb-0">Đã có tài khoản? <a href="/hotelreservationservice/account/login">Đăng nhập tại đây</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'app/views/shares/footer.php'; ?>
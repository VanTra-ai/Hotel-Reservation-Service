<?php include 'app/views/shares/header.php';
//app/views/account/register.php
$errors = $errors ?? [];
$username = $username ?? ($_POST['username'] ?? '');
$fullName = $fullName ?? ($_POST['fullname'] ?? '');
$email = $email ?? ($_POST['email'] ?? '');
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

                        <form action="/Hotel-Reservation-Service/hotelreservationservice/account/save" method="post" autocomplete="off" novalidate>
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

                            <div class="form-row mt-3">
                                <div class="form-group col-md-6">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control"
                                        id="email" name="email" placeholder="Nhập email"
                                        value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="password">Mật khẩu:</label>
                                    <input type="password" class="form-control"
                                        id="password" name="password" placeholder="Nhập mật khẩu" required>
                                </div>
                            </div>

                            <div class="form-row mt-3">
                                <div class="form-group col-md-6">
                                    <label for="confirmpassword">Xác nhận mật khẩu:</label>
                                    <input type="password" class="form-control"
                                        id="confirmpassword" name="confirmpassword" placeholder="Nhập lại mật khẩu" required>
                                </div>
                                <div class="form-group col-md-6 d-none">
                                    <!-- Role bị ẩn: mặc định user. Không cho user chọn admin trên form public. -->
                                    <input type="hidden" id="role" name="role" value="user">
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-block w-100">
                                    Đăng Ký
                                </button>
                            </div>
                        </form>

                        <hr>
                        <div class="text-center">
                            <p class="mb-0">Đã có tài khoản? <a href="/Hotel-Reservation-Service/hotelreservationservice/account/login">Đăng nhập tại đây</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'app/views/shares/footer.php'; ?>
<?php include 'app/views/shares/header.php'; 
//app/views/admin/accounts/edit.php
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Chỉnh sửa tài khoản: <?= htmlspecialchars($data['account']->username) ?></h4>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/admin/account/update/<?= $data['account']->id ?>" method="POST">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($data['account']->fullname) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($data['account']->email) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Vai trò</label>
                            <select name="role" id="role" class="form-select">
                                <option value="user" <?= $data['account']->role == 'user' ? 'selected' : '' ?>>User</option>
                                <option value="partner" <?= $data['account']->role == 'partner' ? 'selected' : '' ?>>Partner</option>
                                <option value="admin" <?= $data['account']->role == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3" id="hotel-assignment-section" style="display: none;">
                            <label for="hotel_id" class="form-label">Gán Khách sạn</label>
                            <select name="hotel_id" id="hotel_id" class="form-select">
                                <option value="">-- Không gán / Bỏ gán --</option>

                                <?php // Ưu tiên hiển thị khách sạn hiện tại của partner (nếu có) 
                                ?>
                                <?php if (isset($data['current_hotel']) && $data['current_hotel']): ?>
                                    <option value="<?= $data['current_hotel']->id; ?>" selected>
                                        Đang quản lý: <?= htmlspecialchars($data['current_hotel']->name); ?>
                                    </option>
                                <?php endif; ?>

                                <?php // Hiển thị danh sách các khách sạn chưa có chủ 
                                ?>
                                <?php foreach ($data['unassigned_hotels'] as $hotel): ?>
                                    <option value="<?= $hotel->id; ?>"><?= htmlspecialchars($hotel->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Chỉ áp dụng khi vai trò là "Partner". Chọn một khách sạn chưa có chủ để gán.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/admin/account" class="btn btn-secondary">Hủy</a>
                            <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const hotelSection = document.getElementById('hotel-assignment-section');

        function toggleHotelSection() {
            // Nếu giá trị được chọn là 'partner', hiện khối gán khách sạn. Ngược lại, ẩn đi.
            if (roleSelect.value === 'partner') {
                hotelSection.style.display = 'block';
            } else {
                hotelSection.style.display = 'none';
            }
        }

        // Chạy hàm một lần khi trang vừa tải xong để kiểm tra vai trò hiện tại
        toggleHotelSection();

        // Thêm một trình nghe sự kiện: mỗi khi vai trò thay đổi, gọi lại hàm
        roleSelect.addEventListener('change', toggleHotelSection);
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
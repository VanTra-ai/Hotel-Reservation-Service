<?php include 'app/views/shares/header.php';
//app/views/booking/confirmation.php
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 text-success"><i class="fas fa-check-circle fa-3x"></i></div>
                    <h3 class="fw-bold mb-3">Đặt phòng thành công!</h3>
                    <p class="text-muted mb-4">
                        Cảm ơn bạn đã đặt phòng. Chúng tôi đã ghi nhận yêu cầu của bạn.
                        Bạn có thể xem lại lịch sử đặt phòng trong trang cá nhân của mình.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= BASE_URL ?>/home" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                        <a href="<?= BASE_URL ?>/hotel/list" class="btn btn-outline-secondary">
                            <i class="fas fa-hotel me-2"></i>Tiếp tục xem khách sạn
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
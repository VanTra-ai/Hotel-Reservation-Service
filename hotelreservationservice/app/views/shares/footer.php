<footer class="bg-dark text-center text-lg-start mt-4 py-4">
  <div class="container text-center text-white">
    <!-- Cập nhật năm cho đúng -->
    &copy; <?= date('Y') ?> Hotel-Reservation-Service. All rights reserved.
  </div>
</footer>

<!-- Bootstrap Bundle (đảm bảo dropdown hoạt động) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Flatpickr JS + locale tiếng Việt -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>

<!-- ✅ Script custom (ĐÃ SỬA DÙNG BASE_URL) -->
<script src="<?= BASE_URL ?>/public/js/search_form.js"></script>

</body>

</html>
<?php include 'app/views/shares/header.php'; ?>
<h1>Thêm khách sạn mới</h1>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<form method="POST" action="/hotelreservationservice/Hotel/save" enctype="multipart/form-data" onsubmit="return validateForm();">
    <div class="form-group">
        <label for="name">Tên khách sạn:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="address">Địa chỉ:</label>
        <textarea id="address" name="address" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label for="city_id">Tỉnh thành:</label>
        <select id="city_id" name="city_id" class="form-control" required>
            <?php foreach ($cities as $city): ?>
                <option value="<?php echo $city->id; ?>"><?php echo htmlspecialchars($city->name, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="image">Hình ảnh:</label>
        <input type="file" id="image" name="image" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Thêm khách sạn</button>
</form>
<a href="/hotelreservationservice/Hotel/list" class="btn btn-secondary mt-2">Quay lại danh sách khách sạn</a>
<?php include 'app/views/shares/footer.php'; ?>
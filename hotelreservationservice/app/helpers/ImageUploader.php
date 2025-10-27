<?php
// app/helpers/ImageUploader.php

class ImageUploader
{
    private string $targetDirectory;
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    private int $maxFileSize = 10 * 1024 * 1024; // 10MB

    /**
     * @param string $relativeTargetDir Thư mục đích lưu ảnh (ví dụ: 'public/images/hotels/')
     */
    public function __construct(string $relativeTargetDir)
    {
        // Đảm bảo đường dẫn luôn kết thúc bằng dấu /
        $this->targetDirectory = rtrim($relativeTargetDir, '/') . '/';

        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($this->targetDirectory)) {
            mkdir($this->targetDirectory, 0777, true);
        }
    }

    /**
     * Xử lý upload một file ảnh.
     *
     * @param array $fileInfo Thông tin file từ $_FILES['input_name']
     * @param string $prefix Tiền tố cho tên file (ví dụ: 'hotel_', 'room_')
     * @return string Đường dẫn tương đối đến file đã lưu, hoặc chuỗi rỗng nếu lỗi.
     * @throws Exception Nếu có lỗi (loại file, kích thước, không thể di chuyển)
     */
    public function upload(array $fileInfo, string $prefix = ''): string
    {
        // Kiểm tra lỗi upload cơ bản
        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            // Có thể throw Exception chi tiết hơn dựa vào mã lỗi
            if ($fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
                return ''; // Không có file tải lên, không phải lỗi
            }
            throw new Exception("Lỗi tải lên file. Mã lỗi: " . $fileInfo['error']);
        }

        // Kiểm tra kích thước
        if ($fileInfo['size'] > $this->maxFileSize) {
            throw new Exception("Kích thước file vượt quá giới hạn " . ($this->maxFileSize / 1024 / 1024) . "MB.");
        }

        // Kiểm tra extension
        $extension = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new Exception("Loại file không được phép. Chỉ chấp nhận: " . implode(', ', $this->allowedExtensions));
        }

        // Tạo tên file duy nhất
        $filename = uniqid($prefix, true) . '.' . $extension;
        $targetPath = $this->targetDirectory . $filename;

        // Di chuyển file
        if (!move_uploaded_file($fileInfo["tmp_name"], $targetPath)) {
            throw new Exception("Không thể di chuyển file đã tải lên.");
        }

        // Trả về đường dẫn tương đối (quan trọng!)
        return $targetPath;
    }

    /**
     * Xóa file ảnh cũ (nếu tồn tại).
     * @param string|null $relativePath Đường dẫn tương đối của file cần xóa.
     * @return bool True nếu xóa thành công hoặc không có file để xóa, False nếu lỗi.
     */
    public function delete(?string $relativePath): bool
    {
        if (empty($relativePath)) {
            return true; // Không có gì để xóa
        }
        // Kiểm tra xem file có tồn tại và có phải là file không
        if (is_file($relativePath)) {
            return unlink($relativePath);
        }
        return true; // File không tồn tại cũng coi như thành công
    }
}

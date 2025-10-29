<?php
// app/models/HotelModel.php
class HotelModel
{
    private $conn;
    private $table_name = "hotel";
    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function getHotels(?int $limit = null, ?int $offset = null, ?string $searchTerm = null)
    {
        $query = "SELECT h.*, c.name AS city_name, a.fullname AS owner_name
                  FROM hotel h 
                  LEFT JOIN city c ON h.city_id = c.id
                  LEFT JOIN account a ON h.owner_id = a.id";

        $params = [];

        // Thêm điều kiện tìm kiếm (WHERE)
        if (!empty($searchTerm)) {
            $query .= " WHERE h.name LIKE :search OR c.name LIKE :search OR h.id LIKE :search";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $query .= " ORDER BY h.id DESC"; // Sắp xếp mặc định

        // <<< LOGIC XỬ LÝ PHÂN TRANG TÙY CHỌN >>>
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

        $stmt = $this->conn->prepare($query);

        // Bind các tham số
        if (!empty($searchTerm)) {
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }
        if ($limit !== null && $offset !== null) {
            $stmt->bindParam(':limit', $params[':limit'], PDO::PARAM_INT);
            $stmt->bindParam(':offset', $params[':offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    // Lấy danh sách khách sạn theo city_id
    public function getHotelsByCityId($cityId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE city_id = :city_id ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':city_id', $cityId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Phương thức getHotelById đã có
    public function getHotelById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    public function addHotel(
        $name,
        $address,
        $description,
        $city_id,
        $image,
        $service_staff = 8.0,
        $amenities = 8.0,
        $cleanliness = 8.0,
        $comfort = 8.0,
        $value_for_money = 8.0,
        $location = 8.0,
        $free_wifi = 8.0
    ) {
        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'Tên khách sạn không được để trống';
        }
        // Thêm validation khác nếu cần
        if (count($errors) > 0) {
            return $errors;
        }

        // <<< THÊM 7 CỘT VÀ PLACEHOLDER VÀO INSERT >>>
        $query = "INSERT INTO " . $this->table_name . "
                    (name, address, description, city_id, image,
                     service_staff, amenities, cleanliness, comfort,
                     value_for_money, location, free_wifi)
                  VALUES
                    (:name, :address, :description, :city_id, :image,
                     :service_staff, :amenities, :cleanliness, :comfort,
                     :value_for_money, :location, :free_wifi)";
        try { // <<< Thêm try-catch >>>
            $stmt = $this->conn->prepare($query);

            // Bind dữ liệu cơ bản
            $name = htmlspecialchars(strip_tags($name));
            $address = htmlspecialchars(strip_tags($address));
            $description = htmlspecialchars(strip_tags($description));
            $city_id = filter_var($city_id, FILTER_VALIDATE_INT) ? (int)$city_id : null;
            $image = $image ? htmlspecialchars(strip_tags($image)) : null; // Cho phép NULL

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
            $stmt->bindParam(':image', $image, PDO::PARAM_STR);

            // <<< BIND 7 ĐIỂM SỐ >>>
            // Đảm bảo là float và trong khoảng hợp lệ (ví dụ 1-10)
            $service_staff = max(1.0, min(10.0, (float)$service_staff));
            $amenities = max(1.0, min(10.0, (float)$amenities));
            $cleanliness = max(1.0, min(10.0, (float)$cleanliness));
            $comfort = max(1.0, min(10.0, (float)$comfort));
            $value_for_money = max(1.0, min(10.0, (float)$value_for_money));
            $location = max(1.0, min(10.0, (float)$location));
            $free_wifi = max(1.0, min(10.0, (float)$free_wifi));

            $stmt->bindParam(':service_staff', $service_staff);
            $stmt->bindParam(':amenities', $amenities);
            $stmt->bindParam(':cleanliness', $cleanliness);
            $stmt->bindParam(':comfort', $comfort);
            $stmt->bindParam(':value_for_money', $value_for_money);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':free_wifi', $free_wifi);


            if ($stmt->execute()) {
                return true;
            } else {
                error_log("Add hotel failed: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Add hotel PDO error: " . $e->getMessage());
            return false;
        }
    }
    public function updateHotel($id, $name, $address, $description, $city_id, $image)
    {
        $query = "UPDATE " . $this->table_name . " SET name=:name, address=:address, description=:description, city_id=:city_id, image=:image WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $address = htmlspecialchars(strip_tags($address));
        $description = htmlspecialchars(strip_tags($description));
        $city_id = htmlspecialchars(strip_tags($city_id));
        $image = htmlspecialchars(strip_tags($image));
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':city_id', $city_id);
        $stmt->bindParam(':image', $image);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function deleteHotel($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    /**
     * Lấy danh sách các khách sạn chưa có chủ sở hữu
     */
    public function getUnassignedHotels()
    {
        $query = "SELECT id, name FROM " . $this->table_name . " WHERE owner_id IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Gán một khách sạn cho một chủ sở hữu (partner/admin)
     */
    public function assignOwnerToHotel(int $hotelId, int $ownerId): bool
    {
        $query = "UPDATE " . $this->table_name . " SET owner_id = :owner_id WHERE id = :hotel_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':owner_id', $ownerId, PDO::PARAM_INT);
            $stmt->bindParam(':hotel_id', $hotelId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    /**
     * Lấy TẤT CẢ thông tin khách sạn mà một partner cụ thể đang sở hữu
     */
    public function getHotelByOwnerId(int $ownerId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE owner_id = :owner_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $ownerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    /**
     * Gỡ bỏ chủ sở hữu khỏi tất cả các khách sạn mà họ đang quản lý.
     * Dùng để "dọn dẹp" trước khi gán mới.
     */
    public function unassignOwnerFromAllHotels(int $ownerId): bool
    {
        $query = "UPDATE " . $this->table_name . " SET owner_id = NULL WHERE owner_id = :owner_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':owner_id', $ownerId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    /**
     * Lấy tất cả khách sạn KÈM THEO TÊN THÀNH PHỐ
     * Dùng cho trang quản trị để hiển thị đầy đủ thông tin.
     */
    public function getHotelsWithCityName(int $limit, int $offset)
    {
        $query = "SELECT h.*, c.name as city_name 
                  FROM " . $this->table_name . " h
                  LEFT JOIN city c ON h.city_id = c.id
                  ORDER BY h.id ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    /**
     * Tính toán và cập nhật lại điểm trung bình (rating) 
     * và tổng số đánh giá (total_rating) cho một khách sạn.
     * Hàm này sẽ tính trung bình dựa trên cột `ai_rating`.
     */
    public function recalculateHotelRating(int $hotelId): bool
    {
        // Câu lệnh SQL để tính AVG và COUNT từ bảng review
        $query = "SELECT 
                    AVG(ai_rating) as average_rating, 
                    COUNT(id) as total_reviews 
                  FROM review 
                  WHERE hotel_id = :hotel_id AND ai_rating IS NOT NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotel_id', $hotelId, PDO::PARAM_INT);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_OBJ);

        if ($stats) {
            // Câu lệnh SQL để cập nhật lại bảng hotel
            $updateQuery = "UPDATE " . $this->table_name . " 
                            SET rating = :avg_rating, total_rating = :total_reviews 
                            WHERE id = :hotel_id";

            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':avg_rating', $stats->average_rating);
            $updateStmt->bindParam(':total_reviews', $stats->total_reviews, PDO::PARAM_INT);
            $updateStmt->bindParam(':hotel_id', $hotelId, PDO::PARAM_INT);

            return $updateStmt->execute();
        }

        return false;
    }
    /**
     * Chỉ cập nhật các thông tin cơ bản của khách sạn (dành cho Partner)
     */
    public function updateHotelBasicInfo($id, $name, $address, $description, $city_id, $image): bool
    {
        $query = "UPDATE " . $this->table_name . " SET 
                  name=:name, address=:address, description=:description, 
                  city_id=:city_id, image=:image 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':city_id', $city_id);
        $stmt->bindParam(':image', $image);

        return $stmt->execute();
    }
    /**
     * Lấy tất cả hình ảnh của một khách sạn
     * @param int $hotelId
     * @return array Danh sách đối tượng hình ảnh
     */
    public function getHotelImages(int $hotelId): array
    {
        $stmt = $this->conn->prepare("SELECT id, image_path, is_thumbnail FROM hotel_images WHERE hotel_id = :hotel_id ORDER BY display_order ASC, id ASC");
        $stmt->bindParam(':hotel_id', $hotelId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    /**
     * Lấy tổng số khách sạn (để phân trang)
     */
    public function getHotelCount(?string $searchTerm = null): int
    {
        $query = "SELECT COUNT(h.id) FROM hotel h 
                  LEFT JOIN city c ON h.city_id = c.id";
        $params = [];

        if (!empty($searchTerm)) {
            $query .= " WHERE h.name LIKE :search OR c.name LIKE :search OR h.id LIKE :search";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    /**
     * Lưu đường dẫn của nhiều ảnh vào bảng hotel_images
     * @param int $hotelId ID khách sạn vừa tạo
     * @param array $imagePaths Mảng các đường dẫn ảnh
     * @param string $mainImagePath Đường dẫn ảnh chính (dùng để đánh dấu is_thumbnail)
     * @return bool
     */
    public function saveHotelImages(int $hotelId, array $imagePaths, ?string $mainImagePath = null): bool
    {
        if (empty($imagePaths)) {
            return true;
        }

        // Tạo chuỗi placeholders và danh sách values
        $placeholders = [];
        $values = [];
        $order = 0;

        foreach ($imagePaths as $path) {
            $isThumbnail = ($path === $mainImagePath) ? 1 : 0;

            $placeholders[] = "(:hotel_id_{$order}, :path_{$order}, :is_thumbnail_{$order}, :order_{$order})";

            $values[":hotel_id_{$order}"] = $hotelId;
            $values[":path_{$order}"] = $path;
            $values[":is_thumbnail_{$order}"] = $isThumbnail;
            $values[":order_{$order}"] = $order;

            $order++;
        }

        $query = "INSERT INTO hotel_images (hotel_id, image_path, is_thumbnail, display_order) 
                  VALUES " . implode(', ', $placeholders);

        try {
            $stmt = $this->conn->prepare($query);

            // Bind tất cả các giá trị
            foreach ($values as $key => &$val) {
                $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindParam($key, $val, $type);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Save hotel images error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa tất cả ảnh trong bảng hotel_images cho một khách sạn
     */
    public function deleteHotelImages(int $hotelId): bool
    {
        $query = "DELETE FROM hotel_images WHERE hotel_id = :hotelId";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete hotel images error: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Xóa một ảnh cụ thể khỏi bảng hotel_images bằng ID của ảnh
     */
    public function deleteHotelImageById(int $imageId): bool
    {
        $query = "DELETE FROM hotel_images WHERE id = :imageId";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete hotel image by ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra xem ảnh đã tồn tại trong gallery của khách sạn chưa
     */
    public function checkImageExists(int $hotelId, string $imagePath): bool
    {
        $query = "SELECT COUNT(*) FROM hotel_images WHERE hotel_id = :hotelId AND image_path = :imagePath";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
        $stmt->bindParam(':imagePath', $imagePath, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }
}

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
    public function getHotels()
    {
        $query = "SELECT p.id, p.name, p.address, p.description, p.rating, p.total_rating, p.image, c.name as city_name
                    FROM " . $this->table_name . " p
                    LEFT JOIN city c ON p.city_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $result;
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
    public function addHotel($name, $address, $description, $city_id, $image)
    {
        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'Tên khách sạn không được để trống';
        }
        if (empty($address)) {
            $errors['address'] = 'Địa chỉ khách sạn không được để trống';
        }
        if (empty($description)) {
            $errors['description'] = 'Mô tả không được để trống';
        }
        if (count($errors) > 0) {
            return $errors;
        }
        $query = "INSERT INTO " . $this->table_name . " (name, address, description, city_id, image) VALUES (:name, :address, :description, :city_id, :image)";
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $address = htmlspecialchars(strip_tags($address));
        $description = htmlspecialchars(strip_tags($description));
        $city_id = htmlspecialchars(strip_tags($city_id));
        $image = htmlspecialchars(strip_tags($image));
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
    public function getHotelsWithCityName()
    {
        $query = "SELECT h.*, c.name as city_name 
                  FROM " . $this->table_name . " h
                  LEFT JOIN city c ON h.city_id = c.id
                  ORDER BY h.id DESC";

        $stmt = $this->conn->prepare($query);
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
}

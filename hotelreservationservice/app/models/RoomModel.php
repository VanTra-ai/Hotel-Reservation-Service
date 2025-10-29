<?php
// app/models/RoomModel.php

class RoomModel
{
    private $conn;
    private $table_name = "room";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Lấy danh sách phòng
     * @param int|null $hotelId Nếu truyền hotelId sẽ lọc theo khách sạn
     * @param bool $onlyAvailable Nếu true, lọc phòng khả dụng trong khoảng ngày
     * @param string|null $checkInDate Ngày check-in (YYYY-MM-DD)
     * @param string|null $checkOutDate Ngày check-out (YYYY-MM-DD)
     * @return array Phòng (fetchAll)
     */
    public function getRooms($hotelId = null, $onlyAvailable = false, $checkInDate = null, $checkOutDate = null)
    {
        try {
            $sql = "SELECT r.*, h.name AS hotel_name, c.name AS city_name
                    FROM " . $this->table_name . " r
                    JOIN hotel h ON r.hotel_id = h.id
                    JOIN city c ON h.city_id = c.id
                    WHERE 1=1";

            $params = [];

            // Lọc theo khách sạn
            if ($hotelId !== null) {
                $sql .= " AND r.hotel_id = :hotel_id";
                $params[':hotel_id'] = (int)$hotelId;
            }

            // Lọc phòng khả dụng
            if ($onlyAvailable && $checkInDate && $checkOutDate) {
                $sql .= " AND r.id NOT IN (
                            SELECT room_id FROM booking
                            WHERE (check_in_date < :checkOutDate AND check_out_date > :checkInDate)
                            AND status IN ('pending','confirmed')
                          )";
                $params[':checkInDate'] = $checkInDate;
                $params[':checkOutDate'] = $checkOutDate;
            }

            $sql .= " ORDER BY r.hotel_id, r.price ASC";

            $stmt = $this->conn->prepare($sql);

            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            // Log lỗi nếu cần
            return [];
        }
    }

    /**
     * Lấy phòng theo ID, kèm theo owner_id của khách sạn
     */
    public function getRoomById($id)
    {
        try {
            $sql = "SELECT r.*, h.name AS hotel_name, c.name AS city_name, h.owner_id
                    FROM " . $this->table_name . " r
                    JOIN hotel h ON r.hotel_id = h.id
                    JOIN city c ON h.city_id = c.id
                    WHERE r.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Thêm phòng mới (dành cho admin)
     */
    public function addRoom($hotel_id, $room_number, $room_type, $capacity, $price, $description = '', $image = '')
    {
        $errors = $this->validateRoomData($hotel_id, $room_number, $room_type, $capacity, $price);
        if (count($errors) > 0) return $errors;

        try {
            $sql = "INSERT INTO " . $this->table_name . " 
                    (hotel_id, room_number, room_type, capacity, price, description, image)
                    VALUES (:hotel_id, :room_number, :room_type, :capacity, :price, :description, :image)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':hotel_id', (int)$hotel_id, PDO::PARAM_INT);
            $stmt->bindValue(':room_number', $room_number, PDO::PARAM_STR);
            $stmt->bindValue(':room_type', $room_type, PDO::PARAM_STR);
            $stmt->bindValue(':capacity', (int)$capacity, PDO::PARAM_INT);
            $stmt->bindValue(':price', (float)$price);
            $stmt->bindValue(':description', $description, PDO::PARAM_STR);
            $stmt->bindValue(':image', $image, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Cập nhật phòng (dành cho admin)
     */
    public function updateRoom($id, $hotel_id, $room_number, $room_type, $capacity, $price, $description = '', $image = '')
    {
        $errors = $this->validateRoomData($hotel_id, $room_number, $room_type, $capacity, $price);
        if (count($errors) > 0) return $errors;

        try {
            $sql = "UPDATE " . $this->table_name . "
                    SET hotel_id=:hotel_id, room_number=:room_number, room_type=:room_type,
                        capacity=:capacity, price=:price, description=:description, image=:image
                    WHERE id=:id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmt->bindValue(':hotel_id', (int)$hotel_id, PDO::PARAM_INT);
            $stmt->bindValue(':room_number', $room_number, PDO::PARAM_STR);
            $stmt->bindValue(':room_type', $room_type, PDO::PARAM_STR);
            $stmt->bindValue(':capacity', (int)$capacity, PDO::PARAM_INT);
            $stmt->bindValue(':price', (float)$price);
            $stmt->bindValue(':description', $description, PDO::PARAM_STR);
            $stmt->bindValue(':image', $image, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Xóa phòng (dành cho admin)
     */
    public function deleteRoom($id)
    {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE id=:id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Cập nhật 1 trường cụ thể (AJAX)
     */
    public function updateRoomField($id, $field, $value)
    {
        $allowedFields = ['room_number', 'room_type', 'capacity', 'price', 'description'];
        if (!in_array($field, $allowedFields, true)) return false;

        // Validate value theo field
        if ($field === 'capacity' && (!is_numeric($value) || $value <= 0)) return false;
        if ($field === 'price' && (!is_numeric($value) || $value <= 0)) return false;

        try {
            $sql = "UPDATE room SET {$field} = :value WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':value', $value, PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Validate dữ liệu phòng (dùng trong add/update)
     */
    private function validateRoomData($hotel_id, $room_number, $room_type, $capacity, $price)
    {
        $errors = [];
        if (empty($hotel_id) || !is_numeric($hotel_id)) $errors['hotel_id'] = 'Khách sạn không hợp lệ';
        if (empty($room_number)) $errors['room_number'] = 'Số phòng không được để trống';
        if (empty($room_type) || !in_array($room_type, ALLOWED_ROOM_TYPES, true)) {
            $errors['room_type'] = 'Loại phòng không hợp lệ';
        }

        if (!is_numeric($capacity) || $capacity <= 0) $errors['capacity'] = 'Sức chứa phải lớn hơn 0';
        if (!is_numeric($price) || $price <= 0) $errors['price'] = 'Giá phòng phải lớn hơn 0';

        return $errors;
    }
    /**
     * Lấy tất cả các phòng thuộc về một khách sạn cụ thể
     */
    public function getRoomsByHotelId($hotelId)
    {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE hotel_id = :hotel_id ORDER BY price ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':hotel_id', (int)$hotelId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
    /**
     * Lấy các phòng còn trống của một khách sạn theo khoảng ngày
     */
    public function getAvailableRooms($hotelId, $checkInDate, $checkOutDate, ?string $roomType = null, int $limit = 5, int $offset = 0): array
    {
        $sql = "SELECT * FROM " . $this->table_name . "
                WHERE hotel_id = :hotelId";

        if ($roomType !== null && $roomType !== '') {
            $sql .= " AND room_type = :roomType ";
        }

        $sql .= " AND id NOT IN (
                    SELECT room_id FROM booking
                    WHERE status IN (:status_pending, :status_confirmed, :status_checked_in)
                    AND (check_in_date < :checkOut AND check_out_date > :checkIn)
                 )
                 ORDER BY price ASC
                 LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conn->prepare($sql);
            // Bind các tham số cũ
            $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
            $stmt->bindParam(':checkIn', $checkInDate);
            $stmt->bindParam(':checkOut', $checkOutDate);
            $stmt->bindValue(':status_pending', BOOKING_STATUS_PENDING);
            $stmt->bindValue(':status_confirmed', BOOKING_STATUS_CONFIRMED);
            $stmt->bindValue(':status_checked_in', BOOKING_STATUS_CHECKED_IN);

            if ($roomType !== null && $roomType !== '') {
                $stmt->bindParam(':roomType', $roomType, PDO::PARAM_STR);
            }

            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("getAvailableRooms error: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Lấy danh sách các loại phòng duy nhất và giá thấp nhất của chúng
     * cho một khách sạn cụ thể. (Phiên bản SQL đơn giản hơn)
     * @param int $hotelId ID của khách sạn
     * @return array Mảng các đối tượng, mỗi đối tượng chứa room_type, min_price, capacity
     */
    public function getUniqueRoomTypesByHotelId(int $hotelId): array
    {
        $sql = "SELECT
                    room_type,
                    MIN(price) as min_price,
                    MIN(capacity) as capacity -- Lấy capacity nhỏ nhất trong nhóm làm đại diện
                FROM " . $this->table_name . "
                WHERE hotel_id = :hotelId
                GROUP BY room_type
                ORDER BY min_price ASC"; // Sắp xếp theo giá tăng dần

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("getUniqueRoomTypesByHotelId error: " . $e->getMessage());
            return []; // Trả về mảng rỗng nếu có lỗi
        }
    }
    /**
     * Lấy SỐ LƯỢNG phòng còn trống (để phân trang)
     */
    public function getAvailableRoomsCount($hotelId, $checkInDate, $checkOutDate, ?string $roomType = null): int
    {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . "
                WHERE hotel_id = :hotelId";

        if ($roomType !== null && $roomType !== '') {
            $sql .= " AND room_type = :roomType ";
        }

        $sql .= " AND id NOT IN (
                    SELECT room_id FROM booking
                    WHERE status IN (:status_pending, :status_confirmed, :status_checked_in)
                    AND (check_in_date < :checkOut AND check_out_date > :checkIn)
                 )";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
            $stmt->bindParam(':checkIn', $checkInDate);
            $stmt->bindParam(':checkOut', $checkOutDate);
            $stmt->bindValue(':status_pending', BOOKING_STATUS_PENDING);
            $stmt->bindValue(':status_confirmed', BOOKING_STATUS_CONFIRMED);
            $stmt->bindValue(':status_checked_in', BOOKING_STATUS_CHECKED_IN);

            if ($roomType !== null && $roomType !== '') {
                $stmt->bindParam(':roomType', $roomType, PDO::PARAM_STR);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getAvailableRoomsCount error: " . $e->getMessage());
            return 0;
        }
    }
    /**
     * Lấy ID của một phòng ngẫu nhiên thuộc khách sạn
     * (Dùng để tạo dữ liệu booking giả lập cho script seed)
     *
     * @param int $hotelId
     * @return int|null
     */
    public function getRandomRoomIdForHotel(int $hotelId): ?int
    {
        // Lấy 1 ID phòng bất kỳ
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE hotel_id = :hotelId 
                  ORDER BY RAND() 
                  LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_COLUMN); // Lấy chỉ cột đầu tiên

            return $result ? (int)$result : null; // Trả về ID (int) hoặc null

        } catch (PDOException $e) {
            error_log("getRandomRoomIdForHotel error: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Lấy tổng số phòng (để phân trang)
     */
    public function getRoomCount(?string $searchTerm = null): int
    {
        $query = "SELECT COUNT(r.id) FROM room r 
                  JOIN hotel h ON r.hotel_id = h.id";
        $params = [];

        if (!empty($searchTerm)) {
            $query .= " WHERE r.room_number LIKE :search OR r.room_type LIKE :search OR h.name LIKE :search";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    /**
     * Lấy danh sách tất cả phòng (CÓ PHÂN TRANG VÀ LỌC)
     */
    public function getAllRooms(int $limit, int $offset, ?string $searchTerm = null)
    {
        $query = "SELECT r.*, h.name AS hotel_name, h.rating AS hotel_rating 
                  FROM room r 
                  JOIN hotel h ON r.hotel_id = h.id";

        $params = [
            ':limit' => $limit,
            ':offset' => $offset
        ];

        if (!empty($searchTerm)) {
            $query .= " WHERE r.room_number LIKE :search OR r.room_type LIKE :search OR h.name LIKE :search";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $query .= " ORDER BY r.hotel_id, r.id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        // Bind các tham số
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        if (!empty($searchTerm)) {
            $params[':search'] = '%' . $searchTerm . '%';
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    /**
     * Lấy danh sách phòng (CÓ PHÂN TRANG) kèm theo tên khách sạn và thành phố.
     */
    public function getRoomsWithRelatedData(int $limit, int $offset): array
    {
        $sql = "SELECT r.*, h.name AS hotel_name, c.name AS city_name
                FROM " . $this->table_name . " r
                JOIN hotel h ON r.hotel_id = h.id
                JOIN city c ON h.city_id = c.id
                ORDER BY r.id ASC
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("getRoomsWithRelatedData error: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Lấy tổng số phòng cho một khách sạn cụ thể (CÓ LỌC)
     */
    public function getRoomCountByHotelId(int $hotelId, ?string $searchTerm = null): int
    {
        $query = "SELECT COUNT(r.id) FROM room r 
                  WHERE r.hotel_id = :hotelId";
        $params = [':hotelId' => $hotelId];

        if (!empty($searchTerm)) {
            $query .= " AND (r.room_number LIKE :search OR r.room_type LIKE :search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
        if (!empty($searchTerm)) {
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    /**
     * Lấy danh sách phòng cho một khách sạn cụ thể (CÓ PHÂN TRANG VÀ LỌC)
     */
    public function getRoomsByPartnerHotel(int $hotelId, int $limit, int $offset, ?string $searchTerm = null)
    {
        $query = "SELECT r.*, h.name AS hotel_name
                  FROM room r 
                  JOIN hotel h ON r.hotel_id = h.id
                  WHERE r.hotel_id = :hotelId";

        $params = [':hotelId' => $hotelId];

        if (!empty($searchTerm)) {
            $query .= " AND (r.room_number LIKE :search OR r.room_type LIKE :search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $query .= " ORDER BY r.id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':hotelId', $hotelId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        if (!empty($searchTerm)) {
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

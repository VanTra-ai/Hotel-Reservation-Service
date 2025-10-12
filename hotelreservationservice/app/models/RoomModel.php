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
     * Lấy phòng theo ID
     */
    public function getRoomById($id)
    {
        try {
            $sql = "SELECT r.*, h.name AS hotel_name, c.name AS city_name
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

        $allowedTypes = [
            'Phòng Tiêu Chuẩn Giường Đôi',
            'Phòng Superior Giường Đôi',
            'Phòng Giường Đôi',
            'Phòng Deluxe Giường Đôi Có Ban Công',
            'Phòng Deluxe Giường Đôi',
            'Phòng Giường Đôi Có Ban Công',
            'Phòng Superior Giường Đôi Có Ban Công',
            'Phòng Gia Đình',
            'Phòng Deluxe Gia đình',
            'Phòng Superior Giường Đôi/2 Giường Đơn',
        ];
        if (empty($room_type) || !in_array($room_type, $allowedTypes, true)) $errors['room_type'] = 'Loại phòng không hợp lệ';
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
            // Trong thực tế, bạn nên log lỗi này thay vì để trống
            return [];
        }
    }
}

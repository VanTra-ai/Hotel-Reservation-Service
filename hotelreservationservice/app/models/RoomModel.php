<?php
class RoomModel
{
    private $conn;
    private $table_name = "room";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy tất cả phòng với thông tin khách sạn
    public function getRooms()
    {
        $query = "SELECT r.*, h.name as hotel_name, c.name as city_name 
                  FROM " . $this->table_name . " r
                  LEFT JOIN hotel h ON r.hotel_id = h.id
                  LEFT JOIN city c ON h.city_id = c.id
                  ORDER BY r.hotel_id, r.price ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy danh sách phòng theo ID khách sạn
    public function getRoomsByHotelId($hotelId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE hotel_id = :hotel_id ORDER BY price ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hotel_id', $hotelId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy thông tin phòng theo ID
    public function getRoomById($id)
    {
        $query = "SELECT r.*, h.name as hotel_name, c.name as city_name 
                  FROM " . $this->table_name . " r
                  LEFT JOIN hotel h ON r.hotel_id = h.id
                  LEFT JOIN city c ON h.city_id = c.id
                  WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Thêm phòng mới
    public function addRoom($hotel_id, $room_number, $room_type, $capacity, $price, $description, $image)
    {
        $errors = [];
        if (empty($hotel_id)) {
            $errors['hotel_id'] = 'Khách sạn không được để trống';
        }
        if (empty($room_number)) {
            $errors['room_number'] = 'Số phòng không được để trống';
        }
        // Validate room_type theo danh sách cho phép
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
        if (empty($room_type) || !in_array($room_type, $allowedTypes, true)) {
            $errors['room_type'] = 'Loại phòng không hợp lệ';
        }
        if (empty($capacity) || $capacity <= 0) {
            $errors['capacity'] = 'Sức chứa phải lớn hơn 0';
        }
        if (empty($price) || $price <= 0) {
            $errors['price'] = 'Giá phòng phải lớn hơn 0';
        }
        if (count($errors) > 0) {
            return $errors;
        }

        $query = "INSERT INTO " . $this->table_name . " (hotel_id, room_number, room_type, capacity, price, description, image) 
                  VALUES (:hotel_id, :room_number, :room_type, :capacity, :price, :description, :image)";
        $stmt = $this->conn->prepare($query);

        $hotel_id = htmlspecialchars(strip_tags($hotel_id));
        $room_number = htmlspecialchars(strip_tags($room_number));
        $room_type = htmlspecialchars(strip_tags($room_type));
        $capacity = htmlspecialchars(strip_tags($capacity));
        $price = htmlspecialchars(strip_tags($price));
        $description = htmlspecialchars(strip_tags($description));
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':hotel_id', $hotel_id);
        $stmt->bindParam(':room_number', $room_number);
        $stmt->bindParam(':room_type', $room_type);
        $stmt->bindParam(':capacity', $capacity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image', $image);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Cập nhật phòng
    public function updateRoom($id, $hotel_id, $room_number, $room_type, $capacity, $price, $description, $image)
    {
        // Validate room_type theo danh sách cho phép
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
        if (empty($room_type) || !in_array($room_type, $allowedTypes, true)) {
            return ['room_type' => 'Loại phòng không hợp lệ'];
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET hotel_id=:hotel_id, room_number=:room_number, room_type=:room_type, 
                      capacity=:capacity, price=:price, description=:description, image=:image 
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $hotel_id = htmlspecialchars(strip_tags($hotel_id));
        $room_number = htmlspecialchars(strip_tags($room_number));
        $room_type = htmlspecialchars(strip_tags($room_type));
        $capacity = htmlspecialchars(strip_tags($capacity));
        $price = htmlspecialchars(strip_tags($price));
        $description = htmlspecialchars(strip_tags($description));
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':hotel_id', $hotel_id);
        $stmt->bindParam(':room_number', $room_number);
        $stmt->bindParam(':room_type', $room_type);
        $stmt->bindParam(':capacity', $capacity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image', $image);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Xóa phòng
    public function deleteRoom($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function getAvailableRooms($checkInDate, $checkOutDate)
{
    $sql = "SELECT r.*, h.name AS hotel_name, c.name AS city_name
            FROM rooms r
            JOIN hotels h ON r.hotel_id = h.id
            JOIN cities c ON h.city_id = c.id
            WHERE r.id NOT IN (
                SELECT room_id
                FROM bookings
                WHERE (check_in_date < :checkOutDate AND check_out_date > :checkInDate)
            )";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':checkInDate', $checkInDate);
    $stmt->bindParam(':checkOutDate', $checkOutDate);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
public function getAllRooms()
{
    $sql = "SELECT r.*, h.name AS hotel_name, c.name AS city_name
            FROM rooms r
            JOIN hotels h ON r.hotel_id = h.id
            JOIN cities c ON h.city_id = c.id";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
}

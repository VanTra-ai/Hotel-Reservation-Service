<?php
class BookingModel
{
    private $conn;  // Kết nối database
    private $table_name = "booking";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Tạo một đặt phòng mới
    public function createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice)
    {
        if (!$this->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (account_id, room_id, check_in_date, check_out_date, total_price) 
                  VALUES (:account_id, :room_id, :check_in_date, :check_out_date, :total_price)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':account_id', $accountId);
        $stmt->bindParam(':room_id', $roomId);
        $stmt->bindParam(':check_in_date', $checkInDate);
        $stmt->bindParam(':check_out_date', $checkOutDate);
        $stmt->bindParam(':total_price', $totalPrice);

        return $stmt->execute();
    }

    // Kiểm tra phòng có trống trong khoảng thời gian hay không
    public function isRoomAvailable($roomId, $checkInDate, $checkOutDate)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                  WHERE room_id = :room_id 
                  AND status IN ('pending','confirmed')
                  AND (:check_in < check_out_date) 
                  AND (:check_out > check_in_date)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':room_id', $roomId);
        $stmt->bindParam(':check_in', $checkInDate);
        $stmt->bindParam(':check_out', $checkOutDate);
        $stmt->execute();
        $count = (int)$stmt->fetchColumn();
        return $count === 0;
    }

    // Lấy danh sách booking theo account
    public function getBookingsByAccountId($accountId)
    {
        $stmt = $this->conn->prepare("
            SELECT b.*, r.room_number, r.room_type, h.name AS hotel_name
            FROM booking b
            JOIN room r ON b.room_id = r.id
            JOIN hotel h ON r.hotel_id = h.id
            WHERE b.account_id = ?
            ORDER BY b.check_in_date DESC
        ");
        $stmt->execute([$accountId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Hủy booking
    public function cancelBooking($bookingId, $accountId)
    {
        $stmt = $this->conn->prepare("
            UPDATE " . $this->table_name . "
            SET status = 'cancelled'
            WHERE id = ? AND account_id = ? AND status != 'cancelled'
        ");
        return $stmt->execute([$bookingId, $accountId]);
    }
}

<?php
class BookingModel
{
    private $conn;
    private $table_name = "booking";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Tạo một đặt phòng mới
    public function createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice)
    {
        // Chặn đặt nếu phòng đã có booking chồng chéo thời gian với trạng thái pending/confirmed
        if (!$this->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) {
            return false;
        }
        $query = "INSERT INTO " . $this->table_name . " (account_id, room_id, check_in_date, check_out_date, total_price) VALUES (:account_id, :room_id, :check_in_date, :check_out_date, :total_price)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':account_id', $accountId);
        $stmt->bindParam(':room_id', $roomId);
        $stmt->bindParam(':check_in_date', $checkInDate);
        $stmt->bindParam(':check_out_date', $checkOutDate);
        $stmt->bindParam(':total_price', $totalPrice);

        return $stmt->execute();
    }

    // Kiểm tra phòng có trống trong khoảng thời gian hay không
    // Logic chồng chéo: (new_start < existing_end) AND (new_end > existing_start)
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
}

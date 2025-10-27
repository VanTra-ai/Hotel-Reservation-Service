<?php
class BookingModel
{
    private $conn;
    private $table_name = "booking";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Tạo booking mới
    public function createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice)
    {
        if (!$this->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) return false;

        if (strtotime($checkInDate) >= strtotime($checkOutDate)) return false;

        $sql = "INSERT INTO " . $this->table_name . "
                (account_id, room_id, check_in_date, check_out_date, total_price, status)
                VALUES (:account_id, :room_id, :check_in_date, :check_out_date, :total_price, 'pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':account_id', (int)$accountId, PDO::PARAM_INT);
        $stmt->bindValue(':room_id', (int)$roomId, PDO::PARAM_INT);
        $stmt->bindValue(':check_in_date', $checkInDate);
        $stmt->bindValue(':check_out_date', $checkOutDate);
        $stmt->bindValue(':total_price', (float)$totalPrice);

        return $stmt->execute();
    }

    // Kiểm tra phòng có trống
    public function isRoomAvailable($roomId, $checkInDate, $checkOutDate)
    {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . "
                WHERE room_id=:room_id
                AND status IN ('pending','confirmed')
                AND (:check_in < check_out_date)
                AND (:check_out > check_in_date)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':room_id', (int)$roomId, PDO::PARAM_INT);
        $stmt->bindValue(':check_in', $checkInDate);
        $stmt->bindValue(':check_out', $checkOutDate);
        $stmt->execute();
        return ((int)$stmt->fetchColumn() === 0);
    }

    // Lấy booking theo account
    public function getBookingsByAccountId($accountId)
    {
        $sql = "SELECT b.*, a.username, r.room_number, r.room_type, h.name AS hotel_name
                FROM booking b
                JOIN account a ON b.account_id = a.id
                JOIN room r ON b.room_id = r.id
                JOIN hotel h ON r.hotel_id = h.id
                WHERE b.account_id = :account_id
                ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':account_id', (int)$accountId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Hủy booking
    public function cancelBooking($bookingId, $accountId)
    {
        $sql = "UPDATE " . $this->table_name . "
                SET status='cancelled'
                WHERE id=:bookingId AND account_id=:accountId AND status!='cancelled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':bookingId', (int)$bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    /**
     * Lấy tất cả booking.
     * Nếu có $ownerId, chỉ lấy booking của các khách sạn thuộc sở hữu của người đó.
     */
    public function getAllBookingsWithInfo($ownerId = null)
    {
        $params = [];
        $sql = "SELECT b.*, a.username, r.room_number, r.room_type, h.name AS hotel_name
                FROM booking b
                JOIN account a ON b.account_id = a.id
                JOIN room r ON b.room_id = r.id
                JOIN hotel h ON r.hotel_id = h.id";

        // Nếu có ownerId, thêm điều kiện WHERE
        if ($ownerId) {
            $sql .= " WHERE h.owner_id = :ownerId";
            $params[':ownerId'] = $ownerId;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Cập nhật trạng thái booking
    public function updateBookingStatus($bookingId, $status)
    {
        $stmt = $this->conn->prepare("UPDATE booking SET status=:status WHERE id=:id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', (int)$bookingId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    /**
     * Lấy thông tin booking cho Partner Calendar
     */
    public function getBookingsForCalendar($ownerId)
    {
        $sql = "SELECT 
                    b.id, b.check_in_date, b.check_out_date, b.status,
                    r.room_number,
                    a.username
                FROM booking b
                JOIN room r ON b.room_id = r.id
                JOIN hotel h ON r.hotel_id = h.id
                JOIN account a ON b.account_id = a.id
                WHERE h.owner_id = :ownerId
                AND b.status != 'cancelled' 
                AND b.status != 'checked_out'";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':ownerId', $ownerId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("getBookingsForCalendar error: " . $e->getMessage());
            return [];
        }
    }
}

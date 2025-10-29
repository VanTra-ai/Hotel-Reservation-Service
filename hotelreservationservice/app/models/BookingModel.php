<?php
// app/models/BookingModel.php
class BookingModel
{
    private $conn;
    private $table_name = "booking";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Tạo booking mới
    public function createBooking($accountId, $roomId, $checkInDate, $checkOutDate, $totalPrice, $groupType = null)
    {
        if (!$this->isRoomAvailable($roomId, $checkInDate, $checkOutDate)) return false;

        if (strtotime($checkInDate) >= strtotime($checkOutDate)) return false;

        $sql = "INSERT INTO " . $this->table_name . "
                (account_id, room_id, check_in_date, check_out_date, total_price, status, group_type)
                VALUES (:account_id, :room_id, :check_in_date, :check_out_date, :total_price, :status, :group_type)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':account_id', (int)$accountId, PDO::PARAM_INT);
            $stmt->bindValue(':room_id', (int)$roomId, PDO::PARAM_INT);
            $stmt->bindValue(':check_in_date', $checkInDate);
            $stmt->bindValue(':check_out_date', $checkOutDate);
            $stmt->bindValue(':total_price', (float)$totalPrice);
            $stmt->bindValue(':status', BOOKING_STATUS_PENDING);
            $stmt->bindValue(':group_type', $groupType, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create booking error: " . $e->getMessage());
            return false;
        }
    }

    // Kiểm tra phòng có trống
    public function isRoomAvailable($roomId, $checkInDate, $checkOutDate)
    {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . "
            WHERE room_id=:room_id
            -- Sử dụng constants cho trạng thái cần kiểm tra
            AND status IN (:status_pending, :status_confirmed)
            AND (:check_in < check_out_date)
            AND (:check_out > check_in_date)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':room_id', (int)$roomId, PDO::PARAM_INT);
        $stmt->bindValue(':check_in', $checkInDate);
        $stmt->bindValue(':check_out', $checkOutDate);
        // Bind các trạng thái
        $stmt->bindValue(':status_pending', BOOKING_STATUS_PENDING);
        $stmt->bindValue(':status_confirmed', BOOKING_STATUS_CONFIRMED);
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
                SET status= :status_cancelled
                WHERE id=:bookingId AND account_id=:accountId AND status!='cancelled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':bookingId', (int)$bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
        $stmt->bindValue(':status_cancelled', BOOKING_STATUS_CANCELLED);
        return $stmt->execute();
    }
    /**
     * Lấy tất cả booking.
     * Nếu có $ownerId, chỉ lấy booking của các khách sạn thuộc sở hữu của người đó.
     */
    public function getAllBookings(int $limit, int $offset, ?string $searchTerm = null)
    {
        $query = "SELECT 
                    b.id, b.check_in_date, b.check_out_date, b.total_price, b.status, b.created_at,
                    h.name AS hotel_name, 
                    a.fullname AS username,
                    r.room_number,
                    r.room_type
                  FROM booking b 
                  JOIN room r ON b.room_id = r.id         
                  JOIN hotel h ON r.hotel_id = h.id       
                  LEFT JOIN account a ON b.account_id = a.id";

        $params = [
            ':limit' => $limit,
            ':offset' => $offset
        ];

        // Thêm điều kiện tìm kiếm (WHERE)
        if (!empty($searchTerm)) {
            $query .= " WHERE a.fullname LIKE :search OR a.email LIKE :search OR b.id LIKE :search";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $query .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        // Bind các tham số
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        if (!empty($searchTerm)) {
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }

        $stmt->execute();
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
                AND b.status != :status_cancelled 
                AND b.status != :status_checked_out";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':ownerId', $ownerId, PDO::PARAM_INT);
            $stmt->bindValue(':status_cancelled', BOOKING_STATUS_CANCELLED);
            $stmt->bindValue(':status_checked_out', BOOKING_STATUS_CHECKED_OUT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("getBookingsForCalendar error: " . $e->getMessage());
            return [];
        }
    }
    // Lấy thông tin booking để tạo form review
    public function getBookingByIdForReview($bookingId, $accountId)
    {
        $sql = "SELECT b.*, r.hotel_id, r.room_type, r.room_number, h.name AS hotel_name, b.group_type,
                        (SELECT COUNT(*) FROM review rev WHERE rev.booking_id = b.id) as review_count
                 FROM booking b
                 JOIN room r ON b.room_id = r.id
                 JOIN hotel h ON r.hotel_id = h.id
                 WHERE b.id = :bookingId AND b.account_id = :accountId AND b.status = :status_checked_out
                 LIMIT 1";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':bookingId', (int)$bookingId, PDO::PARAM_INT);
            $stmt->bindValue(':accountId', (int)$accountId, PDO::PARAM_INT);
            $stmt->bindValue(':status_checked_out', BOOKING_STATUS_CHECKED_OUT);
            $stmt->execute();
            $booking = $stmt->fetch(PDO::FETCH_OBJ);

            if ($booking) {
                $check_in = strtotime($booking->check_in_date);
                $check_out = strtotime($booking->check_out_date);
                $diff = $check_out - $check_in;
                $nights = max(1, round($diff / (60 * 60 * 24)));
                $booking->nights = $nights;
            }

            return $booking;
        } catch (PDOException $e) {
            error_log("getBookingByIdForReview error: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Lấy tổng số lượng đặt phòng (CÓ LỌC)
     */
    public function getBookingCount(?string $searchTerm = null): int
    {
        $query = "SELECT COUNT(b.id) FROM booking b 
                  LEFT JOIN account a ON b.account_id = a.id";
        $params = [];

        if (!empty($searchTerm)) {
            // Lọc theo fullname, email, ID Booking
            $query .= " WHERE a.fullname LIKE :search OR a.email LIKE :search OR b.id LIKE :search";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    /**
     * Lấy tổng số lượng đặt phòng của Partner (CÓ LỌC)
     * @param int $ownerId ID của Partner (chủ sở hữu khách sạn)
     */
    public function getPartnerBookingCount(int $ownerId, ?string $searchTerm = null): int
    {
        $query = "SELECT COUNT(b.id) 
                  FROM booking b 
                  JOIN room r ON b.room_id = r.id
                  JOIN hotel h ON r.hotel_id = h.id
                  LEFT JOIN account a ON b.account_id = a.id
                  WHERE h.owner_id = :ownerId";
        $params = [':ownerId' => $ownerId]; // Bắt đầu với :ownerId

        if (!empty($searchTerm)) {
            $query .= " AND (a.fullname LIKE :search OR a.email LIKE :search OR b.id LIKE :search OR r.room_number LIKE :search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':ownerId', $ownerId, PDO::PARAM_INT);
        if (!empty($searchTerm)) {
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    /**
     * Lấy danh sách đặt phòng của Partner (CÓ PHÂN TRANG, LỌC)
     */
    public function getAllPartnerBookings(int $ownerId, int $limit, int $offset, ?string $searchTerm = null): array
    {
        $query = "SELECT 
                    b.id, b.check_in_date, b.check_out_date, b.total_price, b.status, b.created_at,
                    h.name AS hotel_name, h.id AS hotel_id,
                    a.fullname AS customer_name, a.email AS customer_email, a.id AS customer_id,
                    r.room_number, r.room_type
                  FROM booking b 
                  JOIN room r ON b.room_id = r.id         
                  JOIN hotel h ON r.hotel_id = h.id       
                  LEFT JOIN account a ON b.account_id = a.id
                  WHERE h.owner_id = :ownerId";

        $params = [':ownerId' => $ownerId]; // Bắt đầu với :ownerId

        if (!empty($searchTerm)) {
            $query .= " AND (a.fullname LIKE :search OR a.email LIKE :search OR b.id LIKE :search OR r.room_number LIKE :search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $query .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':ownerId', $ownerId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        if (!empty($searchTerm)) {
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

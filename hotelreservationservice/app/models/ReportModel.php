<?php
// app/models/ReportModel.php
class ReportModel
{
    private $conn;
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Lấy các chỉ số thống kê tổng quan.
     * Nếu có $ownerId, chỉ tính cho các khách sạn của người đó.
     */
    public function getOverallStats($ownerId = null)
    {
        $params = [];
        if ($ownerId) {
            // SQL dành riêng cho Partner
            $sql = "SELECT 
                        COUNT(b.id) as total_bookings,
                        SUM(CASE WHEN b.status IN ('confirmed', 'checked_out') THEN b.total_price ELSE 0 END) as total_revenue,
                        (SELECT COUNT(*) FROM hotel h_sub WHERE h_sub.owner_id = :ownerId) as total_hotels
                    FROM booking b
                    JOIN room r ON b.room_id = r.id
                    JOIN hotel h ON r.hotel_id = h.id
                    WHERE h.owner_id = :ownerId";
            $params[':ownerId'] = $ownerId;
        } else {
            // SQL dành cho Admin (toàn hệ thống)
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM booking) as total_bookings,
                        (SELECT SUM(total_price) FROM booking WHERE status IN ('confirmed', 'checked_out')) as total_revenue,
                        (SELECT COUNT(*) FROM account) as total_users,
                        (SELECT COUNT(*) FROM hotel) as total_hotels
                    ";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Lấy phân phối trạng thái booking.
     * Nếu có $ownerId, chỉ tính cho các khách sạn của người đó.
     */
    public function getBookingStatusDistribution($ownerId = null)
    {
        $params = [];
        $sql = "SELECT b.status, COUNT(b.id) as count 
                FROM booking b";

        if ($ownerId) {
            $sql .= " JOIN room r ON b.room_id = r.id
                      JOIN hotel h ON r.hotel_id = h.id
                      WHERE h.owner_id = :ownerId";
            $params[':ownerId'] = $ownerId;
        }

        $sql .= " GROUP BY b.status";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Lấy doanh thu theo từng ngày trong tháng.
     * Nếu có $ownerId, chỉ tính cho các khách sạn của người đó.
     */
    public function getDailyRevenueForMonth($year, $month, $ownerId = null)
    {
        $params = [':year' => $year, ':month' => $month];
        $sql = "SELECT 
                    DAY(b.created_at) as day, 
                    SUM(b.total_price) as revenue 
                FROM booking b";

        if ($ownerId) {
            $sql .= " JOIN room r ON b.room_id = r.id
                      JOIN hotel h ON r.hotel_id = h.id
                      WHERE YEAR(b.created_at) = :year AND 
                            MONTH(b.created_at) = :month AND
                            b.status IN ('confirmed', 'checked_out') AND
                            h.owner_id = :ownerId";
            $params[':ownerId'] = $ownerId;
        } else {
            $sql .= " WHERE YEAR(b.created_at) = :year AND 
                             MONTH(b.created_at) = :month AND
                             b.status IN ('confirmed', 'checked_out')";
        }

        $sql .= " GROUP BY DAY(b.created_at) ORDER BY day ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

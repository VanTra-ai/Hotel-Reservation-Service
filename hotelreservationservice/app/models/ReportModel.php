<?php
// app/models/ReportModel.php
class ReportModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // XÓA 3 HÀM CŨ: getOverallStats, getBookingStatusDistribution, getDailyRevenueForMonth

    /**
     * Hàm helper để xây dựng WHERE clause động
     */
    private function buildWhereClause(
        ?int $cityId,
        ?int $hotelId,
        ?int $month,
        ?int $year,
        string $bookingAlias = 'b',
        string $hotelAlias = 'h'
    ): array {
        $where = " WHERE 1=1 ";
        $params = [];

        if ($hotelId) {
            $where .= " AND $hotelAlias.id = :hotelId ";
            $params[':hotelId'] = $hotelId;
        } elseif ($cityId) {
            $where .= " AND $hotelAlias.city_id = :cityId ";
            $params[':cityId'] = $cityId;
        }

        // SỬA: Chỉ lọc theo booking_date nếu groupBy là 'day'
        if ($month && $year) {
            $where .= " AND MONTH(b.created_at) = :month AND YEAR(b.created_at) = :year ";
            $params[':month'] = $month;
            $params[':year'] = $year;
        } elseif ($year) {
            $where .= " AND YEAR(b.created_at) = :year ";
            $params[':year'] = $year;
        }

        return ['where' => $where, 'params' => $params];
    }

    /**
     * Lấy tổng số booking (Đã lọc)
     */
    public function getBookingCount(?int $cityId, ?int $hotelId, ?int $month, ?int $year): int
    {
        $filters = $this->buildWhereClause($cityId, $hotelId, $month, $year);

        $sql = "SELECT COUNT(b.id) 
                FROM booking b
                JOIN room r ON b.room_id = r.id
                JOIN hotel h ON r.hotel_id = h.id" . $filters['where'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($filters['params']);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy tổng doanh thu (Đã lọc)
     */
    public function getTotalRevenue(?int $cityId, ?int $hotelId, ?int $month, ?int $year): float
    {
        $filters = $this->buildWhereClause($cityId, $hotelId, $month, $year);

        $sql = "SELECT SUM(b.total_price) 
                FROM booking b
                JOIN room r ON b.room_id = r.id
                JOIN hotel h ON r.hotel_id = h.id" . $filters['where'] .
            " AND b.status = :status";

        $filters['params'][':status'] = BOOKING_STATUS_CHECKED_OUT;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($filters['params']);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Lấy tổng số thành viên (Không bị ảnh hưởng bởi lọc)
     */
    public function getMemberCount(): int
    {
        // SỬA: Chỉ đếm 'user', không đếm 'admin' hay 'partner'
        $stmt = $this->db->prepare("SELECT COUNT(id) FROM account WHERE role = 'user'");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy tổng số khách sạn (Lọc theo thành phố)
     */
    public function getHotelCount(?int $cityId): int
    {
        $sql = "SELECT COUNT(id) FROM hotel";
        $params = [];
        if ($cityId) {
            $sql .= " WHERE city_id = :cityId";
            $params[':cityId'] = $cityId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy dữ liệu biểu đồ doanh thu (Đã lọc VÀ Group By)
     */
    public function getDailyRevenueData(?int $cityId, ?int $hotelId, ?int $month, ?int $year, string $groupBy = 'day'): array
    {
        // SỬA: Truyền $month và $year dựa trên $groupBy
        $filterMonth = ($groupBy === 'day') ? $month : null; // Chỉ lọc theo tháng nếu xem theo ngày
        $filterYear = ($groupBy === 'day' || $groupBy === 'month') ? $year : null; // Lọc theo năm nếu xem theo ngày/tháng

        $filters = $this->buildWhereClause($cityId, $hotelId, $filterMonth, $filterYear);

        $sql = "SELECT ";
        $groupBySql = "";

        switch ($groupBy) {
            case 'month':
                $sql .= " MONTH(b.created_at) as month, YEAR(b.created_at) as year, SUM(b.total_price) as revenue";
                $groupBySql = " GROUP BY YEAR(b.created_at), MONTH(b.created_at) ORDER BY year, month ASC";
                break;
            case 'year':
                $sql .= " YEAR(b.created_at) as year, SUM(b.total_price) as revenue";
                $groupBySql = " GROUP BY YEAR(b.created_at) ORDER BY year ASC";
                break;
            case 'day':
            default:
                $sql .= " DATE(b.created_at) as booking_date, SUM(b.total_price) as daily_revenue";
                $groupBySql = " GROUP BY DATE(b.created_at) ORDER BY booking_date ASC";
                break;
        }

        $sql .= " FROM booking b
                JOIN room r ON b.room_id = r.id
                JOIN hotel h ON r.hotel_id = h.id" . $filters['where'] .
            " AND b.status = :status" . $groupBySql;

        $filters['params'][':status'] = BOOKING_STATUS_CHECKED_OUT;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($filters['params']);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Lấy dữ liệu thống kê trạng thái (Đã lọc)
     */
    public function getBookingStatusData(?int $cityId, ?int $hotelId, ?int $month, ?int $year): array
    {
        $filters = $this->buildWhereClause($cityId, $hotelId, $month, $year);

        $sql = "SELECT status, COUNT(*) as count 
                FROM booking b
                JOIN room r ON b.room_id = r.id
                JOIN hotel h ON r.hotel_id = h.id" . $filters['where'] .
            " GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($filters['params']);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

// app/controllers/AdminBookingController.php
<?php
require_once 'app/config/database.php';
require_once 'app/helpers/SessionHelper.php';

class AdminBookingController
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        SessionHelper::startSession();
        
        if (!SessionHelper::isLoggedIn() || !SessionHelper::isAdmin()) {
            header('Location: /hotelreservationservice/account/login');
            exit;
        }
    }
    public function index()
    {
        $sql = "SELECT b.*, u.username, r.room_number, r.room_type, h.name AS hotel_name
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                JOIN hotels h ON r.hotel_id = h.id
                ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_OBJ);

        include 'app/views/admin/bookings/list.php';
    }

    // Cập nhật trạng thái booking: pending, confirmed, cancelled, checked_in, checked_out
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? 'pending';
            $sql = "UPDATE bookings SET status=:status WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            header('Location: /hotelreservationservice/admin/bookings');
        }
    }

    // Hủy booking
    public function cancel($id)
    {
        $sql = "UPDATE bookings SET status='cancelled' WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header('Location: /hotelreservationservice/admin/bookings');
    }
}

<?php
// app/controllers/PartnerAvailabilityController.php

require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/RoomModel.php';
require_once 'app/models/BookingModel.php';
require_once 'app/models/HotelModel.php';

class PartnerAvailabilityController extends BasePartnerController
{
    private $roomModel;
    private $bookingModel;
    private $partnerHotel;

    public function __construct()
    {
        parent::__construct();
        $this->roomModel = new RoomModel($this->db);
        $this->bookingModel = new BookingModel($this->db);

        $partnerId = SessionHelper::getAccountId();
        $this->partnerHotel = (new HotelModel($this->db))->getHotelByOwnerId($partnerId);

        // Đảm bảo Partner có khách sạn trước khi tiếp tục
        if (!$this->partnerHotel) {
            header('Location: ' . BASE_URL . '/partner/dashboard');
            exit;
        }
    }

    /**
     * Hiển thị bảng điều khiển phòng trống (Availability Matrix)
     */
    public function index()
    {
        $hotelId = $this->partnerHotel->id;

        // 1. Xác định phạm vi ngày
        $today = new DateTime();
        $defaultEndDate = clone $today;
        $defaultEndDate->modify('+30 days');
        $startDate = $_GET['start_date'] ?? $today->format('Y-m-d');
        $endDate = $_GET['end_date'] ?? $defaultEndDate->format('Y-m-d');

        if (strtotime($endDate) <= strtotime($startDate)) {
            $endDate = $defaultEndDate->format('Y-m-d');
            $startDate = $today->format('Y-m-d');
        }

        // 2. Lấy TỔNG SỐ LƯỢNG phòng (Tổng kho)
        // (VD: ['Phòng Deluxe' => 10, 'Phòng Standard' => 20])
        $roomTypeInventory = $this->roomModel->getRoomCountsPerType($hotelId);

        // 3. Lấy tất cả booking trong phạm vi ngày
        $bookings = $this->bookingModel->getBookingCountsPerTypeAndDay($hotelId, $startDate, $endDate);

        // 4. Xây dựng ma trận phòng trống ($availabilityMatrix)
        $availabilityMatrix = $this->buildAvailabilityMatrix(
            $roomTypeInventory,
            $bookings,
            $startDate,
            $endDate
        );

        $data = [
            'hotel_name' => $this->partnerHotel->name,
            'room_types' => $roomTypeInventory, // Gửi kho tổng
            'matrix' => $availabilityMatrix,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        include 'app/views/partner/availability/index.php';
    }

    /**
     * Hàm helper xây dựng ma trận phòng trống/đã đặt
     */
    private function buildAvailabilityMatrix(array $roomTypeInventory, array $bookings, string $startDate, string $endDate): array
    {
        $matrix = [];
        $currentDate = new DateTime($startDate);
        $endDateObj = new DateTime($endDate);

        // 1. Khởi tạo ma trận với số lượng 'total' (tổng kho)
        // Cấu trúc: $matrix[room_type][date] = ['total' => 20, 'booked' => 0]
        foreach ($roomTypeInventory as $roomType => $totalCount) {
            $tempDate = clone $currentDate;
            while ($tempDate < $endDateObj) {
                $dateKey = $tempDate->format('Y-m-d');
                $matrix[$roomType][$dateKey] = [
                    'total' => $totalCount,
                    'booked' => 0,
                    'status_details' => [] // (Nâng cao: lưu chi tiết status)
                ];
                $tempDate->modify('+1 day');
            }
        }

        // 2. Lấp đầy ma trận với số lượng 'booked'
        foreach ($bookings as $booking) {
            $roomType = $booking->room_type;

            // Đảm bảo loại phòng này có trong kho (tránh lỗi nếu phòng bị xóa)
            if (!isset($matrix[$roomType])) continue;

            $start = new DateTime($booking->check_in_date);
            $end = new DateTime($booking->check_out_date);

            $slotDate = clone $start;
            while ($slotDate < $end) {
                $dateKey = $slotDate->format('Y-m-d');

                // Chỉ điền vào những ngày nằm trong phạm vi người dùng chọn
                if (isset($matrix[$roomType][$dateKey])) {
                    // Tăng số lượng đã đặt cho ngày này
                    $matrix[$roomType][$dateKey]['booked'] += $booking->count_per_day_slot;

                    // (Nâng cao: Thêm chi tiết trạng thái)
                    if (!isset($matrix[$roomType][$dateKey]['status_details'][$booking->status])) {
                        $matrix[$roomType][$dateKey]['status_details'][$booking->status] = 0;
                    }
                    $matrix[$roomType][$dateKey]['status_details'][$booking->status] += $booking->count_per_day_slot;
                }

                $slotDate->modify('+1 day');
            }
        }

        return $matrix;
    }
}

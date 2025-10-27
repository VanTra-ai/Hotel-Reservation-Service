<?php
// app/controllers/PartnerCalendarController.php

require_once 'app/controllers/BasePartnerController.php';
require_once 'app/models/BookingModel.php';

class PartnerCalendarController extends BasePartnerController
{
    private $bookingModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookingModel = new BookingModel($this->db);
    }

    /**
     * Hiển thị trang lịch
     */
    public function index()
    {
        include 'app/views/partner/calendar/index.php';
    }

    /**
     * API nội bộ cung cấp sự kiện cho FullCalendar
     */
    public function getEvents()
    {
        header('Content-Type: application/json');
        $partnerId = SessionHelper::getAccountId();

        $bookings = $this->bookingModel->getBookingsForCalendar($partnerId);

        $events = [];
        foreach ($bookings as $booking) {
            $color = '#0d6efd'; // Mặc định (pending)
            if ($booking->status == 'confirmed') $color = '#198754'; // Xanh lá
            if ($booking->status == 'checked_in') $color = '#fd7e14'; // Cam

            $events[] = [
                'title' => 'Phòng ' . $booking->room_number . ' - ' . $booking->username,
                'start' => $booking->check_in_date,
                'end' => $booking->check_out_date, // FullCalendar tự động xử lý ngày kết thúc
                'color' => $color,
                'extendedProps' => [ // Thêm thông tin tùy chỉnh
                    'booking_id' => $booking->id,
                    'status' => $booking->status
                ]
            ];
        }

        echo json_encode($events);
        exit;
    }
}

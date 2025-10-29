<?php include 'app/views/shares/header.php';
//app/views/partner/calendar/index.php
?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<div class="container my-5">
    <h2 class="fw-bold mb-4">Lịch trình Đặt phòng</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/vi.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth', // Giao diện tháng
            locale: 'vi', // Ngôn ngữ Tiếng Việt
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dayMaxEvents: true,
            // 4. Lấy dữ liệu sự kiện từ Controller
            events: '<?= BASE_URL ?>/partner/calendar/getEvents',

            // 5. Thêm hiệu ứng khi di chuột vào sự kiện
            eventDidMount: function(info) {
                var tooltip = new bootstrap.Tooltip(info.el, {
                    title: `<b>${info.event.title}</b><br>Trạng thái: ${info.event.extendedProps.status}`,
                    html: true,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            }
        });
        calendar.render();
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
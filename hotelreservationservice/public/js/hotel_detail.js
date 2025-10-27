// public/js/hotel_detail.js
document.addEventListener("DOMContentLoaded", function () {
  const checkInInput = document.getElementById("ajax_check_in");
  const checkOutInput = document.getElementById("ajax_check_out");
  const filterButton = document.getElementById("filter-rooms-btn");
  const roomListContainer = document.getElementById("room-list-container");
  const hotelIdInput = document.getElementById("hotel_id_for_ajax");

  if (!checkInInput || !filterButton || !roomListContainer) {
    return;
  }

  const checkInPicker = flatpickr(checkInInput, {
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function (selectedDates) {
      if (selectedDates[0]) {
        checkOutPicker.set("minDate", new Date(selectedDates[0]).fp_incr(1));
      }
    },
  });
  const checkOutPicker = flatpickr(checkOutInput, {
    minDate: new Date().fp_incr(1),
    dateFormat: "Y-m-d",
  });

  // Logic lọc AJAX (để có thể tái sử dụng) ---
  function filterRooms() {
    const hotelId = hotelIdInput.value;
    const checkIn = checkInPicker.input.value;
    const checkOut = checkOutPicker.input.value;

    if (!checkIn || !checkOut) {
      alert("Vui lòng chọn cả ngày nhận và trả phòng.");
      return;
    }

    roomListContainer.innerHTML =
      '<li class="list-group-item text-center">Đang tìm kiếm...</li>';

    const formData = new FormData();
    formData.append("hotel_id", hotelId);
    formData.append("check_in", checkIn);
    formData.append("check_out", checkOut);

    fetch(BASE_URL + "/room/getAvailableRoomsAjax", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((rooms) => {
        roomListContainer.innerHTML = "";
        if (rooms.error) {
          roomListContainer.innerHTML = `<li class="list-group-item text-center text-danger">${rooms.error}</li>`;
        } else if (rooms.length === 0) {
          roomListContainer.innerHTML =
            '<li class="list-group-item text-center text-info">Không tìm thấy phòng trống cho ngày bạn chọn.</li>';
        } else {
          rooms.forEach((room) => {
            const price = new Intl.NumberFormat("vi-VN").format(room.price);
            const bookingUrl = `${BASE_URL}/booking/bookRoom?room_id=${room.id}&check_in=${checkIn}&check_out=${checkOut}`;

            roomListContainer.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${room.room_type}</strong>
                                <p class="mb-0 text-muted">Sức chứa: ${room.capacity} người</p>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-success d-block mb-1">${price} VNĐ/đêm</span>
                                <!-- Sử dụng bookingUrl đã tạo -->
                                <a href="${bookingUrl}" class="btn btn-primary btn-sm">Đặt ngay</a>
                            </div>
                        </li>
                    `;
          });
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        roomListContainer.innerHTML =
          '<li class="list-group-item text-center text-danger">Có lỗi xảy ra khi tải dữ liệu.</li>';
      });
  }

  // Lắng nghe sự kiện click nút "Kiểm tra"
  filterButton.addEventListener("click", filterRooms);

  // Tự động chạy khi trang tải nếu có ngày sẵn ---
  if (
    filterButton.dataset.autorun === "true" &&
    checkInInput.value &&
    checkOutInput.value
  ) {
    console.log("Phát hiện ngày có sẵn, tự động lọc...");
    filterRooms();
  }
});

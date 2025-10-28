document.addEventListener("DOMContentLoaded", function () {
  const checkInInput = document.getElementById("ajax_check_in");
  const checkOutInput = document.getElementById("ajax_check_out");
  const filterButton = document.getElementById("filter-rooms-btn");
  const paginationContainer = document.getElementById(
    "available-rooms-pagination"
  );

  const availableRoomsContainer = document.getElementById(
    "available-rooms-details"
  );
  const availableRoomsList = availableRoomsContainer
    ? availableRoomsContainer.querySelector("ul")
    : null;

  const hotelIdInput = document.getElementById("hotel_id_for_ajax");

  if (
    !checkInInput ||
    !checkOutInput ||
    !filterButton ||
    !availableRoomsContainer ||
    !availableRoomsList ||
    !hotelIdInput ||
    !paginationContainer
  ) {
    console.error("Thiếu một hoặc nhiều phần tử DOM cần thiết.");
    return;
  }

  // --- Khởi tạo Flatpickr ---
  const checkInPicker = flatpickr(checkInInput, {
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function (selectedDates) {
      if (selectedDates[0]) {
        const nextDay = new Date(selectedDates[0]);
        nextDay.setDate(nextDay.getDate() + 1); // Ngày kế tiếp
        checkOutPicker.set("minDate", nextDay);
        if (
          checkOutPicker.selectedDates.length > 0 &&
          checkOutPicker.selectedDates[0] <= selectedDates[0]
        ) {
          checkOutPicker.clear();
        }
      } else {
        checkOutPicker.set("minDate", new Date().fp_incr(1));
      }
    },
  });
  const checkOutPicker = flatpickr(checkOutInput, {
    minDate: checkInInput.value
      ? new Date(checkInInput.value).fp_incr(1)
      : new Date().fp_incr(1),
    dateFormat: "Y-m-d",
  });

  // Biến lưu bộ lọc hiện tại
  let currentRoomTypeFilter = null;

  // --- Logic lọc AJAX ---
  function filterRooms(roomTypeFilter = null, page = 1) {
    currentRoomTypeFilter = roomTypeFilter; // Lưu bộ lọc hiện tại

    const hotelId = hotelIdInput.value;
    const checkIn = checkInPicker.input.value;
    const checkOut = checkOutPicker.input.value;

    if (!checkIn || !checkOut) {
      alert("Vui lòng chọn cả ngày nhận và trả phòng.");
      availableRoomsContainer.style.display = "none";
      return;
    }

    availableRoomsContainer.style.display = "block";
    availableRoomsList.innerHTML =
      '<li class="list-group-item text-center"><div class="spinner-border spinner-border-sm" role="status">...</div></li>';
    paginationContainer.innerHTML = ""; // Xóa phân trang cũ

    const formData = new FormData();
    formData.append("hotel_id", hotelId);
    formData.append("check_in", checkIn);
    formData.append("check_out", checkOut);
    formData.append("page", page);

    if (roomTypeFilter) {
      formData.append("room_type", roomTypeFilter);
      const heading = availableRoomsContainer.querySelector("h5");
      if (heading) {
        heading.textContent = `Phòng trống loại "${roomTypeFilter}" cho ngày đã chọn:`;
      }
    } else {
      const heading = availableRoomsContainer.querySelector("h5");
      if (heading) {
        heading.textContent = `Phòng trống chi tiết cho ngày đã chọn:`;
      }
    }

    fetch(BASE_URL + "/room/getAvailableRoomsAjax", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        availableRoomsList.innerHTML = ""; // Xóa loading

        if (data.error) {
          availableRoomsList.innerHTML = `<li class="list-group-item text-center text-danger">${data.error}</li>`;
        } else if (!Array.isArray(data.rooms) || data.rooms.length === 0) {
          const message = roomTypeFilter
            ? `Không tìm thấy phòng trống loại "${roomTypeFilter}"...`
            : "Không tìm thấy phòng trống nào...";
          availableRoomsList.innerHTML = `<li class="list-group-item text-center text-info">${message}</li>`;
        } else {
          // Hiển thị phòng
          data.rooms.forEach((room) => {
            const price = new Intl.NumberFormat("vi-VN").format(room.price);
            const bookingUrl = `${BASE_URL}/booking/bookRoom?room_id=${room.id}&check_in=${checkIn}&check_out=${checkOut}`;

            const roomLi = document.createElement("li");
            roomLi.className =
              "list-group-item d-flex justify-content-between align-items-center shadow-sm mb-3 rounded-3";

            roomLi.innerHTML = `
                        <div>
                            <strong>Phòng ${room.room_number}</strong>
                            <p class="mb-0 text-muted">Loại phòng: ${
                              room.room_type
                            }</p>
                            <p class="mb-0 text-muted" style="font-size: 0.9em;">Sức chứa: ${
                              room.capacity
                            } người</p>
                            ${
                              room.description
                                ? `<small class="text-muted d-block mt-1">${room.description.substring(
                                    0,
                                    100
                                  )}...</small>`
                                : ""
                            }
                        </div>
                        <div class="text-end">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8em;">Giá cho ngày đã chọn</span>
                            <span class="fw-bold text-success d-block mb-1">${price} VNĐ/đêm</span>
                            <a href="${bookingUrl}" class="btn btn-primary btn-sm">Đặt ngay</a>
                        </div>
                    `;
            availableRoomsList.appendChild(roomLi);
          });
          // Render phân trang
          renderPagination(data.pagination);
        }
      })
      .catch((error) => {
        console.error("Fetch Error:", error);
        availableRoomsList.innerHTML =
          '<li class="list-group-item text-center text-danger">Có lỗi xảy ra khi tải dữ liệu phòng trống. Vui lòng thử lại.</li>';
      });
  }

  /**
   * Hàm render các nút phân trang
   */
  function renderPagination(pagination) {
    paginationContainer.innerHTML = ""; // Xóa sạch

    const totalPages = pagination.total_pages;
    const currentPage = pagination.current_page;
    const window = 1; // Số trang hiển thị ở mỗi bên của trang hiện tại (ví dụ: 12, [13], 14)

    if (totalPages <= 1) return; // Không cần nếu chỉ có 1 trang

    const nav = document.createElement("nav");
    nav.setAttribute("aria-label", "Phân trang phòng trống");
    const ul = document.createElement("ul");
    ul.className = "pagination pagination-sm justify-content-center";

    // Hàm trợ giúp tạo link
    const createPageLink = (
      page,
      text,
      isDisabled = false,
      isActive = false
    ) => {
      const li = document.createElement("li");
      li.className = `page-item ${isDisabled ? "disabled" : ""} ${
        isActive ? "active" : ""
      }`;
      const a = document.createElement("a");
      a.className = "page-link";
      a.href = "#";
      a.textContent = text;
      if (!isDisabled) {
        a.dataset.page = page;
      }
      li.appendChild(a);
      return li;
    };

    // Hàm trợ giúp tạo dấu ...
    const createDots = () => {
      const li = document.createElement("li");
      li.className = "page-item disabled";
      const span = document.createElement("span");
      span.className = "page-link";
      span.textContent = "...";
      li.appendChild(span);
      return li;
    };

    // Nút Trang trước
    ul.appendChild(createPageLink(currentPage - 1, "Trước", currentPage <= 1));

    // --- Logic Sliding Window ---

    // Hiển thị trang 1
    ul.appendChild(createPageLink(1, "1", false, currentPage === 1));

    // Dấu ... (bên trái)
    if (currentPage > window + 2) {
      ul.appendChild(createDots());
    }

    // Các trang ở giữa (cửa sổ trượt)
    const start = Math.max(2, currentPage - window);
    const end = Math.min(totalPages - 1, currentPage + window);

    for (let i = start; i <= end; i++) {
      ul.appendChild(createPageLink(i, i.toString(), false, currentPage === i));
    }

    // Dấu ... (bên phải)
    if (currentPage < totalPages - window - 1) {
      ul.appendChild(createDots());
    }

    // Hiển thị trang cuối (nếu không phải là trang 1)
    if (totalPages > 1) {
      ul.appendChild(
        createPageLink(
          totalPages,
          totalPages.toString(),
          false,
          currentPage === totalPages
        )
      );
    }

    // --- Kết thúc Logic Sliding Window ---

    // Nút Trang sau
    ul.appendChild(
      createPageLink(currentPage + 1, "Sau", currentPage >= totalPages)
    );

    nav.appendChild(ul);
    paginationContainer.appendChild(nav);
  }

  /**
   * Thêm Event Listener cho các nút phân trang
   */
  paginationContainer.addEventListener("click", function (e) {
    e.preventDefault();

    if (
      e.target.tagName === "A" &&
      e.target.classList.contains("page-link") &&
      !e.target.parentElement.classList.contains("disabled")
    ) {
      const page = parseInt(e.target.dataset.page, 10);
      if (page) {
        filterRooms(currentRoomTypeFilter, page);
        availableRoomsContainer.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    }
  });

  // Lắng nghe sự kiện click nút "Kiểm tra"
  filterButton.addEventListener("click", () => filterRooms(null, 1)); // Luôn bắt đầu từ trang 1

  // Tự động chạy khi trang tải nếu có ngày sẵn
  if (
    filterButton.dataset.autorun === "true" &&
    checkInInput.value &&
    checkOutInput.value
  ) {
    console.log("Phát hiện ngày có sẵn, tự động lọc...");
    filterRooms(null, 1); // Luôn bắt đầu từ trang 1
  }

  // Xử lý nút "Chọn phòng"
  document.querySelectorAll(".check-availability-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const checkInValue = checkInInput.value;
      const checkOutValue = checkOutInput.value;
      const roomTypeToFilter = button.dataset.roomType;

      if (checkInValue && checkOutValue) {
        filterRooms(roomTypeToFilter, 1); // Luôn bắt đầu từ trang 1
        setTimeout(() => {
          const targetElement = document.getElementById(
            "available-rooms-details"
          );
          if (targetElement) {
            targetElement.scrollIntoView({
              behavior: "smooth",
              block: "start",
            });
          }
        }, 300);
      } else {
        checkInInput.focus();
        alert("Vui lòng chọn ngày nhận và trả phòng trước.");
      }
    });
  });
});

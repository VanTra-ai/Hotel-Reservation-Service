// public/js/ai_playground.js

document.addEventListener("DOMContentLoaded", function () {
  // --- Lấy các phần tử DOM ---
  const hotelSelect = document.getElementById("hotel-select");
  const slidersContainer = document.getElementById("hotel-info-sliders");
  const predictBtn = document.getElementById("predict-btn");
  const resultDisplay = document.getElementById("result-display");
  const resultTextDisplay = document.getElementById("result-text-display");
  const hotelInfoNames = {
    service_staff: "Nhân viên",
    amenities: "Tiện nghi",
    cleanliness: "Sạch sẽ",
    comfort: "Thoải mái",
    value_for_money: "Đáng giá tiền",
    location: "Địa điểm",
    free_wifi: "WiFi",
  };

  // --- SỰ KIỆN 1: Khi người dùng chọn một khách sạn ---
  hotelSelect.addEventListener("change", function () {
    const hotelId = this.value;
    if (!hotelId) return;

    slidersContainer.innerHTML = '<p class="text-muted">Đang tải...</p>';

    // [SỬA LẠI] Sử dụng biến global BASE_URL
    fetch(`${BASE_URL}/ai/getHotelInfo/${hotelId}`)
      .then((response) => response.json())
      .then((data) => {
        slidersContainer.innerHTML = "";
        if (data) {
          Object.keys(hotelInfoNames).forEach((key) => {
            const value = data[key] || 8.0;
            const label = hotelInfoNames[key];
            const sliderHtml = `
                            <div class="mb-3">
                                <label for="${key}" class="form-label">${label}: <span id="${key}-value" class="fw-bold">${value.toFixed(
              1
            )}</span></label>
                                <input type="range" class="form-range" id="${key}" name="${key}" min="1" max="10" step="0.1" value="${value}">
                            </div>
                        `;
            slidersContainer.innerHTML += sliderHtml;
          });

          slidersContainer
            .querySelectorAll("input[type=range]")
            .forEach((slider) => {
              slider.addEventListener("input", function () {
                document.getElementById(`${this.id}-value`).textContent =
                  parseFloat(this.value).toFixed(1);
              });
            });
        }
      });
  });

  // --- SỰ KIỆN 2: Khi người dùng nhấn nút "Dự đoán" ---
  predictBtn.addEventListener("click", function (e) {
    e.preventDefault();
    resultDisplay.textContent = "...";
    resultTextDisplay.textContent = "";
    predictBtn.disabled = true;

    const hotel_info = [];
    const sliderIds = Object.keys(hotelInfoNames);
    sliderIds.forEach((id) => {
      const slider = document.getElementById(id);
      hotel_info.push(slider ? parseFloat(slider.value) : 8.0);
    });

    const review_info = [
      parseInt(document.getElementById("room_type").value),
      parseInt(document.getElementById("stay_duration").value),
      parseInt(document.getElementById("group_type").value),
    ];

    const combinedComment = document
      .getElementById("combined_comment")
      .value.trim();

    // [SỬA LẠI] Sử dụng biến global BASE_URL
    fetch(`${BASE_URL}/ai/performPrediction`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        comment: combinedComment,
        hotel_info,
        review_info,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.predicted_score !== null) {
          resultDisplay.textContent = parseFloat(data.predicted_score).toFixed(
            1
          );
          resultTextDisplay.textContent = data.rating_text || "";
        } else {
          resultDisplay.textContent = "Lỗi!";
          resultTextDisplay.textContent = "Không thể dự đoán";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        resultDisplay.textContent = "Lỗi!";
        resultTextDisplay.textContent = "Lỗi kết nối API";
      })
      .finally(() => {
        predictBtn.disabled = false;
      });
  });

  // Tự động kích hoạt khi có hotel được chọn sẵn
  if (hotelSelect.value) {
    hotelSelect.dispatchEvent(new Event("change"));
  }
});

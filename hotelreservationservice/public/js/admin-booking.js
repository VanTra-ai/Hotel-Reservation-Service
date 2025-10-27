// public/js/admin-booking.js
function updateBookingStatus(id, select) {
  const status = select.value;

  fetch(BASE_URL + "/admin/booking/updateStatus", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}&status=${status}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        console.log("Cập nhật trạng thái thành công!");
      } else {
        alert("Lỗi: " + (data.error || "Không thể cập nhật."));
      }
    })
    .catch((err) => console.error("Lỗi mạng:", err));
}

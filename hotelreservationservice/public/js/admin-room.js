function updateRoomField(id, field, input) {
  let value = input.value;

  if (field === "capacity" || field === "price") {
    value = parseFloat(value);
    if (isNaN(value) || value <= 0) {
      alert("Giá trị không hợp lệ");
      return;
    }
  }

  fetch(
    BASE_URL + "/admin/room/updateFieldAjax", // Trỏ đến action xử lý AJAX
    {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}&field=${field}&value=${encodeURIComponent(value)}`,
    }
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        console.log("Cập nhật phòng thành công!");
      } else {
        alert("Lỗi: " + (data.error || "Không xác định"));
      }
    })
    .catch((err) => alert("Lỗi mạng: " + err));
}

function deleteRoom(id) {
  if (!confirm("Bạn có chắc muốn xóa phòng này?")) return;

  fetch(
    BASE_URL + "/admin/room/deleteAjax", // Trỏ đến action xử lý AJAX
    {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}`,
    }
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert("Xóa phòng thành công!");
        location.reload();
      } else {
        alert("Lỗi: " + (data.error || "Không xác định"));
      }
    })
    .catch((err) => alert("Lỗi mạng: " + err));
}

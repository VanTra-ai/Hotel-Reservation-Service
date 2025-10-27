// public/js/search_form.js
document.addEventListener("DOMContentLoaded", function () {
  const provinceInput = document.getElementById("provinceInput");
  const provinceList = document.getElementById("provinceList");
  let citiesCache = [];

  function renderList(items) {
    provinceList.innerHTML = "";
    items.forEach((city) => {
      const cityItem = document.createElement("a");
      cityItem.className = "dropdown-item";
      cityItem.href = "#";
      cityItem.textContent = city.name;
      cityItem.addEventListener("click", function (e) {
        e.preventDefault();
        provinceInput.value = city.name;
        provinceList.classList.remove("show");
      });
      provinceList.appendChild(cityItem);
    });
    provinceList.classList.toggle("show", items.length > 0);
  }

  function filterCities(query) {
    const q = (query || "").trim().toLowerCase();
    if (!q) {
      provinceList.classList.remove("show");
      return;
    }
    const filtered = citiesCache.filter((c) =>
      (c.name || "").toLowerCase().startsWith(q)
    );
    renderList(filtered);
  }

  function ensureCitiesLoaded(cb) {
    if (citiesCache.length > 0) {
      cb && cb();
      return;
    }
    fetch(BASE_URL + "/city/getCitiesJson")
      .then((response) => response.json())
      .then((cities) => {
        citiesCache = Array.isArray(cities) ? cities : [];
        cb && cb();
      })
      .catch((error) => console.error("Error fetching cities:", error));
  }

  provinceInput.addEventListener("focus", function () {
    ensureCitiesLoaded(() => {
      const currentValue = provinceInput.value.trim();
      if (currentValue) filterCities(currentValue);
    });
  });

  provinceInput.addEventListener("input", function () {
    ensureCitiesLoaded(() => filterCities(provinceInput.value));
  });

  document.addEventListener("click", function (event) {
    if (
      !provinceInput.contains(event.target) &&
      !provinceList.contains(event.target)
    ) {
      provinceList.classList.remove("show");
    }
  });

  flatpickr("#dateRangeInput", {
    mode: "range",
    minDate: "today",
    dateFormat: "d/m/Y",
    locale: "vn",
  });

  let adults = 1,
    children = 0,
    rooms = 1;

  window.updateGuests = function (type, change) {
    if (type === "phòng" && change > 0) {
      // Chỉ cho phép tăng nếu số phòng sau khi tăng <= số người lớn
      if (rooms + change > adults) {
        alert("Số phòng không được nhiều hơn số người lớn.");
        return; // Dừng lại, không làm gì cả
      }
    }

    // Cập nhật giá trị
    if (type === "người") adults = Math.max(1, adults + change);
    if (type === "trẻ") children = Math.max(0, children + change);
    if (type === "phòng") rooms = Math.max(1, rooms + change);

    // Nếu số người lớn ít hơn số phòng, tự động giảm số phòng theo
    if (adults < rooms) {
      rooms = adults;
    }

    updateSummary();
  };

  function updateSummary() {
    document.getElementById("adultsCount").innerText = adults;
    document.getElementById("childrenCount").innerText = children;
    document.getElementById("roomsCount").innerText = rooms;
    document.getElementById(
      "guestsSummary"
    ).innerText = `${adults} người lớn, ${children} trẻ em, ${rooms} phòng`;
  }

  updateSummary();
});

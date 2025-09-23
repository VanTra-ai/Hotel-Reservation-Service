document.addEventListener("DOMContentLoaded", function () {
  const provinceInput = document.getElementById('provinceInput');
  const provinceList = document.getElementById('provinceList');
  let citiesCache = [];

  function renderList(items) {
    provinceList.innerHTML = '';
    items.forEach(city => {
      const cityItem = document.createElement('a');
      cityItem.className = 'dropdown-item';
      cityItem.href = '#';
      cityItem.textContent = city.name;
      cityItem.addEventListener('click', function (e) {
        e.preventDefault();
        provinceInput.value = city.name;
        provinceList.classList.remove('show');
      });
      provinceList.appendChild(cityItem);
    });
    if (items.length > 0) {
      provinceList.classList.add('show');
    } else {
      provinceList.classList.remove('show');
    }
  }

  function filterCities(query) {
    const q = (query || '').trim().toLowerCase();
    if (!q) {
      provinceList.classList.remove('show');
      return;
    }
    const filtered = citiesCache.filter(c => {
      const cityName = (c.name || '').toLowerCase();
      return cityName.startsWith(q);
    });
    renderList(filtered);
  }

  function ensureCitiesLoaded(cb) {
    if (citiesCache.length > 0) {
      cb && cb();
      return;
    }
    fetch('/hotelreservationservice/city/getCitiesJson')
      .then(response => response.json())
      .then(cities => {
        citiesCache = Array.isArray(cities) ? cities : [];
        cb && cb();
      })
      .catch(error => console.error('Error fetching cities:', error));
  }

  provinceInput.addEventListener('focus', function () {
    ensureCitiesLoaded(() => {
      // Khi focus, chỉ hiển thị nếu đã có text
      const currentValue = provinceInput.value.trim();
      if (currentValue) {
        filterCities(currentValue);
      }
    });
  });

  provinceInput.addEventListener('input', function () {
    ensureCitiesLoaded(() => filterCities(provinceInput.value));
  });

  document.addEventListener('click', function (event) {
    if (!provinceInput.contains(event.target) && !provinceList.contains(event.target)) {
      provinceList.classList.remove('show');
    }
  });

  // Khởi tạo Flatpickr cho trường ngày
  flatpickr("#dateRangeInput", {
    mode: "range",
    minDate: "today",
    dateFormat: "d/m/Y",
  });

  // Khởi tạo các biến để lưu số lượng, khớp với giá trị mặc định trong HTML
  let adults = 1;
  let children = 0;
  let rooms = 1;

  // Hàm cập nhật số lượng và hiển thị
  window.updateGuests = function (type, change) {
    if (type === "adults") {
      adults = Math.max(1, adults + change);
      document.getElementById("adultsCount").innerText = adults;
    } else if (type === "children") {
      children = Math.max(0, children + change);
      document.getElementById("childrenCount").innerText = children;
    } else if (type === "rooms") {
      rooms = Math.max(1, rooms + change);
      document.getElementById("roomsCount").innerText = rooms;
    }
    updateSummary();
  };

  function updateSummary() {
    const guestsSummary = document.getElementById("guestsSummary");
    guestsSummary.innerText = `${adults} người lớn, ${children} trẻ em, ${rooms} phòng`;
  }

  // Cập nhật giá trị ban đầu khi trang tải
  updateSummary();
});

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - History</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span
        ><button class="hamburger-icon-btn" id="menu" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <div class="shop-hero-layout">
        <div class="shop-hero-heading">
          <span class="section-tag">OPERATIONS</span>
          <h1 class="main-heading">History</h1>
        </div>
        <p class="page-intro">
          Review all past deliveries. Use the filters below to narrow results.
        </p>
        <div class="shop-hero-animation" aria-hidden="true">
          <dotlottie-player
            src="https://lottie.host/9d302f22-8973-41af-851d-323a89cc0f07/oXN3ArVoZa.lottie"
            background="transparent"
            speed="1"
            loop
            autoplay
          ></dotlottie-player>
        </div>
      </div>
      <form class="ui-card-white history-filter-card" method="GET" action="{{ route('admin.history') }}">
        <h2>Filter orders</h2>
        <div class="history-form-grid">
          <div class="input-field-group full-field">
            <label for="historySearch">SEARCH NAME, PHONE OR ADDRESS</label
            ><input id="historySearch" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search customer name, phone, or address..." />
          </div>
          <div class="input-field-group">
            <label>ONLINE SHOP</label
            ><select name="shop_id">
              <option value="">Select</option>
              @foreach ($shops as $shop)
                <option value="{{ $shop->id }}" @selected(($filters['shop_id'] ?? '') == $shop->id)>{{ $shop->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
            <label>BIKER</label
            ><select name="biker_id">
              <option value="">Choose</option>
              @foreach ($bikers as $biker)
                <option value="{{ $biker->id }}" @selected(($filters['biker_id'] ?? '') == $biker->id)>{{ $biker->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
            <label>STATUS</label
            ><select name="status">
              <option value="">Select</option>
              @foreach (['pending' => 'Pending', 'onway' => 'On way', 'delivered' => 'Delivered', 'failed' => 'Failed'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
            <label>CUST. NAME</label
            ><input name="customer_name" type="text" value="{{ $filters['customer_name'] ?? '' }}" placeholder="cust. name..." />
          </div>
          <div class="input-field-group">
            <label>CUST. PHONE</label
            ><input name="customer_phone" type="tel" value="{{ $filters['customer_phone'] ?? '' }}" placeholder="cust. ph..." />
          </div>
          <div class="input-field-group">
            <label>MIN AMT.</label
            ><input name="min_amount" type="number" min="0" value="{{ $filters['min_amount'] ?? '' }}" placeholder="Min Amt" />
          </div>
          <div class="input-field-group">
            <label>MAX AMT.</label
            ><input name="max_amount" type="number" min="0" value="{{ $filters['max_amount'] ?? '' }}" placeholder="Max Amt" />
          </div>
          <div class="input-field-group full-field">
            <label>DATE</label>
            <div class="custom-date-picker">
              <input id="historyDate" name="date" type="date" value="{{ $filters['date'] ?? '' }}" /><button
                class="custom-date-trigger"
                type="button"
              >
                {{ isset($filters['date']) ? \Carbon\Carbon::parse($filters['date'])->format('d/m/y') : 'dd/mm/yy' }}
              </button>
              <div class="custom-calendar"></div>
            </div>
          </div>
        </div>
        <button class="ui-btn btn-navy-blue history-save" type="submit">
          Search
        </button>
      </form>
      <div class="badge-group history-badges">
        <span class="ui-badge badge-navy">{{ today()->format('d-m-Y') }}</span
        ><span class="ui-badge badge-lime">Total Ways · {{ $totalWays }}</span>
      </div>
      <section class="ui-card-white history-list-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">ORDERS</span>
            <h2>All orders</h2>
          </div>
          <span class="ui-badge badge-navy">{{ $ways->count() }} orders</span>
        </div>
        <div class="history-table-wrap">
          <table class="workspace-table history-table">
            <thead>
              <tr>
                <th>NO.</th>
                <th>ONLINE SHOP</th>
                <th>DATE</th>
                <th>PHOTO</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($ways as $index => $way)
                <tr>
                  <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                  <td>{{ $way->shop?->name ?? 'N/A' }}</td>
                  <td>{{ $way->date->format('d-m-Y') }}</td>
                  <td>{{ $way->item_image ? 'Yes' : 'No' }}</td>
                  <td><a class="table-action" href="{{ route('admin.history.detail', $way) }}">View</a> <a class="table-action" href="{{ route('admin.ways.edit', $way) }}">Edit</a></td>
                </tr>
              @empty
                <tr><td class="no-data-msg" colspan="5">No orders found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
    </main>
    <script>
      const dateInput = document.getElementById("historyDate"),
        datePicker = document.querySelector(".custom-date-picker"),
        dateTrigger = datePicker.querySelector(".custom-date-trigger"),
        calendar = datePicker.querySelector(".custom-calendar");
      let viewDate = new Date(),
        picked = "";
      const pad = (n) => String(n).padStart(2, "0");
      function drawCalendar() {
        const y = viewDate.getFullYear(),
          m = viewDate.getMonth(),
          first = new Date(y, m, 1).getDay(),
          days = new Date(y, m + 1, 0).getDate();
        calendar.innerHTML =
          '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' +
          viewDate.toLocaleString("en", { month: "long", year: "numeric" }) +
          '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" id="days"></div>';
        const grid = document.getElementById("days");
        for (let i = 0; i < first; i++) {
          const b = document.createElement("button");
          b.disabled = true;
          grid.append(b);
        }
        for (let d = 1; d <= days; d++) {
          const b = document.createElement("button");
          b.type = "button";
          b.textContent = d;
          const val = y + "-" + pad(m + 1) + "-" + pad(d);
          if (val === picked) b.className = "selected";
          b.onclick = () => {
            picked = val;
            dateInput.value = val;
            dateTrigger.textContent =
              pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2);
            datePicker.classList.remove("open");
            drawCalendar();
          };
          grid.append(b);
        }
      }
      dateTrigger.onclick = () => {
        datePicker.classList.toggle("open");
        drawCalendar();
      };
      calendar.onclick = (e) => {
        if (e.target.dataset.step)
          viewDate.setMonth(
            viewDate.getMonth() + Number(e.target.dataset.step),
          );
      };
      document.addEventListener("click", (e) => {
        if (!datePicker.contains(e.target)) datePicker.classList.remove("open");
      });
      drawCalendar();
    </script>
  </body>
</html>
<script>
  document.querySelectorAll(".history-filter-card select").forEach((select) => {
    const wrapper = document.createElement("div");
    wrapper.className = "custom-select";
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    const toggle = document.createElement("button");
    toggle.type = "button";
    toggle.className = "custom-select-toggle";
    toggle.setAttribute("aria-haspopup", "listbox");
    toggle.setAttribute("aria-expanded", "false");
    toggle.textContent = select.options[select.selectedIndex]?.text || "Select";
    wrapper.appendChild(toggle);
    const options = document.createElement("ul");
    options.className = "custom-select-options";
    options.setAttribute("role", "listbox");
    Array.from(select.options).forEach((option, index) => {
      const item = document.createElement("li");
      item.className = "custom-select-option";
      item.textContent = option.text;
      item.setAttribute("role", "option");
      if (option.selected) item.classList.add("selected");
      item.onclick = () => {
        select.selectedIndex = index;
        toggle.childNodes[0].textContent = option.text;
        options
          .querySelectorAll(".custom-select-option")
          .forEach((x) => x.classList.remove("selected"));
        item.classList.add("selected");
        wrapper.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
        select.dispatchEvent(new Event("change", { bubbles: true }));
      };
      options.appendChild(item);
    });
    wrapper.appendChild(options);
    toggle.onclick = () => {
      document.querySelectorAll(".custom-select.open").forEach((x) => {
        if (x !== wrapper) x.classList.remove("open");
      });
      const open = wrapper.classList.toggle("open");
      toggle.setAttribute("aria-expanded", String(open));
    };
  });
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".custom-select"))
      document
        .querySelectorAll(".custom-select.open")
        .forEach((x) => x.classList.remove("open"));
  });
</script>
<script>
</script>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Shop History</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="shop" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Shop · {{ $shop->name }}</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">{{ strtoupper($shop->name) }}</span>
      <h1 class="main-heading">Order history</h1>
      <p class="page-intro">All orders made by your shop.</p>
      <form class="ui-card-white history-filter-card" method="GET" action="{{ route('shop.history') }}">
        <h2>Find an order</h2>
        <div class="history-form-grid">
          <div class="input-field-group full-field">
            <label>SEARCH</label
            ><input
              name="search"
              value="{{ $filters['search'] ?? '' }}"
              type="search"
              placeholder="Search order, customer, address..."
            />
          </div>
          <div class="input-field-group">
            <label>STATUS</label
            ><select name="status">
              <option value="">All statuses</option>
              @foreach (['pending' => 'Pending', 'onway' => 'On way', 'delivered' => 'Delivered', 'failed' => 'Failed'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
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
        ><span class="ui-badge badge-lime">{{ $orders->count() }} orders</span>
      </div>
      <section class="ui-card-white history-list-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">ORDERS</span>
            <h2>All orders</h2>
          </div>
        </div>
        <div class="history-table-wrap">
          <table class="workspace-table history-table">
            <thead>
              <tr>
                <th>NO.</th>
                <th>CUSTOMER</th>
                <th>DATE</th>
                <th>STATUS</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($orders as $index => $order)
                <tr>
                  <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                  <td>{{ $order->recipient_name }}</td>
                  <td>{{ $order->date->format('d-m-Y') }}</td>
                  <td>{{ ucfirst($order->status) }}</td>
                  <td><a class="table-action" href="{{ route('shop.history.detail', $order) }}">View</a></td>
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
      document
        .querySelectorAll(".history-filter-card select")
        .forEach((select) => {
          const wrapper = document.createElement("div");
          wrapper.className = "custom-select";
          select.parentNode.insertBefore(wrapper, select);
          wrapper.appendChild(select);
          const toggle = document.createElement("button");
          toggle.type = "button";
          toggle.className = "custom-select-toggle";
          toggle.textContent = select.options[0].text;
          wrapper.appendChild(toggle);
          const options = document.createElement("ul");
          options.className = "custom-select-options";
          [...select.options].forEach((option, i) => {
            const item = document.createElement("li");
            item.className = "custom-select-option";
            item.textContent = option.text;
            item.onclick = () => {
              select.selectedIndex = i;
              toggle.textContent = option.text;
              wrapper.classList.remove("open");
            };
            options.appendChild(item);
          });
          wrapper.appendChild(options);
          toggle.onclick = () => wrapper.classList.toggle("open");
        });
      const dateInput = document.getElementById("historyDate"),
        datePicker = document.querySelector(".custom-date-picker"),
        trigger = datePicker.querySelector(".custom-date-trigger"),
        calendar = datePicker.querySelector(".custom-calendar");
      let viewDate = new Date();
      const pad = (n) => String(n).padStart(2, "0");
      function draw() {
        const y = viewDate.getFullYear(),
          m = viewDate.getMonth(),
          first = new Date(y, m, 1).getDay(),
          days = new Date(y, m + 1, 0).getDate();
        calendar.innerHTML =
          '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' +
          viewDate.toLocaleString("en", { month: "long", year: "numeric" }) +
          '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" id="days"></div>';
        const grid = document.getElementById("days");
        for (let i = 0; i < first; i++)
          grid.append(document.createElement("button"));
        for (let d = 1; d <= days; d++) {
          const b = document.createElement("button");
          b.type = "button";
          b.textContent = d;
          b.onclick = () => {
            dateInput.value = y + "-" + pad(m + 1) + "-" + pad(d);
            trigger.textContent =
              pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2);
            datePicker.classList.remove("open");
          };
          grid.append(b);
        }
      }
      trigger.onclick = () => {
        datePicker.classList.toggle("open");
        draw();
      };
      calendar.onclick = (e) => {
        if (e.target.dataset.step) {
          viewDate.setMonth(
            viewDate.getMonth() + Number(e.target.dataset.step),
          );
          draw();
        }
      };
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".custom-select"))
          document
            .querySelectorAll(".custom-select.open")
            .forEach((x) => x.classList.remove("open"));
        if (!datePicker.contains(e.target)) datePicker.classList.remove("open");
      });
      draw();
    </script>
  </body>
</html>

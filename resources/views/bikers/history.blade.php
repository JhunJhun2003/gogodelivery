<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - My Way History</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="biker" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Biker · {{ $biker->name }}</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">MY RECORDS</span>
      <h1 class="main-heading">Way history</h1>
      <p class="page-intro">All deliveries assigned to you.</p>
      <section class="ui-card-white history-filter-card">
        <h2>Find a way</h2>
        <form id="historyFilterForm" method="GET" action="{{ route('bikers.history') }}">
          <div class="history-form-grid">
          <div class="input-field-group full-field">
            <label>SEARCH</label
            ><input
              id="historySearch"
              type="search"
              name="search"
              value="{{ $filters['search'] ?? '' }}"
              placeholder="Search order, shop, or customer..."
            />
          </div>
          <div class="input-field-group">
            <label>STATUS</label
            ><select name="status">
              <option value="">All statuses</option>
              <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
              <option value="onway" @selected(($filters['status'] ?? '') === 'onway')>On way</option>
              <option value="delivered" @selected(($filters['status'] ?? '') === 'delivered')>Delivered</option>
              <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>Failed</option>
            </select>
          </div>
          <div class="input-field-group">
            <label for="historyDate">DATE</label><div class="custom-date-picker"><input id="historyDate" name="date" value="{{ $filters['date'] ?? '' }}" type="date" /><button class="custom-date-trigger" type="button">dd/mm/yy</button><div class="custom-calendar"></div></div>
          </div>
          </div>
          <button class="ui-btn btn-navy-blue history-save" id="saveFilter" type="submit">Search</button>
        </form>
      </section>
      <div class="badge-group history-badges">
        <span class="ui-badge badge-navy">ALL WAYS</span
        ><span class="ui-badge badge-lime">{{ $ways->count() }} ways</span>
        
      </div>
      <section class="ui-card-white history-list-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">{{ strtoupper($biker->name) }}</span>
            <h2>My delivery history</h2>
          </div>
          <span class="ui-badge badge-navy" id="resultCount">{{ $ways->count() }} ways</span>

        </div>
        <div class="history-table-wrap">
          <table class="workspace-table history-table">
            <thead>
              <tr>
                <th>NO.</th>
                <th>ONLINE SHOP</th>
                <th>DATE</th>
                <th>STATUS</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody id="historyRows">
              @forelse ($ways as $way)
                <tr data-search="{{ strtolower($way->recipient_name . ' ' . $way->phone_number . ' ' . $way->address . ' ' . ($way->shop?->name ?? '')) }}">
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $way->shop?->name ?? 'Shop' }}</td>
                  <td>{{ $way->date->format('d-m-Y') }}</td>
                  <td><span class="status-pill status-{{ $way->status }}">{{ ucfirst($way->status) }}</span></td>
                  <td><a class="table-action" href="{{ route('bikers.history.detail', $way) }}">View</a></td>
                </tr>
              @empty
                <tr><td colspan="5">No ways found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
    </main>
    <script>
      document.getElementById("historySearch").oninput = (e) => {
        const q = e.target.value.toLowerCase();
        document
          .querySelectorAll("#historyRows tr")
          .forEach(
            (row) => (row.hidden = !(row.dataset.search || row.textContent).toLowerCase().includes(q)),
          );
        document.getElementById("resultCount").textContent =
          [...document.querySelectorAll("#historyRows tr")].filter(
            (row) => !row.hidden,
          ).length + " ways";
      };
    </script>
    <script>
      const historyFilterForm = document.getElementById("historyFilterForm");
      document.querySelectorAll(".history-filter-card select").forEach((select) => { const wrapper = document.createElement("div"); wrapper.className = "custom-select"; select.parentNode.insertBefore(wrapper, select); wrapper.appendChild(select); const toggle = document.createElement("button"); toggle.type = "button"; toggle.className = "custom-select-toggle"; toggle.setAttribute("aria-haspopup", "listbox"); toggle.textContent = select.options[select.selectedIndex].text; wrapper.appendChild(toggle); const options = document.createElement("ul"); options.className = "custom-select-options"; Array.from(select.options).forEach((option, index) => { const item = document.createElement("li"); item.className = "custom-select-option"; item.textContent = option.text; item.onclick = () => { select.selectedIndex = index; toggle.textContent = option.text; wrapper.classList.remove("open"); historyFilterForm.submit(); }; options.appendChild(item); }); wrapper.appendChild(options); toggle.onclick = () => wrapper.classList.toggle("open"); });
      const dateInput = document.getElementById("historyDate"), datePicker = document.querySelector(".custom-date-picker"), dateTrigger = datePicker.querySelector(".custom-date-trigger"), calendar = datePicker.querySelector(".custom-calendar"); let viewDate = dateInput.value ? new Date(dateInput.value + "T00:00:00") : new Date(), picked = ""; const pad = n => String(n).padStart(2, "0"); function drawCalendar() { const y = viewDate.getFullYear(), m = viewDate.getMonth(), first = new Date(y, m, 1).getDay(), days = new Date(y, m + 1, 0).getDate(); calendar.innerHTML = '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' + viewDate.toLocaleString("en", {month:"long", year:"numeric"}) + '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" id="days"></div>'; const grid = document.getElementById("days"); for (let i = 0; i < first; i++) grid.append(document.createElement("button")); for (let d = 1; d <= days; d++) { const b = document.createElement("button"); b.type = "button"; b.textContent = d; const val = y + "-" + pad(m + 1) + "-" + pad(d); b.onclick = () => { picked = val; dateInput.value = val; dateTrigger.textContent = pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2); datePicker.classList.remove("open"); historyFilterForm.submit(); }; grid.append(b); } } dateTrigger.onclick = () => { datePicker.classList.toggle("open"); drawCalendar(); }; calendar.onclick = e => { if (e.target.dataset.step) { viewDate.setMonth(viewDate.getMonth() + Number(e.target.dataset.step)); drawCalendar(); } }; document.addEventListener("click", e => { if (!e.target.closest(".custom-select")) document.querySelectorAll(".custom-select.open").forEach(x => x.classList.remove("open")); if (!datePicker.contains(e.target)) datePicker.classList.remove("open"); }); drawCalendar();
    </script>
  </body>
</html>

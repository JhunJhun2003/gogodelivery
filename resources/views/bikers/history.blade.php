<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - My Way History</title>
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787684056" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="biker" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Biker · Ko Ko</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">MY RECORDS</span>
      <h1 class="main-heading">Way history</h1>
      <p class="page-intro">Your completed and failed deliveries.</p>
      <section class="ui-card-white history-filter-card">
        <h2>Find a way</h2>
        <div class="history-form-grid">
          <div class="input-field-group full-field">
            <label>SEARCH</label
            ><input
              id="historySearch"
              type="search"
              placeholder="Search order, shop, or customer..."
            />
          </div>
          <div class="input-field-group">
            <label>STATUS</label
            ><select>
              <option>All statuses</option>
              <option>Delivered</option>
              <option>Failed</option>
            </select>
          </div>
          <div class="input-field-group">
            <label>DATE</label><div class="custom-date-picker"><input id="historyDate" type="date" /><button class="custom-date-trigger" type="button">dd/mm/yy</button><div class="custom-calendar"></div></div>
          </div>
        </div>
        <button class="ui-btn btn-navy-blue history-save" id="saveFilter" type="button">
          Search
        </button>
      </section>
      <div class="badge-group history-badges">
        <span class="ui-badge badge-navy">24-08-2026</span
        ><span class="ui-badge badge-lime">18 total ways</span>
      </div>
      <section class="ui-card-white history-list-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">KO KO</span>
            <h2>My delivery history</h2>
          </div>
          <span class="ui-badge badge-navy" id="resultCount">3 ways</span>
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
              <tr>
                <td>01</td>
                <td>ABC Store</td>
                <td>24-08-2026</td>
                <td>Delivered</td>
                <td>
                  <a class="table-action" href="./history-detail.html">View</a>
                </td>
              </tr>
              <tr>
                <td>02</td>
                <td>M Store</td>
                <td>23-08-2026</td>
                <td>Delivered</td>
                <td>
                  <a class="table-action" href="./history-detail.html">View</a>
                </td>
              </tr>
              <tr>
                <td>03</td>
                <td>City Mart</td>
                <td>21-08-2026</td>
                <td>Failed</td>
                <td>
                  <a class="table-action" href="./history-detail.html">View</a>
                </td>
              </tr>
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
            (row) => (row.hidden = !row.textContent.toLowerCase().includes(q)),
          );
        document.getElementById("resultCount").textContent =
          [...document.querySelectorAll("#historyRows tr")].filter(
            (row) => !row.hidden,
          ).length + " ways";
      };
      const nav = document.createElement("div");
      nav.className = "slide-sidebar";
      nav.innerHTML =
        '<a class="sidebar-row" href="/bikers/ways"><strong>Ways</strong><span>Assigned deliveries</span></a><a class="sidebar-row active-row" href="/bikers/history"><strong>History</strong><span>My records</span></a>';
      const overlay = document.createElement("div");
      overlay.className = "sidebar-overlay";
      document.body.append(nav, overlay);
      document.querySelector(".hamburger-icon-btn").onclick = () => {
        nav.classList.add("open");
        overlay.classList.add("visible");
      };
      overlay.onclick = () => {
        nav.classList.remove("open");
        overlay.classList.remove("visible");
      };
    </script>
    <script>
      document.querySelectorAll(".history-filter-card select").forEach((select) => { const wrapper = document.createElement("div"); wrapper.className = "custom-select"; select.parentNode.insertBefore(wrapper, select); wrapper.appendChild(select); const toggle = document.createElement("button"); toggle.type = "button"; toggle.className = "custom-select-toggle"; toggle.setAttribute("aria-haspopup", "listbox"); toggle.textContent = select.options[select.selectedIndex].text; wrapper.appendChild(toggle); const options = document.createElement("ul"); options.className = "custom-select-options"; Array.from(select.options).forEach((option, index) => { const item = document.createElement("li"); item.className = "custom-select-option"; item.textContent = option.text; item.onclick = () => { select.selectedIndex = index; toggle.textContent = option.text; wrapper.classList.remove("open"); }; options.appendChild(item); }); wrapper.appendChild(options); toggle.onclick = () => wrapper.classList.toggle("open"); });
      const dateInput = document.getElementById("historyDate"), datePicker = document.querySelector(".custom-date-picker"), dateTrigger = datePicker.querySelector(".custom-date-trigger"), calendar = datePicker.querySelector(".custom-calendar"); let viewDate = new Date(), picked = ""; const pad = n => String(n).padStart(2, "0"); function drawCalendar() { const y = viewDate.getFullYear(), m = viewDate.getMonth(), first = new Date(y, m, 1).getDay(), days = new Date(y, m + 1, 0).getDate(); calendar.innerHTML = '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' + viewDate.toLocaleString("en", {month:"long", year:"numeric"}) + '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" id="days"></div>'; const grid = document.getElementById("days"); for (let i = 0; i < first; i++) grid.append(document.createElement("button")); for (let d = 1; d <= days; d++) { const b = document.createElement("button"); b.type = "button"; b.textContent = d; const val = y + "-" + pad(m + 1) + "-" + pad(d); b.onclick = () => { picked = val; dateInput.value = val; dateTrigger.textContent = pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2); datePicker.classList.remove("open"); drawCalendar(); }; grid.append(b); } } dateTrigger.onclick = () => { datePicker.classList.toggle("open"); drawCalendar(); }; calendar.onclick = e => { if (e.target.dataset.step) { viewDate.setMonth(viewDate.getMonth() + Number(e.target.dataset.step)); drawCalendar(); } }; document.addEventListener("click", e => { if (!e.target.closest(".custom-select")) document.querySelectorAll(".custom-select.open").forEach(x => x.classList.remove("open")); if (!datePicker.contains(e.target)) datePicker.classList.remove("open"); }); drawCalendar();
    </script>
  </body>
</html>

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
  <script src="/js/sidebar.js?v=1787684056" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span
        ><button class="hamburger-icon-btn" id="menu" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">OPERATIONS</span>
      <h1 class="main-heading">History</h1>
      <section class="ui-card-white history-filter-card">
        <h2>Filter orders</h2>
        <div class="history-form-grid">
          <div class="input-field-group full-field">
            <label>SEARCH</label
            ><input id="historySearch" type="search" placeholder="Search..." />
          </div>
          <div class="input-field-group">
            <label>ONLINE SHOP</label
            ><select>
              <option>Select</option>
              <option>ABC Store</option>
              <option>M Store</option>
              <option>City Mart</option>
            </select>
          </div>
          <div class="input-field-group">
            <label>BIKER</label
            ><select>
              <option>Choose</option>
              <option>Ko Ko</option>
              <option>KoAye</option>
              <option>Kolay</option>
            </select>
          </div>
          <div class="input-field-group">
            <label>STATUS</label
            ><select>
              <option>Select</option>
              <option>Pending</option>
              <option>On way</option>
              <option>Delivered</option>
              <option>Failed</option>
            </select>
          </div>
          <div class="input-field-group">
            <label>CUST. NAME</label
            ><input type="text" placeholder="cust. name..." />
          </div>
          <div class="input-field-group">
            <label>CUST. PHONE</label
            ><input type="tel" placeholder="cust. ph..." />
          </div>
          <div class="input-field-group">
            <label>MIN AMT.</label
            ><input type="number" min="0" placeholder="Min Amt" />
          </div>
          <div class="input-field-group">
            <label>MAX AMT.</label
            ><input type="number" min="0" placeholder="Max Amt" />
          </div>
          <div class="input-field-group full-field">
            <label>DATE</label>
            <div class="custom-date-picker">
              <input id="historyDate" type="date" /><button
                class="custom-date-trigger"
                type="button"
              >
                dd/mm/yy
              </button>
              <div class="custom-calendar"></div>
            </div>
          </div>
        </div>
        <button
          class="ui-btn btn-navy-blue history-save"
          id="saveFilter"
          type="button"
        >
          Search
        </button>
      </section>
      <div class="badge-group history-badges">
        <span class="ui-badge badge-navy">24-08-2026</span
        ><span class="ui-badge badge-lime">Total Way · 21</span>
      </div>
      <section class="ui-card-white history-list-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">ORDERS</span>
            <h2>All orders</h2>
          </div>
          <span class="ui-badge badge-navy" id="resultCount">3 orders</span>
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
            <tbody id="historyRows">
              <tr>
                <td>01</td>
                <td>ABC Store</td>
                <td>24-08-2026</td>
                <td>Yes</td>
                <td>
                  <a class="table-action" href="./history-detail.html">View</a>
                </td>
              </tr>
              <tr>
                <td>02</td>
                <td>M Store</td>
                <td>23-08-2026</td>
                <td>Yes</td>
                <td>
                  <a class="table-action" href="./history-detail.html">View</a>
                </td>
              </tr>
              <tr>
                <td>03</td>
                <td>City Mart</td>
                <td>21-08-2026</td>
                <td>No</td>
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
      document.getElementById("historySearch").oninput = (e) => {
        const q = e.target.value.toLowerCase();
        document
          .querySelectorAll("#historyRows tr")
          .forEach(
            (row) => (row.hidden = !row.textContent.toLowerCase().includes(q)),
          );
      };
      document.getElementById("saveFilter").onclick = () => {
        document.getElementById("resultCount").textContent =
          [...document.querySelectorAll("#historyRows tr")].filter(
            (row) => !row.hidden,
          ).length + " orders";
      };
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

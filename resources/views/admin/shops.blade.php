<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Online Shops</title>
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787684056" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span
        ><button
          class="hamburger-icon-btn"
          id="openMenuBtn"
          type="button"
          aria-label="Open navigation menu"
        >
          ☰
        </button>
      </div>
    </header>
    <main class="workspace-body shops-page">
      <span class="section-tag">OPERATIONS</span>
      <h1 class="main-heading">Online Shop List</h1>
      <p class="page-intro">
        Select a shop to create orders. Click + to edit today's deliveries.
      </p>
      <section class="ui-card-white shop-list-card">
        <div class="section-card-heading">
          <h2>Shop list</h2>
          <button
            class="ui-btn btn-navy-blue compact-btn"
            id="addShopBtn"
            type="button"
          >
            Add
          </button>
        </div>
        <label class="sr-only" for="shopSearch">Search shops</label
        ><input
          id="shopSearch"
          class="shop-search"
          type="search"
          placeholder="Search shops..."
        />
        <div class="shop-list" id="shopList">
          <button class="shop-row selected" type="button" data-shop="ABC Store">
            <span class="shop-avatar avatar-lime">A</span
            ><span class="shop-copy"
              ><strong>ABC Store</strong><small>3 way(s) today</small></span
            ><span class="shop-row-actions"
              ><span>⚙</span><b>+</b></span
            ></button
          ><button class="shop-row" type="button" data-shop="City Mart">
            <span class="shop-avatar">C</span
            ><span class="shop-copy"
              ><strong>City Mart</strong><small>2 way(s) today</small></span
            ><span class="shop-row-actions"
              ><span>⚙</span><b>+</b></span
            ></button
          ><button class="shop-row" type="button" data-shop="Fresh Kitchen">
            <span class="shop-avatar">F</span
            ><span class="shop-copy"
              ><strong>Fresh Kitchen</strong><small>09-10000002</small></span
            ><span class="shop-row-actions"
              ><span>⚙</span><b>+</b></span
            ></button
          ><button class="shop-row" type="button" data-shop="M Store">
            <span class="shop-avatar">M</span
            ><span class="shop-copy"
              ><strong>M Store</strong><small>11 way(s) today</small></span
            ><span class="shop-row-actions"><span>⚙</span><b>+</b></span>
          </button>
        </div>
        <section class="ui-card-white nested-form" id="shopForm" hidden>
          <h2>Create shop</h2>
          <div class="input-field-group">
            <label>NAME</label><input placeholder="Shop name" />
          </div>
          <div class="input-field-group">
            <label>EMAIL</label><input type="email" placeholder="Email" />
          </div>
          <button class="ui-btn btn-navy-blue" type="button">Save shop</button>
        </section>
      </section>
      <section class="ui-card-white form-card shop-order-card">
        <h2 id="orderHeading">Online Shop · ABC Store</h2>
        <p class="selected-shop-hint">
          Create a delivery for the selected shop.
        </p>
        <form>
          <div class="input-field-group">
            <label for="itemImage">ITEM IMAGE</label
            ><input
              id="itemImage"
              name="item_image"
              class="photo-input"
              type="file"
              accept="image/*"
            />
            <div class="photo-preview" id="photoPreview">
              <img id="photoPreviewImage" alt="Selected item preview" /><span
                id="photoPreviewName"
              ></span>
            </div>
          </div>
          <div class="input-field-group">
            <label>AMOUNT</label
            ><input name="amount" type="number" min="0" value="0" />
          </div>
          <div class="input-field-group">
            <label>DELIVERY FEES</label
            ><input name="delivery_fees" type="number" min="0" value="0" />
          </div>
          <div class="input-field-group">
            <label>RECIPIENT NAME</label
            ><input
              name="recipient_name"
              type="text"
              placeholder="Recipient name"
            />
          </div>
          <div class="input-field-group">
            <label>ADDRESS</label
            ><input name="address" type="text" placeholder="Delivery address" />
          </div>
          <div class="input-field-group">
            <label>PHONE NUMBER</label
            ><input
              name="phone_number"
              type="tel"
              inputmode="numeric"
              placeholder="09..."
            />
          </div>
          <div class="input-field-group">
            <label>DATE</label><input name="date" type="date" />
          </div>
          <div class="input-field-group">
            <label>REMARK</label
            ><textarea
              name="remark"
              rows="3"
              placeholder="Add a note"
            ></textarea>
          </div>
          <button class="ui-btn btn-navy-blue" type="submit">
            Add delivery
          </button>
        </form>
      </section>
    </main>
    <script>
      const rows = [...document.querySelectorAll(".shop-row")];
      rows.forEach(
        (r) =>
          (r.onclick = () => {
            rows.forEach((x) => x.classList.remove("selected"));
            r.classList.add("selected");
            orderHeading.textContent = "Online Shop · " + r.dataset.shop;
          }),
      );
      shopSearch.oninput = () => {
        const q = shopSearch.value.toLowerCase();
        rows.forEach(
          (r) => (r.hidden = !r.dataset.shop.toLowerCase().includes(q)),
        );
      };
      addShopBtn.onclick = () => {
        shopForm.hidden = !shopForm.hidden;
      };
      itemImage.onchange = (e) => {
        const f = e.target.files[0];
        if (!f) return;
        photoPreviewImage.src = URL.createObjectURL(f);
        photoPreviewName.textContent = f.name;
        photoPreview.classList.add("visible");
      };
      const nativeDate = document.querySelector('input[name="date"]');
      const dateWrap = document.createElement("div");
      dateWrap.className = "custom-date-picker";
      nativeDate.parentNode.insertBefore(dateWrap, nativeDate);
      dateWrap.appendChild(nativeDate);
      const trigger = document.createElement("button");
      trigger.type = "button";
      trigger.className = "custom-date-trigger";
      trigger.textContent = "dd/mm/yy";
      dateWrap.appendChild(trigger);
      const calendar = document.createElement("div");
      calendar.className = "custom-calendar";
      dateWrap.appendChild(calendar);
      let view = new Date();
      let chosen = "";
      const pad = (n) => String(n).padStart(2, "0");
      function draw() {
        const y = view.getFullYear(),
          m = view.getMonth(),
          first = new Date(y, m, 1).getDay(),
          days = new Date(y, m + 1, 0).getDate();
        calendar.innerHTML =
          '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' +
          view.toLocaleString("en", { month: "long", year: "numeric" }) +
          '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" id="calendarDays"></div>';
        const grid = calendar.querySelector("#calendarDays");
        for (let i = 0; i < first; i++) {
          const b = document.createElement("button");
          b.className = "muted";
          b.disabled = true;
          grid.appendChild(b);
        }
        for (let d = 1; d <= days; d++) {
          const b = document.createElement("button");
          b.type = "button";
          b.textContent = d;
          const value = y + "-" + pad(m + 1) + "-" + pad(d);
          if (value === chosen) b.className = "selected";
          b.onclick = () => {
            chosen = value;
            nativeDate.value = value;
            trigger.textContent =
              pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2);
            dateWrap.classList.remove("open");
            draw();
          };
          grid.appendChild(b);
        }
      }
      trigger.onclick = () => {
        dateWrap.classList.toggle("open");
        draw();
      };
      calendar.onclick = (e) => {
        if (e.target.dataset.step) {
          view.setMonth(view.getMonth() + Number(e.target.dataset.step));
          draw();
        }
      };
      document.addEventListener("click", (e) => {
        if (!dateWrap.contains(e.target)) dateWrap.classList.remove("open");
      });
      draw();
    </script>
  </body>
</html>
<script>
  const orderData = {
    "ABC Store": [
      ["#91axcn", "Cho Aung Tin · 26,000 · Pending"],
      ["#ovzbgx", "Nyi Nyi Thant · 25,000 · Pending"],
    ],
    "City Mart": [["#95buxg", "Mya Mya · 103,500 · Delivered"]],
    "Fresh Kitchen": [],
    "M Store": [["#kdsI6b", "Kyaw gyi · 15,000 · On way"]],
  };
  const orderCard = document.createElement("section");
  orderCard.className = "ui-card-white shop-orders-card";
  orderCard.innerHTML =
    '<div class="shop-orders-heading"><h2 id="shopOrdersTitle">Orders · ABC Store</h2><span class="ui-badge badge-navy" id="shopOrderCount">2 orders</span></div><div id="shopOrders"></div>';
  document.querySelector(".shop-order-card").after(orderCard);
  function renderShopOrders(name) {
    const orders = orderData[name] || [];
    document.getElementById("shopOrdersTitle").textContent = "Orders · " + name;
    document.getElementById("shopOrderCount").textContent =
      orders.length + " orders";
    document.getElementById("shopOrders").innerHTML = orders.length
      ? orders
          .map(
            (order) =>
              '<div class="shop-order-row"><strong>' +
              order[0] +
              "</strong><span>" +
              order[1] +
              "</span></div>",
          )
          .join("")
      : '<div class="shop-orders-empty">No orders for this shop today.</div>';
  }
  document
    .querySelectorAll(".shop-row")
    .forEach((row) =>
      row.addEventListener("click", () => renderShopOrders(row.dataset.shop)),
    );
  renderShopOrders("ABC Store");
</script>

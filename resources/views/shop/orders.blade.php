<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Shop Orders</title>
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787684056" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="shop" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Shop · ABC Store</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">ABC STORE</span>
      <h1 class="main-heading">Active orders</h1>
      <p class="page-intro">
        Orders you have made that are still being processed.
      </p>
      <section class="ui-card-white shop-orders-card">
        <div class="shop-orders-heading">
          <h2>Current orders</h2>
          <span class="ui-badge badge-lime" id="orderCount">2 active</span>
        </div>
        <input
          class="shop-search"
          id="orderSearch"
          type="search"
          placeholder="Search order, customer, address..."
        />
        <div id="orderList">
          <article class="ui-card-white order-card shop-order-card">
            <label><strong>Order #91axcn</strong></label>
            <div class="order-content">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>Cho Aung Tin</strong>
                <p>0977483047 / 26,000 / 3,000 deli</p>
                <small>Status: PENDING · Biker: Not assigned</small>
              </div>
            </div>
            <label class="order-address"
              >ADDRESS<input value="ကျားမြို့တွင်" readonly
            /></label>
            <div class="delivery-actions">
              <a class="info-btn" href="./history-detail.html?order=91axcn"
                >View details</a
              >
            </div>
          </article>
          <article class="ui-card-white order-card shop-order-card">
            <label><strong>Order #ovzbgx</strong></label>
            <div class="order-content">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>Nyi Nyi Thant</strong>
                <p>09257191532 / 25,000 / 3,000 deli</p>
                <small>Status: ON WAY · Biker: Ko Ko</small>
              </div>
            </div>
            <label class="order-address"
              >ADDRESS<input value="အလုံ" readonly
            /></label>
            <div class="delivery-actions">
              <a class="info-btn" href="./history-detail.html?order=ovzbgx"
                >View details</a
              >
            </div>
          </article>
        </div>
      </section>
    </main>
    <script>
      const search = document.getElementById("orderSearch"),
        rows = [...document.querySelectorAll(".order-card")];
      search.oninput = () => {
        const q = search.value.toLowerCase();
        rows.forEach(
          (row) => (row.hidden = !row.textContent.toLowerCase().includes(q)),
        );
        document.getElementById("orderCount").textContent =
          rows.filter((row) => !row.hidden).length + " active";
      };
      const nav = document.createElement("div");
      nav.className = "slide-sidebar";
      nav.innerHTML =
        '<a class="sidebar-row active-row" href="/shop/orders"><strong>Orders</strong><span>Active orders</span></a><a class="sidebar-row" href="/shop/history"><strong>History</strong><span>All orders</span></a>';
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
  </body>
</html>

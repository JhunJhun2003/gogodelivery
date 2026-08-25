<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Order Detail</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787684056" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="shop" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Shop · ABC Store</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <a class="back-link" href="/shop/history">← Back to history</a
      ><span class="section-tag">ORDER · 02</span>
      <h1 class="main-heading">Order detail</h1>
      <section class="ui-card-white history-detail-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">23-08-2026</span>
            <h2>Nyi Nyi Thant</h2>
          </div>
          <span class="status-pill status-delivered">On way</span>
        </div>
        <div class="detail-layout">
          <div class="detail-photo">
            <img src="/assets/logo.jpg?v=1787684056" alt="Order photo" />
          </div>
          <div class="detail-grid">
            <div><span>Customer name</span><strong>Nyi Nyi Thant</strong></div>
            <div>
              <span>Customer phone</span><strong>09 257 159 132</strong>
            </div>
            <div><span>Order status</span><strong>On way</strong></div>
            <div><span>Biker</span><strong>Ko Ko</strong></div>
            <div><span>Order amount</span><strong>25,000 MMK</strong></div>
            <div><span>Delivery fee</span><strong>3,000 MMK</strong></div>
            <div class="detail-wide">
              <span>Customer address</span><strong>အလုံ</strong>
            </div>
          </div>
        </div>
        <div class="history-event">
          23-08-2026 10:12 · ABC Store<strong>ORDER CREATED</strong>
        </div>
        <div class="history-event">
          23-08-2026 10:30 · Ko Ko<strong>ON_WAY</strong>
        </div>
      </section>
    </main>
    <script>
      const nav = document.createElement("div");
      nav.className = "slide-sidebar";
      nav.innerHTML =
        '<a class="sidebar-row" href="/shop/orders"><strong>Orders</strong><span>Active orders</span></a><a class="sidebar-row active-row" href="/shop/history"><strong>History</strong><span>All orders</span></a>';
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

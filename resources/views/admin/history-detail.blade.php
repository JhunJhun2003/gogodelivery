<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deli - History Detail</title>
    <link rel="stylesheet" href="/css/global.css" />
    <link rel="stylesheet" href="/css/components.css" />
    <link rel="stylesheet" href="/css/screens.css" />
  <script src="/js/sidebar.js" defer></script><script src="/js/history-controls.js" defer></script></head>
  <body class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <a class="back-link" href="./history.html">← Back to history</a
      ><span class="section-tag">ORDER · 01</span>
      <h1 class="main-heading">History detail</h1>
      <section class="ui-card-white history-detail-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">23-08-2026</span>
            <h2>Aye Aye</h2>
          </div>
          <span class="status-pill status-delivered">Delivered</span>
        </div>
        <div class="detail-layout">
          <div class="detail-photo">
            <img src="./assets/logo.jpg" alt="Package photo" />
          </div>
          <div class="detail-grid">
            <div><span>Customer name</span><strong>Aye Aye</strong></div>
            <div><span>Online shop</span><strong>Shopify Store</strong></div>
            <div><span>Biker</span><strong>Ko Min</strong></div>
            <div><span>Status</span><strong>Delivered</strong></div>
            <div>
              <span>Customer phone</span><strong>09 777 123 456</strong>
            </div>
            <div><span>Amount</span><strong>25,000 MMK</strong></div>
            <div class="detail-wide">
              <span>Customer address</span><strong>No. 12, Insein Road</strong>
            </div>
          </div>
        </div>
      </section>
    </main>
  </body>
</html>
<script>
  if (!document.getElementById("appSidebar")) {
    const nav = document.createElement("div");
    nav.className = "slide-sidebar";
    nav.id = "appSidebar";
    nav.innerHTML =
      '<a class="sidebar-row" href="./shops.html"><strong>Shops</strong><span>Partners</span></a><a class="sidebar-row" href="./bikers.html"><strong>Bikers</strong><span>Fleet</span></a><a class="sidebar-row" href="./way-check.html"><strong>Way Check</strong><span>Today</span></a><a class="sidebar-row active-row" href="./history.html"><strong>History</strong><span>Records</span></a><a class="sidebar-row" href="./users.html"><strong>Users</strong><span>Access</span></a>';
    const overlay = document.createElement("div");
    overlay.className = "sidebar-overlay";
    overlay.id = "appOverlay";
    document.body.append(nav, overlay);
    const button = document.querySelector(".hamburger-icon-btn");
    button.onclick = () => {
      nav.classList.add("open");
      overlay.classList.add("visible");
    };
    overlay.onclick = () => {
      nav.classList.remove("open");
      overlay.classList.remove("visible");
    };
  }
</script>

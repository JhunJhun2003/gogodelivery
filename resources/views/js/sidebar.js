/* Shared slide-out navigation for role-based views. */
(function () {
  if (document.getElementById("appSidebar")) return;
  if (location.pathname.includes("/auth/")) return;
  const folder = location.pathname.split("/").slice(-2, -1)[0];
  const isShop = document.body.dataset.role === "shop" || folder === "shop";
  const isAdmin = document.body.dataset.role === "admin" || folder === "admin";
  const links = isShop
    ? '<a class="sidebar-row" href="./orders.html"><strong>Orders</strong><span>Active orders</span></a><a class="sidebar-row" href="./history.html"><strong>History</strong><span>All orders</span></a>'
    : isAdmin
      ? '<a class="sidebar-row" href="./shops.html"><strong>Shops</strong><span>Partners</span></a><a class="sidebar-row" href="./bikers.html"><strong>Bikers</strong><span>Fleet</span></a><a class="sidebar-row" href="./way-check.html"><strong>Way Check</strong><span>Today</span></a><a class="sidebar-row" href="./history.html"><strong>History</strong><span>Records</span></a><a class="sidebar-row" href="./users.html"><strong>Users</strong><span>Access</span></a>'
      : '<a class="sidebar-row" href="./ways.html"><strong>Ways</strong><span>Assigned deliveries</span></a><a class="sidebar-row" href="./history.html"><strong>History</strong><span>My records</span></a>';
  const nav = document.createElement("div");
  nav.className = "slide-sidebar";
  nav.id = "appSidebar";
  nav.innerHTML = links;
  const current = location.pathname.split("/").pop() || "index.html";
  nav.querySelectorAll("a").forEach((link) => {
    if (link.getAttribute("href") === "./" + current)
      link.classList.add("active-row");
  });
  const overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  overlay.id = "appOverlay";
  document.body.append(nav, overlay);
  const button = document.querySelector(".hamburger-icon-btn");
  const close = () => {
    nav.classList.remove("open");
    overlay.classList.remove("visible");
  };
  if (button)
    button.onclick = () => {
      nav.classList.add("open");
      overlay.classList.add("visible");
    };
  overlay.onclick = close;
})();

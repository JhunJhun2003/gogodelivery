/* Shared slide-out navigation for role-based views. */
(function () {
  if (document.getElementById("appSidebar")) return;
  if (location.pathname.includes("/auth/")) return;
  const folder = location.pathname.split("/").slice(1, 2)[0] || '';
  const isShop = document.body.dataset.role === "shop" || (!document.body.dataset.role && folder === "shop");
  const isAdmin = document.body.dataset.role === "admin" || (!document.body.dataset.role && folder === "admin");
  const links = isShop
    ? '<a class="sidebar-row" href="/shop/orders"><strong>Orders</strong><span>Active orders</span></a><a class="sidebar-row" href="/shop/history"><strong>History</strong><span>All orders</span></a>'
    : isAdmin
      ? '<a class="sidebar-row" href="/admin/shops"><strong>Shops</strong><span>Partners</span></a><a class="sidebar-row" href="/admin/bikers"><strong>Bikers</strong><span>Fleet</span></a><a class="sidebar-row" href="/admin/way-check"><strong>Way Check</strong><span>Today</span></a><a class="sidebar-row" href="/admin/history"><strong>History</strong><span>Records</span></a><a class="sidebar-row" href="/admin/users"><strong>Users</strong><span>Access</span></a>'
        : '<a class="sidebar-row" href="/bikers/ways"><strong>Ways</strong><span>Assigned deliveries</span></a><a class="sidebar-row" href="/bikers/history"><strong>History</strong><span>My records</span></a>';
      const logout = '<form class="sidebar-logout" action="/logout" method="POST"><input type="hidden" name="_token" value="' + decodeURIComponent((document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/) || [])[1] || '') + '"><button class="sidebar-row" type="submit"><strong>Logout</strong><span>End session</span></button></form>';
  const nav = document.createElement("div");
  nav.className = "slide-sidebar";
  nav.id = "appSidebar";
  nav.innerHTML = links + logout;
  const current = location.pathname;
  nav.querySelectorAll("a").forEach((link) => {
    if (link.getAttribute("href") === current)
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

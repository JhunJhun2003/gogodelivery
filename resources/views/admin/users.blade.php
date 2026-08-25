<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Users</title>
    <link rel="stylesheet" href="/css/global.css" />
    <link rel="stylesheet" href="/css/components.css" />
    <link rel="stylesheet" href="/css/screens.css" />
  <script src="/js/sidebar.js" defer></script><script src="/js/history-controls.js" defer></script></head>
  <body class="app-bg">
    <header class="top-app-bar">
      <b class="bar-logo">DELI</b
      ><button class="hamburger-icon-btn" type="button">☰</button>
    </header>
    <main class="workspace-body">
      <span class="section-tag">ACCESS</span>
      <h1 class="main-heading">Users</h1>
      <section class="ui-card-white form-card">
        <h2>Create user</h2>
        <div class="input-field-group">
          <label>NAME</label><input placeholder="Full name" />
        </div>
        <div class="input-field-group">
          <label>EMAIL</label><input type="email" placeholder="Email" />
        </div>
        <div class="input-field-group">
          <label>PASSWORD</label
          ><input type="password" placeholder="Password" />
        </div>
        <div class="input-field-group">
          <label>ADDRESS</label><input placeholder="Address" />
        </div>
        <button class="ui-btn btn-navy-blue" type="button">Save user</button>
      </section>
      <section class="ui-card-white">
        <h2>All users</h2>
        <div class="directory-list">
          <div class="directory-item">
            <div><strong>May Zin</strong><span>mayzin@example.com</span></div>
            <div class="directory-actions">
              <button
                class="edit-user"
                data-name="May Zin"
                data-email="mayzin@example.com"
                type="button"
              >
                Edit
              </button>
            </div>
          </div>
          <div class="directory-item">
            <div>
              <strong>Htet Aung</strong><span>htetaung@example.com</span>
            </div>
            <div class="directory-actions">
              <button
                class="edit-user"
                data-name="Htet Aung"
                data-email="htetaung@example.com"
                type="button"
              >
                Edit
              </button>
            </div>
          </div>
        </div>
      </section>
    </main>
    <div class="modal-backdrop" id="editBackdrop" hidden>
      <section
        class="action-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="editTitle"
      >
        <h2 id="editTitle">Edit user</h2>
        <div class="input-field-group">
          <label>NAME</label><input id="editName" />
        </div>
        <div class="input-field-group">
          <label>EMAIL</label><input id="editEmail" type="email" />
        </div>
        <div class="input-field-group">
          <label>PASSWORD</label
          ><input
            id="editPassword"
            type="password"
            placeholder="Leave blank to keep current"
          />
        </div>
        <div class="input-field-group">
          <label>ADDRESS</label><input id="editAddress" placeholder="Address" />
        </div>
        <div class="modal-actions">
          <button class="back-button" id="cancelEdit" type="button">
            Cancel</button
          ><button class="ui-btn btn-navy-blue" id="saveEdit" type="button">
            Save changes
          </button>
        </div>
      </section>
    </div>
    <script>
      const backdrop = document.getElementById("editBackdrop"),
        nameInput = document.getElementById("editName"),
        emailInput = document.getElementById("editEmail");
      document.querySelectorAll(".edit-user").forEach(
        (button) =>
          (button.onclick = () => {
            nameInput.value = button.dataset.name;
            emailInput.value = button.dataset.email;
            document.getElementById("editPassword").value = "";
            document.getElementById("editAddress").value = "";
            backdrop.hidden = false;
            nameInput.focus();
          }),
      );
      document.getElementById("cancelEdit").onclick = () =>
        (backdrop.hidden = true);
      document.getElementById("saveEdit").onclick = () => {
        const active =
          [...document.querySelectorAll(".edit-user")].find(
            (button) => button.dataset.email === emailInput.dataset.original,
          ) ||
          document.querySelector(
            '.edit-user[data-name="' + nameInput.defaultValue + '"]',
          );
        if (active) {
          active.dataset.name = nameInput.value;
          active.dataset.email = emailInput.value;
          const item = active.closest(".directory-item");
          item.querySelector("strong").textContent = nameInput.value;
          item.querySelector("span").textContent = emailInput.value;
        }
        backdrop.hidden = true;
      };
      document.addEventListener("click", (e) => {
        if (e.target === backdrop) backdrop.hidden = true;
      });
    </script>
  </body>
</html>
<script>
  let editingUser = null;
  document.querySelectorAll(".edit-user").forEach((button) =>
    button.addEventListener("click", () => {
      editingUser = button;
    }),
  );
  document.getElementById("saveEdit").addEventListener("click", () => {
    if (!editingUser) return;
    editingUser.dataset.name = nameInput.value;
    editingUser.dataset.email = emailInput.value;
    const item = editingUser.closest(".directory-item");
    item.querySelector("strong").textContent = nameInput.value;
    item.querySelector("span").textContent = emailInput.value;
    backdrop.hidden = true;
  });
</script>
<script>
  if (!document.getElementById("appSidebar")) {
    const nav = document.createElement("div");
    nav.className = "slide-sidebar";
    nav.id = "appSidebar";
    nav.innerHTML =
      '<a class="sidebar-row" href="./shops.html"><strong>Shops</strong><span>Partners</span></a><a class="sidebar-row" href="./bikers.html"><strong>Bikers</strong><span>Fleet</span></a><a class="sidebar-row" href="./way-check.html"><strong>Way Check</strong><span>Today</span></a><a class="sidebar-row" href="./history.html"><strong>History</strong><span>Records</span></a><a class="sidebar-row active-row" href="./users.html"><strong>Users</strong><span>Access</span></a>';
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

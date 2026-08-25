<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Users</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg">
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
</script>

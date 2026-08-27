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
      <b class="bar-logo">DELI</b>
      <button class="hamburger-icon-btn" type="button">☰</button>
    </header>
    <main class="workspace-body">
      <span class="section-tag">ACCESS</span>
      <h1 class="main-heading">Users</h1>
      <section class="ui-card-white form-card">
        <h2>Create user</h2>
        @if (session('user_status'))
          <p class="form-status">{{ session('user_status') }}</p>
        @endif
        @if ($errors->user->any())
          <p class="form-error">{{ $errors->user->first() }}</p>
        @endif
        <form method="POST" action="{{ route('admin.users.create') }}">
          @csrf
          <div class="input-field-group">
            <label for="name">NAME</label>
            <input id="name" name="name" value="{{ old('name') }}" placeholder="Full name" required />
          </div>
          <div class="input-field-group">
            <label for="username">USERNAME</label>
            <input id="username" name="username" value="{{ old('username') }}" placeholder="Username" required />
          </div>
          <div class="input-field-group">
            <label for="role">ROLE</label>
            <select id="role" name="role" required>
              <option value="">Select a role</option>
              <option value="admin" @selected(old('role') === 'admin')>Admin</option>
              <option value="biker" @selected(old('role') === 'biker')>Biker</option>
            </select>
          </div>
          <div class="input-field-group" id="bikerField" hidden>
            <label for="biker_id">BIKER NAME</label>
            <select id="biker_id" name="biker_id" disabled>
              <option value="">Select a biker</option>
              @foreach ($bikers as $biker)
                <option value="{{ $biker->id }}" @selected((string) old('biker_id') === (string) $biker->id)>{{ $biker->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
            <label for="email">EMAIL</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Email" required />
          </div>
          <div class="input-field-group">
            <label for="password">PASSWORD</label>
            <input id="password" name="password" type="password" placeholder="Password" required />
          </div>
          <button class="ui-btn btn-navy-blue" type="submit">Save user</button>
        </form>
      </section>
      <section class="ui-card-white">
        <h2>All users</h2>
        <div class="directory-list">
          @forelse ($users as $user)
            <div class="directory-item">
              <div>
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->username }} · {{ ucfirst($user->role) }}{{ $user->biker ? ' · ' . $user->biker->name : '' }}</span>
              </div>
            </div>
          @empty
            <p class="no-data-msg">No users found.</p>
          @endforelse
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
      document.querySelectorAll(".input-field-group select").forEach((select) => {
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
        const optionsList = document.createElement("ul");
        optionsList.className = "custom-select-options";
        optionsList.setAttribute("role", "listbox");
        Array.from(select.options).forEach((option, index) => {
          const optionItem = document.createElement("li");
          optionItem.className = "custom-select-option";
          optionItem.textContent = option.text;
          optionItem.setAttribute("role", "option");
          optionItem.setAttribute("aria-selected", String(option.selected));
          if (option.selected) optionItem.classList.add("selected");
          optionItem.addEventListener("click", () => {
            select.selectedIndex = index;
            toggle.childNodes[0].textContent = option.text;
            optionsList.querySelectorAll(".custom-select-option").forEach((item) => item.classList.remove("selected"));
            optionItem.classList.add("selected");
            wrapper.classList.remove("open");
            toggle.setAttribute("aria-expanded", "false");
            select.dispatchEvent(new Event("change", { bubbles: true }));
          });
          optionsList.appendChild(optionItem);
        });
        wrapper.appendChild(optionsList);
        toggle.addEventListener("click", () => {
          document.querySelectorAll(".custom-select.open").forEach((openWrapper) => {
            if (openWrapper !== wrapper) openWrapper.classList.remove("open");
          });
          const isOpen = wrapper.classList.toggle("open");
          toggle.setAttribute("aria-expanded", String(isOpen));
        });
      });
      document.addEventListener("click", (event) => {
        if (!event.target.closest(".custom-select")) {
          document.querySelectorAll(".custom-select.open").forEach((wrapper) => wrapper.classList.remove("open"));
        }
      });

      const roleSelect = document.getElementById("role");
      const bikerField = document.getElementById("bikerField");
      const bikerSelect = document.getElementById("biker_id");
      const updateBikerField = () => {
        const isBiker = roleSelect.value === "biker";
        bikerField.hidden = !isBiker;
        bikerSelect.disabled = !isBiker;
        bikerSelect.required = isBiker;
      };
      roleSelect.addEventListener("change", updateBikerField);
      updateBikerField();
    </script>
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

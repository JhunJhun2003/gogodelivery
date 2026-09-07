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
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">{{ auth()->user()->name }} · {{ auth()->user()->username }}</span>
        <button class="hamburger-icon-btn" id="openMenuBtn" type="button" aria-label="Open navigation menu">☰</button>
      </div>
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
            <label for="phone_number">PHONE NUMBER</label>
            <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" placeholder="09..." required />
          </div>
          <div class="input-field-group">
            <label for="password">PASSWORD</label>
            <input id="password" name="password" type="password" placeholder="Password" required />
          </div>
          <div class="input-field-group">
            <label for="role">ROLE</label>
            <select id="role" name="role" required>
              <option value="">Select a role</option>
              <option value="admin" @selected(old('role') === 'admin')>Admin</option>
              <option value="staff" @selected(old('role') === 'staff')>Staff</option>
              <option value="biker" @selected(old('role') === 'biker')>Biker</option>
            </select>
          </div>
          <div class="input-field-group" id="bikerField" hidden>
            <label for="biker_id">BIKER NAME</label>
            <select id="biker_id" name="biker_id" disabled>
              <option value="">Select a biker</option>
              @forelse ($bikers as $biker)
                <option value="{{ $biker->id }}" @selected((string) old('biker_id') === (string) $biker->id)>{{ $biker->name }}</option>
              @empty
                <option value="" disabled>No available bikers left</option>
              @endforelse
            </select>
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
              <span class="shop-row-actions">
                <button type="button" class="edit-user-btn" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-username="{{ $user->username }}" data-phone-number="{{ $user->phone_number }}" data-role="{{ $user->role }}" data-biker-id="{{ $user->biker_id ?? '' }}" aria-label="Edit {{ $user->name }}">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-delete-form" onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="delete-user-btn" aria-label="Delete {{ $user->name }}" title="Delete user">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                  </button>
                </form>
              </span>
            </div>
          @empty
            <p class="no-data-msg">No users found.</p>
          @endforelse
        </div>
      </section>
    </main>
    <div class="modal-backdrop" id="editBackdrop" hidden>
      <section class="action-modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
        <h2 id="editTitle">Edit user</h2>
        <form id="editUserForm" method="POST">
          @csrf
          @method('PUT')
          <div class="input-field-group">
            <label for="editName">NAME</label><input id="editName" name="name" required />
          </div>
          <div class="input-field-group">
            <label for="editUsername">USERNAME</label><input id="editUsername" name="username" required />
          </div>
          <div class="input-field-group">
            <label for="editPhoneNumber">PHONE NUMBER</label><input id="editPhoneNumber" name="phone_number" type="tel" required />
          </div>
          <div class="input-field-group">
            <label for="editRole">ROLE</label>
            <select id="editRole" name="role" required>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
              <option value="biker">Biker</option>
            </select>
          </div>
          <div class="input-field-group" id="editBikerField" hidden>
            <label for="editBikerId">BIKER NAME</label>
            <select id="editBikerId" name="biker_id" disabled>
              <option value="">Select a biker</option>
              @forelse ($bikers as $biker)
                <option value="{{ $biker->id }}">{{ $biker->name }}</option>
              @empty
                <option value="" disabled>No available bikers left</option>
              @endforelse
            </select>
          </div>
          <div class="input-field-group">
            <label for="editPassword">PASSWORD</label><input id="editPassword" name="password" type="password" placeholder="Leave blank to keep current" />
          </div>
          <div class="modal-actions">
            <button class="back-button" id="cancelEdit" type="button">Cancel</button>
            <button class="ui-btn btn-navy-blue" type="submit">Save changes</button>
          </div>
        </form>
      </section>
    </div>
    <script>
      const roleSelect = document.getElementById("role");
      const bikerField = document.getElementById("bikerField");
      const bikerSelect = document.getElementById("biker_id");

      function initCustomSelect(select) {
        if (window.matchMedia("(max-width: 600px)").matches && !select.closest(".action-modal")) return;

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

        const inModal = !!select.closest(".action-modal, .modal-backdrop, #editBackdrop");
        const optionsList = document.createElement("ul");
        optionsList.className = "custom-select-options";
        optionsList.setAttribute("role", "listbox");
        if (inModal) {
          document.body.appendChild(optionsList);
        } else {
          wrapper.appendChild(optionsList);
        }

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
            optionsList.style.display = "none";
            toggle.setAttribute("aria-expanded", "false");
            select.dispatchEvent(new Event("change", { bubbles: true }));
          });
          optionsList.appendChild(optionItem);
        });

        toggle.addEventListener("click", () => {
          document.querySelectorAll(".custom-select-options").forEach((el) => {
            if (el !== optionsList) el.style.display = "none";
          });
          const isOpen = optionsList.style.display === "block";
          if (isOpen) {
            optionsList.style.display = "none";
            toggle.setAttribute("aria-expanded", "false");
          } else {
            const inModal = !!select.closest(".action-modal, .modal-backdrop");
            if (inModal) {
              const rect = toggle.getBoundingClientRect();
              optionsList.style.position = "fixed";
              optionsList.style.top = (rect.bottom + 4) + "px";
              optionsList.style.left = rect.left + "px";
              optionsList.style.width = rect.width + "px";
            } else {
              optionsList.style.position = "absolute";
              optionsList.style.top = "";
              optionsList.style.left = "";
              optionsList.style.width = "";
            }
            optionsList.style.display = "block";
            toggle.setAttribute("aria-expanded", "true");
          }
        });
      }

      document.querySelectorAll(".form-card select").forEach(initCustomSelect);
      document.querySelectorAll("#editBackdrop select").forEach(initCustomSelect);

      document.addEventListener("click", (event) => {
        if (!event.target.closest(".custom-select") && !event.target.closest(".custom-select-options")) {
          document.querySelectorAll(".custom-select-options").forEach((el) => el.style.display = "none");
        }
      });

      if (roleSelect && bikerField && bikerSelect) {
        const updateBikerField = () => {
          const isBiker = roleSelect.value === "biker";
          bikerField.hidden = !isBiker;
          bikerSelect.disabled = !isBiker;
          bikerSelect.required = isBiker;
        };
        roleSelect.addEventListener("change", updateBikerField);
        updateBikerField();
      }

      const backdrop = document.getElementById("editBackdrop");
      const editUserForm = document.getElementById("editUserForm");
      const editNameInput = document.getElementById("editName");
      const editUsernameInput = document.getElementById("editUsername");
      const editPhoneInput = document.getElementById("editPhoneNumber");
      const editRoleSelect = document.getElementById("editRole");
      const editBikerField = document.getElementById("editBikerField");
      const editBikerSelect = document.getElementById("editBikerId");
      const editPasswordInput = document.getElementById("editPassword");

      function syncEditBikerField() {
        const isBiker = editRoleSelect.value === "biker";
        editBikerField.hidden = !isBiker;
        editBikerSelect.disabled = !isBiker;
        editBikerSelect.required = isBiker;
        const roleToggle = editRoleSelect.closest(".custom-select")?.querySelector(".custom-select-toggle");
        if (roleToggle) roleToggle.childNodes[0].textContent = editRoleSelect.options[editRoleSelect.selectedIndex]?.text || "Select";
      }

      editRoleSelect.addEventListener("change", syncEditBikerField);

      document.querySelectorAll(".edit-user-btn").forEach((button) => {
        button.addEventListener("click", (event) => {
          event.stopPropagation();
          editUserForm.action = "/admin/users/" + button.dataset.id;
          editNameInput.value = button.dataset.name;
          editUsernameInput.value = button.dataset.username;
          editPhoneInput.value = button.dataset.phoneNumber;
          editRoleSelect.value = button.dataset.role || "admin";
          editPasswordInput.value = "";
          const bikerId = button.dataset.bikerId || "";
          editBikerSelect.value = bikerId;
          syncEditBikerField();
          const bikerToggle = editBikerSelect.closest(".custom-select")?.querySelector(".custom-select-toggle");
          if (bikerToggle) bikerToggle.childNodes[0].textContent = editBikerSelect.options[editBikerSelect.selectedIndex]?.text || "Select a biker";
          backdrop.hidden = false;
          editNameInput.focus();
        });
        button.addEventListener("keydown", (event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            event.stopPropagation();
            button.click();
          }
        });
      });

      document.getElementById("cancelEdit").addEventListener("click", () => {
        backdrop.hidden = true;
      });

      document.addEventListener("click", (event) => {
        if (event.target === backdrop) backdrop.hidden = true;
      });
    </script>
  </body>
</html>

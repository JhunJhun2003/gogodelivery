<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
    />
    <title>Deli - Way Check</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span>
        <button
          class="hamburger-icon-btn"
          id="openMenuBtn"
          type="button"
          aria-label="Open navigation menu"
          aria-expanded="false"
          aria-controls="appSidebar"
        >
          ☰
        </button>
      </div>
    </header>

    <main class="workspace-body">
      <span class="section-tag">OPERATIONS</span>
      <h1 class="main-heading">Way Check</h1>

      <div class="badge-group">
        <span class="ui-badge badge-navy">23-08-2026</span>
        <span class="ui-badge badge-lime">Total Way · 0</span>
      </div>

      <div class="ui-card-white form-card">
        <h3 class="form-title">New way</h3>
        <div class="input-field-group">
          <label>ONLINE SHOP</label
          ><select>
            <option>Select</option>
          </select>
        </div>
        <div class="input-field-group">
          <label>BIKER</label
          ><select>
            <option>Choose</option>
          </select>
        </div>
        <div class="input-field-group">
          <label>STATUS</label
          ><select>
            <option>PENDING</option>
          </select>
        </div>
        <div class="input-field-group">
          <label>CUSTOMER NAME</label><input type="text" />
        </div>
        <div class="input-field-group">
          <label>CUSTOMER PHONE</label><input type="tel" />
        </div>
        <div class="input-field-group">
          <label>CUSTOMER ADDRESS</label><input type="text" />
        </div>
        <div class="input-field-group">
          <label>AMOUNT</label><input type="number" value="0" />
        </div>
        <div class="input-field-group">
          <label>DELI AMOUNT</label><input type="number" value="0" />
        </div>
        <div class="input-field-group">
          <label>NOTES</label><input type="text" />
        </div>
        <div class="input-field-group photo-field-group">
          <label for="wayPhoto">DELIVERY PHOTO</label>
          <input
            id="wayPhoto"
            class="photo-input"
            type="file"
            accept="image/*"
          />
          <div class="photo-preview" id="photoPreview" aria-live="polite">
            <img id="photoPreviewImage" alt="Selected delivery photo preview" />
            <span id="photoPreviewName"></span>
          </div>
        </div>
        <button class="ui-btn btn-navy-blue">Add way</button>
      </div>
    </main>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const wayPhoto = document.getElementById("wayPhoto");
        const photoPreview = document.getElementById("photoPreview");
        const photoPreviewImage = document.getElementById("photoPreviewImage");
        const photoPreviewName = document.getElementById("photoPreviewName");

        document
          .querySelectorAll(".input-field-group select")
          .forEach((select) => {
            const wrapper = document.createElement("div");
            wrapper.className = "custom-select";
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);

            const toggle = document.createElement("button");
            toggle.type = "button";
            toggle.className = "custom-select-toggle";
            toggle.setAttribute("aria-haspopup", "listbox");
            toggle.setAttribute("aria-expanded", "false");
            toggle.textContent =
              select.options[select.selectedIndex]?.text || "Select";
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
                optionsList
                  .querySelectorAll(".custom-select-option")
                  .forEach((item) => item.classList.remove("selected"));
                optionItem.classList.add("selected");
                wrapper.classList.remove("open");
                toggle.setAttribute("aria-expanded", "false");
                select.dispatchEvent(new Event("change", { bubbles: true }));
              });
              optionsList.appendChild(optionItem);
            });
            wrapper.appendChild(optionsList);

            toggle.addEventListener("click", () => {
              document
                .querySelectorAll(".custom-select.open")
                .forEach((openWrapper) => {
                  if (openWrapper !== wrapper)
                    openWrapper.classList.remove("open");
                });
              const isOpen = wrapper.classList.toggle("open");
              toggle.setAttribute("aria-expanded", String(isOpen));
            });
          });

        document.addEventListener("click", (event) => {
          if (!event.target.closest(".custom-select")) {
            document
              .querySelectorAll(".custom-select.open")
              .forEach((wrapper) => wrapper.classList.remove("open"));
          }
        });

        if (wayPhoto && photoPreview && photoPreviewImage && photoPreviewName) {
          wayPhoto.addEventListener("change", () => {
            const photo = wayPhoto.files[0];
            if (!photo) {
              photoPreview.classList.remove("visible");
              photoPreviewImage.removeAttribute("src");
              return;
            }

            const photoUrl = URL.createObjectURL(photo);
            photoPreviewImage.src = photoUrl;
            photoPreviewName.textContent = photo.name;
            photoPreview.classList.add("visible");
            photoPreviewImage.onload = () => URL.revokeObjectURL(photoUrl);
          });
        }
      });
    </script>
  </body>
</html>

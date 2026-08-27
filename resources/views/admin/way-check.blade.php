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
        <span class="ui-badge badge-navy">{{ $today->format('d-m-Y') }}</span>
        <span class="ui-badge badge-lime">Total Ways · {{ $totalWays }}</span>
      </div>

      @if (session('way_status'))
        <p role="status">{{ session('way_status') }}</p>
      @endif
      <form class="ui-card-white form-card" method="POST" action="{{ route('admin.way-check.store') }}" enctype="multipart/form-data">
        @csrf
        <h3 class="form-title">New way</h3>
        <div class="input-field-group">
          <label>ONLINE SHOP</label>
          <select name="shop_id" required>
            <option value="">Select</option>
            @foreach ($shops as $shop)
              <option value="{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="input-field-group">
          <label>BIKER</label>
          <select name="biker_id">
            <option value="">Choose</option>
            @foreach ($bikers as $biker)
              <option value="{{ $biker->id }}">{{ $biker->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="input-field-group">
          <label>STATUS</label>
          <select name="status">
            <option value="pending">PENDING</option>
            <option value="onway">ON WAY</option>
            <option value="delivered">DELIVERED</option>
            <option value="failed">FAILED</option>
          </select>
        </div>
        <div class="input-field-group">
          <label>CUSTOMER NAME</label><input name="recipient_name" type="text" placeholder="Recipient name" required />
        </div>
        <div class="input-field-group">
          <label>CUSTOMER PHONE</label><input name="phone_number" type="tel" placeholder="09..." required />
        </div>
        <div class="input-field-group">
          <label>CUSTOMER ADDRESS</label><input name="address" type="text" placeholder="Delivery address" required />
        </div>
        <div class="input-field-group">
          <label>AMOUNT</label><input name="amount" type="number" min="0" value="0" required />
        </div>
        <div class="input-field-group">
          <label>DELI AMOUNT</label><input name="delivery_fees" type="number" min="0" value="0" required />
        </div>
        <div class="input-field-group">
          <label>DATE</label><input name="date" type="date" value="{{ old('date', now()->format('Y-m-d')) }}" required />
        </div>
        <div class="input-field-group">
          <label>NOTES</label><input name="remark" type="text" placeholder="Add a note" />
        </div>
        <div class="input-field-group photo-field-group">
          <label for="wayPhoto">DELIVERY PHOTO</label>
          <input
            id="wayPhoto"
            name="item_image"
            class="photo-input"
            type="file"
            accept="image/*"
          />
          <div class="photo-preview" id="photoPreview" aria-live="polite">
            <img id="photoPreviewImage" alt="Selected delivery photo preview" />
            <span id="photoPreviewName"></span>
          </div>
        </div>
        <button class="ui-btn btn-navy-blue" type="submit">Add way</button>
      </form>
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

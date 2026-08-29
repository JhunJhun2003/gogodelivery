<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
    />
    <title>Deli - Edit Way</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787689001" />
    <script src="/js/sidebar.js?v=1787686291" defer></script>
  </head>
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
      <a class="back-link" href="{{ route('admin.history.detail', $way) }}">← Back to detail</a>
      <span class="section-tag">ORDER · {{ $way->id }}</span>
      <h1 class="main-heading">Edit Way</h1>

      @if ($errors->any())
        <div class="ui-card-white" style="border-left:4px solid #e85b61; margin-bottom:12px;">
          @foreach ($errors->all() as $error)
            <p style="color:#e85b61; font-size:13px; margin:4px 0;">{{ $error }}</p>
          @endforeach
        </div>
      @endif

      @if (session('way_status'))
        <div class="ui-card-white" style="border-left:4px solid #00a66a; margin-bottom:12px;">
          <p style="color:#00a66a; font-size:13px;">{{ session('way_status') }}</p>
        </div>
      @endif

      <form class="ui-card-white form-card" method="POST" action="{{ route('admin.ways.update', $way) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h3 class="form-title">Edit Way #{{ $way->id }}</h3>
        <div class="input-field-group">
          <label>ONLINE SHOP</label>
          <select name="shop_id" required>
            @foreach ($shops as $shop)
              <option value="{{ $shop->id }}" @selected($way->shop_id === $shop->id)>{{ $shop->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="input-field-group">
          <label>BIKER</label>
          <select name="biker_id">
            <option value="">Choose</option>
            @foreach ($bikers as $biker)
              <option value="{{ $biker->id }}" @selected($way->biker_id === $biker->id)>{{ $biker->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="input-field-group">
          <label>STATUS</label>
          <select name="status" required>
            @foreach (['pending' => 'Pending', 'onway' => 'On way', 'delivered' => 'Delivered', 'failed' => 'Failed'] as $value => $label)
              <option value="{{ $value }}" @selected($way->status === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="input-field-group">
          <label>CUSTOMER NAME</label><input name="recipient_name" type="text" placeholder="Recipient name" value="{{ old('recipient_name', $way->recipient_name) }}" required />
        </div>
        <div class="input-field-group">
          <label>CUSTOMER PHONE</label><input name="phone_number" type="tel" placeholder="09..." value="{{ old('phone_number', $way->phone_number) }}" required />
        </div>
        <div class="input-field-group">
          <label>CUSTOMER ADDRESS</label><input name="address" type="text" placeholder="Delivery address" value="{{ old('address', $way->address) }}" required />
        </div>
        <div class="input-field-group">
          <label>AMOUNT</label><input name="amount" type="number" min="0" value="{{ old('amount', $way->amount) }}" required />
        </div>
        <div class="input-field-group">
          <label>DELI AMOUNT</label><input name="delivery_fees" type="number" min="0" value="{{ old('delivery_fees', $way->delivery_fees) }}" required />
        </div>
        <div class="input-field-group">
          <label>DATE</label><input name="date" type="date" value="{{ old('date', $way->date->format('Y-m-d')) }}" required />
        </div>
        <div class="input-field-group">
          <label>NOTES</label><input name="remark" type="text" placeholder="Add a note" value="{{ old('remark', $way->remark) }}" />
        </div>
        <div class="input-field-group photo-field-group">
          <label for="wayPhoto">DELIVERY PHOTO</label>
          @if ($way->item_image)
            <div class="photo-preview visible" style="margin-bottom:8px;">
              <img src="{{ asset($way->item_image) }}" alt="Current delivery photo" />
              <span>Current photo</span>
            </div>
          @endif
          <input
            id="wayPhoto"
            name="item_image"
            class="photo-input"
            type="file"
            accept="image/*"
          />
          <div class="photo-preview" id="photoPreview" aria-live="polite">
            <img id="photoPreviewImage" alt="New delivery photo preview" />
            <span id="photoPreviewName"></span>
          </div>
        </div>
        <div style="display:flex; gap:10px; margin-top:12px;">
          <button class="ui-btn btn-navy-blue" type="submit">Save changes</button>
          <a class="ui-btn" style="border:1px solid #e2e8f0; background:#fff; color:#64748b; text-decoration:none; text-align:center;" href="{{ route('admin.history.detail', $way) }}">Cancel</a>
        </div>
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

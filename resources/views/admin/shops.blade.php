<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Online Shops</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787689002" />
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span
        ><button
          class="hamburger-icon-btn"
          id="openMenuBtn"
          type="button"
          aria-label="Open navigation menu"
        >
          ☰
        </button>
      </div>
    </header>
    <main class="workspace-body shops-page">
      <div class="shop-hero-layout">
        <div class="shop-hero-heading">
          <span class="section-tag">OPERATIONS</span>
          <h1 class="main-heading">Online Shop List</h1>
        </div>
        <p class="page-intro">
          Select a shop to create orders. Click + to edit today's deliveries.
        </p>
        <div class="shop-hero-animation" aria-hidden="true">
          <dotlottie-player
            src="{{ asset('/animations/food-courier.json') }}"
            background="transparent"
            speed="1"
            loop
            autoplay
          ></dotlottie-player>
        </div>
      </div>
      @if (session('way_created'))
        <p class="form-success" role="status">{{ session('way_created') }}</p>
      @endif
      <section class="ui-card-white shop-list-card">
        <div class="section-card-heading">
          <h2>Shop list</h2>
          <button
            class="ui-btn btn-navy-blue compact-btn"
            id="addShopBtn"
            type="button"
          >
            Add
          </button>
        </div>
        <label class="sr-only" for="shopSearch">Search shops</label
        ><input
          id="shopSearch"
          class="shop-search"
          type="search"
          placeholder="Search shops..."
        />
        <div class="shop-list" id="shopList">
          @forelse ($shops as $shop)
            <button class="shop-row{{ $loop->first ? ' selected' : '' }}" type="button" data-shop="{{ $shop->name }}" data-shop-id="{{ $shop->id }}" data-username="{{ $shop->username }}" data-email="{{ $shop->email }}">
              <span class="shop-avatar{{ $loop->first ? ' avatar-lime' : '' }}">{{ strtoupper(substr($shop->name, 0, 1)) }}</span>
              <span class="shop-copy"><strong>{{ $shop->name }}</strong><small>{{ $shop->username }} · {{ $shop->email }}</small></span>
              <span class="shop-row-actions"><span class="edit-shop-btn" role="button" tabindex="0" data-id="{{ $shop->id }}" data-name="{{ $shop->name }}" data-username="{{ $shop->username }}" data-email="{{ $shop->email }}" aria-label="Edit {{ $shop->name }}">⚙</span><b class="add-way-btn" role="button" tabindex="0" aria-label="Create way for {{ $shop->name }}">+</b></span>
            </button>
          @empty
            <p class="shop-orders-empty">No shop accounts found.</p>
          @endforelse
          <p class="shop-orders-empty" id="shopNoResult" hidden>No matching shops found.</p>
        </div>
        <section class="ui-card-white nested-form" id="shopForm" @if (!$errors->getBag('shop')->any() && !session('shop_status')) hidden @endif>
          <div class="section-card-heading">
            <h2>Create shop</h2>
            <button class="back-button" id="closeShopForm" type="button" aria-label="Close create shop form">X</button>
          </div>
          @if (session('shop_status'))
            <p role="status">{{ session('shop_status') }}</p>
          @endif
          @if ($errors->getBag('shop')->any())
            <div role="alert">
              @foreach ($errors->getBag('shop')->all() as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
          @endif
          <form method="POST" action="{{ route('admin.shops.create') }}">
            @csrf
            <div class="input-field-group">
              <label for="shopName">NAME</label><input id="shopName" name="name" value="{{ old('name') }}" placeholder="Shop name" required />
            </div>
            <div class="input-field-group">
              <label for="shopUsername">USERNAME</label><input id="shopUsername" name="username" value="{{ old('username') }}" placeholder="Login username" required />
            </div>
            <div class="input-field-group">
              <label for="shopEmail">EMAIL</label><input id="shopEmail" name="email" type="email" value="{{ old('email') }}" placeholder="Email" required />
            </div>
            <div class="input-field-group">
              <label for="shopPassword">PASSWORD</label><input id="shopPassword" name="password" type="password" placeholder="Password" required />
            </div>
            <div class="input-field-group">
              <label for="shopPasswordConfirmation">CONFIRM PASSWORD</label><input id="shopPasswordConfirmation" name="password_confirmation" type="password" placeholder="Confirm password" required />
            </div>
            <button class="ui-btn btn-navy-blue" type="submit">Save shop</button>
          </form>
        </section>
      </section>
      <div class="modal-backdrop" id="editShopBackdrop" hidden>
        <section class="action-modal" role="dialog" aria-modal="true" aria-labelledby="editShopTitle">
          <h2 id="editShopTitle">Edit shop</h2>
          <form id="editShopForm" method="POST">
            @csrf
            @method('PUT')
            <div class="input-field-group">
              <label for="editShopName">NAME</label><input id="editShopName" name="name" required />
            </div>
            <div class="input-field-group">
              <label for="editShopUsername">USERNAME</label><input id="editShopUsername" name="username" required />
            </div>
            <div class="input-field-group">
              <label for="editShopEmail">EMAIL</label><input id="editShopEmail" name="email" type="email" required />
            </div>
            <div class="input-field-group">
              <label for="editShopPassword">PASSWORD</label><input id="editShopPassword" name="password" type="password" placeholder="Leave blank to keep current" />
            </div>
            <div class="input-field-group">
              <label for="editShopPasswordConfirmation">CONFIRM PASSWORD</label><input id="editShopPasswordConfirmation" name="password_confirmation" type="password" />
            </div>
            <div class="modal-actions">
              <button class="ui-btn btn-danger" style="margin-right:auto;" type="submit" form="deleteShopForm">Delete shop</button>
              <button class="back-button" id="cancelEditShop" type="button">Cancel</button>
              <button class="ui-btn btn-navy-blue" type="submit">Save changes</button>
            </div>
          </form>
          <form id="deleteShopForm" method="POST" onsubmit="return confirm('Delete this shop and all of its deliveries? This action cannot be undone.');">
            @csrf
            @method('DELETE')
          </form>
        </section>
      </div>
      
      <section class="ui-card-white form-card shop-order-card" id="wayCard" @if (!$errors->getBag('way')->any() && !session('way_status')) hidden @endif>
        <div class="section-card-heading">
          <h2 id="orderHeading">Online Shop · {{ $shops->first()?->name ?? 'No shop selected' }}</h2>
          <button class="back-button" id="closeWayForm" type="button" aria-label="Close create way form">X</button>
        </div>
        <p class="selected-shop-hint">
          Create a delivery for the selected shop.
        </p>
        @if ($errors->hasBag('way') && $errors->getBag('way')->any())
          <div role="alert">
            @foreach ($errors->getBag('way')->all() as $error)
              <p>{{ $error }}</p>
            @endforeach
          </div>
        @endif
        @if (session('way_status'))
          <p role="status">{{ session('way_status') }}</p>
        @endif
        <form id="wayForm" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="input-field-group">
            <label for="itemImage">ITEM IMAGE</label
            ><input
              id="itemImage"
              name="item_image"
              class="photo-input"
              type="file"
              accept="image/*"
            />
            <div class="photo-preview" id="photoPreview">
              <img id="photoPreviewImage" alt="Selected item preview" /><span
                id="photoPreviewName"
              ></span>
            </div>
          </div>
          <div class="input-field-group">
            <label>AMOUNT</label
            ><input name="amount" type="number" min="0" placeholder="0" />
          </div>
          <div class="input-field-group">
            <label>DELIVERY FEES</label
            ><input name="delivery_fees" type="number" min="0" placeholder="0" />
          </div>
          <div class="input-field-group">
            <label>RECIPIENT NAME</label
            ><input
              name="recipient_name"
              type="text"
              placeholder="Recipient name"
            />
          </div>
          <div class="input-field-group">
            <label>ADDRESS</label
            ><input name="address" type="text" placeholder="Delivery address" />
          </div>
          <div class="input-field-group">
            <label>PHONE NUMBER</label
            ><input
              name="phone_number"
              type="tel"
              inputmode="numeric"
              placeholder="09..."
            />
          </div>
          <div class="input-field-group">
            <label>DATE</label><input name="date" type="date" value="{{ old('date', today()->format('Y-m-d')) }}" required />
          </div>
          <div class="input-field-group">
            <label>REMARK</label
            ><textarea
              name="remark"
              rows="3"
              placeholder="Add a note"
            ></textarea>
          </div>
          <button class="ui-btn btn-navy-blue" type="submit">
            Add way
          </button>
        </form>
      </section>
    </main>
    <script>
      const shopSearch = document.getElementById("shopSearch");
      const addShopBtn = document.getElementById("addShopBtn");
      const shopForm = document.getElementById("shopForm");
      const orderHeading = document.getElementById("orderHeading");
      const rows = [...document.querySelectorAll(".shop-row")];
      const noResult = document.getElementById("shopNoResult");
      const itemImage = document.getElementById("itemImage");
      const photoPreview = document.getElementById("photoPreview");
      const photoPreviewImage = document.getElementById("photoPreviewImage");
      const photoPreviewName = document.getElementById("photoPreviewName");
      rows.forEach(
        (r) =>
          (r.onclick = () => {
            rows.forEach((x) => x.classList.remove("selected"));
            r.classList.add("selected");
            orderHeading.textContent = "Online Shop · " + r.dataset.shop;
            shopForm.hidden = true;
            wayForm.action = "/admin/shops/" + r.dataset.shopId + "/ways";
          }),
      );
      const editShopBackdrop = document.getElementById("editShopBackdrop");
      const editShopForm = document.getElementById("editShopForm");
      const deleteShopForm = document.getElementById("deleteShopForm");
      const editShopName = document.getElementById("editShopName");
      const editShopUsername = document.getElementById("editShopUsername");
      const editShopEmail = document.getElementById("editShopEmail");
      const editShopPassword = document.getElementById("editShopPassword");
      const editShopPasswordConfirmation = document.getElementById(
        "editShopPasswordConfirmation",
      );
      function openEditShop(button) {
        editShopForm.action = "/admin/shops/" + button.dataset.id;
        deleteShopForm.action = "/admin/shops/" + button.dataset.id;
        editShopName.value = button.dataset.name;
        editShopUsername.value = button.dataset.username;
        editShopEmail.value = button.dataset.email;
        editShopPassword.value = "";
        editShopPasswordConfirmation.value = "";
        editShopBackdrop.hidden = false;
        editShopName.focus();
      }
      document.querySelectorAll(".edit-shop-btn").forEach((button) => {
        button.addEventListener("click", (event) => {
          event.stopPropagation();
          openEditShop(button);
        });
        button.addEventListener("keydown", (event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            event.stopPropagation();
            openEditShop(button);
          }
        });
      });
      document.getElementById("cancelEditShop").onclick = () =>
        (editShopBackdrop.hidden = true);
      editShopBackdrop.onclick = (event) => {
        if (event.target === editShopBackdrop) editShopBackdrop.hidden = true;
      };
      const wayForm = document.getElementById("wayForm");
      const wayCard = document.querySelector(".shop-order-card");
      shopSearch.oninput = () => {
        const q = shopSearch.value.trim().toLowerCase();
        const shopList = document.getElementById("shopList");
        const matches = [];
        const others = [];

        rows.forEach((r) => {
          const haystack = ((r.dataset.shop || "") + " " + (r.dataset.username || "") + " " + (r.dataset.email || "")).toLowerCase();
          const show = !q || haystack.includes(q);
          r.hidden = !show;
          if (show) {
            matches.push(r);
          } else {
            others.push(r);
          }
        });

        const ordered = [...matches, ...others];
        ordered.forEach((row) => shopList.appendChild(row));

        if (noResult) {
          noResult.hidden = matches.length > 0;
        }
      };
      addShopBtn.onclick = () => {
        shopForm.hidden = !shopForm.hidden;
      };
      document.getElementById("closeShopForm").onclick = () => {
        shopForm.hidden = true;
      };
      document.getElementById("closeWayForm").onclick = () => {
        wayCard.hidden = true;
      };
      function selectShop(row) {
        rows.forEach((shopRow) => shopRow.classList.remove("selected"));
        row.classList.add("selected");
        orderHeading.textContent = "Online Shop · " + row.dataset.shop;
      }
      function openWayForm(button) {
        const row = button.closest(".shop-row");
        selectShop(row);
        shopForm.hidden = true;
        wayForm.action = "/admin/shops/" + row.dataset.shopId + "/ways";
        wayCard.hidden = false;
        wayCard.scrollIntoView({ behavior: "smooth", block: "start" });
        wayCard.querySelector("input[name=recipient_name]").focus();
      }
      document.querySelectorAll(".add-way-btn").forEach((button) => {
        button.addEventListener("click", (event) => {
          event.stopPropagation();
          openWayForm(button);
        });
        button.addEventListener("keydown", (event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            event.stopPropagation();
            openWayForm(button);
          }
        });
      });
      itemImage.onchange = (e) => {
        const f = e.target.files[0];
        if (!f) return;
        photoPreviewImage.src = URL.createObjectURL(f);
        photoPreviewName.textContent = f.name;
        photoPreview.classList.add("visible");
      };
      const nativeDate = document.querySelector('input[name="date"]');
      const dateWrap = document.createElement("div");
      dateWrap.className = "custom-date-picker";
      nativeDate.parentNode.insertBefore(dateWrap, nativeDate);
      dateWrap.appendChild(nativeDate);
      const trigger = document.createElement("button");
      trigger.type = "button";
      trigger.className = "custom-date-trigger";
      const initialDate = nativeDate.value;
      if (initialDate) {
        const [initialYear, initialMonth, initialDay] = initialDate.split("-");
        trigger.textContent = initialDay + "/" + initialMonth + "/" + initialYear.slice(-2);
      } else {
        trigger.textContent = "dd/mm/yy";
      }
      dateWrap.appendChild(trigger);
      const calendar = document.createElement("div");
      calendar.className = "custom-calendar";
      dateWrap.appendChild(calendar);
      let view = new Date();
      let chosen = initialDate;
      if (chosen) {
        const [chosenYear, chosenMonth] = chosen.split("-").map(Number);
        view = new Date(chosenYear, chosenMonth - 1, 1);
      }
      const pad = (n) => String(n).padStart(2, "0");
      function draw() {
        const y = view.getFullYear(),
          m = view.getMonth(),
          first = new Date(y, m, 1).getDay(),
          days = new Date(y, m + 1, 0).getDate();
        calendar.innerHTML =
          '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' +
          view.toLocaleString("en", { month: "long", year: "numeric" }) +
          '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" id="calendarDays"></div>';
        const grid = calendar.querySelector("#calendarDays");
        for (let i = 0; i < first; i++) {
          const b = document.createElement("button");
          b.className = "muted";
          b.disabled = true;
          grid.appendChild(b);
        }
        for (let d = 1; d <= days; d++) {
          const b = document.createElement("button");
          b.type = "button";
          b.textContent = d;
          const value = y + "-" + pad(m + 1) + "-" + pad(d);
          if (value === chosen) b.className = "selected";
          b.onclick = () => {
            chosen = value;
            nativeDate.value = value;
            trigger.textContent =
              pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2);
            dateWrap.classList.remove("open");
            draw();
          };
          grid.appendChild(b);
        }
      }
      trigger.onclick = () => {
        dateWrap.classList.toggle("open");
        draw();
      };
      calendar.onclick = (e) => {
        if (e.target.dataset.step) {
          view.setMonth(view.getMonth() + Number(e.target.dataset.step));
          draw();
        }
      };
      document.addEventListener("click", (e) => {
        if (!dateWrap.contains(e.target)) dateWrap.classList.remove("open");
      });

      const submitWayForm = async (e) => {
        e.preventDefault();
        const token = wayForm.querySelector('input[name="_token"]').value;
        const formData = new FormData(wayForm);
        const res = await fetch(wayForm.action, {
          method: "POST",
          headers: { "X-CSRF-TOKEN": token, Accept: "application/json" },
          body: formData,
        });
        const json = await res.json();
        if (res.ok) {
          wayForm.reset();
          photoPreview.classList.remove("visible");
          photoPreviewImage.src = "";
          photoPreviewName.textContent = "";
          const banner = document.createElement("p");
          banner.className = "form-success";
          banner.textContent = json.message || "Way created successfully.";
          wayCard.querySelector(".section-card-heading").after(banner);
          setTimeout(() => banner.remove(), 4000);
        } else {
          const errBox = wayCard.querySelector("[role='alert']") || (() => {
            const d = document.createElement("div");
            d.setAttribute("role", "alert");
            wayCard.querySelector("form").insertAdjacentElement("beforeend", d);
            return d;
          })();
          errBox.innerHTML = Object.values(json.errors || {}).flat().map((m) => "<p>" + m + "</p>").join("");
        }
      };

      wayForm.addEventListener("submit", submitWayForm);
      draw();
    </script>
  </body>
</html>

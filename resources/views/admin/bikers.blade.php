<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Bikers</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787684056" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span
        ><button class="hamburger-icon-btn" id="menu" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body bikers-page">
      <span class="section-tag">OPERATIONS</span>
      <h1 class="main-heading">Biker List</h1>
      <p class="page-intro">
        Click a biker name to view assigned deliveries. Click + to assign
        unassigned orders.
      </p>
      <section class="ui-card-white biker-list-card">
        <div class="section-card-heading">
          <h2>Biker list</h2>
          <button
            class="ui-btn btn-navy-blue compact-btn"
            id="addBiker"
            type="button"
          >
            Add
          </button>
        </div>
        <p class="unassigned-count">10 unassigned orders today</p>
        <div class="biker-list">
          <button class="biker-row selected" data-biker="Ko Ko" type="button">
            <span class="biker-avatar avatar-lime">🏍</span
            ><span class="shop-copy"
              ><strong>Ko Ko</strong><small>6 assigned today</small></span
            ><span class="biker-actions"
              ><span>⚙</span><b class="assign-trigger">+</b></span
            ></button
          ><button class="biker-row" data-biker="KoAye" type="button">
            <span class="biker-avatar">🏍</span
            ><span class="shop-copy"
              ><strong>KoAye</strong><small>0 assigned today</small></span
            ><span class="biker-actions"
              ><span>⚙</span><b class="assign-trigger">+</b></span
            ></button
          ><button class="biker-row" data-biker="Kolay" type="button">
            <span class="biker-avatar">🏍</span
            ><span class="shop-copy"
              ><strong>Kolay</strong><small>0 assigned today</small></span
            ><span class="biker-actions"
              ><span>⚙</span><b class="assign-trigger">+</b></span
            ></button
          ><button class="biker-row" data-biker="moemoe" type="button">
            <span class="biker-avatar">🏍</span
            ><span class="shop-copy"
              ><strong>moemoe</strong><small>0 assigned today</small></span
            ><span class="biker-actions"
              ><span>⚙</span><b class="assign-trigger">+</b></span
            >
          </button>
        </div>
        <section class="ui-card-white nested-form" id="bikerForm" hidden>
          <h2>Create biker</h2>
          <div class="input-field-group">
            <label>NAME</label><input placeholder="Full name" />
          </div>
          <div class="input-field-group">
            <label>PHONE</label><input type="tel" placeholder="09..." />
          </div>
          <button class="ui-btn btn-navy-blue" type="button">Save biker</button>
        </section>
      </section>
      <section class="ui-card-white assigned-card" id="assignedView">
        <div class="detail-section-heading">
          <div>
            <h2 id="assignedTitle">Biker Name — Ko Ko</h2>
            <p>Assigned deliveries today · click + to add more</p>
          </div>
          <button class="table-action" type="button">Edit biker</button>
        </div>
        <input
          class="shop-search"
          type="search"
          placeholder="Search assigned orders (#, name, address, phone)..."
        />
        <div class="delivery-list">
          <article class="delivery-card" data-status="active">
            <div class="delivery-main">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>#l4adu · Jejey / Bahan</strong>
                <p>ABC Store / 1234567 / 60,000 / 5,000 deli</p>
                <small>ADDRESS · Bahan</small>
              </div>
            </div>
            <div class="delivery-actions">
              <button class="status-btn onway" type="button">onway</button
              ><button class="status-btn fail" type="button">fail</button
              ><button class="info-btn" type="button">Info</button>
            </div>
          </article>
          <article class="delivery-card" data-status="active">
            <div class="delivery-main">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>#jo6a4h · မောင် / ကျောက်တံတား</strong>
                <p>M Store / 09665690997 / 26,500 / 3,500 deli</p>
                <small>ADDRESS · Downtown</small>
              </div>
            </div>
            <div class="delivery-actions">
              <button class="status-btn onway" type="button">onway</button
              ><button class="status-btn fail" type="button">fail</button
              ><button class="info-btn" type="button">Info</button>
            </div>
          </article>
        </div>
      </section>
      <section class="assign-view" id="assignView" hidden>
        <section class="ui-card-white assign-intro">
          <p>
            Assign unassigned orders to <strong id="assignTitle">Ko Ko</strong>
          </p>
          <button class="back-button" id="backBtn" type="button">
            Back to assigned list
          </button>
        </section>
        <section class="ui-card-white find-order">
          <div class="section-card-heading">
            <h2>Find order</h2>
            <span id="selectedCount">0 selected</span>
          </div>
          <div class="assign-tools">
            <input
              id="orderSearch"
              class="shop-search"
              type="search"
              placeholder="Search by order #, name, address..."
            /><button class="table-action" id="selectAll" type="button">
              Select all
            </button>
          </div>
        </section>
        <div id="orderList">
          <article class="ui-card-white order-card">
            <label
              ><input class="order-check" type="checkbox" />
              <strong>Order #91axcn</strong></label
            >
            <div class="order-content">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>Cho Aung Tin</strong>
                <p>M Store / 0977483047 / 26,000 / 3,000 deli</p>
                <small>Status: PENDING</small>
              </div>
            </div>
            <label class="order-address"
              >ADDRESS<input value="ကျားမြို့တွင်" readonly
            /></label>
          </article>
          <article class="ui-card-white order-card">
            <label
              ><input class="order-check" type="checkbox" />
              <strong>Order #ovzbgx</strong></label
            >
            <div class="order-content">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>Nyi Nyi Thant</strong>
                <p>M Store / 09257191532 / 25,000 / 3,000 deli</p>
                <small>Status: PENDING</small>
              </div>
            </div>
            <label class="order-address"
              >ADDRESS<input value="အလုံ" readonly
            /></label>
          </article>
        </div>
        <div class="assign-footer">
          <span>Select orders to assign</span
          ><button class="ui-btn btn-lime-green" id="assignBtn" type="button">
            Assign selected (0)
          </button>
        </div>
      </section>
    </main>
    <script>
      const bikerRows = [...document.querySelectorAll(".biker-row")],
        assignedView = document.getElementById("assignedView"),
        assignView = document.getElementById("assignView"),
        assignTitle = document.getElementById("assignTitle");
      bikerRows.forEach(
        (row) =>
          (row.onclick = () => {
            bikerRows.forEach((x) => x.classList.remove("selected"));
            row.classList.add("selected");
            document.getElementById("assignedTitle").textContent =
              "Biker Name — " + row.dataset.biker;
          }),
      );
      document.querySelectorAll(".assign-trigger").forEach(
        (button, i) =>
          (button.onclick = (e) => {
            e.stopPropagation();
            const name = bikerRows[i].dataset.biker;
            assignTitle.textContent = name;
            assignedView.hidden = true;
            assignView.hidden = false;
          }),
      );
      addBiker.onclick = () => {
        bikerForm.hidden = !bikerForm.hidden;
      };
      backBtn.onclick = () => {
        assignView.hidden = true;
        assignedView.hidden = false;
      };
      const checks = [...document.querySelectorAll(".order-check")];
      function updateCount() {
        const n = checks.filter((x) => x.checked).length;
        selectedCount.textContent = n + " selected";
        assignBtn.textContent = "Assign selected (" + n + ")";
        assignBtn.disabled = !n;
      }
      checks.forEach((x) => (x.onchange = updateCount));
      selectAll.onclick = () => {
        const visible = checks.filter((x) => !x.closest(".order-card").hidden);
        const all = visible.every((x) => x.checked);
        visible.forEach((x) => (x.checked = !all));
        updateCount();
      };
      orderSearch.oninput = () => {
        const q = orderSearch.value.toLowerCase();
        document
          .querySelectorAll(".order-card")
          .forEach(
            (x) => (x.hidden = !x.textContent.toLowerCase().includes(q)),
          );
      };
      assignBtn.onclick = () => {
        checks
          .filter((x) => x.checked)
          .forEach((x) => x.closest(".order-card").remove());
        updateCount();
      };
    </script>
  </body>
</html>
<div class="modal-backdrop" id="modalBackdrop" hidden>
  <div class="action-modal" id="doneModal">
    <h2>Mark delivery done?</h2>
    <p>This delivery will be marked as completed.</p>
    <div class="modal-actions">
      <button class="back-button" data-close-modal type="button">Cancel</button
      ><button class="ui-btn btn-lime-green" id="confirmDone" type="button">
        Confirm done
      </button>
    </div>
  </div>
  <div class="action-modal" id="failModal" hidden>
    <h2>Fail reason</h2>
    <p>Why did this delivery fail?</p>
    <textarea
      id="failReason"
      rows="4"
      placeholder="Write fail reason..."
    ></textarea>
    <div class="modal-actions">
      <button class="back-button" data-close-modal type="button">Cancel</button
      ><button class="ui-btn btn-danger" id="confirmFail" type="button">
        Confirm fail
      </button>
    </div>
  </div>
  <div class="action-modal info-modal" id="infoModal" hidden>
    <h2>Way Info History</h2>
    <p>Onway / fail notes / delivered timeline</p>
    <div class="history-event">
      24-08-2026 21:56 · Ko Ko<strong>ON_WAY</strong>
    </div>
    <div class="history-event">
      24-08-2026 21:57 · Ko Ko<strong>FAILED</strong
      ><em>Customer unavailable</em>
    </div>
    <div class="history-event">
      24-08-2026 22:10 · Ko Ko<strong>DELIVERED</strong>
    </div>
    <div class="modal-actions">
      <button class="back-button" data-close-modal type="button">Close</button>
    </div>
  </div>
</div>
<script>
  let activeDelivery = null;
  const backdrop = document.getElementById("modalBackdrop"),
    doneModal = document.getElementById("doneModal"),
    failModal = document.getElementById("failModal"),
    infoModal = document.getElementById("infoModal");
  function openModal(modal) {
    backdrop.hidden = false;
    doneModal.hidden = true;
    failModal.hidden = true;
    infoModal.hidden = true;
    modal.hidden = false;
  }
  function closeModal() {
    backdrop.hidden = true;
  }
  document.querySelectorAll(".delivery-card").forEach((card) => {
    const onway = card.querySelector(".onway"),
      fail = card.querySelector(".fail"),
      info = card.querySelector(".info-btn");
    onway.onclick = () => {
      activeDelivery = card;
      onway.hidden = true;
      fail.hidden = false;
      const done = document.createElement("button");
      done.className = "status-btn done";
      done.textContent = "done";
      done.type = "button";
      done.onclick = () => openModal(doneModal);
      onway.parentNode.insertBefore(done, fail);
    };
    fail.onclick = () => {
      activeDelivery = card;
      openModal(failModal);
    };
    info.onclick = () => openModal(infoModal);
  });
  document
    .querySelectorAll("[data-close-modal]")
    .forEach((x) => (x.onclick = closeModal));
  confirmDone.onclick = () => {
    if (activeDelivery) {
      activeDelivery.dataset.status = "done";
      activeDelivery.querySelector(".onway").hidden = true;
      activeDelivery.querySelector(".fail").hidden = true;
      const done = activeDelivery.querySelector(".done");
      if (done) {
        done.textContent = "done";
        done.classList.add("completed");
      }
    }
    closeModal();
  };
  confirmFail.onclick = () => {
    if (activeDelivery) {
      activeDelivery.dataset.status = "failed";
      activeDelivery.querySelector(".onway").hidden = false;
      activeDelivery.querySelector(".fail").classList.add("failed");
      activeDelivery.querySelector(".fail").textContent = "failed";
      let note = activeDelivery.querySelector(".fail-note");
      if (!note) {
        note = document.createElement("small");
        note.className = "fail-note";
        activeDelivery.querySelector(".delivery-actions").append(note);
      }
      note.textContent = failReason.value || "No reason provided";
    }
    closeModal();
  };
</script>
<script>
  document.querySelectorAll(".delivery-card").forEach((card) => {
    const onway = card.querySelector(".onway"),
      fail = card.querySelector(".fail");
    onway.addEventListener("click", () => {
      fail.classList.remove("failed");
      fail.textContent = "fail";
      fail.style.display = "inline-block";
      const done = card.querySelector(".done");
      if (done) done.hidden = false;
    });
    fail.addEventListener("click", () => {
      setTimeout(() => {
        const done = card.querySelector(".done");
        if (done) done.hidden = true;
        onway.hidden = false;
        fail.classList.add("failed");
        fail.textContent = "failed";
      }, 0);
    });
  });
  confirmDone.addEventListener("click", () => {
    setTimeout(() => {
      if (activeDelivery) {
        activeDelivery.querySelector(".onway").hidden = true;
        activeDelivery.querySelector(".fail").hidden = true;
        const done = activeDelivery.querySelector(".done");
        if (done) {
          done.hidden = false;
          done.classList.add("completed");
        }
      }
    }, 0);
  });
</script>
<script>
  document.querySelectorAll(".delivery-card").forEach((card) => {
    card.querySelector(".onway").addEventListener("click", () => {
      const buttons = [...card.querySelectorAll(".done")];
      buttons.slice(1).forEach((button) => button.remove());
      const done = buttons[0];
      if (done) {
        done.hidden = false;
        done.classList.remove("completed");
      }
    });
  });
</script>
<input
  id="bikerSearch"
  class="shop-search"
  type="search"
  placeholder="Search bikers..."
/>
<script>
  document.getElementById("bikerSearch").addEventListener("input", (e) => {
    const query = e.target.value.toLowerCase().trim();
    document.querySelectorAll(".biker-row").forEach((row) => {
      row.hidden = !row.dataset.biker.toLowerCase().includes(query);
    });
  });
</script>
<script>
  const bikerSearch = document.getElementById("bikerSearch");
  const bikerCard = document.querySelector(".biker-list-card");
  bikerCard.insertBefore(bikerSearch, bikerCard.querySelector(".biker-list"));
  bikerSearch.addEventListener("input", (e) => {
    const query = e.target.value.toLowerCase().trim();
    document.querySelectorAll(".biker-row").forEach((row) => {
      row.hidden = !row.dataset.biker.toLowerCase().includes(query);
    });
  });
</script>
<script>
</script>
<script>
  document
    .querySelectorAll(".sidebar-row")
    .forEach((row) =>
      row.classList.toggle(
        "active-row",
        row.getAttribute("href") === "/admin/bikers",
      ),
    );
</script>

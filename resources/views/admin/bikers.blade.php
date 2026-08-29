<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Bikers</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787687001" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
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
        <p class="unassigned-count">{{ $unassignedWays->count() }} unassigned ways today</p>
        <input
          id="bikerSearch"
          class="shop-search"
          type="search"
          placeholder="Search biker name..."
        />
        <div class="biker-list">
          @forelse ($bikers as $biker)
            <button class="biker-row{{ $loop->first ? ' selected' : '' }}" data-biker="{{ $biker->name }}" data-biker-id="{{ $biker->id }}" type="button">
              <span class="biker-avatar{{ $loop->first ? ' avatar-lime' : '' }}">🏍</span>
              <span class="shop-copy"><strong>{{ $biker->name }}</strong><small>{{ $assignedWays->get($biker->id, collect())->count() }} assigned</small></span>
              <span class="biker-actions"><span class="edit-biker-btn" role="button" tabindex="0" data-id="{{ $biker->id }}" data-name="{{ $biker->name }}" aria-label="Edit {{ $biker->name }}">⚙</span><b class="assign-trigger">+</b></span>
            </button>
          @empty
            <p class="shop-orders-empty">No bikers found.</p>
          @endforelse
        </div>
        <section class="ui-card-white nested-form" id="bikerForm" @if (!$errors->getBag('biker')->any()) hidden @endif>
          <h2>Create biker</h2>
          @if (session('biker_status'))
            <p role="status">{{ session('biker_status') }}</p>
          @endif
          @if ($errors->getBag('biker')->any())
            <div role="alert">
              @foreach ($errors->getBag('biker')->all() as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
          @endif
          <form method="POST" action="{{ route('admin.bikers.create') }}">
            @csrf
            <div class="input-field-group">
              <label for="bikerName">NAME</label><input id="bikerName" name="name" value="{{ old('name') }}" placeholder="Full name" required />
            </div>
            <button class="ui-btn btn-navy-blue" type="submit">Save biker</button>
          </form>
        </section>
        <div class="modal-backdrop" id="editBikerBackdrop" hidden>
          <section class="action-modal" role="dialog" aria-modal="true" aria-labelledby="editBikerTitle">
            <h2 id="editBikerTitle">Edit biker</h2>
            <form id="editBikerForm" method="POST">
              @csrf
              @method('PUT')
              <div class="input-field-group">
                <label for="editBikerName">NAME</label><input id="editBikerName" name="name" required />
              </div>
              <div class="modal-actions">
                <button class="back-button" id="cancelEditBiker" type="button">Cancel</button>
                <button class="ui-btn btn-navy-blue" type="submit">Save changes</button>
              </div>
            </form>
          </section>
        </div>
        <br>
        <div id="assignedView">
        <div class="detail-section-heading">
          <div>
            <h2 id="assignedTitle">Biker Name — {{ $bikers->first()?->name ?? 'No biker selected' }}</h2>
            <p>Assigned deliveries today · click + to add more</p>
          </div>
          
        </div>
        <input
          class="shop-search"
          type="search"
          placeholder="Search assigned orders (#, name, address, phone)..."
        />
        <div class="delivery-list">
          @foreach ($bikers as $biker)
            @foreach ($assignedWays->get($biker->id, collect()) as $way)
              <article class="delivery-card" data-biker-id="{{ $biker->id }}" data-way-id="{{ $way->id }}" data-status="{{ $way->status }}">
                <div class="delivery-main">
                  <div class="order-photo">@if($way->item_image)<img src="{{ asset($way->item_image) }}" alt="Item" />@else ITEM @endif</div>
                  <div>
                    <strong>#{{ $way->id }} · {{ $way->recipient_name }}</strong>
                    <p>{{ $way->phone_number }} / {{ $way->amount }} / {{ $way->delivery_fees }} deli</p>
                    <small>ADDRESS · {{ $way->address }}</small>
                    <small>ASSIGNED · {{ ($way->assigned_at ?? $way->date)->format('d-m-Y') }}</small>
                  </div>
                </div>
                <div class="delivery-actions">
                  @if ($way->status !== 'delivered')
                    <button class="status-btn onway" type="button" {{ $way->status === 'onway' ? 'disabled' : '' }}>On way</button>
                    
                    <button class="status-btn done" type="button">done</button>
                    <button class="status-btn fail" type="button">fail</button>
                  @endif
                  <span class="status-pill status-{{ $way->status }}">{{ $way->status === 'onway' ? 'On way' : ucfirst($way->status) }}</span>
                  
                  <button class="info-btn" type="button">Info</button>
                </div>
              </article>
            @endforeach
          @endforeach
          <p class="shop-orders-empty" id="assignedEmpty" hidden>No ways assigned to this biker today.</p>
        </div>
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
          @forelse ($unassignedWays as $way)
            <article class="ui-card-white order-card" data-way-id="{{ $way->id }}">
              <label><input class="order-check" type="checkbox" /><strong>Way #{{ $way->id }}</strong></label>
              <div class="order-content">
                <div class="order-photo">@if($way->item_image)<img src="{{ asset($way->item_image) }}" alt="Item" />@else ITEM @endif</div>
                <div>
                  <strong>{{ $way->recipient_name }}</strong>
                  <p>{{ $way->phone_number }} / {{ $way->amount }} / {{ $way->delivery_fees }} deli</p>
                  <small>Status: {{ strtoupper($way->status) }}</small>
                </div>
              </div>
              <label class="order-address">ADDRESS<input value="{{ $way->address }}" readonly /></label>
            </article>
          @empty
            <p class="shop-orders-empty">No unassigned ways to assign to this biker today.</p>
          @endforelse
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
        assignTitle = document.getElementById("assignTitle"),
        bikerSearch = document.getElementById("bikerSearch");
      let selectedBikerId = null;
        const assignedCards = [...document.querySelectorAll(".delivery-card[data-biker-id]")];
        const assignedEmpty = document.getElementById("assignedEmpty");
        function showAssignedWays(bikerId) {
          const visibleCards = assignedCards.filter((card) => card.dataset.bikerId === bikerId);
          assignedCards.forEach((card) => (card.hidden = card.dataset.bikerId !== bikerId));
          assignedEmpty.hidden = visibleCards.length > 0;
        }
      bikerRows.forEach(
        (row) =>
          (row.onclick = () => {
            bikerRows.forEach((x) => x.classList.remove("selected"));
            row.classList.add("selected");
            document.getElementById("assignedTitle").textContent =
              "Biker Name — " + row.dataset.biker;
              showAssignedWays(row.dataset.bikerId);
          }),
      );
        if (bikerRows[0]) showAssignedWays(bikerRows[0].dataset.bikerId);
      const editBikerBackdrop = document.getElementById("editBikerBackdrop");
      const editBikerForm = document.getElementById("editBikerForm");
      const editBikerName = document.getElementById("editBikerName");
      function openEditBiker(button) {
        editBikerForm.action = "/admin/bikers/" + button.dataset.id;
        editBikerName.value = button.dataset.name;
        editBikerBackdrop.hidden = false;
        editBikerName.focus();
      }
      document.querySelectorAll(".edit-biker-btn").forEach((button) => {
        button.addEventListener("click", (event) => {
          event.stopPropagation();
          openEditBiker(button);
        });
        button.addEventListener("keydown", (event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            event.stopPropagation();
            openEditBiker(button);
          }
        });
      });
      document.getElementById("cancelEditBiker").onclick = () =>
        (editBikerBackdrop.hidden = true);
      editBikerBackdrop.onclick = (event) => {
        if (event.target === editBikerBackdrop) editBikerBackdrop.hidden = true;
      };
      document.querySelectorAll(".assign-trigger").forEach(
        (button, i) =>
          (button.onclick = (e) => {
            e.stopPropagation();
            const name = bikerRows[i].dataset.biker;
            selectedBikerId = bikerRows[i].dataset.bikerId;
            assignTitle.textContent = name;
            assignBtn.dataset.bikerId = selectedBikerId;
            assignedView.hidden = true;
            assignView.hidden = false;
          }),
      );
      if (bikerSearch) {
        bikerSearch.addEventListener("input", (e) => {
          const query = e.target.value.toLowerCase().trim();
          const bikerList = document.querySelector(".biker-list");
          const matches = [];
          const others = [];

          bikerRows.forEach((row) => {
            const bikerName = (row.dataset.biker || "").toLowerCase();
            const show = !query || bikerName.includes(query);
            row.hidden = !show;
            if (show) {
              matches.push(row);
            } else {
              others.push(row);
            }
          });

          const ordered = [...matches, ...others];
          ordered.forEach((row) => bikerList.appendChild(row));

          const visible = bikerRows.filter((row) => !row.hidden);
          if (visible.length) {
            bikerRows.forEach((row) => row.classList.remove("selected"));
            visible[0].classList.add("selected");
            document.getElementById("assignedTitle").textContent = "Biker Name — " + visible[0].dataset.biker;
            showAssignedWays(visible[0].dataset.bikerId);
          } else {
            bikerRows.forEach((row) => row.classList.remove("selected"));
            document.getElementById("assignedTitle").textContent = "Biker Name — No biker selected";
            assignedEmpty.hidden = true;
            assignedCards.forEach((card) => (card.hidden = true));
          }
        });
      }

      addBiker.onclick = () => {
        bikerForm.hidden = !bikerForm.hidden;
      };
      backBtn.onclick = () => {
        assignView.hidden = true;
        assignedView.hidden = false;
      };
      const checks = [...document.querySelectorAll(".order-check")];
      async function updateWayStatus(button, status) {
        const card = button.closest(".delivery-card");
        const wayId = card?.dataset.wayId;
        if (!wayId) return;

        const token = document.querySelector('input[name="_token"]')?.value;
        if (!token) return;

        const response = await fetch("/admin/ways/" + wayId + "/status", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token,
            Accept: "application/json",
          },
          body: JSON.stringify({ status }),
        });

        if (!response.ok) {
          alert("Unable to update delivery status.");
          return;
        }

        window.location.reload();
      }

      document.querySelectorAll(".status-btn.onway").forEach((button) => {
        button.addEventListener("click", () => updateWayStatus(button, "onway"));
      });
      document.querySelectorAll(".status-btn.fail").forEach((button) => {
        button.addEventListener("click", () => updateWayStatus(button, "failed"));
      });
      document.querySelectorAll(".status-btn.done").forEach((button) => {
        button.addEventListener("click", () => updateWayStatus(button, "delivered"));
      });

      const assignedSearch = document.querySelector('#assignedView input[type="search"]');
      if (assignedSearch) {
        assignedSearch.addEventListener('input', (e) => {
          const query = e.target.value.toLowerCase().trim();
          document.querySelectorAll('.delivery-card').forEach((card) => {
            const searchable = [
              card.dataset.wayId || '',
              card.querySelector('strong')?.textContent || '',
              card.querySelector('small')?.textContent || '',
              card.textContent || '',
            ].join(' ').toLowerCase();

            const show = !query || searchable.includes(query);
            card.hidden = !show;
          });

          const visibleCards = [...document.querySelectorAll('.delivery-card:not([hidden])')];
          assignedEmpty.hidden = visibleCards.length > 0;
        });
      }

      document.querySelectorAll(".info-btn").forEach((button) => {
        button.addEventListener("click", () => {
          const card = button.closest(".delivery-card");
          const wayId = card?.dataset.wayId;
          if (wayId) window.location.href = "/admin/history/" + wayId;
        });
      });

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
      assignBtn.onclick = async () => {
        const selectedWays = checks
          .filter((x) => x.checked)
          .map((x) => x.closest(".order-card").dataset.wayId);
        if (!selectedBikerId || !selectedWays.length) return;

        const token = document.querySelector('input[name="_token"]').value;
        const response = await fetch(
          "/admin/bikers/" + selectedBikerId + "/ways",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": token,
              Accept: "application/json",
            },
            body: JSON.stringify({ way_ids: selectedWays }),
          },
        );
        if (response.ok) window.location.reload();
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
    <p>On way / fail notes / delivered timeline</p>
    <div class="history-event">
      24-08-2026 21:56 · Ko Ko<strong>ON WAY</strong>
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
  document
    .querySelectorAll(".sidebar-row")
    .forEach((row) =>
      row.classList.toggle(
        "active-row",
        row.getAttribute("href") === "/admin/bikers",
      ),
    );
</script>

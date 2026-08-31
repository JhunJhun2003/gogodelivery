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
                    @if ($way->status === 'failed' && $way->remark)
                      <small class="fail-note">Reason · {{ $way->remark }}</small>
                    @endif
                  </div>
                </div>
                <div class="delivery-actions" data-status="{{ $way->status }}">
                  @if ($way->status !== 'delivered')
                    <button class="status-btn onway {{ in_array($way->status, ['pending', 'failed'], true) ? '' : 'is-hidden' }}" type="button" data-status-trigger="onway">
                      On way
                    </button>
                    <button class="status-btn done {{ $way->status === 'onway' ? '' : 'is-hidden' }}" type="button" data-status-trigger="delivered">
                      done
                    </button>
                    <button class="status-btn fail {{ $way->status === 'onway' ? '' : 'is-hidden' }}" type="button" data-status-trigger="failed">
                      fail
                    </button>
                  @endif
                  <span class="status-pill status-{{ $way->status }}">{{ $way->status === 'onway' ? 'On way' : ucfirst($way->status) }}</span>
                  <button class="edit-biker-inline" type="button" data-way-id="{{ $way->id }}" data-biker-id="{{ $way->biker_id }}" aria-label="Reassign biker">Edit</button>
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
      let pendingReassignWayId = null;

      document.addEventListener("DOMContentLoaded", () => {
        const reassignSelect = document.getElementById("reassignSelect");
        if (!reassignSelect) return;
        const reassignWrapper = document.createElement("div");
        reassignWrapper.className = "custom-select";
        reassignSelect.parentNode.insertBefore(reassignWrapper, reassignSelect);
        reassignWrapper.appendChild(reassignSelect);

        const reassignToggle = document.createElement("button");
        reassignToggle.type = "button";
        reassignToggle.className = "custom-select-toggle";
        reassignToggle.textContent = reassignSelect.options[reassignSelect.selectedIndex]?.text || "Select";
        reassignWrapper.appendChild(reassignToggle);

        const reassignOptions = document.createElement("ul");
        reassignOptions.className = "custom-select-options";
        reassignOptions.setAttribute("role", "listbox");
        reassignOptions.style.position = "fixed";
        document.body.appendChild(reassignOptions);

        Array.from(reassignSelect.options).forEach((option, index) => {
          const li = document.createElement("li");
          li.className = "custom-select-option";
          li.textContent = option.text;
          li.setAttribute("role", "option");
          if (option.selected) li.classList.add("selected");
          li.addEventListener("click", () => {
            reassignSelect.selectedIndex = index;
            reassignToggle.textContent = option.text;
            reassignOptions.querySelectorAll(".custom-select-option").forEach((x) => x.classList.remove("selected"));
            li.classList.add("selected");
            reassignOptions.style.display = "none";
          });
          reassignOptions.appendChild(li);
        });

        reassignToggle.addEventListener("click", () => {
          document.querySelectorAll(".custom-select-options").forEach((el) => {
            if (el !== reassignOptions) el.style.display = "none";
          });
          const isOpen = reassignOptions.style.display === "block";
          if (isOpen) {
            reassignOptions.style.display = "none";
          } else {
            const rect = reassignToggle.getBoundingClientRect();
            reassignOptions.style.display = "block";
            reassignOptions.style.top = (rect.bottom + 4) + "px";
            reassignOptions.style.left = rect.left + "px";
            reassignOptions.style.width = rect.width + "px";
          }
        });

        document.querySelectorAll(".edit-biker-inline").forEach((button) => {
          button.addEventListener("click", () => {
            document.getElementById("doneModal").hidden = true;
            document.getElementById("failModal").hidden = true;
            document.getElementById("infoModal").hidden = true;
            pendingReassignWayId = button.dataset.wayId;
            reassignSelect.value = button.dataset.bikerId;
            reassignToggle.textContent = reassignSelect.options[reassignSelect.selectedIndex]?.text || "Select";
            reassignOptions.querySelectorAll(".custom-select-option").forEach((x, i) => {
              x.classList.toggle("selected", reassignSelect.options[i].selected);
            });
            document.getElementById("reassignModal").hidden = false;
            document.getElementById("modalBackdrop").hidden = false;
          });
        });

        document.getElementById("confirmReassign").addEventListener("click", async () => {
          if (!pendingReassignWayId) return;
          const bikerId = reassignSelect.value;
          const token = document.querySelector('input[name="_token"]')?.value;
          if (!token) return;

          const response = await fetch("/admin/ways/" + pendingReassignWayId + "/reassign", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": token,
              Accept: "application/json",
            },
            body: JSON.stringify({ biker_id: bikerId, _method: "PUT" }),
          });

          if (!response.ok) {
            alert("Unable to reassign biker.");
            return;
          }

          window.location.reload();
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

      document.addEventListener("DOMContentLoaded", () => {
        const modalBackdrop = document.getElementById("modalBackdrop");
        const doneModal = document.getElementById("doneModal");
        const failModal = document.getElementById("failModal");
        const infoModal = document.getElementById("infoModal");
        const reassignModal = document.getElementById("reassignModal");
        const confirmDone = document.getElementById("confirmDone");
        const confirmFail = document.getElementById("confirmFail");
        const failReason = document.getElementById("failReason");
        let pendingStatusButton = null;

        if (!modalBackdrop || !doneModal || !failModal || !confirmDone || !confirmFail || !failReason) {
          return;
        }

        function closeStatusModal() {
          modalBackdrop.hidden = true;
          doneModal.hidden = true;
          failModal.hidden = true;
          infoModal.hidden = true;
          reassignModal.hidden = true;
          failReason.value = "";
          pendingStatusButton = null;
          pendingReassignWayId = null;
        }

        function openStatusModal(button, status) {
          pendingStatusButton = button;
          modalBackdrop.hidden = false;
          failReason.value = "";

          if (status === "delivered") {
            doneModal.hidden = false;
            failModal.hidden = true;
          } else if (status === "failed") {
            doneModal.hidden = true;
            failModal.hidden = false;
          }
        }

        function syncStatusControls(card, status, { animateReveal = false } = {}) {
          const onwayButton = card.querySelector(".status-btn.onway");
          const doneButton = card.querySelector(".status-btn.done");
          const failButton = card.querySelector(".status-btn.fail");

          const showOnway = status === "pending" || status === "failed";
          const showAdvance = status === "onway";

          if (animateReveal && onwayButton && !showOnway) {
            onwayButton.classList.add("exiting");
            onwayButton.addEventListener("animationend", () => {
              onwayButton.classList.remove("exiting");
              onwayButton.classList.add("is-hidden");
              [doneButton, failButton].forEach((button) => {
                if (!button) return;
                button.classList.remove("is-hidden");
                button.classList.remove("reveal");
                void button.offsetWidth;
                button.classList.add("reveal");
              });
            }, { once: true });
          } else {
            if (onwayButton) {
              onwayButton.classList.toggle("is-hidden", !showOnway);
              if (animateReveal && showOnway) {
                onwayButton.classList.remove("reveal");
                void onwayButton.offsetWidth;
                onwayButton.classList.add("reveal");
              }
            }
            [doneButton, failButton].forEach((button) => {
              if (!button) return;
              button.classList.toggle("is-hidden", !showAdvance);
              if (animateReveal && !showAdvance) {
                button.classList.add("exiting");
                button.addEventListener("animationend", () => {
                  button.classList.remove("exiting");
                  button.classList.add("is-hidden");
                }, { once: true });
              }
            });
          }
        }

        async function updateWayStatus(button, status, remark = "") {
          const card = button.closest(".delivery-card");
          const wayId = card?.dataset.wayId;
          if (!wayId) return;

          const token = document.querySelector('input[name="_token"]')?.value;
          if (!token) return;

          const previousStatus = card.dataset.status || "pending";
          const response = await fetch("/admin/ways/" + wayId + "/status", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": token,
              Accept: "application/json",
            },
            body: JSON.stringify({ status, remark }),
          });

          if (!response.ok) {
            alert("Unable to update delivery status.");
            return;
          }

          if (card) {
            card.dataset.status = status;

            const pill = card.querySelector(".status-pill");
            if (pill) {
              const label = status === "onway" ? "On way" : status.charAt(0).toUpperCase() + status.slice(1);
              pill.className = "status-pill status-" + status;
              pill.textContent = label;
            }

            let failNote = card.querySelector(".fail-note");
            if (status === "failed") {
              const message = remark || "No reason provided";
              if (!failNote) {
                failNote = document.createElement("small");
                failNote.className = "fail-note";
                card.querySelector(".delivery-main > div").appendChild(failNote);
              }
              failNote.textContent = "Reason · " + message;
            } else if (failNote) {
              failNote.remove();
            }

            syncStatusControls(card, status, { animateReveal: (previousStatus === "pending" && status === "onway") || (previousStatus === "onway" && status === "failed") });
          }
        }

        document.querySelectorAll(".status-btn.onway").forEach((button) => {
          button.addEventListener("click", () => updateWayStatus(button, "onway"));
        });
        document.querySelectorAll(".status-btn.fail").forEach((button) => {
          button.addEventListener("click", () => openStatusModal(button, "failed"));
        });
        document.querySelectorAll(".status-btn.done").forEach((button) => {
          button.addEventListener("click", () => openStatusModal(button, "delivered"));
        });

        confirmDone.addEventListener("click", async () => {
          if (!pendingStatusButton) return;
          await updateWayStatus(pendingStatusButton, "delivered");
          closeStatusModal();
        });

        confirmFail.addEventListener("click", async () => {
          if (!pendingStatusButton) return;
          const remark = failReason.value.trim();
          await updateWayStatus(pendingStatusButton, "failed", remark);
          closeStatusModal();
        });

        document.querySelectorAll("[data-close-modal]").forEach((button) => {
          button.addEventListener("click", closeStatusModal);
        });

        modalBackdrop.addEventListener("click", (event) => {
          if (event.target === modalBackdrop) closeStatusModal();
        });
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
        button.addEventListener("click", async () => {
          const card = button.closest(".delivery-card");
          const wayId = card?.dataset.wayId;
          if (!wayId) return;

          document.getElementById("doneModal").hidden = true;
          document.getElementById("failModal").hidden = true;

          const res = await fetch("/admin/ways/" + wayId + "/history");
          const histories = await res.json();

          const container = document.getElementById("infoHistoryList");
          container.innerHTML = histories.length
            ? histories.map((h) => {
                const statusLabel = h.status === "onway" ? "ON_WAY" : h.status.toUpperCase();
                const remarkHtml = h.remark ? "<em>" + h.remark + "</em>" : "";
                return '<div class="history-event">' +
                  "<span>" + (h.created_at || "") + " · " + (h.changed_by || "System") + "</span>" +
                  "<strong>" + statusLabel + "</strong>" +
                  remarkHtml +
                  "</div>";
              }).join("")
            : '<div class="history-event"><span>No status history yet.</span></div>';

          document.getElementById("infoModal").hidden = false;
          document.getElementById("modalBackdrop").hidden = false;
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
        <div id="infoHistoryList"></div>
        <div class="modal-actions">
          <button class="back-button" data-close-modal type="button">Close</button>
        </div>
      </div>
      <div class="action-modal" id="reassignModal" hidden>
        <h2>Reassign biker</h2>
        <p>Choose a biker for this delivery.</p>
        <div class="input-field-group">
          <label>BIKER</label>
          <select id="reassignSelect">
            @foreach ($bikers as $biker)
              <option value="{{ $biker->id }}">{{ $biker->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="modal-actions">
          <button class="back-button" data-close-modal type="button">Cancel</button>
          <button class="ui-btn btn-navy-blue" id="confirmReassign" type="button">Save</button>
        </div>
      </div>
    </div>
  </body>
</html>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - My Ways</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="biker" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Biker · {{ $biker->name }}</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">ASSIGNED WAYS</span>
      <h1 class="main-heading">My ways</h1>
      <p class="page-intro">Deliveries assigned to you.</p>
      <div class="badge-group history-badges">
        <span class="ui-badge badge-navy">{{ $ways->count() }} total</span
        ><span class="ui-badge badge-lime">{{ $ways->count() }} assigned ways</span>
      </div>
      @if (session('way_status'))
        <p class="form-success">{{ session('way_status') }}</p>
      @endif
      <input id="waySearch" class="shop-search" type="search" placeholder="Search id, shop, address, phone..." />
      <section class="ui-card-white assigned-card">
        <div class="detail-section-heading">
          <div>
            <h2>Assigned deliveries</h2>
            <p>Update each way as you deliver it.</p>
          </div>
        </div>
        <div class="delivery-list">
          @forelse ($ways as $way)
            <article class="delivery-card" data-status="{{ $way->status }}" data-way-id="{{ $way->id }}">
              <div class="delivery-main">
                <div class="order-photo">
                  @if ($way->item_image)
                    <img src="{{ asset($way->item_image) }}" alt="Package photo" />
                  @else
                    ITEM
                  @endif
                </div>
                <div>
                  <strong>#{{ $way->id }} · {{ $way->recipient_name }}</strong>
                  <p>{{ $way->shop?->name ?? 'Shop' }} / {{ $way->phone_number }} / {{ number_format($way->amount, 0) }} / {{ number_format($way->delivery_fees, 0) }} deli</p>
                  <small>ADDRESS · {{ $way->address }}</small>
                  <small>ASSIGNED · {{ ($way->assigned_at ?? $way->date)->format('d-m-Y') }}</small>
                  @if ($way->status === 'failed' && $way->remark)
                    <small class="fail-note">{{ $way->remark }}</small>
                  @endif
                </div>
              </div>
              <div class="delivery-actions" data-status="{{ $way->status }}">
                @if ($way->status !== 'delivered')
                  <form method="POST" action="{{ route('bikers.ways.status', $way) }}" class="{{ in_array($way->status, ['pending', 'failed'], true) ? '' : 'is-hidden' }}">
                    @csrf
                    <input type="hidden" name="status" value="onway" />
                    <button class="status-btn onway" type="submit">On way</button>
                  </form>
                  <form class="fail-form {{ $way->status === 'onway' ? '' : 'is-hidden' }}" method="POST" action="{{ route('bikers.ways.status', $way) }}">
                    @csrf
                    <input type="hidden" name="status" value="failed" />
                    <input class="fail-reason" type="hidden" name="remark" />
                    <button class="status-btn fail" type="submit">fail</button>
                  </form>
                  <form method="POST" action="{{ route('bikers.ways.status', $way) }}" class="{{ $way->status === 'onway' ? '' : 'is-hidden' }}">
                    @csrf
                    <input type="hidden" name="status" value="delivered" />
                    <button class="status-btn done" type="submit">done</button>
                  </form>
                @endif
                <span class="status-pill status-{{ $way->status }}">{{ $way->status === 'onway' ? 'On way' : ucfirst($way->status) }}</span>
                <button class="info-btn" type="button">Info</button>
              </div>
            </article>
          @empty
            <p class="empty-state">No ways are assigned to you today.</p>
          @endforelse
        </div>
      </section>
    </main>
    <div class="modal-backdrop" id="doneBackdrop" hidden>
      <section class="action-modal" role="dialog" aria-modal="true" aria-labelledby="doneTitle">
        <h2 id="doneTitle">Confirm</h2>
        <p>Mark this way as delivered (done)?</p>
        <div class="modal-actions">
          <button class="back-button" id="cancelDone" type="button">Cancel</button>
          <button class="ui-btn btn-navy-blue" id="confirmDone" type="button">Confirm</button>
        </div>
      </section>
    </div>
    <div class="modal-backdrop" id="failBackdrop" hidden>
      <section class="action-modal" role="dialog" aria-modal="true" aria-labelledby="failTitle">
        <h2 id="failTitle">Failure reason</h2>
        <p>Why did this delivery fail?</p>
        <textarea id="failReason" rows="4" placeholder="Write a reason..."></textarea>
        <div class="modal-actions">
          <button class="back-button" id="cancelFail" type="button">Cancel</button>
          <button class="ui-btn btn-danger" id="confirmFail" type="button">Confirm fail</button>
        </div>
      </section>
    </div>
    <div class="modal-backdrop" id="infoBackdrop" hidden>
      <section class="action-modal info-modal" role="dialog" aria-modal="true">
        <h2>Way Info History</h2>
        <p>On way / fail notes / delivered timeline</p>
        <div id="infoHistoryList"></div>
        <div class="modal-actions">
          <button class="back-button" id="closeInfo" type="button">Close</button>
        </div>
      </section>
    </div>
    <script>
      let activeFailForm = null;
      let activeDoneForm = null;
      const doneBackdrop = document.getElementById("doneBackdrop");
      const failBackdrop = document.getElementById("failBackdrop");
      const failReason = document.getElementById("failReason");

      document.querySelectorAll(".fail-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
          event.preventDefault();
          activeFailForm = form;
          failReason.value = "";
          failBackdrop.hidden = false;
          failReason.focus();
        });
      });

      document.querySelectorAll(".delivery-actions form").forEach((form) => {
        if (form.querySelector('input[name="status"][value="delivered"]')) {
          form.addEventListener("submit", (event) => {
            event.preventDefault();
            activeDoneForm = form;
            doneBackdrop.hidden = false;
          });
        }
      });

      document.getElementById("cancelDone").onclick = () => {
        doneBackdrop.hidden = true;
        activeDoneForm = null;
      };
      document.getElementById("confirmDone").onclick = () => {
        if (activeDoneForm) activeDoneForm.submit();
      };
      document.getElementById("cancelFail").onclick = () => {
        failBackdrop.hidden = true;
        activeFailForm = null;
      };
      document.getElementById("confirmFail").onclick = () => {
        if (!activeFailForm) return;
        activeFailForm.querySelector(".fail-reason").value = failReason.value.trim();
        activeFailForm.submit();
      };

      [doneBackdrop, failBackdrop].forEach((backdrop) => {
        backdrop.addEventListener("click", (event) => {
          if (event.target === backdrop) backdrop.hidden = true;
        });
      });

      const waySearch = document.getElementById("waySearch");
      const cards = [...document.querySelectorAll(".delivery-card")];
      waySearch.addEventListener("input", () => {
        const q = waySearch.value.toLowerCase();
        cards.forEach((card) => {
          const text = card.textContent.toLowerCase();
          card.style.display = text.includes(q) ? "" : "none";
        });
      });

      const infoBackdrop = document.getElementById("infoBackdrop");
      document.querySelectorAll(".info-btn").forEach((btn) => {
        btn.addEventListener("click", async () => {
          const card = btn.closest(".delivery-card");
          const wayId = card?.dataset.wayId;
          if (!wayId) return;
          const res = await fetch("/bikers/ways/" + wayId + "/history");
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
          infoBackdrop.hidden = false;
        });
      });
      document.getElementById("closeInfo").onclick = () => (infoBackdrop.hidden = true);
      infoBackdrop.addEventListener("click", (e) => {
        if (e.target === infoBackdrop) infoBackdrop.hidden = true;
      });
    </script>
  </body>
</html>

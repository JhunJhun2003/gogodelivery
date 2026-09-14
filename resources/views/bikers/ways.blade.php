<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - My Ways</title>
    <link rel="icon" href="/assets/carlogo.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=20260907193127" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
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
                <a class="info-btn" href="{{ route('bikers.history.detail', $way) }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Info</a>
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
          <button class="ui-btn btn-navy-blue" id="confirmDone" type="button">Continue</button>
        </div>
      </section>
    </div>
    <div class="modal-backdrop" id="signatureBackdrop" hidden>
      <section class="action-modal signature-modal" role="dialog" aria-modal="true" aria-labelledby="signatureTitle">
        <h2 id="signatureTitle">Customer signature</h2>
        <p>Please ask the customer to sign, then press OK to finish this way.</p>
        <canvas id="signatureCanvas" class="signature-canvas" width="780" height="300" aria-label="Customer signature drawing area"></canvas>
        <input id="signatureInput" type="hidden" name="signature" />
        <div class="modal-actions">
          <button class="back-button" id="backToDone" type="button">Back</button>
          <button class="back-button" id="clearSignature" type="button">Clear</button>
          <button class="ui-btn btn-navy-blue" id="confirmSignature" type="button">OK</button>
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
      const signatureBackdrop = document.getElementById("signatureBackdrop");
      const failBackdrop = document.getElementById("failBackdrop");
      const failReason = document.getElementById("failReason");
      const signatureCanvas = document.getElementById("signatureCanvas");
      const signatureInput = document.getElementById("signatureInput");
      const signatureContext = signatureCanvas.getContext("2d");
      let signatureDrawing = false;
      let signatureHasInk = false;

      const clearSignatureCanvas = () => {
        signatureContext.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        signatureContext.beginPath();
        signatureHasInk = false;
      };

      const signaturePoint = (event) => {
        const rect = signatureCanvas.getBoundingClientRect();
        return {
          x: (event.clientX - rect.left) * (signatureCanvas.width / rect.width),
          y: (event.clientY - rect.top) * (signatureCanvas.height / rect.height),
        };
      };

      signatureCanvas.addEventListener("pointerdown", (event) => {
        signatureDrawing = true;
        signatureCanvas.setPointerCapture(event.pointerId);
        const point = signaturePoint(event);
        signatureContext.beginPath();
        signatureContext.moveTo(point.x, point.y);
        signatureHasInk = true;
      });
      signatureCanvas.addEventListener("pointermove", (event) => {
        if (!signatureDrawing) return;
        const point = signaturePoint(event);
        signatureContext.lineTo(point.x, point.y);
        signatureContext.stroke();
      });
      ["pointerup", "pointercancel"].forEach((eventName) => {
        signatureCanvas.addEventListener(eventName, () => { signatureDrawing = false; });
      });
      signatureContext.lineWidth = 4;
      signatureContext.lineCap = "round";
      signatureContext.lineJoin = "round";
      signatureContext.strokeStyle = "#0f172a";

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
        if (!activeDoneForm) return;
        doneBackdrop.hidden = true;
        clearSignatureCanvas();
        signatureBackdrop.hidden = false;
        signatureCanvas.focus();
      };
      document.getElementById("backToDone").onclick = () => {
        signatureBackdrop.hidden = true;
        doneBackdrop.hidden = false;
      };
      document.getElementById("clearSignature").onclick = clearSignatureCanvas;
      document.getElementById("confirmSignature").onclick = () => {
        if (!activeDoneForm || !signatureHasInk) {
          window.alert("Please add the customer signature before pressing OK.");
          return;
        }
        signatureInput.value = signatureCanvas.toDataURL("image/png");
        activeDoneForm.appendChild(signatureInput);
        signatureBackdrop.hidden = true;
        activeDoneForm.submit();
        activeDoneForm = null;
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

      [doneBackdrop, signatureBackdrop, failBackdrop].forEach((backdrop) => {
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
      document.getElementById("closeInfo").onclick = () => (infoBackdrop.hidden = true);
      infoBackdrop.addEventListener("click", (e) => {
        if (e.target === infoBackdrop) infoBackdrop.hidden = true;
      });
    </script>
  </body>
</html>

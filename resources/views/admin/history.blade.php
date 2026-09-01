<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Deli - History</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="admin" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Administrator · ADMI...</span
        ><button class="hamburger-icon-btn" id="menu" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <div class="shop-hero-layout">
        <div class="shop-hero-heading">
          <span class="section-tag">OPERATIONS</span>
          <h1 class="main-heading">History</h1>
        </div>
        <p class="page-intro">
          Review all past deliveries. Use the filters below to narrow results.
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
      <form class="ui-card-white history-filter-card" method="GET" action="{{ route('admin.history') }}">
        <h2>Filter orders</h2>
        <div class="history-form-grid">
          <div class="input-field-group full-field">
            <label for="historySearch">SEARCH id, SHOP, CUSTOMER NAME, PHONE OR ADDRESS</label
            ><input id="historySearch" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search online shop, customer name, phone, or address..." />
          </div>
          <div class="input-field-group">
            <label>ONLINE SHOP</label
            ><select name="shop_id">
              <option value="">Select</option>
              @foreach ($shops as $shop)
                <option value="{{ $shop->id }}" @selected(($filters['shop_id'] ?? '') == $shop->id)>{{ $shop->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
            <label>BIKER</label
            ><select name="biker_id">
              <option value="">Choose</option>
              @foreach ($bikers as $biker)
                <option value="{{ $biker->id }}" @selected(($filters['biker_id'] ?? '') == $biker->id)>{{ $biker->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
            <label>STATUS</label
            ><select name="status">
              <option value="">Status</option>
              @foreach (['pending' => 'Pending', 'onway' => 'On way', 'delivered' => 'Delivered', 'failed' => 'Failed'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="input-field-group">
            <label>CUST. NAME</label
            ><input name="customer_name" type="text" value="{{ $filters['customer_name'] ?? '' }}" placeholder="cust. name..." />
          </div>
          <div class="input-field-group">
            <label>CUST. PHONE</label
            ><input name="customer_phone" type="tel" value="{{ $filters['customer_phone'] ?? '' }}" placeholder="cust. ph..." />
          </div>
          {{-- <div class="input-field-group">
            <label>MIN AMT.</label
            ><input name="min_amount" type="number" min="0" value="{{ $filters['min_amount'] ?? '' }}" placeholder="Min Amt" />
          </div>
          <div class="input-field-group">
            <label>MAX AMT.</label
            ><input name="max_amount" type="number" min="0" value="{{ $filters['max_amount'] ?? '' }}" placeholder="Max Amt" />
          </div> --}}
          <div class="input-field-group full-field">
            <label>DATE</label>
            <div class="custom-date-picker">
              <input id="historyDate" name="date" type="date" value="{{ $filters['date'] ?? '' }}" /><button
                class="custom-date-trigger"
                type="button"
              >
                {{ isset($filters['date']) ? \Carbon\Carbon::parse($filters['date'])->format('d/m/y') : 'dd/mm/yy' }}
              </button>
              <div class="custom-calendar"></div>
            </div>
          </div>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
          <button class="ui-btn btn-navy-blue history-save" type="submit">
            Search
          </button>
          {{-- <a class="ui-btn btn-white" href="{{ route('admin.history.export', request()->query()) }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
            Export Excel
          </a> --}}
          <a class="ui-btn btn-white" href="{{ route('admin.history.export.pdf', request()->query()) }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
            Export PDF
          </a>
        </div>
      </form>
      <div class="badge-group history-badges">
        <span class="ui-badge badge-navy">{{ today()->format('d-m-Y') }}</span
        ><span class="ui-badge badge-lime">Total Ways · {{ $totalWays }}</span>
      </div>
      <section class="ui-card-white history-list-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">ORDERS</span>
            <h2>All orders</h2>
          </div>
          <span class="ui-badge badge-navy">{{ $ways->total() }} orders</span>
        </div>
        <div class="history-table-wrap" style="overflow-x:auto;">
          <table class="workspace-table history-table" style="min-width: 1500px;">
            <thead>
              <tr>
                <th>No</th>
                <th>Shop</th>
                <th>Date</th>
                <th>Image</th>
                <th>Amount</th>
                <th>Deli Fees</th>
                <th>Customer Detail</th>
                <th>Biker</th>
                <th>Status</th>
                <th>Deli Date</th>
                <th>Remark</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($ways as $index => $way)
                <tr>
                  <td>{{ str_pad($ways->firstItem() + $index, 2, '0', STR_PAD_LEFT) }}</td>
                  <td>{{ $way->shop?->name ?? 'N/A' }}</td>
                  <td>{{ $way->date->format('d-m-Y') }}</td>
                  <td>
                    @if ($way->item_image)
                      <img src="{{ asset($way->item_image) }}" alt="Package image" style="display:block;width:54px;height:54px;object-fit:cover;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;" />
                    @else
                      <span style="display:grid;place-items:center;width:54px;height:54px;border:1px solid #e2e8f0;border-radius:8px;background:#f1f5f9;color:#64748b;font-size:10px;">No</span>
                    @endif
                  </td>
                  <td>{{ number_format($way->amount, 2) }}</td>
                  <td>{{ number_format($way->delivery_fees, 2) }}</td>
                  <td>
                    <div>{{ $way->recipient_name }}</div>
                    <small>{{ $way->address }}</small><br>
                    <small>{{ $way->phone_number }}</small>
                  </td>
                  <td>{{ $way->biker?->name ?? 'Unassigned' }}</td>
                  <td><span class="status-pill status-{{ $way->status }}">{{ $way->status === 'onway' ? 'On way' : ucfirst($way->status) }}</span></td>
                  <td>{{ $way->assigned_at ? $way->assigned_at->format('d-m-Y') : ($way->date->format('d-m-Y')) }}</td>
                  <td>{{ $way->remark ?: '—' }}</td>
                  <td>
                    <a class="table-action" href="{{ route('admin.history.detail', $way) }}">View</a>
                    <a class="table-action" href="{{ route('admin.ways.edit', $way) }}">Edit</a>
                    <button type="button" class="table-action delete-btn" data-id="{{ $way->id }}" data-url="{{ route('admin.ways.destroy', $way) }}">Delete</button>
                  </td>
                </tr>
              @empty
                <tr><td class="no-data-msg" colspan="12">No orders found.</td></tr>
              @endforelse
            </tbody>
            @if ($ways->isNotEmpty())
              <tfoot>
                <tr style="background:#f8fafc;font-weight:700;">
                  <td colspan="4" style="text-align:left;padding-left:12px;">Total</td>
                  <td style="text-align:right;">{{ number_format($ways->sum('amount'), 2) }}</td>
                  <td style="text-align:right;">{{ number_format($ways->sum('delivery_fees'), 2) }}</td>
                  <td colspan="6"></td>
                </tr>
              </tfoot>
            @endif
          </table>
        </div>
        @if ($ways->hasPages())
          <div style="padding:16px 24px;display:flex;justify-content:center;gap:6px;flex-wrap:wrap;">
            @if ($ways->onFirstPage())
              <span style="padding:6px 12px;border-radius:6px;background:#f1f5f9;color:#94a3b8;font-size:13px;">Previous</span>
            @else
              <a href="{{ $ways->previousPageUrl() }}" style="padding:6px 12px;border-radius:6px;background:#e2e8f0;color:#0f172a;text-decoration:none;font-size:13px;">Previous</a>
            @endif
            @foreach ($ways->getUrlRange(max(1, $ways->currentPage() - 3), min($ways->lastPage(), $ways->currentPage() + 3)) as $page => $url)
              @if ($page == $ways->currentPage())
                <span style="padding:6px 12px;border-radius:6px;background:#0f172a;color:#fff;font-size:13px;font-weight:700;">{{ $page }}</span>
              @else
                <a href="{{ $url }}" style="padding:6px 12px;border-radius:6px;background:#e2e8f0;color:#0f172a;text-decoration:none;font-size:13px;">{{ $page }}</a>
              @endif
            @endforeach
            @if ($ways->currentPage() >= $ways->lastPage())
              <span style="padding:6px 12px;border-radius:6px;background:#f1f5f9;color:#94a3b8;font-size:13px;">Next</span>
            @else
              <a href="{{ $ways->nextPageUrl() }}" style="padding:6px 12px;border-radius:6px;background:#e2e8f0;color:#0f172a;text-decoration:none;font-size:13px;">Next</a>
            @endif
          </div>
        @endif
      </section>
    </main>
    <div class="modal-backdrop" id="deleteBackdrop" hidden>
      <section class="action-modal" role="dialog" aria-modal="true">
        <h2>Delete this order?</h2>
        <p>This action cannot be undone.</p>
        <div class="modal-actions">
          <button class="back-button" id="cancelDelete" type="button">Cancel</button>
          <button class="ui-btn btn-danger" id="confirmDelete" type="button">Delete</button>
        </div>
      </section>
    </div>
    <script>
      const dateInput = document.getElementById("historyDate");
      const datePicker = document.querySelector(".custom-date-picker");
      const dateTrigger = datePicker?.querySelector(".custom-date-trigger");
      const calendar = datePicker?.querySelector(".custom-calendar");

      if (dateInput && datePicker && dateTrigger && calendar) {
        let viewDate = new Date();
        let picked = dateInput.value || "";

        const pad = (n) => String(n).padStart(2, "0");

        function drawCalendar() {
          const y = viewDate.getFullYear();
          const m = viewDate.getMonth();
          const first = new Date(y, m, 1).getDay();
          const days = new Date(y, m + 1, 0).getDate();

          calendar.innerHTML =
            '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' +
            viewDate.toLocaleString("en", { month: "long", year: "numeric" }) +
            '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" id="days"></div>';

          const grid = document.getElementById("days");
          for (let i = 0; i < first; i++) {
            const b = document.createElement("button");
            b.disabled = true;
            grid.append(b);
          }

          for (let d = 1; d <= days; d++) {
            const b = document.createElement("button");
            b.type = "button";
            b.textContent = d;
            const val = y + "-" + pad(m + 1) + "-" + pad(d);
            if (val === picked) b.className = "selected";
            b.onclick = () => {
              picked = val;
              dateInput.value = val;
              dateTrigger.textContent =
                pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2);
              datePicker.classList.remove("open");
              drawCalendar();
            };
            grid.append(b);
          }
        }

        dateTrigger.onclick = () => {
          datePicker.classList.toggle("open");
          drawCalendar();
        };

        calendar.onclick = (e) => {
          if (e.target.dataset.step) {
            viewDate.setMonth(viewDate.getMonth() + Number(e.target.dataset.step));
            drawCalendar();
          }
        };

        document.addEventListener("click", (e) => {
          if (!datePicker.contains(e.target)) datePicker.classList.remove("open");
        });

        if (picked) {
          const [y, m, d] = picked.split('-').map(Number);
          if (y && m && d) {
            dateTrigger.textContent = pad(d) + "/" + pad(m) + "/" + String(y).slice(-2);
          }
        }

        drawCalendar();
      }

      let pendingDeleteUrl = null;
      const deleteBackdrop = document.getElementById("deleteBackdrop");
      document.querySelectorAll(".delete-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
          pendingDeleteUrl = btn.dataset.url;
          deleteBackdrop.hidden = false;
        });
      });
      document.getElementById("cancelDelete").onclick = () => {
        deleteBackdrop.hidden = true;
        pendingDeleteUrl = null;
      };
      deleteBackdrop.addEventListener("click", (e) => {
        if (e.target === deleteBackdrop) {
          deleteBackdrop.hidden = true;
          pendingDeleteUrl = null;
        }
      });
      document.getElementById("confirmDelete").addEventListener("click", async () => {
        if (!pendingDeleteUrl) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
          const res = await fetch(pendingDeleteUrl, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": token, "X-HTTP-Method-Override": "DELETE", Accept: "application/json" },
            body: new URLSearchParams({ _method: "DELETE", _token: token }),
            credentials: "same-origin",
          });
        if (res.ok || res.status === 204 || res.status === 302) {
          const row = document.querySelector('.delete-btn[data-url="' + pendingDeleteUrl + '"]')?.closest("tr");
          if (row) {
            row.style.transition = "opacity .3s";
            row.style.opacity = "0";
            setTimeout(() => row.remove(), 300);
          }
          deleteBackdrop.hidden = true;
          pendingDeleteUrl = null;
        }
      });
    </script>
  </body>
</html>
<script>
  document.querySelectorAll(".history-filter-card select").forEach((select) => {
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

    const options = document.createElement("ul");
    options.className = "custom-select-options";
    options.setAttribute("role", "listbox");

    Array.from(select.options).forEach((option, index) => {
      const item = document.createElement("li");
      item.className = "custom-select-option";
      item.textContent = option.text;
      item.setAttribute("role", "option");
      if (option.selected) item.classList.add("selected");

      item.onclick = () => {
        select.selectedIndex = index;
        select.value = option.value;
        toggle.textContent = option.text;
        options.querySelectorAll(".custom-select-option").forEach((x) => x.classList.remove("selected"));
        item.classList.add("selected");
        wrapper.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
        select.dispatchEvent(new Event("change", { bubbles: true }));
      };

      options.appendChild(item);
    });

    wrapper.appendChild(options);
    toggle.onclick = () => {
      document.querySelectorAll(".custom-select.open").forEach((x) => {
        if (x !== wrapper) x.classList.remove("open");
      });
      const open = wrapper.classList.toggle("open");
      toggle.setAttribute("aria-expanded", String(open));
    };
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".custom-select")) {
      document.querySelectorAll(".custom-select.open").forEach((x) => x.classList.remove("open"));
    }
  });
</script>

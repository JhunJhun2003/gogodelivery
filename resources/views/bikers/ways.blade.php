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
      <section class="ui-card-white assigned-card">
        <div class="detail-section-heading">
          <div>
            <h2>Assigned deliveries</h2>
            <p>Update each way as you deliver it.</p>
          </div>
        </div>
        <div class="delivery-list">
          @forelse ($ways as $way)
            <article class="delivery-card" data-status="{{ $way->status }}">
              <div class="delivery-main">
                <div class="order-photo">
                  @if ($way->item_image)
                    <img src="/{{ $way->item_image }}" alt="Package photo" />
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
              <div class="delivery-actions">
                @if ($way->status !== 'delivered')
                  <form method="POST" action="{{ route('bikers.ways.status', $way) }}">
                    @csrf
                    <input type="hidden" name="status" value="onway" />
                    <button class="status-btn onway" type="submit" {{ $way->status === 'onway' ? 'disabled' : '' }}>onway</button>
                  </form>
                  <form class="fail-form" method="POST" action="{{ route('bikers.ways.status', $way) }}">
                    @csrf
                    <input type="hidden" name="status" value="failed" />
                    <input class="fail-reason" type="hidden" name="remark" />
                    <button class="status-btn fail" type="submit">fail</button>
                  </form>
                  <form method="POST" action="{{ route('bikers.ways.status', $way) }}">
                    @csrf
                    <input type="hidden" name="status" value="delivered" />
                    <button class="status-btn done" type="submit">done</button>
                  </form>
                @endif
                <span class="status-pill status-{{ $way->status }}">{{ ucfirst($way->status) }}</span>
              </div>
            </article>
          @empty
            <p class="empty-state">No ways are assigned to you today.</p>
          @endforelse
        </div>
      </section>
    </main>
    <script>
      document.querySelectorAll(".fail-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
          const reason = window.prompt("Why did this delivery fail?")
          if (reason === null) {
            event.preventDefault();
            return;
          }
          form.querySelector(".fail-reason").value = reason;
        });
      });
    </script>
  </body>
</html>

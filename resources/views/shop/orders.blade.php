<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Shop Orders</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="shop" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Shop · {{ $shop->name }}</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">{{ strtoupper($shop->name) }}</span>
      <h1 class="main-heading">Active orders</h1>
      <p class="page-intro">
        Orders you have made that are still being processed.
      </p>
      <section class="ui-card-white shop-orders-card">
        <div class="shop-orders-heading">
          <h2>Current orders</h2>
          <span class="ui-badge badge-lime" id="orderCount">{{ $orders->count() }} active</span>
        </div>
        <form method="GET" action="{{ route('shop.orders') }}">
          <input
            class="shop-search"
            id="orderSearch"
            type="search"
            name="search"
            value="{{ $search }}"
            placeholder="Search order, customer, address..."
            aria-label="Search orders"
          />
        </form>
        <div id="orderList">
          @forelse ($orders as $order)
            <article class="ui-card-white order-card shop-order-card">
              <label><strong>Order #{{ $order->id }}</strong></label>
              <div class="order-content">
                <div class="order-photo">
                  @if ($order->item_image)
                    <img src="/{{ $order->item_image }}" alt="Order item" />
                  @else
                    ITEM
                  @endif
                </div>
                <div>
                  <strong>{{ $order->recipient_name }}</strong>
                  <p>{{ $order->phone_number }} / {{ number_format($order->amount, 0) }} / {{ number_format($order->delivery_fees, 0) }} deli</p>
                  <small>Status: {{ strtoupper($order->status) }} · Biker: {{ $order->biker?->name ?? 'Not assigned' }}</small>
                </div>
              </div>
              <label class="order-address">ADDRESS<input value="{{ $order->address }}" readonly /></label>
            </article>
          @empty
            <p class="shop-orders-empty">No active orders.</p>
          @endforelse
        </div>
      </section>
    </main>
    <script>
      const search = document.getElementById("orderSearch"),
        rows = [...document.querySelectorAll(".order-card")];
      search.oninput = () => {
        const q = search.value.trim().toLowerCase();
        rows.forEach(
            (row) => {
              const values = [...row.querySelectorAll("input")]
                .map((input) => input.value)
                .join(" ");
              row.hidden = !(row.textContent + " " + values)
                .toLowerCase()
                .includes(q);
            },
        );
        document.getElementById("orderCount").textContent =
          rows.filter((row) => !row.hidden).length + " active";
      };
      search.form.addEventListener("submit", () => {
        search.value = search.value.trim();
      });
    </script>
  </body>
</html>

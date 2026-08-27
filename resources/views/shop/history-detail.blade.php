<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Order Detail</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="shop" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Shop · {{ $way->shop?->name ?? 'Shop' }}</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <a class="back-link" href="{{ route('shop.history') }}">← Back to history</a
      ><span class="section-tag">ORDER · {{ $way->id }}</span>
      <h1 class="main-heading">Order detail</h1>
      <section class="ui-card-white history-detail-card">
        <div class="history-card-heading">
          <div>
            <span class="section-tag">{{ $way->date->format('d-m-Y') }}</span>
            <h2>{{ $way->recipient_name }}</h2>
          </div>
          <span class="status-pill status-{{ $way->status }}">{{ ucfirst($way->status) }}</span>
        </div>
        <div class="detail-layout">
          <div class="detail-photo">
            @if ($way->item_image)
              <img src="/{{ $way->item_image }}" alt="Order photo" />
            @else
              No photo
            @endif
          </div>
          <div class="detail-grid">
            <div><span>Customer name</span><strong>{{ $way->recipient_name }}</strong></div>
            <div>
              <span>Customer phone</span><strong>{{ $way->phone_number }}</strong>
            </div>
            <div><span>Order status</span><strong>{{ ucfirst($way->status) }}</strong></div>
            <div><span>Biker</span><strong>{{ $way->biker?->name ?? 'Not assigned' }}</strong></div>
            <div><span>Order amount</span><strong>{{ number_format($way->amount, 2) }} MMK</strong></div>
            <div><span>Delivery fee</span><strong>{{ number_format($way->delivery_fees, 2) }} MMK</strong></div>
            <div class="detail-wide">
              <span>Customer address</span><strong>{{ $way->address }}</strong>
            </div>
          </div>
        </div>
        <div class="history-event">
          {{ $way->created_at->format('d-m-Y H:i') }} · {{ $way->shop?->name ?? 'Shop' }}<strong>ORDER CREATED</strong>
        </div>
      </section>
    </main>
  </body>
</html>

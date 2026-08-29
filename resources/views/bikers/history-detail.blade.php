<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Deli - Way History Detail</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
  <script src="/js/sidebar.js?v=1787686291" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="biker" class="app-bg history-screen">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right"><span class="user-role">Biker · {{ $way->biker?->name ?? 'Biker' }}</span><button class="hamburger-icon-btn" type="button">☰</button></div>
    </header>
    <main class="workspace-body">
      <a class="back-link" href="/bikers/history">← Back to history</a>
      <span class="section-tag">WAY · {{ $way->id }}</span>
      <h1 class="main-heading">Way history detail</h1>
      <section class="ui-card-white history-detail-card">
        <div class="history-card-heading"><div><span class="section-tag">{{ $way->date->format('d-m-Y') }}</span><h2>{{ $way->recipient_name }}</h2></div><span class="status-pill status-{{ $way->status }}">{{ $way->status === 'onway' ? 'On way' : ucfirst($way->status) }}</span></div>
        <div class="detail-layout">
          <div class="detail-photo">
            @if ($way->item_image)
              <img src="{{ asset($way->item_image) }}" alt="Package photo" />
            @else
              <span>ITEM</span>
            @endif
          </div>
          <div class="detail-grid">
            <div><span>Customer name</span><strong>{{ $way->recipient_name }}</strong></div>
            <div><span>Online shop</span><strong>{{ $way->shop?->name ?? 'Shop' }}</strong></div>
            <div><span>Biker</span><strong>{{ $way->biker?->name ?? 'Biker' }}</strong></div>
            <div><span>Status</span><strong>{{ $way->status === 'onway' ? 'On way' : ucfirst($way->status) }}</strong></div>
            <div><span>Customer phone</span><strong>{{ $way->phone_number }}</strong></div>
            <div><span>Amount</span><strong>{{ number_format($way->amount, 0) }} MMK</strong></div>
            <div><span>Delivery fee</span><strong>{{ number_format($way->delivery_fees, 0) }} MMK</strong></div>
            <div class="detail-wide"><span>Customer address</span><strong>{{ $way->address }}</strong></div>
            @if ($way->remark)<div class="detail-wide"><span>Remark</span><strong>{{ $way->remark }}</strong></div>@endif
          </div>
        </div>
      </section>
    </main>
    <script>
    </script>
  </body>
</html>

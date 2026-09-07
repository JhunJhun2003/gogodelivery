<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Deli - Shop Users</title>
    <link rel="icon" href="/assets/logo-nobg.png?v=1787685826" />
    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />
    <script src="/js/sidebar.js?v=20260907193127" defer></script>
  </head>
  <body data-role="shop" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Shop · {{ auth()->user()->name }}</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">ACCESS</span>
      <h1 class="main-heading">Users</h1>
      <section class="ui-card-white">
        <div class="directory-list">
          @forelse ($users as $user)
            <div class="directory-item">
              <div>
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->username }} · {{ ucfirst($user->role) }}{{ $user->biker ? ' · ' . $user->biker->name : '' }}</span>
              </div>
            </div>
          @empty
            <p class="no-data-msg">No users found.</p>
          @endforelse
        </div>
      </section>
    </main>
  </body>
</html>

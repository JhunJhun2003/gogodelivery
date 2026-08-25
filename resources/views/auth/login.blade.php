<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DELI | Login</title>
    <link rel="stylesheet" href="/css/global.css" />
    <link rel="stylesheet" href="/css/components.css" />
    <link rel="stylesheet" href="/css/screens.css" />
    <script src="/js/sidebar.js" defer></script>
    <script src="/js/history-controls.js" defer></script>
  </head>
  <body class="login-bg">
    <div class="login-wrapper">
      <div class="logo-area">
        <h1 class="deli-logo">DELI</h1>
        <p class="deli-subtitle">DELIVERY CONTROL</p>
        <p class="deli-desc">
          Route shops, assign bikers, and close the day — built for operators
          who move fast.
        </p>
      </div>

      <div class="ui-card-white login-box">
        <h2 class="card-headline">Welcome back</h2>
        <p class="card-subtext">Sign in to your workspace</p>

        @if ($errors->any())
          <p class="login-error">{{ $errors->first() }}</p>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST">
          @csrf
          <div class="input-field">
            <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="username" required />
          </div>
          <div class="input-field">
            <input type="password" name="password" placeholder="Password" autocomplete="current-password" required />
          </div>
          <button type="submit" class="ui-btn btn-lime-green">Enter workspace</button>
        </form>
        <p class="bottom-hint">Default: admin / admin123</p>
      </div>
    </div>
  </body>
</html>

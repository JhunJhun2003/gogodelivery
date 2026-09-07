<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>DELI | Maintenance</title>

    <link rel="icon" href="/assets/logo-nobg.png" />

    <link rel="stylesheet" href="/css/global.css?v=1787684056" />
    <link rel="stylesheet" href="/css/components.css?v=1787684056" />
    <link rel="stylesheet" href="/css/screens.css?v=1787684056" />

    <style>
      * {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        min-height: 100vh;
        font-family: Arial, Helvetica, sans-serif;
        background: #f5f7f2;
      }

      .maintenance-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px 20px;
      }

      .maintenance-box {
        width: 100%;
        max-width: 620px;
        background: #ffffff;
        border-radius: 24px;
        padding: 50px 40px;
        text-align: center;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
      }

      .maintenance-logo {
        width: 150px;
        height: 150px;
        object-fit: contain;
        margin-bottom: 25px;
      }

      .maintenance-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: #eef7d7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
      }

      .maintenance-title {
        margin: 0 0 12px;
        font-size: 32px;
        font-weight: 700;
        color: #222222;
      }

      .maintenance-subtitle {
        margin: 0 auto;
        max-width: 480px;
        font-size: 17px;
        line-height: 1.6;
        color: #6b6b6b;
      }

      .maintenance-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 28px;
        padding: 10px 18px;
        border-radius: 30px;
        background: #f1f8df;
        color: #5d761f;
        font-size: 14px;
        font-weight: 600;
      }

      .status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #8bb52d;
      }

      .maintenance-divider {
        width: 60px;
        height: 4px;
        margin: 30px auto;
        border-radius: 10px;
        background: #8bb52d;
      }

      .maintenance-footer {
        margin-top: 30px;
        font-size: 13px;
        color: #999999;
      }

      @media (max-width: 600px) {
        .maintenance-box {
          padding: 40px 25px;
          border-radius: 18px;
        }

        .maintenance-logo {
          width: 120px;
          height: 120px;
        }

        .maintenance-title {
          font-size: 26px;
        }

        .maintenance-subtitle {
          font-size: 15px;
        }
      }
    </style>
  </head>

  <body>
    <div class="maintenance-wrapper">

      <div class="maintenance-box">

        <!-- Logo -->
        <img
          src="/assets/logo-nobg.jpg"
          alt="DELI Logo"
          class="maintenance-logo"
        />
        <br />
        <img
          src="/assets/logo-nobg.png"
          alt="DELI Logo"
          class="maintenance-logo"
        />

        <!-- Maintenance Icon -->
        <div class="maintenance-icon">
          🔧
        </div>

        <!-- Main Message -->
        <h1 class="maintenance-title">
          We'll Be Right Back
        </h1>

        <p class="maintenance-subtitle">
          DELI is currently undergoing scheduled maintenance.
          We're working to improve your experience and will be
          back online shortly.
        </p>

        <!-- Status -->
        <div class="maintenance-status">
          <span class="status-dot"></span>
          Maintenance in progress
        </div>

        <div class="maintenance-divider"></div>

        <p class="maintenance-footer">
          Thank you for your patience.
        </p>

      </div>

    </div>
  </body>
</html>
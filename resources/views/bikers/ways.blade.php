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
  <script src="/js/sidebar.js?v=1787684056" defer></script><script src="/js/history-controls.js?v=1787684056" defer></script></head>
  <body data-role="biker" class="app-bg">
    <header class="top-app-bar">
      <div class="bar-logo">DELI</div>
      <div class="bar-right">
        <span class="user-role">Biker · Ko Ko</span
        ><button class="hamburger-icon-btn" type="button">☰</button>
      </div>
    </header>
    <main class="workspace-body">
      <span class="section-tag">TODAY</span>
      <h1 class="main-heading">My ways</h1>
      <p class="page-intro">Deliveries assigned to you today.</p>
      <div class="badge-group history-badges">
        <span class="ui-badge badge-navy">24-08-2026</span
        ><span class="ui-badge badge-lime">6 assigned ways</span>
      </div>
      <section class="ui-card-white assigned-card">
        <div class="detail-section-heading">
          <div>
            <h2>Assigned deliveries</h2>
            <p>Update each way as you deliver it.</p>
          </div>
        </div>
        <div class="delivery-list">
          <article class="delivery-card" data-status="active">
            <div class="delivery-main">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>#l4adu · Jejey / Bahan</strong>
                <p>ABC Store / 1234567 / 60,000 / 5,000 deli</p>
                <small>ADDRESS · Bahan</small>
              </div>
            </div>
            <div class="delivery-actions">
              <button class="status-btn onway" type="button">onway</button
              ><button class="status-btn fail" type="button">fail</button
              ><button class="info-btn" type="button">Info</button>
            </div>
          </article>
          <article class="delivery-card" data-status="active">
            <div class="delivery-main">
              <div class="order-photo">ITEM</div>
              <div>
                <strong>#jo6a4h · မောင် / ကျောက်တံတား</strong>
                <p>M Store / 09665690997 / 26,500 / 3,500 deli</p>
                <small>ADDRESS · Downtown</small>
              </div>
            </div>
            <div class="delivery-actions">
              <button class="status-btn onway" type="button">onway</button
              ><button class="status-btn fail" type="button">fail</button
              ><button class="info-btn" type="button">Info</button>
            </div>
          </article>
        </div>
      </section>
    </main>
    <div class="modal-backdrop" id="doneBackdrop" hidden>
      <section class="action-modal" role="dialog" aria-modal="true">
        <h2>Mark way as done?</h2>
        <p>Confirm that this delivery has been completed.</p>
        <div class="modal-actions">
          <button class="back-button" id="cancelDone" type="button">
            Cancel</button
          ><button class="ui-btn btn-navy-blue" id="confirmDone" type="button">
            Confirm done
          </button>
        </div>
      </section>
    </div>
    <div class="modal-backdrop" id="failBackdrop" hidden>
      <section class="action-modal" role="dialog" aria-modal="true">
        <h2>Why did this delivery fail?</h2>
        <textarea
          id="failReason"
          rows="4"
          placeholder="Write fail reason..."
        ></textarea>
        <div class="modal-actions">
          <button class="back-button" id="cancelFail" type="button">
            Cancel</button
          ><button class="ui-btn btn-danger" id="confirmFail" type="button">
            Confirm fail
          </button>
        </div>
      </section>
    </div>
    <div class="modal-backdrop" id="infoBackdrop" hidden><section class="action-modal info-modal" role="dialog" aria-modal="true"><h2>Way info history</h2><p>Onway / fail notes / delivered timeline</p><div class="history-event">24-08-2026 21:56 · Ko Ko<strong>ON_WAY</strong></div><div class="history-event">24-08-2026 21:57 · Ko Ko<strong>FAILED</strong><em>Customer unavailable</em></div><div class="history-event">24-08-2026 22:10 · Ko Ko<strong>DELIVERED</strong></div><div class="modal-actions"><button class="back-button" id="closeInfo" type="button">Close</button></div></section></div>
    <script>
      let activeDelivery = null;
      const doneBackdrop = document.getElementById("doneBackdrop");
      const failBackdrop = document.getElementById("failBackdrop");
      const infoBackdrop = document.getElementById("infoBackdrop");
      document.querySelectorAll(".delivery-card").forEach((card) => {
        const onway = card.querySelector(".onway"),
          fail = card.querySelector(".fail"), info = card.querySelector(".info-btn");
        onway.onclick = () => {
          activeDelivery = card;
          onway.hidden = true;
          fail.hidden = false;
          fail.classList.remove("failed");
          fail.textContent = "fail";
          const done = document.createElement("button");
          done.className = "status-btn done";
          done.type = "button";
          done.textContent = "done";
          done.onclick = () => {
            activeDelivery = card;
            doneBackdrop.hidden = false;
          };
          onway.parentNode.insertBefore(done, fail);
        };
        fail.onclick = () => {
          activeDelivery = card;
          failBackdrop.hidden = false;
        };
        info.onclick = () => (infoBackdrop.hidden = false);
      });
      cancelDone.onclick = () => (doneBackdrop.hidden = true);
      confirmDone.onclick = () => {
        if (activeDelivery) {
          activeDelivery.dataset.status = "done";
          activeDelivery.querySelector(".onway").hidden = true;
          activeDelivery.querySelector(".fail").hidden = true;
          activeDelivery.querySelector(".done").classList.add("completed");
        }
        doneBackdrop.hidden = true;
      };
      cancelFail.onclick = () => (failBackdrop.hidden = true);
      closeInfo.onclick = () => (infoBackdrop.hidden = true);
      confirmFail.onclick = () => {
        if (activeDelivery) {
          const fail = activeDelivery.querySelector(".fail"),
            onway = activeDelivery.querySelector(".onway"),
            done = activeDelivery.querySelector(".done");
          activeDelivery.dataset.status = "failed";
          onway.hidden = false;
          if (done) done.hidden = true;
          fail.classList.add("failed");
          fail.textContent = "failed";
          let note = activeDelivery.querySelector(".fail-note");
          if (!note) {
            note = document.createElement("small");
            note.className = "fail-note";
            activeDelivery.querySelector(".delivery-actions").append(note);
          }
          note.textContent = failReason.value || "No reason provided";
        }
        failReason.value = "";
        failBackdrop.hidden = true;
      };
      const nav = document.createElement("div");
      nav.className = "slide-sidebar";
      nav.innerHTML =
        '<a class="sidebar-row active-row" href="/bikers/ways"><strong>Ways</strong><span>Assigned deliveries</span></a><a class="sidebar-row" href="/bikers/history"><strong>History</strong><span>My records</span></a>';
      const overlay = document.createElement("div");
      overlay.className = "sidebar-overlay";
      document.body.append(nav, overlay);
      const close = () => {
        nav.classList.remove("open");
        overlay.classList.remove("visible");
      };
      document.querySelector(".hamburger-icon-btn").onclick = () => {
        nav.classList.add("open");
        overlay.classList.add("visible");
      };
      overlay.onclick = close;
    </script>
  </body>
</html>

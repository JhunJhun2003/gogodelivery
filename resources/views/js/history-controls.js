/* Shared custom select and calendar controls. Pages may supply their own filtering. */
(function () {
  document
    .querySelectorAll(".history-filter-card select:not([data-enhanced])")
    .forEach((select) => {
      if (select.parentElement.classList.contains("custom-select")) return;
      select.dataset.enhanced = "true";
      const wrapper = document.createElement("div");
      wrapper.className = "custom-select";
      select.parentNode.insertBefore(wrapper, select);
      wrapper.appendChild(select);
      const toggle = document.createElement("button");
      toggle.type = "button";
      toggle.className = "custom-select-toggle";
      toggle.textContent =
        select.options[select.selectedIndex]?.text || "Select";
      wrapper.appendChild(toggle);
      const options = document.createElement("ul");
      options.className = "custom-select-options";
      [...select.options].forEach((option, index) => {
        const item = document.createElement("li");
        item.className = "custom-select-option";
        item.textContent = option.text;
        item.onclick = () => {
          select.selectedIndex = index;
          toggle.textContent = option.text;
          wrapper.classList.remove("open");
        };
        options.appendChild(item);
      });
      wrapper.appendChild(options);
      toggle.onclick = () => {
        document.querySelectorAll(".custom-select.open").forEach((x) => {
          if (x !== wrapper) x.classList.remove("open");
        });
        wrapper.classList.toggle("open");
      };
    });
  document
    .querySelectorAll(".custom-date-picker:not([data-enhanced])")
    .forEach((picker) => {
      if (picker.querySelector(".calendar-head")) return;
      picker.dataset.enhanced = "true";
      const input = picker.querySelector('input[type="date"]'),
        trigger = picker.querySelector(".custom-date-trigger"),
        calendar = picker.querySelector(".custom-calendar");
      let date = new Date();
      const pad = (n) => String(n).padStart(2, "0");
      const draw = () => {
        const y = date.getFullYear(),
          m = date.getMonth(),
          first = new Date(y, m, 1).getDay(),
          days = new Date(y, m + 1, 0).getDate();
        calendar.innerHTML =
          '<div class="calendar-head"><button type="button" data-step="-1">‹</button><span>' +
          date.toLocaleString("en", { month: "long", year: "numeric" }) +
          '</span><button type="button" data-step="1">›</button></div><div class="calendar-grid"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="calendar-grid" data-days></div>';
        const grid = calendar.querySelector("[data-days]");
        for (let i = 0; i < first; i++)
          grid.append(document.createElement("button"));
        for (let d = 1; d <= days; d++) {
          const b = document.createElement("button");
          b.type = "button";
          b.textContent = d;
          b.onclick = () => {
            input.value = y + "-" + pad(m + 1) + "-" + pad(d);
            trigger.textContent =
              pad(d) + "/" + pad(m + 1) + "/" + String(y).slice(-2);
            picker.classList.remove("open");
          };
          grid.append(b);
        }
      };
      trigger.onclick = () => {
        picker.classList.toggle("open");
        draw();
      };
      calendar.onclick = (e) => {
        if (e.target.dataset.step) {
          date.setMonth(date.getMonth() + Number(e.target.dataset.step));
          draw();
        }
      };
    });
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".custom-select"))
      document
        .querySelectorAll(".custom-select.open")
        .forEach((x) => x.classList.remove("open"));
    document.querySelectorAll(".custom-date-picker.open").forEach((x) => {
      if (!x.contains(e.target)) x.classList.remove("open");
    });
  });
})();


/* ================= UNIVERSAL TAB SYSTEM + LOCAL STORAGE + DEFAULT ================= */

document.addEventListener("DOMContentLoaded", () => {

  document.querySelectorAll(".tabs-container").forEach((container, index) => {

    const storageKey = container.dataset.tabStorage || "tabs-" + index;

    const buttons = container.querySelectorAll(".tab-btn, .tab-btn-msource, .tab-btn-mtype, .tab-btn-admin");
    const panels = container.querySelectorAll(
      ".tab-panel, .tab-panel-msource, .tab-panel-mtype"
    );

    if (!buttons.length) return;

    let activeTabId = null;

    /* ---------- 1️⃣ Restore Saved Tab ---------- */

    const savedTab = localStorage.getItem(storageKey);

    if (savedTab) {
      const savedBtn = container.querySelector(`[data-tab="${savedTab}"]`);
      const savedPanel = container.querySelector("#" + savedTab);

      if (savedBtn && savedPanel) {
        activeTabId = savedTab;
      }
    }

    /* ---------- 2️⃣ Check Existing HTML Active ---------- */

    if (!activeTabId) {
      const activeBtn = container.querySelector("[data-tab].active");
      if (activeBtn) {
        activeTabId = activeBtn.dataset.tab;
      }
    }

    /* ---------- 3️⃣ Default to First Tab ---------- */

    if (!activeTabId) {
      activeTabId = buttons[0].dataset.tab;
    }

    /* ---------- Apply Active State ---------- */

    buttons.forEach(btn => btn.classList.remove("active"));
    panels.forEach(panel => panel.classList.remove("active"));

    const activeBtn = container.querySelector(`[data-tab="${activeTabId}"]`);
    const activePanel = container.querySelector("#" + activeTabId);

    activeBtn?.classList.add("active");
    activePanel?.classList.add("active");

    localStorage.setItem(storageKey, activeTabId);

    /* ---------- Click Events ---------- */

    buttons.forEach(button => {

      button.addEventListener("click", () => {

        const target = button.dataset.tab;
        const panel = container.querySelector("#" + target);

        if (!panel) return;

        buttons.forEach(btn => btn.classList.remove("active"));
        panels.forEach(p => p.classList.remove("active"));

        button.classList.add("active");
        panel.classList.add("active");

        localStorage.setItem(storageKey, target);

      });

    });

  });

});
function toggleWhatsAppChat() {
  var box = document.getElementById("whatsapp-chat-box");
  var overlay = document.getElementById("overlay");
  box.style.display = box.style.display === "block" ? "none" : "block";
  overlay.classList.toggle("active");
}

function sendWhatsAppMessage() {
  var message = document.getElementById("userMessage").value.trim();
  if (message !== "") {
    var phoneNumber = "254781449115";
    var url = "https://wa.me/" + phoneNumber + "?text=" + encodeURIComponent(message);
    window.open(url, "_blank");
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const cards = document.querySelectorAll('.account-card');
  const radios = document.querySelectorAll('input[name="accountType"]');
  const form = document.querySelector('form');
  const errorMessage = document.querySelector('.errorMessage');

  radios.forEach((radio) => {
    radio.addEventListener('change', () => {
      cards.forEach(card => card.classList.remove('selected'));
      radio.closest('.account-card').classList.add('selected');
    });
  });

  if (errorMessage) {
    errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});

/* Successful registration and redirecting */
document.addEventListener("DOMContentLoaded", () => {
  const successParagraph = document.querySelector(".successMessage");
  if (!successParagraph) return;

  const redirectSpan = document.getElementById("redirect-msg");
  if (!redirectSpan) return;

  const baseText = "Redirecting";
  let dots = "";
  let dotCount = 0;
  let typingDone = false;

  let i = 0;
  const typing = setInterval(() => {
    if (i < baseText.length) {
      redirectSpan.textContent += baseText.charAt(i);
      i++;
    } else {
      clearInterval(typing);
      typingDone = true;
      animateDots();
      setTimeout(() => {
        window.location.href = "index.php";
      }, 3500);
    }
  }, 100);

  function animateDots() {
    setInterval(() => {
      if (!typingDone) return;
      dotCount = (dotCount + 1) % 4;
      dots = ".".repeat(dotCount);
      redirectSpan.textContent = baseText + dots;
    }, 500);
  }
});

/* Toggle password visibility */
document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".toggle-password");

  toggles.forEach(icon => {
    icon.addEventListener("click", function () {
      const input = this.closest(".inpBox").querySelector(".password-field");

      if (input.type === "password") {
        input.type = "text";
        this.classList.replace("fa-eye", "fa-eye-slash");
        this.setAttribute("title", "Hide Password");

        // Auto-hide after 3 seconds
        setTimeout(() => {
          input.type = "password";
          this.classList.replace("fa-eye-slash", "fa-eye");
          this.setAttribute("title", "Show Password");
        }, 3000);
      } else {
        input.type = "password";
        this.classList.replace("fa-eye-slash", "fa-eye");
        this.setAttribute("title", "Show Password");
      }
    });
  });
});

/* Main Page Tabs */
let lastActiveMarketTypeTab = null;  // Tracks last market type tab
let lastActiveMarketSourceTab = null; // Tracks last source tab

document.addEventListener("DOMContentLoaded", () => {
  lastActiveMarketTypeTab = document.querySelector('.tab-btn.active');
  lastActiveMarketSourceTab = document.querySelector('.tab-btn-msource.active');
  
  const tabs = document.querySelectorAll('.tab-btn');
  const tabsmsource = document.querySelectorAll('.tab-btn-msource');
  const panels = document.querySelectorAll('.tab-panel');
  const panelsmsource = document.querySelectorAll('.tab-panel-msource');

  function activateTab(tab) {
    const target = tab.dataset.tab;

    // Deactivate all tabs & panels
    tabs.forEach(t => t.classList.remove('active'));
    tabsmsource.forEach(t => t.classList.remove('active'));
    panels.forEach(p => p.classList.remove('active'));
    panelsmsource.forEach(p => p.classList.remove('active'));

    // Activate clicked tab & its panel
    tab.classList.add('active');
    document.getElementById(target)?.classList.add('active');

    // Track last active tab depending on type
    if (tab.classList.contains('tab-btn')) {
      lastActiveMarketTypeTab = tab;
    } else if (tab.classList.contains('tab-btn-msource')) {
      lastActiveMarketSourceTab = tab;
    }
  }

  tabs.forEach(tab => tab.addEventListener('click', () => activateTab(tab)));
  tabsmsource.forEach(tab => tab.addEventListener('click', () => activateTab(tab)));

});

/* Profile Option Js */
function toggleProfileOption() {
  const profileOption = document.getElementById("profileOption");
  const overlay = document.getElementById("overlay1");
  profileOption.style.display = profileOption.style.display === "flex" ? "none" : "flex";
  overlay.classList.toggle("active");
}

/* Recent Order Js */

// Show only the 5 most recent orders
const tableBody = document.querySelector("#ordersTable tbody");
const allRows = Array.from(tableBody.querySelectorAll("tr"));
allRows.forEach((row, index) => {
  if(index >= 5) row.style.display = "none";
});

// Filter by status
const filter = document.getElementById("statusFilter");
filter.addEventListener("change", () => {
  const value = filter.value;
  let visibleCount = 0;

  document.querySelectorAll("#ordersTable tbody tr").forEach(el => {
    if (value === "all" || el.dataset.status === value) {
      el.style.display = visibleCount < 5 ? "" : "none"; // only 5
      visibleCount++;
    } else {
      el.style.display = "none";
    }
  });
});

/* Limit Mobile Order Cards Js */
document.addEventListener("DOMContentLoaded", () => {
  limitMobileOrderCards();
});

function limitMobileOrderCards(max = 5) {
  const cards = document.querySelectorAll("#orderCards .order-card");

  cards.forEach((card, index) => {
    card.style.display = index < max ? "block" : "none";
  });
}

/* Order Details Toggle Js */
document.querySelectorAll(".toggleOrd").forEach(btn => {
  btn.addEventListener("click", () => {
    const target = document.getElementById(btn.dataset.target);
    target.classList.toggle("active");
    btn.textContent = target.classList.contains("active")
      ? "Hide details"
      : "View details";
  });
});

function toggleOrderMain() {
  const orderMain = document.getElementById("orderMain");
  const marketMain = document.getElementById("marketMain");
  orderMain.style.display = "block";
  marketMain.style.display = "none";
}

function toggleMarketMain() {
  const orderMain = document.getElementById("orderMain");
  const marketMain = document.getElementById("marketMain");
  orderMain.style.display = "none";
  marketMain.style.display = "block";
}

/* ================= MARKET NAVIGATION (FIXED) ================= */
function showMarketContainer(target) {
  const typeTab = document.getElementById("toggleMarketTypeTab");
  const sourceTab = document.getElementById("toggleMarketSourceTab");

  if (!typeTab || !sourceTab) return;

  if (target === "type") {
    typeTab.style.display = "block";
    sourceTab.style.display = "none";
  }

  if (target === "source") {
    typeTab.style.display = "none";
    sourceTab.style.display = "block";
  }
}

/* ================= OPEN MARKET SOURCE (CORRECT) ================= */

function openMarketSource(sourceTabId = "shops") {
  showMarketContainer("source");

  // Deactivate all source tabs & panels
  document.querySelectorAll(".tab-btn-msource").forEach(btn =>
    btn.classList.remove("active")
  );
  document.querySelectorAll(".tab-panel-msource").forEach(panel =>
    panel.classList.remove("active")
  );

  // Activate the correct source tab
  const btn = document.querySelector(`.tab-btn-msource[data-tab="${sourceTabId}"]`);
  const panel = document.getElementById(sourceTabId);

  if (btn && panel) {
    btn.classList.add("active");
    panel.classList.add("active");

    // Track as last active source tab
    lastActiveMarketSourceTab = btn;
  }
}

/* ================= GO BACK ================= */

function goBackToMarketTypes() {
  const marketMain = document.getElementById("marketMain");
  const orderMain = document.getElementById("orderMain");

  if (marketMain) marketMain.style.display = "block";
  if (orderMain) orderMain.style.display = "none";

  showMarketContainer("type");

  // Restore last active market type tab
  if (lastActiveMarketTypeTab) {
    lastActiveMarketTypeTab.classList.add('active');
    const panel = document.getElementById(lastActiveMarketTypeTab.dataset.tab);
    if (panel) panel.classList.add('active');
  }

  // Optionally, if you want to **return to the last source tab** after re-opening source
  // Example: after user goes to Shops → Supermarkets → Back → then clicks a source tab again,
  // the previous last source tab is remembered
  if (lastActiveMarketSourceTab) {
    lastActiveMarketSourceTab.classList.add('active');
    const sourcePanel = document.getElementById(lastActiveMarketSourceTab.dataset.tab);
    if (sourcePanel) sourcePanel.classList.add('active');
  }
}
function toggleWhatsAppChat() {
  var box = document.getElementById("whatsapp-chat-box");
  var overlay = document.getElementById("overlay");
  box.style.display = box.style.display === "block" ? "none" : "block";
  overlay.classList.toggle("active");
}

function togglePaymentOption() {
  var box = document.getElementById("paymentContainer");
  var payOverlay = document.getElementById("payOverlay");
  box.style.display = box.style.display === "flex" ? "none" : "flex";
  payOverlay.classList.toggle("active");
}

function toggleCartBar() {
  document.querySelector('.cart-container')?.classList.toggle('show');
  var cartOverlay = document.getElementById("cartOverlay");
  cartOverlay.classList.toggle("active");
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
  const redirectSpan = document.getElementById("redirect-msg");

  // ✅ HARD GUARD — prevents crash
  if (!successParagraph || !redirectSpan) return;

  const baseText = "Redirecting";
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
      redirectSpan.textContent = baseText + ".".repeat(dotCount);
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

const STORAGE_KEYS = {
  marketType: "activeMarketTypeTab",
  marketSource: "activeMarketSourceTab",
  agentMarketType: "activeAgentMarketTypeTab",
  adminTab: "activeAdminTab"
};

/* Main Page Tabs */
let lastActiveMarketTypeTab = null;  // Tracks last market type tab
let lastActiveMarketSourceTab = null; // Tracks last source tab
let lastActiveMarketAgentTab = null; // Tracks last source tab

document.addEventListener("DOMContentLoaded", () => {
  lastActiveMarketTypeTab = document.querySelector('.tab-btn.active');
  lastActiveMarketSourceTab = document.querySelector('.tab-btn-msource.active');
  lastActiveMarketAgentTab = document.querySelector('.tab-btn-mtype.active');

  const tabs = document.querySelectorAll('.tab-btn');
  const tabsmsource = document.querySelectorAll('.tab-btn-msource');
  const tabsmtype = document.querySelectorAll('.tab-btn-mtype');
  const panels = document.querySelectorAll('.tab-panel');
  const panelsmsource = document.querySelectorAll('.tab-panel-msource');
  const panelsmtype = document.querySelectorAll('.tab-panel-mtype');

  // 🔁 RESTORE LAST ACTIVE TABS FROM LOCAL STORAGE

  const savedMarketType = localStorage.getItem(STORAGE_KEYS.marketType);
  const savedMarketSource = localStorage.getItem(STORAGE_KEYS.marketSource);
  const savedAgentMarketType = localStorage.getItem(STORAGE_KEYS.agentMarketType);

  // Restore Market Type
  if (savedMarketType) {
    const btn = document.querySelector(`.tab-btn[data-tab="${savedMarketType}"]`);
    const panel = document.getElementById(savedMarketType);
    btn?.classList.add("active");
    panel?.classList.add("active");
  }

  // Restore Market Source (but DO NOT auto-switch container)
  if (savedMarketSource) {
    const btn = document.querySelector(`.tab-btn-msource[data-tab="${savedMarketSource}"]`);
    const panel = document.getElementById(savedMarketSource);

    btn?.classList.add("active");
    panel?.classList.add("active");

    lastActiveMarketSourceTab = btn;
  }

  // Restore Agent Market Type
  if (savedAgentMarketType) {
    const btn = document.querySelector(`.tab-btn-mtype[data-tab="${savedAgentMarketType}"]`);
    const panel = document.getElementById(savedAgentMarketType);
    btn?.classList.add("active");
    panel?.classList.add("active");
  }

  function activateTab(tab) {
    const target = tab.dataset.tab;
    if (!target) return;

    // Deactivate all
    tabs.forEach(t => t.classList.remove('active'));
    tabsmsource.forEach(t => t.classList.remove('active'));
    tabsmtype.forEach(t => t.classList.remove('active'));
    panels.forEach(p => p.classList.remove('active'));
    panelsmsource.forEach(p => p.classList.remove('active'));
    panelsmtype.forEach(p => p.classList.remove('active'));

    // Activate selected
    tab.classList.add('active');
    document.getElementById(target)?.classList.add('active');

    // 🔐 SAVE TO LOCAL STORAGE
    if (tab.classList.contains('tab-btn')) {
      localStorage.setItem(STORAGE_KEYS.marketType, target);
      lastActiveMarketTypeTab = tab;
    }

    if (target === "products") {
      localStorage.setItem("seller:productsView", "list");
    }

    if (tab.classList.contains('tab-btn-msource')) {
      localStorage.setItem(STORAGE_KEYS.marketSource, target);
      lastActiveMarketSourceTab = tab;
    }

    if (tab.classList.contains('tab-btn-mtype')) {
      localStorage.setItem(STORAGE_KEYS.agentMarketType, target);
      lastActiveMarketAgentTab = tab;
    }
  }

  tabs.forEach(tab => tab.addEventListener('click', () => activateTab(tab)));
  tabsmsource.forEach(tab => tab.addEventListener('click', () => activateTab(tab)));
  tabsmtype.forEach(tab => tab.addEventListener('click', () => activateTab(tab)));

  /* ✅ SELLER DASHBOARD FALLBACK */
  const hasActiveSellerTab = document.querySelector(".tab-btn.active");
  const hasActiveSellerPanel = document.querySelector(".tab-panel.active");

  if (!hasActiveSellerTab || !hasActiveSellerPanel) {
    // Clear any leftovers
    tabs.forEach(t => t.classList.remove("active"));
    panels.forEach(p => p.classList.remove("active"));

    // Activate Seller Dashboard
    const dashboardTab = document.querySelector('.tab-btn[data-tab="dashboard"]');
    const dashboardPanel = document.getElementById("dashboard");

    dashboardTab?.classList.add("active");
    dashboardPanel?.classList.add("active");

    // 🔐 Persist it
    localStorage.setItem(STORAGE_KEYS.marketType, "dashboard");
  }

  // ===============================
  // BUYER MARKET DEFAULT (PRODUCTS)
  // ===============================
  (() => {
    const marketTypeContainer = document.getElementById("toggleMarketTypeTab");
    if (!marketTypeContainer) return; // ⛔ Not buyer page

    const tabs = marketTypeContainer.querySelectorAll(".tab-btn");
    const panels = marketTypeContainer.querySelectorAll(".tab-panel");

    const hasActiveTab = marketTypeContainer.querySelector(".tab-btn.active");
    const hasActivePanel = marketTypeContainer.querySelector(".tab-panel.active");

    // ✅ If NOTHING is active → default to Products
    if (!hasActiveTab || !hasActivePanel) {
      tabs.forEach(t => t.classList.remove("active"));
      panels.forEach(p => p.classList.remove("active"));

      const defaultTab =
        marketTypeContainer.querySelector('.tab-btn[data-tab="products"]');
      const defaultPanel = document.getElementById("products");

      defaultTab?.classList.add("active");
      defaultPanel?.classList.add("active");

      // 🔐 Persist for consistency
      localStorage.setItem(STORAGE_KEYS.marketType, "products");
      lastActiveMarketTypeTab = defaultTab;
    }
  })();

  /* ===============================
   AGENT DASHBOARD DEFAULT
   =============================== */
  (() => {
    const agentContainer = document.getElementById("toggleAgentTab");
    if (!agentContainer) return; // ⛔ Not agent page

    const agentTabs = agentContainer.querySelectorAll(".tab-btn");
    const agentPanels = agentContainer.querySelectorAll(".tab-panel");

    const hasActiveAgentTab = agentContainer.querySelector(".tab-btn.active");
    const hasActiveAgentPanel = agentContainer.querySelector(".tab-panel.active");

    // ✅ Only fallback if NOTHING is active
    if (!hasActiveAgentTab || !hasActiveAgentPanel) {
      agentTabs.forEach(t => t.classList.remove("active"));
      agentPanels.forEach(p => p.classList.remove("active"));

      const dashboardTab =
        agentContainer.querySelector('.tab-btn[data-tab="dashboard"]');
      const dashboardPanel =
        agentContainer.querySelector('#dashboard');

      dashboardTab?.classList.add("active");
      dashboardPanel?.classList.add("active");

      // 🔐 Persist (optional but recommended)
      localStorage.setItem("activeAgentDashboard", "dashboard");
    }
  })();
  /* ===============================
   SELLER PRODUCTS VIEW RESTORE
   =============================== */
  (() => {
    const productsPanel = document.getElementById("products");
    const addPanel = document.getElementById("add-products");
    const productsTab =
      document.querySelector('.tab-btn[data-tab="products"]');

    if (!productsPanel || !addPanel || !productsTab) return;

    // Only restore if PRODUCTS tab is active
    if (!productsTab.classList.contains("active")) return;

    const savedView = localStorage.getItem("seller:productsView") || "list";

    productsPanel.classList.remove("active");
    addPanel.classList.remove("active");

    if (savedView === "add") {
      addPanel.classList.add("active");
    } else {
      productsPanel.classList.add("active");
    }
  })();

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
document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#ordersTable tbody");
  const filter = document.getElementById("statusFilter");

  if (!tableBody) return; // 🔑 PREVENT CRASH

  const allRows = Array.from(tableBody.querySelectorAll("tr"));

  // Show only 5
  allRows.forEach((row, index) => {
    if (index >= 5) row.style.display = "none";
  });

  if (filter) {
    filter.addEventListener("change", () => {
      const value = filter.value;
      let visibleCount = 0;

      allRows.forEach(el => {
        if (value === "all" || el.dataset.status === value) {
          el.style.display = visibleCount < 5 ? "" : "none";
          visibleCount++;
        } else {
          el.style.display = "none";
        }
      });
    });
  }
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

function toggleOrderMarket() {
  const orderMain = document.getElementById("orderMain");
  const marketMain = document.getElementById("marketMain");

  const isMarketVisible = marketMain.style.display !== "none";

  marketMain.style.display = isMarketVisible ? "none" : "flex";
  orderMain.style.display = isMarketVisible ? "flex" : "none";
}

function toggleSellerOrdersTrack() {
  const sellerMain = document.getElementById("sellerMain");
  const ordersTrackMain = document.getElementById("ordersTrackMain");

  const isSellerVisible = sellerMain.style.display !== "none";

  sellerMain.style.display = isSellerVisible ? "none" : "flex";
  ordersTrackMain.style.display = isSellerVisible ? "flex" : "none";
}

function toggleAgentOrdersTrack() { 
  const agentMain = document.getElementById("agentMain");
  const orderMain = document.getElementById("orderMain");
  const earningsTrackMain = document.getElementById("earningsTrackMain");
  const agentWithdrawalH = document.getElementById("agentWithdrawalH");

  const isOrderVisible = getComputedStyle(orderMain).display !== "none";

  orderMain.style.display = isOrderVisible ? "none" : "flex";
  agentMain.style.display = isOrderVisible ? "flex" : "none";
  earningsTrackMain.style.display = "none";
  agentWithdrawalH.style.display = "none"; // hide withdrawals
}

function toggleAgentEarningsTrack() {
  const agentMain = document.getElementById("agentMain");
  const orderMain = document.getElementById("orderMain");
  const earningsTrackMain = document.getElementById("earningsTrackMain");
  const agentWithdrawalH = document.getElementById("agentWithdrawalH");

  const isEarningsVisible = getComputedStyle(earningsTrackMain).display !== "none";

  earningsTrackMain.style.display = isEarningsVisible ? "none" : "flex";
  agentMain.style.display = isEarningsVisible ? "flex" : "none";
  orderMain.style.display = "none";
  agentWithdrawalH.style.display = "none"; // hide withdrawals
}

function toggleAgentWithdrawals() {
  const agentMain = document.getElementById("agentMain");
  const orderMain = document.getElementById("orderMain");
  const earningsTrackMain = document.getElementById("earningsTrackMain");
  const agentWithdrawalH = document.getElementById("agentWithdrawalH");

  const isWithdrawalVisible = getComputedStyle(agentWithdrawalH).display !== "none";

  agentWithdrawalH.style.display = isWithdrawalVisible ? "none" : "flex";
  agentMain.style.display = isWithdrawalVisible ? "flex" : "none";
  orderMain.style.display = "none";
  earningsTrackMain.style.display = "none";
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

function showAgentMarketContainer(target) {
  const typeTab = document.getElementById("toggleMarketTypeTabAgent");
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

function showMarketTypeContainer(target) {
  const agentTab = document.getElementById("toggleAgentTab");
  const typeTab = document.getElementById("toggleMarketTypeTabAgent");

  if (!agentTab || !typeTab) return;

  if (target === "agent") {
    agentTab.style.display = "block";
    typeTab.style.display = "none";
  }

  if (target === "type") {
    agentTab.style.display = "none";
    typeTab.style.display = "block";
  }
}

function showAgentMarketTypeContainer(target) {
  const marketTab = document.getElementById("toggleMarketTypeTabAgent");
  const sourceTab = document.getElementById("toggleMarketSourceTab");

  if (!marketTab || !sourceTab) return;

  if (target === "type") {
    marketTab.style.display = "block";
    sourceTab.style.display = "none";
  }

  if (target === "source") {
    marketTab.style.display = "none";
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

function openAgentMarketSource(sourceTabId = "shops") {
  showAgentMarketContainer("source");

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

function openMarketType(typeTabId = "products") {
  showMarketTypeContainer("type");

  // Deactivate all type tabs & panels
  document.querySelectorAll(".tab-btn-mtype").forEach(btn =>
    btn.classList.remove("active")
  );
  document.querySelectorAll(".tab-panel-mtype").forEach(panel =>
    panel.classList.remove("active")
  );

  // Activate the correct type tab
  const btn = document.querySelector(`.tab-btn-mtype[data-tab="${typeTabId}"]`);
  const panel = document.getElementById(typeTabId);

  if (btn && panel) {
    btn.classList.add("active");
    panel.classList.add("active");

    // Track as last active type tab
    lastActiveMarketAgentTab = btn;
  }
}

/* ================= GO BACK ================= */

function goBackToMarketTypes() {
  const marketMain = document.getElementById("marketMain");
  const orderMain = document.getElementById("orderMain");

  if (marketMain) marketMain.style.display = "flex";
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

function goBackToAgent() {
  const agentTab = document.getElementById("agentMain");
  const productsTab = document.getElementById("toggleMarketTypeTabAgent");

  if (agentTab) agentMain.style.display = "flex";
  if (productsTab) orderMain.style.display = "none";

  showMarketTypeContainer("agent");

  // Restore last active agent tab
  if (lastActiveMarketAgentTab) {
    lastActiveMarketAgentTab.classList.add('active');
    const panel = document.getElementById(lastActiveMarketAgentTab.dataset.tab);
    if (panel) panel.classList.add('active');
  }

  // Optionally, if you want to **return to the last source tab** after re-opening source
  // Example: after user goes to Shops → Supermarkets → Back → then clicks a source tab again,
  // the previous last type tab is remembered
  if (lastActiveMarketTypeTab) {
    lastActiveMarketTypeTab.classList.add('active');
    const typePanel = document.getElementById(lastActiveMarketTypeTab.dataset.tab);
    if (typePanel) typePanel.classList.add('active');
  }
}

function goBackToAgentMarketTypes() {
  const marketTab = document.getElementById("toggleMarketTypeTabAgent");
  const sourceTab = document.getElementById("toggleMarketSourceTab");

  if (marketTab) marketTab.style.display = "block";
  if (sourceTab) sourceTab.style.display = "none";

  showAgentMarketTypeContainer("type");

  // 🔑 CLEAR previous states
  document.querySelectorAll(".tab-btn-mtype").forEach(btn =>
    btn.classList.remove("active")
  );
  document.querySelectorAll(".tab-panel-mtype").forEach(panel =>
    panel.classList.remove("active")
  );

  // 🔑 RESTORE LAST ACTIVE *AGENT* MARKET TYPE TAB
  if (lastActiveMarketAgentTab) {
    lastActiveMarketAgentTab.classList.add("active");
    const panel = document.getElementById(lastActiveMarketAgentTab.dataset.tab);
    if (panel) panel.classList.add("active");
  } else {
    // 🧠 Fallback: default to Products
    const defaultBtn = document.querySelector('.tab-btn-mtype[data-tab="products"]');
    const defaultPanel = document.getElementById("products");

    defaultBtn?.classList.add("active");
    defaultPanel?.classList.add("active");
  }
}

/* =========================
   CART LOGIC – FULL SCRIPT
========================= */

/* ---------- GLOBAL ELEMENTS ---------- */
const cartItemsContainer = document.getElementById("cartItems");
const cartCountEl = document.querySelector(".cart-count");
const subtotalEl = document.getElementById("subtotal");
const totalEl = document.getElementById("total");
const emptyMsg = document.getElementById("emptyCartMessage");

const deliveryFee = 0;

/* ---------- UPDATE TOTALS ---------- */
function updateTotals() {
  if (!subtotalEl || !totalEl) return;

  let subtotal = 0;
  const items = document.querySelectorAll(".cart-item");

  if (items.length === 0) {
    if (emptyMsg) emptyMsg.style.display = "block";
    subtotalEl.textContent = "KES 0";
    totalEl.textContent = "KES 0";
    updateCartCount();
    return;
  }

  if (emptyMsg) emptyMsg.style.display = "none";

  items.forEach(item => {
    const price = Number(item.dataset.price);
    const qty = Number(item.querySelector(".qty-number").textContent);
    subtotal += price * qty;
  });

  subtotalEl.textContent = `KES ${subtotal}`;
  totalEl.textContent = `KES ${subtotal + deliveryFee}`;
}

/* ---------- UPDATE CART COUNT ---------- */
function updateCartCount() {
  let count = 0;
  document.querySelectorAll(".cart-item").forEach(item => {
    count += Number(item.querySelector(".qty-number").textContent);
  });

  if (cartCountEl) cartCountEl.textContent = count;
}

/* ---------- ADD TO CART ---------- */
function addToCart(product) {
  if (!cartItemsContainer) return;

  const existingItem = [...document.querySelectorAll(".cart-item")]
    .find(item => item.dataset.name === product.name);

  if (existingItem) {
    const qtyEl = existingItem.querySelector(".qty-number");
    qtyEl.textContent = Number(qtyEl.textContent) + 1;
  } else {
    const item = document.createElement("div");
    item.className = "cart-item";
    item.dataset.name = product.name;
    item.dataset.price = product.price;

    item.innerHTML = `
      <div class="cart-left">
        <img src="${product.image}" alt="${product.name}">
        <div class="cart-info">
          <h4>${product.name}</h4>
          <p>KES ${product.price}</p>
          <div class="remove-btn">Remove</div>
        </div>
      </div>

      <div class="quantity-control">
        <button class="qty-btn minus">-</button>
        <div class="qty-number">1</div>
        <button class="qty-btn plus">+</button>
      </div>
    `;

    cartItemsContainer.appendChild(item);
  }

  updateTotals();
  updateCartCount();
}

/* ---------- GLOBAL CLICK HANDLER ---------- */
document.addEventListener("click", e => {

  /* Increase quantity */
  if (e.target.classList.contains("plus")) {
    const qty = e.target.parentElement.querySelector(".qty-number");
    qty.textContent = Number(qty.textContent) + 1;
  }

  /* Decrease quantity */
  if (e.target.classList.contains("minus")) {
    const qty = e.target.parentElement.querySelector(".qty-number");
    if (Number(qty.textContent) > 1) {
      qty.textContent = Number(qty.textContent) - 1;
    }
  }

  /* Remove item */
  if (e.target.classList.contains("remove-btn")) {
    e.target.closest(".cart-item")?.remove();
  }

  updateTotals();
  updateCartCount();
});

/* ---------- ADD-TO-CART BUTTONS ---------- */
document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const card = btn.closest(".variable-card");

    if (!card) return;

    const product = {
      name: card.dataset.name,
      price: Number(card.dataset.price),
      image: card.dataset.image
    };

    addToCart(product);
  });
});

/* ---------- INIT ---------- */
document.addEventListener("DOMContentLoaded", () => {
  updateTotals();
  updateCartCount();
});

// ================= HEADER SECTION =================
let headerTimer;
let firstLoadShown = false; // ✅ FIX #1

function showTopSection(timeout = 3000) {
  const header = document.querySelector(".topSection");
  if (!header) return;

  header.classList.add("show");

  clearTimeout(headerTimer);
  headerTimer = setTimeout(() => {
    header.classList.remove("show");
  }, timeout);
}

/* ---------- SHOW ON FIRST LOAD ---------- */
document.addEventListener("DOMContentLoaded", () => {
  showTopSection(4000); // ✅ always show on first load
  firstLoadShown = true;
});

/* ---------- SHOW ON SCROLL ---------- */
let lastScrollY = window.scrollY;
let scrollTimeout;

window.addEventListener("scroll", () => {
  const diff = Math.abs(window.scrollY - lastScrollY);

  if (diff > 20) {
    showTopSection(2500);
  }

  lastScrollY = window.scrollY;

  clearTimeout(scrollTimeout);
  scrollTimeout = setTimeout(() => {
    document.querySelector(".topSection")?.classList.remove("show");
  }, 2800);
});

/* ---------- SHOW ON USER ACTION ---------- */
document.addEventListener("click", e => {
  if (
    e.target.closest(".add-to-cart-btn") ||
    e.target.closest(".cart-wrapper") ||
    e.target.closest(".buy-btn")
  ) {
    showTopSection(3000);
  }
});

// ---------- SHOW ON USER ACTIVITY ----------
let activityTimer;

document.addEventListener("mousemove", () => {
  clearTimeout(activityTimer);
  showTopSection(2000);

  activityTimer = setTimeout(() => {
    document.querySelector(".topSection")?.classList.remove("show");
  }, 3000);
});

// ---------- SHOW ON MOUSE MOVE (TOP EDGE) ----------
let mouseCooldown;

document.addEventListener("mousemove", (e) => {
  // Trigger only when cursor is near top (e.g. 80px)
  if (e.clientY < 80) {
    if (mouseCooldown) return;

    showTopSection(2500);

    mouseCooldown = true;
    setTimeout(() => {
      mouseCooldown = false;
    }, 1000); // throttle to prevent spam
  }
});

// ADD PRODUCT TOGGLE
function toggleProductsAdd(showAdd) {
  const productsPanel = document.getElementById("products");
  const addPanel = document.getElementById("add-products");

  if (!productsPanel || !addPanel) return;

  if (showAdd) {
    productsPanel.classList.remove("active");
    addPanel.classList.add("active");

    // ✅ persist state
    localStorage.setItem("seller:productsView", "add");
  } else {
    addPanel.classList.remove("active");
    productsPanel.classList.add("active");

    // ✅ persist state
    localStorage.setItem("seller:productsView", "list");
  }
}

function toggleAgentAdd(showAdd) {
  const products = document.getElementById("agency");
  const addProducts = document.getElementById("add-products");

  if (showAdd) {
    products.classList.remove("active");
    addProducts.classList.add("active");
  } else {
    products.classList.add("active");
    addProducts.classList.remove("active");
  }
}


// ADMIN DASHBOARD JS
function toggleNavigationBar() {
  const navOverlay = document.getElementById("navOverlay");
  navOverlay.classList.toggle("active");
  document.querySelector('.navigation-bar')?.classList.toggle('show');
}

document.addEventListener("DOMContentLoaded", () => {

  // ✅ Only run if admin dashboard exists
  if (!document.querySelector(".admin-tab-panel")) return;

  const tabs = document.querySelectorAll(".nav-link");
  const panels = document.querySelectorAll(".admin-tab-panel");

  const hasActiveTab = document.querySelector(".nav-link.active");
  const hasActivePanel = document.querySelector(".admin-tab-panel.active");

  if (!hasActiveTab || !hasActivePanel) {
    tabs.forEach(t => t.classList.remove("active"));
    panels.forEach(p => p.classList.remove("active"));

    const defaultTab = document.querySelector('.nav-link[data-tab="dashboard"]');
    const defaultPanel = document.querySelector('.admin-tab-panel[data-tab="dashboard"]');

    defaultTab?.classList.add("active");
    defaultPanel?.classList.add("active");
  }

  tabs.forEach(tab => {
    tab.addEventListener("click", e => {
      e.preventDefault();

      const target = tab.dataset.tab;
      if (!target) return;

      tabs.forEach(t => t.classList.remove("active"));
      panels.forEach(p => p.classList.remove("active"));

      tab.classList.add("active");
      document
        .querySelector(`.admin-tab-panel[data-tab="${target}"]`)
        ?.classList.add("active");

      toggleNavigationBar?.();
    });
  });

});

// WALLET TOGGLE
document.addEventListener("DOMContentLoaded", () => {
  const walletSelect = document.querySelector(".walletChange");
  const salesWallet = document.getElementById("salesWallet");
  const agencyWallet = document.getElementById("agencyWallet");

  // Default view
  salesWallet?.classList.add("active");

  if (walletSelect) {
    walletSelect.addEventListener("change", () => {
      if (walletSelect.value === "Sales Wallet") {
        salesWallet.classList.add("active");
        agencyWallet.classList.remove("active");
      } else {
        agencyWallet.classList.add("active");
        salesWallet.classList.remove("active");
      }
    });
  }
});
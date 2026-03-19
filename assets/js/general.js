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

/* Global success redirect system */
document.addEventListener("DOMContentLoaded", () => {

  const successMessage = document.querySelector(".successMessage");

  if (!successMessage) return;

  const redirectSpan = successMessage.querySelector(".redirect-msg");

  if (!redirectSpan) return;

  const redirectUrl = successMessage.dataset.redirect || "index.php";

  const baseText = "Redirecting";
  let dotCount = 0;
  let typingDone = false;
  let i = 0;

  const typing = setInterval(() => {

    if (i < baseText.length) {
      redirectSpan.textContent += baseText.charAt(i);
      i++;
    }

    else {

      clearInterval(typing);
      typingDone = true;
      animateDots();

      setTimeout(() => {
        window.location.href = redirectUrl;
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

/* Profile Option Js */
function toggleProfileOption() {
  const profileOption = document.getElementById("profileOption");
  const overlay = document.getElementById("overlay1");
  profileOption.style.display = profileOption.style.display === "flex" ? "none" : "flex";
  overlay.classList.toggle("active");
}

/* Recent Orders - Desktop + Mobile Sync */

document.addEventListener("DOMContentLoaded", () => {

  const filter = document.getElementById("statusFilter");

  const tableRows = document.querySelectorAll("#ordersTable tbody tr");
  const cards     = document.querySelectorAll("#orderCards .order-card");

  const MAX_ITEMS = 10;

  function applyFilter() {

    const value = filter ? filter.value : "all";

    let visibleCountTable = 0;
    let visibleCountCards = 0;

    // ===== Desktop Table =====
    tableRows.forEach(row => {

      if (value === "all" || row.dataset.status === value) {

        if (visibleCountTable < MAX_ITEMS) {
          row.style.display = "";
          visibleCountTable++;
        } else {
          row.style.display = "none";
        }

      } else {
        row.style.display = "none";
      }

    });

    // ===== Mobile Cards =====
    cards.forEach(card => {

      if (value === "all" || card.dataset.status === value) {

        if (visibleCountCards < MAX_ITEMS) {
          card.style.display = "block";
          visibleCountCards++;
        } else {
          card.style.display = "none";
        }

      } else {
        card.style.display = "none";
      }

    });

  }

  // Run on page load
  applyFilter();

  // Run on filter change
  if (filter) {
    filter.addEventListener("change", applyFilter);
  }

});

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
  const agentProductMain = document.getElementById("productsAgentMain");

  const isOrderVisible = getComputedStyle(orderMain).display !== "none";

  orderMain.style.display = isOrderVisible ? "none" : "flex";
  agentMain.style.display = isOrderVisible ? "flex" : "none";

  earningsTrackMain.style.display = "none";
  agentWithdrawalH.style.display = "none";
  agentProductMain.style.display = "none";
}

function toggleAgentEarningsTrack() {
  const agentMain = document.getElementById("agentMain");
  const orderMain = document.getElementById("orderMain");
  const earningsTrackMain = document.getElementById("earningsTrackMain");
  const agentWithdrawalH = document.getElementById("agentWithdrawalH");
  const agentProductMain = document.getElementById("productsAgentMain");

  const isEarningsVisible = getComputedStyle(earningsTrackMain).display !== "none";

  earningsTrackMain.style.display = isEarningsVisible ? "none" : "flex";
  agentMain.style.display = isEarningsVisible ? "flex" : "none";

  orderMain.style.display = "none";
  agentWithdrawalH.style.display = "none";
  agentProductMain.style.display = "none";
}

function toggleAgentWithdrawals() {
  const agentMain = document.getElementById("agentMain");
  const orderMain = document.getElementById("orderMain");
  const earningsTrackMain = document.getElementById("earningsTrackMain");
  const agentWithdrawalH = document.getElementById("agentWithdrawalH");
  const agentProductMain = document.getElementById("productsAgentMain");

  const isWithdrawalVisible = getComputedStyle(agentWithdrawalH).display !== "none";

  agentWithdrawalH.style.display = isWithdrawalVisible ? "none" : "flex";
  agentMain.style.display = isWithdrawalVisible ? "flex" : "none";

  orderMain.style.display = "none";
  earningsTrackMain.style.display = "none";
  agentProductMain.style.display = "none";
}

function toggleAgentProductsPage() {
  const agentMain = document.getElementById("agentMain");
  const orderMain = document.getElementById("orderMain");
  const earningsTrackMain = document.getElementById("earningsTrackMain");
  const agentWithdrawalH = document.getElementById("agentWithdrawalH");
  const agentProductMain = document.getElementById("productsAgentMain");

  const isProductsVisible = getComputedStyle(agentProductMain).display !== "none";

  agentProductMain.style.display = isProductsVisible ? "none" : "flex";
  agentMain.style.display = isProductsVisible ? "flex" : "none";

  orderMain.style.display = "none";
  earningsTrackMain.style.display = "none";
  agentWithdrawalH.style.display = "none";
}

/* ================= MARKET NAVIGATION (UPDATED FOR UNIVERSAL TABS) ================= */

/* ---------- SHOW MARKET TYPE OR SOURCE ---------- */

function showMarketContainer(target) {
  const typeTab = document.getElementById("toggleMarketTypeTab");
  const sourceTabs = document.querySelectorAll(".toggleMarketSourceTab");

  if (!typeTab || !sourceTabs.length) return;

  if (target === "type") {
    typeTab.style.display = "block";
    sourceTabs.forEach(el => el.style.display = "none");
  }

  if (target === "source") {
    typeTab.style.display = "none";
    sourceTabs.forEach(el => el.style.display = "block");
  }
}

/* ---------- SHOW AGENT MARKET TYPE OR SOURCE ---------- */

function showAgentMarketContainer(target) {
  const typeTab = document.getElementById("toggleMarketTypeTabAgent");
  const sourceTabs = document.querySelectorAll(".toggleMarketSourceTab");

  if (!typeTab || !sourceTabs.length) return;

  if (target === "type") {
    typeTab.style.display = "block";
    sourceTabs.forEach(el => el.style.display = "none");
  }

  if (target === "source") {
    typeTab.style.display = "none";
    sourceTabs.forEach(el => el.style.display = "block");
  }
}

/* ================= OPEN MARKET SOURCE ================= */

function openMarketSource(sourceTabId = "shops") {

  showMarketContainer("source");

  const btn = document.querySelector(`.tab-btn-msource[data-tab="${sourceTabId}"]`);
  const panel = document.getElementById(sourceTabId);

  if (!btn || !panel) return;

  const container = btn.closest(".tabs-container");

  /* hide other source containers */
  document.querySelectorAll(".toggleMarketSourceTab")
    .forEach(c => c.style.display = "none");

  container.style.display = "block";

  /* activate tab */
  container.querySelectorAll("[data-tab]").forEach(b => b.classList.remove("active"));
  container.querySelectorAll(".tab-panel-msource").forEach(p => p.classList.remove("active"));

  btn.classList.add("active");
  panel.classList.add("active");

  /* save tab */
  const storageKey = container.dataset.tabStorage || "market-source";
  localStorage.setItem(storageKey, sourceTabId);
}

/* ================= OPEN AGENT MARKET SOURCE ================= */

function openAgentMarketSource(sourceTabId = "shops") {

  showAgentMarketContainer("source");

  const btn = document.querySelector(`.tab-btn-msource[data-tab="${sourceTabId}"]`);
  const panel = document.getElementById(sourceTabId);

  if (!btn || !panel) return;

  const container = btn.closest(".tabs-container");

  container.querySelectorAll("[data-tab]").forEach(b => b.classList.remove("active"));
  container.querySelectorAll(".tab-panel-msource").forEach(p => p.classList.remove("active"));

  btn.classList.add("active");
  panel.classList.add("active");

  const storageKey = container.dataset.tabStorage;
  if (storageKey) localStorage.setItem(storageKey, sourceTabId);
}


/* ================= OPEN AGENT MARKET TYPE ================= */

function openMarketType(typeTabId = "products") {

  const btn = document.querySelector(`.tab-btn-mtype[data-tab="${typeTabId}"]`);
  const panel = document.getElementById(typeTabId);

  if (!btn || !panel) return;

  const container = btn.closest(".tabs-container");

  container.querySelectorAll("[data-tab]").forEach(b => b.classList.remove("active"));
  container.querySelectorAll(".tab-panel-mtype").forEach(p => p.classList.remove("active"));

  btn.classList.add("active");
  panel.classList.add("active");

  const storageKey = container.dataset.tabStorage;
  if (storageKey) localStorage.setItem(storageKey, typeTabId);
}


/* ================= GO BACK TO MARKET TYPES ================= */

function goBackToMarketTypes() {

  const marketMain = document.getElementById("marketMain");
  const orderMain = document.getElementById("orderMain");

  if (marketMain) marketMain.style.display = "flex";
  if (orderMain) orderMain.style.display = "none";

  showMarketContainer("type");

}


/* ================= GO BACK TO AGENT DASHBOARD ================= */

function goBackToAgent() {

  const agentMain = document.getElementById("agentMain");
  const productsTab = document.getElementById("toggleMarketTypeTabAgent");

  if (agentMain) agentMain.style.display = "flex";
  if (productsTab) productsTab.style.display = "none";

}


/* ================= GO BACK TO AGENT MARKET TYPES ================= */

function goBackToAgentMarketTypes() {

  const marketTab = document.getElementById("toggleMarketTypeTabAgent");
  const sourceTabs = document.querySelectorAll(".toggleMarketSourceTab");

  if (marketTab) marketTab.style.display = "block";
  sourceTabs.forEach(el => el.style.display = "none");

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
  const items = cartItemsContainer.querySelectorAll(".cart-item");

  if (items.length === 0) {
    if (emptyMsg) emptyMsg.style.display = "block";
    subtotalEl.textContent = "KES 0.00";
    totalEl.textContent = "KES 0.00";
    updateCartCount();
    return;
  }

  if (emptyMsg) emptyMsg.style.display = "none";

  items.forEach(item => {
    const price = Number(item.dataset.price);
    const qty = Number(item.querySelector(".qty-number").textContent);
    subtotal += price * qty;
  });

  // Format subtotal and total with commas and 2 decimals
  const formattedSubtotal = subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const formattedTotal = (subtotal + deliveryFee).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  subtotalEl.textContent = `KES ${formattedSubtotal}`;
  totalEl.textContent = `KES ${formattedTotal}`;
}

/* ---------- UPDATE CART COUNT ---------- */
function updateCartCount() {
  let count = 0;

  document.querySelectorAll(".cart-item").forEach(item => {
    count += Number(item.querySelector(".qty-number").textContent);
  });

  if (!cartCountEl) return;

  // ✅ Show 9+ if more than 9 items
  if (count > 9) {
    cartCountEl.textContent = "9+";
  } else {
    cartCountEl.textContent = count;
  }
}

/* ---------- ADD TO CART ---------- */
function addToCart(productId) {

  fetch("marketDisplay.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `action=add_to_cart&product_id=${productId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      loadCart();
    }
  });
}

document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
  btn.addEventListener("click", function() {

    const card = this.closest(".variable-card");
    const productId = card.dataset.id;

    addToCart(productId);
  });
});

function loadCart() {

  fetch("marketDisplay.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "action=fetch_cart"
  })
  .then(res => res.json())
  .then(data => {

    cartItemsContainer.innerHTML = "";

    if (!data.success || !data.items || data.items.length === 0) {
      cartItemsContainer.innerHTML = "";
      cartItemsContainer.style.display = "none";   // hide column
      emptyMsg.style.display = "block";
      updateTotals();
      updateCartCount();
      return;
    }

    emptyMsg.style.display = "none";
    cartItemsContainer.style.display = "block";

    data.items.forEach(item => {

      const div = document.createElement("div");
      div.className = "cart-item";

      // Keep numeric price in dataset for calculations
      div.dataset.price = parseFloat(item.price);

      // Format price for display
      const formattedPrice = parseFloat(item.price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      div.innerHTML = `
        <div class="cart-left">
          <img src="${item.image_path}">
          <div class="cart-info">
            <h4>${item.product_name}</h4>
            <p>KES ${formattedPrice}</p>
            <div class="remove-btn" onclick="removeItem(${item.product_id})">
              Remove
            </div>
          </div>
        </div>

        <div class="quantity-control">
          <button onclick="changeQty(${item.product_id}, ${item.quantity - 1})" class="qty-btn minus">-</button>
          <div class="qty-number">${item.quantity}</div>
          <button onclick="changeQty(${item.product_id}, ${item.quantity + 1})" class="qty-btn plus">+</button>
        </div>
      `;

      cartItemsContainer.appendChild(div);
    });

    updateTotals();
    updateCartCount();
  });
}

document.addEventListener("DOMContentLoaded", loadCart);

function removeItem(productId) {

  fetch("marketDisplay.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `action=remove_from_cart&product_id=${productId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) loadCart();
  });
}

function changeQty(productId, newQty) {

  if (newQty < 1) return;

  fetch("marketDisplay.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `action=update_quantity&product_id=${productId}&quantity=${newQty}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) loadCart();
  });
}

/* ---------- GLOBAL CLICK HANDLER ---------- */
document.addEventListener("click", e => {

  /* Remove item */
  if (e.target.classList.contains("remove-btn")) {
    e.target.closest(".cart-item")?.remove();
  }

  updateTotals();
  updateCartCount();
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

    // Save state
    localStorage.setItem("agentAddView", "add");

  } else {
    products.classList.add("active");
    addProducts.classList.remove("active");

    // Save state
    localStorage.setItem("agentAddView", "agency");
  }
}

document.addEventListener("DOMContentLoaded", () => {

  const savedView = localStorage.getItem("agentAddView");

  if (savedView === "add") {
    toggleAgentAdd(true);
  }

  if (savedView === "agency") {
    toggleAgentAdd(false);
  }

});

// ADMIN DASHBOARD JS
function toggleNavigationBar() {
  const navOverlay = document.getElementById("navOverlay");
  navOverlay.classList.toggle("active");
  document.querySelector('.navigation-bar')?.classList.toggle('show');
}

document.addEventListener("DOMContentLoaded", () => {

  // Only run if admin dashboard exists
  if (!document.querySelector(".admin-tab-panel")) return;

  const tabs = document.querySelectorAll(".nav-link");
  const navBtnTabs = document.querySelectorAll(".btn-edit");
  const panels = document.querySelectorAll(".admin-tab-panel");

  // ---------- RESTORE LAST TAB ----------
  const savedTab = localStorage.getItem("adminActiveTab");

  if (savedTab) {
    tabs.forEach(t => t.classList.remove("active"));
    panels.forEach(p => p.classList.remove("active"));

    const savedTabBtn = document.querySelector(`.nav-link[data-tab="${savedTab}"]`);
    const savedPanel = document.querySelector(`.admin-tab-panel[data-tab="${savedTab}"]`);

    savedTabBtn?.classList.add("active");
    savedPanel?.classList.add("active");
  }

  // ---------- TAB CLICK ----------
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
      window.scrollTo({ top: 0, behavior: "smooth" });

      // SAVE LAST TAB
      localStorage.setItem("adminActiveTab", target);

      toggleNavigationBar?.();
    });
  });
  navBtnTabs.forEach(tab => {
    tab.addEventListener("click", e => {
      e.preventDefault();

      const target = tab.dataset.tab;
      if (!target) return;
      panels.forEach(p => p.classList.remove("active"));

      document
        .querySelector(`.admin-tab-panel[data-tab="${target}"]`)
        ?.classList.add("active");
      window.scrollTo({ top: 0, behavior: "smooth" });  

      // SAVE LAST TAB
      localStorage.setItem("adminActiveTab", target);
    });
  });

});
document.addEventListener("click", function(e){

const btn = e.target.closest(".action-btn");
if(!btn) return;

const actionsUserId = btn.dataset.userId;
const action = btn.dataset.action;
console.log(actionsUserId, action);

if(!actionsUserId || !action) return;

/* CONFIRMATION MESSAGE */

let message = "";

if(action === "suspend") message = "Suspend this agent?";
if(action === "restore") message = "Restore this agent?";
if(action === "activate") message = "Activate this agent?";
if(action === "deactivate") message = "Deactivate this agent?";
if(action === "delete") message = "Delete this agent permanently?";

if(!confirm(message)) return;


/* AJAX REQUEST */

fetch("adminPage.php",{
method:"POST",
headers:{
"Content-Type":"application/x-www-form-urlencoded",
"X-Requested-With":"XMLHttpRequest"
},
body: new URLSearchParams({
  user_id: actionsUserId,
  action: action
})
})
.then(res=>res.json())
.then(data=>{

if(!data.success){
alert(data.error || "Action failed");
return;
}

const row = btn.closest("tr");
const badge = row.querySelector(".badge");
const actionsCell = row.querySelector(".actions div");

/* DELETE ROW */

if(action === "delete"){
row.remove();
return;
}


/* UPDATE STATUS BADGE */

if(action === "suspend"){
badge.textContent="Suspended";
badge.className="badge suspendedSpan";
}

if(action === "restore"){
badge.textContent="Unverified";
badge.className="badge unverified";
}

if(action === "activate"){
badge.textContent="Verified";
badge.className="badge verified";
}

if(action === "deactivate"){
badge.textContent="Unverified";
badge.className="badge unverified";
}


/* SWITCH BUTTONS */

if(action === "suspend"){
btn.outerHTML=`<button class="btn-restore action-btn"
data-action="restore"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-trash-can-arrow-up"></i></button>`;
}

if(action === "restore"){
btn.outerHTML=`<button class="btn-suspend action-btn"
data-action="suspend"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-ban"></i></button>`;
}

if(action === "activate"){
btn.outerHTML=`<button class="btn-deactivate action-btn"
data-action="deactivate"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-toggle-off"></i> Deactivate
</button>`;
}

if(action === "deactivate"){
btn.outerHTML=`<button class="btn-activate action-btn"
data-action="activate"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-toggle-on"></i> Activate
</button>`;
}

})
.catch(()=>{
alert("Network error");
});

});

document.addEventListener("click", function(e){

const btn = e.target.closest(".copy-link-btn");
if(!btn) return;

const ref = btn.dataset.ref;

const link = `http://localhost/MaketHub.com-Project/agentRegister.php?ref=${ref}`;

navigator.clipboard.writeText(link)
.then(()=>{

const original = btn.innerHTML;

btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';

setTimeout(()=>{
btn.innerHTML = original;
},2000);

})
.catch(()=>{
alert("Failed to copy link");
});

});

// EDIT FUNCTION - No page reload

const allowedTypes = ["agent","buyer","seller","owner","product"];

function editRecord(type, id) {
  if (!Number.isInteger(id) || id <= 0) return;
  if (!allowedTypes.includes(type)) return;

  const activePanel = document.querySelector(".admin-tab-panel.active");

  if (activePanel) {
    localStorage.setItem("previousAdminTab", activePanel.dataset.tab);
  }

  localStorage.setItem("activeForm", type);
  localStorage.setItem("editId", id);

  window.location.href = "adminPage.php?type=" + type + "&id=" + id;
}

// RESTORE FORM AFTER PAGE RELOAD
document.addEventListener("DOMContentLoaded", function () {
  const activeForm = localStorage.getItem("activeForm");
  const editId = localStorage.getItem("editId");

  if (activeForm && allowedTypes.includes(activeForm)) {
    allowedTypes.forEach(t => {
      const f = document.getElementById(t + "-edit-form");
      if (f) f.style.display = "none";
    });

    const form = document.getElementById(activeForm + "-edit-form");
    if (form) form.style.display = "flex";

    // Restore URL if missing
    const params = new URLSearchParams(window.location.search);
    if (!params.get("id") && editId) {
      window.history.replaceState(null, "", "adminPage.php?type=" + activeForm + "&id=" + editId);
    }
  }
});

function copyAgencyLink() {
  const input = document.getElementById("agencyLinkInput");
  const icon = document.querySelector(".agency_link i");
  const text = input.value;

  navigator.clipboard.writeText(text).then(() => {
    icon.classList.remove("fa-copy");
    icon.classList.add("fa-check");

    setTimeout(() => {
      icon.classList.remove("fa-check");
      icon.classList.add("fa-copy");
    }, 1500);
  });
}

function copyAgencyCode() {
  const input = document.getElementById("agencyCodeInput");
  const icon = document.querySelector(".agency_code i");
  const text = input.value;

  navigator.clipboard.writeText(text).then(() => {
    icon.classList.remove("fa-copy");
    icon.classList.add("fa-check");

    setTimeout(() => {
      icon.classList.remove("fa-check");
      icon.classList.add("fa-copy");
    }, 1500);
  });
}

// ------------------------------
// SHARE MENU TOGGLE
// ------------------------------

function toggleShareMenu(){

  const menu = document.getElementById("shareMenu");

  menu.style.display =
    menu.style.display === "block"
    ? "none"
    : "block";

}



// ------------------------------
// GET REFERRAL LINK
// ------------------------------

function getAgencyLink(){

  return document.getElementById("agencyLinkInput").value;

}



// ------------------------------
// WHATSAPP SHARE
// ------------------------------

function shareWhatsApp(){

  const link = getAgencyLink();

  const message =
  "Join Maket Hub as an Agent and start earning commissions! 🚀\n\n" + link;

  const url =
  "https://wa.me/?text=" + encodeURIComponent(message);

  window.open(url, "_blank");

}



// ------------------------------
// FACEBOOK SHARE
// ------------------------------

function shareFacebook(){

  const link = getAgencyLink();

  const url =
  "https://www.facebook.com/sharer/sharer.php?u=" +
  encodeURIComponent(link);

  window.open(url, "_blank");

}



// ------------------------------
// TWITTER (X)
// ------------------------------

function shareTwitter(){

  const link = getAgencyLink();

  const text =
  "Join Maket Hub as an agent and earn commissions!";

  const url =
  "https://twitter.com/intent/tweet?text=" +
  encodeURIComponent(text) +
  "&url=" +
  encodeURIComponent(link);

  window.open(url, "_blank");

}



// ------------------------------
// EMAIL SHARE
// ------------------------------

function shareEmail(){

  const link = getAgencyLink();

  const subject =
  "Join Maket Hub Agent Program";

  const body =
  "I invite you to join Maket Hub and earn commissions.\n\nRegister here:\n" +
  link;

  window.location.href =
  "mailto:?subject=" +
  encodeURIComponent(subject) +
  "&body=" +
  encodeURIComponent(body);

}



// ------------------------------
// NATIVE MOBILE SHARE
// ------------------------------

function shareNative(){

  const link = getAgencyLink();

  const text =
  "Join Maket Hub and start earning commissions.";

  if(navigator.share){

    navigator.share({
      title: "Maket Hub Agent",
      text: text,
      url: link
    });

  }else{

    alert("Sharing not supported on this device.");

  }

}

document.getElementById("goBackBtn")?.addEventListener("click", function () {

  const previousTab = localStorage.getItem("previousAdminTab");

  if (!previousTab) return;

  // Restore tab
  localStorage.setItem("adminActiveTab", previousTab);

  // Clear edit state
  localStorage.removeItem("activeForm");
  localStorage.removeItem("editId");

  // Reload page
  window.location.href = "adminPage.php";
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



// ==============================
// DAILY PRODUCTS CONFIGURATION
// ==============================

const dailyProducts = [
  {
    id: 1,
    name: "Executive Leather Laptop Bag",
    price: "KES 1,300",
    description: "Premium executive laptop bag. Durable and stylish.",
    image: "Images/Executive Leather Laptop Bag.png"
  },
  {
    id: 2,
    name: "6 Litre Electric Pressure Cooker",
    price: "KES 5,200",
    description: "Fast cooking, energy saving, perfect for family meals.",
    image: "Images/6 Litre Electric Pressure Cooker.png"
  },
  {
    id: 3,
    name: "16-inch Standing Fan",
    price: "KES 2,350",
    description: "Powerful airflow with adjustable height.",
    image: "Images/Ipcone 16-inch standing fan.png"
  },
  {
    id: 4,
    name: "Large Travel Duffel Bag",
    price: "KES 1,250",
    description: "Spacious travel bag. Ideal for weekend trips.",
    image: "Images/Large Travel Duffel Bag.png"
  },
  {
    id: 5,
    name: "Velvet Curtains",
    price: "KES 2,700",
    description: "Elegant home curtains. Premium soft material.",
    image: "Images/Velvet Curtains.png"
  }
];

// ==============================
// RENDER PRODUCTS
// ==============================

document.addEventListener("DOMContentLoaded", () => {

  const container = document.getElementById("productsContainer");
  if (!container) return;   // ✅ HARD GUARD – prevents crash

  const today = new Date().getDay();

  dailyProducts.forEach(product => {

    const card = document.createElement("div");
    card.className = "product-card";

    card.innerHTML = `
      <img src="${product.image}" alt="${product.name}">
      <div class="product-name">${product.name}</div>
      <div class="product-price">${product.price}</div>
      <div class="product-description">${product.description}</div>
      <button class="download-btn" data-id="${product.id}">
        Download for Posting
      </button>
    `;

    container.appendChild(card);
  });

});

// ==============================
// DOWNLOAD + SAVE TO LOCAL STORAGE
// ==============================

document.addEventListener("click", function(e) {

  if (!e.target.classList.contains("download-btn")) return;
  if (today === 0) return;

  const id = parseInt(e.target.dataset.id);
  const product = dailyProducts.find(p => p.id === id);

  // Save individually
  localStorage.setItem(
    "marketHubDailyProduct_" + product.id,
    JSON.stringify(product)
  );

  // Trigger image download
  const link = document.createElement("a");
  link.href = product.image;
  link.download = product.name.replace(/\s+/g, "_") + ".jpg";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  alert(product.name + " saved locally. Ready for posting.");

});

// PLACE ORDER JS
function toggleMarketDisplayVSOrder() {
  const sellerContainer = document.querySelector('.sellerProfileContainer');
  const tabsContainer   = document.querySelector('.tabs-container');
  const orderContainer  = document.querySelector('.order-container');

  // Cart + overlays
  const cartContainer   = document.getElementById('cart-container');
  const cartOverlay     = document.getElementById('cartOverlay');
  const payOverlay      = document.getElementById('payOverlay');

  if (!sellerContainer || !tabsContainer || !orderContainer) return;

  const isMarketVisible =
      window.getComputedStyle(sellerContainer).display !== 'none';

  /* ================= SWITCH TO ORDER VIEW ================= */
  if (isMarketVisible) {

      // 🔴 Close cart if open
      if (cartContainer) cartContainer.classList.remove('show');
      if (cartOverlay)   cartOverlay.classList.remove('active');
      if (payOverlay)    payOverlay.classList.remove('active');

      // 🔴 Restore scroll
      document.body.style.overflow = '';

      // 🔴 Toggle main sections
      sellerContainer.style.display = 'none';
      tabsContainer.style.display   = 'none';
      orderContainer.style.display  = 'grid';

  }
  /* ================= SWITCH BACK TO MARKET VIEW ================= */
  else {

      sellerContainer.style.display = 'flex';
      tabsContainer.style.display   = 'block';
      orderContainer.style.display  = 'none';
  }
}

function goBackHandler() {
  const sellerContainer = document.querySelector('.sellerProfileContainer');
  const orderContainer  = document.querySelector('.order-container');

  if (!sellerContainer || !orderContainer) return;

  // Check if order page is currently visible
  const orderVisible = window.getComputedStyle(orderContainer).display !== 'none';

  if (orderVisible) {
    // Toggle back to market display
    toggleMarketDisplayVSOrder();

    // Reload market page after toggling to update cart
    setTimeout(() => {
      location.reload();
    }, 50); // small delay to ensure toggle finishes
  } else {
    // Normal back behavior if not coming from order page
    window.history.back();
  }
}

function buyNow(button) {
  const productId   = button.dataset.id;
  const name        = button.dataset.name;
  const price       = parseFloat(button.dataset.price);
  const image       = button.dataset.image;
  const sellerId    = button.dataset.seller;
  const sellerName  = button.dataset.sellerName;

  toggleMarketDisplayVSOrder();

  const card = document.querySelectorAll(".card")[1];
  if (!card) return;

  // Format price with commas and 2 decimals
  const formattedPrice = price.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  // Individual product with remove button
  const sellerContent = `
    <div class="seller-box">
      <div class="seller-header">Seller: ${sellerName}</div>
      <div class="seller-meta">Estimated delivery: 3 hours</div>
      <div class="product" data-price="${price}" data-product="${productId}" data-seller="${sellerId}">
        <div class="product-left">
          <img src="${image}" width="60">
          <div class="product-info">
            ${name}
            <div class="product-price">
              KSh <span class="item-subtotal">${formattedPrice}</span>
            </div>
          </div>
        </div>
        <div class="qty-and-delete">
          <div class="quantity-control">
            <button class="qty-btn minus">-</button>
            <div class="qty-number">1</div>
            <button class="qty-btn plus">+</button>
          </div>
          <button class="remove-product" data-product="${productId}"><i class="fa-solid fa-trash-can"></i></button>
        </div>
      </div>
    </div>
  `;

  card.innerHTML = `
    <div class="card-title">
      <p>
        Order Summary<br>
        <span>Item(s): <strong id="orderItemCount">1</strong></span>
      </p>
    </div>
    ${sellerContent}
    <div class="guarantee">
      Maket Hub · your number one marketplace.
    </div>
  `;

  // Store selected order globally
  window.selectedOrder = {
    product_id: productId,
    seller_id: sellerId,
    quantity: 1,
    price: price
  };

  updateOrderSummary();

  // Add product to backend cart
  fetch("marketDisplay.php", {
    method: "POST",
    headers: {"Content-Type":"application/x-www-form-urlencoded"},
    body: `action=add_to_cart&product_id=${productId}`
  });
}

function placeOrder() {
  const products = document.querySelectorAll(".order-container .product");

  if (products.length === 0) {
      alert("No products selected");
      return;
  }

  // Gather all products into an array
  const orderItems = [];
  let totalAmount = 0;

  products.forEach(productEl => {
      const product_id = productEl.dataset.product;
      const seller_id  = productEl.dataset.seller;
      const quantity   = parseInt(productEl.querySelector(".qty-number").textContent);
      const price      = parseFloat(productEl.dataset.price);

      if (!product_id || !seller_id || quantity < 1) {
          alert("Invalid product in order");
          return;
      }

      totalAmount += price * quantity;

      orderItems.push({
          product_id,
          seller_id,
          quantity,
          price
      });
  });

  const formData = new URLSearchParams();
  formData.append("action", "place_order_multi");
  formData.append("total_amount", totalAmount);

  formData.append("items", JSON.stringify(orderItems));

  fetch("marketDisplay.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formData.toString()
  })
  .then(res => res.json())
  .then(data => {
      if (data.success) {
          alert("Order placed successfully! Code: " + data.order_code);
          location.reload();
      } else {
          alert(data.error || "Order failed");
      }
  });
}

// ===== Quantity change handler (Buy Now + Cart) =====
document.addEventListener("click", function(e){
  const btn = e.target.closest(".minus, .plus");
  if (!btn) return;

  const productEl = btn.closest(".product");
  if (!productEl) return;

  const qtyEl = productEl.querySelector(".qty-number");
  let quantity = parseInt(qtyEl.textContent);
  const productId = productEl.dataset.product;

  if (btn.classList.contains("plus")) quantity++;
  if (btn.classList.contains("minus") && quantity > 1) quantity--;

  qtyEl.textContent = quantity;

  // ===== Update Buy Now =====
  if (window.selectedOrder && productId == window.selectedOrder.product_id) {
    window.selectedOrder.quantity = quantity;
    updateOrderSummary();

    // Update backend
    fetch("marketDisplay.php", {
      method: "POST",
      headers: {"Content-Type":"application/x-www-form-urlencoded"},
      body: `action=update_quantity&product_id=${productId}&quantity=${quantity}`
    });
    return;
  }

  // ===== Update Cart checkout =====
  updateCheckoutSummary();
  fetch("marketDisplay.php", {
    method: "POST",
    headers: {"Content-Type":"application/x-www-form-urlencoded"},
    body: `action=update_quantity&product_id=${productId}&quantity=${quantity}`
  });
});

// ===== Update Buy Now summary =====
function updateOrderSummary() {
  if (!window.selectedOrder) return;

  const { quantity, price } = window.selectedOrder;
  const itemsTotal = price * quantity;

  const itemsTotalEl = document.getElementById("itemsTotal");
  const finalTotalEl = document.getElementById("finalTotal");
  const orderCountEl = document.getElementById("orderItemCount");

  // Format number with commas and 2 decimals
  const formattedTotal = itemsTotal.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  if (itemsTotalEl) itemsTotalEl.textContent = `KSh ${formattedTotal}`;
  if (finalTotalEl) finalTotalEl.textContent = `KSh ${formattedTotal}`;
  if (orderCountEl) orderCountEl.textContent = quantity;
}

// ===== Update Cart summary =====
function updateCheckoutSummary() {
  const products = document.querySelectorAll("#dynamicOrderBox .product");
  let grandTotal = 0, totalItems = 0;

  products.forEach(product => {
    const price = parseFloat(product.dataset.price);
    const qty = parseInt(product.querySelector(".qty-number").textContent);
    grandTotal += price * qty;
    totalItems += qty;
  });

  const itemsTotalEl = document.getElementById("itemsTotal");
  const finalTotalEl = document.getElementById("finalTotal");
  const orderCountEl = document.getElementById("orderItemCount");

  // Format grand total with commas and 2 decimals
  const formattedTotal = grandTotal.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  if (itemsTotalEl) itemsTotalEl.textContent = `KSh ${formattedTotal}`;
  if (finalTotalEl) finalTotalEl.textContent = `KSh ${formattedTotal}`;
  if (orderCountEl) orderCountEl.textContent = totalItems;
}

function proceedFromCart() {
  window.selectedOrder = null;

  fetch("marketDisplay.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "action=fetch_cart"
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success || data.items.length === 0) {
      alert("Cart is empty");
      return;
    }

    toggleMarketDisplayVSOrder();

    const grouped = {};
    data.items.forEach(item => {
      if (!grouped[item.seller_id]) {
        grouped[item.seller_id] = {
          seller_name: item.business_name,
          items: []
        };
      }
      grouped[item.seller_id].items.push(item);
    });

    let grandTotal = 0;
    let totalItems = 0;
    let allSellersContent = "";

    // Loop over each seller
    for (let sellerId in grouped) {
      const sellerGroup = grouped[sellerId];

      let sellerProductsContent = "";
      sellerGroup.items.forEach(product => {
        const price = parseFloat(product.price) || 0; // convert to number
        grandTotal += price * product.quantity;
        totalItems += product.quantity;

        // Format price with commas and 2 decimals
        const formattedPrice = price.toLocaleString('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });

        sellerProductsContent += `
          <div class="product" data-price="${price}" data-product="${product.product_id}" data-seller="${sellerId}">
            <div class="product-left">
              <img src="${product.image_path}" width="60">
              <div class="product-info">
                ${product.product_name}
                <div class="product-price">KSh ${formattedPrice}</div>
              </div>
            </div>
            <div class="qty-and-delete">
              <div class="quantity-control">
                <button class="qty-btn minus">-</button>
                <div class="qty-number">${product.quantity}</div>
                <button class="qty-btn plus">+</button>
              </div>
              <button class="remove-product" data-product="${product.product_id}">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </div>
        `;
      });
      
      // Wrap this seller's products inside its own seller-box
      allSellersContent += `
        <div class="seller-box">
          <div class="seller-header">Seller: ${sellerGroup.seller_name.toUpperCase()}</div>
          <div class="seller-meta">Estimated delivery: 3 hours</div>
          ${sellerProductsContent}
        </div>
      `;
    }

    // Update the card content
    const card = document.querySelectorAll(".card")[1];
    if (card) {
      card.innerHTML = `
        <div class="card-title">
          <p>
            Order Summary<br>
            <span>Item(s): <strong id="orderItemCount">${totalItems}</strong></span>
          </p>
        </div>

        <div id="dynamicOrderBox">
          ${allSellersContent}
        </div>

        <div class="guarantee">
          Maket Hub · your number one marketplace.
        </div>
      `;
    }

    // Format grand total for display
    const formattedGrandTotal = grandTotal.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    if (document.getElementById("itemsTotal"))
      document.getElementById("itemsTotal").textContent = `KSh ${formattedGrandTotal}`;
    if (document.getElementById("finalTotal"))
      document.getElementById("finalTotal").textContent = `KSh ${formattedGrandTotal}`;
  });
}

function updateQuantity(productId, newQty) {

  fetch("orders.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: `action=update_quantity&product_id=${productId}&quantity=${newQty}`
  })
  .then(res => res.json())
  .then(data => {
    if(data.success){
        updateCheckoutSummary(); // recalc totals
    }
  });
}

// ===== Remove product handler =====
document.addEventListener("click", function(e){
  const removeBtn = e.target.closest(".remove-product");
  if (!removeBtn) return;

  const productEl = removeBtn.closest(".product");
  if (!productEl) return;

  const productId = productEl.dataset.product;

  // Remove visually
  productEl.remove();

  // Clear Buy Now order if matches
  if (window.selectedOrder && productId == window.selectedOrder.product_id) {
    window.selectedOrder = null;
    updateOrderSummary();
  }

  // Remove from backend
  fetch("marketDisplay.php", {
    method: "POST",
    headers: {"Content-Type":"application/x-www-form-urlencoded"},
    body: `action=remove_from_cart&product_id=${productId}`
  });

  // Update Cart summary
  updateCheckoutSummary();
});

/* ===============================
AGENT ALERT VERIFICATION POPUP JS
================================ */



/* SHOW POPUP AFTER 30 SECONDS */

/* setTimeout(()=>{

  document.getElementById("alertPopupOverlay").style.display="flex"
  document.body.classList.add("no-scroll");

},10000) */


/* VIOLENT SHAKE IF OVERLAY CLICKED */

document.getElementById("alertPopupOverlay").addEventListener("click",function(e){

  if(e.target.id === "alertPopupOverlay"){

  let alertPopup = document.getElementById("alert-popup")

  alertPopup.classList.add("shake")

  setTimeout(()=>{

  alertPopup.classList.remove("shake")

  },500)

  }

})
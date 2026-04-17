console.log("General JS loaded");

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
  if (!target) return;
    target.classList.toggle("active");
    btn.textContent = target.classList.contains("active")
      ? "Hide details"
      : "View details";
  });
});

/* ================== HELPER FUNCTION ================== */
function resetScrollFor() {
  window.scrollTo({
    top: 0,
    left: 0,
    behavior: 'auto' // change to 'smooth' if you want smooth scrolling
  });
}

function toggleOrderMarket() {
  const orderMain = document.getElementById("orderMain");
  const marketMain = document.getElementById("marketMain");

  const isMarketVisible = marketMain.style.display !== "none";

  marketMain.style.display = isMarketVisible ? "none" : "flex";
  orderMain.style.display = isMarketVisible ? "flex" : "none";

  if (!isMarketVisible) resetScrollFor(marketMain);
  else resetScrollFor(orderMain);
}

function toggleSellerOrdersTrack() {
  const sellerMain = document.getElementById("sellerMain");
  const ordersTrackMain = document.getElementById("ordersTrackMain");

  const isSellerVisible = sellerMain.style.display !== "none";

  sellerMain.style.display = isSellerVisible ? "none" : "flex";
  ordersTrackMain.style.display = isSellerVisible ? "flex" : "none";

  if (!isSellerVisible) resetScrollFor(ordersTrackMain);
  else resetScrollFor(sellerMain);
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

  if (!isOrderVisible) resetScrollFor(agentMain);
  else resetScrollFor(orderMain);
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

  // ✅ Reset scroll
  if (!isEarningsVisible) resetScrollFor(earningsTrackMain);
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

  if (!isWithdrawalVisible) resetScrollFor(agentWithdrawalH);
  else resetScrollFor(agentMain);
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
    resetScrollFor(typeTab);
  }

  if (target === "source") {
    typeTab.style.display = "none";
    sourceTabs.forEach(el => el.style.display = "block");
    resetScrollFor(...sourceTabs);
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
    resetScrollFor(typeTab);
  }

  if (target === "source") {
    typeTab.style.display = "none";
    sourceTabs.forEach(el => el.style.display = "block");
    resetScrollFor(...sourceTabs);
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



  // ✅ Reset scroll when switching tabs
  resetScrollFor(panel);

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

  /* ✅ FIX: hide all other containers first */
  document.querySelectorAll(".toggleMarketSourceTab")
    .forEach(c => c.style.display = "none");

  /* ✅ show only the current one */
  container.style.display = "block";

  /* activate tab */
  container.querySelectorAll("[data-tab]").forEach(b => b.classList.remove("active"));
  container.querySelectorAll(".tab-panel-msource").forEach(p => p.classList.remove("active"));

  btn.classList.add("active");
  panel.classList.add("active");

  resetScrollFor(panel);

  const storageKey = container.dataset.tabStorage;
  if (storageKey) localStorage.setItem(storageKey, sourceTabId);
}

/* ================= OPEN AGENT MARKET TYPE ================= */

function openMarketType(typeTabId = "products") {

  const toggleAgentTab = document.getElementById("toggleAgentTab");
  const marketContainer = document.getElementById("toggleMarketTypeTabAgent");

  if (toggleAgentTab) toggleAgentTab.style.display = "none";
  if (marketContainer) marketContainer.style.display = "block";

  const btn = document.querySelector(`.tab-btn-mtype[data-tab="${typeTabId}"]`);
  const panel = document.getElementById(typeTabId);

  if (!btn || !panel) return;

  const container = btn.closest(".tabs-container");

  container.querySelectorAll("[data-tab]").forEach(b => b.classList.remove("active"));
  container.querySelectorAll(".tab-panel-mtype").forEach(p => p.classList.remove("active"));

  btn.classList.add("active");
  panel.classList.add("active");

  resetScrollFor(panel);

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

  const toggleAgentTab = document.getElementById("toggleAgentTab");
  const productsTab = document.getElementById("toggleMarketTypeTabAgent");

  if (toggleAgentTab) toggleAgentTab.style.display = "block";
  if (productsTab) productsTab.style.display = "none";
}


/* ================= GO BACK TO AGENT MARKET TYPES ================= */

function goBackToAgentMarketTypes() {

  const marketTab = document.getElementById("toggleMarketTypeTabAgent");
  const sourceTabs = document.querySelectorAll(".toggleMarketSourceTab");

  if (marketTab) marketTab.style.display = "block";
  sourceTabs.forEach(el => el.style.display = "none");

  resetScrollFor(marketTab, ...sourceTabs);

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
  if (!cartItemsContainer || !subtotalEl || !totalEl) return;

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
  if (!cartItemsContainer || !cartCountEl) return;
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

    const duration = 3000;

    if (data.success) {
      showNotification(
        `<i class="fa-solid fa-cart-plus"></i> Added to cart successfully!`,
        duration,
        "success"
      );

      loadCart();

    } else if (data.error && data.error.trim() !== "") {
      showNotification(
        `<i class="fa-solid fa-triangle-exclamation"></i> ${data.error}`,
        duration,
        "warning"
      );

    } else {
      showNotification(
        `<i class="fa-solid fa-triangle-exclamation"></i> Failed to add to cart!`,
        duration,
        "warning"
      );
    }

  })
  .catch(() => {
    showNotification(
      `<i class="fa-solid fa-wifi"></i> Network error!`,
      3000,
      "error"
    );
  });
}

document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
  btn.addEventListener("click", function() {

    const card = this.closest(".variable-card");
    if (!card) return; // ✅ prevent crash

    const productId = card.dataset?.id;
    if (!productId) return; // ✅ extra safety

    addToCart(productId);
  });
});

function loadCart() {
  if (!cartItemsContainer || !emptyMsg) return;

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

    // 👉 Handle minus button state for all cart items
    const cartItems = cartItemsContainer.querySelectorAll(".cart-item");

    cartItems.forEach(item => {
      const minusBtn = item.querySelector(".qty-btn.minus");
      const plusBtn  = item.querySelector(".qty-btn.plus");
      const qtyDisplay = item.querySelector(".qty-number");

      function syncMinusState() {
        if (parseInt(qtyDisplay.textContent) <= 1) {
          minusBtn.classList.add("disabled");
          minusBtn.disabled = true;
        } else {
          minusBtn.classList.remove("disabled");
          minusBtn.disabled = false;
        }
      }

      // Initial check
      syncMinusState();

      // After clicking minus
      minusBtn.addEventListener("click", () => {
        setTimeout(syncMinusState, 0);
      });

      // After clicking plus
      plusBtn.addEventListener("click", () => {
        setTimeout(syncMinusState, 0);
      });
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
    showNotification(`<i class="fa-solid fa-triangle-exclamation"></i> Item removed from cart!`, 2000, "warning");
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
    localStorage.setItem("seller:productsView", "add");
  } else {
    addPanel.classList.remove("active");
    productsPanel.classList.add("active");
    localStorage.setItem("seller:productsView", "list");
  }
}

function goBackToSellerPage() {
  toggleProductsAdd(false);
  setTimeout(() => {
      window.location.href = 'sellerPage.php';
  }, 50);
}

document.addEventListener("DOMContentLoaded", () => {
  setTimeout(() => {
    const productsPanel = document.getElementById("products");

    if (productsPanel?.classList.contains("active")) {
      const savedView = localStorage.getItem("seller:productsView");

      toggleProductsAdd(savedView === "add");
    }
  }, 50);
});

function toggleAgentAdd(showAdd) {
  const agencyPanel = document.getElementById("my-agency");
  const addAgentPanel = document.getElementById("add-agent");

  // ✅ STOP if elements don't exist
  if (!agencyPanel || !addAgentPanel) return;

  if (showAdd) {
    agencyPanel.classList.remove("active");
    addAgentPanel.classList.add("active");
    localStorage.setItem("agentAddView", "add");

  } else {
    agencyPanel.classList.add("active");
    addAgentPanel.classList.remove("active");
    localStorage.setItem("agentAddView", "my-agency");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  setTimeout(() => {
    const agencyPanel = document.getElementById("my-agency");

    if (agencyPanel?.classList.contains("active")) {
      const savedView = localStorage.getItem("agentAddView");

      toggleAgentAdd(savedView === "add");
    }
  }, 50);

});

// ADMIN DASHBOARD JS
function toggleNavigationBar() {
  const navOverlay = document.getElementById("navOverlay");
  navOverlay.classList.toggle("active");
  document.querySelector('.navigation-bar')?.classList.toggle('show');
}

const popup = document.getElementById("confirmation-popup");
const popupOverlay = document.getElementById("confirmation-popup-overlay");
const popupTitle = document.getElementById("popupTitle");
const popupMessage = document.getElementById("popupMessage");
const confirmBtn = document.getElementById("confirmAction");
const cancelBtn = document.getElementById("cancelAction");

// ✅ Safe event binding
if (popup) {
  popup.onclick = (e) => e.stopPropagation();
}

function showPopup(title, message, onConfirm) {

  if (!popup || !popupOverlay || !popupTitle || !popupMessage || !confirmBtn || !cancelBtn) return;

  popupTitle.textContent = title;
  popupMessage.textContent = message;

  popup.classList.add("active");
  popupOverlay.classList.add("active");

  confirmBtn.onclick = null;
  cancelBtn.onclick = null;
  popupOverlay.onclick = null;

  confirmBtn.onclick = () => {
    closePopup();
    onConfirm();
  };

  cancelBtn.onclick = closePopup;
  popupOverlay.onclick = closePopup;
}

function closePopup() {
  if (!popup || !popupOverlay) return;

  popup.classList.remove("active");
  popupOverlay.classList.remove("active");
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

  if(action === "suspend") message = "Suspend this user?";
  if(action === "restore") message = "Restore this user?";
  if(action === "activate") message = "Activate this agent?";
  if(action === "deactivate") message = "Deactivate this agent?";
  if(action === "delete") message = "Delete this user permanently?";

  let title = "Confirm Action";

  if(action === "delete") title = "Delete User";
  if(action === "suspend") title = "Suspend User";
  if(action === "restore") title = "Restore User";
  if(action === "activate") title = "Activate Agent";
  if(action === "deactivate") title = "Deactivate Agent";

showPopup(title, message, () => {

  /* AJAX REQUEST */

  fetch("user_functionalities.php",{
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

/* ===== UPDATE ECONOMIC + SUB AGENTS ===== */

const subAgentsCell = row.querySelector(".sub-agents");
const economicCell = row.querySelector(".economic");

if (data.total_sub_agents !== undefined && subAgentsCell) {
  subAgentsCell.textContent = data.total_sub_agents;
}

if (data.economic_period_count !== undefined && economicCell) {
  economicCell.textContent = data.economic_period_count;
}

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

});
document.addEventListener("click", function(e){

const btn = e.target.closest(".copy-link-btn");
if(!btn) return;

const ref = btn.dataset.ref;

const link = `https://makethub.shop/agentregister.php?ref=${ref}`;

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

function openAddProductForm(type) {
  if (!allowedTypes.includes(type)) return;

  const activePanel = document.querySelector(".admin-tab-panel.active");

  if (activePanel) {
    localStorage.setItem("previousAdminTab", activePanel.dataset.tab);
  }
  localStorage.setItem("activeForm", type);
  localStorage.removeItem("editId");

  window.location.href = "adminPage.php?type=product";
}

// Delete Makethub Products JS
document.addEventListener("click", function(e){

  const btn = e.target.closest(".delete-product-btn");
  if (!btn) return;

  const productId = btn.dataset.productId;
  if (!productId) return;

  showPopup("Delete Product", "Delete this product permanently?", () => {

    fetch("adminPage.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: new URLSearchParams({
        ajax_delete_product: 1,
        product_id: productId
      })
    })
    .then(res => res.json())
    .then(data => {

      if (!data.success) {
        alert(data.error || "Delete failed");
        return;
      }

      try {
        const card = btn.closest(".product-card");

        if (card) {
          const grid = card.closest(".products-grid-admin");

          card.remove();

          if (grid && grid.querySelectorAll(".product-card").length === 0) {
            grid.innerHTML = '<p class="noproducts-admin-p">No products in this category.</p>';
          }
        }

        setTimeout(() => {
          location.reload();
        }, 1000);

      } catch (err) {
        console.error("DOM error:", err);
      }

    })
    .catch(err => {
      console.error("FETCH ERROR:", err);
      alert("Network error");
    });

  });

});

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
  "Join Makethub as an Agent and start earning commissions! 🚀\n\n" + link;

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
  "Join Makethub as an agent and earn commissions!";

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
  "Join Makethub Agent Program";

  const body =
  "I invite you to join Makethub and earn commissions.\n\nRegister here:\n" +
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
  "Join Makethub and start earning commissions.";

  if(navigator.share){

    navigator.share({
      title: "Makethub Agent",
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

// WALLET TOGGLE + LOCALSTORAGE
function initWallets() {
  const walletSelect = document.querySelector("#funds .walletChange");
  const salesWallet = document.getElementById("salesWallet");
  const agencyWallet = document.getElementById("agencyWallet");

  if (!walletSelect || !salesWallet || !agencyWallet) return; // stop if missing

  // Get input fields inside wallets
  const salesInput = salesWallet.querySelector('input[name="withdraw_amount"]');
  const agencyInput = agencyWallet.querySelector('input[name="withdraw_amount"]');
  if (walletSelect && salesWallet && agencyWallet) {
    
    function showWallet(selected) {
      if (selected === "sales") {
          salesWallet.classList.add("active");
          agencyWallet.classList.remove("active");
          salesWallet.querySelector("input").disabled = false;
          agencyWallet.querySelector("input").disabled = true;
      } else {
          agencyWallet.classList.add("active");
          salesWallet.classList.remove("active");
          agencyWallet.querySelector("input").disabled = false;
          salesWallet.querySelector("input").disabled = true;
      }
      // Save selected wallet to localStorage
      localStorage.setItem("selectedWallet", selected);
    }

    // Restore selected wallet from localStorage
    const savedWallet = localStorage.getItem("selectedWallet") || (walletSelect ? walletSelect.value : "sales");
    if(walletSelect) {
      walletSelect.value = savedWallet;
      showWallet(savedWallet);
    }

    // Listen for changes
    if(walletSelect){
      walletSelect.addEventListener("change", () => {
        showWallet(walletSelect.value);
      });
    }

    // Restore input values from localStorage
    if(salesInput) salesInput.value = localStorage.getItem("salesAmount") || "";
    if(agencyInput) agencyInput.value = localStorage.getItem("agencyAmount") || "";

    // Save input values to localStorage on change
    if(salesInput) salesInput.addEventListener("input", () => {
      localStorage.setItem("salesAmount", salesInput.value);
    });
    if(agencyInput) agencyInput.addEventListener("input", () => {
      localStorage.setItem("agencyAmount", agencyInput.value);
    });

  }
};

document.addEventListener("DOMContentLoaded", initWallets);
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
// DOWNLOAD JS CODE
// ==============================

document.addEventListener("click", function(e){
  const btn = e.target.closest(".download-btn");
  if (!btn) return;
  const id = btn.dataset.id;
  if (!id) return;
  // Redirect to same dashboard file with download param
  window.location.href = "?download_product_id=" + id;
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
              KES <span class="item-subtotal">${formattedPrice}</span>
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
      Makethub · your number one marketplace.
    </div>
  `;

  const minusBtn = card.querySelector(".qty-btn.minus");
  const plusBtn = card.querySelector(".qty-btn.plus");
  const qtyDisplay = card.querySelector(".qty-number");

  function syncMinusState() {
    if (parseInt(qtyDisplay.textContent) <= 1) {
      minusBtn.classList.add("disabled");
      minusBtn.disabled = true;
    } else {
      minusBtn.classList.remove("disabled");
      minusBtn.disabled = false;
    }
  }

  // Initial state
  syncMinusState();

  // ➕ PLUS button
  plusBtn.addEventListener("click", () => {
    // let your existing logic increase the number FIRST
    // (important: this must happen before sync)

    setTimeout(syncMinusState, 0); // ensures DOM updated first
  });

  // ➖ MINUS button
  minusBtn.addEventListener("click", () => {
    setTimeout(syncMinusState, 0);
  });

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
  resetScrollFor();
}

function placeOrder() {
  const payButton = document.getElementById("payButton");

  if (payButton.disabled) return;

  // 🔄 Loading state
  payButton.disabled = true;
  payButton.innerHTML = `<span class="btn-spinner"></span> Processing...`;

  const products = document.querySelectorAll(".order-container .product");

  if (products.length === 0) {
    showNotification(`<i class="fa-solid fa-triangle-exclamation"></i> No products selected!`, 3000, "warning");
    resetPayButton();
    return;
  }

  const orderItems = [];
  let totalAmount = 0;
  let hasError = false; // ✅ fix: track errors properly

  products.forEach(productEl => {
      const product_id = productEl.dataset.product;
      const seller_id  = productEl.dataset.seller;
      const quantity   = parseInt(productEl.querySelector(".qty-number").textContent);
      const price      = parseFloat(productEl.dataset.price);

      if (!product_id || !seller_id || quantity < 1) {
          hasError = true;
          return; // ⚠️ this only exits loop, not function
      }

      totalAmount += price * quantity;

      orderItems.push({
          product_id,
          seller_id,
          quantity,
          price
      });
  });

  // ✅ handle validation AFTER loop (important fix)
  if (hasError) {
    showNotification(`<i class="fa-solid fa-circle-exclamation"></i> Invalid product in order!`, 3000, "error");
    resetPayButton();
    return;
  }

  const formData = new URLSearchParams();
  formData.append("action", "place_order_multi");
  formData.append("total_amount", totalAmount);
  formData.append("items", JSON.stringify(orderItems));

  fetch("marketDisplay.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: formData.toString()
  })
  .then(async res => {
    let data;
    try {
        data = await res.json();
    } catch {
        throw new Error("Invalid server response");
    }
    return data;
  })
  .then(data => {
    const duration = 3000;

    if (data.success) {
        // ✅ Success state
        payButton.innerHTML = `<i class="fa-solid fa-check"></i> Paid`;
        showNotification(
            `<i class='fa-solid fa-check-circle'></i> Order placed successfully!`,
            duration,
            "success"
        );
        // 🔥 CALL PAYMENT PROCESSOR
        // After order creation
        fetch("marketDisplay.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `action=process_payment&order_id=${data.order_id}`
        })
        .then(res => res.json()) // ✅ THIS IS THE FIX
        .then(payData => {

            console.log("🔥 FULL PAYMENT RESPONSE:", payData);

            if (!payData.success) {
                console.error("❌ PAYMENT FAILED:", payData);

                showNotification(
                    `<i class='fa-solid fa-circle-exclamation'></i> ${payData.error}`,
                    4000,
                    "error"
                );

                resetPayButton();
            }

        })
        .catch(err => {
            console.error("🚨 FETCH ERROR:", err);
        });

        payButton.innerHTML = `<i class="fa-solid fa-check"></i> Paid`;
        setTimeout(() => location.reload(), 4500);
    } else if (data.error && data.error.trim() !== "") {
        // ⚠️ Backend error (e.g., stock issue)
        showNotification(
          `<i class="fa-solid fa-triangle-exclamation"></i> ${data.error}`, duration, "warning"
        );
        setTimeout(() => {
          resetPayButton();
          updateOrderSummary();
        }, duration);
    } else {
        // ❌ Generic failure
        showNotification(
            `<i class="fa-solid fa-circle-exclamation"></i> Order failed!`,
            duration,
            "error"
        );
        setTimeout(() => {
            resetPayButton();
            updateOrderSummary();
        }, duration);
    }
  })
  .catch(() => {
    // Only triggers if fetch/network fails
    showNotification(
        `<i class="fa-solid fa-wifi"></i> Network error!`,
        3000,
        "error"
    );
    resetPayButton();
  });

  function resetPayButton() {
    payButton.disabled = false;
    payButton.innerHTML = "Try Again";
  }
  resetScrollFor();
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
  const payButton = document.getElementById("payButton");

  // Format price with commas
  const formattedTotal = itemsTotal.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  if (itemsTotalEl) itemsTotalEl.textContent = `KES ${formattedTotal}`;
  if (finalTotalEl) finalTotalEl.textContent = `KES ${formattedTotal}`;
  if (orderCountEl) orderCountEl.textContent = quantity;
  if (payButton) payButton.textContent = `Pay KES ${formattedTotal}`;
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
  const payButton = document.getElementById("payButton");

  const formattedTotal = grandTotal.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  if (itemsTotalEl) itemsTotalEl.textContent = `KES ${formattedTotal}`;
  if (finalTotalEl) finalTotalEl.textContent = `KES ${formattedTotal}`;
  if (orderCountEl) orderCountEl.textContent = totalItems;
  if (payButton) payButton.textContent = `Pay KES ${formattedTotal}`;
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
      showNotification(`<i class="fa-solid fa-triangle-exclamation"></i> Cart is empty`, 3000, "warning");
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
        const price = parseFloat(product.price) || 0;
        grandTotal += price * product.quantity;
        totalItems += product.quantity;

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
                <div class="product-price">KES ${formattedPrice}</div>
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

      allSellersContent += `
        <div class="seller-box">
          <div class="seller-header">Seller: ${sellerGroup.seller_name.toUpperCase()}</div>
          <div class="seller-meta">Estimated delivery: 3 hours</div>
          ${sellerProductsContent}
        </div>
      `;
    }

    // Update the order card
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
          Makethub · your number one marketplace.
        </div>
      `;
    }

    const allProducts = card.querySelectorAll("#dynamicOrderBox .product");

    allProducts.forEach(productEl => {
      const minusBtn = productEl.querySelector(".qty-btn.minus");
      const plusBtn  = productEl.querySelector(".qty-btn.plus");
      const qtyDisplay = productEl.querySelector(".qty-number");

      function syncMinusState() {
        if (parseInt(qtyDisplay.textContent) <= 1) {
          minusBtn.classList.add("disabled");
          minusBtn.disabled = true;
        } else {
          minusBtn.classList.remove("disabled");
          minusBtn.disabled = false;
        }
      }

      // Initial state
      syncMinusState();

      // ➕ PLUS
      plusBtn.addEventListener("click", () => {
        setTimeout(syncMinusState, 0);
      });

      // ➖ MINUS
      minusBtn.addEventListener("click", () => {
        setTimeout(syncMinusState, 0);
      });
    });

    // Update totals in summary section
    const formattedGrandTotal = grandTotal.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    const itemsTotalEl = document.getElementById("itemsTotal");
    const finalTotalEl = document.getElementById("finalTotal");
    const payButton   = document.getElementById("payButton");

    if (itemsTotalEl) itemsTotalEl.textContent = `KES ${formattedGrandTotal}`;
    if (finalTotalEl) finalTotalEl.textContent = `KES ${formattedGrandTotal}`;
    if (payButton)   payButton.textContent = `Pay KES ${formattedGrandTotal}`;
  });
  resetScrollFor();
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

  // 👉 Get seller box BEFORE removing product
  const sellerBox = productEl.closest(".seller-box");

  // Remove product
  productEl.remove();

  // 👉 If no products left inside this seller, remove seller box too
  if (sellerBox && sellerBox.querySelectorAll(".product").length === 0) {
    sellerBox.remove();
  }

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

// ===============================
// AGENT ALERT VERIFICATION POPUP
// ===============================
function showAgentAlertPopup() {
  const overlay = document.getElementById("alertPopupOverlay");
  const alertPopup = document.getElementById("alert-popup");

  if (!overlay || !alertPopup) return; // exit if elements are missing

  // Show overlay & popup
  overlay.style.display = "flex";
  document.body.classList.add("no-scroll");

  // Shake animation on first appearance
  alertPopup.classList.add("shake");
  setTimeout(() => {
    alertPopup.classList.remove("shake");
  }, 500);

  // Clicking overlay hides popup
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) { // only if overlay itself clicked
      hideAgentAlertPopup();
    }
  });

  // Clicking Cancel hides popup
  const cancelBtn = overlay.querySelector(".cancel");
  if (cancelBtn) {
    cancelBtn.addEventListener("click", (e) => {
      e.preventDefault(); // prevent default link
      hideAgentAlertPopup();
    });
  }
}

// Function to hide the popup
function hideAgentAlertPopup() {
  const overlay = document.getElementById("alertPopupOverlay");
  if (!overlay) return;
  overlay.style.display = "none";
  document.body.classList.remove("no-scroll");
}

document.addEventListener("DOMContentLoaded", () => {

  // Textarea character count
  const bioTextarea = document.getElementById("bioTextarea");
  const bioCount = document.getElementById("bioCount");
  if (bioTextarea && bioCount) {
    bioTextarea.addEventListener("input", () => {
      const len = bioTextarea.value.length;
      bioCount.textContent = `${len}/<?= $bioMaxLength ?> characters`;
    });
  }

});

// ===============================
// NOTIFICATION JS
// ===============================

function showNotification(message, duration = 3000, type = "success") {
  const notification = document.createElement('div');
  notification.classList.add('notification', type); // 👈 add type

  notification.innerHTML = `
    <div class="message">${message}</div>
    <div class="progress-bar"></div>
  `;

  document.getElementById('notification-container').appendChild(notification);

  const progressBar = notification.querySelector('.progress-bar');
  let startTime = null;

  function animateProgress(timestamp) {
    if (!startTime) startTime = timestamp;
    const elapsed = timestamp - startTime;
    const progress = Math.max(0, 1 - elapsed / duration);
    progressBar.style.width = `${progress * 100}%`;

    if (elapsed < duration) {
      requestAnimationFrame(animateProgress);
    } else {
      notification.style.animation = 'slideOutWiggle 0.8s forwards';
      setTimeout(() => notification.remove(), 800);
    }
  }

  requestAnimationFrame(animateProgress);
}

// ===============================
// MARK AS SHIPPED JS (WITH CONFIRMATION)
// ===============================  
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("btn-ship")) {

    const button = e.target;
    const orderId = button.dataset.id;

    // ✅ Confirmation alert
    const confirmAction = confirm("Are you sure you want to mark this order as SHIPPED?");

    if (!confirmAction) return; // ❌ Stop if user cancels

    fetch("sellerPage.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `action=mark_shipped&order_id=${orderId}`
    })
    .then(res => res.text())
    .then(data => {
        console.log(data);

        try {
            const json = JSON.parse(data);

            if (json.success) {
                const row = button.closest("tr");
                const statusCell = row.querySelector("td:nth-child(8) span");

                statusCell.className = "badge shipped";
                statusCell.textContent = "Shipped";

                button.outerHTML = `<button class="btn-view"><i class="fa-solid fa-eye"></i></button>`;
            } else {
                alert("Failed: " + (json.error || "Unknown error"));
            }

        } catch (e) {
            alert("Invalid JSON response");
        }
    })
    .catch(() => alert("Error processing request."));
  }
});

// ===============================
// LOCATION DROPDOWNS (UNIFIED API FIXED)
// ===============================

const countrySelect = document.getElementById("country");
const countySelect = document.getElementById("county");
const wardSelect = document.getElementById("ward");

const oldCountry = document.getElementById("old_country")?.value;
const oldCounty = document.getElementById("old_county")?.value;
const oldWard = wardSelect?.dataset.selected;

// store latest loaded structure
let locationTree = [];

// ===============================
// SORT HELPER (A → Z)
// ===============================
function sortByName(arr) {
  return arr.sort((a, b) =>
    (a.name || "").localeCompare(b.name || "", undefined, { sensitivity: "base" })
  );
}

// ===============================
// LOAD FULL LOCATION TREE
// ===============================
function loadLocations(countryId, selectedCounty = null, selectedWard = null) {

  countySelect.innerHTML = '<option value="">Loading...</option>';
  wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

  fetch("fetch_locations.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "country_id=" + countryId
  })
  .then(res => res.json())
  .then(data => {

    locationTree = data || [];

    countySelect.innerHTML = '<option value="">-- Select County --</option>';
    wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

    let counties = [];

    // ===============================
    // EXTRACT ALL COUNTIES
    // ===============================
    locationTree.forEach(region => {
      const regionCounties = region.counties || {};

      Object.values(regionCounties).forEach(county => {
        counties.push({
          location_id: county.location_id,
          name: county.name,
          wards: county.wards || []
        });
      });
    });

    // SORT COUNTIES A-Z
    counties = sortByName(counties);

    // ===============================
    // POPULATE COUNTIES
    // ===============================
    counties.forEach(county => {
      const selected = selectedCounty == county.location_id ? "selected" : "";
      countySelect.innerHTML += `
        <option value="${county.location_id}" ${selected}>
          ${county.name}
        </option>
      `;
    });

    // ===============================
    // AUTO LOAD WARDS
    // ===============================
    if (selectedCounty) {
      loadWardsFromTree(selectedCounty, selectedWard);
    }
  })
  .catch(err => {
    console.error("Location load error:", err);
  });
}

// ===============================
// COUNTY → WARDS (FROM MEMORY TREE)
// ===============================
function loadWardsFromTree(countyId, selectedWard = null) {

  wardSelect.innerHTML = '<option value="">Loading...</option>';

  let wards = [];

  locationTree.forEach(region => {
    const counties = region.counties || {};

    Object.values(counties).forEach(county => {
      if (county.location_id == countyId) {
        wards = county.wards || [];
      }
    });
  });

  // SORT WARDS A-Z
  wards = sortByName(wards);

  wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

  wards.forEach(ward => {
    const selected = selectedWard == ward.location_id ? "selected" : "";
    wardSelect.innerHTML += `
      <option value="${ward.location_id}" ${selected}>
        ${ward.name}
      </option>
    `;
  });
}

// ===============================
// EVENTS
// ===============================
countrySelect.addEventListener("change", function () {
  loadLocations(this.value);
});

countySelect.addEventListener("change", function () {
  loadWardsFromTree(this.value);
});

// ===============================
// RESTORE ON PAGE LOAD
// ===============================
window.addEventListener("DOMContentLoaded", () => {
  if (oldCountry) {
    countrySelect.value = oldCountry;
    loadLocations(oldCountry, oldCounty, oldWard);
  }
});

// ===============================
// ORDER CHART LOCATION-TRACKING JS
// ===============================


const chatBody = document.getElementById("chatBody");
const chatInput = document.getElementById("chatInput");
const orderStatus = document.getElementById("orderStatus");
const chatFooter = document.getElementById("chatFooter");

/* SEND MESSAGE */
function sendMessage() {
  const input = document.getElementById("chatInput");
  if (!input.value.trim()) return;

  const messageWrapper = document.createElement("div");
  messageWrapper.className = "chat-message buyer";

  const bubble = document.createElement("div");
  bubble.className = "bubble";

  const time = new Date().toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit'
  });

  bubble.innerHTML = `
    ${input.value}
    <span class="time">${time}</span>
  `;

  messageWrapper.appendChild(bubble);
  document.getElementById("chatBody").appendChild(messageWrapper);

  input.value = "";
  chatBody.scrollTop = chatBody.scrollHeight;
}

let currentCoords = null;

function shareLocation() {
  navigator.geolocation.getCurrentPosition(async (pos) => {
    currentCoords = {
      lat: pos.coords.latitude,
      lng: pos.coords.longitude,
      accuracy: pos.coords.accuracy
    };

    // Show manual input modal
    document.getElementById("locationModal").style.display = "block";
  });
}

async function confirmLocation() {
  const manualText = document.getElementById("manualLocation").value;

  const { lat, lng, accuracy } = currentCoords;

  const apiKey = "YOUR_GOOGLE_API_KEY";

  let address = "";

  try {
    const res = await fetch(
      `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`
    );
    const data = await res.json();
    address = data.results[0]?.formatted_address || "Unknown location";
  } catch {
    address = "Address unavailable";
  }

  const mapEmbed = `
    <iframe 
      width="100%" 
      height="150" 
      style="border-radius:10px"
      src="https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed">
    </iframe>
  `;

  const time = new Date().toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit'
  });

  const messageWrapper = document.createElement("div");
  messageWrapper.className = "chat-message buyer";

  const bubble = document.createElement("div");
  bubble.className = "bubble";

  bubble.innerHTML = `
    📍 <strong>Delivery Location</strong><br>
    ${address}<br>
    📝 ${manualText}<br><br>
    ${mapEmbed}
    <small>Accuracy: ±${Math.round(accuracy)}m</small>
    <span class="time">${time}</span>
  `;

  messageWrapper.appendChild(bubble);
  chatBody.appendChild(messageWrapper);

  document.getElementById("locationModal").style.display = "none";

  // Send to backend
  sendLocationToServer(lat, lng, address, manualText);

  // Start live tracking
  startLiveTracking();
}

let trackingInterval;

function startLiveTracking() {
  trackingInterval = setInterval(() => {
    navigator.geolocation.getCurrentPosition((pos) => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;

      fetch("update_location.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ lat, lng })
      });
    });
  }, 5000); // every 5 seconds
}

/* COMPLETE ORDER */
function completeOrder() {
  if (!confirm("This will mark the order as completed and close the chat. Continue?")) return;

  orderStatus.textContent = "Order Completed • Chat Closed";
  orderStatus.style.color = "#ffb703";

  const systemMsg = document.createElement("div");
  systemMsg.className = "message system";
  systemMsg.textContent =
    "✅ Order marked as completed. Chat has been closed.";
  chatBody.appendChild(systemMsg);

  chatFooter.classList.add("locked");
}
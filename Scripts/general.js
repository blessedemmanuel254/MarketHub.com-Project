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
  var box = document.getElementById("cart-container");
  box.style.display = box.style.display === "flex" ? "none" : "flex";
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

// CART JS

document.addEventListener("DOMContentLoaded", () => {

  const subtotalEl = document.getElementById('subtotal');
  const totalEl = document.getElementById('total');

  if (!subtotalEl || !totalEl) return; // 🔑 SAFE EXIT

  const deliveryFee = 0;

  function updateTotals() {
    let subtotal = 0;
    const items = document.querySelectorAll('.cart-item');
    const emptyMsg = document.getElementById('emptyCartMessage');

    // 🔹 Handle empty cart
    if (items.length === 0) {
      if (emptyMsg) emptyMsg.style.display = "block";
      subtotalEl.textContent = "KES 0";
      totalEl.textContent = "KES 0";
      return;
    } else {
      if (emptyMsg) emptyMsg.style.display = "none";
    }

    items.forEach(item => {
      const price = parseInt(item.dataset.price);
      const qty = parseInt(item.querySelector('.qty-number').textContent);
      subtotal += price * qty;
    });

    subtotalEl.textContent = "KES " + subtotal;
    totalEl.textContent = "KES " + (subtotal + deliveryFee);
  }

  document.addEventListener("click", e => {

    if (e.target.classList.contains("plus")) {
      const qty = e.target.parentElement.querySelector(".qty-number");
      qty.textContent = parseInt(qty.textContent) + 1;
    }

    if (e.target.classList.contains("minus")) {
      const qty = e.target.parentElement.querySelector(".qty-number");
      if (parseInt(qty.textContent) > 1) {
        qty.textContent--;
      }
    }

    if (e.target.classList.contains("remove-btn")) {
      e.target.closest(".cart-item").remove();
    }

    updateTotals();
    updateCartCount();
  });

  updateTotals();
});

const cartItemsContainer = document.getElementById("cartItems");
const cartCountEl = document.querySelector(".cart-count");

function updateCartCount() {
  let count = 0;
  document.querySelectorAll(".cart-item").forEach(item => {
    count += parseInt(item.querySelector(".qty-number").textContent);
  });
  cartCountEl.textContent = count;
}

function addToCart(product) {
  const existingItem = [...document.querySelectorAll(".cart-item")]
    .find(item => item.dataset.name === product.name);

  // If product already in cart → increase qty
  if (existingItem) {
    const qtyEl = existingItem.querySelector(".qty-number");
    qtyEl.textContent = parseInt(qtyEl.textContent) + 1;
  } 
  // Else → create new cart item
  else {
    const item = document.createElement("div");
    item.className = "cart-item";
    item.dataset.price = product.price;
    item.dataset.name = product.name;

    item.innerHTML = `
      <div class="cart-left">
        <img src="${product.image}" alt="">
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

document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const card = btn.closest(".variable-card");

    const product = {
      name: card.dataset.name,
      price: parseInt(card.dataset.price),
      image: card.dataset.image
    };

    addToCart(product);
  });
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

/* ---------- CART TOGGLE ---------- */
function toggleCartBar() {
  const box = document.getElementById("cart-container");
  if (!box) return;

  box.style.display = box.style.display === "flex" ? "none" : "flex";
  showTopSection(4000);
}
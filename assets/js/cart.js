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
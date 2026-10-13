
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
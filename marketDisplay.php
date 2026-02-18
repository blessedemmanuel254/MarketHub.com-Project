<?php
session_start();
require_once 'connection.php';

/* ---------- SESSION SECURITY ---------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/* Optional: regenerate session ID periodically */
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="apple-touch-icon" sizes="180x180" href="Images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="Images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="Images/favicon-16x16.png">
  <link rel="manifest" href="Images/site.webmanifest">

  <link rel="stylesheet" href="styles/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <title>Seller's shelf | Market Hub</title>
</head>
<body>
  <div class="container">
    <section class="topSection">
      <div class="cart-wrapper" onclick="toggleCartBar()">
        <span class="cart-icon">📦</span>
        <span class="cart-count">0</span>
      </div>
      <button onclick="window.history.back()">
        <i class="fa-solid fa-circle-arrow-left"></i><span>Go&nbsp;Back</span>
      </button>


    </section>
    <div class="cart-container" id="cart-container">
      <div class="cartTop">
        <h1>My Cart</h1>
        <i class="fa-solid fa-xmark" onclick="toggleCartBar()"></i>
      </div>
      <div class="inner-cart-container">
        <div class="cart-items" id="cartItems">
          <div id="emptyCartMessage" class="empty-cart">
            🛒 Your cart is empty
          </div>
        </div>

        <div class="cart-summary">
          <div class="summary-row">
            <span>Subtotal</span>
            <span id="subtotal">KES 0</span>
          </div>

          <div class="summary-row">
            <span>Delivery</span>
            <span>KES 0</span>
          </div>

          <div class="summary-row">
            <span>Discount</span>
            <span>KES 0</span>
          </div>

          <div class="summary-row">
            <span>Market Hub Points</span>
            <span>KES 0</span>
          </div>

          <div class="summary-row summary-total">
            <span>Total</span>
            <span id="total">KES 0</span>
          </div>

          <button class="checkout-btn" onclick="togglePaymentOption()">Proceed&nbsp;to&nbsp;Payment</button>
        </div>
      </div>
    </div>
    <div class="payOverlay" onclick="togglePaymentOption()" id="payOverlay"></div>
    <form class="paymentContainer" action="" id="paymentContainer">
      <h1>Choose&nbsp;Account <br><span>You can set your default account in settings</span></h1>
      <label class="radio-container">
        <div class="rightDiv">
          <img src="Images/M-PESA_LOGO-01.svg.png" alt="Mpesa Logo" width="60">
          <p>MPESA<br><span>254759578630</span></p>
        </div>
        <input type="radio" name="payment" value="mpesa">
        <span class="checkmark"></span>
      </label><!-- 
      <label class="radio-container">
        <div class="rightDiv">
          <img src="Images/credit-card-01.jpg" alt="Mpesa Logo" width="60">
          <p>Card&nbsp;Payment</p>
        </div>
        <input type="radio" name="payment" value="card">
        <span class="checkmark"></span>
      </label> -->
      <button>Continue</button>
      <a href="" onclick="togglePaymentOption()">Cancel&nbsp;Payment</a>

    </form>
    <div class="overlay" onclick="toggleWhatsAppChat()" id="overlay"></div>
    <div id="whatsapp-button" onclick="toggleWhatsAppChat()">
      <img src="Images/Market Hub WhatsApp Icon.avif" width="45" alt="Chat with us on WhatsApp">
    </div>

    <div id="whatsapp-chat-box">
      <div class="chat-header">
        <div class="top">
          <img src="Images/Market Hub Logo.avif" alt="Market Hub Logo" width="35">
          <p><strong>Market Hub</strong><br>
          <small>online</small></p>
        </div>
        <i class="fa-solid fa-xmark" onclick="toggleWhatsAppChat()"></i>
      </div>
      <div class="chat-body">
        <div class="chat-container">
          <div class="chat-bubble">
            <div class="sender">Market Hub</div>
            <div class="message">
              Hello there! 😊<br>
              How can we help?
            </div>
            <div class="time">
              11:31 PM
            </div>
          </div>
        </div>
        <div class="containerWhp">
          <textarea id="userMessage" placeholder="Type a message.."></textarea>
          <img src="Images/Send-35.png" alt="Send Icon" width="45" onclick="sendWhatsAppMessage()">
        </div>
      </div>
    </div>
    <main class="sellerMain" id="marketMain">
      <div class="sellerProfileContainer">
        <div class="seller">
          <div class="seller-left">
            <div class="avatar">MC</div><!-- 
            <img src="" alt="Seller Logo"> -->
            <div>
              <div class="name">Main Canteen</div>
              <div class="rating">★★★★★ (41)</div>
              <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
              <div class="meta"><h2>23k&nbsp;<span>followers</span></h2></div>
              <div class="bsInfo">Delivery: Pickup · Courier</div>
              <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
            </div>
          </div>
          <a href="" class="seller-right">
            <div class="promoBadgeGoGold">200+</div>
            <div class="bsType">Business Type : <i>Kiosk</i></div>
            <div class="action">
              <h2>LOCAL MARKET</h2>
            </div>
          </a>
        </div>
      </div>
      <div class="tabs-container">
        <div class="tabs">
          <button class="tab-btn active" data-tab="products">Food&nbsp;&&nbsp;Snacks</button>
        </div>

        <div class="tab-content">
          <div id="products" class="tab-panel active">
            <div class="tab-top">
              <p>You order we deliver.</p>
            </div>

            <div class="variables-grid">

              <!-- CARD 1 -->
              <div class="variable-card" data-name="Passion Juice" data-price="40" data-image="Images/Passion Juice.jpg">
                <button class="add-to-cart-btn">Add&nbsp;to&nbsp;cart</button>
                <img class="variableAndSnacksImage" src="Images/Passion Juice.jpg" alt="Product Image">
                <div class="variable-content">
                  <div class="variable-title">Passion Juice</div>
                  <div class="variable-desc">Crispy, golden and freshly prepared.</div>
                  <div class="price-row">
                    <div class="price">KES 40</div>
                    <button class="buy-btn" onclick="togglePaymentOption()">Order</button>
                  </div>
                </div>
              </div>

              <!-- CARD 2 -->
              <div class="variable-card" data-name="Burger & Fries" data-price="650" data-image="Images/Market Hub Logo.avif">
                <button class="add-to-cart-btn">Add&nbsp;to&nbsp;cart</button>
                <img class="variableAndSnacksImage" src="Images/Market Hub Logo.avif" alt="Product Image">
                <div class="variable-content">
                  <div class="variable-title">Burger & Fries</div>
                  <div class="variable-desc">Juicy burger served with crispy fries.</div>
                  <div class="price-row">
                    <div class="price">KES 650</div>
                    <button class="buy-btn" onclick="togglePaymentOption()">Order</button>
                  </div>
                </div>
              </div>

              <!-- CARD 3 -->
              <div class="variable-card" data-name="Pizza Slice" data-price="300" data-image="Images/Market Hub Logo.avif">
                <button class="add-to-cart-btn">Add&nbsp;to&nbsp;cart</button>
                <img class="variableAndSnacksImage" src="Images/Market Hub Logo.avif" alt="Product Image">
                <div class="variable-content">
                  <div class="variable-title">Pizza Slice</div>
                  <div class="variable-desc">Cheesy slice with fresh toppings.</div>
                  <div class="price-row">
                    <div class="price">KES 300</div>
                    <button class="buy-btn" onclick="togglePaymentOption()">Order</button>
                  </div>
                </div>
              </div>

              <!-- CARD 4 -->
              <div class="variable-card" data-name="Samosas" data-price="150" data-image="Images/Market Hub Logo.avif">
                <button class="add-to-cart-btn">Add&nbsp;to&nbsp;cart</button>
                <img class="variableAndSnacksImage" src="Images/Market Hub Logo.avif" alt="Product Image">
                <div class="variable-content">
                  <div class="variable-title">Samosas</div>
                  <div class="variable-desc">Crispy snacks filled with spiced meat.</div>
                  <div class="price-row">
                    <div class="price">KES 150</div>
                    <button class="buy-btn" onclick="togglePaymentOption()">Order</button>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </main>
  
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript"></script>

  <script>
  document.querySelectorAll(".toggle").forEach(btn => {
    btn.addEventListener("click", () => {
      const target = document.getElementById(btn.dataset.target);
      target.classList.toggle("active");
      btn.textContent = target.classList.contains("active")
        ? "Hide details"
        : "View details";
    });
  });
  </script>
  
</body>
</html>
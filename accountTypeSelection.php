<?php
session_start();

// ==========================
// CSRF Protection
// ==========================
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ==========================
// Account type whitelist
// ==========================
$validAccountTypes = ['buyer', 'seller']; // Add more if needed
$error = '';

// ==========================
// Handle POST request
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } elseif (!isset($_POST['accountType'])) {
        $error = 'Select account type to proceed';
    } else {
        $selectedType = $_POST['accountType'];

        // Validate against whitelist
        if (in_array($selectedType, $validAccountTypes)) {
            $_SESSION['accountType'] = $selectedType;
            // Regenerate session ID for extra safety
            session_regenerate_id(true);
            header('Location: register.php');
            exit();
        } else {
            $error = 'Invalid account type selected';
        }
    }
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

  <link rel="stylesheet" href="assets/css/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <title>Select Account Type ~ Maket Hub</title>
</head>
<body>
  <div class="container">
    <div class="overlay" onclick="toggleWhatsAppChat()" id="overlay"></div>
    <div id="whatsapp-button" onclick="toggleWhatsAppChat()">
      <img src="Images/Maket Hub WhatsApp Icon.avif" width="45" alt="Chat with us on WhatsApp">
    </div>

    <div id="whatsapp-chat-box">
      <div class="chat-header">
        <div class="top">
          <img src="Images/Maket Hub Logo.avif" alt="Maket Hub Logo" width="35">
          <p><strong>Maket Hub.com</strong><br>
          <small>Online</small></p>
        </div>
        <i class="fa-solid fa-xmark" onclick="toggleWhatsAppChat()"></i>
      </div>
      <div class="chat-body">
        <div class="chat-container">
          <div class="chat-bubble">
            <div class="sender">Maket Hub.com</div>
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
    <main>
      <form class="actpContainer" method="POST" action="">
        <h1>Select Your Account Type&nbsp;:</h1>
        
        <?php if ($error): ?>
          <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- CSRF token hidden input -->
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="actpGrid">
          <label class="account-card">
            <input type="radio" name="accountType" value="buyer">
            <div class="account-icon">🛒</div>
            <div class="account-label">Buyer</div>
            <div class="account-desc">Refill cooking gas, buy food and groceries, buy household items, buy clothes or shoes, pay for services<!-- , Find property to buy or rent e.g land, vehicle, house, Airbnb --> and more. </div>
          </label>
          <label class="account-card">
            <input type="radio" name="accountType" value="seller">
            <div class="account-icon">📦</div>
            <div class="account-label">Seller</div>
            <div class="account-desc">Put your goods and services online to both your local and national customers. This is whether your own a kiosk, shop, boutique, supermarket, stall, mall, chemist, agrovet name them all. </div>
          </label>
          <!-- <label class="account-card">
            <input type="radio" name="accountType" value="property_owner">
            <div class="account-icon">🏠</div>
            <div class="account-label">Property Owner</div>
            <div class="account-desc">Put your houses, land, apartments, rentals, cars or other items online and reach local and national tenants and buyers with ease.</div>
          </label>
          <label class="account-card">
            <input type="radio" name="accountType" value="service_provider">
            <div class="account-icon">🛠️</div>
            <div class="account-label">Service Provider</div>
            <div class="account-desc">Offer professional services to customers around you.</div>
          </label>
          <label class="account-card">
            <input type="radio" name="accountType" value="business_owner">
            <div class="account-icon">🏪</div>
            <div class="account-label">Business Owner</div>
            <div class="account-desc">Manage a storefront and multiple listings with business tools.</div>
          </label>
          <label class="account-card">
            <input type="radio" name="accountType" value="delivery_partner">
            <div class="account-icon">🚚</div>
            <div class="account-label">Delivery Partner</div>
            <div class="account-desc">Join the logistics team to help deliver items efficiently.</div>
          </label> -->
        </div>
        <button type="submit">Continue</button>
      </form>
    </main>
    <footer>
      <p>&copy; 2025/2026, Maket Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>
</body>
</html>
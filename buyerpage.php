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

/* ---------- FETCH USER DATA ---------- */
$user_id = $_SESSION['user_id'];

$query = "SELECT username FROM users WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("System error.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$username = "User";

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
}

$stmt->close();

/* ---------- PROFILE LETTER ---------- */
$profileLetter = strtoupper(substr($username, 0, 1));

/* ---------- FORMAT USERNAME ---------- */
$username = trim($username);

if ($username !== '') {
    $formattedUsername =
        strtoupper(substr($username, 0, 1)) .
        strtolower(substr($username, 1));
} else {
    $formattedUsername = "User";
}

/* ---------- PROFILE LETTER ---------- */
$profileLetter = strtoupper(substr($formattedUsername, 0, 1));

/* ---------- SAFE OUTPUT ---------- */
$safeUsername = htmlspecialchars($formattedUsername, ENT_QUOTES, 'UTF-8');
$safeLetter = htmlspecialchars($profileLetter, ENT_QUOTES, 'UTF-8');

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

  <title>Buyer Page | MarketHub</title>
</head>
<body>
  <div class="container">
    <header class="pgHeader">
      <section>
        <div class="sContainer">
          <!-- <p><?php echo $safeLetter; ?></p> -->
          <img src="Images/MarketHub Logo.avif" alt="Market Hub Logo" width="40">
          <p class="wcmTxt">
            Welcome,<br>
            <span>Logged in as <?php echo $safeUsername; ?></span>
          </p>
        </div>
        <div class="rhs">
          <a class="lkOdr">
            <div class="odrIconDiv">
              <i class="fa-brands fa-first-order-alt"></i>
              <p>8</p>
            </div>
            <p>Order(s)</p>
          </a>
          <select name="" id="ward">
            <option value="">Kilifi</option>
            <!--<option value="">Tanzania</option>
            <option value="">Uganda</option>-->
          </select>
          <div class="help-icon">
            <i class="fa-regular fa-circle-question"></i>
            <p class="help-text">Help&nbsp;Centre</p>
          </div>
          <div class="profile-icon">
            <i class="fa-regular fa-user"></i>
            <p class="help-text">Profile</p>
          </div>
          <img src="Images/Kenya Flag.png" alt="Kenya Flag" width="40">
        </div>
      </section>
    </header>

    <div id="whatsapp-button" onclick="toggleWhatsAppChat()">
      <img src="Images/MarketHub WhatsApp Icon.avif" width="45" alt="Chat with us on WhatsApp">
    </div>

    <div id="whatsapp-chat-box">
      <div class="chat-header">
        <div class="top">
          <img src="Images/MarketHub Logo.avif" alt="MarketHub Logo" width="35">
          <p><strong>MarketHub.com</strong><br>
          <small>online</small></p>
        </div>
        <i class="fa-solid fa-xmark" onclick="toggleWhatsAppChat()"></i>
      </div>
      <div class="chat-body">
        <div class="chat-container">
          <div class="chat-bubble">
            <div class="sender">MarketHub.com</div>
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

    <div class="overlay" id="overlay" onclick="togglePopupBar()"></div>
    <div class="popUpBar" id="popupbar">
      <div class="top">
        <img src="Images/MarketHub Logo.avif" alt="">
        <i class="fa-solid fa-xmark" onclick="togglePopupBar()"></i>
      </div>

      <a href="" class="help-icon">
        <i class="fa-regular fa-circle-question"></i>
        <p class="help-text">Help</p>
      </a>
      <a href="accountTypeSelection.php"></i><i class="fa-regular fa-user"></i> Register</a>
      <a href="index.php">Login</a>
    </div>

    <main class="buyerMain">
      <div class="tabs-container">
        <div class="tabs">
          <button class="tab-btn active" data-tab="products">Products</button>
          <button class="tab-btn" data-tab="services">Services</button>
          <button class="tab-btn" data-tab="rentals">Rentals</button>
        </div>

        <div class="tab-content">
          <div id="products" class="tab-panel active">
            <p>Quality goods from trusted local vendors.</p>
          </div>

          <div id="services" class="tab-panel">
            <p>Professional services delivered with reliability.</p>
          </div>

          <div id="rentals" class="tab-panel">
            <p>Affordable rentals for homes, vehicles and equipment.</p>
          </div>
        </div>
      </div>
    </main>
    <footer>
      <p>&copy; 2025/2026, MarketHub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript"></script>
</body>
</html>
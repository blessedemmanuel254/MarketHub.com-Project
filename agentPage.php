<?php
session_start();
// Dynamic OG data based on page content
$pageTitle = "Agent Page | Maket Hub";
$pageDescription = "Verify your Maket Hub agent account to unlock full agent privileges; receiving commissions, Making withdrawal requests and manage your agency efficiently.";
$pageUrl = "agentregister.php";
$pageImage = "Images/Maket Hub Logo.avif"; // Use a clear visual for verification
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

/* ---------- ROLE + STATUS ACCESS CONTROL ---------- */

$allowedRole = 'sales_agent';

$roleStmt = $conn->prepare(
  "SELECT account_type, is_verified, status 
  FROM users 
  WHERE user_id = ? 
  LIMIT 1"
);

$roleStmt->bind_param("i", $_SESSION['user_id']);
$roleStmt->execute();
$roleStmt->bind_result($accountType, $isVerified, $status);
$roleStmt->fetch();
$roleStmt->close();


/* ---------- VALIDATION ---------- */

if ($accountType !== $allowedRole) {
    header("Location: index.php");
    exit();
}

/* ---------- USER ID ---------- */
$user_id = $_SESSION['user_id'];
/* Ensure the agent is verified */
/* if ($isVerified != 1) {
  header("Location: verifyAgent.php");
  exit();
} */

/* Ensure the account is active */
/* if ($status !== 'active') {
  header("Location: accountSuspended.php");
  exit();
} */

$country = "";
$county = "";
$ward = "";
/* =====================================================
ADD NEW AGENT FROM DASHBOARD
===================================================== */

$agent_error = "";
$agent_success = "";
$agent_full_name = "";
$agent_username = "";
$agent_email = "";
$agent_phone = "";
$agent_country = "";
$agent_county = "";
$agent_ward = "";
$agent_address = "";
$agent_accountType = "";

function validatePassword($password) {
  // Check all rules, but return only a simple generic message if any fail
  if (strlen($password) < 8 || 
    !preg_match('/[A-Z]/', $password) || 
    !preg_match('/[a-z]/', $password) || 
    !preg_match('/\d/', $password) || 
    !preg_match('/[^A-Za-z0-9]/', $password)) {
    return "Password does not meet requirements.";
  }
  return ""; // valid
}

function normalizePhoneNumber($rawPhone) {
  // Remove all characters except numbers and plus sign
  $cleaned = preg_replace('/[^\d+]/', '', $rawPhone);

  // Handle various formats
  if (strpos($cleaned, '+') === 0) {
      // Already starts with country code
      return $cleaned;
  } elseif (strpos($cleaned, '0') === 0 && strlen($cleaned) >= 10) {
      // Starts with 0 — assume it's local Kenyan-style and convert to +254
      return '+254' . substr($cleaned, 1);
  } elseif (strlen($cleaned) >= 9 && !str_starts_with($cleaned, '+')) {
      // Assume starts directly with country code
      return '+' . $cleaned;
  }

  // Invalid fallback
  return '';
}

function generateReferralCode(){
  return strtoupper(substr(bin2hex(random_bytes(5)),0,8));
}

if($_SERVER["REQUEST_METHOD"] === "POST"){

  $agent_full_name = trim($_POST['full_name'] ?? '');
  $agent_username = trim($_POST['username'] ?? '');
  $agent_email = trim($_POST['email'] ?? '');
  $agent_phone = trim($_POST['phone'] ?? '');
  $agent_country = trim($_POST['country'] ?? '');
  $agent_county = trim($_POST['county'] ?? '');
  $agent_ward = trim($_POST['ward'] ?? '');
  $agent_address = trim($_POST['address'] ?? '');

  $agent_accountType = "sales_agent";
  $defaultPassword = "Makethub123#";

  if(!$agent_full_name || !$agent_username || !$agent_email || !$agent_phone || !$agent_country || !$agent_county || !$agent_ward || !$agent_address){
    $agent_error = "All fields are required.";
  }

  elseif(str_word_count($agent_full_name) < 2){
    $agent_error = "Full name must include at least first and last name!";
  }

  elseif(strpos($agent_username,' ') !== false){
    $agent_error = "Username should not have space(s)!";
  }

  elseif(strlen($agent_username) > 20){
    $agent_error = "Username should contain a maximum of 20 characters!";
  }

  elseif(strlen($agent_username) < 5){
    $agent_error = "Username is too short!";
  }

  elseif(!filter_var($agent_email, FILTER_VALIDATE_EMAIL)){
    $agent_error = "Invalid email address!";
  } elseif (!preg_match('/^[0-9+\-\(\)\s]+$/', $agent_phone)) {
    $agent_error = "Phone number contains invalid characters!";
  } 

  elseif(strlen($agent_address) > 25){
    $agent_error = "Address too long!";
  }

  else{

      $normalized_phone = normalizePhoneNumber($agent_phone);

      if(!$normalized_phone || !preg_match('/^(\+254\d{9}|0\d{9})$/',$normalized_phone)){
        $agent_error = "Please enter a valid phone number!";
      }

      else{

          $encrypted_email = base64_encode($agent_email);
          $encrypted_phone = base64_encode($normalized_phone);

          /* CHECK USERNAME / EMAIL */
          $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
          $stmt->bind_param("ss",$encrypted_email,$agent_username);
          $stmt->execute();
          $stmt->store_result();

          if($stmt->num_rows > 0){
              $agent_error = "Username or email already exists.";
          }

          $stmt->close();

          /* CHECK PHONE */
          if(!$agent_error){

              $stmt = $conn->prepare("SELECT user_id FROM users WHERE phone = ?");
              $stmt->bind_param("s",$encrypted_phone);
              $stmt->execute();
              $stmt->store_result();

              if($stmt->num_rows > 0){
                  $agent_error = "Phone number already exists!";
              }

              $stmt->close();
          }

          /* PASSWORD VALIDATION */

          if(!$agent_error){

              $passwordError = validatePassword($defaultPassword);

              if($passwordError){
                  $agent_error = $passwordError;
              }
          }

          if(!$agent_error){

              $hashedPassword = password_hash($defaultPassword,PASSWORD_DEFAULT);

              $newReferralCode = generateReferralCode();

              $empty = "";

              $stmt = $conn->prepare("
              INSERT INTO users
              (
              account_type,
              full_name,
              username,
              email,
              phone,
              password,
              country,
              county,
              ward,
              address,
              business_name,
              business_model,
              business_type,
              market_scope,
              agency_code,
              referred_by,
              created_at,
              updated_at,
              economic_period_count, must_change_password
              )
              VALUES
              (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?,NOW(),NOW(),0,1)
              ");

              $stmt->bind_param(
              "ssssssssssssssss",
              $accountType,
              $agent_full_name,
              $agent_username,
              $encrypted_email,
              $encrypted_phone,
              $hashedPassword,
              $agent_country,
              $agent_county,
              $agent_ward,
              $agent_address,
              $empty,
              $empty,
              $empty,
              $empty,
              $newReferralCode,
              $user_id
              );

              if($stmt->execute()){

                $newAgentID = $stmt->insert_id;

                /* ===========================
                  CREATE AGENT WALLETS
                =========================== */

                $salesWallet = $conn->prepare("
                INSERT INTO wallets (user_id, wallet_type, balance, total_transacted)
                VALUES (?, 'sales', 0.00, 0.00)
                ");

                $salesWallet->bind_param("i", $newAgentID);
                $salesWallet->execute();
                $salesWallet->close();


                $agencyWallet = $conn->prepare("
                INSERT INTO wallets (user_id, wallet_type, balance, total_transacted)
                VALUES (?, 'agency', 0.00, 0.00)
                ");

                $agencyWallet->bind_param("i", $newAgentID);
                $agencyWallet->execute();
                $agencyWallet->close();

                $agent_success = "New agent added successfully! <span class='redirect-msg'></span>";
              }
              else{
                  $agent_error = "Error: ".$stmt->error;
              }

              $stmt->close();
          }

      }

  }

}

$query = "SELECT username, profile_image, agency_code 
FROM users 
WHERE user_id = ? 
LIMIT 1";
$stmt = $conn->prepare($query);

if (!$stmt) {
  die("System error.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$username = "User";
$profileImage = null;
$agencyCode = "";

if ($result && $result->num_rows === 1) {
  $user = $result->fetch_assoc();

  if (!empty($user['username'])) {
      $username = $user['username'];
  }

  $profileImage = $user['profile_image'] ?? null;
  $agencyCode = $user['agency_code'] ?? "";
}

$stmt->close();

/* ---------- PROFILE LETTER ---------- */
$profileLetter = strtoupper(substr($username, 0, 1));

/* ---------- FORMAT USERNAME ---------- */
$username = trim($username);

$formattedUsername =
  strtoupper(substr($username, 0, 1)) .
  strtolower(substr($username, 1));

/* ---------- PROFILE LETTER ---------- */
$profileLetter = strtoupper(substr($formattedUsername, 0, 1));

/* ---------- SAFE OUTPUT ---------- */
$safeUsername = htmlspecialchars($formattedUsername, ENT_QUOTES, 'UTF-8');
$safeLetter = htmlspecialchars($profileLetter, ENT_QUOTES, 'UTF-8');

$defaultAvatar = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

if (!empty($profileImage) && file_exists($profileImage)) {
    $safeProfileImage = htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8');
} else {
    $safeProfileImage = $defaultAvatar;
}

/* ---------- GENERATE AGENCY LINK ---------- */

$baseAgencyLink = "http://localhost/MaketHub.shop-Project/agentRegister.php";

$agencyLink = $baseAgencyLink . "?ref=" . urlencode($agencyCode);

// ---------------------------
// AGENT NETWORK CALCULATION
// ---------------------------
$level1 = [];
$level2 = [];
$level3 = [];

$level1Count = 0;
$level2Count = 0;
$level3Count = 0;

$lvl1Earn = 0;
$lvl2Earn = 0;
$lvl3Earn = 0;
$totalNetwork = 0;
$newThisMonth = 0;
$highestLevel = "None";


/* Ensure the agent is verified */
if ($isVerified === 1 && $status === 'active') {
  // ---------- LEVEL 1 ----------
  $stmt = $conn->prepare("
  SELECT user_id, economic_period_count
  FROM users
  WHERE referred_by = ?
  AND is_verified = 1
  ");

  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {

    $level1[] = $row['user_id'];

    $periods = (int)$row['economic_period_count'];

    if ($periods > 0) {
      $lvl1Earn += (100 * $periods);
    }
  }

  $stmt->close();
  $level1Count = count($level1);


  // ---------- LEVEL 2 ----------
  if ($level1Count > 0) {

      $placeholders = implode(',', array_fill(0, $level1Count, '?'));
      $types = str_repeat('i', $level1Count);

      $stmt = $conn->prepare("
      SELECT user_id, economic_period_count
      FROM users
      WHERE referred_by IN ($placeholders)
      AND is_verified = 1
      ");

      $stmt->bind_param($types, ...$level1);
      $stmt->execute();
      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()) {

          $level2[] = $row['user_id'];

          $periods = (int)$row['economic_period_count'];

          if ($periods > 0) {
              $lvl2Earn += (40 * $periods);
          }
      }

      $stmt->close();
  }

  $level2Count = count($level2);


  // ---------- LEVEL 3 ----------
  if ($level2Count > 0) {

    $placeholders = implode(',', array_fill(0, $level2Count, '?'));
    $types = str_repeat('i', $level2Count);

    $stmt = $conn->prepare("
    SELECT user_id, economic_period_count
    FROM users
    WHERE referred_by IN ($placeholders)
    AND is_verified = 1
    ");

    $stmt->bind_param($types, ...$level2);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

      $level3[] = $row['user_id'];

      $periods = (int)$row['economic_period_count'];

      if ($periods > 0) {
          $lvl3Earn += (20 * $periods);
      }
    }

    $stmt->close();
  }

  $level3Count = count($level3);


  // ---------- TOTAL NETWORK ----------
  $totalNetwork = $level1Count + $level2Count + $level3Count;


  // ---------- TOTAL EARNINGS ----------
  $totalEarnings = $lvl1Earn + $lvl2Earn + $lvl3Earn;


  /* =====================================================
    NEW AGENTS THIS MONTH (LAST 30 DAYS)
  ===================================================== */

  $newThisMonth = 0;


  /* Level 1 new */
  $stmt = $conn->prepare("
  SELECT COUNT(*) as total
  FROM users
  WHERE referred_by = ?
  AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  ");

  $stmt->bind_param("i",$user_id);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();

  $newThisMonth += $res['total'];

  $stmt->close();

  /* Level 2 new */

  if($level1Count > 0){

  $placeholders = implode(',', array_fill(0,$level1Count,'?'));
  $types = str_repeat('i',$level1Count);

  $stmt = $conn->prepare("
  SELECT COUNT(*) as total
  FROM users
  WHERE referred_by IN ($placeholders)
  AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  ");

  $stmt->bind_param($types, ...$level1);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();

  $newThisMonth += $res['total'];

  $stmt->close();
  }


  /* Level 3 new */

  if($level2Count > 0){

  $placeholders = implode(',', array_fill(0,$level2Count,'?'));
  $types = str_repeat('i',$level2Count);

  $stmt = $conn->prepare("
  SELECT COUNT(*) as total
  FROM users
  WHERE referred_by IN ($placeholders)
  AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  ");

  $stmt->bind_param($types, ...$level2);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();

  $newThisMonth += $res['total'];

  $stmt->close();
  }

  /* =====================================================
    HIGHEST EARNING LEVEL
  ===================================================== */

  $highestLevel = "Level 1";
  $highestValue = $lvl1Earn;

  if($lvl2Earn > $highestValue){
      $highestValue = $lvl2Earn;
      $highestLevel = "Level 2";
  }

  if($lvl3Earn > $highestValue){
      $highestLevel = "Level 3";
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <!-- jQuery + DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <title><?= htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription); ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($pageUrl); ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle); ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription); ?>">
  <meta property="og:image" content="<?= htmlspecialchars($pageImage); ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="<?= htmlspecialchars($pageUrl); ?>">
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle); ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription); ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($pageImage); ?>">
</head>
<body>
  <div class="container">
    <!-- ALERT POPUP OVERLAY -->
      <div class="alertPopupOverlay" id="alertPopupOverlay">

      <div class="alert-popup" id="alert-popup">

        <div class="alert-popup-header">
        ACCOUNT VERIFICATION REQUIRED
        </div>

        <div class="alert-popup-body">

          <div class="warning-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>

          <div class="alert-popup-title">
          Verify Your Agent Account
          </div>

          <div class="alert-popup-text">
            Your MarketHub agent account is currently **unverified**.

            To unlock full agent privileges like:

            • Full access to listed products  
            • Receiving commissions
            • Making withdrawal requests and
            • Other premium agent tools  

            You must activate your account.

          </div>

          <div class="buttons">

            <a href="logout.php" class="cancel">
            Cancel
            </a>

            <a href="agentregister.php" class="activate">
            Verify Account
            </a>

          </div>

        </div>

      </div>

    </div>
    <header class="pgHeader">
      <section>
        <div class="sContainer">
          <img src="<?php echo $safeProfileImage; ?>" alt="Profile" class="avatar-img">
          <p class="wcmTxt">
            Welcome,<br>
            <span>Logged in as <?php echo $safeUsername; ?></span>
          </p>
        </div>
        <div class="rhs">
          <a class="lkOdr" onclick="toggleAgentOrdersTrack()">
            <div class="odrIconDiv">
              <i class="fa-brands fa-first-order-alt"></i>
              <p>8</p>
            </div>
          </a>
          <a class="lkOdr" onclick="toggleAgentEarningsTrack()">
            <div class="odrIconDiv">
              <i class="fa-solid fa-sack-dollar"></i>
              <p class="agent-not">3</p>
            </div>
          </a>
          <select name="" id="ward">
            <option value="">Kilifi</option>
            <!--<option value="">Bungoma</option>
            <option value="">Nairobi</option>-->
          </select>
          <a href="helpCentre.php" class="help-icon">
            <i class="fa-regular fa-circle-question"></i>
            <p>Help&nbsp;Centre</p>
          </a>
          <div class="profile-icon" onclick="toggleProfileOption()">
            <i class="fa-regular fa-user"></i>
            <p class="profile-text">Profile</p>
            <div class="profileOption" id="profileOption">
              <?php if ($safeProfileImage !== $defaultAvatar): ?>
                <img src="<?php echo $safeProfileImage; ?>" class="avatar-img large">
              <?php else: ?>
                <p class="avatar-letter large"><?php echo $safeLetter; ?></p>
              <?php endif; ?>

              <a href="userProfile.php"><i class="fa-solid fa-eye"></i>View Profile</a>
              <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
            </div>
          </div>
          <img src="Images/Kenya Flag.png" alt="Kenya Flag" width="40">
        </div>
      </section>
      <div class="overlay" onclick="toggleProfileOption()" id="overlay1"></div>
    </header>
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
      <a href="" onclick="togglePaymentOption()" data-tab="dashboard">Cancel&nbsp;Withdrawal</a>

    </form>
    <div class="overlay" onclick="toggleWhatsAppChat()" id="overlay"></div>
    <div id="whatsapp-button" onclick="toggleWhatsAppChat()">
      <img src="Images/Maket Hub WhatsApp Icon.avif" width="45" alt="Chat with us on WhatsApp">
    </div>

    <div id="whatsapp-chat-box">
      <div class="chat-header">
        <div class="top">
          <img src="Images/Maket Hub Logo.avif" alt="Maket Hub Logo" width="35">
          <p><strong>Maket Hub</strong><br>
          <small>online</small></p>
        </div>
        <i class="fa-solid fa-xmark" onclick="toggleWhatsAppChat()"></i>
      </div>
      <div class="chat-body">
        <div class="chat-container">
          <div class="chat-bubble">
            <div class="sender">Maket Hub</div>
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

    <main class="buyerMain" id="agentMain">
      <div class="agentHeader">
        <h1>Agent Dashboard</h1>
        <p class="status">Status:&nbsp;

        <?php if ($status !== 'active'): ?>

        <span class="suspended">
        <i class="fa-solid fa-ban"></i>&nbsp;Suspended
        </span>

        <?php elseif ($isVerified == 0): ?>

        <span class="unverified">
        <i class="fa-solid fa-ban"></i>&nbsp;Unverified
        </span>

        <?php elseif ($isVerified == 1): ?>

        <span class="verified">
        Verified&nbsp;<i class="fa-solid fa-certificate"></i>
        </span>

        <?php endif; ?>

        </p>
      </div>
      <div class="tabs-container" id="toggleAgentTab">
        <div class="tabs">
          <button class="tab-btn" data-tab="dashboard">Sales&nbsp;Board</button>
          <button class="tab-btn" data-tab="agency">My&nbsp;Agency</button>
          <button class="tab-btn" data-tab="funds" onclick="togglePaymentOption()">Funds</button>
        </div>

        <div class="tab-content">
          <div id="dashboard" class="tab-panel">
            <p>Sales Scope <br><strong>Your work progress and finances <i class="fa-regular fa-circle-check"></i></strong></p>
            

            <div class="cards">
              <!-- AGENT -->
              <a class="card" onclick="toggleAgentProductsPage()">
                <i class="fa-brands fa-product-hunt"></i>
                <h2>Products</h2>
                <p>
                  View your products to market.
                </p>
                <div class="label">
                  <p>AGENT</p>
                  <button>View Products</button>

                </div>
              </a>

              <!-- WITHDRAWAL HISTORY -->
              <a class="card" onclick="toggleAgentWithdrawals()">
                <i class="fa-brands fa-python"></i>
                <h2>Withrawal</h2>
                <p>
                  View your account withdrawal history.
                </p>
                <div class="label">
                  <p>HISTORY</p>
                  <button>View History</button>

                </div>
              </a>

              <!-- MARKET -->
              <a class="card" onclick="openMarketType('products')">
                <i class="fa-brands fa-renren"></i>
                <h2>Market</h2>
                <p>
                  Also order on Maket Hub like other buyers.
                </p>
                <div class="label">
                  <p>Market</p>
                  <button>View Market</button>

                </div>
              </a>
            </div>
          </div>

          <div id="agency" class="tab-panel agency">
            <div class="tab-top">
              <p>Peformance Analytics<br><strong>Monitor your agency and track performance <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleAgentAdd(true)">
                <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Agent</span>
              </button>

            </div>
            <div class="dashboard">

              <!-- TOP CARDS -->
              <div class="grid">

                <!-- AGENCY WALLET -->
                <div class="card">
                  <h3>Agency Balance</h3>
                  <div class="amount">KES 12,540</div>
                  <div class="sub-info">Last withdrawal: 3 days ago</div>
                  <div class="growth up">▲ +18% from last month</div>
                  <div class="progress">
                    <div class="progress-fill"></div>
                  </div>
                  <div class="sub-info">KES 2,460 to next payout milestone</div>
                </div>

                <!-- WITHDRAW HISTORY -->
                <div class="card">
                  <h3>Total Earned</h3>
                  <div class="amount">KES 71,140</div>
                  <div class="sub-info">12 successful withdrawalss</div>
                  <div class="growth up">▲ +15% from last month</div>
                  <div class="sub-info">Endless Payouts in line</div>
                </div>

                <!-- NETWORK SIZE -->
                <div class="card">
                  <div class="amount"><?php echo $totalNetwork; ?> Agents</div>
                  <div class="sub-info">Level 1: <strong><?php echo $level1Count; ?></strong></div>
                  <div class="sub-info">Level 2: <strong><?php echo $level2Count; ?></strong></div>
                  <div class="sub-info">Level 3: <strong><?php echo $level3Count; ?></strong></div>
                  <?php
                    $growthClass = "growth up";
                    $arrow = "▲";
                    if ($highestLevel === "None") {
                      $growthClass = "growth down";
                      $arrow = "▼";
                    }
                  ?>
                  <div class="<?php echo $growthClass; ?>">
                    <?php echo $arrow; ?> +<?php echo $newThisMonth; ?> new in last 28 days
                  </div>
                </div>

                <!-- ADVERTISING -->
                <div class="card">
                  <h3>Withdrawal Status</h3>
                  <span class="wStatus">Eligible</span>
                  <div class="sub-info-m">Minimum threshold met</div>
                  <button>Withdraw</button>
                  <div class="growth up">▲ +12% increase</div>
                </div>

                <!-- ADVERTISING --><!-- 
                <div class="card">
                  <h3>Product Advertising Earnings</h3>
                  <div class="amount">KES 5,400</div>
                  <div class="sub-info">32 conversions this month</div>
                  <div class="growth up">▲ +12% increase</div>
                  <div class="sub-info">Conversion Rate: 4.8%</div>
                </div> -->

              </div>

              <!-- AFFILIATE BREAKDOWN -->
              <div class="grid" style="margin-top:20px;">

                <div class="card agency-longtrm-stats">
                  <h3>Affiliate Earnings Breakdown</h3>

                  <div class="level-row">
                    <span>Level 1 (100 KES)</span>
                    <strong>KES <?php echo number_format($lvl1Earn, 2); ?></strong>
                  </div>

                  <div class="level-row">
                    <span>Level 2 (40 KES)</span>
                    <strong>KES <?php echo number_format($lvl2Earn, 2); ?></strong>
                  </div>

                  <div class="level-row">
                    <span>Level 3 (20 KES)</span>
                    <strong>KES <?php echo number_format($lvl3Earn, 2); ?></strong>
                  </div>

                  <div class="sub-info">
                  Highest earning level: <strong><?php echo $highestLevel; ?></strong>
                  </div>
                </div>

                <div class="card agency-lincods">
                  <div>
                    <h3>Referral Performance</h3>
                    <div class="sub-info">Clicks this month: 73</div>
                    <div class="sub-info">Agent Signups: <?php echo $newThisMonth; ?></div>
                    <div class="sub-info">Activation Rate: 62%</div>
                    <div class="growth up">▲ +12% better than last month</div>
                  </div>
                  <div class="lincod-container">

                    <div class="lincod-box">

                      <span class="agency_link">
                        Your Agency link:

                        <i class="fa-solid fa-copy" onclick="copyAgencyLink()"></i>

                        <button class="share-btn" onclick="toggleShareMenu()">
                          <i class="fa-solid fa-share-nodes"></i> Share
                        </button>

                      </span>

                      <input
                        type="text"
                        id="agencyLinkInput"
                        value="<?php echo htmlspecialchars($agencyLink); ?>"
                        name="agency_link"
                        disabled
                      >

                      <!-- SHARE MENU -->
                      <div class="share-menu" id="shareMenu">

                        <button onclick="shareWhatsApp()">
                          <i class="fa-brands fa-whatsapp"></i> WhatsApp
                        </button>

                        <button onclick="shareFacebook()">
                          <i class="fa-brands fa-facebook"></i> Facebook
                        </button>

                        <button onclick="shareTwitter()">
                          <i class="fa-brands fa-x-twitter"></i> X
                        </button>

                        <button onclick="shareEmail()">
                          <i class="fa-solid fa-envelope"></i> Email
                        </button>

                        <button onclick="shareNative()">
                          <i class="fa-solid fa-mobile"></i> More Apps
                        </button>

                      </div>

                    </div>


                    <div class="lincod-box">

                      <span class="agency_code">
                        Your Agency Code:
                        <i class="fa-solid fa-copy" onclick="copyAgencyCode()"></i>
                      </span>

                      <input
                        type="text"
                        id="agencyCodeInput"
                        value="<?php echo htmlspecialchars($agencyCode); ?>"
                        name="agency_code"
                        disabled
                      >

                    </div>

                  </div>                  
                </div>

              </div>

            </div>
          </div>
          
          <div id="add-products" class="tab-panel">
            <div class="tab-top">
              <p>Add to your Agency</em> <br><strong>Submit new agent's details to be added <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleAgentAdd(false)">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>

            </div>
            <div class="form-wrapper">
              <form method="POST" enctype="multipart/form-data">
                <h1>Add New Agent Details</h1>
                <?php if (!empty($agent_error)): ?>
                  <p class="errorMessage">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($agent_error, ENT_QUOTES, 'UTF-8'); ?>
                  </p>
                <?php elseif (!empty($agent_success)): ?>
                  <p class="successMessage">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= strip_tags($agent_success, '<span>'); ?>
                  </p>
                <?php endif; ?>
                <div class="formBody">
                  <div class="inp-box">
                    <label>Agent's Full Name</label>
                    <input type="text" value="<?= $agent_full_name ?>" name="full_name" placeholder="Full Name" required>
                  </div>
                  <div class="inp-box">
                    <label>Agent's Username</label>
                    <input type="text" value="<?= $agent_username ?>" name="username" placeholder="e.g blessedemmanuel254" required>
                  </div>
                  <div class="inp-box">
                    <label>Agent's Email ID</label>
                    <input type="email" value="<?= $agent_email ?>" name="email" placeholder="john@example.com" required>
                  </div>
                  <div class="inp-box">
                    <label>Agent's Phone</label>
                    <input type="text" value="<?= $agent_phone ?>" name="phone" placeholder="075***630" required>
                  </div>
                  <div class="inp-box">

                    <label>Country</label>
                    <select name="country" required>
                      <option value=""><p>-- Select Country --</p></option>
                      <option value="Kenya" <?php echo ($agent_country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                      <option value="Kenya" <?php echo ($agent_country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                      <option value="Kenya" <?php echo ($agent_country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                    </select>
                  </div>
                  <div class="inp-box">

                    <label>County</label>
                    <select name="county" required>
                      <option value=""><p>-- Select County --</p></option>
                      <option value="Kilifi" <?php echo ($agent_county === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                      <option value="Kenya" <?php echo ($agent_county === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                      <option value="Kenya" <?php echo ($agent_county === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                    </select>
                  </div>
                  <div class="inp-box">
                    <label>Agent's Address</label>
                    <input type="text" value="<?= $agent_address ?>" name="address" placeholder="eg. Kilifi town" required>
                  </div>
                  <div class="inp-box">

                    <label>Ward</label>
                    <select name="ward" required>
                      <option value=""><p>-- Select Ward --</p></option>
                      <option value="Sokoni Ward" <?php echo ($agent_ward === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                      <option value="Kenya" <?php echo ($agent_ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                      <option value="Kenya" <?php echo ($agent_ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                    </select>
                  </div>
                  <div></div>
                  <button type="submit">
                    Submit Details
                  </button>
                </div>

              </form>
            </div>
          </div>
          
          <div id="funds" class="tab-panel">
            <p>Access your earnings</em> <br><strong>Withdraw funds you’ve earned from completed sales and agency <i class="fa-regular fa-circle-check"></i></strong></p>
            
            <div class="form-wrapper agency">
              <form method="POST" enctype="multipart/form-data">
                <h1>Withdraw Funds</h1>
                <?php if (!empty($error)): ?>
                  <p class="errorMessage">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                  <p class="successMessage">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= $success; ?>
                  </p>
                <?php endif; ?>
                <select name="" id="" class="walletChange">
                  <option value="Sales Wallet">Sales Wallet</option>
                  <option value="Agency Wallet">Agency Wallet</option>
                </select>
                <div class="formBody agency" id="salesWallet">
                  <!-- ADVERTISING -->
                  <div class="card">
                    <h3>Sales Wallet Balance</h3>
                    <div class="amount">KES 5,400</div>
                    <div class="sub-info">32 conversions this month</div>
                    <div class="growth up">▲ +12% increase</div>
                    <div class="sub-info">Conversion Rate: 4.8%</div>
                  </div>
                  <div>
                    <div class="inp-box">
                      <label>Withdraw from Sales</label>
                      <input type="number" placeholder="Enter amount" min="0">
                      <button type="button">Request Withdrawal</button>
                    </div>
                  </div>
                </div>
                <div class="formBody agency" id="agencyWallet">
                  <!-- AGENCY WALLET -->
                  <div class="card">
                    <h3>Agency Wallet Balance</h3>
                    <div class="amount">KES 12,540</div>
                    <div class="sub-info">Last withdrawal: 3 days ago</div>
                    <div class="growth up">▲ +18% from last month</div>
                    <div class="progress">
                      <div class="progress-fill"></div>
                    </div>
                    <div class="sub-info">KES 2,460 to next payout milestone</div>
                  </div>

                  <div>
                    <div class="inp-box">
                      <label>Withdraw from Agency</label>
                      <input type="number" placeholder="Enter amount" min="0">
                      <button type="button">Request Withdrawal</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="tabs-container strongRed" id="toggleMarketTypeTabAgent">
        <div class="tabs">
          <button class="tab-btn-mtype" data-tab="products">Products</button>
          <button class="tab-btn-mtype" data-tab="services">Services</button>
          <button class="tab-btn-mtype" data-tab="rentals">Rentals</button>
        </div>

        <div class="tab-content">
          <div id="products" class="tab-panel-mtype">
            <div class="tab-top">
              <p>Quality goods from trusted vendors. <br><strong>Please select Market type <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToAgent()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>
            </div>

            <div class="cards">
              <!-- LOCAL -->
              <a class="card" onclick="openAgentMarketSource()">
                <i class="fa-solid fa-location-dot"></i>
                <h2>Local Market</h2>
                <p>
                  Discover products near you.
                </p>
                <div class="label">
                  <p>Local</p>
                  <button>View Market</button>

                </div>
              </a>

              <!-- NATIONAL (MOST VISITED) -->
              <a class="card">
                <div class="tag">MOST VISITED</div>
                <i class="fa-solid fa-flag-usa"></i>
                <h2>National Market</h2>
                <p>
                  Browse products from across the country.
                </p>
                <div class="label">
                  <p>National</p>
                  <button>View Market</button>

                </div>
              </a>

              <!-- GLOBAL -->
              <a class="card" onclick="openMarketSource('shopsL')">
                <i class="fa-solid fa-earth-americas"></i>
                <h2>Global Market</h2>
                <p>
                  Explore international products.
                </p>
                <div class="label">
                  <p>Global</p>
                  <button>View Market</button>

                </div>
              </a>
            </div>
          </div>

          <div id="services" class="tab-panel-mtype">
            <div class="tab-top">
              <p>Professional services delivered with reliability.<br><strong>Please select Market type <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToAgent()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>
            </div>

            <div class="cards">
              <!-- LOCAL -->
              <a class="card" onclick="openMarketSource('shopsL')">
                <div class="tag">MOST VISITED</div>
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <h2>Local Services</h2>
                <p>
                  Get reliable services from professionals near you.
                </p>
                <div class="label">
                  <p>Local</p>
                  <button>View Services</button>

                </div>
              </a>

              <!-- NATIONAL (MOST VISITED) -->
              <a class="card">
                <i class="fa-solid fa-laptop-code"></i>
                <h2>National Services</h2>
                <p>
                  Access verified service providers from across the country.
                </p>
                <div class="label">
                  <p>National</p>
                  <button>View Services</button>

                </div>
              </a>

              <!-- GLOBAL -->
              <a class="card" onclick="openMarketSource('shopsL')">
                <i class="fa-solid fa-globe"></i>
                <h2>Global Services</h2>
                <p>
                  Connect with international experts and remote professionals.
                </p>
                <div class="label">
                  <p>Global</p>
                  <button>View Services</button>

                </div>
              </a>
            </div>
          </div>

          <div id="rentals" class="tab-panel-mtype">
            <div class="tab-top">
              <p>Affordable rentals for homes, vehicles and equipment.<br><strong>Please select Market type <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToAgent()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>
            </div>

            <div class="cards">
              <!-- LOCAL -->
              <a class="card" onclick="openMarketSource('shopsL')">
                <div class="tag">MOST VISITED</div>
                <i class="fa-solid fa-house"></i>
                <h2>Local Rentals</h2>
                <p>
                  Find rentals close to you including homes, vehicles, tools, and equipment.
                </p>
                <div class="label">
                  <p>Local</p>
                  <button>View Rentals</button>

                </div>
              </a>

              <!-- NATIONAL (MOST VISITED) -->
              <a class="card">
                <i class="fa-solid fa-building"></i>
                <h2>National Rentals</h2>
                <p>
                  Browse rental options available across the country.
                </p>
                <div class="label">
                  <p>National</p>
                  <button>View Rentals</button>

                </div>
              </a>

              <!-- GLOBAL -->
              <a class="card">
                <i class="fa-solid fa-jet-fighter-up"></i>
                <h2>Global Rentals</h2>
                <p>
                  Access international rental opportunities for travel, relocation, and cross-border projects.
                </p>
                <div class="label">
                  <p>Global</p>
                  <button>View Rentals</button>

                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="tabs-container toggleMarketSourceTab">
        <div class="tabs">
          <button class="tab-btn-msource" data-tab="shops">Shops</button>
          <button class="tab-btn-msource" data-tab="supermarkets">Supermarkets</button><!-- 
          <button class="tab-btn-msource" data-tab="rentals">Rentals</button> -->
        </div>

        <div class="tab-content">
          <div id="shops" class="tab-panel-msource">
            <div class="tab-top">
              <p>Showing markets in <em>Sokoni Ward</em> <br><strong>Please select the market source <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToAgentMarketTypes()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>
            </div>

            <!-- SELLERS LIST -->
            <div class="sellers">

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">MC</div>
                  <div>
                    <div class="name">Main Canteen</div>
                    <div class="rating">★★★★★ (41)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>23k&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoGold">200+</div>
                  <div class="bsType">Business Type : <i>Kiosk</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">BE</div>
                  <div>
                    <div class="name">BerryFerry</div>
                    <div class="rating">★★★★★ (165)</div>
                    <div class="meta"><h2>3&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>4&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeDefault">13</div>
                  <div class="bsType">Business Type : <i>Canteen</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">WW</div>
                  <div>
                    <div class="name">Wwrightbright</div>
                    <div class="rating">★★★★★ (11)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>2&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoPro">100+</div>
                  <div class="bsType">Business Type : <i>Kibanda</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

            </div>
          </div>

          <div id="supermarkets" class="tab-panel-msource">
            <div class="tab-top">
              <p>Showing markets in <em>Sokoni Ward</em> <br><strong>Please select the market source <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToAgentMarketTypes()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>
            </div>

            <!-- SELLERS LIST -->
            <div class="sellers">

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">NS</div>
                  <div>
                    <div class="name">Naivas Supermaket</div>
                    <div class="rating">★★★★★ (41)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>23k&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoGold">1000+</div>
                  <div class="bsType">Business Type : <i>Kiosk</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">CM</div>
                  <div>
                    <div class="name">Cherowamaye Minimarket</div>
                    <div class="rating">★★★★★ (165)</div>
                    <div class="meta"><h2>3&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>4&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeDefault">287</div>
                  <div class="bsType">Business Type : <i>Canteen</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">AW</div>
                  <div>
                    <div class="name">Abul Wholesale</div>
                    <div class="rating">★★★★★ (11)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>2&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoPro">500+</div>
                  <div class="bsType">Business Type : <i>Kibanda</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

            </div>
          </div>
        </div>
      </div>

      <h1>Recent Earnings Activity</h1>

      <div class="filter-bar">
        <select id="statusFilter">
          <option value="all">All Orders</option>
          <option value="Delivered">Delivered</option>
          <option value="Shipped">Shipped</option>
          <option value="Processing">Processing</option>
        </select>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="table-wrapper agentEarningsTrack">
        <table id="ordersTable">
          <thead>
            <tr>
              <th>Date</th>
              <th>Source</th>
              <th>Status</th>
              <th>Level</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr data-status="Delivered">
              <td>12 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge pending">Pending</span></td>
              <td>Level&nbsp;1</td>
              <td>KES&nbsp;100</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td><span class="badge processing">Processing</span></td>
              <td>Product</td>
              <td>KES&nbsp;7700</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;2</td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Processing">
              <td>14 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Product</td>
              <td>KES&nbsp;800</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;3</td>
              <td>KES&nbsp;20</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;2</td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;3</td>
              <td>KES&nbsp;20</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleAgentEarningsTrack()">View&nbsp;Activity</button> to access all your recent earnings.</p>

    </main>

    <main class="buyerMain" id="productsAgentMain">
      <div class="tab-top">
        <p>Products main page<br><strong>View products, download and post products <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleAgentProductsPage()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>
      <div class="table-wrapper sellerOrdersTrack active">
        <div class="header">
          <h1>Maket Hub Daily Products</h1>
          <p>Download and post across all platforms today.</p>
        </div>

        <div class="products-grid" id="productsContainer"></div>
      </div>
      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleAgentProductsPage()">Go&nbsp;back</button> to continue with sales.</p>
    </main>

    <main class="buyerMain" id="earningsTrackMain">
      <div class="tab-top">
        <p>Recent Earnings History<br><strong>View and track your recent flow of income <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleAgentEarningsTrack()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>
      <div class="table-wrapper agentEarningsTrack">
        <table id="agentEarnings">
          <thead>
            <tr>
              <th>Date</th>
              <th>Source</th>
              <th>Level</th>
              <th>Name</th>
              <th>Status</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr data-status="Delivered">
              <td>12 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;1</td>
              <td>Sony_254</td>
              <td><span class="badge pending">Pending</span></td>
              <td>KES&nbsp;100</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td>Product</td>
              <td>Passion Juice</td>
              <td><span class="badge processing">Processing</span></td>
              <td>KES&nbsp;7700</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;2</td>
              <td>Levi254</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Processing">
              <td>14 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td>Product</td>
              <td>Oraimo Headphones</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;800</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;3</td>
              <td>Agentrael</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;20</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;2</td>
              <td>Kalvani</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;3</td>
              <td>Blessedemmanuel254</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;20</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleAgentEarningsTrack()">Go&nbsp;back</button> to continue with sales.</p>
    </main>
    <main class="agentWithdrawalH" id="agentWithdrawalH">
      <div class="tab-top">
        <p>Recent Withdrawal History<br><strong>Review your withdrawals and continue earning <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleAgentWithdrawals()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>
      
      <div class="containerWH">
        <h2>Withdrawal History</h2>

        <div class="withdraw-grid">
          <!-- Paid -->
          <div class="withdraw-card">
            <div class="withdraw-left">
              <div class="withdraw-title">
                <i class="fa-solid fa-wallet"></i> Sales Wallet Withdrawal
              </div>
              <div class="withdraw-meta">
                <i class="fa-regular fa-calendar"></i> 24 Feb 2026<br>
                <i class="fa-solid fa-mobile-screen-button"></i> M-Pesa • 07********
              </div>
              <div class="withdraw-reference">
                <i class="fa-solid fa-hashtag"></i> TRX89374219
              </div>
            </div>
            <div class="withdraw-right">
              <div class="withdraw-amount">KES 3,500</div>
              <div class="status paid"><i class="fa-solid fa-check-circle"></i> Paid</div>
            </div>
          </div>

          <!-- Processing -->
          <div class="withdraw-card">
            <div class="withdraw-left">
              <div class="withdraw-title">
                <i class="fa-solid fa-wallet"></i> Affiliate Wallet Withdrawal
              </div>
              <div class="withdraw-meta">
                <i class="fa-regular fa-calendar"></i> 22 Feb 2026<br>
                <i class="fa-solid fa-building-columns"></i> Bank Transfer
              </div>
              <div class="withdraw-reference">
                <i class="fa-solid fa-hashtag"></i> TRX20483910
              </div>
            </div>
            <div class="withdraw-right">
              <div class="withdraw-amount">KES 5,000</div>
              <div class="status processing"><i class="fa-solid fa-spinner fa-spin"></i> Processing</div>
            </div>
          </div>

          <!-- Pending -->
          <div class="withdraw-card">
            <div class="withdraw-left">
              <div class="withdraw-title">
                <i class="fa-solid fa-wallet"></i> Sales Wallet Withdrawal
              </div>
              <div class="withdraw-meta">
                <i class="fa-regular fa-calendar"></i> 20 Feb 2026<br>
                <i class="fa-solid fa-mobile-screen-button"></i> M-Pesa
              </div>
              <div class="withdraw-reference">
                <i class="fa-solid fa-hashtag"></i> TRX56473829
              </div>
            </div>
            <div class="withdraw-right">
              <div class="withdraw-amount">KES 1,800</div>
              <div class="status pending"><i class="fa-solid fa-clock"></i> Pending</div>
            </div>
          </div>

          <!-- Failed -->
          <div class="withdraw-card">
            <div class="withdraw-left">
              <div class="withdraw-title">
                <i class="fa-solid fa-wallet"></i> Affiliate Wallet Withdrawal
              </div>
              <div class="withdraw-meta">
                <i class="fa-regular fa-calendar"></i> 18 Feb 2026<br>
                <i class="fa-solid fa-mobile-screen-button"></i> M-Pesa
              </div>
              <div class="withdraw-reference">
                <i class="fa-solid fa-hashtag"></i> TRX99887766
              </div>
            </div>
            <div class="withdraw-right">
              <div class="withdraw-amount">KES 900</div>
              <div class="status failed"><i class="fa-solid fa-circle-xmark"></i> Failed</div>
            </div>
          </div>

          <!-- Rejected -->
          <div class="withdraw-card">
            <div class="withdraw-left">
              <div class="withdraw-title">
                <i class="fa-solid fa-wallet"></i> Sales Wallet Withdrawal
              </div>
              <div class="withdraw-meta">
                <i class="fa-regular fa-calendar"></i> 15 Feb 2026<br>
                <i class="fa-solid fa-triangle-exclamation"></i> Verification issue
              </div>
              <div class="withdraw-reference">
                <i class="fa-solid fa-hashtag"></i> TRX11223344
              </div>
            </div>
            <div class="withdraw-right">
              <div class="withdraw-amount">KES 2,400</div>
              <div class="status rejected"><i class="fa-solid fa-ban"></i> Rejected</div>
            </div>
          </div>
        </div>
      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleAgentWithdrawals()">Go&nbsp;back</button> to continue with sales.</p>
    </main>

    <main class="buyerMain" id="orderMain">
      <div class="tab-top">
        <p>Track your purchases<br><strong>View order and delivery status <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleAgentOrdersTrack()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>

      <div class="order-group">
        <div class="order-header">
          <div>
            <strong>Order #ORD-90321</strong><br>
            <span>Placed on 12 Feb 2026</span>
          </div>
          <div><strong>3</strong> Items</div>
        </div>

        <div class="order-items-grid">

          <!-- ITEM 1 -->
          <div class="order-item">
            <div class="item-top">
              <div class="item-info">
                <h4>Wireless Headphones</h4>
                <p>Seller: TechZone</p>
                <p>Qty: 1 • Total: KES 3,200</p>
                <p>Status: <span class="status shipped">Shipped</span></p>
                <span class="market-badge">National</span>
              </div>
              <img src="Images/Maket Hub Logo.avif" alt="Product">
            </div>

            <div class="item-actions">
              <button class="toggleOrd" data-target="d1">View details</button>
            </div>

            <div class="item-extra" id="d1">
              <div class="extra-box">
                <strong>Tracking</strong><br>
                Packed → Shipped
              </div>
              <div class="extra-box">
                <strong>Payment</strong><br>
                M-Pesa • KES 3,200
              </div>
            </div>
          </div>

          <!-- ITEM 2 -->
          <div class="order-item">
            <div class="item-top">
              <div class="item-info">
                <h4>Office Chair</h4>
                <p>Seller: Comfort Furnish</p>
                <p>Qty: 2 • Total: KES 18,000</p>
                <p>Status: <span class="status processing">Processing</span></p>
                <span class="market-badge">Local</span>
              </div>
              <img src="Images/Maket Hub Logo.avif" alt="Product">
            </div>

            <div class="item-actions">
              <button class="toggleOrd" data-target="d2">View details</button>
            </div>

            <div class="item-extra" id="d2">
              <div class="extra-box">
                Awaiting dispatch
              </div>
            </div>
          </div>

        </div>
      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleAgentOrdersTrack()">Go&nbsp;back</button> to continue shopping.</p>
    </main>
    <footer>
      <p>&copy; 2025/2026, Maket Hub.shop, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>
  <script>
    // DataTables Script Js
    $(document).ready(function () {
      $('#agentEarnings').DataTable({
        pagingType: "simple_numbers", // only numbers + prev/next
        pageLength: 15,               // rows per page
        lengthChange: false,          // hide "Show X entries"
        searching: true,              // keep search box
        ordering: true,               // column sorting
        stateSave: true,              // ✅ remembers pagination, search & sort
        language: {
          paginate: {
            previous: "PREV",
            next: "NEXT"
          }
        }
      });
    });
  </script>
</body>
</html>
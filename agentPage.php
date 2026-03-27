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
  "SELECT account_type, is_verified, status, subscription_expires_at 
   FROM users 
   WHERE user_id = ? 
   LIMIT 1"
);

$roleStmt->bind_param("i", $_SESSION['user_id']);
$roleStmt->execute();
$roleStmt->bind_result($accountType, $isVerified, $status, $expiresAt);
$roleStmt->fetch();
$roleStmt->close();

$isExpired = false;

if (!empty($expiresAt)) {
  $isExpired = (strtotime($expiresAt) < time());
}


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

/* ---------------------------
   FETCH WALLET BALANCES & TRANSACTIONS (AGENCY + SALES)
--------------------------- */

$agencyBalance   = 0;
$totalTransacted = 0;
$salesBalance    = 0;
$totalWithdrawals = 0;
$totalWithdrawn   = 0;
$totalSales       = 0;
$totalSalesAmount = 0;

// Combined query for balances + withdrawal/sales totals
$stmt = $conn->prepare("
    SELECT 
        w.wallet_type,
        w.balance,
        w.total_transacted,
        -- Agency withdrawals
        (SELECT COUNT(*) 
         FROM wallet_transactions wt 
         WHERE wt.wallet_id = w.wallet_id 
           AND wt.transaction_type = 'debit' 
           AND wt.status = 'completed') AS total_withdrawals,
        (SELECT COALESCE(SUM(wt.amount),0) 
         FROM wallet_transactions wt 
         WHERE wt.wallet_id = w.wallet_id 
           AND wt.transaction_type = 'debit' 
           AND wt.status = 'completed') AS total_withdrawn,
        -- Sales earnings (this month)
        (SELECT COUNT(*) 
         FROM wallet_transactions wt 
         WHERE wt.wallet_id = w.wallet_id 
           AND wt.transaction_type = 'credit' 
           AND wt.status = 'completed'
           AND w.wallet_type = 'sales'
           AND wt.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS sales_count,
        (SELECT COALESCE(SUM(wt.amount),0) 
         FROM wallet_transactions wt 
         WHERE wt.wallet_id = w.wallet_id 
           AND wt.transaction_type = 'credit' 
           AND wt.status = 'completed'
           AND w.wallet_type = 'sales'
           AND wt.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS sales_sum
    FROM wallets w
    WHERE w.user_id = ?
      AND w.wallet_type IN ('agency','sales')
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['wallet_type'] === 'agency') {
        $agencyBalance   = (float)$row['balance'];
        $totalTransacted = (float)$row['total_transacted'];
        $totalWithdrawals = (int)$row['total_withdrawals'];
        $totalWithdrawn   = (float)$row['total_withdrawn'];
    } elseif ($row['wallet_type'] === 'sales') {
        $salesBalance     = (float)$row['balance'];
        $totalSales       = (int)$row['sales_count'];
        $totalSalesAmount = (float)$row['sales_sum'];
    }
}
$stmt->close();

/* ---------------------------
   WITHDRAWAL RULES / MINIMUM THRESHOLDS
--------------------------- */
$agencyMin = 400;
$salesMin  = 500;

$isAgencyEligible = $agencyBalance >= $agencyMin;
$isSalesEligible  = $salesBalance >= $salesMin;
$progressPercent = min(($agencyBalance / $agencyMin) * 100, 100);
$remaining = max($agencyMin - $agencyBalance, 0);
$progressPercentSales = min(($salesBalance / $salesMin) * 100, 100);
$remainingSales = max($salesMin - $salesBalance, 0);

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
    $agent_error = "All fields are required!";
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

          $newUserId = $stmt->insert_id;

          /* =========================
              INSERT COMMISSIONS (PENDING)
          ========================= */

          $commissionLevels = [
              1 => 100,
              2 => 40,
              3 => 20
          ];

          $currentReferrer = $user_id; // direct referrer (logged-in agent)
          $level = 1;

          while ($currentReferrer && $level <= 3) {

              $amount = $commissionLevels[$level];

              // Insert pending commission
              $stmtCom = $conn->prepare("
                  INSERT INTO agent_commissions
                  (agent_id, source_user_id, level, amount, commission_type, created_at, status)
                  VALUES (?, ?, ?, ?, 'activation', NOW(), 'pending')
              ");

              $stmtCom->bind_param(
                  "iiid",
                  $currentReferrer,
                  $newUserId,
                  $level,
                  $amount
              );

              $stmtCom->execute();
              $stmtCom->close();

              // Move to next upline
              $stmtRef = $conn->prepare("
                  SELECT referred_by FROM users WHERE user_id = ?
              ");
              $stmtRef->bind_param("i", $currentReferrer);
              $stmtRef->execute();
              $stmtRef->bind_result($nextReferrer);
              $stmtRef->fetch();
              $stmtRef->close();

              $currentReferrer = $nextReferrer;
              $level++;
          }

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

$baseAgencyLink = "http://localhost/MaketHub.com-Project/agentRegister.php";

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

  // Default
  $highestLevel = "None";
  $highestValue = 0;

  // Check if all levels are equal
  if ($lvl1Earn === $lvl2Earn && $lvl2Earn === $lvl3Earn) {
    $highestLevel = "None";
    $highestValue = $lvl1Earn; // all same anyway
  } else {
    // Find the actual highest
    $highestValue = max($lvl1Earn, $lvl2Earn, $lvl3Earn);

    if ($highestValue === $lvl1Earn) {
        $highestLevel = "Level 1";
    } elseif ($highestValue === $lvl2Earn) {
        $highestLevel = "Level 2";
    } else {
        $highestLevel = "Level 3";
    }
  }


  }


  $commissions = [];

  $stmt = $conn->prepare("
    SELECT 
      ac.source_user_id,
      ac.`level`,
      ac.amount,
      ac.status,
      ac.commission_type,
      ac.created_at,
      u.username,
      u.phone,
      u.email
    FROM agent_commissions ac
    LEFT JOIN users u ON u.user_id = ac.source_user_id
    WHERE ac.agent_id = ?
    ORDER BY ac.created_at DESC
  ");

  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $commissions[] = $row;
  }

  $stmt->close();

  // Current logged in user
  $currentUserId = $_SESSION['user_id'];

  $sellerQuery = "
      SELECT 
          u.user_id,
          u.username,
          u.business_name,
          u.business_type,
          u.market_scope,
          u.ward,
          u.profile_image,
          u.address,
          (
            SELECT COUNT(DISTINCT oi.order_id)
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.seller_id = u.user_id
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          ) AS total_orders,
          (
            SELECT COUNT(*)
            FROM user_followers uf
            WHERE uf.follower_id = u.user_id
          ) AS following_count,
          (
            SELECT COUNT(*)
            FROM user_followers uf
            WHERE uf.followed_id = u.user_id
          ) AS followers_count,
          (
            SELECT COUNT(*)
            FROM user_followers uf
            WHERE uf.follower_id = ?
            AND uf.followed_id = u.user_id
          ) AS is_following

      FROM users u
      WHERE u.account_type = 'seller'

      ORDER BY total_orders DESC
      LIMIT 50
  ";

  $stmt = $conn->prepare($sellerQuery);
  $stmt->bind_param("i", $currentUserId);
  $stmt->execute();
  $result = $stmt->get_result();

  $shops = [];
  $supermarkets = [];

  $shopsN = [];
  $supermarketsN = [];

  $shopsG = [];
  $supermarketsG = [];

  while ($row = $result->fetch_assoc()) {

    $row['business_name'] = ucwords(strtolower($row['business_name']));
    $row['business_type'] = ucwords(strtolower($row['business_type']));
    $row['address'] = ucwords(strtolower($row['address']));

    $type = strtolower(trim($row['business_type']));
    $scope = strtolower(trim($row['market_scope']));

    /* ---------- LOCAL ---------- */
    if ($scope === "local") {

      if (in_array($type, ['shop','kiosk','canteen','kibanda'])) {
        $shops[] = $row;
      }

      elseif (in_array($type, ['supermarket','wholesale'])) {
        $supermarkets[] = $row;
      }

    }

    /* ---------- NATIONAL ---------- */
    elseif ($scope === "national") {

      if (in_array($type, ['shop','kiosk','canteen','kibanda'])) {
        $shopsN[] = $row;
      }

      elseif (in_array($type, ['supermarket','wholesale'])) {
        $supermarketsN[] = $row;
      }

    }

    /* ---------- GLOBAL ---------- */
    elseif ($scope === "global") {

      if (in_array($type, ['shop','kiosk','canteen','kibanda'])) {
        $shopsG[] = $row;
      }

      elseif (in_array($type, ['supermarket','wholesale'])) {
        $supermarketsG[] = $row;
      }

    }

  }
  $stmt->close();

  $markets = [
    'L' => [
      'shops' => $shops,
      'supermarkets' => $supermarkets
    ],
    'N' => [
      'shops' => $shopsN,
      'supermarkets' => $supermarketsN
    ],
    'G' => [
      'shops' => $shopsG,
      'supermarkets' => $supermarketsG
    ]
  ];

$activeProducts = [];

$res = $conn->query("
  SELECT id, product_name, price, currency, description, image, download_file 
  FROM markethub_products 
  WHERE is_active = 1
  ORDER BY created_at DESC
");

while ($row = $res->fetch_assoc()) {
  $activeProducts[] = $row;
}

// ------------------------
// HANDLE PRODUCT IMAGE DOWNLOAD
// ------------------------
if (isset($_GET['download_product_id'])) {

    $productId = (int)$_GET['download_product_id'];

    $stmt = $conn->prepare("
        SELECT product_name, price, currency, image 
        FROM markethub_products 
        WHERE id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$product = $result->fetch_assoc()) {
        die("Product not found");
    }

    $imagePath = $product['image'];
    if (!file_exists($imagePath)) {
        die("Image not found");
    }

    // ------------------------
    // Load original image
    // ------------------------
    $imgInfo = getimagesize($imagePath);
    switch ($imgInfo['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($imagePath);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($imagePath);
            break;
        default:
            die("Unsupported image type");
    }

    // ------------------------
    // Overlay name + price
    // ------------------------
    $textColor = imagecolorallocate($image, 255, 255, 255); // white
    $bgColor   = imagecolorallocatealpha($image, 0, 0, 0, 60); // semi-transparent black

    $fontSize = 5;
    $padding = 10;

    $name  = $product['product_name'];
    $price = $product['currency'] . ' ' . $product['price'];
    $text  = $name . " - " . $price;

    $imgWidth  = imagesx($image);
    $imgHeight = imagesy($image);

    $textWidth  = imagefontwidth($fontSize) * strlen($text);
    $textHeight = imagefontheight($fontSize);

    $x = ($imgWidth - $textWidth) / 2;
    $y = $imgHeight - $textHeight - 20;

    // Background box
    imagefilledrectangle(
        $image,
        $x - $padding,
        $y - $padding,
        $x + $textWidth + $padding,
        $y + $textHeight + $padding,
        $bgColor
    );

    // Draw text
    imagestring($image, $fontSize, $x, $y, $text, $textColor);

    // Output as JPG
    $filename = preg_replace('/\s+/', '_', $name . '_' . $product['price']) . ".jpg";

    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    imagejpeg($image, null, 90);
    imagedestroy($image);
    exit;
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
          <div class="profile-icon">
            <i class="fa-regular fa-user" onclick="toggleProfileOption()"></i>
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

        <?php elseif ($isExpired): ?>

          <span class="expired">
            <i class="fa-solid fa-clock"></i>&nbsp;Expired
          </span>

        <?php else: ?>

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
                  Get to order on Maket Hub like other users.
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
                  <h3>Agency Wallet Balance</h3>

                  <div class="amount">
                    KES <?= htmlspecialchars(number_format($agencyBalance, 2)) ?>
                  </div>

                  <div class="sub-info">Available for withdrawal</div>

                  <div class="growth up">▲ Live agency balance</div>

                  <div class="progress">
                    <div class="progress-fill" 
                        style="width: <?= htmlspecialchars($progressPercent) ?>%">
                    </div>
                  </div>

                  <?php if ($agencyBalance >= $agencyMin): ?>
                    
                    <!-- ✅ Milestone reached -->
                    <div class="sub-info">
                      🎉 Milestone reached!
                    </div>

                  <?php else: ?>
                    
                    <!-- ⏳ Still progressing -->
                    <div class="sub-info">
                      KES <?= htmlspecialchars(number_format($remaining, 2)) ?> to next milestone
                    </div>

                  <?php endif; ?>

                </div>

                <div class="card">
                  <h3>Sales Wallet Balance</h3>

                  <div class="amount">
                    KES <?= number_format($salesBalance, 2) ?>
                  </div>

                  <div class="sub-info">
                    <?php if ($totalSales > 0): ?>
                      Average per sale: KES <?= number_format($totalSalesAmount / $totalSales, 2) ?>
                    <?php else: ?>
                      Average per sale: KES 0.00
                    <?php endif; ?>
                  </div>

                  <div class="growth up">
                    ▲ Live sales earnings
                  </div>

                  <div class="progress">
                    <div class="progress-fill" 
                        style="width: <?= htmlspecialchars($progressPercentSales) ?>%">
                    </div>
                  </div>

                  <?php if ($salesBalance >= $salesMin): ?>
                    
                    <!-- ✅ Milestone reached -->
                    <div class="sub-info">
                      🎉 Kudos!
                    </div>

                  <?php else: ?>
                    
                    <!-- ⏳ Still progressing -->
                    <div class="sub-info">
                      KES <?= htmlspecialchars(number_format($remainingSales, 2)) ?> to next achievement
                    </div>

                  <?php endif; ?>
                </div>

                <!-- WITHDRAWAL HISTORY -->
                <div class="card">
                  <h3>Total Withdrawn</h3>

                  <div class="amount">
                    KES <?= htmlspecialchars(number_format($totalWithdrawn, 2)) ?>
                  </div>

                  <?php if ($totalWithdrawals > 0): ?>
                    <div class="sub-info">
                      <?= htmlspecialchars(
                        $totalWithdrawals . ' successful withdrawal' . ($totalWithdrawals > 1 ? 's' : '')
                      ) ?>
                    </div>
                  <?php endif; ?>

                  <div class="growth up">▲ Withdrawal history</div>

                  <div class="sub-info">Money you have cashed out</div>
                </div>

                <!-- NETWORK SIZE -->
                <div class="card">
                  <?php
                  $agentLabel = ($totalNetwork === 1) ? 'Agent' : 'Agents';
                  ?>

                  <div class="amount">
                    <?php echo $totalNetwork . ' ' . $agentLabel; ?>
                  </div>
                  <div class="sub-info">Level 1: <strong><?php echo $level1Count; ?></strong></div>
                  <div class="sub-info">Level 2: <strong><?php echo $level2Count; ?></strong></div>
                  <div class="sub-info">Level 3: <strong><?php echo $level3Count; ?></strong></div>
                  <?php
                  $growthClass = "growth up";
                  $arrow = "▲";

                  // If no earnings at all OR no highest level
                  if (
                      $highestLevel === "None" || $newThisMonth <= 0) {
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

                  <?php if ($isAgencyEligible || $isSalesEligible): ?>
                    <span class="wStatus">Eligible</span>
                    <div class="sub-info-m">Minimum threshold met</div>

                    <button>Withdraw</button>

                  <?php else: ?>
                    <span class="wStatus ineligible">Not Eligible</span>

                    <div class="sub-info-m">
                      <?php if (!$isAgencyEligible): ?>
                        Agency: KES <span><?= number_format($agencyMin - $agencyBalance, 2) ?></span> remaining<br>
                      <?php endif; ?>

                      <?php if (!$isSalesEligible): ?>
                        Sales: KES <span><?= number_format($salesMin - $salesBalance, 2) ?></span> remaining
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <div class="growth up">▲ Wallet status updated</div>
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

                    <div class="amount">
                      KES <?= number_format($salesBalance, 2) ?>
                    </div>

                    <div class="sub-info">
                      <?php if ($totalSales > 0): ?>
                        Average per sale: KES <?= number_format($totalSalesAmount / $totalSales, 2) ?>
                      <?php else: ?>
                        Average per sale: KES 0.00
                      <?php endif; ?>
                    </div>

                    <div class="growth up">
                      ▲ Live sales earnings
                    </div>

                    <div class="progress">
                      <div class="progress-fill" 
                          style="width: <?= htmlspecialchars($progressPercentSales) ?>%">
                      </div>
                    </div>

                    <?php if ($salesBalance >= $salesMin): ?>
                      
                      <!-- ✅ Milestone reached -->
                      <div class="sub-info">
                        🎉 Kudos!
                      </div>

                    <?php else: ?>
                      
                      <!-- ⏳ Still progressing -->
                      <div class="sub-info">
                        KES <?= htmlspecialchars(number_format($remainingSales, 2)) ?> to next achievement
                      </div>

                    <?php endif; ?>
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
                    <h3>Agency Balance</h3>

                    <div class="amount">
                      KES <?= htmlspecialchars(number_format($agencyBalance, 2)) ?>
                    </div>

                    <div class="sub-info">Available for withdrawal</div>

                    <div class="growth up">▲ Live wallet balance</div>

                    <div class="progress">
                      <div class="progress-fill" 
                          style="width: <?= htmlspecialchars($progressPercent) ?>%">
                      </div>
                    </div>

                    <?php if ($agencyBalance >= $agencyMin): ?>
                      
                      <!-- ✅ Milestone reached -->
                      <div class="sub-info">
                        🎉 Milestone reached!
                      </div>

                    <?php else: ?>
                      
                      <!-- ⏳ Still progressing -->
                      <div class="sub-info">
                        KES <?= htmlspecialchars(number_format($remaining, 2)) ?> to next milestone
                      </div>

                    <?php endif; ?>

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
          <button class="tab-btn-mtype" data-tab="services">Services</button><!-- 
          <button class="tab-btn-mtype" data-tab="rentals">Rentals</button> -->
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
              <a class="card" onclick="openAgentMarketSource('shopsL')">
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
              <a class="card" onclick="openAgentMarketSource('shopsN')">
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
              <a class="card" onclick="openAgentMarketSource('shopsG')">
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
              <a class="card">
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
              <a class="card">
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
              <a class="card">
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

      <?php foreach ($markets as $scope => $types): ?>

      <div class="tabs-container toggleMarketSourceTab" data-tab-storage="marketSource<?= $scope ?>Tabs">

        <div class="tabs">
          <?php foreach ($types as $type => $array): ?>
            <button class="tab-btn-msource" data-tab="<?= $type . $scope ?>">
              <?= ucfirst($type) ?>(<?= $scope ?>)
            </button>
          <?php endforeach; ?>
        </div>

        <div class="tab-content">

        <?php foreach ($types as $type => $sellers): ?>

          <div id="<?= $type . $scope ?>" class="tab-panel-msource">

            <div class="tab-top">
              <p>
                Showing markets in <em>Sokoni Ward</em><br>
                <strong>Please select the market source <i class="fa-regular fa-circle-check"></i></strong>
              </p>

              <button onclick="goBackToAgentMarketTypes()">
                <i class="fa-solid fa-circle-arrow-left"></i>
                <span>Go Back</span>
              </button>
            </div>

            <div class="sellers">

            <?php if (empty($sellers)): ?>

              <div class="no-market-message">
                No markets available.
              </div>

            <?php else: ?>

              <?php foreach ($sellers as $seller): ?>

              <?php
                $bName = htmlspecialchars($seller['business_name']);
                $bType = htmlspecialchars($seller['business_type']);
                $address = htmlspecialchars($seller['address']);

                $initials =
                  strtoupper(substr($bName,0,1)) .
                  (isset($bName[1]) ? strtoupper(substr($bName,1,1)) : '');

                $totalOrders = (int)$seller['total_orders'];

                if ($totalOrders < 100) {
                    $displayOrders = $totalOrders;
                    $badgeClass = 'promoBadgeDefault';
                } elseif ($totalOrders < 200) {
                    $displayOrders = "100+";
                    $badgeClass = 'promoBadgeGoGold';
                } else {
                    $displayOrders = "200+";
                    $badgeClass = 'promoBadgeGoPro';
                }
              ?>

              <div class="seller">

                <div class="seller-left">
                  <div class="avatar"><?= $initials ?></div>

                  <div>
                    <div class="name"><?= $bName ?></div>

                    <div class="rating">
                      ★★★★★ (<?= rand(5,200) ?>)
                    </div>

                    <div class="meta">

                      <h2 class="following-count" data-seller="<?= $seller['user_id'] ?>">
                        <?= $seller['following_count'] ?> <span>following</span>
                      </h2>

                      <h2
                        class="<?= $seller['is_following'] ? 'followingBtn':'followBtn' ?>"
                        data-seller="<?= $seller['user_id'] ?>"
                      >
                        <?= $seller['is_following'] ? 'Following':'Follow' ?>
                      </h2>

                    </div>

                    <div class="meta">
                      <h2 class="followers-count" data-seller="<?= $seller['user_id'] ?>">
                        <?= $seller['followers_count'] ?> <span>followers</span>
                      </h2>
                    </div>

                    <div class="bsInfo">
                      <strong>Location :</strong> <?= $address ?>
                    </div>

                  </div>
                </div>

                <a href="marketDisplay.php?seller=<?= $seller['user_id'] ?>" class="seller-right">

                  <div class="promo-badge-container">
                    Orders :
                    <div class="<?= $badgeClass ?>">
                      <?= $displayOrders ?>
                    </div>
                  </div>

                  <div class="bsType">
                    Business Type : <i><?= $bType ?></i>
                  </div>

                  <div class="action">
                    <button>View seller</button>
                  </div>

                </a>

              </div>

              <?php endforeach; ?>

            <?php endif; ?>

            </div>
            

          </div>

        <?php endforeach; ?>

        </div>

          <script>
          document.addEventListener('click', function (e) {
            const button = e.target.closest('.followBtn, .followingBtn');
            if (!button) return;

            e.preventDefault();

            const sellerId = button.dataset.seller;
            if (!sellerId) return;

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `seller_id=${sellerId}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Something went wrong');
                    return;
                }

                /* ---------- TOGGLE TEXT ---------- */
                button.textContent = data.is_following ? 'Following' : 'Follow';

                /* ---------- TOGGLE CLASS ---------- */
                if (data.is_following) {
                    button.classList.remove('followBtn');
                    button.classList.add('followingBtn');
                } else {
                    button.classList.remove('followingBtn');
                    button.classList.add('followBtn');
                }

                /* ---------- UPDATE COUNTS ---------- */
                const followersEl = document.querySelector(
                    `.followers-count[data-seller="${sellerId}"]`
                );
                const followingEl = document.querySelector(
                    `.following-count[data-seller="${sellerId}"]`
                );

                if (followersEl) {
                    followersEl.innerHTML = `${data.followers}&nbsp;<span>followers</span>`;
                }

                if (followingEl) {
                    followingEl.innerHTML = `${data.following}&nbsp;<span>following</span>`;
                }
            })
            .catch(() => {
                alert('Network error');
            });
          });
          </script>
      </div>

      <?php endforeach; ?>

      <h1>Recent Earnings Activity</h1>

      <div class="filter-bar">
        <select id="statusFilter">
          <option value="all">All Commissions</option>
          <option value="Paid">Paid</option>
          <option value="Pending">Pending</option>
        </select>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="table-wrapper agentEarningsTrack">
        <table id="ordersTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Level</th>
              <th>Source</th>
              <th>Talk</th>
              <th>Date</th>
            </tr>
          </thead>

          <tbody>
          <?php $count = 1; if (!empty($commissions)): ?>

            <?php foreach ($commissions as $row): 

              // Format date
              $date = date("d M Y", strtotime($row['created_at']));

              // Source
              $source = ($row['commission_type'] === 'activation')
                  ? 'Agent Activation'
                  : 'Products Sales';

              // Level
              $level = "Level " . (int)$row['level'];

              // Name (fallback if missing)
              $name = !empty($row['username']) 
                  ? $row['username'] 
                  : 'Deleted User';

              // Status (default since not stored)
              $status = ucfirst($row['status']);
              $statusClass = strtolower($row['status']);

              // Amount
              $amount = "KES " . number_format($row['amount'], 2);

              $phone = !empty($row['phone']) ? base64_decode($row['phone']) : '';
              $email = !empty($row['email']) ? base64_decode($row['email']) : '';
              
            ?>

              <tr data-status="<?php echo $status; ?>">
                <td><?= $count++ ?>.</td>
                <td><?php echo htmlspecialchars(ucwords(strtolower($name))); ?></td>
                <td>
                  <span class="badge <?php echo $statusClass; ?>">
                    <?php echo $status; ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($amount); ?></td>
                <td><?php echo htmlspecialchars($level); ?></td>
                <td><?php echo htmlspecialchars($source); ?></td>
                
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>
                  <div class="comm-dropdown">
                    <a href="tel:<?= htmlspecialchars($phone) ?>"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/<?= preg_replace('/\D/', '', $phone) ?>" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:<?= htmlspecialchars($email) ?>"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($date); ?></td>
              </tr>

            <?php endforeach; ?>

          <?php else: ?>

            <tr>
              <td colspan="7" style="text-align:center;">
                No earnings history yet
              </td>
            </tr>

          <?php endif; ?>
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

        <div class="products-grid" id="productsContainer">
        <?php foreach ($activeProducts as $product): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?>">
                <div class="product-name"><?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?></div>
                <div class="product-price"><?= htmlspecialchars($product['currency'] . ' ' . $product['price'], ENT_QUOTES) ?></div>
                <div class="product-description"><?= htmlspecialchars($product['description'], ENT_QUOTES) ?></div>
                <button class="download-btn" data-id="<?= (int)$product['id'] ?>">
                    Download for Posting
                </button>
            </div>
        <?php endforeach; ?>
        </div>
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
      <div class="filter-bar">
        <select id="statusFilter">
          <option value="all">All Commissions</option>
          <option value="Paid">Paid</option>
          <option value="Pending">Pending</option>
        </select>
      </div>
      <div class="table-wrapper agentEarningsTrack">
        <table id="agentEarnings">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Level</th>
              <th>Source</th>
              <th>Talk</th>
              <th>Date</th>
            </tr>
          </thead>

          <tbody>
          <?php $count = 1; if (!empty($commissions)): ?>

            <?php foreach ($commissions as $row): 

              // Format date
              $date = date("d M Y", strtotime($row['created_at']));

              // Source
              $source = ($row['commission_type'] === 'activation')
                  ? 'Agent Activation'
                  : 'Products Sales';

              // Level
              $level = "Level " . (int)$row['level'];

              // Name (fallback if missing)
              $name = !empty($row['username']) 
                  ? $row['username'] 
                  : 'Deleted User';

              // Status (default since not stored)
              $status = ucfirst($row['status']);
              $statusClass = strtolower($row['status']);

              // Amount
              $amount = "KES " . number_format($row['amount'], 2);

              $phone = !empty($row['phone']) ? base64_decode($row['phone']) : '';
              $email = !empty($row['email']) ? base64_decode($row['email']) : '';              
              
            ?>

              <tr data-status="<?php echo $status; ?>">
                <td><?= $count++ ?>.</td>
                <td><?php echo htmlspecialchars(ucwords(strtolower($name))); ?></td>
                <td>
                  <span class="badge <?php echo $statusClass; ?>">
                    <?php echo $status; ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($amount); ?></td>
                <td><?php echo htmlspecialchars($level); ?></td>
                <td><?php echo htmlspecialchars($source); ?></td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">

                    <?php if ($phone): ?>
                      <a href="tel:<?= htmlspecialchars($phone) ?>">
                        <i class="fas fa-phone"></i> Call
                      </a>

                      <a href="https://wa.me/<?= $cleanPhone ?>" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                      </a>

                      <a href="sms:<?= htmlspecialchars($phone) ?>">
                        <i class="fas fa-comment-dots"></i> SMS
                      </a>
                    <?php endif; ?>

                    <?php if ($email): ?>
                      <a href="mailto:<?= htmlspecialchars($email) ?>">
                        <i class="fas fa-envelope"></i> Email
                      </a>
                    <?php endif; ?>

                  </div>
                </td>
                <td><?php echo htmlspecialchars($date); ?></td>
              </tr>

            <?php endforeach; ?>

          <?php endif; ?>
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
    /* ================= DROPDOWN LOGIC ================= */
    document.addEventListener("click", function (e) {

      // Close all dropdowns
      document.querySelectorAll(".comm-dropdown").forEach(dd => {
        dd.style.display = "none";
      });

      // Toggle clicked dropdown
      const btn = e.target.closest(".comm-btn");
      if (btn) {
        const cell = btn.closest(".comm-cell");
        const dropdown = cell.querySelector(".comm-dropdown");
        dropdown.style.display = "block";
        e.stopPropagation();
      }
    });
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
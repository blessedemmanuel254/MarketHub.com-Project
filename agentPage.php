<?php
session_start();
// Dynamic OG data based on page content
$pageTitle = "Agent Page | Makethub";
$pageDescription = "Verify your Makethub agent account to unlock full agent privileges; receiving commissions, Making withdrawal requests and manage your agency efficiently.";
$pageUrl = "agentregister.php";
$pageImage = "Images/Makethub Logo.avif"; // Use a clear visual for verification
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

/* ---------- AJAX FOLLOW / UNFOLLOW ---------- */
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['seller_id']) &&
  isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
  header('Content-Type: application/json');

  if (!isset($_SESSION['user_id'])) {
      echo json_encode(['error' => 'Not logged in']);
      exit;
  }

  $currentUser = $_SESSION['user_id'];
  $sellerId = (int) $_POST['seller_id'];

  if ($sellerId <= 0 || $sellerId === $currentUser) {
      echo json_encode(['error' => 'Invalid user']);
      exit;
  }

  // Check if already following
  $stmt = $conn->prepare(
      "SELECT 1 FROM user_followers WHERE follower_id = ? AND followed_id = ?"
  );
  $stmt->bind_param("ii", $currentUser, $sellerId);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
      // UNFOLLOW
      $stmt->close();
      $stmt = $conn->prepare(
          "DELETE FROM user_followers WHERE follower_id = ? AND followed_id = ?"
      );
      $stmt->bind_param("ii", $currentUser, $sellerId);
      $stmt->execute();
      $isFollowing = false;
  } else {
      // FOLLOW
      $stmt->close();
      $stmt = $conn->prepare(
          "INSERT INTO user_followers (follower_id, followed_id, followed_at)
            VALUES (?, ?, NOW())"
      );
      $stmt->bind_param("ii", $currentUser, $sellerId);
      $stmt->execute();
      $isFollowing = true;
  }

  $stmt->close();

  // Get updated counts
  $followersStmt = $conn->prepare(
      "SELECT COUNT(*) FROM user_followers WHERE followed_id = ?"
  );
  $followersStmt->bind_param("i", $sellerId);
  $followersStmt->execute();
  $followersStmt->bind_result($followersCount);
  $followersStmt->fetch();
  $followersStmt->close();

  $followingStmt = $conn->prepare(
      "SELECT COUNT(*) FROM user_followers WHERE follower_id = ?"
  );
  $followingStmt->bind_param("i", $sellerId);
  $followingStmt->execute();
  $followingStmt->bind_result($followingCount);
  $followingStmt->fetch();
  $followingStmt->close();

  echo json_encode([
      'success' => true,
      'is_following' => $isFollowing,
      'followers' => $followersCount,
      'following' => $followingCount
  ]);
  exit;
}

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

        -- Total withdrawals count
        (
            SELECT COUNT(*) 
            FROM financial_transactions ft
            WHERE ft.wallet_id = w.wallet_id 
              AND ft.transaction_type = 'withdrawal'
              AND ft.status = 'completed'
        ) AS total_withdrawals,

        -- Total withdrawn amount
        (
            SELECT COALESCE(SUM(ft.amount),0) 
            FROM financial_transactions ft
            WHERE ft.wallet_id = w.wallet_id 
              AND ft.transaction_type = 'withdrawal'
              AND ft.status = 'completed'
        ) AS total_withdrawn,

        -- Sales earnings (this month)
        (
            SELECT COUNT(*) 
            FROM financial_transactions ft
            WHERE ft.wallet_id = w.wallet_id 
              AND ft.transaction_type = 'commission'
              AND ft.status = 'completed'
              AND w.wallet_type = 'sales'
              AND ft.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ) AS sales_count,

        (
            SELECT COALESCE(SUM(ft.amount),0) 
            FROM financial_transactions ft
            WHERE ft.wallet_id = w.wallet_id 
              AND ft.transaction_type = 'commission'
              AND ft.status = 'completed'
              AND w.wallet_type = 'sales'
              AND ft.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ) AS sales_sum

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

/* =====================================================
ADD NEW AGENT FROM DASHBOARD
===================================================== */

$agent_error = "";
$agent_success = "";
$agent_full_name = "";
$agent_username = "";
$agent_email = "";
$agent_phone = "";
$agent_location_id = "";
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
  $agent_location_id = intval($_POST['ward'] ?? 0); // ward = final location
  $agent_address = trim($_POST['address'] ?? '');

  $agent_accountType = "sales_agent";
  $defaultPassword = "Makethub123#";

  if(!$agent_full_name || !$agent_username || !$agent_email || !$agent_phone || !$agent_location_id || !$agent_address){
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
          location_id,
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
          (?,?,?,?,?,?,?,?,?,?,?,?,?, ?,NOW(),NOW(),0,1)
        ");

        $stmt->bind_param(
        "ssssssissssssi",
        $agent_accountType,
        $agent_full_name,
        $agent_username,
        $encrypted_email,
        $encrypted_phone,
        $hashedPassword,
        $agent_location_id,
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
            INSERT COMMISSIONS (FINANCIAL TRANSACTIONS)
          ========================= */

          $commissionLevels = [
              1 => 100,
              2 => 40,
              3 => 20
          ];

          $currentReferrer = $user_id;
          $level = 1;

          while ($currentReferrer && $level <= 3) {

              $amount = $commissionLevels[$level];

              // 🔹 Get SALES wallet of referrer
              $walletStmt = $conn->prepare("
                  SELECT wallet_id 
                  FROM wallets 
                  WHERE user_id = ? AND wallet_type = 'sales'
                  LIMIT 1
              ");
              $walletStmt->bind_param("i", $currentReferrer);
              $walletStmt->execute();
              $walletStmt->bind_result($walletId);
              $walletStmt->fetch();
              $walletStmt->close();

              if ($walletId) {

                  $stmtCom = $conn->prepare("
                      INSERT INTO financial_transactions
                      (
                          source_type,
                          source_id,
                          wallet_id,
                          payer_id,
                          receiver_id,
                          transaction_type,
                          amount,
                          status,
                          description,
                          created_at
                      )
                      VALUES
                      ('commission', ?, ?, ?, ?, 'Agent activation', ?, 'pending', 'Agent activation commission - Level $level', NOW())
                  ");

                  $stmtCom->bind_param(
                      "iiiid",
                      $newUserId,
                      $walletId,
                      $newUserId,
                      $currentReferrer,
                      $amount
                  );

                  $stmtCom->execute();
                  $stmtCom->close();
              }

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

$query = "
SELECT username, profile_image, agency_code, location_id
FROM users 
WHERE user_id = ? 
LIMIT 1
";

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

// location labels (names)
$ward = "";
$county = "";
$country = "";

if ($result && $result->num_rows === 1) {

  $user = $result->fetch_assoc();

  $username = $user['username'] ?? $username;
  $profileImage = $user['profile_image'] ?? null;
  $agencyCode = $user['agency_code'] ?? "";

  $location_id = $user['location_id'] ?? null;

  if ($location_id) {

    // =============================
    // STEP 1: WARD
    // =============================
    $stmt1 = $conn->prepare("
      SELECT name, parent_id 
      FROM locations 
      WHERE location_id = ? AND type = 'ward'
      LIMIT 1
    ");
    $stmt1->bind_param("i", $location_id);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();

    if ($res1) {

      $ward = $res1['name'];
      $county_id = $res1['parent_id'];

      // =============================
      // STEP 2: COUNTY
      // =============================
      $stmt2 = $conn->prepare("
        SELECT name, parent_id 
        FROM locations 
        WHERE location_id = ? AND type = 'county'
        LIMIT 1
      ");
      $stmt2->bind_param("i", $county_id);
      $stmt2->execute();
      $res2 = $stmt2->get_result()->fetch_assoc();
      $stmt2->close();

      if ($res2) {

        $county = $res2['name'];
        $region_id = $res2['parent_id']; // ✅ THIS IS REGION

        // =============================
        // STEP 3: REGION
        // =============================
        $stmt3 = $conn->prepare("
          SELECT parent_id 
          FROM locations 
          WHERE location_id = ? AND type = 'region'
          LIMIT 1
        ");
        $stmt3->bind_param("i", $region_id);
        $stmt3->execute();
        $res3 = $stmt3->get_result()->fetch_assoc();
        $stmt3->close();

        if ($res3) {

          $country_id = $res3['parent_id']; // ✅ NOW THIS IS COUNTRY

          // =============================
          // STEP 4: COUNTRY
          // =============================
          $stmt4 = $conn->prepare("
            SELECT name 
            FROM locations 
            WHERE location_id = ? AND type = 'country'
            LIMIT 1
          ");
          $stmt4->bind_param("i", $country_id);
          $stmt4->execute();
          $res4 = $stmt4->get_result()->fetch_assoc();
          $stmt4->close();

          if ($res4) {
            $country = $res4['name'];
          }
        }
      }
    }
  }
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

$baseAgencyLink = "https://makethub.shop/agentregister.php";

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
    ft.source_id,
    ft.source_type,
    ft.amount,
    ft.status,
    ft.description,
    ft.created_at,
    u.username,
    u.phone,
    u.email
  FROM financial_transactions ft
  LEFT JOIN users u ON u.user_id = ft.source_id
  WHERE ft.receiver_id = ?
  AND ft.transaction_type = 'commission'
  ORDER BY ft.created_at DESC
");

  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $commissions[] = $row;
  }

  // Count pending commissions
  $pendingCount = 0;

  foreach ($commissions as $c) {
    if (strtolower($c['status']) === 'pending') {
        $pendingCount++;
    }
  }

  // Format count for header: if > 9, display "9+"
  $displayCommissionCount = $pendingCount > 9 ? "9+" : $pendingCount;
  $stmt->close();

  // Current logged in user
  $currentUserId = $_SESSION['user_id'];
  $agentWard = strtolower(trim($ward));
  $agentCountry = strtolower(trim($country));

  $sellerQuery = "
  SELECT 
    u.user_id,
    u.username,
    u.business_name,
    u.business_type,
    u.market_scope,
    u.profile_image,
    u.address,

    wardLoc.name AS ward,
    countyLoc.name AS county,
    countryLoc.name AS country,

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
      WHERE uf.followed_id = u.user_id
    ) AS followers_count,

    (
      SELECT COUNT(*)
      FROM user_followers uf
      WHERE uf.follower_id = u.user_id
    ) AS following_count,

    (
      SELECT COUNT(*)
      FROM user_followers uf
      WHERE uf.follower_id = ?
      AND uf.followed_id = u.user_id
    ) AS is_following

  FROM users u

  LEFT JOIN locations wardLoc 
    ON wardLoc.location_id = u.location_id 
    AND wardLoc.type = 'ward'

  LEFT JOIN locations countyLoc 
    ON countyLoc.location_id = wardLoc.parent_id 
    AND countyLoc.type = 'county'

  LEFT JOIN locations countryLoc 
    ON countryLoc.location_id = countyLoc.parent_id 
    AND countryLoc.type = 'country'

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

    $row['ward'] = strtolower(trim($row['ward'] ?? ''));
    $row['country'] = strtolower(trim($row['country'] ?? ''));
    $row['county'] = strtolower(trim($row['county'] ?? ''));
  /* ---------- LOCAL ---------- */
  if (($scope === "local" && strtolower(trim($row['ward'])) === $agentWard) || (strtolower(trim($row['ward'])) === $agentWard)) {

    if (in_array($type, ['shop','kiosk','canteen','kibanda'])) {
      $shops[] = $row;
    }

    elseif (in_array($type, ['supermarket','wholesale'])) {
      $supermarkets[] = $row;
    }

  }

  /* ---------- NATIONAL ---------- */
  if ($scope === "national" && strtolower(trim($row['country'])) === $agentCountry) {

    if (in_array($type, ['shop','kiosk','canteen','kibanda'])) {
      $shopsN[] = $row;
    }

    elseif (in_array($type, ['supermarket','wholesale'])) {
      $supermarketsN[] = $row;
    }

  }

  /* ---------- GLOBAL ---------- */
  if ($scope === "global") {

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

$agentId = $_SESSION['user_id'] ?? 0;

/* ---------- FETCH BUYER ORDERS ---------- */
$orders = [];

$ordersStmt = $conn->prepare("
  SELECT 
      o.order_id, 
      o.order_code,
      oi.item_id,
      oi.quantity,
      oi.subtotal,
      oi.order_status AS order_status,
      oi.shipped_at,
      oi.delivered_at,
      oi.payment_status,
      p.product_name,
      p.image_path,
      u.business_name AS seller_name,
      u.user_id AS seller_id,
      u.market_scope
  FROM order_items oi
  JOIN orders o ON oi.order_id = o.order_id
  JOIN productservicesrentals p ON oi.product_id = p.product_id
  JOIN users u ON oi.seller_id = u.user_id
  WHERE o.buyer_id = ?
  ORDER BY o.created_at DESC
");

$ordersStmt->bind_param("i", $agentId);
$ordersStmt->execute();
$result = $ordersStmt->get_result();

while ($row = $result->fetch_assoc()) {
  $orders[] = $row;
}

$ordersStmt->close();


$orderItemsStmt = $conn->prepare("
  SELECT 
      oi.item_id,
      oi.order_id,
      oi.product_id,
      oi.seller_id,
      oi.quantity,
      oi.price,
      oi.subtotal,
      oi.order_status,
      oi.shipped_at,
      oi.delivered_at,
      p.product_name,
      p.image_path AS product_image,
      u.business_name AS seller_name
  FROM order_items oi
  JOIN productservicesrentals p ON oi.product_id = p.product_id
  JOIN users u ON oi.seller_id = u.user_id
  WHERE oi.order_id = ?
");

/* ---------- COUNT PENDING ORDERS ---------- */
$pendingItems = [];

$stmt = $conn->prepare("
  SELECT 
      oi.item_id,
      oi.order_id,
      oi.product_id,
      oi.quantity,
      oi.price,
      oi.subtotal,
      oi.order_status,
      p.product_name,
      p.image_path AS product_image
  FROM order_items oi
  JOIN orders o ON oi.order_id = o.order_id
  JOIN productservicesrentals p ON oi.product_id = p.product_id
  WHERE o.buyer_id = ?
    AND oi.order_status = 'pending'
  ORDER BY o.created_at DESC
");

if ($stmt) {
  $stmt->bind_param("i", $agentId);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
      $pendingItems[] = $row;
  }

  $stmt->close();
}

$pendingOrders = count($pendingItems);

$activeProducts = [];

$res = $conn->query("
  SELECT id, product_name, price, currency, description, image, download_file 
  FROM markethub_products 
  WHERE is_active = 1
  ORDER BY created_at DESC
");

while ($row = $res->fetch_assoc()) {

  $row['formatted_price'] = number_format((float)$row['price'], 2);

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

  $imageFile = basename($product['image']);
  $imagePath = __DIR__ . '/uploads/company_products/' . $imageFile;

  if (!file_exists($imagePath)) {
    die("Image not found: " . $imagePath);
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
  // Overlay multiple lines
  // ------------------------
  $textColor = imagecolorallocate($image, 255, 255, 255); // white
  $bgColor = imagecolorallocatealpha($image, 0, 0, 0, 60); // semi-transparent black
  $fontSize = 5;
  $padding = 4;

  $imgWidth = imagesx($image);
  $imgHeight = imagesy($image);

  // ----- Line 1: Name - Price -----
  $name = $product['product_name'];

  $formattedPrice = number_format((float)$product['price'], 2); // <-- FIX
  $price = $product['currency'] . ' ' . $formattedPrice;

  $line1 = $name . " - " . $price;

  $textWidth1 = imagefontwidth($fontSize) * strlen($line1);
  $textHeight1 = imagefontheight($fontSize);
  $x1 = (int)(($imgWidth - $textWidth1) / 2);
  $y1 = (int)10;

  imagefilledrectangle(
    $image,
    (int)($x1 - $padding),
    (int)($y1 - $padding),
    (int)($x1 + $textWidth1 + $padding),
    (int)($y1 + $textHeight1 + $padding),
    $bgColor
  );

  imagestring($image, $fontSize, $x1, $y1, $line1, $textColor);

  // ----- Line 2: Agent's Name - username (Formatted) -----
  $formattedUsername = ucfirst(strtolower($username ?? ''));
  $line3 = "Agent's Name - " . $formattedUsername;

  $textWidth3 = imagefontwidth($fontSize) * strlen($line3);
  $textHeight3 = imagefontheight($fontSize);
  $x3 = (int)(($imgWidth - $textWidth3) / 2);
  $y3 = (int)($imgHeight - $textHeight3 - 20);

  imagefilledrectangle(
    $image,
    (int)($x3 - $padding),
    (int)($y3 - $padding),
    (int)($x3 + $textWidth3 + $padding),
    (int)($y3 + $textHeight3 + $padding),
    $bgColor
  );

  imagestring($image, $fontSize, $x3, $y3, $line3, $textColor);

  // ----- Save / Output -----
  $filename = preg_replace(
    '/\s+/', 
    '_', 
    $name . '_' . $product['currency'] . '_' . $formattedPrice
  ) . ".jpg";
  header('Content-Type: image/jpeg');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  imagejpeg($image, null, 90);
  imagedestroy($image);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_wallet'])) {

  $walletType = $_POST['withdraw_wallet'];
  $error = '';
  $success = '';

  // Pick the correct input field and balance/min
  if ($walletType === 'sales') {
      $withdrawAmount = $_POST['withdraw_sales_amount'] ?? '';
      $balance = $salesBalance;
      $min = $salesMin;
  } else {
      $withdrawAmount = $_POST['withdraw_agency_amount'] ?? '';
      $balance = $agencyBalance;
      $min = $agencyMin;
  }
  // Check if withdrawal amount is empty
  if (empty($withdrawAmount) && $withdrawAmount !== '0') {
      $error = "Please enter a withdrawal amount!";
  } else {

    $withdrawAmount = floatval($withdrawAmount);

    // Maximum withdrawal allowed
    $maxWithdrawal = 100000.0; // KES
    if ($withdrawAmount > $maxWithdrawal) {
        $error = "Maximum withdrawal allowed is KES $maxWithdrawal!";
    }

    // --- Calculate M-Pesa style tiered fee ---
    if ($withdrawAmount <= 1000) {
        $fee = 40; // flat minimum fee
    } elseif ($withdrawAmount <= 10000) {
        $fee = 50 + 0.002 * $withdrawAmount; // small % + base
    } elseif ($withdrawAmount <= 50000) {
        $fee = 100 + 0.0015 * $withdrawAmount;
    } else { // >50,000
        $fee = 200 + 0.001 * $withdrawAmount;
    }
    $fee = round($fee, 2); // round to 2 decimals
    $netAmount = $withdrawAmount - $fee;


      // Validation
      if (!$error) {
          if ($withdrawAmount < $min) {
              $error = "Minimum withdrawal for $walletType wallet is KES $min!";
          } elseif ($withdrawAmount > $balance) {
              $error = "Insufficient balance!";
          } elseif ($netAmount <= 0) {
              $error = "Withdrawal amount must be greater than the transaction fee (KES $fee)!";
          }
      }
  }

  if (!$error) {
      // Begin transaction
      $conn->begin_transaction();

      try {
          // 1️⃣ Update wallet balance
          $stmt = $conn->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND wallet_type = ? LIMIT 1");
          $stmt->bind_param("dis", $withdrawAmount, $user_id, $walletType);
          $stmt->execute();
          $withdrawalId = $conn->insert_id;
          $stmt->close();

          // 2️⃣ Insert into financial_transactions
          $stmt = $conn->prepare("INSERT INTO financial_transactions (source_type, source_id, wallet_id, payer_id, receiver_id, transaction_type, amount, currency, status, description, created_at) SELECT ?, ?, wallet_id, ?, ?, 'withdrawal', ?, 'KES', 'pending', ?, NOW() FROM wallets WHERE user_id = ? AND wallet_type = ? LIMIT 1");
          $description = ucfirst($walletType) . " wallet withdrawal request";
          $sourceType = 'agent_withdrawal';
          $stmt->bind_param("siiddsis", $sourceType, $user_id, $user_id, $user_id, $withdrawAmount, $description, $user_id, $walletType);
          $stmt->execute();
          $transactionId = $stmt->insert_id;
          $stmt->close();

          // 3️⃣ Insert into withdrawals
          $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, wallet_id, amount, fee, net_amount, status, transaction_id, requested_at, currency)  SELECT user_id, wallet_id, ?, ?, ?, 'pending', ?, NOW(), 'KES' FROM wallets WHERE user_id = ? AND wallet_type = ? LIMIT 1");
          $stmt->bind_param("dddiis", $withdrawAmount, $fee, $netAmount, $transactionId, $user_id, $walletType);
          $stmt->execute(); // ✅ REQUIRED

          $withdrawalId = $stmt->insert_id; // ✅ CORRECT PLACE
          $stmt->close();

          // 4️⃣ Insert into withdrawal_logs
          $stmt = $conn->prepare("INSERT INTO withdrawal_logs (withdrawal_id, performed_by, note, created_at) VALUES (?, ?, ?, NOW())");
          $action = 'requested';
          $performedBy = $user_id;
          $note = "User requested withdrawal of KES $withdrawAmount, net KES $netAmount, fee KES $fee";
          $stmt->bind_param("iis", $withdrawalId, $performedBy, $note); // use $withdrawalId here
          $stmt->execute();

          // Commit transaction
          $conn->commit();

          $success = "Withdrawal request of KES $withdrawAmount from your $walletType wallet submitted successfully. You will receive KES $netAmount after fees! <span class='redirect-msg'></span>";

          $notificationMessage = "<i class='fa-solid fa-check-circle'></i> Request has been submitted successfully!";

          echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  showNotification(" . json_encode($notificationMessage) . ", 4000);
              });
          </script>";

      } catch (Exception $e) {
          $conn->rollback();
          $error = "Withdrawal failed: " . $e->getMessage();
      }
  }
}

// Prepare SQL with LEFT JOIN to get phone from users table
$query = "
  SELECT w.*, u.phone 
  FROM withdrawals w
  LEFT JOIN users u ON w.user_id = u.user_id
  WHERE w.user_id = ?
  ORDER BY w.created_at DESC
  LIMIT 10
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wResult = $stmt->get_result();

// Helper: format date
function formatDate($date) {
  if (empty($date)) return '-';

  $timestamp = strtotime($date);
  $oneYear = 31536000; // 1 year in seconds

  if (time() - $timestamp < $oneYear) {
      return date("d M, H:i", $timestamp); // recent → show time
  } else {
      return date("d M Y", $timestamp);    // old → show year
  }
}

function decodePhone($encodedPhone) {
  if (empty($encodedPhone)) {
      return '';
  }

  $decoded = base64_decode($encodedPhone, true);

  // If decoding fails, return original safely
  if ($decoded === false) {
      return htmlspecialchars($encodedPhone, ENT_QUOTES, 'UTF-8');
  }

  return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
}

// Helper: mask phone
function maskPhone($phone, $maskChar = '*') {
  // Ensure the phone has at least 8 characters to mask
  if (strlen($phone) < 8) {
      return $phone;
  }

  // Keep first 6 characters (country code + prefix) and last 3 digits
  $firstPart = substr($phone, 0, 6);
  $lastPart = substr($phone, -3);

  // Middle part to be masked
  $maskedLength = strlen($phone) - strlen($firstPart) - strlen($lastPart);
  $maskedPart = str_repeat($maskChar, $maskedLength);

  return $firstPart . $maskedPart . $lastPart;
}

// Helper: status class
function getStatusClass($status) {
  return strtolower($status); // paid, pending, rejected etc.
}

// Helper: status icon
function getStatusIcon($status) {
  switch ($status) {
    case 'Approved': return 'fa-check-circle';
    case 'Pending': return 'fa-clock';
    case 'Processing': return 'fa-spinner fa-spin';
    case 'Rejected': return 'fa-ban';
    case 'Failed': return 'fa-circle-xmark';
    default: return 'fa-clock';
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
        BADGE REQUIRED
        </div>

        <div class="alert-popup-body">

          <div class="warning-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>

          <div class="alert-popup-title">
          Get Your Agency Badge!
          </div>

          <div class="alert-popup-text">
            Your Makethub agent account currently <em>**does not have a badge**.</em>

            To unlock full agent privileges like:

            • Earning through agency tools  
            • Referring other agents  
            • Accessing agency withdrawals and orders  
            including other premium agent features, 

            You must have a badge!
          </div>

          <div class="buttons">

            <a href="index.php" class="cancel">
            Cancel
            </a>

            <a href="paypage.php" class="activate">
            Get Badge
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
          <?php
          $displayCount = ($pendingOrders > 9) ? '9+' : $pendingOrders;
          ?>
          <a class="lkOdr" onclick="toggleAgentEarningsTrack()">
            <div class="odrIconDiv">
              <i class="fa-solid fa-sack-dollar"></i>
              <p class="agent-not"><?= $displayCommissionCount ?></p>
            </div>
          </a>

          <a class="lkOdr" onclick="toggleAgentOrdersTrack()">
            <div class="odrIconDiv">
              <i class="fa-brands fa-first-order-alt"></i>

              <?php if ($pendingOrders > 0): ?>
                <p class="order-count active"><?= $displayCount ?></p>
              <?php else: ?>
                <p class="order-count"><?= $displayCount ?></p>
              <?php endif; ?>

            </div>
            <p>Order(s)</p>
          </a>
          <select id="hCounty">
            <option value="<?= htmlspecialchars($county) ?>" selected>
              <?= htmlspecialchars($county) ?>
            </option>
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
      <img src="Images/Makethub WhatsApp Icon.avif" width="45" alt="Chat with us on WhatsApp">
    </div>

    <div id="whatsapp-chat-box">
      <div class="chat-header">
        <div class="top">
          <img src="Images/Makethub Logo.avif" alt="Makethub Logo" width="35">
          <p><strong>Makethub</strong><br>
          <small>online</small></p>
        </div>
        <i class="fa-solid fa-xmark" onclick="toggleWhatsAppChat()"></i>
      </div>
      <div class="chat-body">
        <div class="chat-container">
          <div class="chat-bubble">
            <div class="sender">Makethub</div>
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

          <span class="no-badge">
            <i class="fa-solid fa-ban"></i>&nbsp;No&nbsp;Badge
          </span>

        <?php elseif ($isExpired): ?>

          <span class="expired">
            <i class="fa-solid fa-clock"></i>&nbsp;Expired
          </span>

        <?php else: ?>

          <span class="badged">
            Badged&nbsp;<i class="fa-solid fa-certificate"></i>
          </span>

        <?php endif; ?>

        </p>
      </div>
      <div class="tabs-container" id="toggleAgentTab">
        <div class="tabs">
          <button class="tab-btn" data-tab="dashboard">Sales&nbsp;Board</button>
          <button class="tab-btn" data-tab="my-agency">My&nbsp;Agency</button>
          <button class="tab-btn" data-tab="funds">Funds</button>
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
              <a class="card" 
                onclick="<?php 
                  if ($isVerified) { 
                      echo 'toggleAgentWithdrawals()'; 
                  } else { 
                      echo 'showAgentAlertPopup()'; 
                  } 
                ?>">
                  <i class="fa-brands fa-python"></i>
                  <h2>Withdrawal</h2>
                  <p>
                    View your account withdrawal history.
                  </p>
                  <div class="label">
                      <p>HISTORY</p>
                      <button>
                        View History
                      </button>
                  </div>
              </a>

              <!-- MARKET -->
              <a class="card" onclick="openMarketType('products')">
                <i class="fa-brands fa-renren"></i>
                <h2>Market</h2>
                <p>
                  Get to order on Makethub like other users.
                </p>
                <div class="label">
                  <p>Market</p>
                  <button>View Market</button>

                </div>
              </a>
            </div>
          </div>

          <div id="my-agency" class="tab-panel agency">
            <div class="tab-top">
              <p>Peformance Analytics<br><strong>Monitor your agency and track performance <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleAgentAdd(true)">
                <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Agent</span>
              </button>

            </div>
            <div class="dashboard">
              <?php if ($status === 'active' && !$isExpired && $isVerified): ?>

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

                    <button class="tab-btn" data-tab="funds">Withdraw</button>

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

              <?php else: ?>
              <!-- TOP CARDS -->
              <div class="grid"> 

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

              </div>
              <?php endif; ?>

            </div>
          </div>
          
          <div id="add-agent" class="tab-panel">
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

                    <select id="country" name="country" required>
                      <option value="">-- Select Country --</option>
                      <?php
                        $countries = $conn->query("SELECT location_id, name FROM locations WHERE type='country' ORDER BY name ASC");
                        while ($row = $countries->fetch_assoc()):
                      ?>
                        <option value="<?= $row['location_id']; ?>" 
                          <?= ($country == $row['location_id']) ? 'selected' : ''; ?>>
                          <?= htmlspecialchars($row['name']); ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="inp-box">

                    <label>County</label>
                    <select id="county" name="county" required>
                      <option value="">-- Select County --</option>
                    </select>
                  </div>
                  <div class="inp-box">
                    <label>Agent's Address</label>
                    <input type="text" value="<?= $agent_address ?>" name="address" placeholder="eg. Kilifi town" required>
                  </div>
                  <div class="inp-box">

                    <label>Ward</label>
                    <select id="ward" name="ward" required data-selected="<?= htmlspecialchars($location_id ?? '') ?>">
                      <option value="">-- Select Ward --</option>
                    </select>
                  </div>
                  <div></div>
                  <button type="submit">
                    Submit Details
                  </button>
                </div>

                <input type="hidden" id="old_country" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>">
                <input type="hidden" id="old_county" value="<?= htmlspecialchars($_POST['county'] ?? '') ?>">
              </form>
            </div>
          </div>
          
          <div id="funds" class="tab-panel">
            <p>Access your earnings</em> <br><strong>Withdraw funds you’ve earned from completed sales and agency <i class="fa-regular fa-circle-check"></i></strong></p>
            
            <div class="form-wrapper agency">
              <form method="POST" autocomplete="off" enctype="multipart/form-data">
                <h1>Withdraw Funds</h1>
                <?php if (!empty($error)): ?>
                  <p class="errorMessage usrWlt">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error); ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                  <p class="successMessage usrWlt" data-redirect="agentPage.php">
                    <i class="fa-solid fa-check-circle"></i> <?= $success ?>
                  </p>

                  <script>
                    showNotification(
                      `<i class="fa-solid fa-check-circle"></i> <?= addslashes($success) ?>`,
                      4000
                    );
                  </script>
                <?php endif; ?>
                
                <select 
                  name="withdraw_wallet" 
                  id="walletSelect" 
                  class="walletChange"
                  <?= ((int)$isVerified !== 1) ? 'onclick="showAgentAlertPopup()"' : '' ?>
                >

                  <option value="sales">Sales Wallet</option>

                  <?php if ((int)$isVerified === 1): ?>
                    <option value="agency">Agency Wallet</option>
                  <?php endif; ?>

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
                      <input type="number" name="withdraw_sales_amount" placeholder="Enter amount" min="0" required>
                      <button type="submit">Request Withdrawal</button>
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
                      <input type="number" name="withdraw_agency_amount" placeholder="Enter amount" min="0" required>
                      <button type="submit" required>Request Withdrawal</button>
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
                <?php if ($scope === 'L'): ?>
                  Showing markets in <em><?= htmlspecialchars(ucwords($ward)) ?> Ward</em><br>
                <?php elseif ($scope === 'N'): ?>
                  Showing the national market in <em><?= htmlspecialchars(ucwords($country)) ?></em><br>
                <?php elseif ($scope === 'G'): ?>
                  Showing global markets available on <em>Makethub</em><br>
                <?php endif; ?>
                
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
          <option value="Completed">Paid</option>
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
              $date = formatDate($row['created_at']);

              // Source
              $source = ($row['source_type']);

              // Level
              preg_match('/Level (\d+)/', $row['description'], $matches);
              $level = isset($matches[1]) ? "Level " . $matches[1] : "Level 0";

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

              $cleanPhone = preg_replace('/\D/', '', $phone);

              // Convert 07XXXXXXXX → 2547XXXXXXXX
              if (strpos($cleanPhone, '0') === 0) {
                  $cleanPhone = '254' . substr($cleanPhone, 1);
              }
              
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
                    <a href="https://wa.me/<?= $cleanPhone ?>" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:<?= htmlspecialchars($email) ?>"><i class="fas fa-envelope"></i> Email</a>
                    <a href="sms:<?= $cleanPhone ?>">
                      <i class="fas fa-comment-dots"></i> SMS
                    </a>
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
          <h1>Makethub Daily Products</h1>
          <p>Download and post across all platforms today.</p>
        </div>

        <div class="products-grid" id="productsContainer">
        <?php foreach ($activeProducts as $product): ?>
            <div class="product-card">
              <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?>">
              <div class="product-name"><?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?></div>
              <div class="product-price">
                <?= htmlspecialchars($product['currency'], ENT_QUOTES) ?>
                <strong><?= htmlspecialchars($product['formatted_price'], ENT_QUOTES) ?></strong>
              </div>
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
          <option value="Completed">Paid</option>
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
              $date = formatDate($row['created_at']); 

              // Source
              $source = ($row['source_type']);

              // Level
              preg_match('/Level (\d+)/', $row['description'], $matches);
              $level = isset($matches[1]) ? "Level " . $matches[1] : "Level 0";

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

        <?php if ($wResult->num_rows > 0): ?>
        <div class="withdraw-grid">

          <?php while($row = $wResult->fetch_assoc()): ?>

            <div class="withdraw-card <?php echo $row['status']; ?>">
              
              <div class="withdraw-left">
                
                <div class="withdraw-title">
                  <i class="fa-solid fa-wallet"></i> 
                  Wallet Withdrawal
                </div>

                <div class="withdraw-meta">
                  <i class="fa-regular fa-calendar"></i> 
                  <?php echo formatDate($row['created_at']); ?><br>

                  <?php
                    $phone = $row['phone'] ?? $row['account_number'];
                  ?>

                  <?php if(strtolower($row['method']) == 'mpesa'): ?>
                    <i class="fa-solid fa-mobile-screen-button"></i> 
                    M-Pesa • 
                    <?php 
                      $rawPhone = $row['phone'] ?? $row['account_number'];
                      $decodedPhone = decodePhone($rawPhone);
                      $finalPhone = $decodedPhone ?: $rawPhone;
                      echo maskPhone($finalPhone);
                    ?>
                  <?php else: ?>
                    <i class="fa-solid fa-building-columns"></i> 
                    <?php echo $row['method']; ?>
                  <?php endif; ?>
                </div>

                <div class="withdraw-reference">
                  <i class="fa-solid fa-hashtag"></i> 
                  <?php echo $row['reference_code'] ?: $row['transaction_id']; ?>
                </div>

                <div style="margin-top:5px; font-size:13px; color:#666;">
                  <?php echo $row['full_name'] ?? $row['account_name']; ?>
                </div>

              </div>

              <div class="withdraw-right">
                
                <div class="withdraw-amount">
                  KES <?php echo number_format($row['amount']); ?>
                </div>

                <div class="status <?php echo getStatusClass($row['status']); ?>">
                  <i class="fa-solid <?php echo getStatusIcon($row['status']); ?>"></i>
                  <?php echo $row['status']; ?>
                </div>

              </div>

            </div>

          <?php endwhile; ?>

          <?php else: ?>

            <!-- ✅ EMPTY STATE -->
            <div class="no-withdrawals">
              <i class="fa-regular fa-folder-open"></i>
              <p>No withdrawals yet</p>
              <small>Your withdrawal history will appear here once you make a request.</small>
            </div>

          <?php endif; ?>


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
      
      <div class="filter-bar">
        <select id="statusFilter">
          <option value="all">All Orders</option>
          <option value="Delivered">Delivered</option>
          <option value="Shipped">Shipped</option>
          <option value="Pending">Processing</option>
        </select>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="table-wrapper agentOrdersTrack">
        <table id="agentOrdersTable">
          <thead>
            <tr>
              <th>Image</th>
              <th>Order</th>
              <th>Product</th>
              <th>Seller</th>
              <th>Market</th>
              <th>Quantity</th>
              <th>Subtotal</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Actions</th>
              <th>Shipped At</th>
              <th>Delivered At</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
            <tr data-status="<?= htmlspecialchars($order['order_status']) ?>">

              <td>
                <img src="<?= !empty($order['image_path']) && file_exists(__DIR__ . '/' . $order['image_path']) 
                    ? htmlspecialchars($order['image_path']) 
                    : 'Images/Makethub Logo.avif'; ?>" 
                    class="product-img">
              </td>

              <td><?= htmlspecialchars($order['order_code']) ?></td>
              <td><?= htmlspecialchars($order['product_name']) ?></td>

              <td>
                <?= mb_strtoupper(htmlspecialchars(
                    !empty($order['seller_name']) 
                    ? $order['seller_name'] 
                    : 'Seller #' . $order['seller_id']
                ), 'UTF-8') ?>
              </td>

              <td><?= htmlspecialchars($order['market_scope'] ?? 'National') ?></td>
              <td><?= $order['quantity'] ?></td>
              <td>KES&nbsp;<?= number_format($order['subtotal'], 2) ?></td>

              <!-- PAYMENT STATUS -->
              <td>
                <?php
                  $paymentClass = strtolower($order['payment_status']);
                  $paymentText  = ucwords($order['payment_status']);
                ?>
                <span class="badge <?= $paymentClass ?>"><?= $paymentText ?></span>
              </td>

              <!-- ORDER / SHIPMENT STATUS -->
              <td>
                <?php
                  $statusClass = strtolower($order['order_status']);
                  $statusText  = ucwords($order['order_status']);
                ?>
                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
              </td>

              <!-- ACTIONS -->
              <td class="actions">
                <div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>

                  <?php if ($order['order_status'] === 'Processing'): ?>
                    <button class="btn-cancel">Cancel</button>
                  <?php elseif ($order['order_status'] === 'Shipped'): ?>
                    <button class="btn-track">Track</button>
                  <?php endif; ?>
                </div>
              </td>

              <!-- SHIPPED & DELIVERED -->
              <td>
              <?=
              !empty($order['shipped_at'])
                ? (
                    (time() - strtotime($order['shipped_at']) < 31536000)
                      ? date("d M, H:i", strtotime($order['shipped_at']))   // recent → show time
                      : date("d M Y", strtotime($order['shipped_at']))      // old → show year
                  )
                : '-'
              ?>
              </td>

              <td>
              <?=
              !empty($order['delivered_at'])
                ? (
                    (time() - strtotime($order['delivered_at']) < 31536000)
                      ? date("d M, H:i", strtotime($order['delivered_at']))
                      : date("d M Y", strtotime($order['delivered_at']))
                  )
                : '-'
              ?>
              </td>

            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <!-- 

      <div class="order-group">
        <div class="order-header">
          <div>
            <strong>Order #ORD-90321</strong><br>
            <span>Placed on 12 Feb 2026</span>
          </div>
          <div><strong>3</strong> Items</div>
        </div>

        <div class="order-items-grid">
          <div class="order-item">
            <div class="item-top">
              <div class="item-info">
                <h4>Wireless Headphones</h4>
                <p>Seller: TechZone</p>
                <p>Qty: 1 • Total: KES 3,200</p>
                <p>Status: <span class="status shipped">Shipped</span></p>
                <span class="market-badge">National</span>
              </div>
              <img src="Images/Makethub Logo.avif" alt="Product">
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
          <div class="order-item">
            <div class="item-top">
              <div class="item-info">
                <h4>Office Chair</h4>
                <p>Seller: Comfort Furnish</p>
                <p>Qty: 2 • Total: KES 18,000</p>
                <p>Status: <span class="status processing">Processing</span></p>
                <span class="market-badge">Local</span>
              </div>
              <img src="Images/Makethub Logo.avif" alt="Product">
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
      </div> -->

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleAgentOrdersTrack()">Go&nbsp;back</button> to continue shopping.</p>
    </main>
        <footer>
      <p>&copy; 2025/2026, Makethub.shop, All Rights Reserved.</p><br>
      <p>
        <a href="privacy.php">Privacy Policy</a> |
        <a href="terms.php">Terms & Conditions</a> |
        <a href="contact.php">Contact Us</a>
      </p>
    </footer>
  </div>

  <!-- Notification container -->
  <div id="notification-container"></div>
  
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
      $('#agentEarnings, #agentOrdersTable').DataTable({
        pagingType: "simple_numbers",
        pageLength: 15,
        lengthChange: false,
        searching: true,
        ordering: true,
        stateSave: true,
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
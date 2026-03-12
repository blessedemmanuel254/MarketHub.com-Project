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

/* ---------- ROLE ACCESS CONTROL ---------- */
$allowedRole = 'administrator';

$roleStmt = $conn->prepare(
  "SELECT account_type FROM users WHERE user_id = ? LIMIT 1"
);
$roleStmt->bind_param("i", $_SESSION['user_id']);
$roleStmt->execute();
$roleStmt->bind_result($accountType);
$roleStmt->fetch();
$roleStmt->close();

if ($accountType !== $allowedRole) {
    // Optional: destroy session for safety
    // session_destroy();/* 

    header("Location: index.php");
    exit();
}

/* ===============================
   HELPER FUNCTIONS
================================= */
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

/**
 * Decode a base64-encoded email safely
 */
function decodeEmail($encodedEmail) {
  if (empty($encodedEmail)) {
      return '';
  }

  $decoded = base64_decode($encodedEmail, true);

  // If decoding fails, return original safely
  if ($decoded === false) {
      return htmlspecialchars($encodedEmail, ENT_QUOTES, 'UTF-8');
  }

  return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
}

/**
 * Mask an email address partially
 * Example: emmanueltindi23@gmail.com => em***23@gmail.com
 */
function maskEmail($email, $mask = '***') {
  if (empty($email)) {
      return '';
  }

  $parts = explode('@', $email);
  if (count($parts) !== 2) {
      return htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
  }

  $local = $parts[0];
  $domain = $parts[1];

  // If local part is too short, just show first char + mask
  if (strlen($local) <= 3) {
      $maskedLocal = substr($local, 0, 1) . $mask;
  } else {
      $firstTwo = substr($local, 0, 2);
      $lastTwo = substr($local, -2);
      $maskedLocal = $firstTwo . $mask . $lastTwo;
  }

  return $maskedLocal . '@' . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');
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

// Fetch admin details from the database using session user_id
$stmt = $conn->prepare("SELECT full_name, account_type, profile_image FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullName, $accountType, $profileImage);
$stmt->fetch();
$stmt->close();

// Format the full name (all uppercase)
$fullNameFormatted = strtoupper($fullName);

// Format account type (first letter uppercase, rest lowercase)
$accountTypeFormatted = ucfirst(strtolower($accountType));

// Default avatar if profile image does not exist
$defaultAvatar = "Images/Maket Hub Logo.avif";
$safeProfileImage = (!empty($profileImage) && file_exists($profileImage)) ? htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8') : $defaultAvatar;

/* ===============================
   USERS CARD DATA
================================= */
$userQuery = $conn->query("
    SELECT 
        COUNT(*) AS total_users,
        SUM(account_type = 'seller') AS total_sellers,
        SUM(account_type = 'buyer') AS total_buyers,
        SUM(account_type = 'property_owner') AS total_property_owners
    FROM users
");

$userData = $userQuery->fetch_assoc();

/* ===============================
   SALES AGENTS CARD DATA
================================= */
$agentQuery = $conn->query("
    SELECT 
        COUNT(*) AS total_agents,
        SUM(status = 'active') AS active_agents,
        SUM(is_verified = 1) AS verified_agents,
        SUM(status = 'suspended') AS under_review
    FROM users
    WHERE account_type = 'sales_agent'
");

$agentData = $agentQuery->fetch_assoc();

/* ===============================
   SALES AGENTS TABLE DATA
================================= */

$defaultAvatar = "Images/Maket Hub Logo.avif";

$agentsStmt = $conn->prepare("
    SELECT user_id, full_name, username, email, phone, profile_image, 
      status, is_verified, created_at, updated_at
    FROM users
    WHERE account_type = 'sales_agent'
    ORDER BY user_id DESC
");

$agentsStmt->execute();
$agentsResult = $agentsStmt->get_result();

// Fetch sellers
$sellerQuery = $conn->query("
    SELECT 
        user_id, full_name, username, email, phone, profile_image, is_verified, status, created_at, updated_at
    FROM users
    WHERE account_type='seller'
    ORDER BY user_id DESC
");

$sellers = [];
$verifiedCount = 0;

while ($row = $sellerQuery->fetch_assoc()) {
    $sellers[] = $row;
    if ($row['is_verified'] == 1) $verifiedCount++;
}

$totalSellers = $userData['total_sellers'] ?? count($sellers);

// Get product counts for all sellers
$productCounts = [];
$productQuery = $conn->query("
  SELECT user_id, COUNT(DISTINCT product_name) AS product_count
  FROM productservicesrentals
  GROUP BY user_id
");

while ($row = $productQuery->fetch_assoc()) {
  $productCounts[$row['user_id']] = $row['product_count'];
}

// ---------- Fetch buyer stats ----------
// Total buyers
$totalBuyersQuery = "SELECT COUNT(*) AS total FROM users WHERE account_type='buyer'";
$totalBuyersResult = mysqli_query($conn, $totalBuyersQuery);
$totalBuyers = mysqli_fetch_assoc($totalBuyersResult)['total'];

// Active buyers (assuming status column exists in users table)
$activeBuyersQuery = "SELECT COUNT(*) AS active FROM users WHERE account_type='buyer' AND status='Active'";
$activeBuyersResult = mysqli_query($conn, $activeBuyersQuery);
$activeBuyers = mysqli_fetch_assoc($activeBuyersResult)['active'];

// Total orders (for third card)
$totalOrdersQuery = "SELECT COUNT(DISTINCT order_code) AS total_orders FROM orders";
$totalOrdersResult = mysqli_query($conn, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($totalOrdersResult)['total_orders'];

// ---------- Fetch buyers table data ----------
$buyersQuery = "
  SELECT u.user_id, u.full_name, u.email, u.phone, u.status, u.created_at, u.updated_at,
          COUNT(o.order_id) AS orders_count,
          SUM(o.total_amount) AS total_spend
  FROM users u
  LEFT JOIN orders o ON u.user_id = o.buyer_id
  WHERE u.account_type='buyer'
  GROUP BY u.user_id
  ORDER BY u.created_at DESC
";
$buyersResult = mysqli_query($conn, $buyersQuery);

// Fetch property owners
$ownersQuery = $conn->query("
  SELECT 
    user_id,
    full_name,
    username,
    email,
    phone,
    profile_image,
    is_verified,
    status,
    created_at,
    updated_at
  FROM users
  WHERE account_type = 'property_owner'
  ORDER BY user_id DESC
");

$propertyOwners = [];
while ($row = $ownersQuery->fetch_assoc()) {
    $propertyOwners[] = $row;
}

// Total Owners count
$totalOwners = count($propertyOwners);

/* ======================================
   FETCH USER FOR EDITING
====================================== */

$editUser = [];

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type   = $_GET['type'] ?? '';

$map = [
  'agent'  => 'sales_agent',
  'seller' => 'seller',
  'buyer'  => 'buyer',
  'owner'  => 'property_owner'
];

if($userId && isset($map[$type])){

  $dbType = $map[$type];

  $stmt = $conn->prepare("
    SELECT
    user_id,
    full_name,
    username,
    email,
    phone,
    account_type,
    country,
    county,
    ward,
    address,
    business_name,
    business_model,
    business_type,
    market_scope,
    agency_code
    FROM users
    WHERE user_id=? AND account_type=?
    LIMIT 1
  ");

  $stmt->bind_param("is",$userId,$dbType);
  $stmt->execute();

  $result = $stmt->get_result();

  if($result->num_rows === 1){
      $editUser = $result->fetch_assoc();
  }

  $stmt->close();
}

/* ---------- Populate variables ---------- */

$edit_user_id       = $editUser['user_id'] ?? '';
$full_name     = htmlspecialchars($editUser['full_name'] ?? '');
$editUsername      = htmlspecialchars($editUser['username'] ?? '');
$editEmail         = decodeEmail($editUser['email'] ?? '');
$editPhone         = decodePhone($editUser['phone'] ?? '');
$editCountry       = htmlspecialchars($editUser['country'] ?? '');
$editCounty        = htmlspecialchars($editUser['county'] ?? '');
$editWard          = htmlspecialchars($editUser['ward'] ?? '');
$editAddress       = htmlspecialchars($editUser['address'] ?? '');
$edit_business_name = htmlspecialchars($editUser['business_name'] ?? '');
$edit_business_model= htmlspecialchars($editUser['business_model'] ?? '');
$edit_business_type = htmlspecialchars($editUser['business_type'] ?? '');
$edit_market_scope  = htmlspecialchars($editUser['market_scope'] ?? '');
$edit_agency_code   = htmlspecialchars($editUser['agency_code'] ?? '');

$editError = "";
$editSuccess = "";

/* ======================================
   HANDLE UPDATE
====================================== */

if($_SERVER["REQUEST_METHOD"] === "POST"){

$formType = $_POST['form_type'] ?? '';

$user_id   = intval($_POST['user_id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$editUsername  = trim($_POST['username'] ?? '');
$editEmail     = trim($_POST['email'] ?? '');
$editPhone     = trim($_POST['phone'] ?? '');

$editCountry   = trim($_POST['country'] ?? '');
$editCounty    = trim($_POST['county'] ?? '');
$editWard      = trim($_POST['ward'] ?? '');
$editAddress   = trim($_POST['address'] ?? '');

$editBusname   = trim($_POST['business_name'] ?? '');
$editBusmodel  = trim($_POST['business_model'] ?? '');
$editBustype   = trim($_POST['business_type'] ?? '');
$editMarket    = trim($_POST['market_scope'] ?? '');

/* ---------- VALIDATION ---------- */

if(!$full_name || !$editUsername || !$editEmail || !$editPhone || !$editCountry || !$editCounty || !$editWard || !$editAddress){
  $editError = "All fields are required.";
}

elseif(str_word_count($full_name) < 2){
  $editError = "Full name must contain first and last name.";
}

elseif(strpos($editUsername,' ') !== false){
  $editError = "Username should not contain spaces.";
}

elseif(strlen($editUsername) < 5){
  $editError = "Username too short.";
}

elseif(!filter_var($editEmail,FILTER_VALIDATE_EMAIL)){
  $editError = "Invalid email.";
}

/* ---------- Seller extra validation ---------- */

if(!$editError && $formType === "seller"){

  if(!$editBusname || !$editBusmodel || !$editBustype || !$editMarket){
    $editError = "Seller business fields required.";
  }

}

/* ---------- Phone normalize ---------- */

if(!$editError){

$normalized_phone = normalizePhoneNumber($editPhone);

if(!$normalized_phone || !preg_match('/^\+254\d{9}$/',$normalized_phone)){
    $editError = "Invalid phone number.";
}

}

/* ---------- Check username duplicates ---------- */
if(!$editError){
  $stmt = $conn->prepare("
    SELECT user_id FROM users
    WHERE username=? AND user_id != ?
    LIMIT 1
  ");
  $stmt->bind_param("si", $editUsername, $edit_user_id);
  $stmt->execute();
  $stmt->store_result();

  if($stmt->num_rows > 0){
    $editError = "Username already exists.";
  }

  $stmt->close();
}

/* ---------- Check email duplicates ---------- */
if(!$editError){
  $encEmail = base64_encode($editEmail);

  $stmt = $conn->prepare("
    SELECT user_id FROM users
    WHERE email=? AND user_id != ?
    LIMIT 1
  ");
  $stmt->bind_param("si", $encEmail, $edit_user_id);
  $stmt->execute();
  $stmt->store_result();

  if($stmt->num_rows > 0){
    $editError = "Email already exists.";
  }

  $stmt->close();
}

/* ---------- Check phone duplicate ---------- */
if(!$editError){
  $encPhone = base64_encode($normalized_phone);

  $stmt = $conn->prepare("
    SELECT user_id FROM users
    WHERE phone=? AND user_id != ?
    LIMIT 1
  ");
  $stmt->bind_param("si", $encPhone, $edit_user_id);
  $stmt->execute();
  $stmt->store_result();

  if($stmt->num_rows > 0){
    $editError = "Phone number already exists.";
  }

  $stmt->close();
}

/* ---------- UPDATE USER ---------- */

if(!$editError){

if($formType === "buyer"){
$editBusname = null;
$editBusmodel = null;
$editBustype = null;
$editMarket = null;
}

$encEmail = base64_encode($editEmail);
$encPhone = base64_encode($normalized_phone);

$stmt = $conn->prepare("
UPDATE users SET
full_name=?,
username=?,
email=?,
phone=?,
country=?,
county=?,
ward=?,
address=?,
business_name=?,
business_model=?,
business_type=?,
market_scope=?,
updated_at=NOW()
WHERE user_id=?
");

$stmt->bind_param(
"ssssssssssssi",
$full_name,
$editUsername,
$encEmail,
$encPhone,
$editCountry,
$editCounty,
$editWard,
$editAddress,
$editBusname,
$editBusmodel,
$editBustype,
$editMarket,
$edit_user_id
);

if($stmt->execute()){
  $editSuccess = "User updated successfully!";
}

else{
  $editError = "Update failed.";
}

$stmt->close();

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
  
  <title>ADMIN Page | Market Hub</title>
  <style>
    /* Pagination buttons */
    .dataTables_wrapper .dataTables_paginate .paginate_button{
      background-color: #898888;
    }

    /* Hover effect */
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: #898888da;
    }

    /* Active page */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: #088000 !important;
    }

    /* Info text below table */
    .dataTables_wrapper .dataTables_info {
      color: #ffffff;
    }
  </style>
</head>
<body id="adminBody">
  <div class="containerAdmin">
    <section>
      <h1>ADMIN&nbsp;PANEL<br><span>Maket&nbsp;Hub</span></h1>
      <div class="admin-rhs">
        <div class="notfy-wrapper">
          <i class="fa-solid fa-bell"></i>
          <span class="notfy-count">0</span>
        </div>
        <div class="admin-profile">
          <img src="<?= $safeProfileImage ?>" width="40" alt="Admin Profile">
          <p><?= htmlspecialchars($fullNameFormatted, ENT_QUOTES, 'UTF-8') ?> <br>
            <em><?= htmlspecialchars($accountTypeFormatted, ENT_QUOTES, 'UTF-8') ?></em></p>
        </div>
      </div>
    </section>

    <div class="navOverlay" onclick="toggleNavigationBar()" id="navOverlay"></div>
    <div id="navigation-button" onclick="toggleNavigationBar()">
      <img src="Images/Admin Menu.png" width="45" alt="Admin Navigation">
    </div>
    <div class="navigation-bar">
      <h4>Admin&nbsp;Navigation<i class="fa-solid fa-xmark" onclick="toggleNavigationBar()"></i></h4>
      <nav>
        <a href="#" class="nav-link active" data-tab="dashboard">
          <i class="fa-solid fa-gauge"></i>Dashboard
        </a>
        <a href="#" class="nav-link" data-tab="salesagents"><i class="fa-solid fa-users"></i>Sales Agents</a>
        <a href="#" class="nav-link"  data-tab="sellers"><i class="fa-solid fa-store"></i>Sellers</a>
        <a href="#" class="nav-link" data-tab="buyers"><i class="fa-solid fa-cart-shopping"></i>Buyers</a>
        <a href="#" class="nav-link" data-tab="propertyowners"><i class="fa-solid fa-building"></i>Property Owners</a>
        <a href="#" class="nav-link" data-tab="withdrawals">
          <i class="fa-solid fa-money-bill-transfer"></i>Withdrawals
        </a>
        <a href="#" class="nav-link" data-tab="transactions">
          <i class="fa-solid fa-money-bill-transfer"></i>Transactions
        </a>
        <a href="#" class="nav-link" data-tab="products">
          <i class="fa-solid fa-barcode"></i>Products
        </a>
        <a href="settingsPage.php" class="nav-link"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="logout.php" class="nav-link-admin-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
      </nav>

    </div>
    <main class="adminMain">
      <div class="admin-tab-panel active" data-tab="dashboard">
        <nav>
          <p>Dashboard</p>
          <ul>
            <a href="#">Home ~ </a> 
            <a href="#" class="active">Dashboard</a>
          </ul>
        </nav>
        <h2>Super Admin Dashboard</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card">
              <h3>Users</h3>
              <div class="value"><?= number_format($userData['total_users']) ?></div>
              <ul class="list">
                <li>
                  <span>Sellers</span>
                  <strong><?= number_format($userData['total_sellers']) ?></strong>
                </li>
                <li>
                  <span>Buyers</span>
                  <strong><?= number_format($userData['total_buyers']) ?></strong>
                </li>
                <li>
                  <span>Property Owners</span>
                  <strong><?= number_format($userData['total_property_owners']) ?></strong>
                </li>
              </ul>
            </div>

            <div class="card">
              <h3>Sales Agents</h3>

              <div class="value"><?= number_format($agentData['total_agents']) ?></div>

              <ul class="list">
                <li>
                  <span>Verified Agents</span>
                  <strong><?= number_format($agentData['verified_agents']) ?></strong>
                </li>
                <li>
                  <span>Under Review</span>
                  <strong><?= number_format($agentData['under_review']) ?></strong>
                </li>
              </ul>

              <small>Live system statistics</small>
            </div>

            <div class="card">
              <h3>Platform Balance</h3>
              <div class="value profit">KES 700,500</div>
              <div class="sub">Withdrawable Company Balance</div>
              <ul class="list">
                <li><span>API</span><strong>Online</strong></li>
              </ul>
              <small>↑ Healthy margin (72%)</small>
            </div>

            <div class="card">
              <h3>Gross Transaction Volume (GMV)</h3>
              <div class="value">KES 2,450,000</div>
              <div class="sub">All platform transactions (monthly)</div>
              <div class="progress"><div class="bar" style="width:82%"></div></div>
              <small>↑ 18% growth vs last month</small>
            </div>

            <div class="card">
              <h3>Net Profit</h3>
              <div class="value net-profit">KES 176,500</div>
              <div class="sub">Commission − Operating Costs</div>
              <div class="progress"><div class="bar" style="width:71%"></div></div>
              <small>↑ This month's net profit</small>
            </div>

            <div class="card">
              <h3>Operational Costs</h3>
              <div class="value loss">KES -68,500</div>
              <div class="sub">Monthly expenses</div>
              <ul class="list">
                <li><span>Hosting & Servers</span><strong>18,000</strong></li>
                <li><span>Payments (MPESA)</span><strong>22,500</strong></li>
                <li><span>Staff & Ops</span><strong>28,000</strong></li>
              </ul>
            </div>

            <div class="card">
              <h3>Platform Health</h3>
              <div class="value">Stable</div>
              <ul class="list">
                <li><span>API</span><strong>Online</strong></li>
                <li><span>MPESA</span><strong>Connected</strong></li>
                <li><span>Disputes</span><strong>3 Active</strong></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- TRANSACTIONS -->
        <div class="table-wrapper">
          <h3>Transactions History</h3>
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">All Transactions</option>
              <option value="Paid">Completed</option>
              <option value="Shipped">Pending</option>
              <option value="Pending">Processing</option>
            </select>
          </div>
          <table id="ordersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Transaction ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Commission</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>#TX20491</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Pending">
                <td>2.</td>
                <td>#TX20492</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-16</td>
              </tr>
              <tr data-status="Paid">
                <td>3.</td>
                <td>#TX20493</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-17</td>
              </tr>
              <tr data-status="Pending">
                <td>4.</td>
                <td>#TX20494</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-18</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="admin-tab-panel" data-tab="salesagents">
        <nav>
          <p>Sales Agents</p>
          <ul>
            <a href="#">Admin ~ </a> 
            <a href="#" class="active">Sales Agents</a>
          </ul>
        </nav>
        <h2>Sales Agents Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-users"></i>
              <div>
                <h3>Total Agents</h3>
                <div class="value"><?= number_format($agentData['total_agents']) ?></div>
                <small>Live system data</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <h3>Total Commissions</h3>
                <div class="value">KES 1.3M</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-chart-simple"></i>
              <div>
                <h3>Total Referrals</h3>
                <div class="value">587</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Verified">Verified</option>
              <option value="Unverified">Unverified</option>
              <option value="Suspended">Suspended</option>
            </select>
            <select id="regionFilter">
              <option value="all">🌍&nbsp;Region</option>
              <option value="Nairobi">Nairobi</option>
              <option value="Coast">Coast</option>
              <option value="Western">Western</option>
            </select>
          </div>
          <table id="salesagentsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Agent</th>
                <th>Phone</th>
                <th>Sub&nbsp;Agents</th>
                <th>Wallet</th>
                <th>Region</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Talk</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On:</th>
              </tr>
            </thead>

            <tbody>
            <?php 
            $count = 1;
            while ($agent = $agentsResult->fetch_assoc()):
              $name = ucfirst(strtolower($agent['full_name']));

              // 🔐 Decode
              $phone = decodePhone($agent['phone']);
              $maskedPhone = maskPhone($phone);

              $email = decodeEmail($agent['email']);
              $maskedEmail = maskEmail($email);

              // Profile Image
              if (!empty($agent['profile_image']) && file_exists($agent['profile_image'])) {
                  $profileImg = $agent['profile_image'];
              } else {
                  $profileImg = $defaultAvatar;
              }

              // Badge Logic
              if ($agent['status'] === 'suspended') {
                  $badgeClass = "suspendedSpan";
                  $badgeText = "Suspended";
              } elseif ($agent['is_verified'] == 1) {
                  $badgeClass = "verified";
                  $badgeText = "Verified";
              } else {
                  $badgeClass = "unverified";
                  $badgeText = "Unverified";
              }
            ?>
            <tr data-status="<?= $badgeText ?>">
              <td><?= $count++ ?>.</td>

              <td>
                <div class="adm-user-profile">
                  <img src="<?= htmlspecialchars($profileImg) ?>" style="border-radius:50%">
                  <?= htmlspecialchars($name) ?>
                </div>
              </td>

              <td><?= $maskedPhone ?></td>

              <td>0</td>

              <td>KES 12,000</td>

              <td>Coast</td>

              <td>
                <span class="badge <?= $badgeClass ?>">
                  <?= $badgeText ?>
                </span>
              </td>

              <td class="actions">
                <div>
                  <button 
                  class="btn-edit" data-user-id="<?= $agent['user_id'] ?>" 
                  data-tab="edit-forms" onclick="editRecord('agent', <?= (int)$agent['user_id'] ?>)">
                  <i class="fa-solid fa-pen"></i>
                  </button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-activate"><i class="fa-solid fa-toggle-on"></i>Activate</button>
                  <button class="btn-deactivate"><i class="fa-solid fa-toggle-off"></i>Deactivate</button>
                  <button class="btn-copy-link"><i class="fa-solid fa-link"></i> Copy&nbsp;Link</button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div>
              </td>

              <td class="comm-cell">
                <button class="comm-btn">
                  <i class="fas fa-ellipsis-vertical"></i>
                </button>

                <div class="comm-dropdown">
                  <a href="tel:<?= htmlspecialchars($phone) ?>"><i class="fas fa-phone"></i> Call</a>
                  <a href="https://wa.me/<?= preg_replace('/^\+/', '', $phone) ?>" target="_blank">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                  </a>
                  <a href="mailto:<?= htmlspecialchars($email ?? '') ?>"><i class="fas fa-envelope"></i> Email</a>
                  <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                </div>
              </td>

              <td><?= date("Y-m-d", strtotime($agent['created_at'])) ?></td>
              <td><?= date("Y-m-d", strtotime($agent['updated_at'])) ?></td>

            </tr>

            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="admin-tab-panel" data-tab="sellers">
        <nav>
          <p>Sellers</p>
          <ul>
            <a href="#">Admin ~ </a> 
            <a href="#" class="active">Sellers</a>
          </ul>
        </nav>
        <h2>Sellers Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
                <i class="fa-solid fa-users"></i>
                <div>
                    <h3>Total Sellers</h3>
                    <div class="value"><?= $totalSellers ?></div>
                </div>
            </div>

            <div class="card sub-card">
                <i class="fa-solid">🛡</i>
                <div>
                    <h3>Verified Sellers</h3>
                    <div class="value"><?= $verifiedCount ?></div>
                    <small>↑ <?= round($verifiedCount / max($totalSellers,1) * 100) ?>% verified</small>
                </div>
            </div>
            
            <div class="card sub-card">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <h3>Total Seller Wallets</h3>
                <div class="value">KES 4.7M</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-hand-holding-dollar"></i>
              <div>
                <h3>Pending Withdrawals</h3>
                <div class="value">4</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
                <option value="all">📌&nbsp;Status</option>
                <option value="Active">Active</option>
                <option value="Pending">Pending</option>
                <option value="Suspended">Suspended</option>
            </select>
            <select id="kycFilter">
                <option value="all">🛡&nbsp;KYC</option>
                <option value="Verified">Verified</option>
                <option value="Unverified">Unverified</option>
            </select>
            <select id="productsFilter">
                <option value="all">📦&nbsp;Has&nbsp;Products</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
          </div>
          <table id="sellersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Seller</th>
                <th>Products</th>
                <th>Wallet</th>
                <th>KYC</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Talk</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On:</th>
              </tr>
            </thead>
            <tbody>
              <?php $count = 1; ?>
              <?php foreach ($sellers as $seller): ?>
              <?php
                  // Determine KYC badge
                  $kycBadge = '';
                  if ($seller['is_verified'] == 1) {
                      $kycBadge = 'verified';
                      $kycText  = 'Verified';
                  } elseif ($seller['is_verified'] == 0) {
                      $kycBadge = 'unverified';
                      $kycText  = 'Unverified';
                  } elseif ($seller['is_verified'] == 2) {
                      $kycBadge = 'pendingDocs';
                      $kycText  = 'Pending Docs';
                  }
                  
                  // Default profile image
                  $img = (!empty($seller['profile_image']) && file_exists($seller['profile_image']))
                      ? $seller['profile_image']
                      : "Images/Maket Hub Logo.avif";
                  $phone = decodePhone($seller['phone']);
                  $maskedPhone = maskPhone($phone);

                  $email = decodeEmail($seller['email']);
                  $maskedEmail = maskEmail($email);
              ?>
              <tr data-user-id="<?= $seller['user_id'] ?>" data-status="<?= htmlspecialchars($seller['status']) ?>" data-kyc="<?= $kycText ?>">
                  <td><?= $count++ ?>.</td>
                  <td>
                      <div class="adm-user-profile">
                          <img src="<?= htmlspecialchars($img) ?>">
                          <?= htmlspecialchars(ucwords(strtolower($seller['full_name']))) ?>
                      </div>
                  </td>
                  <td><?= $productCounts[$seller['user_id']] ?? 0 ?></td>
                  <td>KES <?= number_format($seller['wallet'] ?? 0) ?></td>
                  <td><span class="badge <?= $kycBadge ?>"><?= $kycText ?></span></td>
                  <td><span class="badge <?= strtolower($seller['status']) ?>"><?= ucfirst($seller['status']) ?></span></td>
                  <td class="actions">
                    <div>
                      <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                      <button
                      class="btn-edit" data-user-id="<?= $seller['user_id'] ?>" 
                      data-tab="edit-forms" onclick="editRecord('seller', <?= (int)$seller['user_id'] ?>)">
                      <i class="fa-solid fa-pen"></i>
                      </button>
                      <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                      <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                  </td>
                  <td class="comm-cell">
                      <button class="comm-btn"><i class="fas fa-ellipsis-vertical"></i></button>
                      <div class="comm-dropdown">
                          <a href="tel:<?= htmlspecialchars($phone) ?>"><i class="fas fa-phone"></i> Call</a>
                          <a href="https://wa.me/<?= preg_replace('/^\+/', '', $phone) ?>" target="_blank">
                              <i class="fab fa-whatsapp"></i> WhatsApp
                          </a>
                          <a href="mailto:<?= htmlspecialchars($email ?? '') ?>"><i class="fas fa-envelope"></i> Email</a>
                          <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                      </div>
                  </td>
                  <td><?= date("Y-m-d", strtotime($seller['created_at'])) ?></td>
                  <td><?= date("Y-m-d", strtotime($seller['updated_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Buyers Management Table -->
      <div class="admin-tab-panel" data-tab="buyers">
        <nav>
          <p>Buyers</p>
          <ul>
            <a href="#">Admin ~ </a>
            <a href="#" class="active">Buyers</a>
          </ul>
        </nav>
        <h2>Buyers Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
                <i class="fa-solid fa-users"></i>
                <div>
                    <h3>Total Buyers</h3>
                    <div class="value"><?= number_format($totalBuyers) ?></div>
                </div>
            </div>

            <div class="card sub-card">
                <i class="fa-solid fa-user-check"></i>
                <div>
                    <h3>Active Buyers</h3>
                    <div class="value"><?= number_format($activeBuyers) ?></div>
                    <small>↑ 12% productivity growth this month</small>
                </div>
            </div>

            <div class="card sub-card">
                <i class="fa-solid fa-cart-shopping"></i>
                <div>
                    <h3>Total Orders</h3>
                    <div class="value"><?= number_format($totalOrders) ?></div>
                </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <h3>Total Spend</h3>
                <div class="value">KES 7.3M</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Active">Active</option>
              <option value="pending">Pending</option>
              <option value="Suspended">Suspended</option>
            </select>
          </div>
          <table id="buyersTable">
              <thead>
                  <tr>
                      <th>#</th>
                      <th>Buyer</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Region</th>
                      <th>Orders</th>
                      <th>Total&nbsp;Spend</th>
                      <th>Status</th>
                      <th>Actions</th>
                      <th>Talk</th>
                      <th>Created&nbsp;On</th>
                      <th>Updated&nbsp;On</th>
                  </tr>
              </thead>
              <tbody>
                  <?php $i = 1; while($buyer = mysqli_fetch_assoc($buyersResult)): 
                  // Default profile image
                  $img = (!empty($buyer['profile_image']) && file_exists($buyer['profile_image']))
                      ? $buyer['profile_image']
                      : "https://cdn-icons-png.flaticon.com/512/149/149071.png"; 
                  $phone = decodePhone($buyer['phone']);
                  $maskedPhone = maskPhone($phone);

                  $email = decodeEmail($buyer['email']);
                  $maskedEmail = maskEmail($email);

                  ?>
                  <tr data-status="<?= $buyer['status'] ?>">
                      <td><?= $i++ ?>.</td>
                      <td>

                      <div class="adm-user-profile">
                          <img src="<?= htmlspecialchars($img) ?>">
                          <?= htmlspecialchars(ucwords(strtolower($buyer['full_name']))) ?>
                      </div>
                      </td>
                      <td><?= htmlspecialchars($maskedEmail) ?></td>
                      <td><?= htmlspecialchars($maskedPhone) ?></td>
                      <td>Coast</td>
                      <td><?= $buyer['orders_count'] ?: 0 ?></td>
                      <td>KES <?= number_format($buyer['total_spend'] ?: 0) ?></td>
                      <td>
                        <span class="badge <?= strtolower($buyer['status']) ?>"><?= htmlspecialchars($buyer['status']) ?></span>
                      </td>
                      <td class="actions">
                        <div>
                          <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                          <button 
                          class="btn-edit" data-user-id="<?= $buyer['user_id'] ?>" 
                          data-tab="edit-forms" onclick="editRecord('buyer', <?= (int)$buyer['user_id'] ?>)">
                          <i class="fa-solid fa-pen"></i>
                          </button>
                          <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                          <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                      </td>
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
                      <td><?= date('Y-m-d', strtotime($buyer['created_at'])) ?></td>
                      <td><?= date('Y-m-d', strtotime($buyer['updated_at'])) ?></td>
                  </tr>
                  <?php endwhile; ?>
              </tbody>
          </table>
        </div>
      </div>
      <!-- Property Owners Management Table -->
      <div class="admin-tab-panel" data-tab="propertyowners">
        <nav>
          <p>Property Owners</p>
          <ul>
            <a href="#">Admin&nbsp;~</a>
            <a href="#" class="active">Property Owners</a>
          </ul>
        </nav>
        <h2>Property Owners Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-users"></i>
              <div>
                <h3>Total Owners</h3>
                <div class="value"><?= $totalOwners ?></div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-house"></i>
              <div>
                <h3>Total Properties</h3>

                <div class="value">593</div>

                <small>↑ 12% productivity growth this month</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-money-bill-wave"></i>
              <div>
                <h3>Total Portfolio Value</h3>
                <div class="value">KES 49.3M</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-percent"></i>
              <div>
                <h3>Average Occupancy</h3>
                <div class="value">83%</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Active">Active</option>
              <option value="Pending">Pending</option>
              <option value="Suspended">Suspended</option>
            </select>
          </div>
          <table id="propertyownersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Owner</th>
                <th>Contact</th>
                <th>Properties</th>
                <th>Occupancy</th>
                <th>Verification</th>
                <th>Actions</th>
                <th>Talk</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On:</th>
              </tr>
            </thead>
            <tbody>
            <?php $count = 1; ?>
            <?php foreach ($propertyOwners as $owner): ?>

            <?php
                // KYC Badge Logic
                if ($owner['is_verified'] == 1) {
                    $kycClass = 'verified';
                    $kycText  = 'Verified';
                } elseif ($owner['is_verified'] == 2) {
                    $kycClass = 'pendingDocs';
                    $kycText  = 'Pending Docs';
                } else {
                    $kycClass = 'unverified';
                    $kycText  = 'Unverified';
                }

                // Default profile image
                $img = (!empty($owner['profile_image']) && file_exists($owner['profile_image']))
                ? $owner['profile_image']
                : "https://cdn-icons-png.flaticon.com/512/149/149071.png";

                $email = decodeEmail($owner['email']);
                $maskedEmail = maskEmail($email);
                $phone = decodePhone($owner['phone']);
                $maskedPhone = maskPhone($phone);
            ?>

            <tr data-status="<?= htmlspecialchars($owner['status']) ?>">
                <td><?= $count++ ?>.</td>

                <td>
                  <div class="adm-user-profile">
                    <img src="<?= htmlspecialchars($img) ?>">
                    <?= htmlspecialchars(ucwords(strtolower($owner['full_name']))) ?>
                  </div>
                  <em>ID: <?= $owner['user_id'] ?></em>
                </td>

                <td>
                  <p class="contactOwer">
                    <?= htmlspecialchars($maskedEmail) ?><br>
                    <?= htmlspecialchars($maskedPhone) ?>
                  </p>
                </td>

                <!-- Properties column (0 for now unless you have property table) -->
                <td>0</td>

                <!-- Occupancy column (placeholder unless you calculate it) -->
                <td>--</td>

                <td>
                    <span class="badge <?= $kycClass ?>">
                        <?= $kycText ?>
                    </span>
                </td>

                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button
                    class="btn-edit" data-user-id="<?= $owner['user_id'] ?>" 
                    data-tab="edit-forms" onclick="editRecord('owner', <?= (int)$owner['user_id'] ?>)">
                    <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>

                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:<?= htmlspecialchars($owner['phone']) ?>">
                        <i class="fas fa-phone"></i> Call
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/^\+/', '', $owner['phone']) ?>" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="mailto:<?= htmlspecialchars($owner['email']) ?>">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                    <a href="#">
                        <i class="fas fa-comment-dots"></i> SMS
                    </a>
                  </div>
                </td>

                <td><?= date("Y-m-d", strtotime($owner['created_at'])) ?></td>
                <td><?= date("Y-m-d", strtotime($owner['updated_at'])) ?></td>
            </tr>

            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="admin-tab-panel" data-tab="withdrawals">
        <nav>
          <p>Withdrawals</p>
          <ul>
            <a href="#">Admin&nbsp;~</a>
            <a href="#" class="active">Withdrawals</a>
          </ul>
        </nav>
        <h2>Withdrawals Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-hourglass-half"></i>
              <div>
                <h3>Pending Requests</h3>
                <div class="value">17</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-check-circle"></i>
              <div>
                <h3>Approved Today</h3>

                <div class="value">3.1M</div>

                <small>↑ 12% productivity growth this month</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-money-bill"></i>
              <div>
                <h3>Total Withdrawn</h3>
                <div class="value">KES 37.1M</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Pending">Pending</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
            </select>
            <select id="statusFilter">
              <option value="all">👤&nbsp;Account&nbsp;Type</option>
              <option value="Seller">Seller</option>
              <option value="Agent">Agent</option>
              <option value="Property Owner">Property&nbsp;Owner</option>
            </select>
          </div>
          <table id="withdrawalsTable">
            <thead>
              <tr>
                <th>User</th>
                <th>Type</th>
                <th>Available</th>
                <th>Requested</th>
                <th>Method</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Talk</th>
                <th>Requested&nbsp;At:</th>
                <th>Updated&nbsp;At:</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=12" style="border-radius:50%">Blessed Emmanuel
                  </div>
                  <em>+254759578630</em>
                </td>
                <td>Agent</td>
                <td>KES 800,000</td>
                <td>KES 33,489</td>
                <td>M-pesa</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</i></button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-15</td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=13" style="border-radius:50%">John Mwangi
                  </div>
                  <em>+254711000001</em>
                </td>
                <td>Agent</td>
                <td>KES 120,000</td>
                <td>KES 4,800</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-16</td>
                <td>2025-01-16</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=14" style="border-radius:50%">Mary Wanjiku
                  </div>
                  <em>+254711000002</em>
                </td>
                <td>Property Owner</td>
                <td>KES 560,000</td>
                <td>KES 22,400</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-17</td>
                <td>2025-01-17</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=15" style="border-radius:50%">Kevin Otieno
                  </div>
                  <em>+254711000003</em>
                </td>
                <td>Agent</td>
                <td>KES 75,000</td>
                <td>KES 3,000</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-18</td>
                <td>2025-01-18</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=16" style="border-radius:50%">Faith Njeri
                  </div>
                  <em>+254711000004</em>
                </td>
                <td>Seller</td>
                <td>KES 310,000</td>
                <td>KES 12,400</td>
                <td>M-pesa</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-19</td>
                <td>2025-01-19</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=17" style="border-radius:50%">Brian Kiptoo
                  </div>
                  <em>+254711000005</em>
                </td>
                <td>Property Owner</td>
                <td>KES 980,000</td>
                <td>KES 39,200</td>
                <td>Bank</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-20</td>
                <td>2025-01-20</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=18" style="border-radius:50%">Lucy Achieng
                  </div>
                  <em>+254711000006</em>
                </td>
                <td>Seller</td>
                <td>KES 44,000</td>
                <td>KES 1,760</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-21</td>
                <td>2025-01-21</td>
              </tr>
              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=19" style="border-radius:50%">Daniel Kariuki
                  </div>
                  <em>+254711000007</em>
                </td>
                <td>Property Owner</td>
                <td>KES 250,000</td>
                <td>KES 10,000</td>
                <td>M-pesa</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-22</td>
                <td>2025-01-22</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=20" style="border-radius:50%">Susan Mutua
                  </div>
                  <em>+254711000008</em>
                </td>
                <td>Seller</td>
                <td>KES 680,000</td>
                <td>KES 27,200</td>
                <td>Bank</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-23</td>
                <td>2025-01-23</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=21" style="border-radius:50%">Peter Ndegwa
                  </div>
                  <em>+254711000009</em>
                </td>
                <td>Agent</td>
                <td>KES 90,000</td>
                <td>KES 3,600</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-24</td>
                <td>2025-01-24</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=22" style="border-radius:50%">Janet Kiplagat
                  </div>
                  <em>+254711000010</em>
                </td>
                <td>Property Owner</td>
                <td>KES 1,200,000</td>
                <td>KES 48,000</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-25</td>
                <td>2025-01-25</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=23" style="border-radius:50%">Samuel Ouma
                  </div>
                  <em>+254711000011</em>
                </td>
                <td>Agent</td>
                <td>KES 340,000</td>
                <td>KES 13,600</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-26</td>
                <td>2025-01-26</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=24" style="border-radius:50%">Grace Wambui
                  </div>
                  <em>+254711000012</em>
                </td>
                <td>Seller</td>
                <td>KES 60,000</td>
                <td>KES 2,400</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-27</td>
                <td>2025-01-27</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=25" style="border-radius:50%">Dennis Barasa
                  </div>
                  <em>+254711000013</em>
                </td>
                <td>Agent</td>
                <td>KES 470,000</td>
                <td>KES 18,800</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-28</td>
                <td>2025-01-28</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=26" style="border-radius:50%">Alice Chebet
                  </div>
                  <em>+254711000014</em>
                </td>
                <td>Seller</td>
                <td>KES 150,000</td>
                <td>KES 6,000</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-29</td>
                <td>2025-01-29</td>
              </tr>
              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=27" style="border-radius:50%">Michael Kimani
                  </div>
                  <em>+254711000015</em>
                </td>
                <td>Agent</td>
                <td>KES 520,000</td>
                <td>KES 20,800</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-30</td>
                <td>2025-01-30</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=28" style="border-radius:50%">Esther Waithera
                  </div>
                  <em>+254711000016</em>
                </td>
                <td>Seller</td>
                <td>KES 210,000</td>
                <td>KES 8,400</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-31</td>
                <td>2025-01-31</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=29" style="border-radius:50%">Paul Onyango
                  </div>
                  <em>+254711000017</em>
                </td>
                <td>Propery Owner</td>
                <td>KES 65,000</td>
                <td>KES 2,600</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-01</td>
                <td>2025-02-01</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=30" style="border-radius:50%">Naomi Cherono
                  </div>
                  <em>+254711000018</em>
                </td>
                <td>Seller</td>
                <td>KES 890,000</td>
                <td>KES 35,600</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-02</td>
                <td>2025-02-02</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=31" style="border-radius:50%">Isaac Muriuki
                  </div>
                  <em>+254711000019</em>
                </td>
                <td>Agent</td>
                <td>KES 300,000</td>
                <td>KES 12,000</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-03</td>
                <td>2025-02-03</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=32" style="border-radius:50%">Brenda Atieno
                  </div>
                  <em>+254711000020</em>
                </td>
                <td>Propery Owner</td>
                <td>KES 55,000</td>
                <td>KES 2,200</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-04</td>
                <td>2025-02-04</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=33" style="border-radius:50%">Joseph Karanja
                  </div>
                  <em>+254711000021</em>
                </td>
                <td>Agent</td>
                <td>KES 760,000</td>
                <td>KES 30,400</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-05</td>
                <td>2025-02-05</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=34" style="border-radius:50%">Ruth Jepkosgei
                  </div>
                  <em>+254711000022</em>
                </td>
                <td>Seller</td>
                <td>KES 180,000</td>
                <td>KES 7,200</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-06</td>
                <td>2025-02-06</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=35" style="border-radius:50%">Allan Mutiso
                  </div>
                  <em>+254711000023</em>
                </td>
                <td>Property Owner</td>
                <td>KES 95,000</td>
                <td>KES 3,800</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-07</td>
                <td>2025-02-07</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=36" style="border-radius:50%">Lydia Muthoni
                  </div>
                  <em>+254711000024</em>
                </td>
                <td>Seller</td>
                <td>KES 640,000</td>
                <td>KES 25,600</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-08</td>
                <td>2025-02-08</td>
              </tr>
              
            </tbody>
          </table>
        </div>
      </div>

      <div class="admin-tab-panel" data-tab="transactions">
        <nav>
          <p>Transactions</p>
          <ul>
            <a href="#">Home ~ </a> 
            <a href="#" class="active">Transactions</a>
          </ul>
        </nav>
        <h2>View all tansactions</h2>
        <div class="admin-tab-content">
        </div>
        <!-- TRANSACTIONS -->
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">All Transactions</option>
              <option value="Delivered">Completed</option>
              <option value="Shipped">Pending</option>
              <option value="Processing">Processing</option>
            </select>
          </div>
          <table id="transactionsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Transaction ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Commission</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>#TX20491</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Pending">
                <td>2.</td>
                <td>#TX20492</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-16</td>
              </tr>
              <tr data-status="Paid">
                <td>3.</td>
                <td>#TX20493</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-17</td>
              </tr>
              <tr data-status="Pending">
                <td>4.</td>
                <td>#TX20494</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-18</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>7.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>8.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>9.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>10.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>11.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>12.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>13.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>14.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>15.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>16.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>17.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>18.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="admin-tab-panel" data-tab="products">
        <nav>
        <p>Products</p>
        <ul>
            <a href="#">Admin ~ </a> 
            <a href="#" class="active">Products</a>
        </ul>
        </nav>
        <h2>Market Hub Products</h2>
        <div class="admin-tab-content">
        <div class="cards">
            <div class="card sub-card">
                <i class="fa-solid fa-box"></i>
                <div>
                <h3>Total Products</h3>
                <div class="value">245</div>
                <small>Products in system</small>
                </div>
            </div>
            <div class="card sub-card">

                <i class="fa-solid fa-circle-check"></i>
                <div>
                <h3>Active Products</h3>
                <div class="value">5</div>
                <small>Currently visible</small>
                </div>
            </div>

            <div class="card sub-card">
                
                <i class="fa-solid fa-ban"></i>
                <div>
                <h3>Inactive Products</h3>
                <div class="value">193</div>
                <small>Disabled or hidden</small>
                </div>
            </div>

            <div class="card sub-card">
                <i class="fa-solid fa-coins"></i>
                <div>
                <h3>Total Product Value</h3>
                <div class="value">KES 2.4M</div>
                </div>
            </div>
        </div>
        </div>
        <div class="tabs-container">
            <div class="tabs">
            <button class="tab-btn-admin active" data-tab="beauty">Beauty</button>
            <button class="tab-btn-admin" data-tab="electronics">Electronics</button>
            <button class="tab-btn-admin" data-tab="fashion">Fashion</button>
            </div>
            <div id="company-products" class="tab-panel-admin">
                <div class="tab-top">
                    <p>Market Hub Store<br><strong>Your control center for Market Hub products <i class="fa-regular fa-circle-check"></i></strong></p>
                    <button onclick="toggleProductsAdd(true)">
                    <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Product</span>
                    </button>

                </div>

                <!-- PRODUCTS GRID -->
                <div id="beauty" class="products-grid-admin active">
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- 
                    <p class="noproducts-admin-p">No products uploaded yet. Click "Add Product" to start.</p> -->
                </div>
                <!-- PRODUCTS GRID -->
                <div id="electronics" class="products-grid-admin">
                    <div class="product-card">
                        <img src="Images/6 Litre Electric Pressure Cooker.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">6 Litre Electric Pressure Cooker</div>
                        <div class="product-price">KES 5,200.00</div>
                        <p class="product-description">Fast cooking, energy saving, perfect for family meals.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- PRODUCTS GRID -->
                <div id="fashion" class="products-grid-admin">
                    <div class="product-card">
                        <img src="Images/Ipcone 16-inch standing fan.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">16-inch Standing Fan</div>
                        <div class="product-price">KES 2,350.00</div>
                        <p class="product-description">Powerful airflow with adjustable height.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                        <div class="product-name">Executive Leather Laptop Bag</div>
                        <div class="product-price">KES 1,300.00</div>
                        <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                        <div class="card-actions">
                            <a href="#" class="edit">
                            <i class="fa fa-pen"></i> Edit
                            </a>
                            <form method="POST">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="admin-tab-panel" data-tab="edit-forms">
        <nav>
          <p>Edit-Manage Sales Agents</p>
          <ul>
            <a href="#">Admin&nbsp;~</a>
            <a href="#" class="active">Agents</a><!-- 
            <a href="">Orders</a>
            <a href="">Users</a> -->
          </ul>
        </nav>
        <h2>Agents Manual Management</h2>
        <div id="seller-products" class="tab-panel-admin">
          <div class="tab-top">
            <p>Manually manage agents</em> <br><strong>Oversee existing agents individual data <i class="fa-regular fa-circle-check"></i></strong></p>
            <button id="goBackBtn">
              <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
            </button>

          </div>
          <div class="form-wrapper" id="agent-edit-form">
            <form method="POST" enctype="multipart/form-data"><!-- 
              <input type="hidden" name="user_id" value="<?= $user_id ?>"> -->
              <input type="hidden" name="form_type" value="agent">
              <h1>Update Agent Details</h1>
              <?php if (!empty($editError)): ?>
                <p class="errorMessage">
                  <i class="fa-solid fa-circle-exclamation"></i>
                  <?= htmlspecialchars($editError, ENT_QUOTES) ?>
                </p>
              <?php elseif ($editSuccess): ?>
                <p class="successMessage">
                  <i class="fa-solid fa-check-circle"></i> <?= $editSuccess ?>
                </p>
              <?php endif; ?>
              <div class="formBody">
                <div class="inp-box">
                  <label>Agent's Full Name</label>
                  <input type="text" value="<?= $full_name ?>" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="inp-box">
                  <label>Agent's Username</label>
                  <input type="text" value="<?= $editUsername ?>" name="username" placeholder="e.g blessedemmanuel254" required>
                </div>
                <div class="inp-box">
                  <label>Agent's Email ID</label>
                  <input type="email" value="<?= $editEmail ?>" name="email" placeholder="john@example.com" required>
                </div>
                <div class="inp-box">
                  <label>Agent's Phone</label>
                  <input type="text" value="<?= $editPhone ?>" name="phone" placeholder="075***630" required>
                </div>
                <div class="inp-box">

                  <label>Country</label>
                  <select name="country" required>
                    <option value=""><p>-- Select Country --</p></option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">

                  <label>County</label>
                  <select name="county" required>
                    <option value=""><p>-- Select County --</p></option>
                    <option value="Kilifi" <?php echo ($editCounty === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">
                  <label>Agent's Address</label>
                  <input type="text" value="<?= $editAddress ?>" name="address" placeholder="eg. Kilifi town" required>
                </div>
                <div class="inp-box">

                  <label>Ward</label>
                  <select name="ward" required>
                    <option value=""><p>-- Select Ward --</p></option>
                    <option value="Sokoni Ward" <?php echo ($editWard === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div></div>
                <div class="inp-box">
                  <label class="agency_code">Agency Code (read-only)<i class="fa-solid fa-copy" onclick="copyAgencyCode()"></i></label>
                    <input type="text" id="agencyCodeInput" value="<?= $edit_agency_code ?>" name="agency_code" placeholder="A56D3847" disabled
                    >
                </div>
                <div></div>
                <button type="submit">
                  Update User
                </button>
              </div>

            </form>
          </div>
          <div class="form-wrapper" id="seller-edit-form">
            <form method="POST" enctype="multipart/form-data"><!-- 
              <input type="hidden" name="user_id" value="<?= $user_id ?>"> -->
              <input type="hidden" name="form_type" value="seller">
              <h1>Update Seller Details</h1>
              <?php if (!empty($editError)): ?>
                <p class="errorMessage">
                  <i class="fa-solid fa-circle-exclamation"></i>
                  <?= htmlspecialchars($editError, ENT_QUOTES) ?>
                </p>
              <?php elseif ($editSuccess): ?>
                <p class="successMessage">
                  <i class="fa-solid fa-check-circle"></i> <?= $editSuccess ?>
                </p>
              <?php endif; ?>
              <div class="formBody">
                <div class="inp-box">
                  <label>Seller's Full Name</label>
                  <input type="text" value="<?= $full_name ?>" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="inp-box">
                  <label>Seller's Username</label>
                  <input type="text" value="<?= $editUsername ?>" name="username" placeholder="e.g blessedemmanuel254" required>
                </div>
                <div class="inp-box">
                  <label>Seller's Email ID</label>
                  <input type="email" value="<?= $editEmail ?>" name="email" placeholder="john@example.com" required>
                </div>
                <div class="inp-box">
                  <label>Sellers's Phone</label>
                  <input type="text" value="<?= $editPhone ?>" name="phone" placeholder="075***630" required>
                </div>
                <div class="inp-box">
                  <label>Business Name</label>
                  <input type="text" value="<?= $edit_business_name ?>" name="business_name" placeholder="Main Cateen" required>
                </div>
                <div class="inp-box">

                  <label>Business Model</label>
                  <select name="business_model" required>
                    <option value=""><p>-- Select Business Model --</p></option>
                    <option value="products" <?php echo ($edit_business_model === 'products') ? 'selected' : ''; ?>>Products</option>
                    <option value="services" <?php echo ($edit_business_model === 'services') ? 'selected' : ''; ?>>Services</option>
                    <option value="rental" <?php echo ($edit_business_model === 'rental') ? 'selected' : ''; ?>>Rental</option>
                  </select>
                </div>
                <div class="inp-box">

                  <label>Business type</label>
                  <select name="business_type" required>
                    <option value=""><p>-- Select Type --</p></option>
                    <option value="shop" <?php echo ($edit_business_type === 'shop') ? 'selected' : ''; ?>>Shop</option>
                    <option value="supermarket" <?php echo ($edit_business_type === 'supermarket') ? 'selected' : ''; ?>>Supermarket</option>
                    <option value="kiosk" <?php echo ($edit_business_type === 'kiosk') ? 'selected' : ''; ?>>Kiosk</option>
                    <option value="kibanda" <?php echo ($edit_business_type === 'kibanda') ? 'selected' : ''; ?>>Kibanda</option>
                    <option value="canteen" <?php echo ($edit_business_type === 'canteen') ? 'selected' : ''; ?>>Canteen</option>
                    <option value="service_provider" <?php echo ($edit_business_type === 'service_provider') ? 'selected' : ''; ?>>Service_provider</option>
                    <option value="rental" <?php echo ($edit_business_type === 'rental') ? 'selected' : ''; ?>>Rental</option>
                  </select>
                </div>
                <div class="inp-box">
                  <label>Seller's Address</label>
                  <input type="text" value="<?= $editAddress ?>" name="address" placeholder="eg. Kilifi town" required>
                </div>
                <div class="inp-box">

                  <label>Country</label>
                  <select name="country" required>
                    <option value=""><p>-- Select Country --</p></option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">

                  <label>Market Type</label>
                  <select name="market_scope" required>
                    <option value=""><p>-- Select Market Type --</p></option>
                    <option value="local" <?php echo ($edit_market_scope === 'local') ? 'selected' : ''; ?>>Local</option>
                    <option value="national" <?php echo ($edit_market_scope === 'national') ? 'selected' : ''; ?>>National</option>
                    <option value="global" <?php echo ($edit_market_scope === 'global') ? 'selected' : ''; ?>>Global</option>
                  </select>
                </div>
                <div class="inp-box">

                  <label>County</label>
                  <select name="county" required>
                    <option value=""><p>-- Select County --</p></option>
                    <option value="Kilifi" <?php echo ($editCounty === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">

                  <label>Ward</label>
                  <select name="ward" required>
                    <option value=""><p>-- Select Ward --</p></option>
                    <option value="Sokoni Ward" <?php echo ($editWard === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div></div>
                <div></div><!-- 
                <div class="inp-box">
                  <label>Agency Code (read-only)</label>
                  <input type="text" name="agency_code" placeholder="A56D3847" disabled required>
                </div> -->
                <div></div>
                <button type="submit">
                  Update User
                </button>
              </div>

            </form>
          </div>
          <div class="form-wrapper" id="buyer-edit-form">
            <form method="POST" enctype="multipart/form-data"><!-- 
              <input type="hidden" name="user_id" value="<?= $user_id ?>"> -->
              <input type="hidden" name="form_type" value="buyer">
              <h1>Update Buyer Details</h1>
              <?php if (!empty($editError)): ?>
                <p class="errorMessage">
                  <i class="fa-solid fa-circle-exclamation"></i>
                  <?= htmlspecialchars($editError, ENT_QUOTES) ?>
                </p>
              <?php elseif ($editSuccess): ?>
                <p class="successMessage">
                  <i class="fa-solid fa-check-circle"></i> <?= $editSuccess ?>
                </p>
              <?php endif; ?>
              <div class="formBody">
                <div class="inp-box">
                  <label>Buyer's Full Name</label>
                  <input type="text" value="<?= $full_name ?>" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="inp-box">
                  <label>Buyer's Username</label>
                  <input type="text" value="<?= $editUsername ?>" name="username" placeholder="e.g blessedemmanuel254" required>
                </div>
                <div class="inp-box">
                  <label>Buyer's Email ID</label>
                  <input type="email" value="<?= $editEmail ?>" name="email" placeholder="john@example.com" required>
                </div>
                <div class="inp-box">
                  <label>Buyer's Phone</label>
                  <input type="text" value="<?= $editPhone ?>" name="phone" placeholder="075***630" required>
                </div>
                <div class="inp-box">

                  <label>Country</label>
                  <select name="country" required>
                    <option value=""><p>-- Select Country --</p></option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">
                  <label>Buyer's Address</label>
                  <input type="text" value="<?= $editAddress ?>" name="address" placeholder="eg. Kilifi town" required>
                </div>
                <div class="inp-box">

                  <label>County</label>
                  <select name="county" required>
                    <option value=""><p>-- Select County --</p></option>
                    <option value="Kilifi" <?php echo ($editCounty === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">

                  <label>Ward</label>
                  <select name="ward" required>
                    <option value=""><p>-- Select Ward --</p></option>
                    <option value="Sokoni Ward" <?php echo ($editWard === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div></div>
                <button type="submit">
                  Update User
                </button>
              </div>

            </form>
          </div>
          <div class="form-wrapper" id="owner-edit-form">
            <form method="POST" enctype="multipart/form-data"><!-- 
              <input type="hidden" name="user_id" value="<?= $user_id ?>"> -->
              <input type="hidden" name="form_type" value="owner">
              <h1>Update Owner Details</h1>
              <?php if (!empty($editError)): ?>
                <p class="errorMessage">
                  <i class="fa-solid fa-circle-exclamation"></i>
                  <?= htmlspecialchars($editError, ENT_QUOTES) ?>
                </p>
              <?php elseif ($editSuccess): ?>
                <p class="successMessage">
                  <i class="fa-solid fa-check-circle"></i> <?= $editSuccess ?>
                </p>
              <?php endif; ?>
              <div class="formBody">
                <div class="inp-box">
                  <label>Owner's Full Name</label>
                  <input type="text" value="<?= $full_name ?>" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="inp-box">
                  <label>Owner's Username</label>
                  <input type="text" value="<?= $editUsername ?>" name="username" placeholder="e.g blessedemmanuel254" required>
                </div>
                <div class="inp-box">
                  <label>Owner's Email ID</label>
                  <input type="email" value="<?= $editEmail ?>" name="email" placeholder="john@example.com" required>
                </div>
                <div class="inp-box">
                  <label>Owner's Phone</label>
                  <input type="text" value="<?= $editPhone ?>" name="phone" placeholder="075***630" required>
                </div><!-- 
                <div class="account-type-box">
                  <p class="account-title">Property Type</p>

                  <label class="account-type">
                    <input type="radio" name="property_type" value="cars" required>
                    <div class="radio-dot"></div>
                    <span>Cars</span>
                  </label>

                  <label class="account-type">
                    <input type="radio" name="property_type" value="rental_houses" required>
                    <div class="radio-dot"></div>
                    Rental Houses
                  </label>

                  <label class="account-type">
                    <input type="radio" name="property_type" value="lands" required>
                    <div class="radio-dot"></div>
                    Lands
                  </label>

                  <label class="account-type">
                    <input type="radio" name="property_type" value="Tents" required>
                    <div class="radio-dot"></div>
                    Tents
                  </label>

                  <label class="account-type">
                    <input type="radio" name="property_type" value="air_bnbs" required>
                    <div class="radio-dot"></div>
                    Air BNBs
                  </label>
                </div> -->
                <div class="inp-box">

                  <label>Country</label>
                  <select name="country" required>
                    <option value=""><p>-- Select Country --</p></option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCountry === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">

                  <label>County</label>
                  <select name="county" required>
                    <option value=""><p>-- Select County --</p></option>
                    <option value="Kilifi" <?php echo ($editCounty === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editCounty === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">
                  <label>Owner's Address</label>
                  <input type="text" value="<?= $editAddress ?>" name="address" placeholder="eg. Kilifi town" required>
                </div>
                <div class="inp-box">

                  <label>Ward</label>
                  <select name="ward" required>
                    <option value=""><p>-- Select Ward --</p></option>
                    <option value="Sokoni Ward" <?php echo ($editWard === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($editWard === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div></div>
                <button type="submit">
                  Update User
                </button>
              </div>

            </form>
          </div>
          
          <div class="form-wrapper" id="product-edit-form">
            <form method="POST" enctype="multipart/form-data">

              <?php if ($editMode): ?>
                  <input type="hidden" name="edit_product_id" value="<?= $editProductId ?>">
              <?php endif; ?>

              <h1><?= $editMode ? 'Edit Product' : 'Add Product' ?></h1>

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

              <div class="formBody">
                  <div class="inp-box">
                      <label>Product Name</label>
                      <input type="text" name="name" placeholder="Enter name" 
                          value="<?= htmlspecialchars($productName, ENT_QUOTES) ?>" required>
                  </div>
                  <div class="inp-box">
                    <label>Price (KES)</label>
                    <input type="number" name="price" step="0.01" placeholder="Enter price"
                    value="<?= htmlspecialchars($price, ENT_QUOTES) ?>"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '')" min="0" required>
                  </div>
                  <div class="inp-box">

                    <label>Currency :</label>
                    <select name="currency">
                      <option value=""><p>-- Select currency --</p></option>
                      <option value="KES" <?php echo ($currency === 'KES') ? 'selected' : ''; ?>>KES</option><!-- 
                      <option value="USD" <?php echo ($currency === 'USD') ? 'selected' : ''; ?>>USD</option>
                      <option value="TSH" <?php echo ($currency === 'TSH') ? 'selected' : ''; ?>>TSH</option> -->
                    </select>
                  </div>
                  <div class="inp-box">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Enter description" 
                    value="<?= htmlspecialchars($productDescription, ENT_QUOTES) ?>" required>
                  </div>
                  <div class="inp-box">
                    <label>Is Active?</label>
                    <select id="is_active" name="is_active">
                      <option value=""><p>-- Select if active --</p></option>
                      <option value="1" <?php echo ($is_active === '1') ? 'selected' : ''; ?>>Yes</option>
                      <option value="0" <?php echo ($is_active === '0') ? 'selected' : ''; ?>>No</option>
                    </select>
                  </div>

                  <?php if ($editMode): ?>
                      <!-- IMAGE PREVIEW ONLY IN EDIT MODE -->
                      <div class="inp-box">
                          <label>Product Image</label>
                          <?php if (!empty($currentImagePath) && file_exists($currentImagePath)): ?>
                            <div class="edit-preview">
                              <img src="<?= htmlspecialchars($currentImagePath) ?>" 
                                  style="">
                              <p style="font-size:12px;">Current Image</p>
                            </div>
                          <?php endif; ?>
                      </div>

                      <div class="inp-box">
                          <label>Change Product Image (optional)</label>
                          <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
                          <div class="note">
                              600×600 – 1600×1600 px • Max 5MB<br>
                              Automatically optimized for buyers
                          </div>
                      </div>
                  <?php else: ?>
                      <!-- ONLY FOR ADD MODE -->
                      <div class="inp-box">
                        <label>Upload Product Image</label>
                        <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" required>
                        <div class="note">
                          600×600 – 1600×1600 px • Max 5MB<br>
                          Automatically optimized for buyers
                        </div>
                      </div>
                  <?php endif; ?>

                  <button type="submit">
                    <?= $editMode ? 'Update Product' : 'Add Product' ?>
                  </button>
              </div>

            </form>
          </div>
        </div>
      </div>

    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  <script src="assets/js/general.js" type="text/javascript" defer></script>
  <script>
  $(document).ready(function () {

    const dataTableConfig = {
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
    };

    // Initialize all tables
    const tables = $('#salesagentsTable, #sellersTable, #buyersTable, #transactionsTable, #withdrawalsTable, #propertyownersTable')
    .DataTable(dataTableConfig);

    // ===== Custom Status + Region filter for salesagentsTable =====
    var salesAgentsTable = $('#salesagentsTable').DataTable();

    // Custom filter function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      if (settings.nTable.id !== 'salesagentsTable') return true; // only apply to salesagentsTable

      var statusFilter = $('#statusFilter').val();
      var regionFilter = $('#regionFilter').val();

      var rowStatus = $(data[6]).text() || data[6]; // Status column (6)
      var rowRegion = $(data[5]).text() || data[5]; // Region column (5)

      if (statusFilter !== 'all' && rowStatus.trim() !== statusFilter) {
        return false;
      }

      if (regionFilter !== 'all' && rowRegion.trim() !== regionFilter) {
        return false;
      }

      return true;
    });

    // Trigger filter on change
    $('#statusFilter, #regionFilter').on('change', function() {
      salesAgentsTable.draw();
    });

  });
  </script>
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
    // MARKET HUB PRODUCTS GRID SWITCH JS

    document.addEventListener("DOMContentLoaded", function () {

    const tabButtons = document.querySelectorAll(".tab-btn-admin");
    const productPanels = document.querySelectorAll(".products-grid-admin");

    tabButtons.forEach((button) => {

        button.addEventListener("click", function () {

        const targetTab = this.dataset.tab;

        // Remove active class from buttons
        tabButtons.forEach(btn => btn.classList.remove("active"));

        // Activate clicked button
        this.classList.add("active");

        // Hide all product panels
        productPanels.forEach(panel => {
            panel.classList.remove("active");
        });

        // Show selected panel instantly
        const targetPanel = document.getElementById(targetTab);

        if (targetPanel) {
            targetPanel.classList.add("active");
        }

        });

    });

    });
  </script>
</body>
</html>
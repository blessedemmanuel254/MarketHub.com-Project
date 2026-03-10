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
  
  <title>ADMIN Page | Maket Hub</title>
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
        <a href="#" class="nav-link" data-tab="edit-forms">
          <i class="fa-solid fa-money-bill-transfer"></i>Edit&nbsp;Page
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
                  <span>Active Agents</span>
                  <strong><?= number_format($agentData['active_agents']) ?></strong>
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
                          <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
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
                      : "Images/Maket Hub Logo.avif"; 
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
                              <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
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
                    : "Images/Maket Hub Logo.avif";

                $email = decodeEmail($owner['email']);
                $maskedEmail = maskEmail($email);
                $phone = decodePhone($owner['phone']);
                $maskedPhone = maskPhone($phone);
            ?>

            <tr data-status="<?= htmlspecialchars($owner['status']) ?>">
                <td><?= $count++ ?>.</td>

                <td>
                  <div class="adm-user-profile">
                    <img src="<?= htmlspecialchars($img) ?>" style="border-radius:50%">
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
                        <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
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
            <button>
              <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
            </button>

          </div>
          <div class="form-wrapper">
            <form method="POST" enctype="multipart/form-data">
              <h1>Update Agent Details</h1>
              <?php if (!empty($errors)): ?>
                <p class="errorMessage">
                  <i class="fa-solid fa-circle-exclamation"></i>
                  <?= implode("<br>", $errors); ?>
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
                  <label>Agent's Full Name</label>
                  <input type="text" name="full-name" placeholder="Full Name">
                </div>
                <div class="inp-box">
                  <label>Agent's Username</label>
                  <input type="text" name="username" placeholder="e.g blessedemmanuel254">
                </div>
                <div class="inp-box">
                  <label>Agent's Email ID</label>
                  <input type="text" name="email" placeholder="john@example.com">
                </div>
                <div class="inp-box">
                  <label>Agent's Phone</label>
                  <input type="text" name="phone" placeholder="075***630">
                </div>
                <div class="inp-box">

                  <label>Country</label>
                  <select name="country">
                    <option value=""><p>-- Select Country --</p></option>
                    <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                    <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">

                  <label>County</label>
                  <select name="county">
                    <option value=""><p>-- Select County --</p></option>
                    <option value="Kilifi" <?php echo ($county === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                    <option value="Kenya" <?php echo ($county === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($county === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div class="inp-box">
                  <label>Agent's Address</label>
                  <input type="text" name="address" placeholder="eg. Kilifi town">
                </div>
                <div class="inp-box">

                  <label>Ward</label>
                  <select name="ward">
                    <option value=""><p>-- Select Ward --</p></option>
                    <option value="Sokoni Ward" <?php echo ($ward === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                    <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div></div>
                <div class="inp-box">
                  <label class="agency_code">Agency Code (read-only)<i class="fa-solid fa-copy"></i></label>
                  <input type="text" name="agency_code" placeholder="A56D3847" disabled>
                </div>
                <div></div>
                <button type="submit">
                  Submit Details
                </button>
              </div>

            </form>
          </div>
        </div>
      </div>

    </main>
    <footer>
      <p>&copy; 2025/2026, Maket Hub.com, All Rights reserved.</p>
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
    const tables = $('#ordersTable, #salesagentsTable, #sellersTable, #buyersTable, #transactionsTable, #withdrawalsTable, #propertyownersTable')
      .DataTable(dataTableConfig);

    // Override ordersTable only
    $('#ordersTable').DataTable().page.len(10).draw(false);

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
    // Maket Hub PRODUCTS GRID SWITCH JS

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
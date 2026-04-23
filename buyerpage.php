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
$allowedRole = 'buyer'; // change to 'seller' on seller-only pages

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
    // session_destroy();

    header("Location: index.php");
    exit();
}

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

/* ---------- FETCH USER DATA ---------- */
$user_id = $_SESSION['user_id'];

$query = "
  SELECT 
    u.username,
    u.profile_image,

    ward.name   AS ward,
    county.name AS county,
    country.name AS country

  FROM users u

  LEFT JOIN locations ward 
    ON u.location_id = ward.location_id

  LEFT JOIN locations county 
    ON ward.parent_id = county.location_id

  LEFT JOIN locations region 
    ON county.parent_id = region.location_id

  LEFT JOIN locations country 
    ON region.parent_id = country.location_id

  WHERE u.user_id = ?
  LIMIT 1
";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("System error.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$username = "Not set";
$profileImage = null;
$county = "Not set";
$ward = "not set";
$country = "not set";

if ($result && $result->num_rows === 1) {
  $user = $result->fetch_assoc();

  if (!empty($user['username'])) {
      $username = $user['username'];
  }

  $profileImage = $user['profile_image'] ?? null;

  $county = !empty($user['county']) ? $user['county'] : "Not set";

  // Normalize for matching
  $ward = !empty($user['ward']) ? strtolower(trim($user['ward'])) : "";
  $country = !empty($user['country']) ? strtolower(trim($user['country'])) : "";
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

LEFT JOIN locations countyLoc 
  ON countyLoc.location_id = wardLoc.parent_id

LEFT JOIN locations regionLoc 
  ON regionLoc.location_id = countyLoc.parent_id

LEFT JOIN locations countryLoc 
  ON countryLoc.location_id = regionLoc.parent_id

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

$buyerId = $_SESSION['user_id'] ?? 0;

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

$ordersStmt->bind_param("i", $buyerId);
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
  $stmt->bind_param("i", $buyerId);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
      $pendingItems[] = $row;
  }

  $stmt->close();
}

$pendingOrders = count($pendingItems);

// Example: display pending items
/* foreach ($pendingItems as $item) {
  echo "Product: " . htmlspecialchars($item['product_name']) . "<br>";
  echo "Quantity: " . $item['quantity'] . "<br>";
  echo "Subtotal: " . number_format($item['subtotal'], 2) . "<br>";
  echo "<img src='" . (!empty($item['product_image']) && file_exists(__DIR__ . '/' . $item['product_image']) ? $item['product_image'] : 'Images/Makethub Logo.png') . "' width='80'><br><hr>";
} */
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

  <title>Buyer Page | Makethub</title>
</head>
<body>
  <div class="container">
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

        <a class="lkOdr" onclick="toggleOrderMarket()">
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
          <select id="county">
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
    <div class="overlay" onclick="toggleWhatsAppChat()" id="overlay"></div>
    <div id="whatsapp-button" onclick="toggleWhatsAppChat()">
      <img src="Images/Makethub WhatsApp Icon.avif" width="45" alt="Chat with us on WhatsApp">
    </div>

    <div id="whatsapp-chat-box">
      <div class="chat-header">
        <div class="top">
          <img src="Images/Makethub Logo.png" alt="Makethub Logo" width="35">
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
          <textarea id="userMessage" placeholder="Type a message..."></textarea>
          <img src="Images/Send-35.png" alt="Send Icon" width="45" onclick="sendWhatsAppMessage()">
        </div>
      </div>
    </div>

    <main class="buyerMain" id="marketMain">

      <div class="chat-wrapper">

        <!-- HEADER -->
        <div class="chat-header">
          <h3>Order Chat • Seller: Alex</h3>
          <div class="meta">Order Code: ORD-20260421-71795</div>
          <div class="status" id="orderStatus">Order in progress...</div>
        </div>

        <!-- CHAT BODY -->
        <div class="chat-body" id="chatBody">

          <!-- Seller Message -->
          <div class="chat-message seller">
            <div class="bubble">
              Hello 👋 I’m ready to deliver your product.
              <span class="time">10:32 AM</span>
            </div>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="chat-footer" id="chatFooter">
          
          <div class="chat-input">
            <textarea id="chatInput" placeholder="Type a message..."></textarea>
            <img src="Images/Send-35.png" alt="Send Icon" width="45" onclick="sendMessage()">
          </div>

          <div class="chat-actions">
            <button class="location-btn" onclick="shareLocation()">📍 Share Location</button>
            <button class="complete-btn" onclick="completeOrder()">✔ I have Received Order</button>
          </div>

        </div>

      </div>
      <div class="locationModal" id="locationModal" style="display:none; position:fixed; bottom:0; background:lightblue; width:100%; padding:15px;">
        <h4>Describe your location</h4>
        <input type="text" id="manualLocation" placeholder="e.g. Blue gate, near church" style="width:100%; padding:10px;">
        <button onclick="confirmLocation()">Confirm Location</button>
      </div>
      <div class="tabs-container strongRed" id="toggleMarketTypeTab" data-tab-storage="marketTypeTabs">
        <div class="tabs">
          <button class="tab-btn" data-tab="products">Products</button>
          <button class="tab-btn" data-tab="services">Services</button><!-- 
          <button class="tab-btn" data-tab="rentals">Rentals</button> -->
        </div>

        <div class="tab-content">
          <div id="products" class="tab-panel">
            <p>Quality goods from trusted vendors. <br><strong>Please select Market type <i class="fa-regular fa-circle-check"></i></strong></p>

            <div class="cards">
              <!-- LOCAL -->
              <a class="card" onclick="openMarketSource('shopsL'); return false;">
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
              <a class="card" onclick="openMarketSource('shopsN'); return false;">
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
              <a class="card" onclick="openMarketSource('shopsG'); return false;">
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

          <div id="services" class="tab-panel">
            <p>Professional services delivered with reliability.<br><strong>Please select Market type <i class="fa-regular fa-circle-check"></i></strong></p>

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

          <div id="rentals" class="tab-panel">
            <p>Affordable rentals for homes, vehicles and equipment.<br><strong>Please select Market type <i class="fa-regular fa-circle-check"></i></strong></p>

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

              <button onclick="goBackToMarketTypes()">
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

      <h1>Recent Orders</h1>

      <div class="filter-bar">
        <select id="statusFilter">
          <option value="all">All Orders</option>
          <option value="Delivered">Delivered</option>
          <option value="Shipped">Shipped</option>
          <option value="Pending">Processing</option>
        </select>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="table-wrapper">
        <table id="ordersTable">
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
            <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
            <tr data-status="<?= htmlspecialchars($order['order_status']) ?>">

              <td>
                <img src="<?= !empty($order['image_path']) && file_exists(__DIR__ . '/' . $order['image_path']) 
                    ? htmlspecialchars($order['image_path']) 
                    : 'Images/Makethub Logo.png'; ?>" 
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
            <?php else: ?>
              <tr>
                <td colspan="12" style="text-align:center; color :#898888">No data available in table</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- MOBILE CARDS -->
      <div class="cards" id="orderCards">
        <?php foreach ($orders as $order): ?>
          <?php
            $image = (!empty($order['image_path']) && file_exists(__DIR__ . '/' . $order['image_path']))
                ? $order['image_path']
                : "Images/Makethub Logo.png";

            $paymentClass = strtolower($order['payment_status']);
            $paymentText  = ucwords($order['payment_status']);

            $statusClass = strtolower($order['order_status']);
            $statusText  = ucwords($order['order_status']);
          ?>

          <div class="order-card" data-status="<?= htmlspecialchars($order['order_status']) ?>">

            <div class="card-header">
              <img src="<?= htmlspecialchars($image) ?>" class="product-img">

              <div>
                <div class="card-title"><?= htmlspecialchars($order['product_name']) ?></div>
                <div class="card-meta">Order: <?= htmlspecialchars($order['order_code']) ?></div>
                <div class="card-meta">Quantity: <?= $order['quantity'] ?></div>
              </div>
            </div>

            <div class="card-row">
              <span>Subtotal</span>
              <strong>KES <?= number_format($order['subtotal'], 2) ?></strong>
            </div>

            <div class="card-row">
              <span>Payment</span>
              <span class="badge <?= $paymentClass ?>"><?= $paymentText ?></span>
            </div>

            <div class="card-row">
              <span>Status</span>
              <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
            </div>

            <div class="card-row">
              <span>Shipped</span>
              <span><?= !empty($order['shipped_at']) ? date("d M Y", strtotime($order['shipped_at'])) : '-' ?></span>
            </div>

            <div class="card-row">
              <span>Delivered</span>
              <span><?= !empty($order['delivered_at']) ? date("d M Y", strtotime($order['delivered_at'])) : '-' ?></span>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
      
      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleOrderMarket()">View&nbsp;All&nbsp;Orders</button> to access all your orders.</p>

    </main>

    <main class="buyerMain" id="orderMain">
      <div class="tab-top">
        <p>Track your purchases<br><strong>View order and delivery status <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleOrderMarket()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>      
      <div class="filter-bar">
        <select id="statusFilter">
          <option value="all">All Orders</option>
          <option value="delivered">Delivered</option>
          <option value="shipped">Shipped</option>
          <option value="pending">Processing</option>
        </select>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="table-wrapper buyerOrdersTrack">
        <table id="buyerOrdersTable">
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
                    : 'Images/Makethub Logo.png'; ?>" 
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

<!--       <div class="order-group">
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
              <img src="Images/Makethub Logo.png" alt="Product">
            </div>

            <div class="item-actions">
              <button class="toggle" data-target="d1">View details</button>
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
              <img src="Images/Makethub Logo.png" alt="Product">
            </div>

            <div class="item-actions">
              <button class="toggle" data-target="d2">View details</button>
            </div>

            <div class="item-extra" id="d2">
              <div class="extra-box">
                Awaiting dispatch
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
              <img src="Images/Makethub Logo.png" alt="Product">
            </div>

            <div class="item-actions">
              <button class="toggle" data-target="d2">View details</button>
            </div>

            <div class="item-extra" id="d2">
              <div class="extra-box">
                Awaiting dispatch
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
              <img src="Images/Makethub Logo.png" alt="Product">
            </div>

            <div class="item-actions">
              <button class="toggle" data-target="d2">View details</button>
            </div>

            <div class="item-extra" id="d2">
              <div class="extra-box">
                Awaiting dispatch
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
              <img src="Images/Makethub Logo.png" alt="Product">
            </div>

            <div class="item-actions">
              <button class="toggle" data-target="d2">View details</button>
            </div>

            <div class="item-extra" id="d2">
              <div class="extra-box">
                Awaiting dispatch
              </div>
            </div>
          </div>

        </div>
      </div> -->

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleOrderMarket()">Go&nbsp;back</button> to continue shopping.</p>
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
    // DataTables Script Js
    $(document).ready(function () {
      $('#buyerOrdersTable').DataTable({
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

  <script>
    const chatBody = document.getElementById("chatBody");
    const chatInput = document.getElementById("chatInput");
    const orderStatus = document.getElementById("orderStatus");
    const chatFooter = document.getElementById("chatFooter");

    /* SEND MESSAGE */
    function sendMessage() {
      const input = document.getElementById("chatInput");
      if (!input.value.trim()) return;

      const messageWrapper = document.createElement("div");
      messageWrapper.className = "chat-message buyer";

      const bubble = document.createElement("div");
      bubble.className = "bubble";

      const time = new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });

      bubble.innerHTML = `
        ${input.value}
        <span class="time">${time}</span>
      `;

      messageWrapper.appendChild(bubble);
      document.getElementById("chatBody").appendChild(messageWrapper);

      input.value = "";
      chatBody.scrollTop = chatBody.scrollHeight;
    }

    let currentCoords = null;

    function shareLocation() {
      navigator.geolocation.getCurrentPosition(async (pos) => {
        currentCoords = {
          lat: pos.coords.latitude,
          lng: pos.coords.longitude,
          accuracy: pos.coords.accuracy
        };

        // Show manual input modal
        document.getElementById("locationModal").style.display = "block";
      });
    }

    async function confirmLocation() {
      const manualText = document.getElementById("manualLocation").value;

      const { lat, lng, accuracy } = currentCoords;

      const apiKey = "YOUR_GOOGLE_API_KEY";

      let address = "";

      try {
        const res = await fetch(
          `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`
        );
        const data = await res.json();
        address = data.results[0]?.formatted_address || "Unknown location";
      } catch {
        address = "Address unavailable";
      }

      const mapEmbed = `
        <iframe 
          width="100%" 
          height="150" 
          style="border-radius:10px"
          src="https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed">
        </iframe>
      `;

      const time = new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });

      const messageWrapper = document.createElement("div");
      messageWrapper.className = "chat-message buyer";

      const bubble = document.createElement("div");
      bubble.className = "bubble";

      bubble.innerHTML = `
        📍 <strong>Delivery Location</strong><br>
        ${address}<br>
        📝 ${manualText}<br><br>
        ${mapEmbed}
        <small>Accuracy: ±${Math.round(accuracy)}m</small>
        <span class="time">${time}</span>
      `;

      messageWrapper.appendChild(bubble);
      chatBody.appendChild(messageWrapper);

      document.getElementById("locationModal").style.display = "none";

      // Send to backend
      sendLocationToServer(lat, lng, address, manualText);

      // Start live tracking
      startLiveTracking();
    }

    let trackingInterval;

    function startLiveTracking() {
      trackingInterval = setInterval(() => {
        navigator.geolocation.getCurrentPosition((pos) => {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;

          fetch("update_location.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ lat, lng })
          });
        });
      }, 5000); // every 5 seconds
    }

    /* COMPLETE ORDER */
    function completeOrder() {
      if (!confirm("This will mark the order as completed and close the chat. Continue?")) return;

      orderStatus.textContent = "Order Completed • Chat Closed";
      orderStatus.style.color = "#ffb703";

      const systemMsg = document.createElement("div");
      systemMsg.className = "message system";
      systemMsg.textContent =
        "✅ Order marked as completed. Chat has been closed!";
      chatBody.appendChild(systemMsg);

      chatFooter.classList.add("locked");
    }
  </script>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>

</body>
</html>
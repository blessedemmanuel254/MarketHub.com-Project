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


/* ---------- HANDLE FOLLOW / UNFOLLOW AJAX ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_follow') {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit();
    }

    $followerId = $_SESSION['user_id'];
    $followedId = (int)($_POST['seller_id'] ?? 0);

    if ($followedId <= 0 || $followedId === $followerId) {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit();
    }

    /* Check if already following */
    $check = $conn->prepare(
        "SELECT 1 FROM user_followers WHERE follower_id = ? AND followed_id = ?"
    );
    $check->bind_param("ii", $followerId, $followedId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // UNFOLLOW
        $delete = $conn->prepare(
            "DELETE FROM user_followers WHERE follower_id = ? AND followed_id = ?"
        );
        $delete->bind_param("ii", $followerId, $followedId);
        $delete->execute();
        $delete->close();

        $isFollowing = false;
    } else {
        // FOLLOW
        $insert = $conn->prepare(
            "INSERT INTO user_followers (follower_id, followed_id) VALUES (?, ?)"
        );
        $insert->bind_param("ii", $followerId, $followedId);
        $insert->execute();
        $insert->close();

        $isFollowing = true;
    }
    $check->close();

    /* Updated followers count */
    $countStmt = $conn->prepare(
        "SELECT COUNT(*) FROM user_followers WHERE followed_id = ?"
    );
    $countStmt->bind_param("i", $followedId);
    $countStmt->execute();
    $countStmt->bind_result($followersCount);
    $countStmt->fetch();
    $countStmt->close();

    echo json_encode([
        'success' => true,
        'is_following' => $isFollowing,
        'followers' => $followersCount
    ]);
    exit();
}

/* ---------- HANDLE CART AJAX ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

  if (!isset($_SESSION['user_id'])) {
      echo json_encode(['success' => false, 'error' => 'Not logged in']);
      exit();
  }

  $userId = $_SESSION['user_id'];
  $action = $_POST['action'];

  /* ================= ADD TO CART ================= */
  if ($action === 'add_to_cart') {

      $productId = (int)($_POST['product_id'] ?? 0);

      if ($productId <= 0) {
          echo json_encode(['success' => false]);
          exit();
      }

      // Check if already in cart
      $check = $conn->prepare("
          SELECT quantity FROM user_cart
          WHERE user_id = ? AND product_id = ?
      ");
      $check->bind_param("ii", $userId, $productId);
      $check->execute();
      $check->bind_result($qty);

      if ($check->fetch()) {
          $check->close();

          $update = $conn->prepare("
              UPDATE user_cart
              SET quantity = quantity + 1
              WHERE user_id = ? AND product_id = ?
          ");
          $update->bind_param("ii", $userId, $productId);
          $update->execute();
          $update->close();

      } else {
          $check->close();

          $insert = $conn->prepare("
              INSERT INTO user_cart (user_id, product_id, quantity)
              VALUES (?, ?, 1)
          ");
          $insert->bind_param("ii", $userId, $productId);
          $insert->execute();
          $insert->close();
      }

      echo json_encode(['success' => true]);
      exit();
  }

  /* ================= FETCH CART ================= */
  if ($action === 'fetch_cart') {

      $stmt = $conn->prepare("
          SELECT 
              uc.product_id,
              uc.quantity,
              p.product_name,
              p.price,
              p.image_path
          FROM user_cart uc
          JOIN productservicesrentals p 
              ON uc.product_id = p.product_id
          WHERE uc.user_id = ?
      ");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $result = $stmt->get_result();

      $items = [];
      while ($row = $result->fetch_assoc()) {
          $items[] = $row;
      }

      echo json_encode(['success' => true, 'items' => $items]);
      exit();
  }

  /* ================= REMOVE ================= */
  if ($action === 'remove_from_cart') {

      $productId = (int)($_POST['product_id'] ?? 0);

      $stmt = $conn->prepare("
          DELETE FROM user_cart
          WHERE user_id = ? AND product_id = ?
      ");
      $stmt->bind_param("ii", $userId, $productId);
      $stmt->execute();
      $stmt->close();

      echo json_encode(['success' => true]);
      exit();
  }

  /* ================= UPDATE QUANTITY ================= */
  if ($action === 'update_quantity') {

      $productId = (int)($_POST['product_id'] ?? 0);
      $quantity  = (int)($_POST['quantity'] ?? 1);

      if ($quantity <= 0) $quantity = 1;

      $stmt = $conn->prepare("
          UPDATE user_cart
          SET quantity = ?
          WHERE user_id = ? AND product_id = ?
      ");
      $stmt->bind_param("iii", $quantity, $userId, $productId);
      $stmt->execute();
      $stmt->close();

      echo json_encode(['success' => true]);
      exit();
  }
}

if (!isset($_GET['seller']) || !is_numeric($_GET['seller'])) {
    die("Invalid seller");
}

$buyerId  = $_SESSION['user_id'];
$sellerId = (int)$_GET['seller'];

/* ---------- FETCH SELLER PROFILE ---------- */
$sellerStmt = $conn->prepare("
    SELECT 
      user_id,
      business_name,
      business_type,
      market_scope,
      address,
      ward,
      profile_image,
      total_sales,
      rating_average,
      rating_count
    FROM users
    WHERE user_id = ? AND account_type = 'seller'
    LIMIT 1
");
$sellerStmt->bind_param("i", $sellerId);
$sellerStmt->execute();
$seller = $sellerStmt->get_result()->fetch_assoc();
$sellerStmt->close();

if (!$seller) {
    die("Seller not found");
}

/* ---------- FOLLOWERS COUNT ---------- */
$followersStmt = $conn->prepare(
    "SELECT COUNT(*) FROM user_followers WHERE followed_id = ?"
);
$followersStmt->bind_param("i", $sellerId);
$followersStmt->execute();
$followersStmt->bind_result($followersCount);
$followersStmt->fetch();
$followersStmt->close();

/* ---------- FOLLOWING COUNT ---------- */
$followingStmt = $conn->prepare(
    "SELECT COUNT(*) FROM user_followers WHERE follower_id = ?"
);
$followingStmt->bind_param("i", $sellerId);
$followingStmt->execute();
$followingStmt->bind_result($followingCount);
$followingStmt->fetch();
$followingStmt->close();

/* ---------- IS BUYER FOLLOWING SELLER ---------- */
$isFollowing = false;
$checkFollow = $conn->prepare(
    "SELECT 1 FROM user_followers WHERE follower_id = ? AND followed_id = ?"
);
$checkFollow->bind_param("ii", $buyerId, $sellerId);
$checkFollow->execute();
$checkFollow->store_result();
$isFollowing = $checkFollow->num_rows > 0;
$checkFollow->close();

/* ---------- FETCH SELLER PRODUCTS ---------- */


$productsByCategory = [];

$productStmt = $conn->prepare("
    SELECT 
        product_id,
        product_name,
        category,
        stock_quantity,
        price,
        image_path
    FROM productservicesrentals
    WHERE user_id = ? AND status = 'active'
    ORDER BY created_at DESC
");
$productStmt->bind_param("i", $sellerId);
$productStmt->execute();
$result = $productStmt->get_result();

while ($row = $result->fetch_assoc()) {
    $category = $row['category'] ?? 'Uncategorized';
    $productsByCategory[$category][] = $row;
}

function formatProductName($name) {
  return ucwords(strtolower($name));
}

$productStmt->close();
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
        <h1>My&nbsp;Cart</h1>
        <i class="fa-solid fa-xmark" onclick="toggleCartBar()"></i>
      </div>
      <div class="inner-cart-container">
        <div class="cart-items" id="cartItems">
        </div>
        <div id="emptyCartMessage" class="empty-cart">
          🛒 Your cart is empty
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
    <div class="cartOverlay" onclick="toggleCartBar()" id="cartOverlay"></div>
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
        <div class="seller mDisplay">
          <div class="seller-left">
            <div class="avatar">
              <?php echo strtoupper(substr($seller['business_name'], 0, 2)); ?>
            </div><!-- 
            <img src="" alt="Seller Logo"> -->
            <div>
              <div class="name">
                <?php echo htmlspecialchars(ucwords(strtolower($seller['business_name']))); ?>
              </div>

              <div class="rating">
                ★★★★★ (<?php echo (int)$seller['rating_count']; ?>)
              </div>

              <div class="meta">
                <h2 class="following-count" data-seller="<?php echo $sellerId; ?>">
                  <?php echo $followingCount; ?>&nbsp;<span>following</span>
                </h2>
                <h2 
                  class="<?php echo $isFollowing  ? 'followingBtn' : 'followBtn'; ?>" 
                  data-seller="<?php echo $seller['user_id']; ?>"
                >
                  <?php echo $isFollowing ? 'Following' : 'Follow'; ?>
                </h2>
              </div>

              <div class="meta">
                <h2 class="followers-count" data-seller="<?php echo $sellerId; ?>">
                  <?php echo $followersCount; ?>&nbsp;<span>followers</span>
                </h2>
              </div>

              <div class="bsInfo">
                <strong>Location :</strong>
                <?php echo htmlspecialchars(ucwords(strtolower($seller['address']))); ?>
              </div>
            </div>
          </div>
            <a href="" class="seller-right">
            <?php
            $totalSales = (int)$seller['total_sales'];

            if ($totalSales < 100) {
                $badgeClass = 'promoBadgeDefault';
            } elseif ($totalSales >= 100 && $totalSales < 200) {
                $badgeClass = 'promoBadgeGoGold';
            } else { // >= 200
                $badgeClass = 'promoBadgeGoPro';
            }
            ?>

            <div class="<?php echo $badgeClass; ?>">
                <?php echo $totalSales; ?>+
            </div>

            <div class="bsType">
              Business Type :
              <i><?php echo ucwords(strtolower($seller['business_type'])); ?></i>
            </div>

            <div class="action">
              <h2><?php echo strtoupper($seller['market_scope']); ?> MARKET</h2>
            </div>
          </a>
        </div>
      </div>
      <div class="tabs-container">
        <?php if (empty($productsByCategory)): ?>
            <p>No products available.</p>
        <?php endif; ?>
        <div class="tabs">
          <?php 
          $first = true;
          foreach ($productsByCategory as $category => $items): 
              $safeId = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($category));
          ?>
              <button 
                  class="tab-btn <?= $first ? 'active' : '' ?>" 
                  data-tab="<?= $safeId ?>"
              >
                  <?= htmlspecialchars($category) ?>
              </button>
          <?php 
              $first = false;
          endforeach; 
          ?>
        </div>
        <div class="tab-content">
          <div class="tab-top">
            <p>You order we deliver.</p>
          </div>
        <?php 
        $first = true;
        foreach ($productsByCategory as $category => $items): 
            $safeId = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($category));
        ?>
            <div id="<?= $safeId ?>" class="tab-panel <?= $first ? 'active' : '' ?>">
                
                <div class="variables-grid">
                    <?php foreach ($items as $product): ?>
                        <div class="variable-card"
                            data-id="<?= $product['product_id']; ?>"
                            data-name="<?= htmlspecialchars($product['product_name']); ?>"
                            data-price="<?= $product['price']; ?>"
                            data-image="<?= $product['image_path']; ?>">

                            <button class="add-to-cart-btn">Add&nbsp;to&nbsp;cart</button>

                            <img class="variableAndSnacksImage"
                                src="<?= htmlspecialchars($product['image_path']); ?>"
                                alt="Product Image">

                            <div class="variable-content">
                              <div class="variable-title">
                                <?= htmlspecialchars(formatProductName($product['product_name'])); ?>
                              </div>

                              <div class="stock 
                                <?= ($product['stock_quantity'] > 5) ? 'in-stock' : 
                                    (($product['stock_quantity'] > 0) ? 'low-stock' : 'out-stock') ?>">
                                <?= ($product['stock_quantity'] > 0) 
                                    ? "In stock ({$product['stock_quantity']})" 
                                    : "Out of stock" ?>
                              </div>

                              <div class="price-row">
                                  <div class="price">
                                      KES <?= number_format($product['price'], 2); ?>
                                  </div>
                                  <button class="buy-btn" onclick="togglePaymentOption()">
                                      Order
                                  </button>
                              </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        <?php 
            $first = false;
        endforeach; 
        ?>
        </div>
      </div>
    </main>
  
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript" defer></script>

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
  <script>
  document.addEventListener("DOMContentLoaded", () => {
      document.addEventListener("click", (e) => {
          // Handle clicks on both followBtn and followingBtn
          const button = e.target.closest(".followBtn, .followingBtn");
          if (!button) return;

          e.preventDefault();

          const sellerId = button.dataset.seller;
          if (!sellerId) return;

          // Disable button briefly to prevent double-click
          button.style.pointerEvents = "none";

          fetch("marketDisplay.php", {
              method: "POST",
              headers: {
                  "Content-Type": "application/x-www-form-urlencoded",
                  "X-Requested-With": "XMLHttpRequest"
              },
              body: `action=toggle_follow&seller_id=${sellerId}`
          })
          .then(res => res.json())
          .then(data => {
              if (!data.success) {
                  alert(data.error || "Action failed");
                  return;
              }

              // Toggle text
              button.textContent = data.is_following ? "Following" : "Follow";

              // Toggle class
              if (data.is_following) {
                  button.classList.remove("followBtn");
                  button.classList.add("followingBtn");
              } else {
                  button.classList.remove("followingBtn");
                  button.classList.add("followBtn");
              }

              // Update followers count
              const followersCountEl = document.querySelector(
                  `.followers-count[data-seller="${sellerId}"]`
              );

              if (followersCountEl) {
                  followersCountEl.innerHTML = `${data.followers}&nbsp;<span>followers</span>`;
              }
          })
          .catch(() => alert("Network error"))
          .finally(() => {
              // Re-enable button
              button.style.pointerEvents = "auto";
          });
      });
  });
  </script>
  
</body>
</html>
<?php
session_start();
require_once 'connection.php';

ini_set('display_errors', 0); // prevent HTML error output
error_reporting(E_ALL);
/* ---------- SESSION SECURITY ---------- */
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$userId = $_SESSION['user_id'];

/* ---------- FETCH BUYER DELIVERY DETAILS ---------- */
$stmt = $conn->prepare("
  SELECT full_name, phone, county, ward, address
  FROM users
  WHERE user_id = ?
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Format full name (First letter capital, rest small)
$formattedName = isset($user['full_name']) 
  ? ucwords(strtolower($user['full_name'])) 
  : 'Not Provided';

// Decode phone from Base64
$decodedPhone = isset($user['phone']) 
    ? base64_decode($user['phone']) 
    : 'No Phone';

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
  $action = $_POST['action'];

  $stmt = $conn->prepare("
    SELECT full_name, phone, county, ward, address
    FROM users
    WHERE user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  /* ================= PLACE ORDER ================= */
  if ($action === 'place_order') {
    // Fetch current stock before committing
    $stockStmt = $conn->prepare("SELECT stock_quantity FROM productservicesrentals WHERE product_id = ? LIMIT 1");
    $stockStmt->bind_param("i", $productId);
    $stockStmt->execute();
    $stockStmt->bind_result($currentStock);
    $stockStmt->fetch();
    $stockStmt->close();

    if ($quantity > $currentStock) {
      echo json_encode(['success' => false, 'error' => 'Not enough stock']);
      exit();
    }

    foreach ($items as $item) {
      $productId = (int)$item['product_id'];
      $quantity = (int)$item['quantity'];

      $stockStmt->bind_param("i", $productId);
      $stockStmt->execute();
      $stockStmt->bind_result($currentStock);
      $stockStmt->fetch();

      if ($quantity > $currentStock) {
          throw new Exception("Product ID {$productId} does not have enough stock.");
      }
    }
    $stockStmt->close();

    $productId = (int)($_POST['product_id'] ?? 0);
    $sellerId  = (int)($_POST['seller_id'] ?? 0);
    $quantity  = (int)($_POST['quantity'] ?? 1);

    // Fetch real price from database
    $priceStmt = $conn->prepare("
        SELECT price FROM productservicesrentals
        WHERE product_id = ? LIMIT 1
    ");
    $priceStmt->bind_param("i", $productId);
    $priceStmt->execute();
    $priceStmt->bind_result($realPrice);
    $priceStmt->fetch();
    $priceStmt->close();

    if (!$realPrice) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit();
    }

    // ✅ Validate
    if ($productId <= 0 || $sellerId <= 0 || $quantity <= 0 || $realPrice <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid order']);
        exit();
    }

    $totalAmount = $realPrice * $quantity;

    $conn->begin_transaction();

    try {
        // 1️⃣ Generate unique order code
        $dateStr = date('Ymd');
        $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $orderCode = "ORD-" . $dateStr . "-" . $randomDigits;

        // 2️⃣ Insert order
        $orderStmt = $conn->prepare("
            INSERT INTO orders (order_code, buyer_id, total_amount, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        $orderStmt->bind_param("sid", $orderCode, $userId, $totalAmount);
        $orderStmt->execute();
        $orderId = $orderStmt->insert_id;
        $orderStmt->close();

        // 3️⃣ Insert order item
        $subtotal = $realPrice * $quantity;

        $itemStmt = $conn->prepare("
          INSERT INTO order_items 
          (order_id, product_id, seller_id, quantity, price, subtotal, payment_status, order_status)
          VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending')
        ");

        $itemStmt->bind_param(
          "iiiidd",
          $orderId,
          $productId,
          $sellerId,
          $quantity,
          $price,
          $subtotal
        );

        $itemStmt->execute();
        $itemStmt->close();

        $updateStock = $conn->prepare("
            UPDATE productservicesrentals
            SET stock_quantity = stock_quantity - ?
            WHERE product_id = ? AND stock_quantity >= ?
        ");
        $updateStock->bind_param("iii", $quantity, $productId, $quantity);
        $updateStock->execute();
        $updateStock->close();

        $conn->commit();

        echo json_encode([
          'success' => true,
          'order_code' => $orderCode,
          'order_id' => $orderId // ✅ REQUIRED
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }

    exit();
  }

  if ($action === 'place_order_multi') {
    $items = json_decode($_POST['items'] ?? '[]', true);
    $totalAmount = floatval($_POST['total_amount'] ?? 0);

    if (!$items || $totalAmount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid order']);
        exit();
    }

    $conn->begin_transaction();
    try {
        // Generate unique order code for this whole order
        $dateStr = date('Ymd');
        $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $orderCode = "ORD-" . $dateStr . "-" . $randomDigits;

        // Insert order
        $orderStmt = $conn->prepare("
            INSERT INTO orders (order_code, buyer_id, total_amount, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        $orderStmt->bind_param("sid", $orderCode, $userId, $totalAmount);
        $orderStmt->execute();
        $orderId = $orderStmt->insert_id;
        $orderStmt->close();

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            // ✅ ALWAYS GET seller from DB (correct way)
            $sellerStmt = $conn->prepare("
                SELECT user_id 
                FROM productservicesrentals 
                WHERE product_id = ? 
                LIMIT 1
            ");
            $sellerStmt->bind_param("i", $productId);
            $sellerStmt->execute();
            $sellerStmt->bind_result($sellerId);
            $sellerStmt->fetch();
            $sellerStmt->close();

            if (!$sellerId || !is_numeric($sellerId) || $sellerId <= 0) {
              error_log("❌ Invalid seller_id for product {$productId}: " . var_export($sellerId, true));
              throw new Exception("Seller not found for product ID {$productId}");
            }
            $quantity  = (int)$item['quantity'];
            $price     = floatval($item['price']);

            // Fetch product name from DB
            $nameStmt = $conn->prepare("SELECT product_name FROM productservicesrentals WHERE product_id = ? LIMIT 1");
            $nameStmt->bind_param("i", $productId);
            $nameStmt->execute();
            $nameStmt->bind_result($productName);
            if (!$nameStmt->fetch()) {
                $productName = "Unknown Product";
            }
            $nameStmt->close();

            // --- Check stock for this product ---
            $stockStmt = $conn->prepare("SELECT stock_quantity FROM productservicesrentals WHERE product_id = ? LIMIT 1");
            $stockStmt->bind_param("i", $productId);
            $stockStmt->execute();
            $stockStmt->bind_result($currentStock);
            $stockStmt->fetch();
            $stockStmt->close();

            if ($quantity > $currentStock) {
              if ($currentStock <= 0) {
                throw new Exception("Oops! {$productName} is out of stock.");
              } else {
                throw new Exception("Sorry, we only have {$currentStock} of {$productName} left.");
              }
            }

            // --- Insert order item ---
            $subtotal = $price * $quantity;

            $itemStmt = $conn->prepare("
              INSERT INTO order_items 
              (order_id, product_id, seller_id, quantity, price, subtotal, payment_status, order_status)
              VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending')
            ");

            $itemStmt->bind_param(
              "iiiidd",
              $orderId,
              $productId,
              $sellerId,
              $quantity,
              $price,
              $subtotal
            );

            $itemStmt->execute();
            $itemStmt->close();

            // --- Reduce stock ---
            $updateStock = $conn->prepare("
                UPDATE productservicesrentals
                SET stock_quantity = stock_quantity - ?
                WHERE product_id = ? AND stock_quantity >= ?
            ");
            $updateStock->bind_param("iii", $quantity, $productId, $quantity);
            $updateStock->execute();
            $updateStock->close();

            // --- Remove from cart ---
            $removeStmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ? AND product_id = ?");
            $removeStmt->bind_param("ii", $userId, $productId);
            $removeStmt->execute();
            $removeStmt->close();
        }

        $conn->commit();
        echo json_encode([
          'success' => true,
          'order_code' => $orderCode,
          'order_id' => $orderId // ✅ REQUIRED
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    exit();
  }

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
              p.image_path,
              p.user_id AS seller_id,
              u.business_name
          FROM user_cart uc
          JOIN productservicesrentals p 
              ON uc.product_id = p.product_id
          JOIN users u
              ON p.user_id = u.user_id
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

  if ($action === 'checkout_cart') {

    $conn->begin_transaction();

    try {

        // Fetch cart items
        $cartStmt = $conn->prepare("
            SELECT 
            uc.product_id, 
            uc.quantity, 
            p.price, 
            p.stock_quantity,
            p.user_id AS seller_id
            FROM user_cart uc
            JOIN productservicesrentals p ON uc.product_id = p.product_id
            WHERE uc.user_id = ?
        ");
        $cartStmt->bind_param("i", $userId);
        $cartStmt->execute();
        $result = $cartStmt->get_result();

        $grouped = [];
        while ($row = $result->fetch_assoc()) {
          $grouped[$row['seller_id']][] = $row;
          if ($row['quantity'] > $row['stock_quantity']) {
            throw new Exception("Stock exceeded");
          }
        }

        foreach ($grouped as $sellerId => $items) {

            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Generate unique order code with 5 random digits for each seller
            $dateStr = date('Ymd'); // e.g., 20260303
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); // 5 random digits
            $orderCode = "ORD-" . $dateStr . "-" . $randomDigits;

            // Insert order
            $orderStmt = $conn->prepare("
                INSERT INTO orders (order_code, buyer_id, total_amount, created_at)
                VALUES (?, ?, ?, NOW())
            ");

            $orderStmt->bind_param("sid", $orderCode, $userId, $totalAmount);
            $orderStmt->execute();
            $orderId = $orderStmt->insert_id;
            $orderStmt->close();

            // Insert order items
            foreach ($items as $item) {
            $subtotal = $item['price'] * $item['quantity'];

            $itemStmt = $conn->prepare("
              INSERT INTO order_items 
              (order_id, product_id, seller_id, quantity, price, subtotal, payment_status, order_status)
              VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending')
            ");

              $itemStmt->bind_param(
                "iiiidd",
                $orderId,
                $productId,
                $sellerId,
                $quantity,
                $price,
                $subtotal
              );

              $itemStmt->execute();
              $itemStmt->close();
            }
        }

        // Clear cart
        $clear = $conn->prepare("DELETE FROM user_cart WHERE user_id = ?");
        $clear->bind_param("i", $userId);
        $clear->execute();
        $clear->close();

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false]);
    }

    exit();
  }

  /* ================= PROCESS PAYMENT ================= */
  if ($action === 'process_payment') {

    $orderId = $_POST['order_id'] ?? null;

    if (!$orderId || !is_numeric($orderId)) {
      echo json_encode([
          'success' => false,
          'error' => 'Invalid order ID received: ' . $orderId
      ]);
      exit();
    }

    $orderId = (int)$orderId;

    if ($orderId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid order']);
        exit();
    }

    $conn->begin_transaction();

    try {
        error_log("PROCESSING ORDER ID: " . $orderId);

        $itemsStmt = $conn->prepare("
            SELECT item_id, seller_id, subtotal, order_status
            FROM order_items
            WHERE order_id = ?
            FOR UPDATE
        ");
        $itemsStmt->bind_param("i", $orderId);
        $itemsStmt->execute();
        $result = $itemsStmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Order not found");
        }

        $updateItem = $conn->prepare("
            UPDATE order_items SET payment_status = 'paid' WHERE item_id = ?
        ");

        $walletCheck = $conn->prepare("
            SELECT wallet_id FROM wallets WHERE user_id = ? AND wallet_type = 'seller' LIMIT 1
        ");

        $walletInsert = $conn->prepare("
            INSERT INTO wallets (user_id, wallet_type, balance, total_transacted, created_at, updated_at)
            VALUES (?, 'seller', ?, ?, NOW(), NOW())
        ");

        $walletUpdate = $conn->prepare("
            UPDATE wallets
            SET balance = balance + ?, total_transacted = total_transacted + ?, updated_at = NOW()
            WHERE user_id = ? AND wallet_type = 'seller'
        ");

        while ($row = $result->fetch_assoc()) {

          $itemId       = (int)$row['item_id'];
          $itemSellerId = $row['seller_id'];
          $amount       = (float)$row['subtotal'];
          $status       = $row['order_status'];

          if (!$itemSellerId || !is_numeric($itemSellerId) || $itemSellerId <= 0) {
            throw new Exception("Invalid seller ID in order_items (item_id: {$itemId})");
          }

          $itemSellerId = (int)$itemSellerId;

          $userCheck = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? LIMIT 1");
          $userCheck->bind_param("i", $itemSellerId);
          $userCheck->execute();
          $userCheck->store_result();

          if ($status === 'paid') continue;

          $updateItem->bind_param("i", $itemId);
          $updateItem->execute();

          if ($userCheck->num_rows === 0) {
              throw new Exception("Seller does not exist (ID: {$itemSellerId})");
          }
          $userCheck->close();

          $walletCheck->bind_param("i", $itemSellerId);
          $walletCheck->execute();
          $walletCheck->store_result();

          if ($walletCheck->num_rows === 0) {
              $walletInsert->bind_param("idd", $itemSellerId, $amount, $amount);
              if (!$walletInsert->execute()) {
                  throw new Exception("Failed to create wallet for seller {$itemSellerId}");
              }
          } else {
              $walletUpdate->bind_param("ddi", $amount, $amount, $itemSellerId);
              $walletUpdate->execute();
          }

          error_log("Processing item {$itemId} | seller_id: {$itemSellerId} | amount: {$amount}");
        }

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => [
                'order_id' => $orderId,
                'last_error' => $conn->error ?? null
            ]
        ]);
    }

    exit();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (!isset($_GET['seller']) || !is_numeric($_GET['seller'])) {
        die("Invalid seller");
    }

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
      profile_image
    FROM users
    WHERE user_id = ? AND account_type = 'seller'
    LIMIT 1
");
$sellerStmt->bind_param("i", $sellerId);
$sellerStmt->execute();
$seller = $sellerStmt->get_result()->fetch_assoc();
$sellerStmt->close();

if (!$seller) {
  echo json_encode([
      "success" => false,
      "error" => "Seller not found"
  ]);
  exit;
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

// Fetch seller orders
$sellerOrders = [];
$stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.order_code,
        o.total_amount,
        o.created_at,
        u.username AS buyer_name,
        oi.quantity,
        oi.price,
        p.product_name
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN users u ON o.buyer_id = u.user_id
    JOIN productservicesrentals p ON oi.product_id = p.product_id
    WHERE oi.seller_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sellerOrders[] = $row;
    }
}
$stmt->close();


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

  <link rel="stylesheet" href="assets/css/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <title>Seller's shelf | Maket Hub</title>
</head>
<body>
  <div class="container">
    <section class="topSection">
      <div class="cart-wrapper" onclick="toggleCartBar()">
        <span class="cart-icon">📦</span>
        <span class="cart-count">0</span>
      </div>
      <button onclick="goBackHandler()">
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
          <h1>Cart Summary</h1>
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
            <span>Maket Hub Points</span>
            <span>KES 0</span>
          </div>

          <div class="summary-row summary-total">
            <span>Total</span>
            <span id="total">KES 0</span>
          </div>

          <button class="checkout-btn" onclick="proceedFromCart()">Proceed&nbsp;to&nbsp;Payment</button>
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
          <p>MPESA<br><span>+254759578630</span></p>
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
                ★★★★★ 5
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
              /* ---------- FETCH SELLER TOTAL DISTINCT ORDERS ---------- */
              $orderCountStmt = $conn->prepare("
                SELECT COUNT(DISTINCT oi.order_id) AS total_orders
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE oi.seller_id = ?
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              ");
              $orderCountStmt->bind_param("i", $sellerId);
              $orderCountStmt->execute();
              $orderCountStmt->bind_result($totalOrders);
              $orderCountStmt->fetch();
              $orderCountStmt->close();

              $totalOrders = (int)$totalOrders;
              if ($totalOrders < 100) {
                  $displayOrders = $totalOrders;
                  $badgeClass = 'promoBadgeDefault';

              } elseif ($totalOrders < 200) {
                  $displayOrders = "100+";
                  $badgeClass = 'promoBadgeGoGold';

              } elseif ($totalOrders < 250) {
                  $displayOrders = "200+";
                  $badgeClass = 'promoBadgeGoPro';

              } else {
                  $displayOrders = "250+";
                  $badgeClass = 'promoBadgeGoPro';
              }

            ?>
            <div class="promo-badge-container">Orders : 
              <p class="<?php echo $badgeClass; ?>">
                <?php echo $displayOrders; ?>
              </p>
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
                                <?= htmlspecialchars($product['product_name']); ?>
                              </div>

                              <div class="stock 
                                <?= ($product['stock_quantity'] > 5) ? 'in-stock' : 
                                    (($product['stock_quantity'] > 0) ? 'low-stock' : 'out-stock') ?>">
                                <?= ($product['stock_quantity'] > 0) 
                                    ? "In stock (<strong>{$product['stock_quantity']}</strong>)" 
                                    : "Out of stock" ?>
                              </div>

                              <div class="price-row">
                                  <div class="price">
                                      KES <?= number_format($product['price'], 2); ?>
                                  </div>
                                  <button 
                                    class="buy-btn"
                                    onclick="buyNow(this)"
                                    data-id="<?= $product['product_id']; ?>"
                                    data-name="<?= htmlspecialchars($product['product_name']); ?>"
                                    data-price="<?= $product['price']; ?>"
                                    data-image="<?= $product['image_path']; ?>"
                                    data-seller="<?= $sellerId; ?>"
                                    data-seller-name="<?= htmlspecialchars($seller['business_name']); ?>"
                                  >
                                    Buy&nbsp;Now
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
      <div class="order-container">

        <!-- LEFT COLUMN -->
        <div>
          <!-- Shipping -->
          <div class="card">
            <div class="card-title">
              Delivery Details
              <a href="userProfile.php" class="update-btn">
                <i class="fa-solid fa-cloud-arrow-up"></i>Update
              </a>
            </div>

            <div class="address-name">
              <?= htmlspecialchars($formattedName) ?>:
            </div>

            <div class="address-text">
              From <?= htmlspecialchars($user['address'] ?? '') ?>, <?= htmlspecialchars($user['ward'] ?? '') ?> ward in <?= htmlspecialchars($user['county'] ?? '') ?><br>
              Contact: <?= htmlspecialchars($decodedPhone) ?>
            </div>
          </div>                  

          <br>
          
          <br>

          <!-- PRODUCTS BY SELLER -->
          <div class="card">
          </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div>
          <div class="card">
            <div class="card-title">Payment Summary</div>

            <div class="summary-row">
              <span>Items Total</span>
              <span id="itemsTotal">KSh 0.00</span>
            </div>

            <div class="summary-row">
              <span>Delivery Fees</span>
              <span>0</span>
            </div>

            <div class="summary-row">
              <span>Promotions</span>
              <span>0</span>
            </div>

            <div class="summary-row">
              <span>Maket Hub Points</span>
              <span>0</span>
            </div>

            <div class="summary-row total">
              <span>Total</span>
              <span id="finalTotal">KSh 0.00</span>
            </div>

            <button id="payButton" class="place-order" onclick="placeOrder()">
              Pay KES 0.00
            </button>
          </div>
        </div>

      </div>
    </main>
  
    <footer>
      <p>&copy; 2025/2026, Maket Hub.shop, All Rights reserved.</p>
    </footer>
  </div>
  
  <!-- Notification container -->
  <div id="notification-container"></div>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>

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
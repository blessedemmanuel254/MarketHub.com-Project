<?php
session_start();
require_once 'connection.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
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
$allowedRole = 'seller';

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

/* ---------- FETCH USER DATA ---------- */
$user_id = $_SESSION['user_id'];

function formatDate($date) {
  if (empty($date)) return '-';

  $timestamp = strtotime($date);
  $oneYear = 31536000;

  if (time() - $timestamp < $oneYear) {
      return date("d M, H:i", $timestamp);
  } else {
      return date("d M Y", $timestamp);
  }
}

/* =========================================================
  CORRECT IMAGE ORIENTATION
  ========================================================= */

function fixImageOrientation($image, $fileTmp, $mime)
{
  /*
    * EXIF orientation is normally available for JPEG images.
    * PNG and WebP generally don't use JPEG-style EXIF
    * orientation in the same way.
    */
  if ($mime !== 'image/jpeg') {
    return $image;
  }

  if (!function_exists('exif_read_data')) {
    return $image;
  }

  $exif = @exif_read_data($fileTmp);

  if (!$exif || empty($exif['Orientation'])) {
    return $image;
  }

  $orientation = (int)$exif['Orientation'];

  switch ($orientation) {

      /* Normal */
      case 1:
        break;

      /* Flip horizontally */
      case 2:
          imageflip($image, IMG_FLIP_HORIZONTAL);
          break;

      /* Rotate 180° */
      case 3:
          $image = imagerotate($image, 180, 0);
          break;

      /* Flip vertically */
      case 4:
          imageflip($image, IMG_FLIP_VERTICAL);
          break;

      /* Flip horizontally + rotate 90° CCW */
      case 5:
          imageflip($image, IMG_FLIP_HORIZONTAL);
          $image = imagerotate($image, 90, 0);
          break;

      /* Rotate 90° CW */
      case 6:
          $image = imagerotate($image, -90, 0);
          break;

      /* Flip horizontally + rotate 90° CW */
      case 7:
          imageflip($image, IMG_FLIP_HORIZONTAL);
          $image = imagerotate($image, -90, 0);
          break;

      /* Rotate 90° CCW */
      case 8:
          $image = imagerotate($image, 90, 0);
          break;
  }

  return $image;
}

function formatToK($number) {

  if ($number >= 9950) {
    $k = $number / 1000;

    // round to nearest 0.1
    $k = round($k, 1);

    // remove .0
    if (floor($k) == $k) {
        return $k . "k";
    }

    return $k . "k";
  }

  return number_format($number);
}

function smartTitleCase(string $text): string
{
  // Normalize spacing & lowercase
  $text = strtolower(trim(preg_replace('/\s+/', ' ', $text)));

  // Capitalize words & hyphenated parts
  $text = preg_replace_callback('/\b[\w-]+\b/u', function ($match) {
      return implode('-', array_map(function ($part) {
          // Keep acronyms uppercase
          if (strlen($part) <= 3 && ctype_alpha($part)) {
              return strtoupper($part);
          }

          // Handle special brand casing
          $special = [
              'iphone' => 'iPhone',
              'ipad'   => 'iPad',
              'ipod'   => 'iPod',
              'macbook'=> 'MacBook',
              'airpods'=> 'AirPods',
              'ebay'   => 'eBay',
              'wifi'   => 'Wi-Fi'
          ];

          if (isset($special[$part])) {
              return $special[$part];
          }

          return ucfirst($part);
      }, explode('-', $match[0])));
  }, $text);

  return $text;
}

function generateImageDHash($filePath)
{
    $size = 8;

    $img = imagecreatefromstring(file_get_contents($filePath));

    $resized = imagecreatetruecolor($size + 1, $size);

    imagecopyresampled(
        $resized,
        $img,
        0,0,0,0,
        $size + 1,
        $size,
        imagesx($img),
        imagesy($img)
    );

    $hash = '';

    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {

            $left  = imagecolorat($resized, $x, $y);
            $right = imagecolorat($resized, $x+1, $y);

            $hash .= ($left > $right) ? '1' : '0';
        }
    }

    imagedestroy($img);
    imagedestroy($resized);

    return $hash;
}

// Fetch seller's data including county
$query = "
  SELECT 
    u.username,
    u.business_name,
    u.profile_image,
    county.name AS county_name
  FROM users u
  LEFT JOIN locations ward ON u.location_id = ward.location_id
  LEFT JOIN locations county ON ward.parent_id = county.location_id
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
$businessName = "Not set";
$profileImage = null;
$county = "Not Set"; // default

if ($result && $result->num_rows === 1) {
  $user = $result->fetch_assoc();

  if (!empty($user['username'])) {
    $username = $user['username'];
  }
  if (!empty($user['business_name'])) {
    $businessName = $user['business_name'];
  }
  $profileImage = $user['profile_image'] ?? null;
  $county = $user['county_name'] ?? $county; // use DB value if exists
}

$stmt->close();

/* ---------- SELLER QR LINK ---------- */
$shopUrl = "https://makethub.shop/marketDisplay.php?seller=" . $user_id;

/* ---------- QR IMAGE SOURCE ---------- */
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($shopUrl);

function centerText($image, $size, $angle, $y, $color, $font, $text) {
  $box = imagettfbbox($size, $angle, $font, $text);
  $textWidth = $box[2] - $box[0];
  $x = (imagesx($image) - $textWidth) / 2;
  imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
}

function drawGoldGradientText($image, $size, $angle, $x, $y, $text, $font)
{
  // Gold gradient shades (dark → bright)
  $colors = [
    imagecolorallocate($image, 184, 134, 11),  // dark gold
    imagecolorallocate($image, 218, 165, 32),  // goldenrod
    imagecolorallocate($image, 255, 193, 7),   // your gold
    imagecolorallocate($image, 255, 215, 0),   // bright gold
  ];

  // slight offsets to simulate glow + gradient depth
  $offsets = [
    [-1, 0],
    [0, -1],
    [1, 0],
    [0, 1],
  ];

  foreach ($colors as $index => $color) {
    $ox = $offsets[$index % count($offsets)][0];
    $oy = $offsets[$index % count($offsets)][1];

    imagettftext($image, $size, $angle, $x + $ox, $y + $oy, $color, $font, $text);
  }
}

/* ---------- QR STICKER DOWNLOAD ---------- */
if (isset($_GET['download_qr'])) {

  while (ob_get_level()) {
    ob_end_clean();
  }
  header("Content-Type: image/png");
  header("Content-Disposition: attachment; filename=makethub_qr_" . $user_id . ".png");

  /* ✅ FIX 1: MATCH CANVAS SIZE */
  $width = 991;
  $height = 1236;

  $image = imagecreatetruecolor($width, $height);

  // Colors
  $black = imagecolorallocate($image, 0, 0, 0);
  $white = imagecolorallocate($image, 255, 255, 255);
  $gold  = imagecolorallocate($image, 255, 193, 7);

  imagefill($image, 0, 0, $black);

  /* ---------- LOAD QR ---------- */
  $qrData = file_get_contents($qrImageUrl);
  if ($qrData === false) die("Failed to load QR code.");

  $qr = imagecreatefromstring($qrData);
  if ($qr === false) die("Invalid QR image data.");

  /* ✅ FIX 2: CREATE THICK WHITE FRAME LIKE IMAGE 1 */
  $qrBoxSize = 560;
  $qrInnerSize = 440;
  $qrPadding = ($qrBoxSize - $qrInnerSize) / 2;

  $qrBox = imagecreatetruecolor($qrBoxSize, $qrBoxSize);
  imagefill($qrBox, 0, 0, $white);

  imagecopyresampled(
    $qrBox,
    $qr,
    $qrPadding,
    $qrPadding,
    0,
    0,
    $qrInnerSize,
    $qrInnerSize,
    imagesx($qr),
    imagesy($qr)
  );

  /* ✅ FIX 3: PERFECT CENTER + LOWER POSITION */
  $qrX = ($width - $qrBoxSize) / 2;
  $qrY = 320;

  imagecopy($image, $qrBox, $qrX, $qrY, 0, 0, $qrBoxSize, $qrBoxSize);

  /* ---------- FONTS ---------- */
  $fontBold = __DIR__ . '/fonts/impact.ttf';
  $fontScript = __DIR__ . '/fonts/GreatVibes-Regular.ttf';
  $fontShop = __DIR__ . '/fonts/calibri.ttf';

  if (!file_exists($fontBold) || !file_exists($fontScript) || !file_exists($fontShop)) {
    die("Font file missing.");
  }

  /* ✅ FIX 4: CENTER ALL TEXT */

  centerText($image, 60, 0, 140, $white, $fontBold, "FOLLOW MY SHOP");
  centerText($image, 80, 0, 250, $gold, $fontScript, "Scan Me:");

  /* ---------- LOGO + SHOP NAME (CENTERED + ALIGN-ITEMS CENTER) ---------- */

  $shopName = strtoupper($businessName);
  $fontSize = 45;

  // Measure text box
  $box = imagettfbbox($fontSize, 0, $fontShop, $shopName);
  $textWidth  = $box[2] - $box[0];
  $textHeight = $box[1] - $box[7]; // correct height

  // Logo setup
  $logoPath = __DIR__ . '/Images/Makethub Logo.png';
  $logoSize = 90;
  $gap = 20;

  // Total width (for horizontal centering)
  $totalWidth = $logoSize + $gap + $textWidth;
  $startX = ($width - $totalWidth) / 2;

  // ---- ALIGN-ITEMS CENTER LOGIC ----
  $centerY = $qrY + $qrBoxSize + 80; // vertical center line

  // Logo position (centered vertically)
  $logoX = $startX;
  $logoY = $centerY - ($logoSize / 2);

  // Text position (baseline adjusted to center)
  $textX = $startX + $logoSize + $gap;
  $textY = $centerY + ($textHeight / 2);

  // Draw logo
  if (file_exists($logoPath)) {
      $logoData = file_get_contents($logoPath);
      $logo = @imagecreatefromstring($logoData);

      if ($logo !== false) {
        imagecopyresampled(
          $image,
          $logo,
          $logoX,
          $logoY,
          0,
          0,
          $logoSize,
          $logoSize,
          imagesx($logo),
          imagesy($logo)
        );
        imagedestroy($logo);
      }
  }

  // Draw text
  imagettftext($image, $fontSize, 0, $textX, $textY, $white, $fontShop, $shopName);

  /* ---------- OUTPUT ---------- */
  imagepng($image);
  imagedestroy($image);
  imagedestroy($qr);
  imagedestroy($qrBox);
  exit();
}

/* =========================================================
   POS CHECKOUT
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'checkout_sale'
) {

    /*
     * Return JSON because the request is sent
     * from general.js.
     */
    header('Content-Type: application/json; charset=utf-8');

    /*
     * Never allow a checkout request to use
     * another seller's products.
     */
    $sellerId = (int)$user_id;

    /*
     * Payment method.
     */
    $paymentMethod = $_POST['payment_method'] ?? '';

    $allowedPaymentMethods = [
        'cash',
        'bank'
    ];

    if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {

        echo json_encode([
            'success' => false,
            'message' => 'Please select a valid payment method.'
        ]);

        exit();
    }

    /*
     * Cart sent from JavaScript.
     */
    $cartJson = $_POST['cart'] ?? '';

    if ($cartJson === '') {

        echo json_encode([
            'success' => false,
            'message' => 'Checkout list is empty.'
        ]);

        exit();
    }

    $cart = json_decode(
        $cartJson,
        true
    );

    if (
        !is_array($cart) ||
        empty($cart)
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Checkout list is empty.'
        ]);

        exit();
    }

    /*
     * Validate cart items.
     */
    $cleanCart = [];

    foreach ($cart as $item) {

        if (!is_array($item)) {
            continue;
        }

        $productId = isset($item['id'])
            ? (int)$item['id']
            : 0;

        $quantity = isset($item['quantity'])
            ? (float)$item['quantity']
            : 0;

        if (
            $productId <= 0 ||
            $quantity <= 0
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid product or quantity detected.'
            ]);

            exit();
        }

        /*
         * Keep measured products to quarter
         * increments.
         */
        $quantity =
            round($quantity * 4) / 4;

        $cleanCart[] = [
            'id' => $productId,
            'quantity' => $quantity
        ];
    }

    if (empty($cleanCart)) {

        echo json_encode([
            'success' => false,
            'message' => 'No valid products were found.'
        ]);

        exit();
    }

    /*
     * =====================================================
     * START DATABASE TRANSACTION
     * =====================================================
     */

    $conn->begin_transaction();

    try {

        $totalAmount = 0;

        $verifiedItems = [];

        /*
         * We lock every product row while checking
         * stock.
         *
         * This is important because the UI stock is
         * only temporary.
         *
         * The database is the final authority.
         */
        $productStmt = $conn->prepare("
            SELECT
                product_id,
                user_id,
                product_name,
                selling_price,
                stock_quantity,
                unit
            FROM productservicesrentals
            WHERE product_id = ?
              AND user_id = ?
            LIMIT 1
            FOR UPDATE
        ");

        if (!$productStmt) {
            throw new Exception(
                "Could not prepare product verification."
            );
        }

        foreach ($cleanCart as $item) {

            $productId =
                $item['id'];

            $requestedQuantity =
                $item['quantity'];

            $productStmt->bind_param(
                "ii",
                $productId,
                $sellerId
            );

            $productStmt->execute();

            $result =
                $productStmt->get_result();

            $product =
                $result->fetch_assoc();

            if (!$product) {

                throw new Exception(
                    "Product ID {$productId} was not found in your store."
                );
            }

            $databaseStock =
                (float)$product['stock_quantity'];

            /*
             * FINAL DATABASE STOCK CHECK.
             */
            if (
                $requestedQuantity >
                $databaseStock
            ) {

                throw new Exception(
                    "Not enough stock for " .
                    $product['product_name'] .
                    ". Available: " .
                    $databaseStock .
                    "."
                );
            }

            $price =
                (float)$product['selling_price'];

            $subtotal =
                $requestedQuantity *
                $price;

            $totalAmount +=
                $subtotal;

            $verifiedItems[] = [
                'product_id' =>
                    $productId,

                'product_name' =>
                    $product['product_name'],

                'quantity' =>
                    $requestedQuantity,

                'price' =>
                    $price,

                'subtotal' =>
                    $subtotal,

                'stock' =>
                    $databaseStock,

                'unit' =>
                    $product['unit']
            ];
        }

        $productStmt->close();

        /*
        * =========================================================
        * GENERATE UNIQUE MARKET HUB ORDER CODE
        * =========================================================
        *
        * Example:
        *
        *     MH-7K4P9X
        *
        * The database order_id remains the internal primary key.
        * This code is the public-facing order reference.
        */


        /*
        * Characters intentionally exclude:
        *
        * 0 O 1 I L
        *
        * to avoid confusion when customers read or type
        * the order code.
        */

        $characters =
          'ABCDEFGHJKMNPQRSTUVWXYZ23456789';


        $characterCount =
          strlen($characters);


        /*
        * Generate a 6-character random code.
        */

        do {

          $randomPart = '';

          for ($i = 0; $i < 6; $i++) {

              $randomPart .=
                  $characters[
                      random_int(
                          0,
                          $characterCount - 1
                      )
                  ];

          }


          /*
            * Final public order code.
            */

          $orderCode =
              'MH-' . $randomPart;


          /*
            * Check whether the code already exists.
            */

          $checkStmt = $conn->prepare("
              SELECT order_id
              FROM orders
              WHERE order_code = ?
              LIMIT 1
          ");

          $checkStmt->bind_param(
              "s",
              $orderCode
          );

          $checkStmt->execute();

          $checkResult =
              $checkStmt->get_result();

          $codeExists =
              $checkResult->num_rows > 0;

          $checkStmt->close();


        } while ($codeExists);



        /*
         * =================================================
         * CREATE ORDER
         * =================================================
         *
         * buyer_id = NULL
         * customer_name = Customer
         *
         * payment_method = cash/bank
         */

        $orderStmt = $conn->prepare("
            INSERT INTO orders
            (
              order_code,
              buyer_id,
              total_amount,
              payment_method
            )
            VALUES
            (
              ?,
              NULL,
              ?,
              ?
            )
        ");

        if (!$orderStmt) {
            throw new Exception(
                "Could not prepare order creation."
            );
        }

        $orderStmt->bind_param(
            "sds",
            $orderCode,
            $totalAmount,
            $paymentMethod
        );

        if (!$orderStmt->execute()) {

            throw new Exception(
              "Could not create the sale order."
            );
        }

        $orderId =
            $conn->insert_id;

        $orderStmt->close();

        /*
         * =================================================
         * INSERT ORDER ITEMS
         * =================================================
         */

        $itemStmt = $conn->prepare("
            INSERT INTO order_items
            (
                order_id,
                product_id,
                seller_id,
                quantity,
                price,
                subtotal,
                order_status,
                payment_status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                'delivered',
                'paid'
            )
        ");

        if (!$itemStmt) {
            throw new Exception(
                "Could not prepare order items."
            );
        }

        /*
         * =================================================
         * UPDATE DATABASE STOCK
         * =================================================
         */

        $stockStmt = $conn->prepare("
            UPDATE productservicesrentals
            SET
                stock_quantity =
                    stock_quantity - ?,
                updated_at = NOW()
            WHERE product_id = ?
              AND user_id = ?
              AND stock_quantity >= ?
        ");

        if (!$stockStmt) {
            throw new Exception(
                "Could not prepare stock update."
            );
        }

        foreach ($verifiedItems as $item) {

            $productId =
                $item['product_id'];

            $quantity = (float)$item['quantity'];

            $price =
                $item['price'];

            $subtotal =
                $item['subtotal'];

            /*
             * Insert order item.
             */
            $itemStmt->bind_param(
                "iiiddd",
                $orderId,
                $productId,
                $sellerId,
                $quantity,
                $price,
                $subtotal
            );

            if (!$itemStmt->execute()) {

                throw new Exception(
                    "Could not save " .
                    $item['product_name'] .
                    " to the order."
                );
            }

            /*
             * Permanently subtract stock.
             */
            $stockStmt->bind_param(
                "diid",
                $quantity,
                $productId,
                $sellerId,
                $quantity
            );

            if (!$stockStmt->execute()) {

                throw new Exception(
                    "Could not update stock for " .
                    $item['product_name'] .
                    "."
                );
            }

            /*
             * This should always be exactly 1 because
             * the row was locked and verified above.
             */
            if ($stockStmt->affected_rows !== 1) {

                throw new Exception(
                    "Stock changed while processing " .
                    $item['product_name'] .
                    ". Please try checkout again."
                );
            }
        }

        $itemStmt->close();
        $stockStmt->close();

        /*
         * =================================================
         * EVERYTHING SUCCEEDED
         * =================================================
         */

        $conn->commit();

        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'order_code' => $orderCode,
            'total' => $totalAmount,
            'payment_method' => $paymentMethod,
            'message' => 'Sale completed successfully.'
        ]);

        exit();

    } catch (Throwable $e) {

        /*
         * Anything that failed is completely rolled back.
         *
         * This prevents situations where:
         *
         * order created
         * but stock not updated
         *
         * OR
         *
         * stock updated
         * but order item not created.
         */
        $conn->rollback();

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);

        exit();
    }
}

/* ---------- DELETE PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $deleteId = intval($_POST['delete_product_id']);

    // Verify product belongs to current seller
    $stmt = $conn->prepare("SELECT image_path FROM productservicesrentals WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $product = $result->fetch_assoc();

        // Delete image file if exists
        if (!empty($product['image_path']) && file_exists($product['image_path'])) {
            unlink($product['image_path']);
        }

        // Delete product from DB
        $stmtDel = $conn->prepare("DELETE FROM productservicesrentals WHERE product_id = ? AND user_id = ?");
        $stmtDel->bind_param("ii", $deleteId, $user_id);
        if ($stmtDel->execute()) {
            $success = "Product deleted successfully!";
        } else {
            $error = "Failed to delete product. Please try again.";
        }
        $stmtDel->close();
    } else {
        $error = "Product not found or not owned by you.";
    }

    $stmt->close();
}

// =========================================================
// LOAD PRODUCT FOR EDITING
// =========================================================

$editProductId = isset($_GET['edit_product_id'])
    ? (int) $_GET['edit_product_id']
    : 0;

$editMode = $editProductId > 0;

if ($editMode) {

    foreach ($products as $product) {

        if ((int) $product['product_id'] === $editProductId) {

            $productName      = $product['product_name'];
            $category         = $product['category'];
            $customCategoryId = $product['custom_category_id'];
            $buyingPrice      = $product['buying_price'];
            $sellingPrice     = $product['selling_price'];
            $stock            = $product['stock_quantity'];
            $unit             = $product['unit'];
            $currentImagePath = $product['image_path'];

            break;
        }
    }
}

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

$defaultAvatar = "Images/Makethub Logo.png";

if (!empty($profileImage) && file_exists($profileImage)) {
    $safeProfileImage = htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8');
} else {
    $safeProfileImage = $defaultAvatar;
}

$error = "";
$success = "";

$productName = '';
$category    = '';

$customCategoryId = null;
$saleType = '';
$unit      = '';
$buyingPrice       = '';
$sellingPrice       = '';
$stock       = '';

$editMode = false;
$editProductId = null;

if (isset($_GET['edit_product_id'])) {

  $editProductId = intval($_GET['edit_product_id']);

  $stmt = $conn->prepare("
    SELECT
        product_name,
        category,
        custom_category_id,
        sale_type,
        unit,
        buying_price,
        selling_price,
        stock_quantity,
        image_path
    FROM productservicesrentals
    WHERE product_id = ? AND user_id = ?
    LIMIT 1
  ");

  $stmt->bind_param("ii", $editProductId, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows === 1) {

      $product = $result->fetch_assoc();

      $productName       = $product['product_name'];
      $category          = $product['category'];
      $customCategoryId  = $product['custom_category_id'];
      $saleType          = $product['sale_type'];
      $unit             = $product['unit'];
      $buyingPrice            = $product['buying_price'];
      $sellingPrice            = $product['selling_price'];
      $stock            = $product['stock_quantity'];
      $currentImagePath = $product['image_path'];

      $editMode = true;
  }

  $stmt->close();
}

// ---------- ADD PRODUCT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_product_id']) && !isset($_POST['delete_product_id'])) {

$productName = smartTitleCase($_POST['name'] ?? '');
$category    = trim($_POST['category'] ?? '');
$saleType = $_POST['sale_type'] ?? 'Each';
$unit      = $_POST['unit'] ?? 'Each';
$buyingPrice       = floatval($_POST['bPrice'] ?? 0);
$sellingPrice       = floatval($_POST['sPrice'] ?? 0);
$stock       = intval($_POST['stock'] ?? 0);
if ($saleType === 'Each') {
  $unit = 'Each';
}

/* ---------- BASIC VALIDATION ---------- */

if ($productName === '') {
$error = "Product name is required!";
}
elseif ($category === '') {
$error = "Please select a category!";
}
elseif ($saleType === '') {
$error = "Please select a sale type!";
}
elseif ($unit === '') {
$error = "Please select a unit!";
}
elseif ($buyingPrice > $sellingPrice) {
$error = "Buying price must be less than selling price!";
}
elseif ($buyingPrice <= 0) {
$error = "Buying must be greater than zero!";
}
elseif ($sellingPrice <= 0) {
$error = "Selling price must be greater than zero!";
}

elseif ($stock < 0) {
$error = "Stock cannot be negative!";
}
elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
$error = "Please upload a product image!";
}
if ($saleType === 'Each') {
  $unit = 'Each';
}

if ($saleType === 'Measurement') {
  $unit = $_POST['unit'] ?? null;
} else {
  $unit = 'Each';
}

/* ---------- CHECK DUPLICATE PRODUCT NAME ---------- */

if (empty($error)) {

$stmt = $conn->prepare("
SELECT product_id
FROM productservicesrentals
WHERE user_id = ? AND product_name = ?
LIMIT 1
");

$stmt->bind_param("is", $user_id, $productName);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
$error = "You already added a product with this name.";
}

$stmt->close();

}


/* ---------- IMAGE VALIDATION ---------- */

if (empty($error)) {

$fileTmp  = $_FILES['photo']['tmp_name'];
$fileSize = $_FILES['photo']['size'];
$mime     = mime_content_type($fileTmp);

$allowed = ['image/jpeg','image/png','image/webp'];

if (!in_array($mime,$allowed)) {
$error = "Invalid image format.";
}
elseif($fileSize > 10 * 1024 * 1024){
  $error = "Image too large. Maximum size is 10MB.";
}

$imgInfo = getimagesize($fileTmp);

if (!$imgInfo) {
$error = "Invalid image.";
}

if (empty($error)) {

[$width,$height] = $imgInfo;

if ($width < 400 || $height < 400) {
  $error = "Image too small. Minimum size is 400 × 400 px.";
}

}

}

/* ---------- LOAD + CORRECT IMAGE ORIENTATION ---------- */

if (empty($error)) {

  switch ($mime) {

      case 'image/jpeg':

          $source = imagecreatefromjpeg($fileTmp);

          if (!$source) {
              $error = "Unable to process JPEG image.";
              break;
          }

          /*
            * Correct phone/camera orientation.
            */
          $source = fixImageOrientation(
              $source,
              $fileTmp,
              $mime
          );

          break;


      case 'image/png':

          $source = imagecreatefrompng($fileTmp);

          if (!$source) {
              $error = "Unable to process PNG image.";
              break;
          }

          break;


      case 'image/webp':

          $source = imagecreatefromwebp($fileTmp);

          if (!$source) {
              $error = "Unable to process WebP image.";
              break;
          }

          break;


      default:

          $error = "Unsupported image format.";
          break;
  }
}


/* ---------- RESIZE CORRECTED IMAGE ---------- */

if (empty($error)) {

  /*
    * IMPORTANT:
    * Get dimensions AFTER EXIF orientation
    * has been corrected.
    */
  $width  = imagesx($source);
  $height = imagesy($source);


  $maxSize = 700;

  $ratio = min(
      $maxSize / $width,
      $maxSize / $height,
      1
  );


  $newWidth  = (int)round($width * $ratio);
  $newHeight = (int)round($height * $ratio);


  $canvas = imagecreatetruecolor(
      $newWidth,
      $newHeight
  );


  /* Preserve PNG/WebP transparency */
  imagealphablending(
      $canvas,
      false
  );

  imagesavealpha(
      $canvas,
      true
  );


  imagecopyresampled(
      $canvas,
      $source,
      0,
      0,
      0,
      0,
      $newWidth,
      $newHeight,
      $width,
      $height
  );


  /* ---------- SAVE TEMP IMAGE FOR HASHING ---------- */

  $tempFile =
      tempnam(
          sys_get_temp_dir(),
          'img_'
      ) . '.webp';


  imagewebp(
      $canvas,
      $tempFile,
      75
  );


  /* ---------- GENERATE HASHES ---------- */

  $imgHash =
      md5_file($tempFile);

  $imgPhash =
      generateImageDHash($tempFile);
}


/* ---------- FAST DUPLICATE CHECK (MD5) ---------- */

if (empty($error)) {

$stmt = $conn->prepare("
SELECT product_id
FROM productservicesrentals
WHERE user_id = ? AND image_hash = ?
LIMIT 1
");

$stmt->bind_param("is",$user_id,$imgHash);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
$error = "This image already exists for another product.";
}

$stmt->close();

}


/* ---------- VISUAL DUPLICATE CHECK (pHash) ---------- */

if (empty($error)) {

$stmt = $conn->prepare("
SELECT image_phash
FROM productservicesrentals
WHERE user_id = ?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

$distance = levenshtein($imgPhash,$row['image_phash']);

if ($distance <= 5) {
$error = "A visually similar image already exists.";
break;
}

}

$stmt->close();

}


/* ---------- SAVE IMAGE IF NO DUPLICATE ---------- */

if (empty($error)) {

$uploadDir = 'uploads/products/';

if (!is_dir($uploadDir)) {
mkdir($uploadDir,0755,true);
}

$fileName = uniqid('product_',true).'.webp';
$filePath = $uploadDir.$fileName;

rename($tempFile,$filePath);

$fileSizeKB = round(filesize($filePath)/1024);


/* ---------- CLEAN MEMORY ---------- */

imagedestroy($canvas);
imagedestroy($source);

/* =========================================================
  HANDLE CUSTOM CATEGORY
========================================================= */

$customCategoryId = !empty($_POST['custom_category_id'])
    ? (int) $_POST['custom_category_id']
    : null;

$newCustomCategory = smartTitleCase(trim($_POST['new_custom_category'] ?? ''));

$postedCustomCategoryId = !empty($_POST['custom_category_id'])
  ? (int) $_POST['custom_category_id']
  : 0;


/*
---------------------------------------------------------
IF SELLER SELECTED AN EXISTING CUSTOM GROUP
---------------------------------------------------------
*/

if ($postedCustomCategoryId > 0) {

  $stmt = $conn->prepare("
      SELECT custom_category_id
      FROM custom_categories
      WHERE custom_category_id = ?
        AND user_id = ?
        AND company_category = ?
      LIMIT 1
  ");

  $stmt->bind_param(
      "iis",
      $postedCustomCategoryId,
      $user_id,
      $category
  );

  $stmt->execute();

  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {

      $customCategoryId = (int) $row['custom_category_id'];

  }

  $stmt->close();
}


/*
---------------------------------------------------------
IF SELLER ENTERED A NEW CUSTOM GROUP
---------------------------------------------------------
*/

elseif ($newCustomCategory !== '') {

  /*
    * Check whether this seller already has the same
    * custom group under this company category.
    */
  $stmt = $conn->prepare("
      SELECT custom_category_id
      FROM custom_categories
      WHERE user_id = ?
        AND company_category = ?
        AND name = ?
      LIMIT 1
  ");

  $stmt->bind_param(
      "iss",
      $user_id,
      $category,
      $newCustomCategory
  );

  $stmt->execute();

  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {

      /*
        * Use the existing group instead of creating
        * a duplicate.
        */
      $customCategoryId = (int) $row['custom_category_id'];

  } else {

      /*
        * Create the new custom group.
        *
        * parent_id is NULL because this is currently
        * a top-level seller custom group.
        */
      $stmtInsertCategory = $conn->prepare("
          INSERT INTO custom_categories
          (
              user_id,
              company_category,
              name,
              parent_id
          )
          VALUES (?, ?, ?, NULL)
      ");

      $stmtInsertCategory->bind_param(
          "iss",
          $user_id,
          $category,
          $newCustomCategory
      );

      if (!$stmtInsertCategory->execute()) {

          $error = "Failed to create custom group: "
                  . $stmtInsertCategory->error;

      } else {

          $customCategoryId = $stmtInsertCategory->insert_id;

      }

      $stmtInsertCategory->close();
  }

  $stmt->close();
}

/* ---------- INSERT PRODUCT ---------- */

$stmt = $conn->prepare("
    INSERT INTO productservicesrentals
    (
        user_id,
        product_name,
        category,
        custom_category_id,
        sale_type,
        unit,
        buying_price,
        selling_price,
        stock_quantity,
        image_path,
        image_width,
        image_height,
        image_size_kb,
        image_format,
        image_hash,
        image_phash
    )
    VALUES
    (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'webp', ?, ?
    )
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
  "ississddisiiiss",
  $user_id,
  $productName,
  $category,
  $customCategoryId,
  $saleType,
  $unit,
  $buyingPrice,
  $sellingPrice,
  $stock,
  $filePath,
  $newWidth,
  $newHeight,
  $fileSizeKB,
  $imgHash,
  $imgPhash
);

if ($stmt->execute()) {

    $success = "Product added successfully! <span class='redirect-msg'></span>";

    $productName = '';
    $category = '';
    $customCategoryId = null;
    $newCustomCategory = '';
    $saleType = '';
    $unit = '';
    $buyingPrice = '';
    $sellingPrice = '';
    $stock = '';

} else {

    die($stmt->error);

}

$stmt->close();

}

}

// ---------- EDIT PRODUCT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product_id'])) {

$editProductId = intval($_POST['edit_product_id']);

$productName = smartTitleCase($_POST['name'] ?? '');
$category    = trim($_POST['category'] ?? '');
$customCategoryId    = trim($_POST['custom_category_id'] ?? '');
$saleType = $_POST['sale_type'] ?? 'Each';
$unit      = $_POST['unit'] ?? 'Each';
$buyingPrice  = isset($_POST['bPrice']) ? (float)$_POST['bPrice'] : 0;
$sellingPrice = isset($_POST['sPrice']) ? (float)$_POST['sPrice'] : 0;
$stock       = intval($_POST['stock'] ?? 0);

/* ---------- BASIC VALIDATION ---------- */

if ($productName === '') {
$error="Product name required!";
}
elseif ($category === '') {
$error="Select category!";
}
elseif ($buyingPrice>$sellingPrice) {
$error="Buying price must be less than selling price!";
}
elseif ($buyingPrice<=0) {
$error="Buying price must be greater than zero!";
}
elseif ($sellingPrice<=0) {
$error="Selling price must be greater than zero!";
}
elseif ($stock<0) {
$error="Stock cannot be negative!";
}


/* ---------- CHECK DUPLICATE PRODUCT NAME ---------- */

if (empty($error)) {

$stmt=$conn->prepare("
SELECT product_id
FROM productservicesrentals
WHERE user_id=? AND product_name=? AND product_id<>?
LIMIT 1
");

$stmt->bind_param("isi",$user_id,$productName,$editProductId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows>0){
$error="Another product already has this name.";
}

$stmt->close();

}


/* ---------- FETCH CURRENT IMAGE ---------- */

$stmt=$conn->prepare("
SELECT image_path,image_hash,image_phash
FROM productservicesrentals
WHERE product_id=? AND user_id=?
LIMIT 1
");

$stmt->bind_param("ii",$editProductId,$user_id);
$stmt->execute();
$res=$stmt->get_result();
$row=$res->fetch_assoc();
$stmt->close();

$currentImage=$row['image_path'];
$currentHash =$row['image_hash'];
$currentPhash=$row['image_phash'];

$imageToSave=$currentImage;
$imgHash=$currentHash;
$imgPhash=$currentPhash;


/* ---------- IF USER UPLOADS NEW IMAGE ---------- */
$newImageUploaded = false;
if (isset($_FILES['photo']) && $_FILES['photo']['error']==0){
$newImageUploaded = true;

$fileTmp  = $_FILES['photo']['tmp_name'];
$fileSize = $_FILES['photo']['size'];
$mime     = mime_content_type($fileTmp);

$allowed=['image/jpeg','image/png','image/webp'];

if(!in_array($mime,$allowed)){
$error="Invalid image format.";
}

elseif($fileSize > 10 * 1024 * 1024){
  $error = "Image too large. Maximum size is 10MB.";
}

$imgInfo=getimagesize($fileTmp);

if(!$imgInfo){
$error="Invalid image file.";
}

if(empty($error)){

[$width,$height]=$imgInfo;

if ($width < 400 || $height < 400) {
  $error = "Image too small. Minimum size is 400 × 400 px.";
}

}

}


/* ---------- LOAD + CORRECT IMAGE ORIENTATION ---------- */

if (
  empty($error) &&
  isset($_FILES['photo']) &&
  $_FILES['photo']['error'] == 0
) {

  switch ($mime) {

      case 'image/jpeg':

          $source =
              imagecreatefromjpeg($fileTmp);

          if (!$source) {
              $error = "Unable to process JPEG image.";
              break;
          }

          /*
            * Correct phone/camera EXIF orientation.
            */
          $source =
              fixImageOrientation(
                  $source,
                  $fileTmp,
                  $mime
              );

          break;


      case 'image/png':

          $source =
              imagecreatefrompng($fileTmp);

          if (!$source) {
              $error = "Unable to process PNG image.";
              break;
          }

          break;


      case 'image/webp':

          $source =
              imagecreatefromwebp($fileTmp);

          if (!$source) {
              $error = "Unable to process WebP image.";
              break;
          }

          break;


      default:

          $error = "Unsupported image format.";
          break;
  }
}


/* ---------- RESIZE CORRECTED IMAGE ---------- */

if (
  empty($error) &&
  isset($source)
) {

  /*
    * Get dimensions AFTER orientation correction.
    */
  $width  = imagesx($source);
  $height = imagesy($source);


  $maxSize = 700;

  $ratio = min(
      $maxSize / $width,
      $maxSize / $height,
      1
  );


  $newWidth =
      (int)round($width * $ratio);

  $newHeight =
      (int)round($height * $ratio);


  $canvas =
      imagecreatetruecolor(
          $newWidth,
          $newHeight
      );


  /*
    * Preserve transparency.
    */
  imagealphablending(
      $canvas,
      false
  );

  imagesavealpha(
      $canvas,
      true
  );


  imagecopyresampled(
      $canvas,
      $source,
      0,
      0,
      0,
      0,
      $newWidth,
      $newHeight,
      $width,
      $height
  );


  /* ---------- TEMP FILE FOR HASH ---------- */

  $tempFile =
      tempnam(
          sys_get_temp_dir(),
          'img_'
      ) . '.webp';


  imagewebp(
      $canvas,
      $tempFile,
      75
  );


  /* ---------- GENERATE HASHES ---------- */

  $imgHash =
      md5_file($tempFile);

  $imgPhash =
      generateImageDHash($tempFile);
}


/* ---------- EXACT DUPLICATE CHECK ---------- */

if(empty($error) && $newImageUploaded) {

$stmt=$conn->prepare("
SELECT product_id
FROM productservicesrentals
WHERE user_id=? AND image_hash=? AND product_id<>?
LIMIT 1
");

$stmt->bind_param("isi",$user_id,$imgHash,$editProductId);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows>0){
$error="This image already exists for another product.";
}

$stmt->close();

}


/* ---------- VISUAL DUPLICATE CHECK ---------- */

if(empty($error) && isset($imgPhash)){

$stmt=$conn->prepare("
SELECT image_phash
FROM productservicesrentals
WHERE user_id=? AND product_id<>?
");
$stmt->bind_param("ii",$user_id,$editProductId);
$stmt->execute();
$result=$stmt->get_result();

while($r=$result->fetch_assoc()){

$distance=levenshtein($imgPhash,$r['image_phash']);

if($distance<=5){
$error="A visually similar image already exists.";
break;
}

}

$stmt->close();

}


/* ---------- SAVE NEW IMAGE ---------- */

if(empty($error) && $newImageUploaded){

$uploadDir='uploads/products/';

if(!is_dir($uploadDir)){
mkdir($uploadDir,0755,true);
}

$fileName = uniqid('product_',true).'.webp';
$filePath = $uploadDir.$fileName;

rename($tempFile,$filePath);

if(file_exists($currentImage)){
unlink($currentImage);
}

$imageToSave = $filePath;

if(isset($canvas)) imagedestroy($canvas);
if(isset($source)) imagedestroy($source);

}

/* =========================================================
  HANDLE CUSTOM GROUP DURING PRODUCT UPDATE
========================================================= */

$customCategoryId = !empty($_POST['custom_category_id'])
  ? (int)$_POST['custom_category_id']
  : null;

$newCustomCategory = smartTitleCase(trim($_POST['new_custom_category'] ?? ''));


/*
---------------------------------------------------------
CREATE NEW CUSTOM GROUP IF SELLER TYPED ONE
---------------------------------------------------------
*/

if ($newCustomCategory !== '') {

  /*
    * Make sure a company category was selected.
    */
  if (empty($category)) {

      $error = "Please select a general category first.";

  } else {

      /*
        * Check whether this seller already has this group
        * under the selected company category.
        */
      $checkStmt = $conn->prepare("
          SELECT custom_category_id
          FROM custom_categories
          WHERE user_id = ?
            AND company_category = ?
            AND name = ?
          LIMIT 1
      ");

      $checkStmt->bind_param(
          "iss",
          $user_id,
          $category,
          $newCustomCategory
      );

      $checkStmt->execute();

      $checkResult = $checkStmt->get_result();

      if ($checkResult && $checkResult->num_rows > 0) {

          /*
            * Group already exists.
            * Use the existing ID instead of creating duplicate.
            */
          $existingGroup = $checkResult->fetch_assoc();

          $customCategoryId =
              (int)$existingGroup['custom_category_id'];

      } else {

          /*
            * Create the new custom group.
            */
          $insertCategory = $conn->prepare("
              INSERT INTO custom_categories
              (
                  user_id,
                  company_category,
                  name,
                  parent_id
              )
              VALUES (?, ?, ?, NULL)
          ");

          $insertCategory->bind_param(
              "iss",
              $user_id,
              $category,
              $newCustomCategory
          );

          if ($insertCategory->execute()) {

              /*
                * Get the newly-created custom category ID.
                */
              $customCategoryId =
                  $insertCategory->insert_id;

          } else {

              $error =
                  "Failed to create custom group: "
                  . $insertCategory->error;
          }

          $insertCategory->close();
      }

      $checkStmt->close();
  }
}


/* ---------- UPDATE PRODUCT ---------- */

if (empty($error)) {

    $stmt = $conn->prepare("
      UPDATE productservicesrentals
      SET
          product_name = ?,
          category = ?,
          custom_category_id = ?,
          sale_type = ?,
          unit = ?,
          buying_price = ?,
          selling_price = ?,
          stock_quantity = ?,
          image_path = ?,
          image_hash = ?,
          image_phash = ?
      WHERE product_id = ?
        AND user_id = ?
    ");

    $stmt->bind_param(
      "ssissddisssii",
      $productName,
      $category,
      $customCategoryId,
      $saleType,
      $unit,
      $buyingPrice,
      $sellingPrice,
      $stock,
      $imageToSave,
      $imgHash,
      $imgPhash,
      $editProductId,
      $user_id
    );

    if ($stmt->execute()) {

      $success = "Product updated successfully! <span class='redirect-msg'></span>";

      $productName = '';
      $category = '';
      $customCategoryId = null;
      $saleType = 'Each';
      $unit = 'Each';
      $buyingPrice = '';
      $sellingPrice = '';
      $stock = '';

    } else {

      $error = "Update failed: " . $stmt->error;

    }

  $stmt->close();
}
}

/* =========================================================
  FETCH SELLER PRODUCTS + CUSTOM CATEGORY INFORMATION
  ========================================================= */

$products = [];

$stmt = $conn->prepare("
  SELECT
      p.product_id,
      p.product_name,
      p.category,
      p.custom_category_id,
      p.buying_price,
      p.selling_price,
      p.stock_quantity,
      p.unit,
      p.image_path,

      cc.name AS custom_category_name,
      cc.parent_id AS custom_category_parent_id

  FROM productservicesrentals p

  LEFT JOIN custom_categories cc
      ON p.custom_category_id = cc.custom_category_id

  WHERE p.user_id = ?

  ORDER BY p.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {

  while ($row = $result->fetch_assoc()) {

      $products[] = $row;

  }

}

$stmt->close();


/* =========================================================
  FETCH SELLER CUSTOM CATEGORIES
========================================================= */

$customCategories = [];

$stmt = $conn->prepare("
  SELECT
      custom_category_id,
      company_category,
      name,
      parent_id
  FROM custom_categories
  WHERE user_id = ?
  ORDER BY name ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {

  while ($row = $result->fetch_assoc()) {

      $customCategories[] = $row;

  }

}

$stmt->close();


/* =========================================================
  PREPARE CATEGORY DATA FOR JAVASCRIPT
========================================================= */

$categoryJson = json_encode(
  $customCategories,
  JSON_HEX_TAG |
  JSON_HEX_APOS |
  JSON_HEX_AMP |
  JSON_HEX_QUOT
);

$productJson = json_encode(
  $products,
  JSON_HEX_TAG |
  JSON_HEX_APOS |
  JSON_HEX_AMP |
  JSON_HEX_QUOT
);

/* =========================================================
  LOAD SELLER'S CUSTOM GROUPS
========================================================= */

$customCategories = [];

$stmt = $conn->prepare("
  SELECT custom_category_id, company_category, name, parent_id
  FROM custom_categories
  WHERE user_id = ?
  ORDER BY name ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $customCategories[] = $row;
}

$stmt->close();

// Fetch seller orders
// ONE ROW PER ORDER
$sellerOrders = [];

$stmt = $conn->prepare("
  SELECT
      o.order_id,
      o.order_code,
      o.created_at,
      o.buyer_id,
      o.payment_method,

      CASE
          WHEN o.buyer_id IS NULL THEN 'Customer'
          ELSE COALESCE(u.full_name, 'Customer')
      END AS buyer_name,

      /* Number of DIFFERENT product types */
      COUNT(DISTINCT oi.product_id) AS product_count,

      /* Total physical quantity across all products */
      SUM(oi.quantity) AS total_quantity,

      /* Total value of this seller's order */
      SUM(oi.quantity * oi.price) AS seller_total,

      /* Product images, maximum handled in PHP */
      GROUP_CONCAT(
          DISTINCT p.image_path
          ORDER BY oi.item_id
          SEPARATOR '|||'
      ) AS product_images,

      /* Payment/order information */
      MAX(oi.order_status) AS order_status,
      MAX(oi.shipped_at) AS shipped_at,
      MAX(oi.delivered_at) AS delivered_at,
      MAX(oi.payment_status) AS payment_status

  FROM orders o

  INNER JOIN order_items oi
      ON oi.order_id = o.order_id

  LEFT JOIN users u
      ON o.buyer_id = u.user_id

  INNER JOIN productservicesrentals p
      ON oi.product_id = p.product_id

  WHERE oi.seller_id = ?

  GROUP BY
      o.order_id,
      o.order_code,
      o.created_at,
      o.buyer_id,
      o.payment_method,
      u.full_name

  ORDER BY o.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {
  while ($row = $result->fetch_assoc()) {
      $sellerOrders[] = $row;
  }
}

$stmt->close();

/* =========================================================
  DAILY STATS
  Today's seller sales
  ========================================================= */

$dailySales = 0;
$dailyOrders = 0;
$dailyProducts = 0;
$dailyCash = 0;
$dailyBank = 0;

$stmt = $conn->prepare("
  SELECT
      COUNT(DISTINCT o.order_id) AS daily_orders,

      COUNT(DISTINCT oi.product_id) AS daily_products,

      COALESCE(SUM(oi.quantity * oi.price), 0) AS daily_sales,

      COALESCE(
          SUM(
              CASE
                  WHEN LOWER(o.payment_method) = 'cash'
                  THEN (oi.quantity * oi.price)
                  ELSE 0
              END
          ),
          0
      ) AS daily_cash,

      COALESCE(
          SUM(
              CASE
                  WHEN LOWER(o.payment_method) = 'bank'
                  THEN (oi.quantity * oi.price)
                  ELSE 0
              END
          ),
          0
      ) AS daily_bank

  FROM order_items oi

  INNER JOIN orders o
      ON oi.order_id = o.order_id

  WHERE oi.seller_id = ?
    AND DATE(o.created_at) = CURDATE()
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {
  $row = $result->fetch_assoc();

  $dailyOrders   = (int)($row['daily_orders'] ?? 0);
  $dailyProducts = (int)($row['daily_products'] ?? 0);
  $dailySales    = (float)($row['daily_sales'] ?? 0);
  $dailyCash     = (float)($row['daily_cash'] ?? 0);
  $dailyBank     = (float)($row['daily_bank'] ?? 0);
}

$stmt->close();

/* =========================================================
  DAILY STOCK STATUS
========================================================= */

$stmt = $conn->prepare("
  SELECT
      COALESCE(SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END), 0)
          AS out_of_stock,

      COALESCE(SUM(
          CASE
              WHEN stock_quantity > 0
                AND stock_quantity <= 5
              THEN 1
              ELSE 0
          END
      ), 0)
          AS low_stock

  FROM productservicesrentals

  WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {
  $stockRow = $result->fetch_assoc();

  $dailyOutOfStock = (int)($stockRow['out_of_stock'] ?? 0);
  $dailyLowStock   = (int)($stockRow['low_stock'] ?? 0);
}

$stmt->close();

// Count seller's orders
// One order = one count, regardless of number of products
$countStmt = $conn->prepare("
  SELECT COUNT(DISTINCT o.order_id) AS activeOrders
  FROM orders o
  INNER JOIN order_items oi
      ON oi.order_id = o.order_id
  LEFT JOIN users u
      ON o.buyer_id = u.user_id
  INNER JOIN productservicesrentals p
      ON oi.product_id = p.product_id
  WHERE seller_id = ?
    AND order_status = 'pending'
");

$countStmt->bind_param("i", $user_id);
$countStmt->execute();

$countResult = $countStmt->get_result();

$activeOrders = 0;

if ($countResult && $countResult->num_rows === 1) {
  $row = $countResult->fetch_assoc();
  $activeOrders = (int)$row['activeOrders'];
}

$countStmt->close();

// Prepare display value
$displayCount = $activeOrders > 9 ? "9+" : $activeOrders;

$walletType = 'seller';

$stmt = $conn->prepare("
  SELECT balance 
  FROM wallets 
  WHERE user_id = ? AND wallet_type = ? 
  LIMIT 1
");
$stmt->bind_param("is", $user_id, $walletType);
$stmt->execute();
$stmt->bind_result($walletBalance);
$walletExists = $stmt->fetch();
$stmt->close();

// If wallet does not exist → initialize it
if (!$walletExists) {
  $walletBalance = 0;

  $stmt = $conn->prepare("
      INSERT INTO wallets 
      (user_id, wallet_type, balance, total_transacted, created_at, updated_at)
      VALUES (?, ?, 0, 0, NOW(), NOW())
  ");
  $stmt->bind_param("is", $user_id, $walletType);
  $stmt->execute();
  $stmt->close();
}

$minWithdrawal = 500;

$isEligible = $walletBalance >= $minWithdrawal;
$withdrawStatus = $isEligible ? "Eligible" : "Not Eligible";
$withdrawClass = $isEligible ? "green" : "red";

// Fetch seller orders summary - LAST 28 DAYS
$stmt = $conn->prepare("
  SELECT 
      COUNT(DISTINCT oi.order_id) AS total_orders,

      COUNT(DISTINCT CASE 
          WHEN oi.order_status = 'pending' 
          THEN oi.order_id 
      END) AS processing_orders,

      COUNT(DISTINCT CASE 
          WHEN oi.order_status = 'shipped' 
          THEN oi.order_id 
      END) AS shipped_orders,

      COUNT(DISTINCT CASE 
          WHEN oi.order_status = 'delivered' 
          THEN oi.order_id 
      END) AS delivered_orders

  FROM order_items oi

  INNER JOIN orders o 
      ON oi.order_id = o.order_id

  WHERE oi.seller_id = ?

    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 28 DAY)
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();


$totalOrders      = (int)($row['total_orders'] ?? 0);
$processingOrders = (int)($row['processing_orders'] ?? 0);
$shippedOrders    = (int)($row['shipped_orders'] ?? 0);
$deliveredOrders  = (int)($row['delivered_orders'] ?? 0);

$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_wallet'])) {

  $walletType = 'seller'; // seller always uses seller wallet
  $error = '';
  $success = '';

  $withdrawAmount = $_POST['withdraw_sales_amount'] ?? '';

  // 1️⃣ Ensure the seller has a wallet
  $stmt = $conn->prepare("SELECT wallet_id, balance FROM wallets WHERE user_id = ? AND wallet_type = ? LIMIT 1");
  $stmt->bind_param("is", $user_id, $walletType);
  $stmt->execute();
  $stmt->bind_result($walletId, $walletBalance);
  $walletExists = $stmt->fetch();
  $stmt->close();

  $balance = $walletBalance;
  $min = $minWithdrawal;

  if (!$walletExists) {
    // Create a new wallet
    $stmt = $conn->prepare("
      INSERT INTO wallets (user_id, wallet_type, balance, total_transacted, created_at, updated_at)
      VALUES (?, ?, 0, 0, NOW(), NOW())
    ");
    $stmt->bind_param("is", $user_id, $walletType);
    $stmt->execute();
    $walletId = $stmt->insert_id;
    $walletBalance = 0;
    $stmt->close();
  }

  if (empty($withdrawAmount) && $withdrawAmount !== '0') {
    $error = "Please enter a withdrawal amount!";
  } else {

    $withdrawAmount = floatval($withdrawAmount);

    // Max limit
    $maxWithdrawal = 100000.0;
    if ($withdrawAmount > $maxWithdrawal) {
        $error = "Maximum withdrawal allowed is KES $maxWithdrawal!";
    }

    // --- M-Pesa style fee ---
    if ($withdrawAmount <= 1000) {
        $fee = 40;
    } elseif ($withdrawAmount <= 10000) {
        $fee = 50 + 0.002 * $withdrawAmount;
    } elseif ($withdrawAmount <= 50000) {
        $fee = 100 + 0.0015 * $withdrawAmount;
    } else {
        $fee = 200 + 0.001 * $withdrawAmount;
    }

    $fee = round($fee, 2);
    $netAmount = $withdrawAmount - $fee;

    // Validation
    if (!$error) {
      if ($withdrawAmount < $min) {
        $error = "Minimum withdrawal is KES $min!";
      } elseif ($withdrawAmount > (float)$balance) {
        $error = "Insufficient balance. Your wallet balance is KES $balance!";
      } elseif ($netAmount <= 0) {
        $error = "Withdrawal amount must be greater than fee (KES $fee)!";
    }
    }
  }

  if (!$error) {

    $conn->begin_transaction();

    try {
      
      // 1️⃣ Deduct wallet
      $stmt = $conn->prepare("
        UPDATE wallets 
        SET balance = balance - ? 
        WHERE user_id = ? AND wallet_type = ? AND balance >= ? LIMIT 1
      ");
      $stmt->bind_param("disd", $withdrawAmount, $user_id, $walletType, $withdrawAmount);
      $stmt->execute();

      if ($stmt->error) {
        throw new Exception("Update error: " . $stmt->error);
      }

      if ($stmt->affected_rows === 0) {
        throw new Exception("No rows updated. Check wallet_type or balance.");
      }
      $stmt->close();
      $sourceType = "seller_withdrawal";
      $description = "Withdrawal request";

      // 2️⃣ financial_transactions
      $stmt = $conn->prepare("
        INSERT INTO financial_transactions 
        (source_type, source_id, wallet_id, payer_id, receiver_id, transaction_type, amount, currency, status, description, created_at)
        VALUES (?, ?, ?, ?, ?, 'withdrawal', ?, 'KES', 'pending', ?, NOW())
      ");
      $stmt->bind_param(
        "siiiids",
        $sourceType,
        $user_id,
        $walletId,
        $user_id,
        $user_id,
        $withdrawAmount,
        $description
      );
      $stmt->execute();
      $transactionId = $conn->insert_id;
      $stmt->close();

      // 3️⃣ withdrawals
      $stmt = $conn->prepare("
        INSERT INTO withdrawals (user_id, wallet_id, amount, fee, net_amount, status, transaction_id, requested_at, currency)
        VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), 'KES')
      ");
      $stmt->bind_param("iidddi", $user_id, $walletId, $withdrawAmount, $fee, $netAmount, $transactionId);
      $stmt->execute();
      $withdrawalId = $stmt->insert_id;
      $stmt->close();

      // 4️⃣ withdrawal_logs
      $stmt = $conn->prepare("
        INSERT INTO withdrawal_logs 
        (withdrawal_id, performed_by, note, created_at)
        VALUES (?, ?, ?, NOW())
      ");

      $note = "Seller requested withdrawal of KES $withdrawAmount, net KES $netAmount, fee KES $fee";

      $stmt->bind_param("iis", $withdrawalId, $user_id, $note);
      $stmt->execute();
      $stmt->close();

      $conn->commit();

      $success = "Withdrawal request of KES $withdrawAmount submitted successfully. You will receive KES $netAmount after fees! <span class='redirect-msg'></span>";

    } catch (Exception $e) {
      $conn->rollback();
      $error = "Withdrawal failed: " . $e->getMessage();
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {

  $itemId = intval($_POST['item_id']);
  $newStatus = $_POST['status'];

  // Only allow valid statuses
  if (!in_array($newStatus, ['shipped', 'delivered'])) {
      echo json_encode(['success' => false, 'message' => 'Invalid status']);
      exit();
  }

  if ($newStatus === 'shipped') {
      $stmt = $conn->prepare("
          UPDATE order_items 
          SET order_status = 'shipped', shipped_at = NOW()
          WHERE item_id = ? AND seller_id = ?
      ");
  } else { // Delivered
      $stmt = $conn->prepare("
          UPDATE order_items 
          SET order_status = 'delivered', delivered_at = NOW()
          WHERE item_id = ? AND seller_id = ?
      ");
  }

  $stmt->bind_param("ii", $itemId, $user_id);

  if ($stmt->execute()) {
      echo json_encode(['success' => true, 'order_id' => $itemId, 'new_status' => $newStatus]);
  } else {
      echo json_encode(['success' => false, 'message' => 'Failed to update status']);
  }

  $stmt->close();
  exit(); // Stop the script so no extra HTML or headers are sent
}

if (isset($_POST['action']) && $_POST['action'] === 'mark_shipped') {

  header('Content-Type: application/json'); // ✅ IMPORTANT

  $orderId = intval($_POST['order_id']);

  $stmt = $conn->prepare("
    UPDATE order_items SET order_status = 'shipped', shipped_at = NOW() 
    WHERE order_id = ?
  ");
  $stmt->bind_param("i", $orderId);

  if ($stmt->execute()) {
      echo json_encode([
          'success' => true,
          'status' => 'shipped'
      ]);
  } else {
      echo json_encode([
          'success' => false,
          'error' => $stmt->error
      ]);
  }

  exit; // ✅ MUST STOP EVERYTHING
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

  <title>Seller Page | Makethub</title>
</head>
<body>
  
  <!-- =========================================================
    CAMERA MODAL
    ========================================================= -->

  <div id="cameraModal" class="camera-modal" >
    <div class="camera-box">
      <div class="camera-header">
        <span>
          <img src="Images/Makethub Logo.png" alt="Makethub Logo" width="28"> Take Product Photo
        </span>
        <button type="button" id="closeCameraButton" class="camera-close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="camera-view"> 
        <video id="cameraVideo" autoplay playsinline muted>
        </video>
        <canvas id="cameraCanvas" ></canvas>
      </div>
      <div class="camera-controls">
        <button type="button" id="capturePhotoButton" class="capture-photo-button">
          <span class="capture-circle"></span>
        </button>
      </div>
    </div>
  </div>
  <div class="confirmation-popup" id="confirmation-popup">
    <h3 id="popupTitle">Confirm Action</h3>
    <p id="popupMessage">Are you sure?</p>

    <div class="popup-actions">
      <button id="confirmAction" class="btn-confirm">Yes, Confirm</button>
      <button id="cancelAction" class="btn-cancel">Cancel</button>
    </div>
  </div>
  <div class="container">
    <header class="pgHeader">
      <section>
        <div class="sContainer seller">
          <div class="sCrh">
            <img src="<?php echo $safeProfileImage; ?>" alt="Profile" class="avatar-img">
            <p class="wcmTxt">
              Welcome,<br>
              <span><?php echo $safeUsername; ?></span>
            </p>
          </div>
          <div class="sClh">
            <div class="days-battery" id="daysBattery">
              <div class="days-battery-fill" id="daysBatteryFill"></div>
            </div>
          </div>
        </div>
        <div class="rhs">
          <a class="lkOdr" onclick="toggleSellerOrdersTrack()">
            <div class="odrIconDiv">
              <i class="fa-brands fa-first-order-alt"></i>
              <p><?= $displayCount ?></p>
            </div>
            <p>Order(s)</p>
          </a>
          <select name="county">
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
          <textarea id="userMessage" placeholder="Type a message.."></textarea>
          <img src="Images/Send-35.png" alt="Send Icon" width="45" onclick="sendWhatsAppMessage()">
        </div>
      </div>
    </div>

    <main class="buyerMain" id="sellerMain">
      <div id="subGOverlay" class="subGOverlay"></div>
      <form action="" class="subG-form">
        <h3>Add sub group 
          <span>
            <i class="fa-solid fa-xmark"></i>
          </span>
        </h3>
        <p class="errorMessage">Failed!</p>
        <label for="subgroup">Enter Sub Group Name</label>
        <input type="text" placeholder="eg. Cups">
        <button type="submit">Add</button>
      </form>
      <div class="tabs-container" id="toggleMarketTypeTab">
        <div class="tabs">
          <button class="tab-btn" data-tab="dashboard">Dashboard</button>
          <button class="tab-btn" data-tab="products">Store</button>
          <!-- <button class="tab-btn" data-tab="funds">Funds</button> -->
        </div>

        <div class="tab-content">
         <!--  <div style="margin:20px 0;">
            <a href="?download_qr=1" 
              style="padding:12px 20px;background:#000;color:#fff;text-decoration:none;border-radius:8px;">
              📥 Download Shop QR Sticker
            </a>

            <br><br>

            <img src="<?php echo $qrImageUrl; ?>" width="150" style="border:5px solid #eee;">
          </div> -->
          <div id="dashboard" class="tab-panel">
            <div class="tab-top sales">
              <p>Dashboard Area <br><strong>Your business operations <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleSalesDash()">
                Sale&nbsp;<span><i class="fa-solid fa-tags"></i></span>
              </button>

            </div>
            <div class="containerInner">

              <div class="grid">
                <!-- DAILY STATS -->
                <div class="card">

                    <i class="fa-solid fa-receipt icon"></i>

                    <h3>Daily Sales</h3>

                    <!-- Total sales -->
                    <div class="stat">
                        KES <?= number_format($dailySales, 2) ?>
                    </div>

                    <p class="meta">
                        Total daily stats
                    </p>

                    <div class="progress">
                        <span style="width:<?= min(($dailySales / 20000) * 100, 100) ?>%"></span>
                    </div>

                    <div class="daily-stats-slider">

                      <p class="small daily-stat-item green">
                          <?= $dailyOrders ?>
                          <?= $dailyOrders == 1 ? 'sale' : 'sales' ?>
                          made today 🎉
                      </p>

                      <p class="small daily-stat-item green">
                          KES <?= number_format($dailyCash, 2) ?>
                          collected by cash today 💸
                      </p>

                      <p class="small daily-stat-item green">
                          KES <?= number_format($dailyBank, 2) ?>
                          collected by bank today 🏦
                      </p>

                      <?php if ($dailyOutOfStock > 0): ?>

                          <p class="small daily-stat-item red">
                              <?= $dailyOutOfStock ?>
                              <?= $dailyOutOfStock == 1 ? 'product is' : 'products are' ?>
                              out of stock ❗
                          </p>

                      <?php endif; ?>

                      <?php if ($dailyLowStock > 0): ?>

                          <p class="small daily-stat-item yellow">
                              <?= $dailyLowStock ?>
                              <?= $dailyLowStock == 1 ? 'product is' : 'products are' ?>
                              running low ⚠️
                          </p>

                      <?php endif; ?>

                    </div>

                </div>

                <!-- WALLET HEALTH --><!-- 
                <div class="card">
                  <i class="fa fa-wallet icon"></i>
                  <h3>Wallet Health</h3>

                  <div class="stat">KES <?= number_format($walletBalance, 2) ?></div>

                  <p class="meta">Available for withdrawal</p>

                  <div class="progress">
                    <span style="width:<?= min(($walletBalance/20000)*100,100) ?>%"></span>
                  </div>

                  <p class="small">KES 0 pending clearance</p>
                </div> -->
                <!-- ORDERS SUMMARY -->
                <div class="card">
                  <i class="fa fa-box icon"></i>
                  <h3>Orders Summary</h3>

                  <div class="stat">
                      <?= formatToK($totalOrders) ?> <?= $totalOrders == 1 ? 'Order' : 'Orders' ?>
                  </div>

                  <p class="meta">
                      <span class="badge yellow"><?= $processingOrders ?> <?= $processingOrders == 1 ? 'Processing' : 'Processing' ?></span>
                      <span class="badge blue"><?= $shippedOrders ?> <?= $shippedOrders == 1 ? 'Shipped' : 'Shipped' ?></span>
                      <span class="badge green"><?= $deliveredOrders ?> <?= $deliveredOrders == 1 ? 'Delivered' : 'Delivered' ?></span>
                  </p>
                  <p class="small">In the last 28 days</p>
                </div>

                <!-- WITHDRAWAL STATUS --><!-- 
                <div class="card">
                  <i class="fa fa-money-bill-wave icon"></i>
                  <h3>Withdrawal Status</h3>

                  <span class="badge <?= $withdrawClass ?>">
                    <?= $withdrawStatus ?>
                  </span>

                  <p class="meta">
                    Minimum withdrawal KES 500
                  </p>

                  <div class="actions">
                    <button 
                      onclick="togglePaymentOption()"
                      <?= !$isEligible ? 'disabled' : '' ?>
                    >
                      Withdraw
                    </button>
                  </div>

                  <p class="small">
                    Available: KES <?= number_format($walletBalance) ?>
                  </p>
                </div> -->

                <!-- CUSTOMER TRUST -->
                <div class="card">
                  <i class="fa fa-star icon"></i>
                  <h3>Customer Trust</h3>
                  <div class="stat">4.7 ★</div>
                  <p class="meta">From 1 review</p>
                  <span class="badge green">Excellent</span>
                </div>

                <!-- GROWTH INSIGHTS -->
                <div class="card">
                  <i class="fa fa-seedling icon"></i>
                  <h3>Growth Tips</h3>
                  <p class="meta">Improve visibility</p>
                  <p class="small">
                    ✔ Encourage ratings<br>
                    ✔ Enable fast delivery<br>
                    ✔ Respond to reviews
                  </p>
                </div>

              </div>
            </div>
          </div>

          <div id="products" class="tab-panel">
            <div class="tab-top">
              <p>Your Products Shelf<br><strong>Manage your listed items <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleProductsAdd(true)">
                <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Product</span>
              </button>

            </div>
            
            <div class="store-page">
              

              <div class="categorical-navigation"> 
                <!-- =================================================
                  COMPANY CATEGORY
                ================================================== --> 
                <div
                    class="category-side company-side"
                    id="companyCategorySide">


                    <button
                        type="button"
                        class="category-crumb"
                        id="companyCategoryButton">

                        <span id="companyCategoryText">
                          HOME ITEMS
                        </span>
                        <span class="category-arrow"></span>
                    </button>


                    <!-- COMPANY POPUP -->

                    <div class="category-popup company-popup">

                        <div class="popup-title">
                          General Categories
                        </div>
                        <?php

                        /*
                        * Your company categories can remain
                        * whatever your existing system uses.
                        */

                        $companyCategories = [
                            'HOME ITEMS',
                            'FASHION',
                            'FOOD',
                            'STATIONERY',
                            'ELECTRONICS',
                            'BEAUTY'
                        ];

                        foreach ($companyCategories as $index => $company):

                        ?>

                            <button
                                type="button"
                                class="category-option <?= $index === 0 ? 'active' : '' ?>"
                                data-company="<?= htmlspecialchars($company) ?>">

                                <?= htmlspecialchars($company) ?>

                            </button>

                        <?php endforeach; ?>
                    </div>

                </div>


                <!-- CHEVRON -->

                <div class="category-separator"></div>


                <!-- =================================================
                      SELLER CUSTOM CATEGORY
                ================================================== -->

                <div
                    class="category-side seller-side"
                    id="sellerCategorySide">

                    <button
                        type="button"
                        class="category-crumb"
                        id="sellerCategoryButton">

                        <span id="sellerCategoryText">
                            All
                        </span>

                        <span class="category-arrow"></span>

                    </button>


                    <!-- SELLER POPUP -->

                    <div class="category-popup seller-popup">
                      <div class="popup-title">
                          Custom Categories
                      </div>
                      <div id="sellerCategoryOptions"></div>
                    </div>

                </div>

              </div>



              <!-- =====================================================
                  MINI NAVIGATION
              ====================================================== -->

              <div class="mini-navigation-wrapper">

                <div class="mini-navigation-scroll">

                    <nav
                      class="mini-navigation"
                      id="miniNavigation">

                      <button type="button" class="mini-nav-item active" data-category-id="all">

                        All

                      </button>

                      <button class="subgrou-add-btn">
                        <i class="fa fa-plus"></i>Add sub group
                      </button>
                      
                      <!-- SLIDING INDICATOR -->
                      <span
                          class="mini-nav-indicator"
                          id="miniNavIndicator">
                      </span>
                    </nav>
                </div>
              </div>
              <!-- =====================================================
                  PRODUCTS HEADER
              ====================================================== -->
              <div class="products-header">

                <h2
                    class="products-title"
                    id="productsTitle">

                    All Products

                </h2>

                <span
                    class="products-count"
                    id="productsCount">

                    0 products

                </span>

              </div>
            </div>

            <!-- PRODUCTS GRID -->
            <div class="products-grid" id="productsGrid">
              <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <div
                    class="card-contain"
                    data-product-id="<?= (int)$product['product_id'] ?>"
                    data-custom-category-id="<?= (int)$product['custom_category_id'] ?>"
                    data-company-category="<?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?>"
                >
                    
                    <div class="card">
                      <img src="<?= htmlspecialchars($product['image_path']) ?>" loading="lazy" decoding="async" alt="<?= htmlspecialchars($product['product_name']) ?>">
                      <div class="card-body">
                        <div class="price buying">KES <?= number_format($product['buying_price'], 2) ?></div>
                        <div class="price">KES <?= number_format($product['selling_price'], 2) ?></div>
                        <div class="perDiv">
                            <?php if (strcasecmp(trim($product['unit']), 'Each') === 0): ?>
                                Each
                            <?php else: ?>
                                Per <?= htmlspecialchars($product['unit']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="stock <?= ((float)$product['stock_quantity'] > 5) ? 'in-stock' : (((float)$product['stock_quantity'] > 0) ? 'low-stock' : 'out-stock') ?>">
                            <?php
                            $stockP = (float)$product['stock_quantity'];

                            if ($stockP >= 100) {

                                echo '<strong>99+</strong>';

                            } elseif ($stockP <= 0) {

                                echo '<strong>0</strong>';

                            } else {

                                // Get the whole-number part
                                $whole = (int)$stockP;

                                // Get the decimal part without rounding the stock itself
                                $decimal = $stockP - $whole;

                                // Convert decimal to quarter
                                if ($decimal >= 0.875) {
                                    $whole++;
                                    $fractionText = '';

                                } elseif ($decimal >= 0.625) {
                                    $fractionText = '¾';

                                } elseif ($decimal >= 0.375) {
                                    $fractionText = '½';

                                } elseif ($decimal >= 0.125) {
                                    $fractionText = '¼';

                                } else {
                                    $fractionText = '';
                                }

                                if ($whole === 0) {
                                    $displayStock = $fractionText ?: '0';
                                } else {
                                    $displayStock = $whole . $fractionText;
                                }

                                echo '<strong>' . htmlspecialchars($displayStock) . '</strong>';
                            }
                            ?>
                        </div>
                      </div>
                      <div class="card-actions">
                        <a href="?edit_product_id=<?= $product['product_id'] ?>" class="edit" >
                          <i class="fa fa-pen"></i>
                        </a>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?')">
                          <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                          <button type="submit" class="delete">
                              <i class="fa fa-trash"></i>
                          </button>
                        </form>
                      </div>
                      
                      <button class="comm-btn">
                        <i class="fas fa-ellipsis-vertical"></i>
                      </button>
                    </div>
                    <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                  </div>
                <?php endforeach; ?>
                <div class="card-contain products-navigation-wrapper" id="productsNavigationWrapper">
                  <div class="products-navigation-card">
                      <div class="navigation-buttons">

                          <button
                              type="button"
                              class="navigation-button"
                              id="previousProducts"
                              aria-label="Previous products"
                          >
                              <svg viewBox="0 0 24 24" fill="none">
                                  <path
                                      d="M19 12H5"
                                      stroke="currentColor"
                                      stroke-width="2"
                                      stroke-linecap="round"
                                  />
                                  <path
                                      d="M11 6L5 12L11 18"
                                      stroke="currentColor"
                                      stroke-width="2"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                  />
                              </svg>
                          </button>

                          <button
                              type="button"
                              class="navigation-button"
                              id="nextProducts"
                              aria-label="Next products"
                          >
                              <svg viewBox="0 0 24 24" fill="none">
                                  <path
                                      d="M5 12H19"
                                      stroke="currentColor"
                                      stroke-width="2"
                                      stroke-linecap="round"
                                  />
                                  <path
                                      d="M13 6L19 12L13 18"
                                      stroke="currentColor"
                                      stroke-width="2"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                  />
                              </svg>
                          </button>

                      </div>

                      <div class="navigation-page" id="navigationPage">
                          Page 1 of 1
                      </div>

                      <div class="navigation-count" id="navigationCount">
                          0 products
                      </div>
                  </div>
                </div>
                <?php else: ?>
                    <p>No products uploaded yet. Click "Add Product" to start selling.</p>
                <?php endif; ?>
              </div>
            </div>
          
          <div id="add-products" class="tab-panel">
            <div class="tab-top">
              <p>Add products to your catalog</em> <br><strong>Show customers what you offer <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToSellerPage()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>

            </div>
            <div class="form-wrapper">
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
                <?php elseif ($success): ?>
                  <p class="successMessage" data-redirect="sellerPage.php">
                    <i class="fa-solid fa-check-circle"></i> <?= $success ?>
                  </p>
                <?php endif; ?>

                <div class="formBody">
                  <div class="inp-box">
                      <label>Product Name</label>
                      <input type="text" name="name" placeholder="Enter name" 
                      value="<?= htmlspecialchars($productName, ENT_QUOTES) ?>" required>
                  </div>

                  <div class="inp-box">
                      <label>Stock Quantity</label>
                      <input type="number" name="stock" placeholder="e.g 24" 
                          value="<?= htmlspecialchars($stock, ENT_QUOTES) ?>" 
                          oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="0" step="1" required>
                  </div>
                  <div class="inp-box">

                    <label>Category</label>
                    <select name="category" required>
                      <option value=""><p>-- Select category --</p></option>
                      <option value="Beauty" <?php echo ($category === 'Beauty') ? 'selected' : ''; ?>>Beauty</option>
                      <option value="Electronics" <?php echo ($category === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                      <option value="Fashions" <?php echo ($category === 'Fashions') ? 'selected' : ''; ?>>Fashions</option>
                      <option value="Food & Snacks" <?php echo ($category === 'Food & Snacks') ? 'selected' : ''; ?>>Food & Snacks</option>
                      <option value="Home Items" <?php echo ($category === 'Home Items') ? 'selected' : ''; ?>>Home Items</option>
                      <option value="Stationery" <?php echo ($category === 'Stationery') ? 'selected' : ''; ?>>Stationery</option>
                    </select>
                  </div>
                  <div class="inp-box" id="customCategoryBox">
                    <label>Group Name (optional)</label>
                    <!-- SELECT MODE -->
                    <div class="custom-category-row" id="customCategorySelectRow">

                    <select
                        name="custom_category_id"
                        id="customCategorySelect"
                        data-selected-category-id="<?= htmlspecialchars((string)($customCategoryId ?? ''), ENT_QUOTES) ?>"
                    >
                        <option value="">-- Select Group Name --</option>
                    </select>

                      <button type="button" id="newCustomCategoryBtn" title="Create new group">
                          <i class="fa-solid fa-plus"></i> Add
                      </button>

                    </div>
                    <!-- INPUT MODE -->
                    <div class="custom-category-row" id="newCustomCategoryRow">

                        <input
                            type="text"
                            name="new_custom_category"
                            id="newCustomCategory"
                            placeholder="e.g. Utensils"
                            maxlength="100"
                            autocomplete="off"
                        >

                        <button type="button" id="cancelNewCustomCategoryBtn">
                            Cancel
                        </button>

                    </div>
                    <!-- MESSAGE -->
                    <small id="customCategoryMessage">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Select a general category first!
                    </small>
                  </div>
                  <div class="inp-box sold-by">

                    <label>Sold by</label>
                    <div class="soldByDiv">

                      <label class="account-type">
                        <input type="radio" name="sale_type" value="Each" <?= ($saleType === 'Each') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Each
                      </label>

                      <label class="account-type">
                        <input type="radio" name="sale_type" value="Measurement" <?= ($saleType === 'Measurement') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Measurement
                      </label>
                    </div>
                    <div class="soldByDiv" id="unitOptions">
                      <label class="account-type">
                        <input type="radio" name="unit" value="Kg" <?= ($unit === 'Kg') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Kgs
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Liter" <?= ($unit === 'Liter') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Liters
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Meter" <?= ($unit === 'Meter') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Meters
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Inch" <?= ($unit === 'Inch') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Inches
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Gram" <?= ($unit === 'Gram') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Grams
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Dozen" <?= ($unit === 'Dozen') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Dozens
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Set" <?= ($unit === 'Set') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Sets
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Plate" <?= ($unit === 'Plate') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Plate
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Cup" <?= ($unit === 'Cup') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Cup
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Gallon" <?= ($unit === 'Gallon') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Gallons
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Roll" <?= ($unit === 'Roll') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Rolls
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="m²" <?= ($unit === 'm²') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Square Meters
                      </label>
                      <label class="account-type">
                        <input type="radio" name="unit" value="Tone" <?= ($unit === 'Tone') ? 'checked' : '' ?>>
                        <div class="radio-dot"></div>
                        Tones
                      </label>
                    </div>
                    
                  </div>

                  <div class="inp-box">
                    <label id="priceLabel">Price (KES)</label>
                    <input
                        type="number"
                        name="bPrice"
                        step="1"
                        placeholder="Buying price"
                        value="<?= htmlspecialchars($buyingPrice, ENT_QUOTES) ?>"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                        min="0"
                        required
                    >
                    <input
                        type="number"
                        name="sPrice"
                        step="1"
                        placeholder="Selling price"
                        value="<?= htmlspecialchars($sellingPrice, ENT_QUOTES) ?>"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                        min="0"
                        required
                    >
                  </div>

                  <?php if ($editMode): ?>
                      <!-- IMAGE PREVIEW ONLY IN EDIT MODE -->
                      <div class="inp-box">
                          <label>Product Image</label>
                          <?php if (!empty($currentImagePath) && file_exists($currentImagePath)): ?>
                            <div class="edit-preview">
                              <img src="<?= htmlspecialchars($currentImagePath) ?>" 
                                style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                              <p>Current Image</p>
                            </div>
                          <?php endif; ?>
                      </div>

                        <div class="inp-box">
                          <label>Change Product Image (optional)</label>

                          <div class="photo-buttons">

                              <!-- CHOOSE FROM DEVICE -->

                              <label
                                  for="galleryPhoto"
                                  class="photo-button"
                              >
                                  📁 Choose from device
                              </label>

                              <div class="or-divider">or</div>

                              <!-- TAKE PHOTO -->

                              <label
                                  id="openCameraButton"
                                  class="photo-button"
                              >
                                📷 Take Photo
                              </label>

                          </div>


                          <!--
                              Normal gallery/file picker.

                              IMPORTANT:
                              This remains name="photo" so PHP receives:

                              $_FILES['photo']
                          -->

                          <input
                              type="file"
                              id="galleryPhoto"
                              name="photo"
                              accept="image/jpeg,image/png,image/webp"
                              hidden
                          >


                          <!-- =====================================================
                                IMAGE PREVIEW
                                ===================================================== -->

                          <div
                              id="photoPreview"
                              class="photo-preview"
                          >

                              <img
                                  id="photoPreviewImage"
                                  src=""
                                  alt="Product image preview"
                              >

                              <div
                                  id="removePhoto"
                                  class="remove-photo"
                              >
                                  <i class="fa fa-trash"></i>
                          </div>

                          </div>


                          <div class="note">

                              400×400 – 1600×1600 px • Max 10MB<br>
                              Automatically optimized for buyers

                          </div>

                        </div>
                      
                  <?php else: ?>
                      <!-- ONLY FOR ADD MODE -->
                        <!-- =========================================================
                            PRODUCT IMAGE
                            ========================================================= -->

                        <div class="inp-box">

                          <label>Upload Product Image</label>

                          <div class="photo-buttons">

                              <!-- CHOOSE FROM DEVICE -->

                              <label
                                  for="galleryPhoto"
                                  class="photo-button"
                              >
                                  📁 Choose from device
                              </label>

                              <div class="or-divider">or</div>

                              <!-- TAKE PHOTO -->

                              <label
                                  id="openCameraButton"
                                  class="photo-button"
                              >
                                📷 Take Photo
                              </label>

                          </div>


                          <!--
                              Normal gallery/file picker.

                              IMPORTANT:
                              This remains name="photo" so PHP receives:

                              $_FILES['photo']
                          -->

                          <input
                              type="file"
                              id="galleryPhoto"
                              name="photo"
                              accept="image/jpeg,image/png,image/webp"
                              hidden
                          >


                          <!-- =====================================================
                                IMAGE PREVIEW
                                ===================================================== -->

                          <div
                              id="photoPreview"
                              class="photo-preview"
                          >

                              <img
                                  id="photoPreviewImage"
                                  src=""
                                  alt="Product image preview"
                              >

                              <div
                                  id="removePhoto"
                                  class="remove-photo"
                              >
                                  <i class="fa fa-trash"></i>
                          </div>

                          </div>


                          <div class="note">

                              400×400 – 1600×1600 px • Max 10MB<br>
                              Automatically optimized for buyers

                          </div>

                        </div>

                  <?php endif; ?>
                  <div></div>

                  <button type="submit">
                    <?= $editMode ? 'Update Product' : 'Add Product' ?>
                  </button>
                </div>


<script>

/* =========================================================
   PRODUCT CAMERA
   ========================================================= */

(function () {

    const galleryPhoto =
        document.getElementById(
            "galleryPhoto"
        );

    const openCameraButton =
        document.getElementById(
            "openCameraButton"
        );

    const closeCameraButton =
        document.getElementById(
            "closeCameraButton"
        );

    const capturePhotoButton =
        document.getElementById(
            "capturePhotoButton"
        );

    const cameraModal =
        document.getElementById(
            "cameraModal"
        );

    const cameraVideo =
        document.getElementById(
            "cameraVideo"
        );

    const cameraCanvas =
        document.getElementById(
            "cameraCanvas"
        );

    const photoPreview =
        document.getElementById(
            "photoPreview"
        );

    const photoPreviewImage =
        document.getElementById(
            "photoPreviewImage"
        );

    const removePhoto =
        document.getElementById(
            "removePhoto"
        );


    let cameraStream = null;

    let selectedProductPhoto = null;

    let previewURL = null;


    const MAX_FILE_SIZE =
        10 * 1024 * 1024;


    /* =====================================================
       GALLERY
       ===================================================== */

    galleryPhoto.addEventListener(
        "change",
        function () {

            const file =
                this.files[0];

            if (!file) {
                return;
            }

            useProductPhoto(file);

        }
    );


    /* =====================================================
       OPEN CAMERA
       ===================================================== */

    openCameraButton.addEventListener(
        "click",
        async function () {

            /*
             * Make sure the browser supports
             * camera access.
             */

            if (
                !navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia
            ) {

                alert(
                    "Your browser does not support camera access. Please choose a photo from your device."
                );

                return;

            }


            try {

                cameraStream =
                    await navigator.mediaDevices
                        .getUserMedia({

                            video: {

                                facingMode: {
                                    ideal: "environment"
                                },

                                /*
                                 * Do NOT request
                                 * a huge camera resolution.
                                 *
                                 * We only need a good
                                 * product image.
                                 */

                                width: {
                                    ideal: 1280
                                },

                                height: {
                                    ideal: 960
                                }

                            },

                            audio: false

                        });


                cameraVideo.srcObject =
                    cameraStream;


                cameraModal.classList.add(
                    "active"
                );


                await cameraVideo.play();


            } catch (error) {

                console.error(
                    "Camera error:",
                    error
                );


                alert(
                    "Unable to access the camera. Please allow camera permission or choose a photo from your device."
                );

            }

        }
    );


    /* =====================================================
       CAPTURE PHOTO
       ===================================================== */

    capturePhotoButton.addEventListener(
        "click",
        function () {

            if (
                !cameraStream ||
                !cameraVideo.videoWidth
            ) {

                return;

            }


            /*
             * IMPORTANT:
             *
             * Limit the captured image.
             *
             * We do NOT save the camera's
             * full native resolution.
             */

            const MAX_WIDTH = 1280;

            const MAX_HEIGHT = 1280;


            let width =
                cameraVideo.videoWidth;

            let height =
                cameraVideo.videoHeight;


            const ratio =
                Math.min(
                    MAX_WIDTH / width,
                    MAX_HEIGHT / height,
                    1
                );


            width =
                Math.round(
                    width * ratio
                );

            height =
                Math.round(
                    height * ratio
                );


            cameraCanvas.width =
                width;

            cameraCanvas.height =
                height;


            const context =
                cameraCanvas.getContext(
                    "2d",
                    {
                        alpha: false
                    }
                );


            context.drawImage(
                cameraVideo,
                0,
                0,
                width,
                height
            );


            /*
             * Convert directly to JPEG.
             *
             * 80 quality gives a good
             * product image while keeping
             * memory/file size reasonable.
             */

            cameraCanvas.toBlob(
                function (blob) {

                    if (!blob) {

                        alert(
                            "Unable to capture the photo. Please try again."
                        );

                        return;

                    }


                    const file =
                        new File(
                            [blob],
                            "product-camera.jpg",
                            {
                                type:
                                    "image/jpeg",
                                lastModified:
                                    Date.now()
                            }
                        );


                    useProductPhoto(file);


                    closeCamera();


                },

                "image/jpeg",

                0.80

            );

        }
    );


    /* =====================================================
       USE PRODUCT PHOTO
       ===================================================== */

    function useProductPhoto(file) {


        if (
            ![
                "image/jpeg",
                "image/png",
                "image/webp"
            ].includes(file.type)
        ) {

            alert(
                "Please select a JPG, PNG or WEBP image."
            );

            return;

        }


        if (
            file.size >
            MAX_FILE_SIZE
        ) {

            alert(
                "The image must not exceed 10MB."
            );

            return;

        }


        /*
         * Store selected file.
         */

        selectedProductPhoto =
            file;


        /*
         * Release previous preview.
         */

        if (previewURL) {

            URL.revokeObjectURL(
                previewURL
            );

        }


        /*
         * Lightweight preview.
         */

        previewURL =
            URL.createObjectURL(
                file
            );


        photoPreviewImage.src =
            previewURL;


        photoPreview.style.display =
            "block";


        /*
         * For gallery images, the actual
         * input already contains the file.
         *
         * For camera images, selectedProductPhoto
         * is used during form submission.
         */

    }


    /* =====================================================
       CLOSE CAMERA
       ===================================================== */

    closeCameraButton.addEventListener(
        "click",
        closeCamera
    );


    function closeCamera() {

        if (cameraStream) {

            cameraStream
                .getTracks()
                .forEach(
                    function (track) {

                        track.stop();

                    }
                );

            cameraStream = null;

        }


        cameraVideo.srcObject =
            null;


        cameraModal.classList.remove(
            "active"
        );

    }


    /* =====================================================
       REMOVE PHOTO
       ===================================================== */

    removePhoto.addEventListener(
        "click",
        function () {

            selectedProductPhoto =
                null;

            galleryPhoto.value =
                "";

            if (previewURL) {

                URL.revokeObjectURL(
                    previewURL
                );

                previewURL = null;

            }

            photoPreviewImage.src =
                "";

            photoPreview.style.display =
                "none";

        }
    );


    /* =====================================================
       FORM SUBMISSION
       =====================================================

       IMPORTANT:

       Your form currently submits normally.

       We intercept it only to ensure that a
       camera-captured File is sent as:

           $_FILES['photo']

       ===================================================== */

    const productForm =
        galleryPhoto.closest("form");


    if (productForm) {

        productForm.addEventListener(
            "submit",
            function (event) {

                /*
                 * If the seller selected from
                 * the gallery, the normal form
                 * submission already contains
                 * name="photo".
                 */

                if (
                    !selectedProductPhoto
                ) {

                    return;

                }


                /*
                 * If selectedProductPhoto is
                 * exactly the file already in
                 * galleryPhoto, let the normal
                 * browser submission continue.
                 */

                if (
                    galleryPhoto.files.length &&
                    galleryPhoto.files[0] ===
                    selectedProductPhoto
                ) {

                    return;

                }


                /*
                 * Camera photo needs to be
                 * submitted manually.
                 */

                event.preventDefault();


                const formData =
                    new FormData(
                        productForm
                    );


                /*
                 * Replace/add the photo.
                 */

                formData.set(
                    "photo",
                    selectedProductPhoto,
                    selectedProductPhoto.name
                );


                /*
                 * Submit to the same PHP page.
                 */

                fetch(
                    productForm.action ||
                    window.location.href,
                    {
                        method: "POST",
                        body: formData
                    }
                )
                .then(
                    function (response) {

                        /*
                         * Your existing PHP
                         * returns the normal
                         * seller page HTML.
                         */

                        return response.text();

                    }
                )
                .then(
                    function (html) {

                        /*
                         * Replace the page with
                         * PHP's normal response.
                         */

                        document.open();

                        document.write(html);

                        document.close();

                    }
                )
                .catch(
                    function (error) {

                        console.error(
                            "Product upload error:",
                            error
                        );

                        alert(
                            "Unable to add the product. Please try again."
                        );

                    }
                );

            }
        );

    }


    /* =====================================================
       CLEANUP
       ===================================================== */

    window.addEventListener(
        "beforeunload",
        function () {

            closeCamera();

            if (previewURL) {

                URL.revokeObjectURL(
                    previewURL
                );

            }

        }
    );

})();

</script>

            </form>
            </div>
          </div><!-- 
          
          <div id="funds" class="tab-panel">
            <p>Access your earnings</em> <br><strong>Withdraw funds you’ve earned from completed sales <i class="fa-regular fa-circle-check"></i></strong></p>
            
            <div class="form-wrapper">
              <form method="POST" enctype="multipart/form-data">
                <h1>Withdraw Funds</h1>
                <?php if (!empty($error)): ?>
                  <p class="errorMessage usrWlt">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error); ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                  <p class="successMessage usrWlt" data-redirect="sellerPage.php">
                    <i class="fa-solid fa-check-circle"></i> <?= $success ?>
                  </p>

                  <script>
                    showNotification(
                      `<i class="fa-solid fa-check-circle"></i> <?= addslashes($success) ?>`,
                      4000
                    );
                  </script>
                <?php endif; ?>

                <input type="hidden" name="withdraw_wallet" value="seller">

                <div class="formBody active">
                  <div class="card">
                    <i class="fa fa-wallet icon"></i>
                    <h3>Wallet Health</h3>

                    <div class="stat">KES <?= number_format($walletBalance, 2) ?></div>
                    <p class="meta">Available for withdrawal</p>

                    <div class="progress">
                      <span style="width:<?= min(($walletBalance/20000)*100,100) ?>%"></span>
                    </div>

                    <p class="small">KES 0 pending clearance</p>
                  </div>

                  <div>
                    <div class="inp-box">
                      <label>Withdrawal Amount</label>
                      <input type="number" name="withdraw_sales_amount" placeholder="Enter amount" min="0" required>
                      <button type="submit">Request Withdrawal</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div> -->
        </div>
      </div>

      <h1>Most Recent Orders</h1>

      <div class="filter-bar">
        <select id="statusFilter">
          <option value="all">All Orders</option>
          <option value="pending">Pending</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
        </select>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="table-wrapper sellerOrdersTrack">

        <table id="ordersTable">
          <thead>
            <tr>
              <th>Order</th>
              <th>Product</th>
              <th>Item&nbsp;Qty</th>
              <th>Total</th>
              <th>Buyer</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Actions</th>
              <th>Paid&nbsp;by</th>
              <th>Receipt</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if (!empty($sellerOrders)) {
              $count = 1;
              foreach ($sellerOrders as $order) {
                  // -----------------------------------------
                  // PRODUCT IMAGES
                  // -----------------------------------------

                  $productImages = [];

                  if (!empty($order['product_images'])) {

                      $images = explode('|||', $order['product_images']);

                      // Remove empty values
                      $images = array_filter($images);

                      // Maximum of 3 images
                      $productImages = array_slice($images, 0, 3);
                  }

                  // Default image
                  $defaultImage = "Images/Makethub Logo.png";
                  $imageHTML = '<div class="order-product-images">';

                  foreach ($productImages as $image) {

                      $image = trim($image);

                      if (
                          empty($image) ||
                          !file_exists($image)
                      ) {
                          $image = $defaultImage;
                      }

                      $image = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');

                      $imageHTML .= "
                          <img
                              src=\"{$image}\"
                              alt=\"Product\"
                              loading=\"lazy\"
                          >
                      ";
                  }

                  $imageHTML .= '</div>';
                  $productCount = (int)$order['product_count'];

                  $total = number_format($order['seller_total'], 2);
                  $date = formatDate($order['created_at']);

                  // Payment badge
                  $paymentStatus = strtolower($order['payment_status'] ?? '');
                  $paymentClass = $paymentStatus === 'paid' ? 'paid' : 'pending';
                  $paymentLabel = ucfirst($paymentStatus ?: 'Pending');

                  // Order status badge
                  $statusClass = strtolower($order['order_status'] ?? '');
                  $statusLabel = ucfirst($order['order_status'] ?? 'Pending');

                  // Optional tooltip for shipped/delivered timestamps
                  $statusTooltip = '';
                  if (!empty($order['shipped_at'])) {
                      $statusTooltip .= "Shipped: " . date("d M Y H:i", strtotime($order['shipped_at']));
                  }
                  if (!empty($order['delivered_at'])) {
                      if ($statusTooltip) $statusTooltip .= "\n";
                      $statusTooltip .= "Delivered: " . date("d M Y H:i", strtotime($order['delivered_at']));
                  }
                  
                  // Product image
                  $productImage = !empty($order['image_path']) && file_exists($order['image_path']) 
                                  ? htmlspecialchars($order['image_path']) 
                                  : "Images/Makethub Logo.png"; // default image

                  echo "<tr data-status=\"{$order['order_status']}\">
                          <td>
                            <div class='newStylOrd'>
                              #{$order['order_code']}<p>{$date}</p>
                            </div>
                          </td>
                          <td>{$imageHTML}</td>
                          <td>{$productCount}</td>
                          <td>KES {$total}</td>
                          <td>".htmlspecialchars(ucwords(strtolower($order['buyer_name'])))."</td>
                          <td><span class='badge {$paymentClass}'>{$paymentLabel}</span></td>
                          <td><span class='badge {$statusClass}' title=\"".htmlspecialchars($statusTooltip)."\">{$statusLabel}</span></td>
                          <td class='actions'>
                        <div>";

                  // Action based on status
                  if ($statusClass === 'pending') {
                    echo "<button class='btn-ship' data-id='{$order['order_id']}'>Mark&nbsp;as&nbsp;Shipped</button>";
                  } else {
                    echo "<button class='btn-view' 
                            data-buyer='{$order['buyer_id']}'
                            data-order='{$order['order_code']}'
                            data-buyername='".htmlspecialchars($order['buyer_name'], ENT_QUOTES)."'>
                            <i class='fa-solid fa-eye'></i>
                          </button>";
                  }

                  echo "      </div>
                          </td>
                          <td>".htmlspecialchars(ucfirst($order['payment_method'] ?? 'Unknown'))."</td>
                          <td><div id='receiptTd'><i class='fa-solid fa-receipt'></i></div></td>
                        </tr>";
                  $count++;
              }
          } else {
            // Display message when no data
            echo "<tr>
                    <td colspan='10' style='text-align:center; color:#888;'>No data available in table</td>
                  </tr>";
            }
          ?>
          </tbody>
        </table>
      </div>
      
      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleSellerOrdersTrack()">View&nbsp;All&nbsp;Orders</button> to access all your orders.</p>

    </main>
    

    <main class="buyerMain" id="salesDashMain">
      <div class="tab-top">
        <p>Sale<br><strong>Tap to sale <i class="fa-solid fa-hand-pointer"></i></strong></p>
        <div class="salesSideDiv">
          <div class="salesCounter">
            <i class="fa-solid fa-receipt"></i>
            <p>0</p>
          </div>
          <button onclick="toggleSalesDash()">
            <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
          </button>
        </div>
      </div>
      <div class="sales-wrapper">
            
            <div class="store-page">
              

              <div class="categorical-navigation"> 
                <!-- =================================================
                      SELLER CUSTOM CATEGORY
                ================================================== -->

                <div
                    class="category-side seller-side"
                    id="sellerCategorySide">
                    <button
                        type="button"
                        class="category-crumb"
                        id="sellerCategoryButton">

                        <span id="sellerCategoryText">
                            Utensils
                        </span>

                        <span class="category-arrow"></span>

                    </button>


                    <!-- SELLER POPUP -->

                    <div class="category-popup seller-popup">

                        <div class="popup-title">
                            Custom Categories
                        </div>


                        <button
                            type="button"
                            class="category-option active"
                            data-seller="Utensils">

                            Utensils

                        </button>


                        <button
                            type="button"
                            class="category-option"
                            data-seller="Zippers">

                            Zippers

                        </button>


                        <button
                            type="button"
                            class="category-option"
                            data-seller="Mattresses">

                            Mattresses

                        </button>


                        <button
                            type="button"
                            class="category-option"
                            data-seller="Shoes">

                            Shoes

                        </button>


                        <button
                            type="button"
                            class="category-option"
                            data-seller="Bags">

                            Bags

                        </button>


                        <button
                            type="button"
                            class="category-option"
                            data-seller="Lexines">

                            Lexines

                        </button>

                    </div>

                </div>

              </div>



              <!-- =====================================================
                  MINI NAVIGATION
              ====================================================== -->

              <div class="mini-navigation-wrapper">

                <div class="mini-navigation-scroll">

                    <nav
                        class="mini-navigation"
                        id="miniNavigation">


                        <button
                            type="button"
                            class="mini-nav-item active"
                            data-mini="all">

                            All

                        </button>


                        <button
                            type="button"
                            class="mini-nav-item"
                            data-mini="sufurias">

                            Sufurias

                        </button>
                        <button
                            type="button"
                            class="mini-nav-item"
                            data-mini="basins">
                            Basins
                        </button>
                        <button
                            type="button"
                            class="mini-nav-item"
                            data-mini="knives">
                            Knives
                        </button>
                        <button
                            type="button"
                            class="mini-nav-item"
                            data-mini="stands">
                            Stands
                        </button>
                        <button
                            type="button"
                            class="mini-nav-item"
                            data-mini="cups">
                            Cups
                        </button>
                        <button
                            type="button"
                            class="mini-nav-item"
                            data-mini="plates">
                            Plates
                        </button>
                        <button
                            type="button"
                            class="mini-nav-item"
                            data-mini="spoons">
                            Spoons
                        </button>
                        
                        <!-- SLIDING INDICATOR -->
                        <span
                            class="mini-nav-indicator"
                            id="miniNavIndicator">
                        </span>
                    </nav>
                </div>
              </div>
            </div>

        <?php
        $userId = $_SESSION['user_id'] ?? 0;

        $sql = "
            SELECT
                product_id,
                product_name,
                selling_price,
                stock_quantity,
                unit,
                image_path
            FROM productservicesrentals
            WHERE user_id = ?
              AND status = 'active'
            ORDER BY product_id DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        ?>

        <?php if ($result->num_rows === 0): ?>

          <p>No products here. Click "Go Back" to update your store.</p>

        <?php endif; ?>


        <div class="offer-container">

          <div class="sales-grid">

            <?php while ($product = $result->fetch_assoc()): ?>

              <?php
              $productId = (int)$product['product_id'];

              $productName = htmlspecialchars(
                  $product['product_name'],
                  ENT_QUOTES,
                  'UTF-8'
              );

              $price = (float)$product['selling_price'];
              $stock = (float)$product['stock_quantity'];

              $unit = htmlspecialchars(
                  $product['unit'] ?? 'Each',
                  ENT_QUOTES,
                  'UTF-8'
              );

              $imagePath = !empty($product['image_path'])
                  ? htmlspecialchars(
                      $product['image_path'],
                      ENT_QUOTES,
                      'UTF-8'
                  )
                  : 'Images/Makethub Logo.png';

              $displayStock = rtrim(
                  rtrim(number_format($stock, 2, '.', ''), '0'),
                  '.'
              );

              if ($stock <= 0) {
                  $stockClass = 'out-stock';
              } elseif ($stock <= 5) {
                  $stockClass = 'low-stock';
              } else {
                  $stockClass = 'in-stock';
              }
              ?>


              <div
                class="cardContainer"
                data-product-id="<?= $productId ?>"
                data-product-name="<?= $productName ?>"
                data-price="<?= $price ?>"
                data-stock="<?= $stock ?>"
                data-available-stock="<?= $stock ?>"
                data-unit="<?= $unit ?>"
              >

                <div class="productSalesCard">

                  <img
                    src="<?= $imagePath ?>"
                    alt="<?= $productName ?>"
                  >

                  <strong class="stock <?= $stockClass ?>">
                    <?= $displayStock ?>
                  </strong>
                </div>

                <div class="adjust-popup">

                  <div class="adjust-buttons">

                    <button type="button" class="adjust-btn minus" data-value="-1">
                      -1
                    </button>

                    <button type="button" class="adjust-btn minus" data-value="-0.5">
                      -½
                    </button>

                    <button type="button" class="adjust-btn minus" data-value="-0.25">
                      -¼
                    </button>

                    <button type="button" class="adjust-btn zero" data-value="0">
                      0
                    </button>

                    <button type="button" class="adjust-btn plus" data-value="0.25">
                      +¼
                    </button>

                    <button type="button" class="adjust-btn plus" data-value="0.5">
                      +½
                    </button>

                    <button type="button" class="adjust-btn plus" data-value="1">
                      +1
                    </button>

                  </div>


                  <div class="current-adjustment">

                    <span class="quantity-value">0</span>

                    <span class="quantity-unit">
                      <?= $unit ?>
                    </span>

                  </div>


                  <button type="button" class="add-list-btn">
                    Add to list
                  </button>

                </div>

              </div>

            <?php endwhile; ?>
            

                  <div class="products-navigation-card salesPageNav">
                    <div class="navigation-buttons">

                        <!-- PREVIOUS -->

                        <button
                            type="button"
                            class="navigation-button"
                            id="previousProducts"
                            aria-label="Previous products"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                            >

                                <path
                                    d="M19 12H5"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />

                                <path
                                    d="M11 6L5 12L11 18"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                            </svg>

                        </button>


                        <!-- NEXT -->

                        <button
                            type="button"
                            class="navigation-button"
                            id="nextProducts"
                            aria-label="Next products"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                            >

                                <path
                                    d="M5 12H19"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                                <path
                                    d="M13 6L19 12L13 18"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                            </svg>

                        </button>

                    </div>
                    <div class="navigation-page" id="navigationPage">Page 1 of 2</div>
                  </div>

          </div>
          <div class="fSales-container">
            <form class="cardFSales">
              <div class="card-title">Checkout List
                <a class="reset-btn">
                  <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
              </div>
              <div
                id="checkoutError"
                class="checkout-error"
              ></div>
              <p class="emptyListP"><i class="fa-solid fa-battery-empty"></i> Click on a product to sale!...</p>

              <div id="checkoutItems" class="checkout-items">
              </div>

              <div class="summary-row items-total ksh">
                <span>Items Total</span>
                <span id="itemsTotal ksh">KES 0.00</span>
              </div>

              <div class="summary-row ksh">
                <span>Delivery Fees</span>
                <span>0</span>
              </div>

              <div class="summary-row ksh">
                <span>Promotions</span>
                <span>0</span>
              </div>

              <div class="summary-row total">
                <span>Total</span>
                <span id="finalTotal">KES 0.00</span>
              </div>

              <div class="payMethodDiv">
                <label>Paid by</label>
                <div class="paidByDiv">

                  <label class="money-method">
                    <input type="radio" name="pmethod" value="cash" <?= ($saleType === 'cash') ? 'checked' : '' ?>>
                    <div class="radio-dot"></div>
                    Cash
                  </label>
                  <label class="money-method">
                    <input type="radio" name="pmethod" value="bank" <?= ($saleType === 'bank') ? 'checked' : '' ?>>
                    <div class="radio-dot"></div>
                    Bank
                  </label>
                </div>
              </div>
              <button
                type="button"
                id="checkoutButton"
                class="checkout-order"
                onclick="checkOutOrder()">

                Checkout
                <span id="checkoutTotal">
                    KES 0.00
                </span>

              </button>
            </form>
          </div>

        </div>

        <?php $stmt->close(); ?>

      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleSalesDash()">Go&nbsp;back</button> to continue your dashboard.</p>
    </main>

    <main class="buyerMain" id="chatSection">
      <div class="tab-top">
        <p>Track customer for delivery<br><strong>Get to chat with customer using our in-built chat feature <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="goBack()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>

      <div class="chat-wrapper">

        <!-- HEADER -->
        <div class="chat-header">
          <h3 id="chatTitle">Order Chat • Seller: Alex</h3>
          <div class="meta" id="chatOrderCode">Order Code: ORD-XXXX</div>
          <div class="status" id="orderStatus">Order in progress...</div>
        </div>

        <!-- CHAT BODY -->
        <div class="chat-body" id="chatBody">

          <!-- Seller Message -->
          <div class="chat-message seller">
            <div class="bubble">
              Hello 👋 I’m ready to deliver your order.
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

    </main>

    <main class="buyerMain" id="ordersTrackMain">
      <div class="tab-top">
        <p>Track customer orders<br><strong>Monitor order status easily <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleSellerOrdersTrack()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>
      <div class="table-wrapper sellerOrdersTrack">
        <table id="sellerTransactions">
          <thead>
            <tr>
              <th>Order</th>
              <th>Product</th>
              <th>Itme&nbsp;Qty</th>
              <th>Total</th>
              <th>Buyer</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Actions</th>
              <th>Paid&nbsp;by</th>
              <th>Receipt</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if (!empty($sellerOrders)) {
              $count = 1;
              foreach ($sellerOrders as $order) {
                  // -----------------------------------------
                  // PRODUCT IMAGES
                  // -----------------------------------------

                  $productImages = [];

                  if (!empty($order['product_images'])) {

                      $images = explode('|||', $order['product_images']);

                      // Remove empty values
                      $images = array_filter($images);

                      // Maximum of 3 images
                      $productImages = array_slice($images, 0, 3);
                  }

                  // Default image
                  $defaultImage = "Images/Makethub Logo.png";
                  $imageHTML = '<div class="order-product-images">';

                  foreach ($productImages as $image) {

                      $image = trim($image);

                      if (
                          empty($image) ||
                          !file_exists($image)
                      ) {
                          $image = $defaultImage;
                      }

                      $image = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');

                      $imageHTML .= "
                          <img
                              src=\"{$image}\"
                              alt=\"Product\"
                              loading=\"lazy\"
                          >
                      ";
                  }

                  $imageHTML .= '</div>';
                  $productCount = (int)$order['product_count'];

                  $total = number_format($order['seller_total'], 2);
                  $date = formatDate($order['created_at']);

                  // Payment badge
                  $paymentStatus = strtolower($order['payment_status'] ?? '');
                  $paymentClass = $paymentStatus === 'paid' ? 'paid' : 'pending';
                  $paymentLabel = ucfirst($paymentStatus ?: 'Pending');

                  // Order status badge
                  $statusClass = strtolower($order['order_status'] ?? '');
                  $statusLabel = ucfirst($order['order_status'] ?? 'Pending');

                  // Optional tooltip for shipped/delivered timestamps
                  $statusTooltip = '';
                  if (!empty($order['shipped_at'])) {
                      $statusTooltip .= "Shipped: " . date("d M Y H:i", strtotime($order['shipped_at']));
                  }
                  if (!empty($order['delivered_at'])) {
                      if ($statusTooltip) $statusTooltip .= "\n";
                      $statusTooltip .= "Delivered: " . date("d M Y H:i", strtotime($order['delivered_at']));
                  }
                  
                  // Product image
                  $productImage = !empty($order['image_path']) && file_exists($order['image_path']) 
                                  ? htmlspecialchars($order['image_path']) 
                                  : "Images/Makethub Logo.png"; // default image

                  echo "<tr data-status=\"{$order['order_status']}\">
                          <td>
                            <div class='newStylOrd'>
                              {$order['order_code']}<p>{$date}</p>
                            </div>
                          </td>
                          <td>{$imageHTML}</td>
                          <td>{$productCount}</td>
                          <td>KES {$total}</td>
                          <td>".htmlspecialchars(ucwords(strtolower($order['buyer_name'])))."</td>
                          <td><span class='badge {$paymentClass}'>{$paymentLabel}</span></td>
                          <td><span class='badge {$statusClass}' title=\"".htmlspecialchars($statusTooltip)."\">{$statusLabel}</span></td>
                          <td class='actions'>
                        <div>";

                  // Action based on status
                  if ($statusClass === 'pending') {
                    echo "<button class='btn-ship' data-id='{$order['order_id']}'>Mark&nbsp;as&nbsp;Shipped</button>";
                  } else {
                    echo "<button class='btn-view' 
                            data-buyer='{$order['buyer_id']}'
                            data-order='{$order['order_code']}'
                            data-buyername='".htmlspecialchars($order['buyer_name'], ENT_QUOTES)."'>
                            <i class='fa-solid fa-eye'></i>
                          </button>";
                  }

                  echo "      </div>
                          </td>
                          <td>".htmlspecialchars(ucfirst($order['payment_method'] ?? 'Unknown'))."</td>
                          <td><div id='receiptTd'><i class='fa-solid fa-barcode'></i></div></td>
                        </tr>";
                  $count++;
              }
          } else {
            // Display message when no data
            echo "<tr>
                    <td colspan='10' style='text-align:center; color:#888;'>No data available in table</td>
                  </tr>";
            }
          ?>
          </tbody>
        </table>
      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleSellerOrdersTrack()">Go&nbsp;back</button> to continue delivering.</p>
    </main>
    <footer>
      <p>&copy; 2025/2026, Makethub.shop, All Rights Reserved.</p><br>
      <p>
        <a href="privacy.php">Privacy Policy</a> |
        <a href="terms.php">Terms & Conditions</a> |
        <a href="contact.php">Contact Us</a>
      </p>
    </footer>

  <!-- Notification container -->
  <div id="notification-container"></div>
<script>
/* =========================================================
   MARKET HUB
   DYNAMIC PRODUCT / CATEGORY NAVIGATION
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       PHP DATABASE DATA
       ===================================================== */

    const products = <?= $productJson ?: '[]' ?>;
    const customCategories = <?= $categoryJson ?: '[]' ?>;


    /* =====================================================
       CONFIGURATION
    ===================================================== */

    const PRODUCTS_PER_PAGE = 11;


    /* =====================================================
       ELEMENTS
    ===================================================== */

    const storePage =
        document.querySelector(".store-page");

    const companySide =
        document.getElementById("companyCategorySide");

    const sellerSide =
        document.getElementById("sellerCategorySide");

    const companyButton =
        document.getElementById("companyCategoryButton");

    const sellerButton =
        document.getElementById("sellerCategoryButton");

    const companyText =
        document.getElementById("companyCategoryText");

    const sellerText =
        document.getElementById("sellerCategoryText");

    const companyPopup =
        document.querySelector(".company-popup");

    const sellerPopup =
        document.querySelector(".seller-popup");

    const miniNavigation =
        document.getElementById("miniNavigation");

    const productsTitle =
        document.getElementById("productsTitle");

    const productsCount =
        document.getElementById("productsCount");

    const productGrid =
        document.querySelector(".products-grid");

    const previousProducts =
        document.getElementById("previousProducts");

    const nextProducts =
        document.getElementById("nextProducts");

    const navigationPage =
        document.getElementById("navigationPage");

    const navigationCount =
        document.getElementById("navigationCount");


    /* =====================================================
       SAFETY CHECK
    ===================================================== */

    if (!storePage || !productGrid) {
        return;
    }


    /* =====================================================
       STORE EMPTY CHECK
       
       If seller has NO products at all:
       hide the complete store page.
    ===================================================== */

    if (!Array.isArray(products) || products.length === 0) {

        storePage.style.display = "none";

        return;
    }


    /* =====================================================
       STATE
    ===================================================== */

    let selectedCompanyCategory = null;

    let selectedCustomCategory = null;

    let selectedSubCategory = null;

    let currentProducts = [];

    let currentPage = 1;


    /* =====================================================
       HELPERS
    ===================================================== */

    function normalize(value) {

        return String(value ?? "")
            .trim()
            .toLowerCase();

    }


    /* =====================================================
       DISPLAY NAME
       
       Company categories:
       ALL UPPERCASE
       
       Custom groups / subgroups:
       First letter uppercase,
       remaining letters lowercase.
    ===================================================== */

    function formatCompanyName(name) {

        return String(name ?? "")
            .trim()
            .toUpperCase();

    }


    function formatNormalName(name) {

        const value =
            String(name ?? "")
                .trim()
                .toLowerCase();

        if (!value) {
            return "";
        }

        return value.charAt(0).toUpperCase() +
               value.slice(1);

    }


    /* =====================================================
       GET ROOT CUSTOM GROUPS
       
       parent_id = NULL / 0
       
       These belong directly to a company category.
    ===================================================== */

    function getRootCustomGroups(companyCategory) {

        const company =
            normalize(companyCategory);

        return customCategories.filter(category => {

            const sameCompany =
                normalize(category.company_category) === company;

            const parent =
                category.parent_id;

            const isRoot =
                parent === null ||
                parent === undefined ||
                parent === "" ||
                Number(parent) === 0;

            return sameCompany && isRoot;

        });

    }


    /* =====================================================
       GET SUBGROUPS
    ===================================================== */

    function getSubGroups(parentId, companyCategory) {

        const company =
            normalize(companyCategory);

        return customCategories.filter(category => {

            return (
                normalize(category.company_category) === company &&
                Number(category.parent_id) === Number(parentId)
            );

        });

    }


    /* =====================================================
       GET PRODUCTS FOR CUSTOM CATEGORY
       
       Includes products directly attached to that
       custom category.
    ===================================================== */

    function getProductsForCustomCategory(categoryId) {

        return products.filter(product => {

            return Number(product.custom_category_id) ===
                   Number(categoryId);

        });

    }


    /* =====================================================
       GET PRODUCTS FOR COMPANY CATEGORY
       
       When "All" is selected at custom-group level,
       display all products belonging to that company
       category.
    ===================================================== */

    function getProductsForCompanyCategory(companyCategory) {

        const company =
            normalize(companyCategory);

        return products.filter(product => {

            return normalize(product.category) === company;

        });

    }


    /* =====================================================
       IMPORTANT:
       A CUSTOM GROUP SHOULD NOT DISPLAY IF IT HAS NO
       PRODUCTS.
       
       A GROUP is considered to have products if:
       
       1. Products are directly attached to it
       OR
       2. Its subgroups contain products.
    ===================================================== */

    function categoryHasProducts(category) {

        const directProducts =
            getProductsForCustomCategory(
                category.custom_category_id
            );

        if (directProducts.length > 0) {
            return true;
        }


        const children =
            getSubGroups(
                category.custom_category_id,
                category.company_category
            );

        for (const child of children) {

            if (categoryHasProducts(child)) {
                return true;
            }

        }

        return false;
    }


    /* =====================================================
       BUILD COMPANY CATEGORY LIST
       
       Only company categories that actually have products
       are displayed.
    ===================================================== */

    function getAvailableCompanyCategories() {

        const companies = [];

        products.forEach(product => {

            const category =
                String(product.category ?? "").trim();

            if (!category) {
                return;
            }

            const exists =
                companies.some(existing =>
                    normalize(existing) === normalize(category)
                );

            if (!exists) {
                companies.push(category);
            }

        });

        return companies;

    }


    /* =====================================================
       BUILD COMPANY POPUP
    ===================================================== */

    function buildCompanyPopup() {

        if (!companyPopup) {
            return;
        }

        companyPopup.innerHTML = "";

        const title =
            document.createElement("div");

        title.className = "popup-title";
        title.textContent = "General Categories";

        companyPopup.appendChild(title);


        const companies =
            getAvailableCompanyCategories();


        companies.forEach((company, index) => {

            const button =
                document.createElement("button");

            button.type = "button";
            button.className = "category-option";

            button.dataset.company = company;

            button.textContent =
                formatCompanyName(company);

            if (
                normalize(company) ===
                normalize(selectedCompanyCategory)
            ) {

                button.classList.add("active");

            }

            companyPopup.appendChild(button);

        });

    }


    /* =====================================================
       BUILD SELLER CUSTOM GROUP POPUP
       
       Only groups having products are displayed.
    ===================================================== */

    function buildSellerPopup() {

        if (!sellerPopup) {
            return;
        }

        sellerPopup.innerHTML = "";

        const title =
            document.createElement("div");

        title.className = "popup-title";
        title.textContent = "Custom Categories";

        sellerPopup.appendChild(title);


        if (!selectedCompanyCategory) {
            return;
        }


        const groups =
            getRootCustomGroups(
                selectedCompanyCategory
            );


        groups.forEach(group => {

            /*
             * Do not display empty groups.
             */
            if (!categoryHasProducts(group)) {
                return;
            }


            const button =
                document.createElement("button");

            button.type = "button";

            button.className =
                "category-option";

            button.dataset.seller =
                group.custom_category_id;

            button.dataset.categoryName =
                group.name;

            button.textContent =
                formatNormalName(group.name);

            if (
                Number(selectedCustomCategory) ===
                Number(group.custom_category_id)
            ) {

                button.classList.add("active");

            }


            sellerPopup.appendChild(button);

        });

    }


    /* =====================================================
       BUILD MINI NAVIGATION
       
       IMPORTANT:
       "All" is ALWAYS present and ACTIVE.
       
       It displays:
       - Direct products of the selected group
       - Products in its subgroups
       
       Subgroups with no products are hidden.
    ===================================================== */

    function buildMiniNavigation(customCategoryId) {

        if (!miniNavigation) {
            return;
        }

        miniNavigation.innerHTML = "";


        /* ================================================
           ALL BUTTON
        ================================================= */

        const allButton =
            document.createElement("button");

        allButton.type = "button";

        allButton.className =
            "mini-nav-item active";

        allButton.dataset.mini =
            "all";

        allButton.textContent =
            "All";

        miniNavigation.appendChild(
            allButton
        );


        /* ================================================
           FIND SELECTED GROUP
        ================================================= */

        const selectedGroup =
            customCategories.find(category =>
                Number(category.custom_category_id) ===
                Number(customCategoryId)
            );


        if (selectedGroup) {

            const subGroups =
                getSubGroups(
                    selectedGroup.custom_category_id,
                    selectedGroup.company_category
                );


            subGroups.forEach(subGroup => {

                /*
                 * Hide empty subgroup.
                 */
                if (!categoryHasProducts(subGroup)) {
                    return;
                }


                const button =
                    document.createElement("button");

                button.type = "button";

                button.className =
                    "mini-nav-item";

                button.dataset.mini =
                    subGroup.custom_category_id;

                button.dataset.categoryId =
                    subGroup.custom_category_id;

                button.textContent =
                    formatNormalName(
                        subGroup.name
                    );

                miniNavigation.appendChild(
                    button
                );

            });

        }


        /* ================================================
           ADD SUB GROUP BUTTON
           
           ALWAYS VISIBLE
        ================================================= */

        const addButton =
            document.createElement("button");

        addButton.type = "button";

        addButton.className =
            "subgrou-add-btn";

        addButton.innerHTML =
            '<i class="fa fa-plus"></i>Add sub group';

        miniNavigation.appendChild(
            addButton
        );


        /* ================================================
           SLIDING INDICATOR
        ================================================= */

        const indicator =
            document.createElement("span");

        indicator.className =
            "mini-nav-indicator";

        indicator.id =
            "miniNavIndicator";

        miniNavigation.appendChild(
            indicator
        );


        attachMiniNavigationEvents();

        requestAnimationFrame(
            updateMiniIndicator
        );

    }


    /* =====================================================
       FIND ALL PRODUCTS BELONGING TO A CUSTOM GROUP
       
       This includes:
       - products directly assigned to group
       - products assigned to its subgroups
       ===================================================== */

    function getProductsUnderGroup(categoryId) {

        const result = [];


        function collect(id) {

            products.forEach(product => {

                if (
                    Number(product.custom_category_id) ===
                    Number(id)
                ) {

                    result.push(product);

                }

            });


            customCategories
                .filter(category =>
                    Number(category.parent_id) ===
                    Number(id)
                )
                .forEach(child => {

                    collect(
                        child.custom_category_id
                    );

                });

        }


        collect(categoryId);


        /*
         * Remove duplicate products.
         */

        const unique =
            new Map();

        result.forEach(product => {

            unique.set(
                product.product_id,
                product
            );

        });


        return Array.from(
            unique.values()
        );

    }


    /* =====================================================
       COMPANY CATEGORY FILTER
    ===================================================== */

    function selectCompanyCategory(companyCategory) {

        selectedCompanyCategory =
            companyCategory;
        /* =====================================================
          REFRESH COMPANY POPUP ACTIVE STATE
          ===================================================== */

        buildCompanyPopup();


        /*
         * IMPORTANT:
         *
         * Changing company category resets:
         *
         * 1. Custom group to first available group
         * 2. Mini navigation to ALL
         */

        selectedCustomCategory = null;
        selectedSubCategory = null;
        currentPage = 1;


        companyText.textContent =
            formatCompanyName(
                companyCategory
            );


        /*
         * Find available custom groups.
         */

        const groups =
            getRootCustomGroups(
                companyCategory
            )
            .filter(category =>
                categoryHasProducts(category)
            );


        /*
         * If there is a custom group,
         * automatically select the first one.
         */

        if (groups.length > 0) {

            selectedCustomCategory =
                groups[0].custom_category_id;

            sellerText.textContent =
                formatNormalName(
                    groups[0].name
                );

        } else {

            /*
             * No custom group.
             *
             * Seller side displays "All".
             */

            sellerText.textContent =
                "All";

        }


        /*
         * Rebuild seller popup.
         */

        buildSellerPopup();


        /*
         * Rebuild mini navigation.
         */

        if (selectedCustomCategory) {

            buildMiniNavigation(
                selectedCustomCategory
            );

        } else {

            buildMiniNavigation(null);

        }


        /*
         * ALWAYS SELECT ALL.
         */

        requestAnimationFrame(() => {

            const all =
                miniNavigation.querySelector(
                    ".mini-nav-item[data-mini='all']"
                );

            if (all) {

                document
                    .querySelectorAll(
                        ".mini-nav-item"
                    )
                    .forEach(item =>
                        item.classList.remove(
                            "active"
                        )
                    );

                all.classList.add(
                    "active"
                );

            }

            updateMiniIndicator();

        });


        /*
         * Display products.
         */

        if (selectedCustomCategory) {

            currentProducts =
                getProductsUnderGroup(
                    selectedCustomCategory
                );

        } else {

            currentProducts =
                getProductsForCompanyCategory(
                    selectedCompanyCategory
                );

        }


        renderProducts();


        /*
         * Close popup after selection.
         */

        companySide.classList.remove(
            "open"
        );

    }


    /* =====================================================
       SELECT CUSTOM GROUP
    ===================================================== */

    function selectCustomCategory(categoryId) {

        const category =
            customCategories.find(item =>
                Number(item.custom_category_id) ===
                Number(categoryId)
            );


        if (!category) {
            return;
        }


        selectedCustomCategory =
            category.custom_category_id;
        /* =====================================================
          REFRESH CUSTOM POPUP ACTIVE STATE
          ===================================================== */
        buildSellerPopup();

        selectedSubCategory = null;

        currentPage = 1;


        sellerText.textContent =
            formatNormalName(
                category.name
            );


        /*
         * Rebuild mini navigation.
         *
         * ALL becomes active automatically.
         */

        buildMiniNavigation(
            selectedCustomCategory
        );


        /*
         * ALL products under this custom group.
         */

        currentProducts =
            getProductsUnderGroup(
                selectedCustomCategory
            );


        renderProducts();


        /*
         * Close popup.
         */

        sellerSide.classList.remove(
            "open"
        );

    }


    /* =====================================================
       SELECT SUBGROUP
    ===================================================== */

    function selectSubCategory(categoryId) {

        selectedSubCategory =
            categoryId;

        currentPage = 1;


        currentProducts =
            getProductsForCustomCategory(
                categoryId
            );


        renderProducts();

    }


    /* =====================================================
       RENDER PRODUCTS
       
       Maximum 11 products per page.
       
       12th position:
       Navigation card
       
       ONLY if more than 11 products.
    ===================================================== */

    function renderProducts() {

        productGrid.innerHTML = "";


        const totalProducts =
            currentProducts.length;


        const totalPages =
            Math.max(
                1,
                Math.ceil(
                    totalProducts /
                    PRODUCTS_PER_PAGE
                )
            );


        /*
         * Protect page number.
         */

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        if (currentPage < 1) {
            currentPage = 1;
        }


        /*
         * Current page products.
         */

        const start =
            (currentPage - 1) *
            PRODUCTS_PER_PAGE;


        const pageProducts =
            currentProducts.slice(
                start,
                start + PRODUCTS_PER_PAGE
            );


        /*
         * Render products.
         */

        pageProducts.forEach(product => {

            productGrid.appendChild(
                createProductCard(product)
            );

        });


        /*
         * Navigation card only if
         * there are MORE than 11 products.
         */

        if (totalProducts > PRODUCTS_PER_PAGE) {

            productGrid.appendChild(
                createNavigationCard(
                    totalPages
                )
            );

        }


        /*
         * Count.
         */

        productsCount.textContent =
            totalProducts +
            (
                totalProducts === 1
                    ? " product"
                    : " products"
            );


        /*
         * Navigation information.
         */

        if (totalProducts > PRODUCTS_PER_PAGE) {

            navigationPage.textContent =
                "Page " +
                currentPage +
                " of " +
                totalPages;

            navigationCount.textContent =
                totalProducts +
                " products";


            previousProducts.disabled =
                currentPage === 1;

            nextProducts.disabled =
                currentPage === totalPages;

        }


        /*
         * Empty group.
         */

        if (totalProducts === 0) {

            productGrid.innerHTML = `
                <div class="empty-products">
                    No products available in this category.
                </div>
            `;

        }

    }


    /* =====================================================
       CREATE PRODUCT CARD
       
       Uses your existing card structure.
    ===================================================== */

    function createProductCard(product) {

        const wrapper =
            document.createElement("div");

        wrapper.className =
            "card-contain";


        const card =
            document.createElement("div");

        card.className =
            "card";


        /* ================================================
           IMAGE
        ================================================= */

        const image =
            document.createElement("img");

        image.src =
            product.image_path || "";

        image.loading =
            "lazy";

        image.decoding =
            "async";

        image.alt =
            product.product_name || "Product";


        /* ================================================
           BODY
        ================================================= */

        const body =
            document.createElement("div");

        body.className =
            "card-body";


        /* Buying price */

        const buying =
            document.createElement("div");

        buying.className =
            "price buying";

        buying.textContent =
            "KES " +
            Number(
                product.buying_price || 0
            ).toLocaleString(
                "en-KE",
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        /* Selling price */

        const selling =
            document.createElement("div");

        selling.className =
            "price";

        selling.textContent =
            "KES " +
            Number(
                product.selling_price || 0
            ).toLocaleString(
                "en-KE",
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        /* Unit */

        const unit =
            document.createElement("div");

        unit.className =
            "perDiv";


        const productUnit =
            String(
                product.unit || ""
            ).trim();


        if (
            normalize(productUnit) ===
            "each"
        ) {

            unit.textContent =
                "Each";

        } else {

            unit.textContent =
                "Per " +
                formatNormalName(
                    productUnit
                );

        }


        /* Stock */

        const stock =
            document.createElement("div");

        const stockValue =
            Number(
                product.stock_quantity || 0
            );


        stock.className =
            "stock " +
            (
                stockValue > 5
                    ? "in-stock"
                    : (
                        stockValue > 0
                            ? "low-stock"
                            : "out-stock"
                    )
            );


        let displayStock;


        if (stockValue >= 100) {

            displayStock =
                "99+";

        } else if (stockValue <= 0) {

            displayStock =
                "0";

        } else {

            const whole =
                Math.floor(stockValue);

            const decimal =
                stockValue - whole;

            let fractionText = "";


            if (decimal >= 0.875) {

                displayStock =
                    String(whole + 1);

            } else if (decimal >= 0.625) {

                fractionText =
                    "¾";

            } else if (decimal >= 0.375) {

                fractionText =
                    "½";

            } else if (decimal >= 0.125) {

                fractionText =
                    "¼";

            }


            if (!displayStock) {

                displayStock =
                    whole > 0
                        ? whole + fractionText
                        : fractionText || "0";

            }

        }


        const strong =
            document.createElement("strong");

        strong.textContent =
            displayStock;

        stock.appendChild(
            strong
        );


        body.appendChild(buying);
        body.appendChild(selling);
        body.appendChild(unit);
        body.appendChild(stock);


        /* ================================================
           ACTIONS
        ================================================= */

        const actions =
            document.createElement("div");

        actions.className =
            "card-actions";


        /* Edit */

        const edit =
            document.createElement("a");

        edit.href =
            "?edit_product_id=" +
            encodeURIComponent(
                product.product_id
            );

        edit.className =
            "edit";

        edit.innerHTML =
            '<i class="fa fa-pen"></i>';


        /* Delete */

        const form =
            document.createElement("form");

        form.method =
            "POST";

        form.onsubmit =
            function () {

                return confirm(
                    "Are you sure you want to delete this product?"
                );

            };


        const hidden =
            document.createElement("input");

        hidden.type =
            "hidden";

        hidden.name =
            "delete_product_id";

        hidden.value =
            product.product_id;


        const deleteButton =
            document.createElement("button");

        deleteButton.type =
            "submit";

        deleteButton.className =
            "delete";

        deleteButton.innerHTML =
            '<i class="fa fa-trash"></i>';


        form.appendChild(hidden);
        form.appendChild(deleteButton);


        actions.appendChild(edit);
        actions.appendChild(form);


        /* ================================================
           ASSEMBLE
        ================================================= */

        card.appendChild(image);
        card.appendChild(body);
        card.appendChild(actions);


        const name =
            document.createElement("div");

        name.className =
            "product-name";

        name.textContent =
            product.product_name || "";


        wrapper.appendChild(card);
        wrapper.appendChild(name);


        return wrapper;

    }


    /* =====================================================
       NAVIGATION CARD
    ===================================================== */

    function createNavigationCard(totalPages) {

        const wrapper =
            document.createElement("div");

        wrapper.className =
            "card-contain";


        const navigationCard =
            document.createElement("div");

        navigationCard.className =
            "products-navigation-card";


        navigationCard.innerHTML = `

            <div class="navigation-buttons">

                <button
                    type="button"
                    class="navigation-button"
                    id="previousProducts"
                    aria-label="Previous products"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <path
                            d="M19 12H5"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />

                        <path
                            d="M11 6L5 12L11 18"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </button>


                <button
                    type="button"
                    class="navigation-button"
                    id="nextProducts"
                    aria-label="Next products"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <path
                            d="M5 12H19"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />

                        <path
                            d="M13 6L19 12L13 18"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </button>

            </div>


            <div
                class="navigation-page"
                id="navigationPage"
            >
                Page ${currentPage} of ${totalPages}
            </div>


            <div
                class="navigation-count"
                id="navigationCount"
            >
                ${currentProducts.length} products
            </div>

        `;


        wrapper.appendChild(
            navigationCard
        );


        /*
         * Attach buttons directly.
         */

        const previous =
            navigationCard.querySelector(
                "#previousProducts"
            );

        const next =
            navigationCard.querySelector(
                "#nextProducts"
            );


        previous.disabled =
            currentPage === 1;

        next.disabled =
            currentPage === totalPages;


        previous.addEventListener(
            "click",
            function () {

                if (currentPage > 1) {

                    currentPage--;

                    renderProducts();

                }

            }
        );


        next.addEventListener(
            "click",
            function () {

                if (currentPage < totalPages) {

                    currentPage++;

                    renderProducts();

                }

            }
        );


        return wrapper;

    }


    /* =====================================================
       MINI NAVIGATION EVENTS
    ===================================================== */

    function attachMiniNavigationEvents() {

        miniNavigation
            .querySelectorAll(
                ".mini-nav-item"
            )
            .forEach(button => {

                button.addEventListener(
                    "click",
                    function () {

                        /*
                         * Remove active from all.
                         */

                        miniNavigation
                            .querySelectorAll(
                                ".mini-nav-item"
                            )
                            .forEach(item =>
                                item.classList.remove(
                                    "active"
                                )
                            );


                        /*
                         * Activate clicked item.
                         */

                        this.classList.add(
                            "active"
                        );


                        /*
                         * Reset pagination.
                         */

                        currentPage = 1;


                        const value =
                            this.dataset.mini;


                        /*
                         * ALL
                         */

                        if (
                            value === "all"
                        ) {

                            selectedSubCategory =
                                null;

                            currentProducts =
                                getProductsUnderGroup(
                                    selectedCustomCategory
                                );

                        }

                        /*
                         * SUBGROUP
                         */

                        else {

                            selectedSubCategory =
                                value;

                            currentProducts =
                                getProductsForCustomCategory(
                                    value
                                );

                        }


                        renderProducts();


                        updateMiniIndicator();

                    }
                );

            });


        /*
         * Add subgroup button.
         */

        const addButton =
            miniNavigation.querySelector(
                ".subgrou-add-btn"
            );


        if (addButton) {

            addButton.addEventListener(
                "click",
                function () {

                    /*
                     * Keep your existing
                     * add-sub-group function here.
                     *
                     * Example:
                     *
                     * toggleSubGroupAdd(true);
                     */

                    if (
                        typeof toggleSubGroupAdd ===
                        "function"
                    ) {

                        toggleSubGroupAdd(
                            true
                        );

                    }

                }
            );

        }

    }


    /* =====================================================
       SLIDING UNDERLINE
    ===================================================== */

    function updateMiniIndicator() {

        const indicator =
            document.getElementById(
                "miniNavIndicator"
            );

        const active =
            miniNavigation.querySelector(
                ".mini-nav-item.active"
            );


        if (
            !indicator ||
            !active
        ) {

            return;

        }


        indicator.style.left =
            active.offsetLeft +
            "px";


        indicator.style.width =
            active.offsetWidth +
            "px";

    }


    /* =====================================================
       COMPANY POPUP CLICK
    ===================================================== */

    companyButton.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();


            sellerSide.classList.remove(
                "open"
            );


            companySide.classList.toggle(
                "open"
            );

        }
    );


    /* =====================================================
       SELLER POPUP CLICK
    ===================================================== */

    sellerButton.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();


            companySide.classList.remove(
                "open"
            );


            sellerSide.classList.toggle(
                "open"
            );

        }
    );


    /* =====================================================
       COMPANY OPTION EVENTS
       
       Delegated because popup is dynamic.
    ===================================================== */

    companyPopup.addEventListener(
        "click",
        function (event) {

            const option =
                event.target.closest(
                    ".category-option"
                );


            if (!option) {
                return;
            }


            event.stopPropagation();


            selectCompanyCategory(
                option.dataset.company
            );

        }
    );


    /* =====================================================
       SELLER OPTION EVENTS
    ===================================================== */

    sellerPopup.addEventListener(
        "click",
        function (event) {

            const option =
                event.target.closest(
                    ".category-option"
                );


            if (!option) {
                return;
            }


            event.stopPropagation();


            selectCustomCategory(
                option.dataset.seller
            );

        }
    );


    /* =====================================================
       CLOSE POPUPS OUTSIDE
    ===================================================== */

    document.addEventListener(
        "click",
        function () {

            companySide.classList.remove(
                "open"
            );

            sellerSide.classList.remove(
                "open"
            );

        }
    );


    /* =====================================================
       STOP POPUP CLICKS
    ===================================================== */

    document
        .querySelectorAll(
            ".category-popup"
        )
        .forEach(popup => {

            popup.addEventListener(
                "click",
                function (event) {

                    event.stopPropagation();

                }
            );

        });


    /* =====================================================
       RESIZE
    ===================================================== */

    window.addEventListener(
        "resize",
        function () {

            updateMiniIndicator();

        }
    );


    /* =====================================================
       INITIALIZATION
    ===================================================== */

    const availableCompanies =
        getAvailableCompanyCategories();


    if (availableCompanies.length === 0) {

        storePage.style.display =
            "none";

        return;

    }


    /*
     * Select the first company category
     * containing products.
     */

    selectCompanyCategory(
        availableCompanies[0]
    );


    /*
     * Build company popup.
     */

    buildCompanyPopup();


    /*
     * Build seller popup.
     */

    buildSellerPopup();


    /*
     * Ensure ALL is selected.
     */

    requestAnimationFrame(
        updateMiniIndicator
    );

});
</script>
  <script>
    const customCategories = <?= json_encode(
        $customCategories,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;
  </script>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>
  <script>
    // DataTables Script Js
    $(document).ready(function () {
      $('#sellerTransactions').DataTable({
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
  <?php if ($editMode): ?>
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    toggleProductsAdd(true);
  });
  </script>
  <?php endif; ?>

  <script>
    const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
    const chatBody = document.getElementById("chatBody");
    const chatInput = document.getElementById("chatInput");
    const orderStatus = document.getElementById("orderStatus");
    const chatFooter = document.getElementById("chatFooter");

    chatInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        
        // If SHIFT or CTRL is pressed → allow newline
        if (e.shiftKey || e.ctrlKey) {
          return; // do nothing (default behavior = new line)
        }

        // Otherwise → send message
        e.preventDefault(); // stop newline
        sendMessage();
      }
    });

    /* SEND MESSAGE */
    async function sendMessage() {
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

      const text = document.createElement("span");
      text.textContent = input.value;

      const timeSpan = document.createElement("span");
      timeSpan.className = "time";
      timeSpan.textContent = time;

      bubble.appendChild(text);
      bubble.appendChild(timeSpan);

      messageWrapper.appendChild(bubble);
      document.getElementById("chatBody").appendChild(messageWrapper);

      const message = input.value;
      let conversationId;

      initChat();

      // Send to backend
      await fetch("send_message.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
          conversation_id: conversationId, // dynamic later
          message: message
        })
      });

      input.value = "";
      chatBody.scrollTop = chatBody.scrollHeight;
    }

    async function initChat(buyerId, orderCode) {
      try {
        const res = await fetch("get_or_create_conversation.php", {
          method: "POST",
          headers: {"Content-Type": "application/json"},
          body: JSON.stringify({
            buyer_id: buyerId,
            seller_id: CURRENT_USER_ID,
            order_code: orderCode
          })
        });

        const data = await res.json();

        if (!data.conversation_id) {
          console.error("No conversation returned");
          return;
        }

        conversationId = data.conversation_id;

        // Join socket room
        socket.emit("join", {
          user_id: CURRENT_USER_ID,
          conversation_id: conversationId
        });

        // Load messages
        loadMessages();

      } catch (err) {
        console.error("Init chat error:", err);
      }
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
      //sendLocationToServer(lat, lng, address, manualText);
      await fetch("send_location.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
          conversation_id: conversationId,
          lat,
          lng,
          address,
          manualText
        })
      });

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

    setInterval(fetchMessages, 2000);

    let lastMessageId = 0;

    async function fetchMessages() {
      const res = await fetch(`fetch_messages.php?conversation_id=1&last_id=${lastMessageId}`);
      const messages = await res.json();

      // Check if user is near bottom BEFORE adding messages
      const threshold = 100; // px
      const isNearBottom =
        chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < threshold;

      messages.forEach(msg => {
        const messageWrapper = document.createElement("div");
        messageWrapper.className =
          msg.sender_id == CURRENT_USER_ID
            ? "chat-message buyer"
            : "chat-message seller";

        const bubble = document.createElement("div");
        bubble.className = "bubble";

        bubble.innerHTML = `
          ${msg.message}
          <span class="time">${msg.time}</span>
        `;

        messageWrapper.appendChild(bubble);
        chatBody.appendChild(messageWrapper);

        lastMessageId = msg.id;
      });

      // Only scroll if user was already at bottom
      if (isNearBottom && messages.length > 0) {
        chatBody.scrollTop = chatBody.scrollHeight;
      }
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
  <script>

  let conversationId = null;

  // Handle view click
  document.querySelectorAll(".btn-view").forEach(btn => {
    btn.addEventListener("click", function () {

      const buyerId = this.dataset.buyer;
      const orderCode = this.dataset.order;

      // Switch UI
      document.getElementById("sellerMain").style.display = "none";
      document.getElementById("chatSection").style.display = "flex";

      // Initialize chat with real data
      initChat(buyerId, orderCode);
    });
  });

  async function loadMessages() {
    const res = await fetch(`fetch_messages.php?conversation_id=${conversationId}&last_id=0`);
    const messages = await res.json();

    chatBody.innerHTML = "";

    messages.forEach(msg => {
      appendMessage(msg);
    });
  }

  function goBack() {
    document.getElementById("chatSection").style.display = "none";
    document.getElementById("sellerMain").style.display = "flex";
  }

  document.querySelectorAll(".btn-view").forEach(btn => {
    btn.addEventListener("click", function () {

      const buyerId = this.dataset.buyer;
      const orderCode = this.dataset.order;
      const buyerName = this.dataset.buyername;

      // 🔥 SET CHAT HEADER DATA
      document.getElementById("chatTitle").textContent =
        "Order Chat • Buyer: " + buyerName;

      document.getElementById("chatOrderCode").textContent =
        "Order Code: " + orderCode;

      // Optional: reset status
      document.getElementById("orderStatus").textContent =
        "Order in progress...";

      // Switch UI
      document.getElementById("sellerMain").style.display = "none";
      document.getElementById("chatSection").style.display = "flex";

      // Init chat
      initChat(buyerId, orderCode);
    });
  });
  </script>
</body>
</html>
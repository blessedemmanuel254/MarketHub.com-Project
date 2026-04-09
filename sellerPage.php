<?php
session_start();
require_once 'connection.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
$query = "SELECT username, profile_image, county FROM users WHERE user_id = ? LIMIT 1"; 
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("System error.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$username = "User";
$profileImage = null;
$county = "Kilifi"; // default

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (!empty($user['username'])) {
        $username = $user['username'];
    }

    $profileImage = $user['profile_image'] ?? null;
    $county = $user['county'] ?? $county; // use DB value if exists
}

$stmt->close();
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
// ---------- FETCH SELLER PRODUCTS ----------
$products = [];
$stmt = $conn->prepare("
    SELECT product_id, product_name, category, price, stock_quantity, image_path
    FROM productservicesrentals
    WHERE user_id = ?
    ORDER BY created_at DESC
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

$defaultAvatar = "Images/Maket Hub Logo.avif";

if (!empty($profileImage) && file_exists($profileImage)) {
    $safeProfileImage = htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8');
} else {
    $safeProfileImage = $defaultAvatar;
}


$error = "";
$success = "";

$productName = '';
$category    = '';
$price       = '';
$stock       = '';

$editMode = false;
$editProductId = null;

if (isset($_GET['edit_product_id'])) {

  $editProductId = intval($_GET['edit_product_id']);

  $stmt = $conn->prepare("
    SELECT product_name, category, price, stock_quantity, image_path
    FROM productservicesrentals
    WHERE product_id = ? AND user_id = ?
    LIMIT 1
  ");

  $stmt->bind_param("ii", $editProductId, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows === 1) {

    $product = $result->fetch_assoc();

    $productName = $product['product_name'];
    $category    = $product['category'];
    $price       = $product['price'];
    $stock       = $product['stock_quantity'];
    $currentImagePath = $product['image_path'];

    $editMode = true;
  }

  $stmt->close();
}
// ---------- ADD PRODUCT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_product_id']) && !isset($_POST['delete_product_id'])) {

$productName = smartTitleCase($_POST['name'] ?? '');
$category    = trim($_POST['category'] ?? '');
$price       = floatval($_POST['price'] ?? 0);
$stock       = intval($_POST['stock'] ?? 0);


/* ---------- BASIC VALIDATION ---------- */

if ($productName === '') {
$error = "Product name is required.";
}
elseif ($category === '') {
$error = "Please select a category.";
}
elseif ($price <= 0) {
$error = "Price must be greater than zero.";
}
elseif ($stock < 0) {
$error = "Stock cannot be negative.";
}
elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
$error = "Please upload a product image.";
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
elseif ($fileSize > 5 * 1024 * 1024) {
$error = "Image too large. Max 5MB.";
}

$imgInfo = getimagesize($fileTmp);

if (!$imgInfo) {
$error = "Invalid image.";
}

if (empty($error)) {

[$width,$height] = $imgInfo;

if ($width < 600 || $height < 600) {
$error = "Image too small. Minimum size is 600×600 px.";
}

}

}

/* ---------- RESIZE IMAGE FIRST ---------- */

if (empty($error)) {

$maxSize = 700;

$ratio = min($maxSize/$width,$maxSize/$height,1);

$newWidth  = (int)($width*$ratio);
$newHeight = (int)($height*$ratio);

$canvas = imagecreatetruecolor($newWidth,$newHeight);

switch ($mime) {

case 'image/jpeg':
$source = imagecreatefromjpeg($fileTmp);
break;

case 'image/png':
$source = imagecreatefrompng($fileTmp);
imagealphablending($canvas,false);
imagesavealpha($canvas,true);
break;

case 'image/webp':
$source = imagecreatefromwebp($fileTmp);
break;

}

imagecopyresampled(
$canvas,$source,
0,0,0,0,
$newWidth,$newHeight,
$width,$height
);


/* ---------- SAVE TEMP IMAGE FOR HASHING ---------- */

$tempFile = tempnam(sys_get_temp_dir(),'img_').'.webp';

imagewebp($canvas,$tempFile,75);


/* ---------- GENERATE HASHES ---------- */

$imgHash  = md5_file($tempFile);
$imgPhash = generateImageDHash($tempFile);

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


/* ---------- INSERT PRODUCT ---------- */

$stmt = $conn->prepare("
INSERT INTO productservicesrentals
(
user_id,
product_name,
category,
price,
stock_quantity,
image_path,
image_width,
image_height,
image_size_kb,
image_format,
image_hash,
image_phash
)
VALUES (?,?,?,?,?,?,?,?,?,'webp',?,?)
");

$stmt->bind_param(
"issdissiiss",
$user_id,
$productName,
$category,
$price,
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

$productName='';
$category='';
$price='';
$stock='';

} else {

$error="Failed to save product.";

}

$stmt->close();

}

}

// ---------- EDIT PRODUCT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product_id'])) {

$editProductId = intval($_POST['edit_product_id']);

$productName = smartTitleCase($_POST['name'] ?? '');
$category    = trim($_POST['category'] ?? '');
$price       = floatval($_POST['price'] ?? 0);
$stock       = intval($_POST['stock'] ?? 0);


/* ---------- BASIC VALIDATION ---------- */

if ($productName === '') {
$error="Product name required.";
}
elseif ($category === '') {
$error="Select category.";
}
elseif ($price<=0) {
$error="Price must be greater than zero.";
}
elseif ($stock<0) {
$error="Stock cannot be negative.";
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
elseif($fileSize>5*1024*1024){
$error="Image too large. Max 5MB.";
}

$imgInfo=getimagesize($fileTmp);

if(!$imgInfo){
$error="Invalid image file.";
}

if(empty($error)){

[$width,$height]=$imgInfo;

if($width<600 || $height<600){
$error="Image too small. Minimum size is 600×600 px.";
}

}

}


/* ---------- RESIZE IMAGE BEFORE HASH ---------- */

if(empty($error) && isset($_FILES['photo']) && $_FILES['photo']['error']==0){

$maxSize=700;

$ratio=min($maxSize/$width,$maxSize/$height,1);

$newWidth  =(int)($width*$ratio);
$newHeight =(int)($height*$ratio);

$canvas=imagecreatetruecolor($newWidth,$newHeight);

switch($mime){

case 'image/jpeg':
$source=imagecreatefromjpeg($fileTmp);
break;

case 'image/png':
$source=imagecreatefrompng($fileTmp);
imagealphablending($canvas,false);
imagesavealpha($canvas,true);
break;

case 'image/webp':
$source=imagecreatefromwebp($fileTmp);
break;

}

imagecopyresampled(
$canvas,$source,
0,0,0,0,
$newWidth,$newHeight,
$width,$height
);


/* ---------- TEMP FILE FOR HASH ---------- */

$tempFile=tempnam(sys_get_temp_dir(),'img_').'.webp';

imagewebp($canvas,$tempFile,75);


/* ---------- GENERATE HASHES ---------- */

$imgHash  = md5_file($tempFile);
$imgPhash = generateImageDHash($tempFile);

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


/* ---------- UPDATE PRODUCT ---------- */

if(empty($error)){

$stmt=$conn->prepare("
UPDATE productservicesrentals
SET product_name=?,category=?,price=?,stock_quantity=?,image_path=?,image_hash=?,image_phash=?
WHERE product_id=? AND user_id=?
");

$stmt->bind_param(
"ssdsssiii",
$productName,
$category,
$price,
$stock,
$imageToSave,
$imgHash,
$imgPhash,
$editProductId,
$user_id
);

if($stmt->execute()){

$success = "Product updated successfully! <span class='redirect-msg'></span>";

$productName='';
$category='';
$price='';
$stock='';

}else{
$error="Update failed.";
}

$stmt->close();

}

}

// Fetch seller orders
$sellerOrders = [];
$stmt = $conn->prepare("
  SELECT 
    o.order_id,
    o.order_code,
    o.created_at,
    u.full_name AS buyer_name,

    oi.item_id,
    oi.product_id,
    oi.quantity,
    oi.price,
    oi.subtotal,
    oi.order_status,
    oi.shipped_at,
    oi.delivered_at,
    oi.payment_status,

    p.product_name,
    p.image_path,

    (oi.quantity * oi.price) AS seller_total

  FROM order_items oi
  JOIN orders o ON oi.order_id = o.order_id
  JOIN users u ON o.buyer_id = u.user_id
  JOIN productservicesrentals p ON oi.product_id = p.product_id

  WHERE oi.seller_id = ?

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

// Count seller's active orders (not delivered)
$countStmt = $conn->prepare("
    SELECT COUNT(*) AS activeOrders
    FROM order_items
    WHERE seller_id = ? AND order_status != 'delivered'
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

// Fetch seller orders summary
$stmt = $conn->prepare("
  SELECT 
    COUNT(DISTINCT oi.order_id) AS total_orders,
    COUNT(DISTINCT CASE WHEN oi.order_status = 'pending' THEN oi.order_id END) AS processing_orders,
    COUNT(DISTINCT CASE WHEN oi.order_status = 'shipped' THEN oi.order_id END) AS shipped_orders,
    COUNT(DISTINCT CASE WHEN oi.order_status = 'delivered' THEN oi.order_id END) AS delivered_orders
  FROM order_items oi
  WHERE oi.seller_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$totalOrders      = $row['total_orders'] ?? 0;
$processingOrders = $row['processing_orders'] ?? 0;
$shippedOrders    = $row['shipped_orders'] ?? 0;
$deliveredOrders  = $row['delivered_orders'] ?? 0;

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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <!-- jQuery + DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <title>Seller Page | Maket Hub</title>
</head>
<body>
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
        <div class="sContainer">
          <img src="<?php echo $safeProfileImage; ?>" alt="Profile" class="avatar-img">
          <p class="wcmTxt">
            Welcome,<br>
            <span>Logged in as <?php echo $safeUsername; ?></span>
          </p>
        </div>
        <div class="rhs">
          <a class="lkOdr" onclick="toggleSellerOrdersTrack()">
            <div class="odrIconDiv">
              <i class="fa-brands fa-first-order-alt"></i>
              <p><?= $displayCount ?></p>
            </div>
            <p>Order(s)</p>
          </a>
          <select name="county" id="ward">
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

    <main class="buyerMain" id="sellerMain">
      <div class="tabs-container" id="toggleMarketTypeTab">
        <div class="tabs">
          <button class="tab-btn" data-tab="dashboard">Dashboard</button>
          <button class="tab-btn" data-tab="products">Products</button>
          <button class="tab-btn" data-tab="funds">Funds</button>
        </div>

        <div class="tab-content">
          <div id="dashboard" class="tab-panel">
            <p>Dashboard Area <br><strong>Your business performance and finances <i class="fa-regular fa-circle-check"></i></strong></p>
            <div class="containerInner">

              <div class="grid">
                <!-- WALLET HEALTH -->
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

                <!-- WITHDRAWAL STATUS -->
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
                </div>
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
                </div>

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
              <p>Your Products Shelf<br><strong>Manage your listed items efficiently <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleProductsAdd(true)">
                <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Product</span>
              </button>

            </div>

            <!-- PRODUCTS GRID -->
            <div class="products-grid">
              <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="card">
                      <img src="<?= htmlspecialchars($product['image_path']) ?>" loading="lazy" decoding="async" alt="<?= htmlspecialchars($product['product_name']) ?>">
                      <div class="card-body">
                        <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                        <div class="product-meta"><?= htmlspecialchars($product['category']) ?></div>
                        <div class="price">KES <?= number_format($product['price'], 2) ?></div>
                        <div class="stock <?= ($product['stock_quantity'] > 5) ? 'in-stock' : (($product['stock_quantity'] > 0) ? 'low-stock' : 'out-stock') ?>">
                          <?= ($product['stock_quantity'] > 0) ? "In stock (<strong>{$product['stock_quantity']}</strong>)" : "Out of stock" ?>
                        </div>
                      </div>
                      <div class="card-actions">
                          <a href="?edit_product_id=<?= $product['product_id'] ?>" class="edit" >
                            <i class="fa fa-pen"></i> Edit
                          </a>
                          <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?')">
                            <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                          </form>
                      </div>
                    </div>
                <?php endforeach; ?>
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

                  <div class="inp-box">
                    <label>Price (KES)</label>
                    <input type="number" name="price" step="0.01" placeholder="Enter price"
                    value="<?= htmlspecialchars($price, ENT_QUOTES) ?>"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '')" min="0" required>
                  </div>

                  <div class="inp-box">
                      <label>Stock Quantity</label>
                      <input type="number" name="stock" placeholder="e.g 24" 
                          value="<?= htmlspecialchars($stock, ENT_QUOTES) ?>" 
                          oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="0" step="1" required>
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
                  <div></div>

                  <button type="submit">
                    <?= $editMode ? 'Update Product' : 'Add Product' ?>
                  </button>
                </div>

            </form>
            </div>
          </div>
          
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
          </div>
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
              <th>Image</th>
              <th>Order ID</th>
              <th>Buyer</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if (!empty($sellerOrders)) {
              $count = 1;
              foreach ($sellerOrders as $order) {

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
                                  : "Images/Maket Hub Logo.avif"; // default image

                  echo "<tr data-status=\"{$order['order_status']}\">
                          <td><img src='{$productImage}' alt='Product Image' style='width:50px;height:50px;object-fit:cover;border-radius:4px;'></td>
                          <td>{$order['order_code']}</td>
                          <td>".htmlspecialchars($order['buyer_name'])."</td>
                          <td>".htmlspecialchars($order['product_name'])."</td>
                          <td>{$order['quantity']}</td>
                          <td>KES {$total}</td>
                          <td><span class='badge {$paymentClass}'>{$paymentLabel}</span></td>
                          <td><span class='badge {$statusClass}' title=\"".htmlspecialchars($statusTooltip)."\">{$statusLabel}</span></td>
                          <td>{$date}</td>
                          <td class='actions'>
                        <div>";

                  // Action based on status
                  if ($statusClass === 'pending') {
                    echo "<button class='btn-ship' data-id='{$order['order_id']}'>Mark&nbsp;as&nbsp;Shipped</button>";
                  } else {
                    echo "<button class='btn-view'><i class='fa-solid fa-eye'></i></button>";
                  }

                  echo "      </div>
                          </td>
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
              <th>Image</th>
              <th>Order ID</th>
              <th>Buyer</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if (!empty($sellerOrders)) {
              $count = 1;
              foreach ($sellerOrders as $order) {

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
                                  : "Images/Maket Hub Logo.avif"; // default image

                  echo "<tr data-status=\"{$order['order_status']}\">
                          <td><img src='{$productImage}' alt='Product Image' style='width:50px;height:50px;object-fit:cover;border-radius:4px;'></td>
                          <td>{$order['order_code']}</td>
                          <td>".htmlspecialchars($order['buyer_name'])."</td>
                          <td>".htmlspecialchars($order['product_name'])."</td>
                          <td>{$order['quantity']}</td>
                          <td>KES {$total}</td>
                          <td><span class='badge {$paymentClass}'>{$paymentLabel}</span></td>
                          <td><span class='badge {$statusClass}' title=\"".htmlspecialchars($statusTooltip)."\">{$statusLabel}</span></td>
                          <td>{$date}</td>
                          <td class='actions'>
                              <div>";

                  // Action based on status
                  if ($statusClass === 'pending') {
                    echo "<button class='btn-ship' data-id='{$order['order_id']}'>Mark&nbsp;as&nbsp;Shipped</button>";
                  } else {
                    echo "<button class='btn-view'><i class='fa-solid fa-eye'></i></button>";
                  }

                  echo "      </div>
                          </td>
                        </tr>";
                  $count++;
              }
          }
          ?>
          </tbody>
        </table>
      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleSellerOrdersTrack()">Go&nbsp;back</button> to continue delivering.</p>
    </main>
    <footer>
      <p>&copy; 2025/2026, Maket Hub.shop, All Rights reserved.</p>
    </footer>
  </div>

  <!-- Notification container -->
  <div id="notification-container"></div>
  
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
</body>
</html>
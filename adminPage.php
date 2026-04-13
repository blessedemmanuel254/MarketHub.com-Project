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

function safe($v) {
  return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
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
 * 
 * Mask an email address partially
 * Example: emmanueltindi23@gmail.com => em***23@gmail.com
 * 
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
$defaultAvatar = "https://cdn-icons-png.flaticon.com/512/149/149071.png";
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

$defaultAvatar = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

$agentsStmt = $conn->prepare("
  SELECT 
    a.user_id,
    a.full_name,
    a.username,
    a.email,
    a.phone,
    a.profile_image,
    a.status,
    a.is_verified,
    a.created_at,
    a.updated_at,
    a.agency_code,
    a.economic_period_count,
    r.username AS referrer_username,
    (
      SELECT COUNT(*) 
      FROM users u 
      WHERE u.referred_by = a.user_id and is_verified = 1
    ) AS total_sub_agents
  FROM users a
  LEFT JOIN users r 
    ON a.referred_by = r.user_id
  WHERE a.account_type = 'sales_agent'
  ORDER BY a.user_id DESC
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
  $editError = "All fields are required!";
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
} elseif (!preg_match('/^[0-9+\-\(\)\s]+$/', $editPhone)) {
  $editError = "Phone number contains invalid characters!";
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

$mproductError = "";
$mproductSuccess = "";

$mproductProductName = '';
$mproductCategory = '';
$mproductPrice = '';
$mproductCurrency = '';
$mproductProductDescription = '';
$mproductIs_active = '';

$mproductEditMode = false;
$mproductEditProductId = null;
$currentImagePath = null;

/* =========================
   EDIT MODE FETCH
========================= */
if (isset($_GET['id']) && $_GET['type'] === 'product') {
  $mproductEditProductId = intval($_GET['id']);

  $stmt = $conn->prepare("SELECT * FROM markethub_products WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $mproductEditProductId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
      $row = $result->fetch_assoc();

      $mproductProductName = $row['product_name'];
      $mproductCategory = $row['category'];
      $mproductPrice = $row['price'];
      $mproductCurrency = $row['currency'];
      $mproductProductDescription = $row['description'];
      $mproductIs_active = $row['is_active'];
      $currentImagePath = $row['image'];

      $mproductEditMode = true;
  }
  $stmt->close();
}

/* =========================
   FORM SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $mproductProductName = smartTitleCase($_POST['name'] ?? '');
  $mproductCategory = trim($_POST['category'] ?? '');
  $mproductPrice = floatval($_POST['price'] ?? 0);
  $mproductCurrency = $_POST['currency'] ?? '';
  $mproductProductDescription = $_POST['description'] ?? '';
  $mproductIs_active = $_POST['is_active'] ?? '';
  if ($mproductProductName === '') {
    $mproductError = "Product name required!";
  }
  elseif ($mproductPrice <= 0) {
    $mproductError = "Price must be greater than zero.";
  }
  elseif ($mproductCurrency === '') {
    $mproductError = "Select currency!";
  }
  elseif ($mproductCategory === '') {
    $mproductError = "Select product category!";
  }
  elseif ($mproductProductDescription === '') {
    $mproductError = "Description required!";
  }
  elseif (strlen($mproductProductDescription) < 50) {
    $mproductError = "Description must be at least 50 characters!";
  }
  elseif (strlen($mproductProductDescription) > 150) {
    $mproductError = "Description must not exceed 150 characters!";
  }
  elseif ($mproductIs_active === '') {
    $mproductError = "Select active status!";
  }

  /* =========================
    CHECK DUPLICATE PRODUCT NAME
  ========================= */

  if (empty($mproductError)) {

    if ($mproductEditMode) {
      // Exclude current product when editing
      $stmt = $conn->prepare("
        SELECT id 
        FROM markethub_products 
        WHERE product_name = ? AND id != ?
        LIMIT 1
      ");
      $stmt->bind_param("si", $mproductProductName, $mproductEditProductId);

    } else {
      // Normal insert check
      $stmt = $conn->prepare("
        SELECT id 
        FROM markethub_products 
        WHERE product_name = ?
        LIMIT 1
      ");
      $stmt->bind_param("s", $mproductProductName);
    }

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $mproductError = "A product with this name already exists!";
    }

    $stmt->close();
  }

  /* =========================
      IMAGE PROCESSING
  ========================= */
  $fileSize = $_FILES['photo']['size'] ?? 0;

  if ($fileSize > 5 * 1024 * 1024) {
    $mproductError = "Image too large. Max 5MB.";
  }

  $imagePath = null;

  if ((!$mproductEditMode) || (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0)) {

      if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
          $mproductError = "Image is required.";
      } else {

          $tmp = $_FILES['photo']['tmp_name'];
          $mime = mime_content_type($tmp);

          $allowed = ['image/jpeg','image/png','image/webp'];

          if (!in_array($mime, $allowed)) {
              $mproductError = "Invalid image format!";
          }

          $fileSize = $_FILES['photo']['size'];

          if ($fileSize > 5 * 1024 * 1024) {
              $mproductError = "Image too large. Max 5MB!";
          }

          $imgInfo = getimagesize($tmp);
          if (!$imgInfo) {
              $mproductError = "Invalid image file!";
          }

          if (empty($mproductError)) {

              list($width, $height) = $imgInfo;

              $ratio = $width / $height;

              /* ✅ RATIO VALIDATION */
              if ($ratio < 0.65 || $ratio > 0.80) {
                  $mproductError = "Image must follow portrait ratio (e.g. 500x700)!";
              }

              /* ✅ MIN SIZE */
              if ($width < 400 || $height < 560) {
                $mproductError = "Image too small. Minimum 400x560!";
              }
          }

          if (empty($mproductError)) {

            $uploadDir = "uploads/company_products/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = uniqid('prod_', true) . ".webp";
            $filePath = $uploadDir . $fileName;

            /* =========================
                FORCE CONSISTENT SIZE (500x700)
            ========================== */
            $targetWidth = 500;
            $targetHeight = 700;

            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

            switch ($mime) {
                case 'image/jpeg':
                    $src = imagecreatefromjpeg($tmp);
                    break;

                case 'image/png':
                    $src = imagecreatefrompng($tmp);
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    break;

                case 'image/webp':
                    $src = imagecreatefromwebp($tmp);
                    break;
            }

            imagecopyresampled(
              $canvas, $src,
              0, 0, 0, 0,
              $targetWidth, $targetHeight,
              $width, $height
            );

            /* TEMP FILE FOR HASH */
            $tempFile = tempnam(sys_get_temp_dir(), 'img_') . '.webp';
            imagewebp($canvas, $tempFile, 75);

            /* GENERATE HASHES */
            $imageHash  = md5_file($tempFile);
            $imagePhash = generateImageDHash($tempFile);

            /* =========================
              DUPLICATE CHECK
            ========================= */

            if (empty($mproductError)) {

              // EXACT DUPLICATE
              $stmt = $conn->prepare("
                  SELECT id FROM markethub_products 
                  WHERE image_hash = ?
                  " . ($mproductEditMode ? "AND id != ?" : "") . "
                  LIMIT 1
              ");

              if ($mproductEditMode) {
                  $stmt->bind_param("si", $imageHash, $mproductEditProductId);
              } else {
                  $stmt->bind_param("s", $imageHash);
              }

              $stmt->execute();
              $stmt->store_result();

              if ($stmt->num_rows > 0) {
                  $mproductError = "This exact image already exists!";
              }

              $stmt->close();
            }


            /* VISUAL DUPLICATE (pHash) */
            if (empty($mproductError)) {

              $stmt = $conn->prepare("
                SELECT image_phash FROM markethub_products
                " . ($mproductEditMode ? "WHERE id != ?" : "")
              );

              if ($mproductEditMode) {
                $stmt->bind_param("i", $mproductEditProductId);
              }

              $stmt->execute();
              $result = $stmt->get_result();/* 

              while ($row = $result->fetch_assoc()) {
                $distance = levenshtein($imagePhash, $row['image_phash']);

                if ($distance <= 5) {
                  $mproductError = "A visually similar image already exists.";
                  break;
                }
              } */

              $stmt->close();
            }
            
            if (empty($mproductError)) {

              imagewebp($canvas, $filePath, 80);

              $imageSizeKB = round(filesize($tempFile) / 1024);

              $imagePath = $filePath;

              // DELETE OLD IMAGE (EDIT MODE)
              if ($mproductEditMode && $currentImagePath && file_exists($currentImagePath)) {
                  unlink($currentImagePath);
              }
            }

            imagedestroy($canvas);
            imagedestroy($src);

            if (isset($tempFile) && file_exists($tempFile)) {
              unlink($tempFile);
            }

            /* DELETE OLD IMAGE (EDIT MODE) */
            if ($mproductEditMode && $currentImagePath && file_exists($currentImagePath)) {
              unlink($currentImagePath);
            }
        }
      }
  }
  /* =========================
    LIMIT ACTIVE PRODUCTS (MAX 5)
  ========================= */

  if (empty($mproductError) && $mproductIs_active == '1') {

    if ($mproductEditMode) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM markethub_products 
            WHERE is_active = 1 AND id != ?
        ");
        $stmt->bind_param("i", $mproductEditProductId);
    } else {
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM markethub_products 
            WHERE is_active = 1
        ");
    }

    $stmt->execute();
    $stmt->bind_result($activeCount);
    $stmt->fetch();
    $stmt->close();

    if ($activeCount >= 5) {
        $mproductError = "There are already 5 active products!";
    }
  }


  /* =========================
    INSERT OR UPDATE (ONLY IF NO ERROR)
  ========================= */

  if (empty($mproductError)) {

    if ($mproductEditMode) {

        if ($imagePath) {
            $stmt = $conn->prepare("
                UPDATE markethub_products 
                SET product_name=?, price=?, currency=?, description=?, category=?, image=?, is_active=? 
                WHERE id=?
            ");

            $stmt->bind_param("sdssssii",
                $mproductProductName,
                $mproductPrice,
                $mproductCurrency,
                $mproductProductDescription,
                $mproductCategory,
                $imagePath,
                $mproductIs_active,
                $mproductEditProductId
            );

        } else {
            $stmt = $conn->prepare("
                UPDATE markethub_products 
                SET product_name=?, price=?, currency=?, description=?, category=?, is_active=? 
                WHERE id=?
            ");

            $stmt->bind_param("sdsssii",
                $mproductProductName,
                $mproductPrice,
                $mproductCurrency,
                $mproductProductDescription,
                $mproductCategory,
                $mproductIs_active,
                $mproductEditProductId
            );
        }

          if ($stmt->execute()) {
            $mproductSuccess = "Product updated successfully! <span class='redirect-msg'></span>";
          } else {
            $mproductError = "Update failed!";
          }

    } else {

      $stmt = $conn->prepare("
        INSERT INTO markethub_products 
        (product_name, price, currency, description, category, image, is_active, created_at, image_hash, image_phash, image_size_kb)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)
      ");

      $stmt->bind_param("sdssssissi",
        $mproductProductName,
        $mproductPrice,
        $mproductCurrency,
        $mproductProductDescription,
        $mproductCategory,
        $imagePath,
        $mproductIs_active,
        $imageHash,
        $imagePhash,
        $imageSizeKB
      );

      if ($stmt->execute()) {
        $mproductSuccess = "Product added successfully! <span class='redirect-msg'></span>";
      } else {
        $mproductError = "Insert failed!";
      }
    }

    $stmt->close();
  }
}

/* ---------- BIO ---------- */
$descMaxLength = 150;
$productDesc = !empty($mproductProductDescription) ? substr($mproductProductDescription, 0, $descMaxLength) : '';
$safeDesc = safe($productDesc);

/* =========================
  DAILY ACTIVE PRODUCTS RESET (SMART ROTATION)
========================= */

date_default_timezone_set('UTC');

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$resetFile = __DIR__ . '/last_reset.txt';
$lastReset = file_exists($resetFile) ? trim(file_get_contents($resetFile)) : '';

if ($lastReset !== $today) {

  // 1. Reset all products
  $conn->query("UPDATE markethub_products SET is_active = 0");

  // 2. Count total products
  $res = $conn->query("SELECT COUNT(*) as total FROM markethub_products");
  $total = $res->fetch_assoc()['total'] ?? 0;

  if ($total > 0) {

    // 3. Decide number of active products
    $limit = rand(1, min(5, $total));

    // 4. Get categories
    $categories = [];
    $res = $conn->query("SELECT DISTINCT category FROM markethub_products");

    while ($row = $res->fetch_assoc()) {
      $categories[] = $row['category'];
    }

    shuffle($categories);

    $selectedIds = [];
    $remainingSlots = $limit;

    /* =========================
      FIRST PASS: BALANCED + SMART PICK
    ========================= */
    foreach ($categories as $cat) {
      if ($remainingSlots <= 0) break;

      $stmt = $conn->prepare("
        SELECT id FROM markethub_products
        WHERE category = ?
          AND (last_activated_at IS NULL OR DATE(last_activated_at) < ?)
        ORDER BY 
          (last_activated_at IS NULL) DESC,     -- never used first
          last_activated_at ASC,                -- oldest first
          created_at DESC,                      -- newer products boosted
          RAND()
        LIMIT 1
      ");

      $stmt->bind_param("ss", $cat, $yesterday);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($row = $result->fetch_assoc()) {
        $selectedIds[] = (int)$row['id'];
        $remainingSlots--;
      }
    }

    /* =========================
      SECOND PASS: FILL REMAINING (SMART GLOBAL)
    ========================= */
    if ($remainingSlots > 0) {

      $res = $conn->query("
        SELECT id FROM markethub_products
        WHERE (last_activated_at IS NULL OR DATE(last_activated_at) < '$yesterday')
        ORDER BY 
          (last_activated_at IS NULL) DESC,
          last_activated_at ASC,
          created_at DESC,
          RAND()
        LIMIT $remainingSlots
      ");

      while ($row = $res->fetch_assoc()) {
        if (!in_array($row['id'], $selectedIds)) {
          $selectedIds[] = (int)$row['id'];
        }
      }
    }

    /* =========================
      FALLBACK (if everything was used yesterday)
    ========================= */
    if (count($selectedIds) < $limit) {

      $needed = $limit - count($selectedIds);

      $res = $conn->query("
        SELECT id FROM markethub_products
        ORDER BY RAND()
        LIMIT $needed
      ");

      while ($row = $res->fetch_assoc()) {
        if (!in_array($row['id'], $selectedIds)) {
          $selectedIds[] = (int)$row['id'];
        }
      }
    }

    /* =========================
      ACTIVATE + TRACK
    ========================= */
    if (!empty($selectedIds)) {
      $idList = implode(',', $selectedIds);

      $conn->query("
        UPDATE markethub_products 
        SET is_active = 1, last_activated_at = NOW()
        WHERE id IN ($idList)
      ");
    }
  }

  file_put_contents($resetFile, $today);
}

/* =========================
  PRODUCT STATS
========================= */

// Total products
$totalProducts = 0;
$activeProducts = 0;
$inactiveProducts = 0;
$totalValue = 0;

// TOTAL PRODUCTS
$res = $conn->query("SELECT COUNT(*) AS total FROM markethub_products");
$totalProducts = $res->fetch_assoc()['total'] ?? 0;

// ACTIVE PRODUCTS
$res = $conn->query("SELECT COUNT(*) AS total FROM markethub_products WHERE is_active = 1");
$activeProducts = $res->fetch_assoc()['total'] ?? 0;

// INACTIVE PRODUCTS
$res = $conn->query("SELECT COUNT(*) AS total FROM markethub_products WHERE is_active = 0");
$inactiveProducts = $res->fetch_assoc()['total'] ?? 0;

// TOTAL VALUE
$res = $conn->query("SELECT SUM(price) AS total FROM markethub_products");
$totalValue = $res->fetch_assoc()['total'] ?? 0;


/* =========================
  FETCH ALL PRODUCTS
========================= */

$products = [];

$res = $conn->query("SELECT * FROM markethub_products ORDER BY created_at DESC");

while ($row = $res->fetch_assoc()) {
  $products[] = $row;
}

/* =========================
  GROUP PRODUCTS BY CATEGORY
========================= */

$groupedProducts = [];

foreach ($products as $p) {
  $cat = strtolower(trim($p['category']));
  $groupedProducts[$cat][] = $p;
}

function safeCategoryId($cat) {
  return strtolower(preg_replace('/[^a-z0-9]/', '-', $cat));
}

// =========================
// AJAX DELETE PRODUCT
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_delete_product'])) {

  header('Content-Type: application/json');

  $productId = intval($_POST['product_id'] ?? 0);

  if ($productId <= 0) {
      echo json_encode(['success' => false, 'error' => 'Invalid product']);
      exit;
  }

  // FETCH IMAGE
  $stmt = $conn->prepare("SELECT image FROM markethub_products WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $productId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows !== 1) {
      echo json_encode(['success' => false, 'error' => 'Product not found']);
      exit;
  }

  $product = $result->fetch_assoc();
  $imagePath = $product['image'];

  $stmt->close();

  // DELETE PRODUCT
  $stmt = $conn->prepare("DELETE FROM markethub_products WHERE id = ?");
  $stmt->bind_param("i", $productId);

  if ($stmt->execute()) {

      if (!empty($imagePath) && file_exists($imagePath)) {
          unlink($imagePath);
      }

      echo json_encode(['success' => true]);
  } else {
      echo json_encode(['success' => false, 'error' => 'Delete failed']);
  }

  $stmt->close();
  exit; // VERY IMPORTANT (stops HTML rendering)
}

$withdrawalsQuery = $conn->query("
  SELECT 
    w.*,
    u.full_name,
    u.phone,
    u.account_type,
    u.profile_image,
    u.email
  FROM withdrawals w
  LEFT JOIN users u ON w.user_id = u.user_id
  ORDER BY w.created_at DESC
");

// Pending Requests
$pendingQuery = "SELECT COUNT(*) as total FROM withdrawals WHERE status = 'Pending'";
$pendingResult = $conn->query($pendingQuery);
$pendingCount = $pendingResult->fetch_assoc()['total'] ?? 0;

// Approved Today (SUM of net_amount)
$approvedTodayQuery = "
    SELECT SUM(net_amount) as total 
    FROM withdrawals 
    WHERE status = 'Approved' 
    AND DATE(processed_at) = CURDATE()
";
$approvedTodayResult = $conn->query($approvedTodayQuery);
$approvedToday = $approvedTodayResult->fetch_assoc()['total'] ?? 0;

// Total Withdrawn (All Approved)
$totalWithdrawnQuery = "
    SELECT SUM(net_amount) as total 
    FROM withdrawals 
    WHERE status = 'Approved'
";
$totalWithdrawnResult = $conn->query($totalWithdrawnQuery);
$totalWithdrawn = $totalWithdrawnResult->fetch_assoc()['total'] ?? 0;


// Format numbers (KES)
function formatKES($amount) {
  if ($amount >= 1000000) {
    return number_format($amount / 1000000, 1) . "M";
  }
  return number_format($amount);
}

/* =========================
   PLATFORM WALLET (SYSTEM)
========================= */
$SYSTEM_USER_ID = 21;

$stmt = $conn->prepare("
  SELECT balance, total_transacted
  FROM wallets
  WHERE user_id = ? AND wallet_type = 'administrator'
  LIMIT 1
");
$stmt->bind_param("i", $SYSTEM_USER_ID);
$stmt->execute();
$stmt->bind_result($platformBalance, $totalTransacted);
$stmt->fetch();
$stmt->close();

$platformBalance = $platformBalance ?? 0;
$totalTransacted = $totalTransacted ?? 0;

/* =========================
   GMV (ALL COMPLETED MONEY FLOW)
========================= */
$stmt = $conn->prepare("
  SELECT SUM(amount) 
  FROM financial_transactions
  WHERE status = 'completed'
");
$stmt->execute();
$stmt->bind_result($gmv);
$stmt->fetch();
$stmt->close();

$gmv = $gmv ?? 0;


/* =========================
   TOTAL COMMISSIONS (YOUR PROFIT SOURCE)
========================= */
$stmt = $conn->prepare("
    SELECT SUM(amount)
    FROM financial_transactions
    WHERE transaction_type = 'commission'
    AND status = 'completed'
");
$stmt->execute();
$stmt->bind_result($totalCommission);
$stmt->fetch();
$stmt->close();

$totalCommission = $totalCommission ?? 0;


/* =========================
   PENDING TRANSACTIONS
========================= */
$stmt = $conn->prepare("
  SELECT COUNT(*)
  FROM financial_transactions
  WHERE status = 'pending'
");
$stmt->execute();
$stmt->bind_result($pendingCount);
$stmt->fetch();
$stmt->close();


/* =========================
   NET PROFIT (SIMPLE VERSION)
========================= */
$operationalCosts = 3500; // you can later move this to DB
$netProfit = $totalCommission - $operationalCosts;


/* =========================
   MARGIN %
========================= */
$marginPercent = $gmv > 0 ? round(($platformBalance / $gmv) * 100) : 0;

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
  <div class="confirmation-popup" id="confirmation-popup">
    <h3 id="popupTitle">Confirm Action</h3>
    <p id="popupMessage">Are you sure?</p>

    <div class="popup-actions">
      <button id="confirmAction" class="btn-confirm">Yes, Confirm</button>
      <button id="cancelAction" class="btn-cancel">Cancel</button>
    </div>
  </div>

  <div class="confirmation-popup-overlay" id="confirmation-popup-overlay"></div>
  <div class="containerAdmin">
    <section>
      <h1>ADMIN&nbsp;PANEL<br><span>Makethub</span></h1>
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

              <div class="value profit">
                KES <?= number_format($platformBalance, 2) ?>
              </div>

              <div class="sub">Withdrawable Company Balance</div>

              <ul class="list">
                <li>
                  <span>API</span>
                  <strong>Online</strong>
                </li>

                <li>
                  <span>Total Transacted</span>
                  <strong>KES <?= number_format($totalTransacted, 2) ?></strong>
                </li>
              </ul>

              <small>
                ↑ Healthy margin (<?= $marginPercent ?>%)
              </small>
            </div>

            <div class="card">
              <h3>Gross Transaction Volume (GMV)</h3>
              <div class="value">KES <?= number_format($gmv, 2) ?></div>
              <div class="sub">All platform transactions</div>
              <div class="progress"><div class="bar" style="width:82%"></div></div>
              <small>↑ 18% growth vs last month</small>
            </div>

            <div class="card">
              <h3>Net Profit</h3>
              <div class="value net-profit">
                KES <?= number_format($netProfit, 2) ?>
              </div>
              <div class="sub">Commission − Operating Costs</div>
              <div class="progress"><div class="bar" style="width:71%"></div></div>
              <small>↑ This month's net profit</small>
            </div>

            <div class="card">
              <h3>Operational Costs</h3>
              <div class="value loss">
                KES -<?= number_format($operationalCosts, 2) ?>
              </div>
              <div class="sub">Monthly expenses</div>
              <ul class="list">
                <li><span>Hosting & Servers</span><strong>18,000</strong></li>
                <li><span>Payments (MPESA)</span><strong>22,500</strong></li>
                <li><span>Staff & Ops</span><strong>28,000</strong></li>
              </ul>
            </div>

            <div class="card">
              <div class="value">
                <?= $pendingCount > 10 ? 'Busy' : 'Stable' ?>
              </div>

              <ul class="list">
                <li><span>API</span><strong>Online</strong></li>
                <li><span>MPESA</span><strong>Connected</strong></li>
                <li>
                  <span>Pending</span>
                  <strong><?= $pendingCount ?></strong>
                </li>
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
              <option value="Completed">Completed</option>
              <option value="Pending">Pending</option>
              <option value="Processing">Processing</option>
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
            <?php
            $stmt = $conn->prepare("
                SELECT *
                FROM financial_transactions
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $stmt->execute();
            $result = $stmt->get_result();

            $index = 1;

            while ($row = $result->fetch_assoc()):
                
                $statusClass = match($row['status']) {
                    'completed' => 'paid',
                    'pending'   => 'pending',
                    'failed'    => 'rejected',
                    default     => 'pending'
                };

                $typeLabel = match($row['transaction_type']) {
                    'commission' => 'Commission',
                    'payment'    => 'Payment',
                    default      => ucfirst($row['transaction_type'])
                };
            ?>
            <tr data-status="<?= ucfirst($row['status']) ?>">
              <td><?= $index++ ?>.</td>

              <td>#TX<?= $row['transaction_id'] ?></td>

              <td><?= $typeLabel ?></td>

              <td>
                <?= htmlspecialchars($row['currency']) ?>
                <?= number_format($row['amount'], 2) ?>
              </td>

              <td>
                <?= $row['transaction_type'] === 'commission'
                    ? 'KES ' . number_format($row['amount'], 2)
                    : '-' ?>
              </td>

              <td>
                <span class="badge <?= $statusClass ?>">
                  <?= ucfirst($row['status']) ?>
                </span>
              </td>

              <td>
                <?= date('Y-m-d', strtotime($row['created_at'])) ?>
              </td>
            </tr>
            <?php endwhile; ?>
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
                <th>Economic&nbsp;P.</th>
                <th>Referred&nbsp;by</th>
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
              $name = ucfirst(strtolower($agent['username']));
              $referrer = !empty($agent['referrer_username']) 
                ? ucfirst(strtolower($agent['referrer_username'])) 
                : 'Direct Registration';

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

              <td class="sub-agents"><?= (int)$agent['total_sub_agents'] ?></td>
              <td class="economic"><?= (int)$agent['economic_period_count'] ?></td>
              <td><?= htmlspecialchars($referrer) ?></td>

              <td>KES 12,000</td>

              <td>Coast</td>

              <td>
                <span class="badge <?= $badgeClass ?>">
                  <?= $badgeText ?>
                </span>
              </td>

                <td class="actions">
                <div>
                <button class="btn-view"><i class="fa-solid fa-eye"></i></button>

                <button 
                class="btn-edit"
                data-user-id="<?= $agent['user_id'] ?>"
                data-tab="edit-forms"
                onclick="editRecord('agent', <?= (int)$agent['user_id'] ?>)">
                <i class="fa-solid fa-pen"></i>
                </button>

                <?php if ($agent['status'] === 'suspended'): ?>

                <button class="btn-restore action-btn"
                data-action="restore"
                data-user-id="<?= $agent['user_id'] ?>">
                <i class="fa-solid fa-trash-can-arrow-up"></i></button>

                <?php else: ?>

                <button class="btn-suspend action-btn"
                data-action="suspend"
                data-user-id="<?= $agent['user_id'] ?>">
                <i class="fa-solid fa-ban"></i></button>

                <?php endif; ?>


                <?php if ($agent['is_verified'] == 1): ?>

                <button class="btn-deactivate action-btn"
                data-action="deactivate"
                data-user-id="<?= $agent['user_id'] ?>">
                <i class="fa-solid fa-toggle-off"></i> Deactivate
                </button>

                <?php else: ?>

                <button class="btn-activate action-btn"
                data-action="activate"
                data-user-id="<?= $agent['user_id'] ?>">
                <i class="fa-solid fa-toggle-on"></i> Activate
                </button>

                <?php endif; ?>


                <button 
                class="btn-copy-link copy-link-btn"
                data-ref="<?= htmlspecialchars($agent['agency_code']) ?>">
                <i class="fa-solid fa-link"></i> Copy&nbsp;Link
                </button>

                <button class="btn-delete action-btn"
                data-action="delete"
                data-user-id="<?= $agent['user_id'] ?>">
                <i class="fa-solid fa-trash-can"></i>
                </button>

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
                      : "Images/Makethub Logo.avif";
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
                      </button>                <?php if ($seller['status'] === 'suspended'): ?>

                      <button class="btn-restore action-btn"
                      data-action="restore"
                      data-user-id="<?= $seller['user_id'] ?>">
                      <i class="fa-solid fa-trash-can-arrow-up"></i></button>

                      <?php else: ?>

                      <button class="btn-suspend action-btn"
                      data-action="suspend"
                      data-user-id="<?= $seller['user_id'] ?>">
                      <i class="fa-solid fa-ban"></i></button>

                      <?php endif; ?>

                      <button class="btn-delete action-btn"
                      data-action="delete"
                      data-user-id="<?= $seller['user_id'] ?>">
                      <i class="fa-solid fa-trash-can"></i>
                      </button>
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
                          <?php if ($buyer['status'] === 'suspended'): ?>

                          <button class="btn-restore action-btn"
                          data-action="restore"
                          data-user-id="<?= $buyer['user_id'] ?>">
                          <i class="fa-solid fa-trash-can-arrow-up"></i></button>

                          <?php else: ?>

                          <button class="btn-suspend action-btn"
                          data-action="suspend"
                          data-user-id="<?= $buyer['user_id'] ?>">
                          <i class="fa-solid fa-ban"></i></button>

                          <?php endif; ?>

                          <button class="btn-delete action-btn"
                          data-action="delete"
                          data-user-id="<?= $buyer['user_id'] ?>">
                          <i class="fa-solid fa-trash-can"></i>
                          </button>
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
                    <?php if ($owner['status'] === 'suspended'): ?>

                    <button class="btn-restore action-btn"
                    data-action="restore"
                    data-user-id="<?= $owner['user_id'] ?>">
                    <i class="fa-solid fa-trash-can-arrow-up"></i></button>

                    <?php else: ?>

                    <button class="btn-suspend action-btn"
                    data-action="suspend"
                    data-user-id="<?= $owner['user_id'] ?>">
                    <i class="fa-solid fa-ban"></i></button>

                    <?php endif; ?>

                    <button class="btn-delete action-btn"
                    data-action="delete"
                    data-user-id="<?= $buyer['user_id'] ?>">
                    <i class="fa-solid fa-trash-can"></i>
                    </button>
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

            <!-- Pending -->
            <div class="card sub-card">
              <i class="fa-solid fa-hourglass-half"></i>
              <div>
                <h3>Pending Requests</h3>
                <div class="value"><?php echo $pendingCount; ?></div>
              </div>
            </div>

            <!-- Approved Today -->
            <div class="card sub-card">
              <i class="fa-solid fa-check-circle"></i>
              <div>
                <h3>Approved Today</h3>
                <div class="value">KES <?php echo formatKES($approvedToday); ?></div>
                <small>Based on today's processed withdrawals</small>
              </div>
            </div>

            <!-- Total Withdrawn -->
            <div class="card sub-card">
              <i class="fa-solid fa-money-bill"></i>
              <div>
                <h3>Total Withdrawn</h3>
                <div class="value">KES <?php echo formatKES($totalWithdrawn); ?></div>
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
                <th>#</th>
                <th>User</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Fee</th>
                <th>Method</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Talk</th>
                <th>Requested&nbsp;At:</th>
                <th>Updated&nbsp;At:</th>
              </tr>
            </thead>
            <tbody>
            <?php $count = 1; ?>
            <?php 
            while ($row = $withdrawalsQuery->fetch_assoc()):

              // Safe data
              $name = safe($row['full_name']);
              $phoneRaw = decodePhone($row['phone']);
              $phone = maskPhone($phoneRaw);
              $accountType = ucfirst(str_replace('_', ' ', $row['account_type']));
              $amount = number_format($row['amount']);
              $fee = number_format($row['fee']);
              $method = ucfirst($row['method']);
              $status = strtolower($row['status']);

              $email = decodeEmail($row['email']);

              // Image
              $defaultAvatar = "https://cdn-icons-png.flaticon.com/512/149/149071.png";
              $profileImage = (!empty($row['profile_image']) && file_exists($row['profile_image']))
                  ? htmlspecialchars($row['profile_image'], ENT_QUOTES, 'UTF-8')
                  : $defaultAvatar;

              // Status class
              $statusClass = "pending";
              if ($status === "approved") $statusClass = "approved";
              if ($status === "rejected") $statusClass = "rejected";

            ?>
            <tr data-status="<?= $status ?>">
              
              <td><?= $count++ ?>.</td>

              <!-- USER -->
              <td>
                <div class="adm-user-profile">
                  <img src="<?= $profileImage ?>" style="border-radius:50%">
                  <?= $name ?>
                </div>
                <em><?= $phone ?></em>
              </td>

              <!-- ROLE -->
              <td><?= $accountType ?></td>

              <!-- AMOUNT -->
              <td>KES <?= $amount ?></td>

              <!-- FEE -->
              <td>KES <?= $fee ?></td>

              <!-- METHOD -->
              <td><?= $method ?></td>

              <!-- STATUS -->
              <td>
                <span class="badge <?= $statusClass ?>">
                  <?= ucfirst($status) ?>
                </span>
              </td>

              <!-- ACTIONS -->
              <td class="actions">
                <div>

                  <?php if ($status === 'pending'): ?>
                    <button class="btn-edit" data-id="<?= $row['withdrawal_id'] ?>">Approve</button>
                    <button class="btn-suspend" data-id="<?= $row['withdrawal_id'] ?>">
                      Reject <i class="fa-solid fa-ban"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>

              <!-- CONTACT -->
              <td class="comm-cell">
                <button class="comm-btn">
                  <i class="fas fa-ellipsis-vertical"></i>
                </button>

                <div class="comm-dropdown">
                  <a href="tel:<?= $phoneRaw ?>"><i class="fas fa-phone"></i> Call</a>
                  <a href="https://wa.me/<?= preg_replace('/^\+/', '', $phoneRaw) ?>" target="_blank">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                  </a>
                  <a href="mailto:<?= $email ?>"><i class="fas fa-envelope"></i> Email</a>
                  <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                </div>
              </td>

              <!-- DATES -->
              <td><?= date('Y-m-d', strtotime($row['requested_at'])) ?></td>
              <td><?= $row['updated_at'] ? date('Y-m-d', strtotime($row['updated_at'])) : '-' ?></td>

            </tr>

            <?php endwhile; ?>
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
              <option value="Completed">Completed</option>
              <option value="Pending">Pending</option>
              <option value="Rejected">Rejected</option>
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
              <?php

              $query = "
              SELECT 
                  ft.transaction_id,
                  ft.transaction_type,
                  ft.amount,
                  ft.status,
                  ft.created_at,

                  payer.account_type AS payer_type,
                  receiver.account_type AS receiver_type

              FROM financial_transactions ft
              LEFT JOIN users payer ON ft.payer_id = payer.user_id
              LEFT JOIN users receiver ON ft.receiver_id = receiver.user_id

              ORDER BY ft.created_at DESC
              ";

              $result = $conn->query($query);

              $index = 1;

              while ($row = $result->fetch_assoc()) {

                  // Format type
                  $type = ($row['payer_type'] ?? 'User') . " → " . ($row['receiver_type'] ?? 'User');

                  // Commission (example 10%)
                  $commission = $row['amount'] * 0.10;

                  // Status class
                  $status = strtolower($row['status']);
                  $statusClass = "pending";

                  if ($status === "completed") $statusClass = "paid";
                  if ($status === "rejected") $statusClass = "rejected";

                  echo "
                  <tr data-status='{$status}'>
                      <td>{$index}.</td>
                      <td>#TX{$row['transaction_id']}</td>
                      <td>{$type}</td>
                      <td>KES " . number_format($row['amount']) . "</td>
                      <td>KES " . number_format($commission) . "</td>
                      <td><span class='badge {$statusClass}'>" . ucfirst($status) . "</span></td>
                      <td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>
                  </tr>
                  ";

                  $index++;
              }
              ?>
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
                <div class="value"><?= number_format($totalProducts) ?></div>
                <small>Products in system</small>
                </div>
            </div>
            <div class="card sub-card">

                <i class="fa-solid fa-circle-check"></i>
                <div>
                <h3>Active Products</h3>
                <div class="value"><?= number_format($activeProducts) ?></div>
                <small>Currently visible</small>
                </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-ban"></i>
              <div>
              <h3>Inactive Products</h3>
              <div class="value"><?= number_format($inactiveProducts) ?></div>
              <small>Is-Active value = 0</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-coins"></i>
              <div>
                <h3>Total Product Value</h3>
                <div class="value">
                KES <?= number_format($totalValue, 2) ?>
                </div>
              </div>
            </div>
        </div>
        </div>
        <div class="tabs-container">
          <div class="tabs">
          <?php 
          $first = true;
          foreach ($groupedProducts as $category => $items): 
          ?>
            <button 
              class="tab-btn-admin" data-tab="<?= safeCategoryId($category) ?>">
              <?= htmlspecialchars(ucwords(strtolower($category))) ?>
            </button>
          <?php 
          $first = false;
          endforeach; 
          ?>
          </div>
            <div id="company-products" class="tab-panel-admin">
                <div class="tab-top">
                  <p>Market Hub Store<br><strong>Your control center for Market Hub products <i class="fa-regular fa-circle-check"></i></strong></p>
                  <button class="btn-edit" data-tab="edit-forms" onclick="openAddProductForm('product')">
                  <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Product</span>
                  </button>

                </div>
                <!-- PRODUCTS GRID -->
                <?php if (empty($groupedProducts)): ?>
                  <p class="mproducts-alert-p">No products found</p>
                <?php endif; ?>
                <?php 
                $first = true;
                foreach ($groupedProducts as $category => $items): 
                ?>
                
                <div id="<?= safeCategoryId($category) ?>" class="products-grid-admin">

                <?php if (empty($items)): ?>
                  <p class="noproducts-admin-p">No products in this category.</p>
                <?php else: ?>

                  <?php foreach ($items as $product): ?>
                    <div class="product-card">

                      <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" loading="lazy">

                      <div class="product-name">
                        <?= htmlspecialchars($product['product_name']) ?>
                      </div>

                      <div class="product-price">
                        <?= htmlspecialchars($product['currency']) ?> 
                        <strong><?= number_format($product['price'], 2) ?></strong>
                      </div>

                      <p class="product-description">
                        <?= htmlspecialchars($product['description']) ?>
                      </p>

                      <div class="card-actions">

                        <!-- EDIT -->

                        <button 
                        class="btn-edit"
                        data-user-id="<?= $product['id'] ?>"
                        data-tab="edit-forms"
                        onclick="editRecord('product', <?= (int)$product['id'] ?>)">
                        <i class="fa fa-pen"></i> Edit
                        </button>

                        <!-- DELETE -->
                      <button 
                        class="delete-product-btn"
                        data-product-id="<?= (int)$product['id'] ?>">
                        <i class="fa fa-trash"></i> Delete
                      </button>

                      </div>

                    </div>
                  <?php endforeach; ?>

                <?php endif; ?>

                </div>

                <?php 
                $first = false;
                endforeach; 
                ?>
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
                    <option value="rentals" <?php echo ($edit_business_model === 'rentals') ? 'selected' : ''; ?>>Rentals</option>
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
                    <option value="rentals" <?php echo ($edit_business_type === 'rentals') ? 'selected' : ''; ?>>Rentals</option>
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

              <?php if ($mproductEditMode): ?>
                  <input type="hidden" name="edit_product_id" value="<?= $mproductEditProductId ?>">
              <?php endif; ?>

              <h1><?= $mproductEditMode ? 'Edit Product' : 'Add Product' ?></h1>

              <?php if (!empty($mproductError)): ?>
                  <p class="errorMessage">
                      <i class="fa-solid fa-circle-exclamation"></i>
                      <?= htmlspecialchars($mproductError); ?>
                  </p>
              <?php endif; ?>

              <?php if (!empty($mproductSuccess)): ?>
                  <p class="successMessage">
                      <i class="fa-solid fa-check-circle"></i>
                      <?= $mproductSuccess; ?>
                  </p>
              <?php endif; ?>

              <div class="formBody">
                  <div class="inp-box">
                      <label>Product Name</label>
                      <input type="text" name="name" placeholder="Enter name" 
                          value="<?= htmlspecialchars($mproductProductName, ENT_QUOTES) ?>" required>
                  </div>
                  <div class="inp-box">

                    <label>Category</label>
                    <select name="category" required>
                      <option value=""><p>-- Select category --</p></option>
                      <option value="Beauty" <?php echo ($mproductCategory === 'Beauty') ? 'selected' : ''; ?>>Beauty</option>
                      <option value="Electronics" <?php echo ($mproductCategory === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                      <option value="Fashions" <?php echo ($mproductCategory === 'Fashions') ? 'selected' : ''; ?>>Fashions</option>
                      <option value="Food & Snacks" <?php echo ($mproductCategory === 'Food & Snacks') ? 'selected' : ''; ?>>Food & Snacks</option>
                      <option value="Home Items" <?php echo ($mproductCategory === 'Home Items') ? 'selected' : ''; ?>>Home Items</option>
                      <option value="Stationery" <?php echo ($mproductCategory === 'Stationery') ? 'selected' : ''; ?>>Stationery</option>
                    </select>
                  </div>
                  <div class="inp-box">
                    <label>Price (KES)</label>
                    <input type="number" name="price" step="0.01" placeholder="Enter price"
                    value="<?= htmlspecialchars($mproductPrice, ENT_QUOTES) ?>"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '')" min="0" required>
                  </div>
                  <div class="inp-box">

                    <label>Currency :</label>
                    <select name="currency" required>
                      <option value=""><p>-- Select currency --</p></option>
                      <option value="KES" <?php echo ($mproductCurrency === 'KES') ? 'selected' : ''; ?>>KES</option><!-- 
                      <option value="USD" <?php echo ($mproductCurrency === 'USD') ? 'selected' : ''; ?>>USD</option>
                      <option value="TSH" <?php echo ($mproductCurrency === 'TSH') ? 'selected' : ''; ?>>TSH</option> -->
                    </select>
                  </div>
                  <div class="inp-box">
                    <label>Is Active?</label>
                    <select id="is_active" name="is_active" required>
                      <option value=""><p>-- Select if active --</p></option>
                      <option value="1" <?php echo ($mproductIs_active == '1') ? 'selected' : ''; ?>>Yes</option>
                      <option value="0" <?php echo ($mproductIs_active == '0') ? 'selected' : ''; ?>>No</option>
                    </select>
                  </div>

                  <?php if ($mproductEditMode): ?>
                      <!-- IMAGE PREVIEW ONLY IN EDIT MODE -->
                      <div></div>
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
                      </div>
                  <?php else: ?>
                    <!-- ONLY FOR ADD MODE -->
                    <div class="inp-box">
                      <label>Upload Product Image</label>
                      <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" required>
                    </div>
                  <?php endif; ?>
                  <div class="inp-box">
                    <label>Description (max 150 characters)</label>

                    <textarea 
                      name="description" 
                      id="productDescription"
                      placeholder="Enter product description"
                      maxlength="<?= $descMaxLength ?>" 
                      required><?= safe($productDesc); ?></textarea>

                    <div class="char-counter">
                    <small id="charCount"><?= strlen($productDesc) ?>/<?= $descMaxLength ?> characters</small>
                  </div>

                  <button type="submit">
                    <?= $mproductEditMode ? 'Update Product' : 'Add Product' ?>
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

  <script>
  const productDescription = document.getElementById("productDescription");
  const charCount = document.getElementById("charCount");

  productDescription.addEventListener("input", () => {
      const len = productDescription.value.length;
      charCount.textContent = `${len}/<?= $descMaxLength ?> characters`;
  });
  </script>
</body>
</html>
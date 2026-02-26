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

$query = "SELECT username, profile_image FROM users WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("System error.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$username = "User";
$profileImage = null;

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (!empty($user['username'])) {
        $username = $user['username'];
    }

    $profileImage = $user['profile_image'] ?? null;
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

$defaultAvatar = "Images/Market Hub Logo.avif";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_product_id'])) {
    // ---------- FORM INPUTS ----------
    $productName = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);

    // ---------- VALIDATION ----------
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
      $error = "Stock quantity cannot be negative.";
    }

    elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
      $error = "Please upload a product image.";
    }


    // ---------- IMAGE VALIDATION ----------
    if (empty($error)) {

        $fileTmp  = $_FILES['photo']['tmp_name'];
        $fileSize = $_FILES['photo']['size'];
        $mime     = mime_content_type($fileTmp);

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowed)) {
          $error = "Invalid image format. Use JPG, PNG or WebP.";
        }
        elseif ($fileSize > 5 * 1024 * 1024) {
          $error = "Image too large. Max size is 5MB.";
        }
        $imgInfo = getimagesize($fileTmp);
        if (!$imgInfo) {
          $error = "Uploaded file is not a valid image.";
        }
        
        if (empty($error)) {
        $imgHash = md5_file($fileTmp);
    }
    }
    if (empty($error)) {

        $dupStmt = $conn->prepare("
            SELECT product_name, image_hash 
            FROM productservicesrentals 
            WHERE user_id = ? 
            AND (product_name = ? OR image_hash = ?) 
            LIMIT 1
        ");

        $dupStmt->bind_param("iss", $user_id, $productName, $imgHash);
        $dupStmt->execute();
        $dupStmt->store_result();
        $dupStmt->bind_result($existingName, $existingHash);

        if ($dupStmt->num_rows > 0) {
            $dupStmt->fetch();

            if ($existingName === $productName) {
                $error = "Product name already exists.";
            }
            elseif ($existingHash === $imgHash) {
                $error = "Image already exists.";
            }
        }

        $dupStmt->close();
    }

    // ---------- IMAGE RESIZE & SAVE ----------
    if (empty($error)) {

        [$width, $height] = $imgInfo;

        if ($width < 600 || $height < 600) {
            $error = "Image too small. Minimum size is 600×600 px.";
        } else {

            $maxSize = 700;
            $ratio   = min($maxSize / $width, $maxSize / $height, 1);

            $newWidth  = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $canvas = imagecreatetruecolor($newWidth, $newHeight);

            switch ($mime) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($fileTmp);
                    // ----- FIX ORIENTATION -----
                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($fileTmp);
                        if (!empty($exif['Orientation'])) {
                            switch ($exif['Orientation']) {
                                case 3:
                                    $source = imagerotate($source, 180, 0);
                                    break;
                                case 6:
                                    $source = imagerotate($source, -90, 0);
                                    break;
                                case 8:
                                    $source = imagerotate($source, 90, 0);
                                    break;
                            }
                        }
                    }

                    break;
                case 'image/png':
                    $source = imagecreatefrompng($fileTmp);
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($fileTmp);
                    break;
            }

            imagecopyresampled(
                $canvas,
                $source,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $width,
                $height
            );

            // Upload directory
            $uploadDir = 'uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid('product_', true) . '.webp';
            $filePath = $uploadDir . $fileName;

            imagewebp($canvas, $filePath, 75);

            imagedestroy($canvas);
            imagedestroy($source);

            $fileSizeKB = round(filesize($filePath) / 1024);

            // ---------- INSERT INTO DATABASE ----------
            $stmt = $conn->prepare("
                INSERT INTO productservicesrentals (user_id, product_name, category, price, stock_quantity, image_path, image_width, image_height, image_size_kb, image_format, image_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'webp', ?)
            ");

            $stmt->bind_param(
                "issdissiis",
                $user_id,
                $productName,
                $category,
                $price,
                $stock,
                $filePath,
                $newWidth,
                $newHeight,
                $fileSizeKB,
                $imgHash
            );

            if ($stmt->execute()) {
              $success = "Product added successfully! <span id='count'>3</span>…";
              // ✅ Reset the form variables
              $productName = '';
              $category    = '';
              $price       = '';
              $stock       = '';
            } else {
              $error = "Failed to save product. Please try again.";
            }

            $stmt->close();
        }
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

  <link rel="stylesheet" href="styles/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <!-- jQuery + DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <title>Seller Page | Market Hub</title>
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
          <a class="lkOdr" onclick="toggleSellerOrdersTrack()">
            <div class="odrIconDiv">
              <i class="fa-brands fa-first-order-alt"></i>
              <p>8</p>
            </div>
            <p>Order(s)</p>
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
          <div class="profile-icon" onclick="toggleProfileOption()">
            <i class="fa-regular fa-user"></i>
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

    <main class="buyerMain" id="productsAgentMain">
      <div class="tab-top">
        <p>Products main page<br><strong>View products, download and post products <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleSellerOrdersTrack()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>
      <div class="table-wrapper sellerOrdersTrack active">
        <div class="header">
          <h1>Market Hub Daily Products</h1>
          <p>Download and post across all platforms today.</p>
        </div>

        <div class="products-grid" id="productsContainer"></div>
      </div>
      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleAgentEarningsTrack()">Go&nbsp;back</button> to continue with sales.</p>
    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>

<script>

// ==============================
// DAILY PRODUCTS CONFIGURATION
// ==============================

const dailyProducts = [
  {
    id: 1,
    name: "Executive Leather Laptop Bag",
    price: "KES 1,300",
    description: "Premium executive laptop bag. Durable and stylish.",
    image: "Images/Executive Leather Laptop Bag.png"
  },
  {
    id: 2,
    name: "6 Litre Electric Pressure Cooker",
    price: "KES 5,200",
    description: "Fast cooking, energy saving, perfect for family meals.",
    image: "Images/6 Litre Electric Pressure Cooker.png"
  },
  {
    id: 3,
    name: "16-inch Standing Fan",
    price: "KES 2,350",
    description: "Powerful airflow with adjustable height.",
    image: "Images/Ipcone 16-inch standing fan.png"
  },
  {
    id: 4,
    name: "Large Travel Duffel Bag",
    price: "KES 1,250",
    description: "Spacious travel bag. Ideal for weekend trips.",
    image: "Images/Large Travel Duffel Bag.png"
  },
  {
    id: 5,
    name: "Velvet Curtains",
    price: "KES 2,700",
    description: "Elegant home curtains. Premium soft material.",
    image: "Images/Velvet Curtains.png"
  }
];

// ==============================
// RENDER PRODUCTS
// ==============================

const container = document.getElementById("productsContainer");
const today = new Date().getDay(); // 0 = Sunday

dailyProducts.forEach(product => {

  const card = document.createElement("div");
  card.className = "product-card";

  card.innerHTML = `
    <img src="${product.image}" alt="${product.name}">
    <div class="product-name">${product.name}</div>
    <div class="product-price">${product.price}</div>
    <div class="product-description">${product.description}</div>
    <button class="download-btn" data-id="${product.id}">
      Download for Posting
    </button>
  `;

  container.appendChild(card);
});

// ==============================
// DOWNLOAD + SAVE TO LOCAL STORAGE
// ==============================

document.addEventListener("click", function(e) {

  if (!e.target.classList.contains("download-btn")) return;
  if (today === 0) return;

  const id = parseInt(e.target.dataset.id);
  const product = dailyProducts.find(p => p.id === id);

  // Save individually
  localStorage.setItem(
    "marketHubDailyProduct_" + product.id,
    JSON.stringify(product)
  );

  // Trigger image download
  const link = document.createElement("a");
  link.href = product.image;
  link.download = product.name.replace(/\s+/g, "_") + ".jpg";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  alert(product.name + " saved locally. Ready for posting.");

});

</script>
</body>
</html>
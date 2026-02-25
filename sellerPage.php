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

    <main class="buyerMain" id="sellerMain">
      <div class="tabs-container" id="toggleMarketTypeTab">
        <div class="tabs">
          <button class="tab-btn" data-tab="dashboard">Dashboard</button>
          <button class="tab-btn" data-tab="products">Products</button>
          <button class="tab-btn" data-tab="funds" onclick="togglePaymentOption()">Funds</button>
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
                  <div class="stat">KES 12,450</div>
                  <p class="meta">Available for withdrawal</p>
                  <div class="progress"><span style="width:75%"></span></div>
                  <p class="small">KES 3,200 pending clearance</p>
                </div>

                <!-- WITHDRAWAL READINESS -->
                <div class="card">
                  <i class="fa fa-money-bill-wave icon"></i>
                  <h3>Withdrawal Status</h3>
                  <span class="badge green">Eligible</span>
                  <p class="meta">Minimum threshold met</p>
                  <div class="actions">
                    <button onclick="togglePaymentOption()">Withdraw</button>
                  </div>
                  <p class="small">Last withdrawal: KES 5,000 • 10 Feb</p>
                </div>

                <!-- ORDERS SUMMARY -->
                <div class="card">
                  <i class="fa fa-box icon"></i>
                  <h3>Orders Summary</h3>
                  <div class="stat">18 Orders</div>
                  <p class="meta">
                    <span class="badge yellow">5&nbsp;Processing</span>
                    <span class="badge blue">3&nbsp;Shipped</span>
                    <span class="badge green">10&nbsp;Delivered</span>
                  </p>
                </div>

                <!-- CUSTOMER TRUST -->
                <div class="card">
                  <i class="fa fa-star icon"></i>
                  <h3>Customer Trust</h3>
                  <div class="stat">4.7 ★</div>
                  <p class="meta">From 213 reviews</p>
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
                            <?= ($product['stock_quantity'] > 0) ? "In stock ({$product['stock_quantity']})" : "Out of stock" ?>
                        </div>
                      </div>
                      <div class="card-actions">
                          <button href="editProduct.php?id=<?= $product['product_id'] ?>" class="edit"><i class="fa fa-pen"></i> Edit</button>
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
              <button onclick="toggleProductsAdd(false)">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>

            </div>
            <div class="form-wrapper">
              <form method="POST" enctype="multipart/form-data">
                <h1>Add Product</h1>
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
                    <input type="text" name="name" placeholder="Enter name" value="<?= htmlspecialchars($productName, ENT_QUOTES) ?>" required>
                  </div>

                  <div class="inp-box">
                      <label>Category</label>
                      <select name="category" required>
                          <option value="">--Select category--</option>
                          <option <?= ($category === 'Beauty') ? 'selected' : '' ?>>Beauty</option>
                          <option <?= ($category === 'Electronics') ? 'selected' : '' ?>>Electronics</option>
                          <option <?= ($category === 'Fashions') ? 'selected' : '' ?>>Fashions</option>
                          <option <?= ($category === 'Food & Snacks') ? 'selected' : '' ?>>Food & Snacks</option>
                          <option <?= ($category === 'Home Items') ? 'selected' : '' ?>>Home Items</option>
                          <option <?= ($category === 'Stationery') ? 'selected' : '' ?>>Stationery</option>
                      </select>
                  </div>

                  <div class="inp-box">
                      <label>Price (KES)</label>
                      <input type="number" name="price" step="1" placeholder="Enter price" value="<?= htmlspecialchars($price, ENT_QUOTES) ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="0" required>
                  </div>

                  <div class="inp-box">
                      <label>Stock Quantity</label>
                      <input type="number" name="stock" placeholder="e.g 24" value="<?= htmlspecialchars($stock, ENT_QUOTES) ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="0" step="1" required>
                  </div>
                  <div class="inp-box">
                    <label>Product Image</label>
                    <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" required>
                    
                    <div class="note">
                      600×600 – 1600×1600 px • Max 5MB<br>
                      Automatically optimized for buyers
                    </div>
                  </div>
                  <div></div>
                  <button type="submit">
                    Add Product
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
                  <!-- WALLET HEALTH -->
                  <div class="card">
                    <i class="fa fa-wallet icon"></i>
                    <h3>Wallet Health</h3>
                    <div class="stat">KES 12,450</div>
                    <p class="meta">Available for withdrawal</p>
                    <div class="progress"><span style="width:75%"></span></div>
                    <p class="small">KES 3,200 pending clearance</p>
                  </div>
                  <div>
                    <div class="inp-box">
                      <label>Withdrawal Amount</label>
                      <input type="number" placeholder="Enter amount">
                      <button type="button">Request Withdrawal</button>
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
          <option value="Delivered">Delivered</option>
          <option value="Shipped">Shipped</option>
          <option value="Processing">Processing</option>
        </select>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="table-wrapper">
        <table id="ordersTable">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Buyer</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr data-status="Delivered">
              <td>ORD-10021</td>
              <td>John Doe</td>
              <td>2</td>
              <td>KES 7,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge delivered">Delivered</span></td>
              <td>12 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Processing">
              <td>ORD-10022</td>
              <td>Jane Smith</td>
              <td>1</td>
              <td>KES 6,800</td>
              <td><span class="badge pending">Pending</span></td>
              <td><span class="badge processing">Processing</span></td>
              <td>13 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Shipped">
              <td>ORD-10023</td>
              <td>Mary Johnson</td>
              <td>3</td>
              <td>KES 12,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge shipped">Shipped</span></td>
              <td>14 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Processing">
              <td>ORD-10022</td>
              <td>Jane Smith</td>
              <td>1</td>
              <td>KES 6,800</td>
              <td><span class="badge pending">Pending</span></td>
              <td><span class="badge processing">Processing</span></td>
              <td>13 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Shipped">
              <td>ORD-10023</td>
              <td>Mary Johnson</td>
              <td>3</td>
              <td>KES 12,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge shipped">Shipped</span></td>
              <td>14 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Processing">
              <td>ORD-10022</td>
              <td>Jane Smith</td>
              <td>1</td>
              <td>KES 6,800</td>
              <td><span class="badge pending">Pending</span></td>
              <td><span class="badge processing">Processing</span></td>
              <td>13 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Shipped">
              <td>ORD-10023</td>
              <td>Mary Johnson</td>
              <td>3</td>
              <td>KES 12,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge shipped">Shipped</span></td>
              <td>14 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <!-- Add more rows here as needed -->
          </tbody>
        </table>
      </div>

      <!-- MOBILE CARDS -->
      <div class="cards" id="orderCards">

        <div class="order-card" data-status="Processing">
          <div class="card-header">
            <img src="Images/Market Hub Logo.avif" class="product-img">
            <div>
              <div class="card-title">Smart Watch</div>
              <div class="card-meta">Order: MH-10702 • Global</div>
            </div>
          </div>

          <div class="card-row">
            <span>Price</span>
            <strong>KES 6,800</strong>
          </div>

          <div class="card-row">
            <span>Status</span>
            <span class="badge processing">Processing</span>
          </div>

          <div class="card-actions">
            <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
          </div>
        </div>

        <div class="order-card" data-status="Processing">
          <div class="card-header">
            <img src="Images/Market Hub Logo.avif" class="product-img">
            <div>
              <div class="card-title">Smart Watch</div>
              <div class="card-meta">Order: MH-10702 • Global</div>
            </div>
          </div>

          <div class="card-row">
            <span>Price</span>
            <strong>KES 6,800</strong>
          </div>

          <div class="card-row">
            <span>Status</span>
            <span class="badge processing">Processing</span>
          </div>

          <div class="card-actions">
            <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
          </div>
        </div>

        <div class="order-card" data-status="Delivered">
          <div class="card-header">
            <img src="Images/Market Hub Logo.avif" class="product-img">
            <div>
              <div class="card-title">Wireless Headphones</div>
              <div class="card-meta">Order: MH-10231 • National</div>
            </div>
          </div>

          <div class="card-row">
            <span>Price</span>
            <strong>KES 3,500</strong>
          </div>

          <div class="card-row">
            <span>Status</span>
            <span class="badge delivered">Delivered</span>
          </div>

          <div class="card-actions">
            <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
          </div>
        </div>


        <div class="order-card" data-status="Delivered">
          <div class="card-header">
            <img src="Images/Market Hub Logo.avif" class="product-img">
            <div>
              <div class="card-title">Wireless Headphones</div>
              <div class="card-meta">Order: MH-10231 • National</div>
            </div>
          </div>

          <div class="card-row">
            <span>Price</span>
            <strong>KES 3,500</strong>
          </div>

          <div class="card-row">
            <span>Status</span>
            <span class="badge delivered">Delivered</span>
          </div>

          <div class="card-actions">
            <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
          </div>
        </div>


        <div class="order-card" data-status="Processing">
          <div class="card-header">
            <img src="Images/Market Hub Logo.avif" class="product-img">
            <div>
              <div class="card-title">Smart Watch</div>
              <div class="card-meta">Order: MH-10702 • Global</div>
            </div>
          </div>

          <div class="card-row">
            <span>Price</span>
            <strong>KES 6,800</strong>
          </div>

          <div class="card-row">
            <span>Status</span>
            <span class="badge processing">Processing</span>
          </div>

          <div class="card-actions">
            <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
          </div>
        </div>

        <div class="order-card" data-status="Processing">
          <div class="card-header">
            <img src="Images/Market Hub Logo.avif" class="product-img">
            <div>
              <div class="card-title">Smart Watch</div>
              <div class="card-meta">Order: MH-10702 • Global</div>
            </div>
          </div>

          <div class="card-row">
            <span>Price</span>
            <strong>KES 6,800</strong>
          </div>

          <div class="card-row">
            <span>Status</span>
            <span class="badge processing">Processing</span>
          </div>

          <div class="card-actions">
            <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
          </div>
        </div>
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
              <th>#</th>
              <th>Order ID</th>
              <th>Buyer</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr data-status="Delivered">
              <td>1.</td>
              <td>ORD-10021</td>
              <td>John Doe</td>
              <td>2</td>
              <td>KES 7,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge delivered">Delivered</span></td>
              <td>12 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Processing">
              <td>2.</td>
              <td>ORD-10022</td>
              <td>Jane Smith</td>
              <td>1</td>
              <td>KES 6,800</td>
              <td><span class="badge pending">Pending</span></td>
              <td><span class="badge processing">Processing</span></td>
              <td>13 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Shipped">
              <td>3.</td>
              <td>ORD-10023</td>
              <td>Mary Johnson</td>
              <td>3</td>
              <td>KES 12,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge shipped">Shipped</span></td>
              <td>14 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Processing">
              <td>4.</td>
              <td>ORD-10022</td>
              <td>Jane Smith</td>
              <td>1</td>
              <td>KES 6,800</td>
              <td><span class="badge pending">Pending</span></td>
              <td><span class="badge processing">Processing</span></td>
              <td>13 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Shipped">
              <td>5.</td>
              <td>ORD-10023</td>
              <td>Mary Johnson</td>
              <td>3</td>
              <td>KES 12,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge shipped">Shipped</span></td>
              <td>14 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Processing">
              <td>6.</td>
              <td>ORD-10022</td>
              <td>Jane Smith</td>
              <td>1</td>
              <td>KES 6,800</td>
              <td><span class="badge pending">Pending</span></td>
              <td><span class="badge processing">Processing</span></td>
              <td>13 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <tr data-status="Shipped">
              <td>7.</td>
              <td>ORD-10023</td>
              <td>Mary Johnson</td>
              <td>3</td>
              <td>KES 12,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge shipped">Shipped</span></td>
              <td>14 Feb 2026</td>
              <td class="actions">
                <div>
                <button class="btn-ship">Mark&nbsp;as&nbsp;Shipped</button>
                </div>
              </td>
            </tr>

            <!-- Add more rows here as needed -->
          </tbody>
        </table>
      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleSellerOrdersTrack()">Go&nbsp;back</button> to continue delivering.</p>
    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript" defer></script>
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
  <script>
    let seconds = 3;
    const counter = document.getElementById("count");

    const interval = setInterval(() => {
      seconds--;
      counter.textContent = seconds;
      if (seconds <= 0) {
        clearInterval(interval);
        window.location.href = "sellerPage.php";
      }
    }, 1000);
  </script>
</body>
</html>
<?php
session_start();
require_once 'connection.php';

/* ---------- SESSION SECURITY ---------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/* =========================
   EDIT MODE FETCH
========================= */
if (isset($_GET['edit_id'])) {
    $mproductEditProductId = intval($_GET['edit_id']);

    $stmt = $conn->prepare("SELECT * FROM markethub_products WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $mproductEditProductId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        $mproductProductName = $row['product_name'];
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

    $mproductProductName = trim($_POST['name']);
    $mproductPrice = floatval($_POST['price']);
    $mproductCurrency = $_POST['currency'];
    $mproductProductDescription = trim($_POST['description']);
    $mproductIs_active = $_POST['is_active'];

    if ($mproductProductName === '') $mproductError = "Product name required.";
    elseif ($mproductPrice <= 0) $mproductError = "Invalid price.";
    elseif ($mproductCurrency === '') $mproductError = "Select currency.";
    elseif ($mproductProductDescription === '') $mproductError = "Description required.";
    elseif ($mproductIs_active === '') $mproductError = "Select active status.";

    /* =========================
       IMAGE PROCESSING
    ========================= */
    $imagePath = null;

    if ((!$mproductEditMode) || (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0)) {

        $tmp = $_FILES['photo']['tmp_name'];
        $size = $_FILES['photo']['size'];
        $mime = mime_content_type($tmp);

        $allowed = ['image/jpeg','image/png','image/webp'];

        if (!in_array($mime, $allowed)) {
            $mproductError = "Invalid image format.";
        }

        $imgInfo = getimagesize($tmp);
        if (!$imgInfo) {
            $mproductError = "Invalid image.";
        }

        list($width, $height) = $imgInfo;

        /* ✅ ENFORCE SQUARE IMAGE */
        if ($width !== $height) {
            $mproductError = "Image must be square (1:1 ratio).";
        }

        if ($width < 800 || $height < 800) {
            $mproductError = "Minimum image size is 800x800.";
        }

        if (empty($mproductError)) {

            $uploadDir = "uploads/company_products/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = uniqid('prod_', true) . ".webp";
            $filePath = $uploadDir . $fileName;

            /* Convert to WEBP */
            switch ($mime) {
                case 'image/jpeg':
                    $src = imagecreatefromjpeg($tmp);
                    break;
                case 'image/png':
                    $src = imagecreatefrompng($tmp);
                    break;
                case 'image/webp':
                    $src = imagecreatefromwebp($tmp);
                    break;
            }

            imagewebp($src, $filePath, 80);
            imagedestroy($src);

            $imagePath = $filePath;

            $imageSizeKB = round(filesize($filePath)/1024);
        }
    }

    /* =========================
       INSERT OR UPDATE
    ========================= */
    if (empty($mproductError)) {

        if ($mproductEditMode) {

            if ($imagePath) {
                $stmt = $conn->prepare("
                    UPDATE markethub_products 
                    SET product_name=?, price=?, currency=?, description=?, image=?, is_active=? 
                    WHERE id=?
                ");
                $stmt->bind_param("sdsssii", $mproductProductName, $mproductPrice, $mproductCurrency, $mproductProductDescription, $imagePath, $mproductIs_active, $mproductEditProductId);
            } else {
                $stmt = $conn->prepare("
                    UPDATE markethub_products 
                    SET product_name=?, price=?, currency=?, description=?, is_active=? 
                    WHERE id=?
                ");
                $stmt->bind_param("sdssii", $mproductProductName, $mproductPrice, $mproductCurrency, $mproductProductDescription, $mproductIs_active, $mproductEditProductId);
            }

            if ($stmt->execute()) {
                $mproductSuccess = "Product updated successfully!";
            } else {
                $mproductError = "Update failed.";
            }

        } else {

            $stmt = $conn->prepare("
                INSERT INTO markethub_products 
                (product_name, price, currency, description, image, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->bind_param("sdsssi", $mproductProductName, $mproductPrice, $mproductCurrency, $mproductProductDescription, $imagePath, $mproductIs_active);

            if ($stmt->execute()) {
                $mproductSuccess = "Product added successfully!";
                $mproductProductName = $mproductPrice = $mproductCurrency = $mproductProductDescription = '';
            } else {
                $mproductError = "Insert failed.";
            }
        }

        $stmt->close();
    }
}

/* Optional: regenerate session ID periodically *//* 
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
 */
/* ---------- ROLE ACCESS CONTROL ---------- *//* 
$allowedRole = 'administrator';

$roleStmt = $conn->prepare(
    "SELECT account_type FROM users WHERE user_id = ? LIMIT 1"
);
$roleStmt->bind_param("i", $_SESSION['user_id']);
$roleStmt->execute();
$roleStmt->bind_result($accountType);
$roleStmt->fetch();
$roleStmt->close();

if ($accountType !== $allowedRole) { */
    // Optional: destroy session for safety
    // session_destroy();/* 

    /* header("Location: index.php");
    exit();
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
          <img src="Images/Maket Hub Logo.avif" width="40" alt="Maket Hub Logo">
          <p>EMMANUEL&nbsp;WERANGAI <br><em>Administrator</em></p>
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
        <a href="agentPage.php" class="nav-link"><i class="fa-solid fa-users"></i>Sales Agents</a>
        <a href="sellerPage.php" class="nav-link"><i class="fa-solid fa-store"></i>Sellers</a>
        <a href="buyerPage.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i>Buyers</a>
        <a href="propertyOwnerPage.php" class="nav-link"><i class="fa-solid fa-building"></i>Property Owners</a>
        <a href="#" class="nav-link">
          <i class="fa-solid fa-money-bill-transfer"></i>Withdrawals
        </a>
        <a href="#" class="nav-link" data-tab="transactions">
          <i class="fa-solid fa-money-bill-transfer"></i>Transactions
        </a>
        <a href="settingsPage.php" class="nav-link"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="logout.php" class="nav-link"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
      </nav>

    </div>
    <main class="adminMain">
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
          <div class="form-wrapper" id="agent-edit-form">
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
          <div class="form-wrapper" id="seller-edit-form">
            <form method="POST" enctype="multipart/form-data">
              <h1>Update Seller Details</h1>
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
                  <label>Seller's Full Name</label>
                  <input type="text" name="full-name" placeholder="Full Name">
                </div>
                <div class="inp-box">
                  <label>Seller's Username</label>
                  <input type="text" name="username" placeholder="e.g blessedemmanuel254">
                </div>
                <div class="inp-box">
                  <label>Seller's Email ID</label>
                  <input type="text" name="email" placeholder="john@example.com">
                </div>
                <div class="inp-box">
                  <label>Sellers's Phone</label>
                  <input type="text" name="phone" placeholder="075***630">
                </div>
                <div class="inp-box">
                  <label>Business Name</label>
                  <input type="text" name="phone" placeholder="Main Cateen">
                </div>
                <div class="inp-box">

                  <label>Business Model</label>
                  <select name="busmodel">
                    <option value=""><p>-- Select Business Model --</p></option>
                    <option value="products" <?php echo ($busmodel === 'products') ? 'selected' : ''; ?>>Products</option>
                    <option value="services" <?php echo ($busmodel === 'services') ? 'selected' : ''; ?>>Services</option>
                    <option value="rentals" <?php echo ($busmodel === 'rentals') ? 'selected' : ''; ?>>Rentals</option>
                  </select>
                </div>
                <div class="inp-box">

                  <label>Business type</label>
                  <select name="bustype">
                    <option value=""><p>-- Select Type --</p></option>
                    <option value="shop" <?php echo ($bustype === 'shop') ? 'selected' : ''; ?>>Shop</option>
                    <option value="supermarket" <?php echo ($bustype === 'supermarket') ? 'selected' : ''; ?>>Supermarket</option>
                    <option value="kiosk" <?php echo ($bustype === 'kiosk') ? 'selected' : ''; ?>>Kiosk</option>
                    <option value="kibanda" <?php echo ($bustype === 'kibanda') ? 'selected' : ''; ?>>Kibanda</option>
                    <option value="canteen" <?php echo ($bustype === 'canteen') ? 'selected' : ''; ?>>Canteen</option>
                    <option value="service_provider" <?php echo ($bustype === 'service_provider') ? 'selected' : ''; ?>>Service_provider</option>
                    <option value="rentals" <?php echo ($bustype === 'rentals') ? 'selected' : ''; ?>>Rentals</option>
                  </select>
                </div>
                <div class="inp-box">
                  <label>Seller's Address</label>
                  <input type="text" name="address" placeholder="eg. Kilifi town">
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

                  <label>Market Type</label>
                  <select name="market">
                    <option value=""><p>-- Select Market Type --</p></option>
                    <option value="local" <?php echo ($market === 'local') ? 'selected' : ''; ?>>Local</option>
                    <option value="national" <?php echo ($market === 'national') ? 'selected' : ''; ?>>National</option>
                    <option value="global" <?php echo ($market === 'global') ? 'selected' : ''; ?>>Global</option>
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

                  <label>Ward</label>
                  <select name="ward">
                    <option value=""><p>-- Select Ward --</p></option>
                    <option value="Sokoni Ward" <?php echo ($ward === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                    <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div></div>
                <div></div><!-- 
                <div class="inp-box">
                  <label>Agency Code (read-only)</label>
                  <input type="text" name="agency_code" placeholder="A56D3847" disabled>
                </div> -->
                <div></div>
                <button type="submit">
                  Submit Details
                </button>
              </div>

            </form>
          </div>
          <div class="form-wrapper" id="buyer-edit-form">
            <form method="POST" enctype="multipart/form-data">
              <h1>Update Buyer Details</h1>
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
                  <label>Buyer's Full Name</label>
                  <input type="text" name="full-name" placeholder="Full Name">
                </div>
                <div class="inp-box">
                  <label>Buyer's Username</label>
                  <input type="text" name="username" placeholder="e.g blessedemmanuel254">
                </div>
                <div class="inp-box">
                  <label>Buyer's Email ID</label>
                  <input type="text" name="email" placeholder="john@example.com">
                </div>
                <div class="inp-box">
                  <label>Buyer's Phone</label>
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
                  <label>Buyer's Address</label>
                  <input type="text" name="address" placeholder="eg. Kilifi town">
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

                  <label>Ward</label>
                  <select name="ward">
                    <option value=""><p>-- Select Ward --</p></option>
                    <option value="Sokoni Ward" <?php echo ($ward === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option><!-- 
                    <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                  </select>
                </div>
                <div></div>
                <button type="submit">
                  Submit Details
                </button>
              </div>

            </form>
          </div>
          <div class="form-wrapper" id="product-edit-form">
            <form method="POST" enctype="multipart/form-data">
              <h1>Update Owner Details</h1>
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
                  <label>Owner's Full Name</label>
                  <input type="text" name="full-name" placeholder="Full Name">
                </div>
                <div class="inp-box">
                  <label>Owner's Username</label>
                  <input type="text" name="username" placeholder="e.g blessedemmanuel254">
                </div>
                <div class="inp-box">
                  <label>Owner's Email ID</label>
                  <input type="text" name="email" placeholder="john@example.com">
                </div>
                <div class="inp-box">
                  <label>Owner's Phone</label>
                  <input type="text" name="phone" placeholder="075***630">
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
                  <label>Owner's Address</label>
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
                <button type="submit">
                  Submit Details
                </button>
              </div>

            </form>
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
      <p>&copy; 2025/2026, Maket Hub.shop, All Rights reserved.</p>
    </footer>
  </div>
  <script src="assets/js/general.js" type="text/javascript" defer></script>
  <script>
    // DataTables Script Js
    $(document).ready(function () {
      $('#transactionsTable').DataTable({
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
</body>
</html>
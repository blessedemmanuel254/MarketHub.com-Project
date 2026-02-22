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

  <title>Agent Page | Market Hub</title>
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
      <div class="agentHeader">
        <h1>Agent Dashboard</h1>
        <p class="status">Status:&nbsp;<span class="verified">Verified&nbsp;<i class="fa-solid fa-certificate"></i></span></p><!-- 
        <p class="status">Status:&nbsp;<span class="unverified">Unverified&nbsp;<i class="fa-solid fa-certificate"></i></span></p> -->

      </div>
      <div class="tabs-container" id="toggleMarketTypeTab">
        <div class="tabs">
          <button class="tab-btn active" data-tab="dashboard">Sales&nbsp;Board</button>
          <button class="tab-btn" data-tab="products">My&nbsp;Agency</button>
          <button class="tab-btn" data-tab="funds" onclick="togglePaymentOption()">Funds</button>
        </div>

        <div class="tab-content">
          <div id="dashboard" class="tab-panel active">
            <p>Sales Scope <br><strong>Your work progress and finances <i class="fa-regular fa-circle-check"></i></strong></p>
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

              <!-- PRODUCT CARD -->
              <div class="card">
                <img src="Images/Passion Juice.jpg" alt="Product">
                <div class="card-body">
                  <div class="product-name">Passion Juice</div>
                  <div class="product-meta">Food & Snacks</div>
                  <div class="price">KES 40</div>
                  <div class="stock in-stock">In stock (24)</div>
                </div>
                <div class="card-actions">
                  <button class="edit"><i class="fa fa-pen"></i> Edit</button>
                  <button class="delete"><i class="fa fa-trash"></i> Delete</button>
                </div>
              </div>

              <!-- PRODUCT CARD -->
              <div class="card">
                <img src="Images/Market Hub Logo.avif" alt="Product">
                <div class="card-body">
                  <div class="product-name">Smart Watch</div>
                  <div class="product-meta">Gadgets</div>
                  <div class="price">KES 6,800</div>
                  <div class="stock low-stock">Low stock (3)</div>
                </div>
                <div class="card-actions">
                  <button class="edit"><i class="fa fa-pen"></i> Edit</button>
                  <button class="delete"><i class="fa fa-trash"></i> Delete</button>
                </div>
              </div>
              <!-- PRODUCT CARD -->
              <div class="card">
                <img src="Images/Market Hub Logo.avif" alt="Product">
                <div class="card-body">
                  <div class="product-name">Smart Watch</div>
                  <div class="product-meta">Gadgets</div>
                  <div class="price">KES 6,800</div>
                  <div class="stock low-stock">Low stock (3)</div>
                </div>
                <div class="card-actions">
                  <button class="edit"><i class="fa fa-pen"></i> Edit</button>
                  <button class="delete"><i class="fa fa-trash"></i> Delete</button>
                </div>
              </div>
              <!-- PRODUCT CARD -->
              <div class="card">
                <img src="Images/Market Hub Logo.avif" alt="Product">
                <div class="card-body">
                  <div class="product-name">Smart Watch</div>
                  <div class="product-meta">Gadgets</div>
                  <div class="price">KES 6,800</div>
                  <div class="stock low-stock">Low stock (3)</div>
                </div>
                <div class="card-actions">
                  <button class="edit"><i class="fa fa-pen"></i> Edit</button>
                  <button class="delete"><i class="fa fa-trash"></i> Delete</button>
                </div>
              </div>
              <!-- PRODUCT CARD -->
              <div class="card">
                <img src="Images/Market Hub Logo.avif" alt="Product">
                <div class="card-body">
                  <div class="product-name">Smart Watch</div>
                  <div class="product-meta">Gadgets</div>
                  <div class="price">KES 6,800</div>
                  <div class="stock low-stock">Low stock (3)</div>
                </div>
                <div class="card-actions">
                  <button class="edit"><i class="fa fa-pen"></i> Edit</button>
                  <button class="delete"><i class="fa fa-trash"></i> Delete</button>
                </div>
              </div>

              <!-- PRODUCT CARD -->
              <div class="card">
                <img src="Images/Market Hub Logo.avif" alt="Product">
                <div class="card-body">
                  <div class="product-name">Office Chair</div>
                  <div class="product-meta">Furniture</div>
                  <div class="price">KES 9,000</div>
                  <div class="stock out-stock">Out of stock</div>
                </div>
                <div class="card-actions">
                  <button class="edit"><i class="fa fa-pen"></i> Edit</button>
                  <button class="delete"><i class="fa fa-trash"></i> Delete</button>
                </div>
              </div>
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
                <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i>Image too large!</p>
                <p class="successMessage"><i class="fa-solid fa-check-circle"></i>Product added successfully!</p>
                <div class="formBody">
                  <div class="inp-box">
                    <label>Product Name</label>
                    <input type="text" name="name" placeholder="Passion Juice">
                  </div>
                  <div class="inp-box">

                    <label>Category</label>
                    <select name="category">
                      <option value="" class="span"><span>--Select category--</span></option>
                      <option>Food & Snacks</option>
                      <option>Drinks</option>
                      <option>Electronics</option>
                    </select>
                  </div>
                  <div class="inp-box">
                    <label>Price (KES)</label>
                    <input type="number" name="price" step="0.01" placeholder="40">
                  </div>
                  <div class="inp-box">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock" placeholder="24">
                  </div>
                  <div class="inp-box">
                    <label>Product Image</label>
                    <input type="file" accept="image/png,image/jpeg,image/webp" name="photo" accept="image/*">
                    <div class="note">
                      400×400 – 1200×1200 px • Max 1MB
                    </div>
                  </div>
                  <div></div>
                  <button type="submit">
                    <i class="fa fa-plus"></i> Add Product
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
                <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i>Insufficient funds in your wallet!</p>
                <p class="successMessage"><i class="fa-solid fa-check-circle"></i>Withdrawal request submitted successfully!</p>
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

      <h1>Recent Earnings Activity</h1>

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
              <th>Date</th>
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
              <td>12 Feb 2026</td>
              <td>ORD-10021</td>
              <td>John Doe</td>
              <td>2</td>
              <td>KES 7,000</td>
              <td><span class="badge paid">Paid</span></td>
              <td><span class="badge delivered">Delivered</span></td>
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
  
  <script src="Scripts/general.js" type="text/javascript"></script>
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
</body>
</html>
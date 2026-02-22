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

  <title>Buyer Page | Market Hub</title>
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
          <a class="lkOdr" onclick="toggleOrderMain()">
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

    <main class="buyerMain" id="marketMain">
      <div class="tabs-container" id="toggleMarketTypeTab">
        <div class="tabs">
          <button class="tab-btn active" data-tab="products">Products</button>
          <button class="tab-btn" data-tab="services">Services</button>
          <button class="tab-btn" data-tab="rentals">Rentals</button>
        </div>

        <div class="tab-content">
          <div id="products" class="tab-panel active">
            <div class="tab-top">
              <p>Add products to your catalog</em> <br><strong>Show customers what you offer <i class="fa-regular fa-circle-check"></i></strong></p>
              <button>
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
      <div class="tabs-container" id="toggleMarketSourceTab">
        <div class="tabs">
          <button class="tab-btn-msource active" data-tab="shops">Shops</button>
          <button class="tab-btn-msource" data-tab="supermarkets">Supermarkets</button><!-- 
          <button class="tab-btn-msource" data-tab="rentals">Rentals</button> -->
        </div>

        <div class="tab-content">
          <div id="shops" class="tab-panel-msource active">
            <div class="tab-top">
              <p>Showing markets in <em>Sokoni Ward</em> <br><strong>Please select the market source <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToMarketTypes()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>
            </div>

            <!-- SELLERS LIST -->
            <div class="sellers">

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">MC</div>
                  <div>
                    <div class="name">Main Canteen</div>
                    <div class="rating">★★★★★ (41)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>23k&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo">Delivery: Pickup · Courier</div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoGold">200+</div>
                  <div class="bsType">Business Type : <i>Kiosk</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">BE</div>
                  <div>
                    <div class="name">BerryFerry</div>
                    <div class="rating">★★★★★ (165)</div>
                    <div class="meta"><h2>3&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>4&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo">Delivery: Pickup · Courier</div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeDefault">13</div>
                  <div class="bsType">Business Type : <i>Canteen</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">WW</div>
                  <div>
                    <div class="name">Wwrightbright</div>
                    <div class="rating">★★★★★ (11)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>2&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo">Delivery: Pickup · Courier</div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoPro">100+</div>
                  <div class="bsType">Business Type : <i>Kibanda</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

            </div>
          </div>

          <div id="supermarkets" class="tab-panel-msource">
            <div class="tab-top">
              <p>Showing markets in <em>Sokoni Ward</em> <br><strong>Please select the market source <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="goBackToMarketTypes()">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>
            </div>

            <!-- SELLERS LIST -->
            <div class="sellers">

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">NS</div>
                  <div>
                    <div class="name">Naivas Supermaket</div>
                    <div class="rating">★★★★★ (41)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>23k&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo">Delivery: Pickup · Courier</div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoGold">1000+</div>
                  <div class="bsType">Business Type : <i>Kiosk</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">CM</div>
                  <div>
                    <div class="name">Cherowamaye Minimarket</div>
                    <div class="rating">★★★★★ (165)</div>
                    <div class="meta"><h2>3&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>4&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo">Delivery: Pickup · Courier</div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeDefault">287</div>
                  <div class="bsType">Business Type : <i>Canteen</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

              <div class="seller">
                <div class="seller-left">
                  <div class="avatar">AW</div>
                  <div>
                    <div class="name">Abul Wholesale</div>
                    <div class="rating">★★★★★ (11)</div>
                    <div class="meta"><h2>2&nbsp;<span>following</span></h2> <h2 class="followBtn">Follow</h2></div>
                    <div class="meta"><h2>2&nbsp;<span>followers</span></h2></div>
                    <div class="bsInfo">Delivery: Pickup · Courier</div>
                    <div class="bsInfo"><strong>Location :</strong> Pwani University Area</div>
                  </div>
                </div>
                <a href="marketDisplay.php" class="seller-right">
                  <div class="promoBadgeGoPro">500+</div>
                  <div class="bsType">Business Type : <i>Kibanda</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

            </div>
          </div>
        </div>
      </div>

      <h1>Recent Orders</h1>

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
        <th>Image</th><th>Order</th><th>Product</th><th>Seller</th>
        <th>Market</th><th>Qty</th><th>Price</th>
        <th>Payment</th><th>Status</th><th>Actions</th>
      </tr>
      </thead>
      <tbody>

      <tr data-status="Delivered">
        <td><img src="Images/Market Hub Logo.avif" class="product-img"></td>
        <td>MH-10231</td>
        <td>Wireless Headphones</td>
        <td>SoundTech</td>
        <td>National</td>
        <td>1</td>
        <td>KES&nbsp;3,500</td>
        <td><span class="badge paid">Paid</span></td>
        <td><span class="badge delivered">Delivered</span></td>
        <td class="actions">
          <div>
            <button class="btn-view">View</button>
            <button class="btn-track">Track</button>
          </div>
        </td>
      </tr>

      <tr data-status="Processing">
        <td><img src="Images/Market Hub Logo.avif" class="product-img"></td>
        <td>MH-10702</td>
        <td>Smart Watch</td>
        <td>Global Gadgets</td>
        <td>Global</td>
        <td>1</td>
        <td>KES&nbsp;6,800</td>
        <td><span class="badge pending">Pending</span></td>
        <td><span class="badge processing">Processing</span></td>
        <td class="actions">
          <div>
            <button class="btn-view">View</button>
            <button class="btn-cancel">Cancel</button>
          </div>
        </td>
      </tr>

      <tr data-status="Processing">
        <td><img src="Images/Market Hub Logo.avif" class="product-img"></td>
        <td>MH-10702</td>
        <td>Smart Watch</td>
        <td>Global Gadgets</td>
        <td>Global</td>
        <td>1</td>
        <td>KES&nbsp;6,800</td>
        <td><span class="badge pending">Pending</span></td>
        <td><span class="badge processing">Processing</span></td>
        <td class="actions">
          <div>
            <button class="btn-view">View</button>
            <button class="btn-cancel">Cancel</button>
          </div>
        </td>
      </tr>

      <tr data-status="Processing">
        <td><img src="Images/Market Hub Logo.avif" class="product-img"></td>
        <td>MH-10702</td>
        <td>Smart Watch</td>
        <td>Global Gadgets</td>
        <td>Global</td>
        <td>1</td>
        <td>KES&nbsp;6,800</td>
        <td><span class="badge pending">Pending</span></td>
        <td><span class="badge processing">Processing</span></td>
        <td class="actions">
          <div>
            <button class="btn-view">View</button>
            <button class="btn-cancel">Cancel</button>
          </div>
        </td>
      </tr>

      <tr data-status="Processing">
        <td><img src="Images/Market Hub Logo.avif" class="product-img"></td>
        <td>MH-10702</td>
        <td>Smart Watch</td>
        <td>Global Gadgets</td>
        <td>Global</td>
        <td>1</td>
        <td>KES&nbsp;6,800</td>
        <td><span class="badge pending">Pending</span></td>
        <td><span class="badge processing">Processing</span></td>
        <td class="actions">
          <div>
            <button class="btn-view">View</button>
            <button class="btn-cancel">Cancel</button>
          </div>
        </td>
      </tr>

      <tr data-status="Processing">
        <td><img src="Images/Market Hub Logo.avif" class="product-img"></td>
        <td>MH-10702</td>
        <td>Smart Watch</td>
        <td>Global Gadgets</td>
        <td>Global</td>
        <td>1</td>
        <td>KES&nbsp;6,800</td>
        <td><span class="badge pending">Pending</span></td>
        <td><span class="badge processing">Processing</span></td>
        <td class="actions">
          <div>
            <button class="btn-view">View</button>
            <button class="btn-cancel">Cancel</button>
          </div>
        </td>
      </tr>

      <tr data-status="Processing">
        <td><img src="Images/Market Hub Logo.avif" class="product-img"></td>
        <td>MH-10702</td>
        <td>Smart Watch</td>
        <td>Global Gadgets</td>
        <td>Global</td>
        <td>1</td>
        <td>KES&nbsp;6,800</td>
        <td><span class="badge pending">Pending</span></td>
        <td><span class="badge processing">Processing</span></td>
        <td class="actions">
          <div>
            <button class="btn-view">View</button>
            <button class="btn-cancel">Cancel</button>
          </div>
        </td>
      </tr>

      </tbody>
      </table>
      </div>

      <!-- MOBILE CARDS -->
      <div class="cards" id="orderCards">

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
          <div></div>
          <button class="btn-view">View</button>
          <button class="btn-track">Track</button>
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
          <button class="btn-view">View</button>
          <button class="btn-cancel">Cancel</button>
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
          <button class="btn-view">View</button>
          <button class="btn-cancel">Cancel</button>
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
          <div></div>
          <button class="btn-view">View</button>
          <button class="btn-track">Track</button>
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
          <div></div>
          <button class="btn-view">View</button>
          <button class="btn-track">Track</button>
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
          <button class="btn-view">View</button>
          <button class="btn-cancel">Cancel</button>
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
          <button class="btn-view">View</button>
          <button class="btn-cancel">Cancel</button>
        </div>
      </div>

      </div>
      
      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleOrderMain()">View&nbsp;All&nbsp;Orders</button> to access all your orders.</p>

    </main>

    <main class="buyerMain" id="orderMain">

      <div class="order-group">
        <div class="order-header">
          <div>
            <strong>Order #ORD-90321</strong><br>
            <span>Placed on 12 Feb 2026</span>
          </div>
          <div>3 Items</div>
        </div>

        <div class="order-items-grid">

          <!-- ITEM 1 -->
          <div class="order-item">
            <div class="item-top">
              <div class="item-info">
                <h4>Wireless Headphones</h4>
                <p>Seller: TechZone</p>
                <p>Qty: 1 • Total: KES 3,200</p>
                <p>Status: <span class="status shipped">Shipped</span></p>
                <span class="market-badge">National</span>
              </div>
              <img src="Images/Market Hub Logo.avif" alt="Product">
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

          <!-- ITEM 2 -->
          <div class="order-item">
            <div class="item-top">
              <div class="item-info">
                <h4>Office Chair</h4>
                <p>Seller: Comfort Furnish</p>
                <p>Qty: 2 • Total: KES 18,000</p>
                <p>Status: <span class="status processing">Processing</span></p>
                <span class="market-badge">Local</span>
              </div>
              <img src="Images/Market Hub Logo.avif" alt="Product">
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
      </div>

      <p class="toggleOrdersOrMarket"><button href="" onclick="toggleMarketMain()">Go&nbsp;back</button> to continue shopping.</p>
    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript"></script>

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
</body>
</html>
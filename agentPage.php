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

$country = "";
$county = "";
$ward = "";

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
            

            <div class="cards">
              <!-- AGENT -->
              <a class="card" onclick="openMarketSource('shops')">
                <i class="fa-brands fa-product-hunt"></i>
                <h2>Products</h2>
                <p>
                  View your products to market.
                </p>
                <div class="label">
                  <p>AGENT</p>
                  <button>View Products</button>

                </div>
              </a>

              <!-- MARKET -->
              <a class="card">
                <i class="fa-brands fa-python"></i>
                <h2>Withrawal</h2>
                <p>
                  View your account withdrawal history.
                </p>
                <div class="label">
                  <p>HISTORY</p>
                  <button>View History</button>

                </div>
              </a>

              <!-- GLOBAL -->
              <a class="card">
                <i class="fa-brands fa-renren"></i>
                <h2>Market</h2>
                <p>
                  Also order on Market Hub like other buyers.
                </p>
                <div class="label">
                  <p>Market</p>
                  <button>View Market</button>

                </div>
              </a>
            </div>
          </div>

          <div id="products" class="tab-panel agency">
            <div class="tab-top">
              <p>Peformance Analytics<br><strong>Monitor your agency and track performance <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleProductsAdd(true)">
                <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Agent</span>
              </button>

            </div>
            <div class="dashboard">

              <!-- TOP CARDS -->
              <div class="grid">

                <!-- AGENCY WALLET -->
                <div class="card">
                  <h3>Agency Balance</h3>
                  <div class="amount">KES 12,540</div>
                  <div class="sub-info">Last withdrawal: 3 days ago</div>
                  <div class="growth up">▲ +18% from last month</div>
                  <div class="progress">
                    <div class="progress-fill"></div>
                  </div>
                  <div class="sub-info">KES 2,460 to next payout milestone</div>
                </div>

                <!-- WITHDRAW HISTORY -->
                <div class="card">
                  <h3>Total Withdrawn</h3>
                  <div class="amount">KES 71,140</div>
                  <div class="sub-info">12 successful withdrawalss</div>
                  <div class="growth up">▲ +15% from last month</div>
                  <div class="sub-info">Endless Payouts in line</div>
                </div>

                <!-- NETWORK SIZE -->
                <div class="card">
                  <h3>Your Network Size</h3>
                  <div class="amount">46 Agents</div>
                  <div class="sub-info">Level 1: 8</div>
                  <div class="sub-info">Level 2: 14</div>
                  <div class="sub-info">Level 3: 24</div>
                  <div class="growth up">▲ +6 new this month</div>
                </div>

                <!-- ADVERTISING -->
                <div class="card">
                  <h3>Withdrawal Status</h3>
                  <span class="wStatus">Eligible</span>
                  <div class="sub-info-m">Minimum threshold met</div>
                  <button>Withdraw</button>
                  <div class="growth up">▲ +12% increase</div>
                </div>

                <!-- ADVERTISING --><!-- 
                <div class="card">
                  <h3>Product Advertising Earnings</h3>
                  <div class="amount">KES 5,400</div>
                  <div class="sub-info">32 conversions this month</div>
                  <div class="growth up">▲ +12% increase</div>
                  <div class="sub-info">Conversion Rate: 4.8%</div>
                </div> -->

              </div>

              <!-- AFFILIATE BREAKDOWN -->
              <div class="grid" style="margin-top:20px;">

                <div class="card">
                  <h3>Affiliate Earnings Breakdown</h3>

                  <div class="level-row">
                    <span>Level 1 (100 KES)</span>
                    <strong>KES 3,200</strong>
                  </div>

                  <div class="level-row">
                    <span>Level 2 (40 KES)</span>
                    <strong>KES 2,240</strong>
                  </div>

                  <div class="level-row">
                    <span>Level 3 (20 KES)</span>
                    <strong>KES 1,700</strong>
                  </div>

                  <div class="sub-info">Highest earning level: Level 1</div>
                </div>

                <div class="card">
                  <h3>Referral Performance</h3>
                  <div class="sub-info">Clicks this month: 1,240</div>
                  <div class="sub-info">Agent Signups: 24</div>
                  <div class="sub-info">Activation Rate: 62%</div>
                  <div class="growth up">▲ +8% better than last month</div>
                </div>

              </div>

            </div>
          </div>
          
          <div id="add-products" class="tab-panel">
            <div class="tab-top">
              <p>Add to your Agency</em> <br><strong>Submit new agent's details to be added <i class="fa-regular fa-circle-check"></i></strong></p>
              <button onclick="toggleProductsAdd(false)">
                <i class="fa-solid fa-circle-arrow-left"></i>&nbsp;<span>Go&nbsp;Back</span>
              </button>

            </div>
            <div class="form-wrapper">
              <form method="POST" enctype="multipart/form-data">
                <h1>Add New Agent Details</h1>
                <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i>Invalid credentials!</p>
                <p class="successMessage"><i class="fa-solid fa-check-circle"></i>Agent added successfully!</p>
                <div class="formBody">
                  <div class="inp-box">
                    <label>Agent's Full Name</label>
                    <input type="text" name="agent-full-name" placeholder="Full Name">
                  </div>
                  <div class="inp-box">
                    <label>Agent's Username</label>
                    <input type="text" name="username" placeholder="e.g blessedemmanuel254">
                  </div>
                  <div class="inp-box">
                    <label>Agent's Email ID</label>
                    <input type="text" name="agent-email" placeholder="john@example.com">
                  </div>
                  <div class="inp-box">
                    <label>Agent's Phone</label>
                    <input type="agent-phone" name="agent-email" placeholder="075***630">
                  </div>
                  <div class="inp-box">

                    <label>Country</label>
                    <select name="category">
                      <option value=""><p>-- Select Country --</p></option>
                      <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                      <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                      <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                    </select>
                  </div>
                  <div class="inp-box">
                    <label>Agent's Address</label>
                    <input type="text" name="agent-address" placeholder="eg. Kilifi town">
                  </div>
                  <div class="inp-box">

                    <label>County</label>
                    <select name="category">
                      <option value=""><p>-- Select County --</p></option>
                      <option value="Kenya" <?php echo ($county === 'Kenya') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                      <option value="Kenya" <?php echo ($county === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                      <option value="Kenya" <?php echo ($county === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                    </select>
                  </div>
                  <div class="inp-box">

                    <label>Ward</label>
                    <select name="category">
                      <option value=""><p>-- Select Ward --</p></option>
                      <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kilifi</option><!-- 
                      <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                      <option value="Kenya" <?php echo ($ward === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                    </select>
                  </div>
                  <button type="submit">
                    Submit Details
                  </button>
                </div>

              </form>
            </div>
          </div>
          
          <div id="funds" class="tab-panel">
            <p>Access your earnings</em> <br><strong>Withdraw funds you’ve earned from completed sales <i class="fa-regular fa-circle-check"></i></strong></p>
            
            <div class="form-wrapper agency">
              <form method="POST" enctype="multipart/form-data">
                <h1>Withdraw Funds</h1>
                <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i>Insufficient funds in your wallet!</p>
                <p class="successMessage"><i class="fa-solid fa-check-circle"></i>Withdrawal request submitted successfully!</p>
                <select name="" id="" class="walletChange">
                  <option value="Sales Wallet">Sales Wallet</option>
                  <option value="Agency Wallet">Agency Wallet</option>
                </select>
                <div class="formBody agency" id="salesWallet">
                  <!-- ADVERTISING -->
                  <div class="card">
                    <h3>Sales Wallet Balance</h3>
                    <div class="amount">KES 5,400</div>
                    <div class="sub-info">32 conversions this month</div>
                    <div class="growth up">▲ +12% increase</div>
                    <div class="sub-info">Conversion Rate: 4.8%</div>
                  </div>
                  <div>
                    <div class="inp-box">
                      <label>Withdraw from Sales</label>
                      <input type="number" placeholder="Enter amount">
                      <button type="button">Request Withdrawal</button>
                    </div>
                  </div>
                </div>
                <div class="formBody agency" id="agencyWallet">
                  <!-- AGENCY WALLET -->
                  <div class="card">
                    <h3>Agency Wallet Balance</h3>
                    <div class="amount">KES 12,540</div>
                    <div class="sub-info">Last withdrawal: 3 days ago</div>
                    <div class="growth up">▲ +18% from last month</div>
                    <div class="progress">
                      <div class="progress-fill"></div>
                    </div>
                    <div class="sub-info">KES 2,460 to next payout milestone</div>
                  </div>

                  <div>
                    <div class="inp-box">
                      <label>Withdraw from Agency</label>
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
              <th>Source</th>
              <th>Status</th>
              <th>Level</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr data-status="Delivered">
              <td>12 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge pending">Pending</span></td>
              <td>Level&nbsp;1</td>
              <td>KES&nbsp;100</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td><span class="badge processing">Processing</span></td>
              <td>Product</td>
              <td>KES&nbsp;7700</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;2</td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Processing">
              <td>14 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Product</td>
              <td>KES&nbsp;800</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;3</td>
              <td>KES&nbsp;20</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;2</td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td><span class="badge paid">Paid</span></td>
              <td>Level&nbsp;3</td>
              <td>KES&nbsp;20</td>
            </tr>
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
      
      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleSellerOrdersTrack()">View&nbsp;Activity</button> to access all your recent earnings.</p>

    </main>

    <main class="buyerMain" id="ordersTrackMain">
      <div class="tab-top">
        <p>Recent Earnings History<br><strong>View and track your recent flow of income <i class="fa-regular fa-circle-check"></i></strong></p>
        <button onclick="toggleSellerOrdersTrack()">
          <i class="fa-solid fa-circle-arrow-left" data-tab="products"></i> <span>Go&nbsp;Back</span>
        </button>
      </div>
      <div class="table-wrapper sellerOrdersTrack">
        <table id="agentEarnings">
          <thead>
            <tr>
              <th>Date</th>
              <th>Source</th>
              <th>Level</th>
              <th>Name</th>
              <th>Status</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr data-status="Delivered">
              <td>12 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;1</td>
              <td>Sony_254</td>
              <td><span class="badge pending">Pending</span></td>
              <td>KES&nbsp;100</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td>Product</td>
              <td>Passion Juice</td>
              <td><span class="badge processing">Processing</span></td>
              <td>KES&nbsp;7700</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;2</td>
              <td>Levi254</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Processing">
              <td>14 Feb 2026</td>
              <td>Product&nbsp;Sales</td>
              <td>Product</td>
              <td>Oraimo Headphones</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;800</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;3</td>
              <td>Agentrael</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;20</td>
            </tr>

            <tr data-status="Processing">
              <td>13 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;2</td>
              <td>Kalvani</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;40</td>
            </tr>

            <tr data-status="Shipped">
              <td>14 Feb 2026</td>
              <td>Agent&nbsp;Activation</td>
              <td>Level&nbsp;3</td>
              <td>Blessedemmanuel254</td>
              <td><span class="badge paid">Paid</span></td>
              <td>KES&nbsp;20</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="toggleOrdersOrMarket">Click <button href="" onclick="toggleSellerOrdersTrack()">Go&nbsp;back</button> to continue with sales.</p>
    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript"></script>
  <script>
    // DataTables Script Js
    $(document).ready(function () {
      $('#agentEarnings').DataTable({
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
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
$account_type = $_SESSION['account_type'];

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
      <div class="tabs-container">
        <div class="tabs">
          <button class="tab-btn active" data-tab="shops">Shops</button>
          <button class="tab-btn" data-tab="supermarkets">Supermarkets</button><!-- 
          <button class="tab-btn" data-tab="rentals">Rentals</button> -->
        </div>

        <div class="tab-content">
          <div id="shops" class="tab-panel active">
            <p>Quality goods from trusted vendors. <br><strong>Please select the market source <i class="fa-regular fa-circle-check"></i></strong></p>

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
                <a href="" class="seller-right">
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
                <a href="" class="seller-right">
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
                <a href="" class="seller-right">
                  <div class="promoBadgeGoPro">100+</div>
                  <div class="bsType">Business Type : <i>Kibanda</i></div>
                  <div class="action">
                    <button>View&nbsp;seller</button>
                  </div>
                </a>
              </div>

            </div>
          </div>

          <div id="supermarkets" class="tab-panel">
            <p>Quality goods from trusted vendors. <br><strong>Please select the market source <i class="fa-regular fa-circle-check"></i></strong></p>

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
                <a href="" class="seller-right">
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
                <a href="" class="seller-right">
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
                <a href="" class="seller-right">
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
    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript" defer></script>

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
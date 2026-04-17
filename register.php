<?php
session_start();
include 'connection.php';

// Prevent direct access without selection
if (!isset($_SESSION['accountType'])) {
  header('Location: accountTypeSelection.php');
  exit();
}

$country = "";
$accountType = $_SESSION['accountType'];
$error = "";
$success = "";
$full_name = "";
$username = "";
$email = "";
$phone = "";
$location_id = "";
$address = "";
$busname = "";
$busmodel = "";
$bustype = "";
$market = "";

function validatePassword($password) {
  // Check all rules, but return only a simple generic message if any fail
  if (strlen($password) < 8 || 
    !preg_match('/[A-Z]/', $password) || 
    !preg_match('/[a-z]/', $password) || 
    !preg_match('/\d/', $password) || 
    !preg_match('/[^A-Za-z0-9]/', $password)) {
    return "Password does not meet requirements.";
  }
  return ""; // valid
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

function normalizeBusinessName($name) {

  // Remove leading and trailing spaces
  $name = trim($name);

  // Convert multiple spaces to a single space
  $name = preg_replace('/\s+/', ' ', $name);

  // Convert to lowercase for comparison
  $name = strtolower($name);

  return $name;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $full_name = trim($_POST['full_name'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $location_id = intval($_POST['ward'] ?? 0); // ward is the final level
  $address = trim($_POST['address'] ?? '');
  $busname = trim($_POST['busname'] ?? '');
  $normalizedBusname = normalizeBusinessName($busname);
  $busmodel = trim($_POST['busmodel'] ?? '');
  $bustype = trim($_POST['bustype'] ?? '');
  $market = trim($_POST['market'] ?? '');

  if (!$full_name || !$username || !$email || !$phone || !$password || !$confirm_password || !$location_id || !$address || ($accountType === 'seller' && (!$busname || !$busmodel || !$bustype || !$market))) {
    $error = "All fields are required!";
  } else if (!$accountType) {
    $error = 'Visit the <a href="accountTypeSelection.php">account-type selection</a> page to proceed!';
  } elseif (str_word_count($full_name) < 2) {
    $error = "Full name must include at least first and last name!";
  } elseif (strpos($username, ' ') !== false) {
    $error = 'Username should not have space(s)!';
  } elseif (strlen($username) < 5) {
    $error = 'Username is too short!';
  } elseif (strlen($username) > 20) {
    $error = 'Username is too long!';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email address!';
  } elseif (!preg_match('/^[0-9+\-\(\)\s]+$/', $phone)) {
    $error = "Phone number contains invalid characters!";
  } elseif ($accountType === 'seller' && strlen($busname) > 25) {
    $error = "Business name too long!";
  } elseif (strlen($address) > 25) {
    $error = "Address too long!";
  } else {
    $encrypted_email = base64_encode(strtolower($email));
    $normalized_phone = normalizePhoneNumber($phone);

    $checkUserQuery = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($checkUserQuery);
    $stmt->bind_param("ss", $encrypted_email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = "Username or email already exists!";
    } else {
      $encrypted_phone = base64_encode($normalized_phone);
      $stmt = $conn->prepare("SELECT user_id FROM users WHERE phone = ?");
      $stmt->bind_param("s", $encrypted_phone);
      $stmt->execute();
      $stmt->store_result();

      if (!$normalized_phone || !preg_match('/^(\+254\d{9}|0\d{9})$/', $normalized_phone)) {
        $error = "Please enter a valid phone number!";
      } elseif ($stmt->num_rows > 0) {
        $error = "Phone number already exists!";
        
      } 

      /* -----------------------------
      CHECK BUSINESS NAME (CASE INSENSITIVE)
      ----------------------------- */

      elseif ($accountType === 'seller') {

        $stmt = $conn->prepare("
            SELECT user_id 
            FROM users 
            WHERE LOWER(business_name) = LOWER(?) 
            LIMIT 1
        ");

        $stmt->bind_param("s", $normalizedBusname);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
          $error = "Business name already exists!";
        }

        $stmt->close();
      }
      
      if ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
      } else {
        // Validate password strength
        $passwordError = validatePassword($password);
      if ($passwordError) {
        $error = $passwordError; // simple single-line error
      } else {

        if (!$error) {
          $busname = ucwords($normalizedBusname);
          $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
          // Force NULL for buyer accounts
          if ($accountType === 'buyer') {
              $busname = null;
              $busmodel = null;
              $bustype = null;
              $market  = null;
          }

          // Insert into database
          $stmt = $conn->prepare("
            INSERT INTO users 
            (account_type, full_name, username, email, phone, password, location_id, address, business_name, business_model, business_type, market_scope, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
          ");

          $stmt->bind_param(
            "ssssssisssss",
            $accountType,
            $full_name,
            $username,
            $encrypted_email,
            $encrypted_phone,
            $hashedPassword,
            $location_id,
            $address,
            $busname,
            $busmodel,
            $bustype,
            $market
          );

          if ($stmt->execute()) {

            $userId = $stmt->insert_id;

            /* ===========================
              CREATE USER WALLET
            ============================ */

            if ($accountType === 'buyer') {

                $walletType = 'buyer';

                $walletStmt = $conn->prepare("
                    INSERT INTO wallets (user_id, wallet_type, balance, total_transacted)
                    VALUES (?, ?, 0.00, 0.00)
                ");
                $walletStmt->bind_param("is", $userId, $walletType);
                $walletStmt->execute();
                $walletStmt->close();

            } elseif ($accountType === 'seller') {

                $walletType = 'seller';

                $walletStmt = $conn->prepare("
                    INSERT INTO wallets (user_id, wallet_type, balance, total_transacted)
                    VALUES (?, ?, 0.00, 0.00)
                ");
                $walletStmt->bind_param("is", $userId, $walletType);
                $walletStmt->execute();
                $walletStmt->close();
            }

            $success = "Account registered successfully! <span class='redirect-msg'></span>";

          } else {
            $error = "Error: " . $stmt->error;
          }

          $stmt->close();
        }
      }
     }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- ✅ BASIC SEO -->
  <meta name="description" content="Create your Makethub account and start buying, selling, or listing properties globally.">

  <!-- ✅ OPEN GRAPH (WHATSAPP, FACEBOOK) -->
  <meta property="og:title" content="Register on Makethub!" />
  <meta property="og:description" content="Join Makethub today — connect as a buyer, seller, or property owner in a global marketplace." />
  <meta property="og:image" content="https://makethub.shop/Images/Makethub%20Logo.png" />
  <meta property="og:url" content="https://makethub.shop/register.php" />
  <meta property="og:type" content="website" />

  <!-- ✅ TWITTER -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Register on Makethub">
  <meta name="twitter:description" content="Create your Makethub account and start trading globally.">
  <meta name="twitter:image" content="https://makethub.shop/Images/Makethub%20Logo.png">

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

  <title>Register Account ~ Makethub</title>
</head>
<body>
  <div class="container">
    <?php if ($accountType === 'buyer'): ?>
    <main id="buyer-register-tab">
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/Makethub Logo.png" alt="Makethub Logo" width="40">
            <h1 class="login">Makethub</h1>
          </div>
          <h3>Find Local. Shop Without Limits!</h3>
        </section>
        <form action="" method="POST">
          <h2>Register account on Makethub</h2>
          <p style="font-size:13px; color:#555; margin-bottom:10px;">
            Your information is securely stored and used only to create your Makethub account. 
            We do not share your data with third parties.
          </p>
          <div class="account-type">
            <div class="account-icon">🛒</div>
            <div class="regInfo">
              <h4>Account type</h4>
              <p><?= ucfirst(htmlspecialchars($accountType)) ?></p>
            </div>
          </div>

          <?php if ($error): ?>
            <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></p>
          <?php elseif ($success): ?>
            <p class="successMessage">
              <i class="fa-solid fa-check-circle"></i> <?= $success ?>
            </p>
          <?php endif; ?>
          <div class="form-content-wrapper">
            <div class="form-content">
              <div class="inpBox">
                <input type="text" name="full_name" value="<?= htmlspecialchars($full_name ?? '') ?>" placeholder="" required>
                <label>Full Name</label>
              </div>
              <div class="inpBox">
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" placeholder="" required>
                <label>Username</label>
              </div>
              <div class="inpBox">
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="" required>
                <label>Email</label>
              </div>
              <div class="inpBox">
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="" required>
                <label>Phone</label>
              </div>
              <div class="selectorBox">
                <span>Country</span>
                <select id="country" name="country" required>
                  <option value="">-- Select Country --</option>
                  <?php
                    $countries = $conn->query("SELECT location_id, name FROM locations WHERE type='country' ORDER BY name ASC");
                    while ($row = $countries->fetch_assoc()):
                  ?>
                    <option value="<?= $row['location_id']; ?>" 
                      <?= ($country == $row['location_id']) ? 'selected' : ''; ?>>
                      <?= htmlspecialchars($row['name']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>
            <div class="form-content">
              <div class="inpBox">
                <input type="text" name="address" value="<?= htmlspecialchars($address ?? '') ?>" placeholder="" required>
                <label>Address e.g Watamu</label>
              </div>
              <div class="selectorBox">
                <span>County</span>
                <select id="county" name="county">
                  <option value="">-- Select County --</option>
                </select>
              </div>
              <div class="selectorBox">
                <span>Ward</span>
                <select id="ward" name="ward" required data-selected="<?= htmlspecialchars($location_id ?? '') ?>">
                  <option value="">-- Select Ward --</option>
                </select>
              </div>
              <div class="inpBox">
                <input type="password" name="password" id="password" class="password-field" placeholder="" required>
                <label>Password</label>
                <i class="fa-regular fa-eye toggle-password" title="Show Password"></i>

                <!-- Password strength -->
                <div class="password-strength">
                  <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                  </div>
                  <ul class="strength-rules">
                    <li id="len">• At least 8 characters</li>
                    <li id="upper">• Uppercase letter</li>
                    <li id="lower">• Lowercase letter</li>
                    <li id="number">• Number</li>
                    <li id="special">• Special character</li>
                  </ul>
                </div>
              </div>
              <div class="inpBox">
                <input type="password" name="confirm_password" class="password-field" placeholder="" required>
                <label>Confirm Password</label>
                <i class="fa-regular fa-eye toggle-password" title="Show Password"></i>
              </div>
              <button type="submit">Register</button>
            </div>
          </div>
          <p class="reDctor">Already have an account? <a href="index.php">Login</a></p>
          <input type="hidden" id="old_country" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>">
          <input type="hidden" id="old_county" value="<?= htmlspecialchars($_POST['county'] ?? '') ?>">
        </form>
      </div>
    </main>
    <?php endif; ?>
    <?php if ($accountType === 'seller'): ?>
    <main id="seller-register-tab">
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/Makethub Logo.png" alt="Makethub Logo" width="40">
            <h1 class="login">Makethub</h1>
          </div>
          <h3>List Once. Sell Everywhere!</h3>
        </section>
        <form action="" method="POST">
          <h2>Register account on Makethub</h2>
          <p style="font-size:13px; color:#555; margin-bottom:10px;">
            Your information is securely stored and used only to create your Makethub account. 
            We do not share your data with third parties.
          </p>
          <div class="account-type">
            <div class="account-icon">📦</div>
            <div class="regInfo">
              <h4>Account type</h4>
              <p><?= ucfirst(htmlspecialchars($accountType)) ?></p>
            </div>
          </div>

          <?php if ($error): ?>
            <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></p>
          <?php elseif ($success): ?>
            <p class="successMessage"><i class="fa-solid fa-check-circle"></i> <?= $success ?></p>
          <?php endif; ?>
          <div class="form-content-wrapper">
            <div class="form-content">
              <div class="inpBox">
                <input type="text" name="full_name" value="<?= htmlspecialchars($full_name ?? '') ?>" placeholder="" required>
                <label>Full Name</label>
              </div>
              <div class="inpBox">
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" placeholder="" required>
                <label>Username</label>
              </div>
              <div class="inpBox">
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="" required>
                <label>Email</label>
              </div>
              <div class="inpBox">
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="" required>
                <label>Phone</label>
              </div>
              <div class="inpBox">
                <input type="text" name="busname" value="<?= htmlspecialchars($busname ?? '') ?>" placeholder="" required>
                <label>Business Name</label>
              </div>
              
              <div class="selectorBox">
                <span>Business Model</span>
                <select id="busmodel" name="busmodel" required>
                  <option value="">-- Select Business Model --</option>
                  <option value="products" <?= ($busmodel ?? '') === 'products' ? 'selected' : ''; ?>>Products</option>
                  <option value="services" <?= ($busmodel ?? '') === 'services' ? 'selected' : ''; ?>>Services</option>
                  <option value="rentals" <?= ($busmodel ?? '') === 'rentals' ? 'selected' : ''; ?>>Rentals</option>
                </select>
              </div>

              <div class="selectorBox">
                <span>Business type</span>
                <select id="bustype" name="bustype" required>
                  <option value="">-- Select Type --</option>
                  <option value="shop" <?= ($bustype ?? '') === 'shop' ? 'selected' : ''; ?>>Shop</option>
                  <option value="supermarket" <?= ($bustype ?? '') === 'supermarket' ? 'selected' : ''; ?>>Supermarket</option>
                  <option value="kiosk" <?= ($bustype ?? '') === 'kiosk' ? 'selected' : ''; ?>>Kiosk</option>
                  <option value="kibanda" <?= ($bustype ?? '') === 'kibanda' ? 'selected' : ''; ?>>Kibanda</option>
                  <option value="canteen" <?= ($bustype ?? '') === 'canteen' ? 'selected' : ''; ?>>Canteen</option>
                  <option value="service_provider" <?= ($bustype ?? '') === 'service_provider' ? 'selected' : ''; ?>>Service Provider</option>
                  <option value="rentals" <?= ($bustype ?? '') === 'rentals' ? 'selected' : ''; ?>>Rentals</option>
                </select>
              </div>
            </div>
            <div class="form-content">
              <div class="inpBox">
                <input type="text" name="address" value="<?= htmlspecialchars($address ?? '') ?>" placeholder="" required>
                <label>Address eg. Tezo</label>
              </div>
              <div class="selectorBox">
                <span>Country</span>
                  <select id="country" name="country">
                    <option value="">-- Select Country --</option>
                    <?php
                      $countries = $conn->query("SELECT location_id, name FROM locations WHERE type='country' ORDER BY name ASC");
                      while ($row = $countries->fetch_assoc()):
                    ?>
                      <option value="<?= $row['location_id']; ?>" 
                        <?= ($country == $row['location_id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($row['name']); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
              </div>

              <div class="selectorBox">
                <span>Market Type</span>
                <select id="market" name="market" required>
                  <option value="">-- Select Market Type --</option>
                  <option value="Local" <?= ($market ?? '') === 'Local' ? 'selected' : ''; ?>>Local</option>
                  <option value="National" <?= ($market ?? '') === 'National' ? 'selected' : ''; ?>>National</option>
                  <option value="Global" <?= ($market ?? '') === 'Global' ? 'selected' : ''; ?>>Global</option>
                </select>
              </div>

              <div class="selectorBox">
                <span>County</span>
                <select id="county" name="county">
                  <option value="">-- Select County --</option>
                </select>
              </div>
              <div class="selectorBox">
                <span>Ward</span>
                <select id="ward" name="ward" required data-selected="<?= htmlspecialchars($location_id ?? '') ?>">
                  <option value="">-- Select Ward --</option>
                </select>
              </div>
              <div class="inpBox">
                <input type="password" name="password" id="password" class="password-field" placeholder="" required>
                <label>Password</label>
                <i class="fa-regular fa-eye toggle-password" title="Show Password"></i>

                <!-- Password strength -->
                <div class="password-strength">
                  <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                  </div>
                  <ul class="strength-rules">
                    <li id="len">• At least 8 characters</li>
                    <li id="upper">• Uppercase letter</li>
                    <li id="lower">• Lowercase letter</li>
                    <li id="number">• Number</li>
                    <li id="special">• Special character</li>
                  </ul>
                </div>
              </div>
              <div class="inpBox">
                <input type="password" name="confirm_password" class="password-field" placeholder="" required>
                <label>Confirm Password</label>
                <i class="fa-regular fa-eye toggle-password" title="Show Password"></i>
              </div>
              <button type="submit">Register</button>
            </div>
          </div>
          <p class="reDctor">Already have an account? <a href="index.php">Login</a></p>

          <input type="hidden" id="old_country" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>">
          <input type="hidden" id="old_county" value="<?= htmlspecialchars($_POST['county'] ?? '') ?>">
        </form>
      </div>
    </main>
    <?php endif; ?>
    <footer>
      <p>&copy; 2025/2026, Makethub.shop, All Rights Reserved.</p><br>
      <p>
        <a href="privacy.php">Privacy Policy</a> |
        <a href="terms.php">Terms & Conditions</a> |
        <a href="contact.php">Contact Us</a>
      </p>
    </footer>
  </div>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>
  <script>
    const passwordInput = document.getElementById("password");
    const strengthFill = document.getElementById("strengthFill");
    const strengthBox = document.querySelector(".password-strength");

    const rules = {
      len: v => v.length >= 8,
      upper: v => /[A-Z]/.test(v),
      lower: v => /[a-z]/.test(v),
      number: v => /\d/.test(v),
      special: v => /[^A-Za-z0-9]/.test(v)
    };

    // Show when focused
    passwordInput.addEventListener("focus", () => {
      strengthBox.classList.add("active");
    });

    // Hide when focus leaves (optional but clean)
    passwordInput.addEventListener("blur", () => {
      if (!passwordInput.value) {
        strengthBox.classList.remove("active");
      }
    });

    // Strength logic
    passwordInput.addEventListener("input", () => {
      const value = passwordInput.value;
      let score = 0;

      Object.keys(rules).forEach(id => {
        const valid = rules[id](value);
        document.getElementById(id).classList.toggle("valid", valid);
        if (valid) score++;
      });

      const percent = (score / 5) * 100;
      strengthFill.style.width = percent + "%";

      if (percent < 40) strengthFill.style.background = "#dc2626";
      else if (percent < 80) strengthFill.style.background = "#f59e0b";
      else strengthFill.style.background = "#16a34a";
    });
  </script>
</body>
</html>
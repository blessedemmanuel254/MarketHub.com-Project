<?php
session_start();
include 'connection.php';

$_SESSION['accountType'] = "sales_agent";

$accountType = $_SESSION['accountType']; 

/* -----------------------------
CAPTURE REFERRAL FROM LINK
----------------------------- */
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $_SESSION['agency_code'] = trim($_GET['ref']); // store in session
}

// Use session value globally
$country = "";
$refFromLink = $_SESSION['agency_code'] ?? '';

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
$referralToCheck = "";

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

function generateReferralCode(){
  return strtoupper(substr(bin2hex(random_bytes(5)),0,8));
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

  $referral_input = trim($_POST['agency_code'] ?? '');

  // Priority: POST (user typed) > session (from URL)
  $referralToCheck = $referral_input ?: $refFromLink;

  $referrer_id = null;

  if (!empty($referralToCheck)) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE agency_code = ? LIMIT 1");
    $stmt->bind_param("s", $referralToCheck);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $referrer_id = $row['user_id'];
    } else {
        $error = "Invalid agency code!";
    }

    $stmt->close();
  }

  if (!$full_name || !$username || !$email || !$phone || !$password || !$confirm_password || !$location_id || !$address){
    $error = "All fields are required!";
  } else if (!$accountType) {
    $error = 'Visit the <a href="accountTypeSelection.php">account-type selection</a> page to proceed.';
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
      $error = "Username or email already exists.";
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
      } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
      } else {
        // Validate password strength
        $passwordError = validatePassword($password);
      if ($passwordError) {
        $error = $passwordError; // simple single-line error
      } else {

        if (!$error) {
        $hashedPassword = password_hash($password,PASSWORD_DEFAULT);
        $newReferralCode = generateReferralCode();

        /* INSERT USER */
        $busname = null;
        $busmodel = null;
        $bustype = null;
        $market = null;

        $stmt = $conn->prepare("
          INSERT INTO users
          (account_type, full_name, username, email, phone, password, location_id, address, business_name, business_model, business_type, market_scope, agency_code, referred_by, created_at, updated_at, economic_period_count)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 0)
        ");
        $stmt->bind_param(
          "ssssssissssssi",
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
          $market,
          $newReferralCode,
          $referrer_id
        );

        if($stmt->execute()){

          $newUserId = $stmt->insert_id;

          /* =========================
            INSERT COMMISSIONS (PENDING - FINANCIAL TRANSACTIONS)
          ========================= */

          $commissionLevels = [
            1 => 100,
            2 => 40,
            3 => 20
          ];

          $currentReferrer = $referrer_id;
          $level = 1;
          
          /* -----------------------------
            NO REFERRER → COMPANY ACCOUNT
          ----------------------------- */
          if (!$currentReferrer) {

              $systemUserId = 21;

              $walletStmt = $conn->prepare("
                SELECT wallet_id 
                FROM wallets 
                WHERE user_id = ? AND wallet_type = 'administrator' LIMIT 1
              ");
              $walletStmt->bind_param("i", $systemUserId);
              $walletStmt->execute();
              $walletStmt->bind_result($walletId);
              $walletStmt->fetch();
              $walletStmt->close();

              if ($walletId) {

                  $amount = $commissionLevels[1];

                  $description = "System commission (no referrer) from agent $newUserId";

                  $stmtTxn = $conn->prepare("
                    INSERT INTO financial_transactions
                    (
                      source_type,
                      source_id,
                      wallet_id,
                      payer_id,
                      receiver_id,
                      transaction_type,
                      amount,
                      status,
                      description,
                      created_at
                    )
                    VALUES
                    ('agency_commission', ?, ?, ?, ?, 'commission', ?, 'pending', ?, NOW())
                  ");

                  $stmtTxn->bind_param(
                    "iiiids",
                    $newUserId,
                    $walletId,
                    $newUserId,
                    $systemUserId,
                    $amount,
                    $description
                  );

                  $stmtTxn->execute();
                  $stmtTxn->close();
              }
          } else {

              while ($currentReferrer && $level <= 3) {

                  $amount = $commissionLevels[$level];

                  /* -----------------------------
                    GET SALES WALLET OF REFERRER
                  ----------------------------- */
                  $walletStmt = $conn->prepare("
                      SELECT wallet_id 
                      FROM wallets 
                      WHERE user_id = ? AND wallet_type = 'sales'
                      LIMIT 1
                  ");
                  $walletStmt->bind_param("i", $currentReferrer);
                  $walletStmt->execute();
                  $walletStmt->bind_result($walletId);
                  $walletStmt->fetch();
                  $walletStmt->close();

                  if ($walletId) {

                      $description = "Pending Level $level commission from agent $newUserId";

                      $stmtTxn = $conn->prepare("
                          INSERT INTO financial_transactions
                          (
                            source_type,
                            source_id,
                            wallet_id,
                            payer_id,
                            receiver_id,
                            transaction_type,
                            amount,
                            status,
                            description,
                            created_at
                          )
                          VALUES
                          ('agency_commission', ?, ?, ?, ?, 'commission', ?, 'pending', ?, NOW())
                      ");

                      $stmtTxn->bind_param(
                          "iiiids",
                          $newUserId,        // source agent
                          $walletId,
                          $newUserId,        // payer
                          $currentReferrer,  // receiver
                          $amount,
                          $description
                      );

                      $stmtTxn->execute();
                      $stmtTxn->close();
                  }

                  /* -----------------------------
                    MOVE TO NEXT LEVEL
                  ----------------------------- */
                  $stmtRef = $conn->prepare("
                      SELECT referred_by FROM users WHERE user_id = ?
                  ");
                  $stmtRef->bind_param("i", $currentReferrer);
                  $stmtRef->execute();
                  $stmtRef->bind_result($nextReferrer);
                  $stmtRef->fetch();
                  $stmtRef->close();

                  $currentReferrer = $nextReferrer;
                  $level++;
              }
          }

          // Clear referral session
          unset($_SESSION['agency_code']);

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

  <!-- SEO / LINK PREVIEW (OPEN GRAPH) -->
  <meta property="og:title" content="Join Makethub as a Sales Agent!" />
  <meta property="og:description" content="Start earning by joining Makethub. Grow your network and income today." />
  <meta property="og:image" content="https://makethub.shop/Images/Makethub%20Logo.png" />
  <meta property="og:url" content="https://makethub.shop/agentregister.php?ref=<?= htmlspecialchars($_GET['ref'] ?? '') ?>" />
  <meta property="og:type" content="website" />

  <!-- Twitter (also used by WhatsApp/Telegram sometimes) -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Join Makethub as a Sales Agent!">
  <meta name="twitter:description" content="Become a Makethub agent and start earning today.">
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

  <title>Agent Register Account ~ Makethub</title>
</head>
<body>
  <div class="container">
    <?php if ($accountType === 'sales_agent'): ?>
    <main id="sales_agent-register-tab">
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/Makethub Logo.png" alt="Makethub Logo" width="40">
            <h1 class="login">Makethub</h1>
          </div>
          <h3>Join Makethub as a Sales Agent. Grow Your Network!</h3>
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

          <?php if (!empty($error)): ?>
            <p class="errorMessage">
              <i class="fa-solid fa-circle-exclamation"></i>
              <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
          <?php elseif (!empty($success)): ?>
            <p class="successMessage">
              <i class="fa-solid fa-check-circle"></i>
              <?= strip_tags($success, '<span>'); ?>
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
              <div class="selectorBox">
                <span>County</span>
                <select id="county" name="county" required>
                  <option value="">-- Select County --</option>
                </select>
              </div>
            </div>
            <div class="form-content">
              <div class="inpBox">
                <input type="text" name="address" value="<?= htmlspecialchars($address ?? '') ?>" placeholder="" required>
                <label>Address e.g Watamu</label>
              </div>

              <div class="selectorBox">
                <span>Ward</span>
                <select id="ward" name="ward" required data-selected="<?= htmlspecialchars($location_id ?? '') ?>">
                  <option value="">-- Select Ward --</option>
                </select>
              </div>
              <div></div>
              <div class="inpBox">
                <input 
                type="text" 
                name="agency_code" 
                value="<?= htmlspecialchars(!empty($refFromLink) ? $refFromLink : $referralToCheck) ?>" 
                placeholder="">
                <label>
                  Agency Code
                  <?php if(empty($refFromLink)): ?>
                    (Optional)
                  <?php endif; ?>
                </label>
              </div>
              <div></div>
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
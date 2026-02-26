<?php
session_start();
include 'connection.php';

// Prevent direct access without selection
if (!isset($_SESSION['accountType'])) {
  header('Location: accountTypeSelection.php');
  exit();
}

$accountType = $_SESSION['accountType']; // buyer | seller

$error = "";
$success = "";
$full_name = "";
$username = "";
$email = "";
$phone = "";
$country = "";
$county = "";
$ward = "";
$address = "";
$busname = "";
$busmodel = "";
$bustype = "";
$market = "";

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
  

    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $country = trim($_POST['country'] ?? '');
    $county = trim($_POST['county'] ?? '');
    $ward = trim($_POST['ward'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $busname = trim($_POST['busname'] ?? '');
    $busmodel = trim($_POST['busmodel'] ?? '');
    $bustype = trim($_POST['bustype'] ?? '');
    $market = trim($_POST['market'] ?? '');

  if (!$full_name || !$username || !$email || !$phone || !$password || !$confirm_password || !$country || !$county || !$ward || !$address || ($accountType === 'seller' && (!$busname || !$busmodel || !$bustype || !$market))) {
    $error = "All fields are required.";
  } else if (!$accountType) {
    $error = 'Visit the <a href="accountTypeSelection.php">account-type selection</a> page to proceed.';
  } elseif (str_word_count($full_name) < 2) {
    $error = "Full name must include at least first and last name!";
  } elseif (strpos($username, ' ') !== false) {
    $error = 'Username should not have space(s)!';
  } elseif (strlen($username) > 20) {
      $error = 'Username should contain a maximum of 20 characters!';
  } elseif (strlen($username) < 5) {
    $error = 'Username is too short!';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email address!";
  } elseif (strlen($username) > 20) {
    $error = 'Username is too long!';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email address!';
  } elseif ($accountType === 'seller' && strlen($busname) > 25) {
    $error = "Business name too long!";
  } elseif (strlen($address) > 25) {
    $error = "Address too long!";
  } else {
    $encrypted_email = base64_encode($email);
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
              (account_type, full_name, username, email, phone, password, country, county, ward, address, business_name, business_model, business_type, market_scope, total_sales, rating_average, rating_count, created_at, updated_at)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, NOW(), NOW())
          ");

          $stmt->bind_param(
              "ssssssssssssss",
              $accountType,
              $full_name,
              $username,
              $encrypted_email,
              $encrypted_phone,
              $hashedPassword,
              $country,
              $county,
              $ward,
              $address,
              $busname,
              $busmodel,
              $bustype,
              $market
          );

          if ($stmt->execute()) {
            $success = "Account registered successfully! <span id='redirect-msg'></span>";
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <title>Register Account ~ Market Hub</title>
</head>
<body>
  <div class="container">
    <?php if ($accountType === 'buyer'): ?>
    <main id="buyer-register-tab">
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/Market Hub Logo.avif" alt="Market Hub Logo" width="40">
            <h1 class="login">Market&nbsp;Hub</h1>
          </div>
          <h3>Find Local. Shop Without Limits.</h3>
        </section>
        <form action="" method="POST">
          <h2>Register account on Market Hub</h2>
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
              <div class="selectorBox">
                <span>Country</span>
                <select id="country" name="country" required>
                  <option value=""><p>-- Select Country --</p></option>
                  <!--<option value="Baringo">Baringo</option>
                  <option value="Bomet">Bomet</option>
                  <option value="Bungoma">Bungoma</option>
                  <option value="Busia">Busia</option>
                  <option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
                  <option value="Embu">Embu</option>
                  <option value="Garissa">Garissa</option>
                  <option value="Homa Bay">Homa Bay</option>
                  <option value="Isiolo">Isiolo</option>
                  <option value="Kajiado">Kajiado</option>
                  <option value="Kakamega">Kakamega</option>
                  <option value="Kericho">Kericho</option>
                  <option value="Kiambu">Kiambu</option>-->
                  <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                  <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                  <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
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
                <select id="county" name="county" required>
                  <option value=""><p>-- Select County --</p></option>
                  <!--<option value="Baringo">Baringo</option>
                  <option value="Bomet">Bomet</option>
                  <option value="Bungoma">Bungoma</option>
                  <option value="Busia">Busia</option>
                  <option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
                  <option value="Embu">Embu</option>
                  <option value="Garissa">Garissa</option>
                  <option value="Homa Bay">Homa Bay</option>
                  <option value="Isiolo">Isiolo</option>
                  <option value="Kajiado">Kajiado</option>
                  <option value="Kakamega">Kakamega</option>
                  <option value="Kericho">Kericho</option>
                  <option value="Kiambu">Kiambu</option>-->
                  <option value="Kilifi" <?php echo ($county === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option>
                  <!--<option value="Kirinyaga">Kirinyaga</option>
                  <option value="Kisii">Kisii</option>
                  <option value="Kisumu">Kisumu</option>
                  <option value="Kitui">Kitui</option>
                  <option value="Kwale">Kwale</option> 
                  <option value="Laikipia">Laikipia</option>
                  <option value="Lamu">Lamu</option>
                  <option value="Machakos">Machakos</option>
                  <option value="Makueni">Makueni</option>
                  <option value="Mandera">Mandera</option>
                  <option value="Marsabit">Marsabit</option>
                  <option value="Meru">Meru</option>
                  <option value="Migori">Migori</option>
                  <option value="Mombasa">Mombasa</option>
                  <option value="Murang'a">Murang'a</option>
                  <option value="Nairobi">Nairobi</option>
                  <option value="Nakuru">Nakuru</option>
                  <option value="Nandi">Nandi</option>
                  <option value="Narok">Narok</option>
                  <option value="Nyamira">Nyamira</option>
                  <option value="Nyandarua">Nyandarua</option>
                  <option value="Nyeri">Nyeri</option>
                  <option value="Samburu">Samburu</option>
                  <option value="Siaya">Siaya</option>
                  <option value="Taita Taveta">Taita Taveta</option>
                  <option value="Tana River">Tana River</option>
                  <option value="Tharaka-Nithi">Tharaka-Nithi</option>
                  <option value="Trans Nzoia">Trans Nzoia</option>
                  <option value="Turkana">Turkana</option>
                  <option value="Uasin Gishu">Uasin Gishu</option>
                  <option value="Vihiga">Vihiga</option>
                  <option value="Wajir">Wajir</option>
                  <option value="West Pokot">West Pokot</option>-->
                </select>
              </div>
              <div class="selectorBox">
                <span>Ward</span>
                <select id="ward" name="ward" required>
                  <option value=""><p>-- Select Ward --</p></option>
                  <!--<option value="Sokoni Ward">Sokoni Ward</option>
                  <option value="Bomet">Bomet</option>
                  <option value="Bungoma">Bungoma</option>
                  <option value="Busia">Busia</option>
                  <option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
                  <option value="Embu">Embu</option>
                  <option value="Garissa">Garissa</option>
                  <option value="Homa Bay">Homa Bay</option>
                  <option value="Isiolo">Isiolo</option>
                  <option value="Kajiado">Kajiado</option>
                  <option value="Kakamega">Kakamega</option>
                  <option value="Kericho">Kericho</option>
                  <option value="Kiambu">Kiambu</option>-->
                  <option value="Sokoni Ward" <?php echo ($ward === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option>
                  <!--<option value="Kirinyaga">Kirinyaga</option>
                  <option value="Kisii">Kisii</option>
                  <option value="Kisumu">Kisumu</option>
                  <option value="Kitui">Kitui</option>
                  <option value="Kwale">Kwale</option> 
                  <option value="Laikipia">Laikipia</option>
                  <option value="Lamu">Lamu</option>
                  <option value="Machakos">Machakos</option>
                  <option value="Makueni">Makueni</option>
                  <option value="Mandera">Mandera</option>
                  <option value="Marsabit">Marsabit</option>
                  <option value="Meru">Meru</option>
                  <option value="Migori">Migori</option>
                  <option value="Mombasa">Mombasa</option>
                  <option value="Murang'a">Murang'a</option>
                  <option value="Nairobi">Nairobi</option>
                  <option value="Nakuru">Nakuru</option>
                  <option value="Nandi">Nandi</option>
                  <option value="Narok">Narok</option>
                  <option value="Nyamira">Nyamira</option>
                  <option value="Nyandarua">Nyandarua</option>
                  <option value="Nyeri">Nyeri</option>
                  <option value="Samburu">Samburu</option>
                  <option value="Siaya">Siaya</option>
                  <option value="Taita Taveta">Taita Taveta</option>
                  <option value="Tana River">Tana River</option>
                  <option value="Tharaka-Nithi">Tharaka-Nithi</option>
                  <option value="Trans Nzoia">Trans Nzoia</option>
                  <option value="Turkana">Turkana</option>
                  <option value="Uasin Gishu">Uasin Gishu</option>
                  <option value="Vihiga">Vihiga</option>
                  <option value="Wajir">Wajir</option>
                  <option value="West Pokot">West Pokot</option>-->
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
          <div class="or-divider">or</div>
          <div class="socialRegister">
            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google" width="20">
            <p>Register with google</p>
          </div>
          <div class="socialRegister">
            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apple/apple-original.svg" alt="Apple" width="20">
            <p>Register with apple</p>
          </div>
          <div class="socialRegister">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft" width="20">
            <p>Register with microsoft account</p>
          </div>
        </form>
      </div>
    </main>
    <?php endif; ?>
    <?php if ($accountType === 'seller'): ?>
    <main id="seller-register-tab">
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/Market Hub Logo.avif" alt="Market Hub Logo" width="40">
            <h1 class="login">Market&nbsp;Hub</h1>
          </div>
          <h3>List Once. Sell Everywhere.</h3>
        </section>
        <form action="" method="POST">
          <h2>Register account on Market Hub</h2>
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
                  <option value="dervices" <?= ($busmodel ?? '') === 'dervices' ? 'selected' : ''; ?>>Services</option>
                  <option value="dental" <?= ($busmodel ?? '') === 'dental' ? 'selected' : ''; ?>>Rental</option>
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
                  <option value="rental" <?= ($bustype ?? '') === 'rental' ? 'selected' : ''; ?>>Rental</option>
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
                <select id="country" name="country" required>
                  <option value=""><p>-- Select Country --</p></option>
                  <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option><!-- 
                  <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                  <option value="Kenya" <?php echo ($country === 'Kenya') ? 'selected' : ''; ?>>Kenya</option> -->
                </select>
              </div>

              <div class="selectorBox">
                <span>Market Type</span>
                <select id="market" name="market" required>
                  <option value="">-- Select Market Type --</option>
                  <option value="local" <?= ($market ?? '') === 'local' ? 'selected' : ''; ?>>Local</option>
                  <option value="national" <?= ($market ?? '') === 'national' ? 'selected' : ''; ?>>National</option>
                  <option value="global" <?= ($market ?? '') === 'global' ? 'selected' : ''; ?>>Global</option>
                </select>
              </div>

              <div class="selectorBox">
                <span>County</span>
                <select id="county" name="county" required>
                  <option value=""><p>-- Select County --</p></option>
                  <!--<option value="Baringo">Baringo</option>
                  <option value="Bomet">Bomet</option>
                  <option value="Bungoma">Bungoma</option>
                  <option value="Busia">Busia</option>
                  <option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
                  <option value="Embu">Embu</option>
                  <option value="Garissa">Garissa</option>
                  <option value="Homa Bay">Homa Bay</option>
                  <option value="Isiolo">Isiolo</option>
                  <option value="Kajiado">Kajiado</option>
                  <option value="Kakamega">Kakamega</option>
                  <option value="Kericho">Kericho</option>
                  <option value="Kiambu">Kiambu</option>-->
                  <option value="Kilifi" <?php echo ($county === 'Kilifi') ? 'selected' : ''; ?>>Kilifi</option>
                  <!--<option value="Kirinyaga">Kirinyaga</option>
                  <option value="Kisii">Kisii</option>
                  <option value="Kisumu">Kisumu</option>
                  <option value="Kitui">Kitui</option>
                  <option value="Kwale">Kwale</option> 
                  <option value="Laikipia">Laikipia</option>
                  <option value="Lamu">Lamu</option>
                  <option value="Machakos">Machakos</option>
                  <option value="Makueni">Makueni</option>
                  <option value="Mandera">Mandera</option>
                  <option value="Marsabit">Marsabit</option>
                  <option value="Meru">Meru</option>
                  <option value="Migori">Migori</option>
                  <option value="Mombasa">Mombasa</option>
                  <option value="Murang'a">Murang'a</option>
                  <option value="Nairobi">Nairobi</option>
                  <option value="Nakuru">Nakuru</option>
                  <option value="Nandi">Nandi</option>
                  <option value="Narok">Narok</option>
                  <option value="Nyamira">Nyamira</option>
                  <option value="Nyandarua">Nyandarua</option>
                  <option value="Nyeri">Nyeri</option>
                  <option value="Samburu">Samburu</option>
                  <option value="Siaya">Siaya</option>
                  <option value="Taita Taveta">Taita Taveta</option>
                  <option value="Tana River">Tana River</option>
                  <option value="Tharaka-Nithi">Tharaka-Nithi</option>
                  <option value="Trans Nzoia">Trans Nzoia</option>
                  <option value="Turkana">Turkana</option>
                  <option value="Uasin Gishu">Uasin Gishu</option>
                  <option value="Vihiga">Vihiga</option>
                  <option value="Wajir">Wajir</option>
                  <option value="West Pokot">West Pokot</option>-->
                </select>
              </div>
              <div class="selectorBox">
                <span>Ward</span>
                <select id="ward" name="ward" required>
                  <option value=""><p>-- Select Ward --</p></option>
                  <!--<option value="Sokoni Ward">Sokoni Ward</option>
                  <option value="Bomet">Bomet</option>
                  <option value="Bungoma">Bungoma</option>
                  <option value="Busia">Busia</option>
                  <option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
                  <option value="Embu">Embu</option>
                  <option value="Garissa">Garissa</option>
                  <option value="Homa Bay">Homa Bay</option>
                  <option value="Isiolo">Isiolo</option>
                  <option value="Kajiado">Kajiado</option>
                  <option value="Kakamega">Kakamega</option>
                  <option value="Kericho">Kericho</option>
                  <option value="Kiambu">Kiambu</option>-->
                  <option value="Sokoni Ward" <?php echo ($ward === 'Sokoni Ward') ? 'selected' : ''; ?>>Sokoni Ward</option>
                  <!--<option value="Kirinyaga">Kirinyaga</option>
                  <option value="Kisii">Kisii</option>
                  <option value="Kisumu">Kisumu</option>
                  <option value="Kitui">Kitui</option>
                  <option value="Kwale">Kwale</option> 
                  <option value="Laikipia">Laikipia</option>
                  <option value="Lamu">Lamu</option>
                  <option value="Machakos">Machakos</option>
                  <option value="Makueni">Makueni</option>
                  <option value="Mandera">Mandera</option>
                  <option value="Marsabit">Marsabit</option>
                  <option value="Meru">Meru</option>
                  <option value="Migori">Migori</option>
                  <option value="Mombasa">Mombasa</option>
                  <option value="Murang'a">Murang'a</option>
                  <option value="Nairobi">Nairobi</option>
                  <option value="Nakuru">Nakuru</option>
                  <option value="Nandi">Nandi</option>
                  <option value="Narok">Narok</option>
                  <option value="Nyamira">Nyamira</option>
                  <option value="Nyandarua">Nyandarua</option>
                  <option value="Nyeri">Nyeri</option>
                  <option value="Samburu">Samburu</option>
                  <option value="Siaya">Siaya</option>
                  <option value="Taita Taveta">Taita Taveta</option>
                  <option value="Tana River">Tana River</option>
                  <option value="Tharaka-Nithi">Tharaka-Nithi</option>
                  <option value="Trans Nzoia">Trans Nzoia</option>
                  <option value="Turkana">Turkana</option>
                  <option value="Uasin Gishu">Uasin Gishu</option>
                  <option value="Vihiga">Vihiga</option>
                  <option value="Wajir">Wajir</option>
                  <option value="West Pokot">West Pokot</option>-->
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
          <div class="or-divider">or</div>
          <div class="socialRegister">
            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google" width="20">
            <p>Register with google</p>
          </div>
          <div class="socialRegister">
            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apple/apple-original.svg" alt="Apple" width="20">
            <p>Register with apple</p>
          </div>
          <div class="socialRegister">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft" width="20">
            <p>Register with microsoft account</p>
          </div>
        </form>
      </div>
    </main>
    <?php endif; ?>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript" defer></script>
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
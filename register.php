<?php
session_start();
include 'connection.php';

$error = "";
$username = "";
$success = "";
$county = "";
$ward = "";

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
  $accountType = isset($_SESSION['accountType']) ? ucfirst(trim($_SESSION['accountType'])) : '';
  $country = "Kenya";
  $county = trim($_POST['county'] ?? '');
  $ward = trim($_POST['ward'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (empty($country) || empty($county) || empty($ward) || empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
    $error = "All fields are required.";
  } else if (!$accountType) {
    $error = 'Visit the <a href="accountTypeSelection.php">account-type selection</a> page to proceed.';
  } elseif (strpos($username, ' ') !== false) {
    $error = 'Username should not have space(s)!';
  } elseif (strlen($username) > 20) {
      $error = 'Username should contain a maximum of 20 characters!';
  } elseif (strlen($username) < 5) {
    $error = 'Username is too short!';
  } elseif (strlen($username) > 20) {
    $error = 'Username is too long!';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email address!';
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
        if (!$error) {
          $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

          $stmt = $conn->prepare("INSERT INTO users (account_type, country, county, ward, username, email, phone, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
          $stmt->bind_param("ssssssss", $accountType, $country, $county, $ward, $username, $encrypted_email, $encrypted_phone, $hashedPassword);

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
$accountType = isset($_SESSION['accountType']) ? ucfirst($_SESSION['accountType']) : '';
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

  <title>Register Account ~ MarketHub</title>
</head>
<body>
  <div class="container">
    <main>
      <div class="formContainer">
        <form action="" method="POST">
          <h2>Register account on MarketHub</h2>
          <div class="account-type">
            <div class="account-icon">🛒</div>
            <div class="regInfo">
              <h4>Account type</h4>
              <p><?= htmlspecialchars($accountType) ?></p>
            </div>
          </div>

          <?php if ($error): ?>
            <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></p>
          <?php elseif ($success): ?>
            <p class="successMessage"><i class="fa-solid fa-check-circle"></i> <?= $success ?></p>
          <?php endif; ?>
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
            <input type="password" name="password" class="password-field" placeholder="" required>
            <label>Password</label>
            <i class="fa-regular fa-eye toggle-password" title="Show Password"></i>
          </div>
          <div class="inpBox">
            <input type="password" name="confirm_password" class="password-field" placeholder="" required>
            <label>Confirm Password</label>
            <i class="fa-regular fa-eye toggle-password" title="Show Password"></i>
          </div>
          <button type="submit">Register</button>
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
    <footer>
      <p>&copy; 2025/2026, MarketHub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript"></script>
</body>
</html>
<?php
session_start();
include 'connection.php';

$error = "";
$success = "";

function normalizePhone($phone) {
  // Keep only digits and plus
  $cleaned = preg_replace('/[^\d+]/', '', $phone);
  if (strpos($cleaned, '+') === 0) {
    return $cleaned;
  } elseif (strpos($cleaned, '0') === 0) {
    return '+254' . substr($cleaned, 1);
  } elseif (strlen($cleaned) >= 9) {
    return '+' . $cleaned;
  }
  return '';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $identifier = trim($_POST['identifier'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($identifier) || empty($password)) {
    $error = "All fields are required.";
  } else {
    $encrypted_email = base64_encode($identifier);
    $normalized_phone = normalizePhone($identifier);
    $encrypted_phone = base64_encode($normalized_phone);

    // Check by username, email, or phone
    $query = "SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $identifier, $encrypted_email, $encrypted_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if (password_verify($password, $user['password'])) {
        $success = "Successfully logged in! <span id='redirect-msg'></span>";
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['account_type'] = $user['account_type'];

        // Redirect based on account type
        $redirectPage = strtolower($user['account_type']) === 'seller' ? 'sellerpage.php' : 'buyerpage.php';
        echo "<script>
                setTimeout(() => window.location.href = '$redirectPage', 3500);
              </script>";
      } else {
        $error = "Invalid password.";
      }
    } else {
      $error = "No account found with those credentials.";
    }
    $stmt->close();
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

  <title>Login ~ MarketHub account</title>
</head>
<body>
  <div class="container">
    <main>
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/MarketHub Logo.avif" alt="Market Hub Logo" width="40">
            <h1 class="login">Market&nbsp;Hub</h1>
          </div>
          <h3>Buy Local. Order Global!</h3>
        </section>
        <form action="" method="POST">
          <h2>Login to MarketHub</h2>
          <?php if ($error): ?>
            <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></p>
          <?php elseif ($success): ?>
            <p class="successMessage"><i class="fa-solid fa-check-circle"></i> <?= $success ?></p>
          <?php endif; ?>
          <div class="inpBox">
            <input type="text" name="identifier" value="<?php echo htmlspecialchars($identifier ?? ''); ?>" placeholder="" required>
            <label>Username, email or phone</label>
          </div>
          <div class="inpBox">
            <input type="password" name="password" class="password-field" placeholder="" required>
            <label>Password</label>
            <i class="fa-regular fa-eye toggle-password" title="Show Password"></i>
          </div>
          <div class="remember-me">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
          </div>
          <button type="submit">Login</button>
          <p class="reDctor"><a href="#" class="anchFgt">Forgot password</a></p>
          <p class="reDctor">Don't have an account? <a href="accountTypeSelection.php">Register</a></p>
          <div class="or-divider">or</div>
          <div class="socialLogin">
            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google" width="20">
            <p>Login with google</p>
          </div>
          <div class="socialLogin">
            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apple/apple-original.svg" alt="Apple" width="20">
            <p>Login with apple</p>
          </div>
          <div class="socialLogin">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft" width="20">
            <p>Login with microsoft account</p>
          </div>
        </form>
      </div>
    </main>
    <footer>
      <p>&copy; 2025, MarketHub.com, All Rights reserved.</p>
    </footer>
  </div>

  <script src="Scripts/general.js" type="text/javascript"></script>
</body>
</html>
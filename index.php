<?php
session_start();
include 'connection.php';

// AUTO-LOGIN VIA COOKIE
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['account_type'] = $_COOKIE['account_type'];
}

/* ===============================
  REDIRECT IF ALREADY LOGGED IN
================================ */
if (isset($_SESSION['user_id'], $_SESSION['account_type'])) {
  switch (strtolower($_SESSION['account_type'])) {
    case 'seller': header("Location: sellerPage.php"); exit;
    case 'buyer':  header("Location: buyerPage.php");  exit;
    case 'agent':  header("Location: agentPage.php");  exit;
    case 'admin':  header("Location: adminPage.php");  exit;
  }
}

/* ---------- SESSION SECURITY ---------- */
if (isset($_SESSION['user_id'], $_SESSION['account_type'])) {

  $accountType = strtolower($_SESSION['account_type']);

  if ($accountType === 'seller') {
    header("Location: sellerPage.php");
  } elseif ($accountType === 'buyer') {
    header("Location: buyerPage.php");
  } elseif ($accountType === 'admin') {
    header("Location: adminPage.php");
  } else {
    header("Location: index.php");
  }
  exit();
}

$error = $success = "";

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
  $passsword = $_POST['password'] ?? '';
  $login_type  = strtolower($_POST['login_type'] ?? '');

  if (!$identifier || !$passsword || !$login_type) {
    $error = "All fields are required.";
  } else {
    $encrypted_email = base64_encode($identifier);
    $normalized_phone = normalizePhone($identifier);
    $encrypted_phone = base64_encode($normalized_phone);

    /*
    IMPORTANT:
    Enforce account_type in the query itself
    */
    $query = "
      SELECT *
      FROM users
      WHERE account_type = ?
        AND (username = ? OR email = ? OR phone = ?)
      LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
      "ssss",
      $login_type,
      $identifier,
      $encrypted_email,
      $encrypted_phone
    );
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if (password_verify($passsword, $user['password'])) {
        $success = "Successfully logged in! <span id='redirect-msg'></span>";
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['account_type'] = $user['account_type'];

        // -----------------------
        // REMEMBER ME LOGIC
        // -----------------------
        if (!empty($_POST['remember'])) {
            // Cookie valid for 30 days
            $cookieTime = time() + (30 * 24 * 60 * 60); 
            setcookie("user_id", $user['user_id'], $cookieTime, "/");
            setcookie("username", $user['username'], $cookieTime, "/");
            setcookie("account_type", $user['account_type'], $cookieTime, "/");
        }

        // Redirect based on account type
        $redirectPage = strtolower($user['account_type']) === 'seller' ? 'sellerPage.php' : 'buyerPage.php';
        echo "<script>
                setTimeout(() => window.location.href = '$redirectPage', 3500);
              </script>";
      } else {
        $error = "One of the credentials is invalid.";
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

  <title>Login | Market Hub</title>
</head>
<body>
  <div class="container">
    <main>
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/Market Hub Logo.avif" alt="Market Hub Logo" width="40">
            <h1 class="login">Market&nbsp;Hub</h1>
          </div>
          <h3>Buy Local. Order Global!</h3>
        </section>
        <form action="" method="POST">
          <h2>Login to Market Hub</h2>
          <?php if ($error): ?>
            <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></p>
          <?php elseif ($success): ?>
            <p class="successMessage"><i class="fa-solid fa-check-circle"></i> <?= $success ?></p>
          <?php endif; ?>
          <div class="form-content-wrapper">
            <div class="form-content">
              <div class="inpBox">
                <input type="text" name="identifier" value="<?php echo htmlspecialchars($identifier ?? ''); ?>" placeholder="" required>
                <label>Username, email or phone</label>
              </div>
              <div class="inpBox">
                <input type="password" name="password" value="<?php echo htmlspecialchars($passsword ?? ''); ?>" id="password" class="password-field" placeholder="" required>
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
              <div class="account-type-box">
                <p class="account-title">Login as</p>

                <label class="account-type">
                  <input type="radio" name="login_type" value="buyer" required>
                  <div class="radio-dot"></div>
                  <span>Buyer</span>
                </label>

                <label class="account-type">
                  <input type="radio" name="login_type" value="seller">
                  <div class="radio-dot"></div>
                  Seller
                </label>

                <label class="account-type">
                  <input type="radio" name="login_type" value="agent">
                  <div class="radio-dot"></div>
                  Agent
                </label>

                <label class="account-type">
                  <input type="radio" name="login_type" value="admin">
                  <div class="radio-dot"></div>
                  Administrator
                </label>
              </div>
              <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
              </div>
              <button type="submit">Login</button>
              <p class="reDctor"><a href="#" class="anchFgt">Forgot password</a></p>
            </div>
            <div class="form-content">
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
            </div>
          </div>
        </form>
      </div>
    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>

  <script src="Scripts/general.js" type="text/javascript"></script>
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
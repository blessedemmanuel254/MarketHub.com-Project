<?php
session_start();
include 'connection.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set your launch date in UTC
$launchDateUTC = new DateTime('2026-04-07 12:00:00', new DateTimeZone('UTC'));
$launchTimestamp = $launchDateUTC->getTimestamp() * 1000;

$error = "";
$success = "";

if (isset($_POST['subscribe'])) {

  // CSRF validation
  if (
      empty($_POST['csrf_token']) ||
      empty($_SESSION['csrf_token']) ||
      !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
  ) {
      $error = "Invalid request. Please refresh and try again.";
  } else {

      $email = trim($_POST['email']);
      $hashed_email = hash('sha256', strtolower($email));

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $error = "Please enter a valid email address!";
      } else {
          // Check if email already exists
          $stmt = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
          $stmt->bind_param("s", $hashed_email);
          $stmt->execute();
          $stmt->store_result();

          if ($stmt->num_rows > 0) {
              $error = "This email is already subscribed!";
          } else {
              // Insert new subscriber
              $stmtInsert = $conn->prepare("INSERT INTO subscribers (email, created_at) VALUES (?, NOW())");
              $stmtInsert->bind_param("s", $hashed_email);

              if ($stmtInsert->execute()) {
                  $success = "Noted. We’ll update you! <span class='redirect-msg'></span>";
              } else {
                  $error = "Something went wrong. Please try again later.";
              }
              $stmtInsert->close();
          }
          $stmt->close();
      }

      // Regenerate token after processing
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

  <link rel="stylesheet" href="assets/css/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <title>Coming Soon ~ Maket Hub</title>
</head>
<body>
  <div class="container cmng-sn">
    <main>
      <section>
        <div class="top">
          <img src="Images/Maket Hub Logo.avif" alt="Maket Hub Logo" width="40">
          <h1 class="login">Maket&nbsp;Hub</h1>
        </div>
        <h3>Buy Local. Order Global!</h3>
      </section>

      <div class="content">
        <p class="ready-p">Getting things ready for launch</p>
        <h1>We’re <span>Going Live</span> Soon!</h1>
        <div class="launch-time">
          <div>
            <p>00</p>
            <span>Days</span>
          </div>
          <div>
            <p>00</p>
            <span>HOurs</span>
          </div>
          <div>
            <p>00</p>
            <span>Minutes</span>
          </div>
          <div>
            <p>00</p>
            <span>Seconds</span>
          </div>
        </div>
        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
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
          <div>
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="subscribe">Notify&nbsp;Me</button>
          </div>
        </form>
        <img src="Images/rocket.png" alt="Coming Soon Plane" class="rocket">
      </div>
    </main>
    <footer>
      <p>&copy; 2025/2026, Maket Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>

  <script>
  // Use the server-provided timestamp
  const launchTime = <?php echo $launchTimestamp; ?>;

  const daysEl = document.querySelector('.launch-time div:nth-child(1) p');
  const hoursEl = document.querySelector('.launch-time div:nth-child(2) p');
  const minutesEl = document.querySelector('.launch-time div:nth-child(3) p');
  const secondsEl = document.querySelector('.launch-time div:nth-child(4) p');

  function updateCountdown() {
      const now = Date.now(); // current UTC time
      const distance = launchTime - now;

      if (distance <= 0) {
          clearInterval(timerInterval);
          daysEl.textContent = "00";
          hoursEl.textContent = "00";
          minutesEl.textContent = "00";
          secondsEl.textContent = "00";
          return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      daysEl.textContent = days.toString().padStart(2,'0');
      hoursEl.textContent = hours.toString().padStart(2,'0');
      minutesEl.textContent = minutes.toString().padStart(2,'0');
      secondsEl.textContent = seconds.toString().padStart(2,'0');
  }

  const timerInterval = setInterval(updateCountdown, 1000);
  updateCountdown();
  </script>
</body>
</html>
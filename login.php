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
        <h1 class="login">MarketHub</h1>
        <form action="" method="POST">
          <h2>Login to MarketHub</h2>
          <!-- <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> All fields are required.</p> -->
          <div class="inpBox">
            <input type="text" placeholder="" required>
            <label>Username, email or phone</label>
          </div>
          <div class="inpBox">
            <input type="password" placeholder="" required>
            <label>Password</label>
            <i class="fa-regular fa-eye"></i>
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
</body>
</html>
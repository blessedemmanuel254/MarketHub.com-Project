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
          <!-- <p class="errorMessage"><i class="fa-solid fa-circle-exclamation"></i> All fields are required.</p> -->
          <div class="account-type">
            <div class="account-icon">🛒</div>
            <div class="regInfo">
              <h4>Account type</h4>
              <p>Buyer</p>
            </div>
          </div>
          <div class="selectorBox">
            <span>County</span>
            <select name="county" id="county" required>
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
              <option value="Kilifi">Kilifi</option>
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
            <input type="text" placeholder="" required>
            <label>Username</label>
          </div>
          <div class="inpBox">
            <input type="email" placeholder="" required>
            <label>Email</label>
          </div>
          <div class="inpBox">
            <input type="text" placeholder="" required>
            <label>Phone</label>
          </div>
          <div class="inpBox">
            <input type="password" placeholder="" required>
            <label>Password</label>
            <i class="fa-regular fa-eye"></i>
          </div>
          <div class="inpBox">
            <input type="password" placeholder="" required>
            <label>Confirm password</label>
            <i class="fa-regular fa-eye"></i>
          </div>
          <button type="submit">Register</button>
          <p class="reDctor">Already have an account? <a href="login.php">Login</a></p>
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
      <p>&copy; 2025, MarketHub.com, All Rights reserved.</p>
    </footer>
  </div>
</body>
</html>
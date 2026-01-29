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

  <title>Homepage | MarketHub</title>
</head>
<body>
  <div class="container">
    <header>
      <div class="lhs">
        <a href="index.html">
          <img src="Images/MarketHub Logo.avif" alt="MarketHub Logo">
          <h1>MarketHub</h1>
        </a>
      </div>
      <div class="rhs">
        <div class="help-icon">
          <i class="fa-regular fa-circle-question"></i>
          <p class="help-text">Help</p>
        </div>
        <select name="" id="country">
          <option value="">Kenya</option>
          <!--<option value="">Tanzania</option>
          <option value="">Uganda</option>-->
        </select>
        <a href="accountTypeSelection.php"></i><i class="fa-regular fa-user"></i> Register</a>
        <a href="index.php">Login</a>
        <i class="fa-solid fa-bars"></i>
      </div>
    </header>

    <main>
      <div class="mainContainer1">
        <div class="mContainer">
          <div class="welcomeContainer">
            <h1>Welcome&nbsp;to the MarketHub&nbsp;!</h1>
            <p>Your all-in-one platform for discovering and ordering from local markets and service providers. Bringing Local Markets to Your Screen.</p>
          </div>
          <div class="welcomeContainer">
            <h2>Are you a seller, service provider or a property owner scaling your market or a buyer seeking a product or service? It's just a tap away !</h2>
            <img src="Images/business-illustration.avif" alt="Scale your business on discover the markets" width="400">
            <a href="accountTypeSelection.php">Get&nbsp;started</a>
          </div>
        </div>
        <div class="mContainerForForm">
          <form action="" method="POST">
            <p class="catFormTitle">Choose your region to proceed...<br><span>Discover the markets near you !</span></p>
            <div class="selectorBox">
              <span>County</span>
              <select name="county" id="county" required>
                <option value="">-- Select County --</option>
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
            <div class="selectorBox">
              <span>Area of residence</span>
              <select name="area" id="area" required>
                <option value="">-- Select Area --</option>
                <optgroup label="Sokoni ward">
                <option value="Kibaoni">Kibaoni</option>
                <option value="Pwani University Area">Pwani University Area</option>
                <option value="Kilifi Town Center">Kilifi Town Center</option>
                <option value="Mnarani">Mnarani</option>
                <option value="Mtwapa">Mtwapa</option>
                <option value="Sokoni Market Area">Sokoni Market Area</option>
                <option value="Sokoni Market Area">Cherowamaye</option>
                </optgroup>
                <optgroup label="Tezo Ward">
                  <option value="Ngerenya">Ngerenya</option>
                  <option value="Zowerani">Zowerani</option>
                  <option value="Mtondia/Majaoni">Mtondia/Majaoni</option>
                </optgroup>
              </select>
            </div>
            <button type="submit">Find market</button>
          </form>
        </div>
        <div class="mContainer">
          <div class="welcomeContainer1">
            <h2>Are you a seller scaling your business or a customer seeking a product or service? It's just a tap away !</h2>
            <img src="Images/business-illustration.avif" alt="Scale your business on discover the markets" width="400">
            <a href="accountTypeSelection.php">Get started</a>
          </div>
        </div>
      </div>
    </main>
    <footer>
      <p>&copy; 2025, MarketHub.com, All Rights reserved.</p>
    </footer>
  </div>
</body>
</html>
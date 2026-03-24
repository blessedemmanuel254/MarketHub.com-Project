<?php
session_start();
require_once 'connection.php';

/* ---------- AUTH ---------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$accountType = $_SESSION['account_type'];

/* ---------- HELPERS ---------- */
function safe($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
function encodeField($v) {
    return base64_encode($v);
}
function decodeField($v) {
    return $v ? base64_decode($v) : '';
}

/* ---------- FETCH USER ---------- */
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit();
}

/* ---------- DECODE DATA ---------- */
$user['email'] = decodeField($user['email']);
$user['phone'] = decodeField($user['phone']);

/* ---------- UPDATE PROFILE ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $full_name    = trim($_POST['full_name']);
  $phone        = encodeField(trim($_POST['phone']));
  $bio          = trim($_POST['bio']);
  $username     = trim($_POST['username']);
  $country      = trim($_POST['country']);
  $county       = trim($_POST['county']);
  $ward         = trim($_POST['ward']);
  $address      = trim($_POST['address']);

  $profile_image = $user['profile_image'];

  /* ---------- IMAGE UPLOAD (SAFE) ---------- */
  if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === 0) {

      $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
      $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

      $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime  = finfo_file($finfo, $_FILES['profile_image']['tmp_name']);
      finfo_close($finfo);

      if (in_array($ext, $allowedExt) && in_array($mime, $allowedMime)) {

          $dir = "uploads/profiles/";
          if (!is_dir($dir)) {
              mkdir($dir, 0755, true);
          }

          $fileName = "user_{$user_id}_" . time() . "." . $ext;
          $path = $dir . $fileName;

          if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $path)) {
              $profile_image = $path;
          }
      }
      // invalid files are silently ignored
  }

  /* ---------- SAVE ---------- */
  $update = $conn->prepare("
      UPDATE users SET
          full_name = ?, phone = ?, bio = ?, username = ?, country = ?, county = ?, ward = ?,
          address = ?, profile_image = ?, updated_at = NOW()
      WHERE user_id = ?
  ");

  $update->bind_param(
    "ssssssssssi",
    $full_name, $phone, $bio, $username, $country, $county, $ward, $address, $profile_image, $user_id
  );
  $update->execute();
  $update->close();

  header("Location: userProfile.php?updated=1");
  exit();
}

/* ---------- BIO ---------- */
$bioMaxLength = 150;
$bio = !empty($user['bio']) ? substr($user['bio'], 0, $bioMaxLength) : '';
$safeBio = safe($bio);

/* ---------- SAFE IMAGE ---------- */
$profileImg = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
if (!empty($user['profile_image'])) {
    $realPath = realpath($user['profile_image']);
    if ($realPath && is_file($realPath)) {
        $profileImg = $user['profile_image'];
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <title>My Profile | Maket Hub</title>
</head>
<body>
  <div class="container">
    <main class="profile-main" id="buyerProfile">
      <div class="formContainer">
        <section>
          <div class="top">
            <img src="Images/Maket Hub Logo.avif" alt="Maket Hub Logo" width="40">
            <h1 class="login">Maket&nbsp;Hub</h1>
          </div>
          <h3>Update your Maket Hub Profile!</h3>
        </section>
      </div>
      <?php if ($accountType === 'buyer'): ?>
      <div class="container profile-container">

        <?php if (isset($_GET['updated'])): ?>
          <div class="success"><i class="fa-solid fa-check-circle"></i>Profile updated successfully!</div>
        <?php endif; ?>

        <div class="profile-header">
          <div class="profile-pic">
            <img id="profilePreview"
              src="<?= safe($user['profile_image']) ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>">
              <input type="file" id="profileImage" name="profile_image" accept="image/png,image/jpeg,image/webp" form="profileForm">
            <label for="profileImage"><i class="fa fa-camera"></i></label>
          </div>
          <div>
            <h2><?= safe($user['full_name']); ?></h2>
            <p>Edit your Maket Hub profile details</p>
          </div>
        </div>

        <form id="profileForm" class="profile-form" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= safe($user['full_name']); ?>">
          </div>

          <div class="form-group">
            <label>Username (read-only)</label>
            <input type="text" name="username" value="<?= safe($user['username']); ?>" disabled>
          </div>

          <div class="form-group">
            <label>Email (read-only)</label>
            <input type="email" value="<?= safe($user['email']); ?>" disabled>
          </div>

          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= safe($user['phone']); ?>">
          </div>


          <div class="form-group">
            <label>Country</label>
            <select name="country">
              <option <?= $user['country']=='Kenya'?'selected':'' ?>>Kenya</option>
              <option <?= $user['country']=='Uganda'?'selected':'' ?>>Uganda</option>
              <option <?= $user['country']=='Tanzania'?'selected':'' ?>>Tanzania</option>
            </select>
          </div>

          <div class="form-group">
            <label>Physical Address</label>
            <input type="text" name="address" value="<?= safe($user['address']); ?>">
          </div>

          <div class="form-group">
            <label>Bio (max <?= $bioMaxLength ?> characters)</label>
            <textarea id="bioTextarea" name="bio" maxlength="<?= $bioMaxLength ?>" 
                      placeholder="Tell something about yourself..."><?= $safeBio ?></textarea>
            <small id="bioCount"><?= strlen($bio) ?>/<?= $bioMaxLength ?> characters</small>
          </div>
          
          <div class="form-group">
            <label>County</label>
            <select name="county">
              <option <?= $user['county']=='Kenya'?'selected':'' ?>>Kilifi</option>
              <option <?= $user['county']=='Uganda'?'selected':'' ?>>Mombasa</option>
              <option <?= $user['county']=='Tanzania'?'selected':'' ?>>Bungoma</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Ward</label>
            <select name="ward">
              <option <?= $user['ward']=='Kenya'?'selected':'' ?>>Sokoni</option>
              <option <?= $user['ward']=='Uganda'?'selected':'' ?>>Kilifi North</option>
              <option <?= $user['ward']=='Tanzania'?'selected':'' ?>>Kilifi South</option>
            </select>
          </div>
          <div></div>
          <div></div>

          <button type="submit">Save Profile</button>
        </form>
      </div>
      <?php endif; ?>
      <?php if ($accountType === 'seller'): ?>
      <div class="container profile-container">

        <?php if (isset($_GET['updated'])): ?>
          <div class="success"><i class="fa-solid fa-check-circle"></i>Profile updated successfully!</div>
        <?php endif; ?>

        <div class="profile-header">
          <div class="profile-pic">
            <img id="profilePreview"
              src="<?= safe($user['profile_image']) ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>">
              <input type="file" id="profileImage" name="profile_image" accept="image/png,image/jpeg,image/webp" form="profileForm">
            <label for="profileImage"><i class="fa fa-camera"></i></label>
          </div>
          <div>
            <h2><?= safe($user['full_name']); ?></h2>
            <p>Edit your Maket Hub details</p>
          </div>
        </div>

        <form id="profileForm" class="profile-form" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= safe($user['full_name']); ?>" required>
          </div>

          <div class="form-group">
            <label>Username (read-only)</label>
            <input type="text" name="username" value="<?= safe($user['username']); ?>" disabled>
          </div>

          <div class="form-group">
            <label>Email (read-only)</label>
            <input type="email" value="<?= safe($user['email']); ?>" disabled>
          </div>

          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= safe($user['phone']); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Business Name</label>
            <input type="text" name="busname" value="<?= safe($user['business_name']); ?>" placeholder="" required>
          </div>
              
          <div class="form-group">
            <label>Business Model</label>
            <select id="busmodel" name="busmodel" required>
              <option value="">-- Select Business Model --</option>
              <option value="products" <?= $user['business_model']=='products'?'selected':'' ?>>Products</option>
              <option value="services" <?= $user['business_model']=='services'?'selected':'' ?>>Services</option>
              <option value="rental" <?= $user['business_model']=='rental'?'selected':'' ?>>Rental</option>
            </select>
          </div>

          <div class="form-group">
            <label>Business Type</label>
            <select id="busmodel" name="busmodel" required>
              <option value="">-- Select Type --</option>
              <option value="shop" <?= $user['business_type']=='shop'?'selected':'' ?>>Shop</option>
              <option value="supermarket" <?= $user['business_type']=='supermarket'?'selected':'' ?>>Supermarket</option>
              <option value="kiosk" <?= $user['business_type']=='kiosk'?'selected':'' ?>>Kiosk</option>
              <option value="kibanda" <?= $user['business_type']=='kibanda'?'selected':'' ?>>Kibanda</option>
              <option value="canteen" <?= $user['business_type']=='canteen'?'selected':'' ?>>Canteen</option>
              <option value="service_provider" <?= $user['business_type']=='service_provider'?'selected':'' ?>>Service Provider</option>
              <option value="rental" <?= $user['business_type']=='rental'?'selected':'' ?>>Rental</option>
            </select>
          </div>

          <div class="form-group">
            <label>Physical Address</label>
            <input type="text" name="address" value="<?= safe($user['address']); ?>" required>
          </div>

          <div class="form-group">
            <label>Country</label>
            <select name="country">
              <option <?= $user['country']=='Kenya'?'selected':'' ?>>Kenya</option>
              <option <?= $user['country']=='Uganda'?'selected':'' ?>>Uganda</option>
              <option <?= $user['country']=='Tanzania'?'selected':'' ?>>Tanzania</option>
            </select>
          </div>
              
          <div class="form-group">
            <label>Market Type</label>
            <select id="market" name="market" disabled>
              <option value="">-- Select Market Type --</option>
              <option value="local" <?= $user['market_scope']=='local'?'selected':'' ?>>Local</option>
              <option value="national" <?= $user['market_scope']=='national'?'selected':'' ?>>National</option>
              <option value="global" <?= $user['market_scope']=='global'?'selected':'' ?>>Global</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>County</label>
            <select name="county" required>
              <option <?= $user['county']=='Kenya'?'selected':'' ?>>Kilifi</option>
              <option <?= $user['county']=='Uganda'?'selected':'' ?>>Mombasa</option>
              <option <?= $user['county']=='Tanzania'?'selected':'' ?>>Bungoma</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Ward</label>
            <select name="ward" required>
              <option <?= $user['ward']=='Kenya'?'selected':'' ?>>Sokoni</option>
              <option <?= $user['ward']=='Uganda'?'selected':'' ?>>Kilifi North</option>
              <option <?= $user['ward']=='Tanzania'?'selected':'' ?>>Kilifi South</option>
            </select>
          </div>

          <div class="form-group">
            <label>Bio (max <?= $bioMaxLength ?> characters)</label>
            <textarea id="bioTextarea" name="bio" maxlength="<?= $bioMaxLength ?>" placeholder="Tell something about yourself..." required><?= $safeBio ?></textarea>
            <small id="bioCount"><?= strlen($bio) ?>/<?= $bioMaxLength ?> characters</small>
          </div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <button type="submit">Save Profile</button>

        </form>
      </div>
      <?php endif; ?>
      <?php if ($accountType === 'sales_agent'): ?>
      <div class="container profile-container">

        <?php if (isset($_GET['updated'])): ?>
          <div class="success"><i class="fa-solid fa-check-circle"></i>Profile updated successfully!</div>
        <?php endif; ?>

        <div class="profile-header">
          <div class="profile-pic">
            <img id="profilePreview"
              src="<?= safe($user['profile_image']) ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>">
              <input type="file" id="profileImage" name="profile_image" accept="image/png,image/jpeg,image/webp" form="profileForm">
            <label for="profileImage"><i class="fa fa-camera"></i></label>
          </div>
          <div>
            <h2><?= safe($user['full_name']); ?></h2>
            <p>Edit your Maket Hub details</p>
          </div>
        </div>

        <form id="profileForm" class="profile-form" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= safe($user['full_name']); ?>">
          </div>

          <div class="form-group">
            <label>Username (read-only)</label>
            <input type="text" name="username" value="<?= safe($user['username']); ?>" disabled>
          </div>

          <div class="form-group">
            <label>Email (read-only)</label>
            <input type="email" value="<?= safe($user['email']); ?>" disabled>
          </div>

          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= safe($user['phone']); ?>">
          </div>

          <div class="form-group">
            <label>Country</label>
            <select name="country">
              <option <?= $user['country']=='Kenya'?'selected':'' ?>>Kenya</option>
              <option <?= $user['country']=='Uganda'?'selected':'' ?>>Uganda</option>
              <option <?= $user['country']=='Tanzania'?'selected':'' ?>>Tanzania</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>County</label>
            <select name="county">
              <option <?= $user['county']=='Kenya'?'selected':'' ?>>Kilifi</option>
              <option <?= $user['county']=='Uganda'?'selected':'' ?>>Mombasa</option>
              <option <?= $user['county']=='Tanzania'?'selected':'' ?>>Bungoma</option>
            </select>
          </div>

          <div class="form-group">
            <label>Bio (max <?= $bioMaxLength ?> characters)</label>
            <textarea id="bioTextarea" name="bio" maxlength="<?= $bioMaxLength ?>" 
                      placeholder="Tell something about yourself..."><?= $safeBio ?></textarea>
            <small id="bioCount"><?= strlen($bio) ?>/<?= $bioMaxLength ?> characters</small>
          </div>

          <div class="form-group">
            <label>Physical Address</label>
            <input type="text" name="address" value="<?= safe($user['address']); ?>">
          </div>
          
          <div class="form-group">
            <label>Ward</label>
            <select name="ward">
              <option <?= $user['ward']=='Kenya'?'selected':'' ?>>Sokoni</option>
              <option <?= $user['ward']=='Uganda'?'selected':'' ?>>Kilifi North</option>
              <option <?= $user['ward']=='Tanzania'?'selected':'' ?>>Kilifi South</option>
            </select>
          </div>
          <div></div>
          <div></div>

          <button type="submit">Save Profile</button>
        </form>
      </div>
      <?php endif; ?>
      <?php if ($accountType === 'property_owner'): ?>
      <div class="container profile-container">

        <?php if (isset($_GET['updated'])): ?>
          <div class="success"><i class="fa-solid fa-check-circle"></i>Profile updated successfully!</div>
        <?php endif; ?>

        <div class="profile-header">
          <div class="profile-pic">
            <img id="profilePreview"
              src="<?= safe($user['profile_image']) ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>">
              <input type="file" id="profileImage" name="profile_image" accept="image/png,image/jpeg,image/webp" form="profileForm">
            <label for="profileImage"><i class="fa fa-camera"></i></label>
          </div>
          <div>
            <h2><?= safe($user['full_name']); ?></h2>
            <p>Edit your Maket Hub details</p>
          </div>
        </div>

        <form id="profileForm" class="profile-form" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= safe($user['full_name']); ?>">
          </div>

          <div class="form-group">
            <label>Email (read-only)</label>
            <input type="email" value="<?= safe($user['email']); ?>" disabled>
          </div>


          <div class="form-group">
            <label>Country</label>
            <select name="country">
              <option <?= $user['country']=='Kenya'?'selected':'' ?>>Kenya</option>
              <option <?= $user['country']=='Uganda'?'selected':'' ?>>Uganda</option>
              <option <?= $user['country']=='Tanzania'?'selected':'' ?>>Tanzania</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>County</label>
            <select name="county">
              <option <?= $user['county']=='Kenya'?'selected':'' ?>>Kilifi</option>
              <option <?= $user['county']=='Uganda'?'selected':'' ?>>Mombasa</option>
              <option <?= $user['county']=='Tanzania'?'selected':'' ?>>Bungoma</option>
            </select>
          </div>

          <div class="form-group">
            <label>username</label>
            <input type="text" name="username" value="<?= safe($user['username']); ?>">
          </div>

          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= safe($user['phone']); ?>">
          </div>

          <div class="form-group">
            <label>Bio (max <?= $bioMaxLength ?> characters)</label>
            <textarea id="bioTextarea" name="bio" maxlength="<?= $bioMaxLength ?>" 
                      placeholder="Tell something about yourself..."><?= $safeBio ?></textarea>
            <small id="bioCount"><?= strlen($bio) ?>/<?= $bioMaxLength ?> characters</small>
          </div>

          <div class="form-group">
            <label>Physical Address</label>
            <input type="text" name="address" value="<?= safe($user['address']); ?>">
          </div>
          
          <div class="form-group">
            <label>Ward</label>
            <select name="ward">
              <option <?= $user['ward']=='Kenya'?'selected':'' ?>>Sokoni</option>
              <option <?= $user['ward']=='Uganda'?'selected':'' ?>>Kilifi North</option>
              <option <?= $user['ward']=='Tanzania'?'selected':'' ?>>Kilifi South</option>
            </select>
          </div>
          <div></div>
          <div></div>

          <button type="submit">Save Profile</button>
        </form>
      </div>
      <?php endif; ?>
    </main>
    <footer>
      <p>&copy; 2025/2026, Maket Hub.shop, All Rights reserved.</p>
    </footer>
  </div>

<script>
document.getElementById("profileImage").addEventListener("change", function () {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => document.getElementById("profilePreview").src = e.target.result;
  reader.readAsDataURL(file);
});
</script>

<script>
const bioTextarea = document.getElementById("bioTextarea");
const bioCount = document.getElementById("bioCount");

bioTextarea.addEventListener("input", () => {
    const len = bioTextarea.value.length;
    bioCount.textContent = `${len}/<?= $bioMaxLength ?> characters`;
});
</script>


</body>
</html>
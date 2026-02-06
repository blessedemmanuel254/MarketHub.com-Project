<?php
session_start();
require_once 'connection.php';

/* ---------- AUTH ---------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

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
    $account_type = trim($_POST['account_type']);

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
            address = ?, account_type = ?, profile_image = ?, updated_at = NOW()
        WHERE user_id = ?
    ");

    $update->bind_param(
        "ssssssssssi",
        $full_name, $phone, $bio, $username, $country, $county, $ward,
        $address, $account_type, $profile_image, $user_id
    );
    $update->execute();
    $update->close();

    header("Location: buyerprofile.php?updated=1");
    exit();
}

/* ---------- BIO ---------- */
$bioMaxLength = 150;
$bio = !empty($user['bio']) ? substr($user['bio'], 0, $bioMaxLength) : '';
$safeBio = safe($bio);

/* ---------- SAFE IMAGE ---------- */
$profileImg = 'Images/Market Hub Logo.avif';
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
<title>My Profile | Market Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="styles/general.css">

<style>
* { box-sizing: border-box; font-family: "Segoe UI", sans-serif; }
body { margin:0; padding:20px; }

.profile-container {
  max-width: 900px;
  margin: auto;
  background: #fff;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
}

.profile-pic {
  position: relative;
  width: 120px;
  height: 120px;
}

.profile-pic img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ddd;
}

.profile-pic input { display:none; }

.profile-pic label {
  position:absolute;
  bottom:0;
  right:0;
  background:#0f0f0f;
  color:#fff;
  padding:8px;
  border-radius:50%;
  cursor:pointer;
}

.profile-form {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:20px;
}

.form-group { display:flex; flex-direction:column; }
.form-group label { font-size:14px; margin-bottom:6px; color:#555; }

.form-group input,
.form-group select,
.form-group textarea {
  padding:10px;
  border:1px solid #ccc;
  border-radius:4px;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline:none;
  border-color:#0f0f0f;
}

.save-btn {
  margin-top:30px;
  text-align:right;
}

.save-btn button {
  background:#0f0f0f;
  color:#fff;
  border:none;
  padding:12px 24px;
  border-radius:4px;
  cursor:pointer;
}

.success {
  background:#e6fffa;
  color:#065f46;
  padding:10px;
  border-radius:4px;
  margin-bottom:15px;
}
</style>
</head>
<body>
  <div class="container">

    <div class="container profile-container">

    <?php if (isset($_GET['updated'])): ?>
      <div class="success">Profile updated successfully ✅</div>
    <?php endif; ?>

    <div class="profile-header">
      <div class="profile-pic">
        <img id="profilePreview"
          src="<?= safe($user['profile_image']) ?: 'Images/Market Hub Logo.avif'; ?>">
          <input type="file" id="profileImage" name="profile_image" accept="image/png,image/jpeg,image/webp" form="profileForm">
        <label for="profileImage"><i class="fa fa-camera"></i></label>
      </div>
      <div>
        <h2><?= safe($user['full_name']); ?></h2>
        <p>Edit your Market Hub details</p>
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
        <label>Phone</label>
        <input type="text" name="phone" value="<?= safe($user['phone']); ?>">
      </div>

      <div class="form-group">
        <label>Bio (max <?= $bioMaxLength ?> characters)</label>
        <textarea id="bioTextarea" name="bio" maxlength="<?= $bioMaxLength ?>" 
                  placeholder="Tell something about yourself..."><?= $safeBio ?></textarea>
        <small id="bioCount"><?= strlen($bio) ?>/<?= $bioMaxLength ?> characters</small>
      </div>

      <!-- <div class="form-group">
        <label>Bio</label>
        <textarea name="bio" placeholder="Tell people about yourself..."><?= safe($user['bio']); ?></textarea>
      </div> -->

      <div class="form-group">
        <label>username</label>
        <input type="text" name="username" value="<?= safe($user['username']); ?>">
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
        <input type="text" name="county" value="<?= safe($user['county']); ?>">
      </div>

      <div class="form-group">
        <label>Ward</label>
        <input type="text" name="ward" value="<?= safe($user['ward']); ?>">
      </div>

      <div class="form-group">
        <label>Physical Address</label>
        <input type="text" name="address" value="<?= safe($user['address']); ?>">
      </div>

      <div class="form-group">
        <label>Market Hub Role</label>
        <select name="account_type">
          <option value="buyer" <?= $user['account_type']=='buyer'?'selected':'' ?>>Buyer</option>
          <option value="seller" <?= $user['account_type']=='seller'?'selected':'' ?>>Seller</option>
          <option value="service_provider" <?= $user['account_type']=='service_provider'?'selected':'' ?>>Service Provider</option>
        </select>
      </div>

      <div class="save-btn">
        <button type="submit">Save Profile</button>
      </div>

    </form>
    </div>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
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
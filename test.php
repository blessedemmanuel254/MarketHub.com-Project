<?php
session_start();
// require_once 'connection.php'; // Uncomment when saving to DB

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ---------- SANITIZE INPUT ----------
    $name  = trim($_POST['name'] ?? '');
    $cat   = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($name === '')  $errors[] = "Product name is required.";
    if ($cat === '')   $errors[] = "Category is required.";
    if ($price <= 0)   $errors[] = "Price must be greater than zero.";
    if ($stock < 0)    $errors[] = "Stock cannot be negative.";

    // ---------- IMAGE VALIDATION ----------
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
        $errors[] = "Product image is required.";
    } else {

        $file = $_FILES['photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG or WEBP images are allowed.";
        }

        if ($file['size'] > 1024 * 1024) {
            $errors[] = "Image size must not exceed 1MB.";
        }

        [$width, $height] = getimagesize($file['tmp_name']);

        if ($width < 400 || $height < 400) {
            $errors[] = "Image is too small. Minimum size is 400 × 400 pixels.";
        }

        if ($width > 1200 || $height > 1200) {
            $errors[] = "Image is too large. Maximum size is 1200 × 1200 pixels.";
        }
    }

    // ---------- SAVE ----------
    if (empty($errors)) {

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid("product_", true) . "." . $ext;
        $uploadDir = "uploads/products/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file($file['tmp_name'], $uploadDir . $newName);

        // ---- DATABASE INSERT (WHEN READY) ----
        /*
        $stmt = $conn->prepare(
          "INSERT INTO products (name, category, price, stock, image)
           VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssdiss", $name, $cat, $price, $stock, $newName);
        $stmt->execute();
        */

        $success = "Product added successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product | Seller</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{
  font-family:Poppins,sans-serif;
  background:#f4f6f8;
}
.container{
  max-width:520px;
  margin:40px auto;
  background:#fff;
  padding:25px;
  border-radius:14px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
}
h1{
  margin-top:0;
}
label{
  font-size:14px;
  margin-top:15px;
  display:block;
}
input, select{
  width:100%;
  padding:12px;
  margin-top:6px;
  border-radius:10px;
  border:1px solid #ddd;
}
button{
  margin-top:20px;
  width:100%;
  padding:14px;
  background:#000;
  color:#fff;
  border:none;
  border-radius:12px;
  font-size:15px;
  cursor:pointer;
}
.error{
  background:#fee2e2;
  color:#991b1b;
  padding:12px;
  border-radius:10px;
  margin-bottom:15px;
}
.success{
  background:#dcfce7;
  color:#166534;
  padding:12px;
  border-radius:10px;
  margin-bottom:15px;
}
.note{
  font-size:12px;
  color:#666;
  margin-top:6px;
}
</style>
</head>

<body>

<div class="container">
<h1>Add Product</h1>

<?php if ($errors): ?>
<div class="error">
  <?php foreach ($errors as $e) echo "• $e<br>"; ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

  <label>Product Name</label>
  <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" placeholder="Passion Juice">

  <label>Category</label>
  <select name="category">
    <option value="">Select category</option>
    <option <?= ($cat ?? '')=="Food & Snacks"?'selected':'' ?>>Food & Snacks</option>
    <option>Drinks</option>
    <option>Electronics</option>
  </select>

  <label>Price (KES)</label>
  <input type="number" name="price" step="0.01" placeholder="40">

  <label>Stock Quantity</label>
  <input type="number" name="stock" placeholder="24">

  <label>Product Image</label>
  <input type="file" name="photo" accept="image/*">
  <div class="note">
    JPG / PNG / WEBP • 400×400 – 1200×1200 px • Max 1MB
  </div>

  <button type="submit">
    <i class="fa fa-plus"></i> Add Product
  </button>

</form>
</div>

</body>
</html>
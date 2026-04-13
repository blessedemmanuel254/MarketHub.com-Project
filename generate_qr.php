<?php
$sellerId = (int)($_GET['seller'] ?? 0);

if ($sellerId <= 0) {
    die("Invalid seller");
}

$url = "https://makethub.shop/marketdisplay.php?seller=" . $sellerId;

$qr = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Seller QR Poster</title>
</head>
<body style="text-align:center; font-family:Arial;">

  <h1>Makethub</h1>
  <h2>Scan to Shop</h2>

  <img src="<?php echo $qr; ?>" alt="QR Code">

  <p>Scan this code to view our products instantly</p>

  <button onclick="window.print()">Print</button>

</body>
</html>
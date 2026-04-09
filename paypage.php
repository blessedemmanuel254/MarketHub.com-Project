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

  <!-- jQuery + DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  
  <title>Manual Payment | Maket Hub</title>
<style>

  .payPageContainer {
    background: #ffffff;
    color: #0f0f0f;
    padding: 20px;
    border-radius: 12px;
    width: clamp(300px, 100%, 400px);
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin: 10px auto;
    border: 1px solid #dedede;
  }

  h1 {
    margin-bottom: 10px;
  }

  .amount {
    font-size: 24px;
    color: #088000;
    margin: 10px 0;
    font-weight: bold;
  }

  .details {
    background: #dedede;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    font-size: 14px;
  }

  .container .payPageContainer .details strong {
    font-size: 18px;
  }


  .mpesa {
    background: #088000;
    color: white;
    padding: 10px;
    border-radius: 4px;
    font-weight: bold;
    margin: 10px 0;
  }

  input {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 4px;
    border: 1px solid #dedede;
    outline: none;
  }

  button {
    width: 100%;
    padding: 8px 16px;
    margin-top: 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
  }

  .pay-btn {
    background: #088000;
    color: white;
  }

  .whatsapp-btn {
    background: #055000;
    color: white;
    display: none;
  }

  .warning {
    color: #ff3b3b;
    font-size: 13px;
    margin-top: 10px;
  }
</style>
</head>
<body>
  <div class="container">
    <h1 class="paypage-H1">AGENT BADGE PAYMENT</h1>

    <div class="payPageContainer">
      <h1>Maket Hub</h1>
      <p>Agent Badge Payment</p>

      <div class="mpesa">Lipa na M-PESA</div>

      <div class="amount">KES 200</div>

      <div class="details">
        <p>Buy Goods and Services</p>
        <p><strong>Till Number:</strong></p>
        <p><strong>5442656</strong></p>
        <span>(BETRADES TALES)</span>
      </div>

      <input type="text" id="name" placeholder="Enter your name">

      <button class="pay-btn" onclick="confirmPayment()">I Have Paid</button>

      <button class="whatsapp-btn" id="whatsappBtn" onclick="goWhatsApp()">Send Screenshot via WhatsApp</button>

      <div class="warning">After payment, click the button above and send your screenshot.</div>
    </div>
    <footer>
      <p>&copy; 2025/2026, Maket Hub.shop, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="assets/js/general.js" type="text/javascript" defer></script>
  <script>
  function confirmPayment() {
    const name = document.getElementById('name').value.trim();

    if (name === '') {
      alert('Please enter your name first');
      return;
    }

    document.getElementById('whatsappBtn').style.display = 'block';
  }

  function goWhatsApp() {
    const name = document.getElementById('name').value.trim();
    const message = `Hello, I have paid KES 200 for Maket Hub badge. Name: ${name}`;

    const phone = '254773029440';
    const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

    window.open(url, '_blank');
  }

  </script>

</body>
</html>

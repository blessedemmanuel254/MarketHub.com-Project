<?php
session_start();

/* --- SAMPLE DATA (REPLACE WITH DB VALUES) --- */
$wallet = [
    'available' => 12450,
    'pending' => 3200,
    'lifetime' => 189600,
    'last_withdrawal' => 'KES 5,000 on 10 Feb 2026'
];

$orders = [
    ['#ORD1023', 'Phone Charger', 'KES 1,200', 'Pending'],
    ['#ORD1024', 'Bluetooth Speaker', 'KES 4,500', 'Shipped'],
    ['#ORD1025', 'Laptop Stand', 'KES 2,800', 'Delivered'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seller Dashboard | Market Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{
    margin:0;
    font-family:Poppins, sans-serif;
    background:#f5f6f8;
}
.dashboard{
    display:grid;
    grid-template-columns:260px 1fr;
    min-height:100vh;
}
.sidebar{
    background:#000;
    color:#fff;
    padding:20px;
}
.sidebar h2{
    margin-bottom:30px;
}
.sidebar a{
    display:block;
    color:#fff;
    text-decoration:none;
    padding:12px;
    border-radius:6px;
    margin-bottom:8px;
}
.sidebar a:hover{
    background:#222;
}
.main{
    padding:25px;
}
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 5px 20px rgba(0,0,0,.05);
}
.card h4{
    margin:0;
    color:#666;
}
.card h2{
    margin:10px 0 0;
}
.withdraw-box{
    margin-top:20px;
    background:#fff;
    padding:20px;
    border-radius:10px;
}
.withdraw-box input, select, button{
    width:100%;
    padding:10px;
    margin-top:10px;
}
button{
    background:#000;
    color:#fff;
    border:none;
    cursor:pointer;
}
button:disabled{
    background:#999;
}
.table-box{
    margin-top:30px;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:14px;
    text-align:left;
}
th{
    background:#f0f0f0;
}
.status{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
}
.Pending{ background:#fde68a; }
.Shipped{ background:#93c5fd; }
.Delivered{ background:#86efac; }
.quick-actions{
    display:flex;
    gap:10px;
    margin-top:20px;
}
.quick-actions a{
    flex:1;
    background:#000;
    color:#fff;
    text-align:center;
    padding:12px;
    border-radius:8px;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Market Hub</h2>
        <a href="#"><i class="fa fa-home"></i> Dashboard</a>
        <a href="#"><i class="fa fa-box"></i> Products</a>
        <a href="#"><i class="fa fa-shopping-cart"></i> Orders</a>
        <a href="#"><i class="fa fa-wallet"></i> Wallet</a>
        <a href="#"><i class="fa fa-star"></i> Reviews</a>
        <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">

        <!-- WALLET OVERVIEW -->
        <div class="cards">
            <div class="card">
                <h4>Available Balance</h4>
                <h2>KES <?= number_format($wallet['available']) ?></h2>
            </div>
            <div class="card">
                <h4>Pending Balance</h4>
                <h2>KES <?= number_format($wallet['pending']) ?></h2>
            </div>
            <div class="card">
                <h4>Lifetime Earnings</h4>
                <h2>KES <?= number_format($wallet['lifetime']) ?></h2>
            </div>
            <div class="card">
                <h4>Last Withdrawal</h4>
                <h2><?= $wallet['last_withdrawal'] ?></h2>
            </div>
        </div>

        <!-- WITHDRAW -->
        <div class="withdraw-box">
            <h3>Withdraw Funds</h3>
            <form>
                <select>
                    <option>M-Pesa</option>
                    <option>Bank Transfer</option>
                </select>
                <input type="number" placeholder="Amount (KES)">
                <button <?= $wallet['available'] < 1000 ? 'disabled' : '' ?>>Withdraw</button>
                <small>Minimum withdrawal: KES 1,000</small>
            </form>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="quick-actions">
            <a href="#">Add Product</a>
            <a href="#">View Orders</a>
            <a href="#">Withdraw</a>
        </div>

        <!-- RECENT ORDERS -->
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($orders as $o): ?>
                    <tr>
                        <td><?= $o[0] ?></td>
                        <td><?= $o[1] ?></td>
                        <td><?= $o[2] ?></td>
                        <td><span class="status <?= $o[3] ?>"><?= $o[3] ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>

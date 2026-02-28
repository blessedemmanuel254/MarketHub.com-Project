<?php
/* session_start();
require_once 'connection.php'; */

/* ---------- SESSION SECURITY ---------- *//* 
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}  */

/* Optional: regenerate session ID periodically *//* 
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
 */
/* ---------- ROLE ACCESS CONTROL ---------- *//* 
$allowedRole = 'administrator';

$roleStmt = $conn->prepare(
    "SELECT account_type FROM users WHERE user_id = ? LIMIT 1"
);
$roleStmt->bind_param("i", $_SESSION['user_id']);
$roleStmt->execute();
$roleStmt->bind_result($accountType);
$roleStmt->fetch();
$roleStmt->close();

if ($accountType !== $allowedRole) { */
    // Optional: destroy session for safety
    // session_destroy();/* 

    /* header("Location: index.php");
    exit();
} */
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

  <link rel="stylesheet" href="styles/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <!-- jQuery + DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  
  <title>ADMIN Page | Market Hub</title>
  <style>
    /* Pagination buttons */
    .dataTables_wrapper .dataTables_paginate .paginate_button{
      background-color: #898888;
    }

    /* Hover effect */
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: #898888da;
    }

    /* Active page */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: #088000 !important;
    }

    /* Info text below table */
    .dataTables_wrapper .dataTables_info {
      color: #ffffff;
    }
  </style>
</head>
<body id="adminBody">
  <div class="containerAdmin">
    <section>
      <h1>ADMIN&nbsp;PANEL<br><span>Market&nbsp;Hub</span></h1>
      <div class="admin-rhs">
        <div class="notfy-wrapper">
          <i class="fa-solid fa-bell"></i>
          <span class="notfy-count">0</span>
        </div>
        <div class="admin-profile">
          <img src="Images/Market Hub Logo.avif" width="40" alt="Market Hub Logo">
          <p>EMMANUEL&nbsp;WERANGAI <br><em>Administrator</em></p>
        </div>
      </div>
    </section>

    <div class="navOverlay" onclick="toggleNavigationBar()" id="navOverlay"></div>
    <div id="navigation-button" onclick="toggleNavigationBar()">
      <img src="Images/Admin Menu.png" width="45" alt="Admin Navigation">
    </div>
    <div class="navigation-bar">
      <h4>Admin&nbsp;Navigation<i class="fa-solid fa-xmark" onclick="toggleNavigationBar()"></i></h4>
      <nav>
        <a href="#" class="nav-link active" data-tab="dashboard">
          <i class="fa-solid fa-gauge"></i>Dashboard
        </a>
        <a href="agentPage.php" class="nav-link"><i class="fa-solid fa-users"></i>Sales Agents</a>
        <a href="sellerPage.php" class="nav-link"><i class="fa-solid fa-store"></i>Sellers</a>
        <a href="buyerPage.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i>Buyers</a>
        <a href="propertyOwnerPage.php" class="nav-link"><i class="fa-solid fa-building"></i>Property Owners</a>
        <a href="#" class="nav-link">
          <i class="fa-solid fa-money-bill-transfer"></i>Withdrawals
        </a>
        <a href="#" class="nav-link" data-tab="transactions">
          <i class="fa-solid fa-money-bill-transfer"></i>Transactions
        </a>
        <a href="settingsPage.php" class="nav-link"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="logout.php" class="nav-link"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
      </nav>

    </div>
    <main class="adminMain">
      <div class="admin-tab-panel" data-tab="dashboard">
        <nav>
          <p>Buyers</p>
          <ul>
            <a href="#">Admin ~ </a>
            <a href="#" class="active">Buyers</a><!-- 
            <a href="">Orders</a>
            <a href="">Users</a> -->
          </ul>
        </nav>
        <h2>Buyers Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-users"></i>
              <div>
                <h3>Total Buyers</h3>
                <div class="value">5,687</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-user-check"></i>
              <div>
                <h3>Active Buyers</h3>

                <div class="value">5,123</div>

                <small>↑ 12% productivity growth this month</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-cart-shopping"></i>
              <div>
                <h3>Total Orders</h3>
                <div class="value">7,890</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <h3>Total Spend</h3>
                <div class="value">Ksh 7.3M</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Active">Active</option>
              <option value="pending">Pending</option>
              <option value="Suspended">Suspended</option>
            </select>
          </div>
          <table id="transactionsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Buyer</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Region</th>
                <th>Orders</th>
                <th>Total&nbsp;Spend</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On:</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=12" style="border-radius:50%">Blessed Emmanuel
                  </div>
                </td>
                <td>emmanueltindi23@gmail.com</td>
                <td>+254759578630</td>
                <td>Coast</td>
                <td>35</td>
                <td>KES 33,489</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-15</td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Paid">
                <td>2.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=13" style="border-radius:50%">Mary Wanjiku
                  </div>
                </td>
                <td>marywanjiku@gmail.com</td>
                <td>+254701223344</td>
                <td>Nairobi</td>
                <td>28</td>
                <td>KES 21,340</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-18</td>
                <td>2025-01-20</td>
              </tr>

              <tr data-status="Paid">
                <td>3.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=14" style="border-radius:50%">Daniel Otieno
                  </div>
                </td>
                <td>danotieno@gmail.com</td>
                <td>+254712998771</td>
                <td>Western</td>
                <td>41</td>
                <td>KES 52,880</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-19</td>
                <td>2025-01-23</td>
              </tr>

              <tr data-status="Paid">
                <td>4.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=15" style="border-radius:50%">Fatma Ali
                  </div>
                </td>
                <td>fatmaali@yahoo.com</td>
                <td>+254734556889</td>
                <td>Coast</td>
                <td>19</td>
                <td>KES 14,560</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-20</td>
                <td>2025-01-25</td>
              </tr>

              <tr data-status="Paid">
                <td>5.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=16" style="border-radius:50%">James Mwangi
                  </div>
                </td>
                <td>jamesmwangi@gmail.com</td>
                <td>+254722334455</td>
                <td>Rift Valley</td>
                <td>50</td>
                <td>KES 61,200</td>
                <td><span class="badge suspended">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-22</td>
                <td>2025-01-28</td>
              </tr>
              <tr data-status="Paid">
                <td>6.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=17" style="border-radius:50%">Agnes Nduta
                  </div>
                </td>
                <td>agnesnduta@gmail.com</td>
                <td>+254711445566</td>
                <td>Central</td>
                <td>22</td>
                <td>KES 18,950</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-24</td>
                <td>2025-01-30</td>
              </tr>

              <tr data-status="Paid">
                <td>7.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=18" style="border-radius:50%">Brian Kiplagat
                  </div>
                </td>
                <td>briankip@gmail.com</td>
                <td>+254711556677</td>
                <td>North Eastern</td>
                <td>54</td>
                <td>KES 65,900</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-26</td>
                <td>2025-02-02</td>
              </tr>

              <tr data-status="Paid">
                <td>8.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=19" style="border-radius:50%">Lucy Achieng
                  </div>
                </td>
                <td>lucyachieng@gmail.com</td>
                <td>+254700889911</td>
                <td>Nyanza</td>
                <td>31</td>
                <td>KES 27,340</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-27</td>
                <td>2025-02-04</td>
              </tr>

              <tr data-status="Paid">
                <td>9.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=20" style="border-radius:50%">Kevin Mutua
                  </div>
                </td>
                <td>kevinmutua@gmail.com</td>
                <td>+254733221144</td>
                <td>Eastern</td>
                <td>15</td>
                <td>KES 12,780</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-29</td>
                <td>2025-02-05</td>
              </tr>

              <tr data-status="Paid">
                <td>10.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=21" style="border-radius:50%">Mercy Chebet
                  </div>
                </td>
                <td>mercychebet@gmail.com</td>
                <td>+254721334455</td>
                <td>Rift Valley</td>
                <td>38</td>
                <td>KES 44,120</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-30</td>
                <td>2025-02-06</td>
              </tr>

              <tr data-status="Paid">
                <td>11.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=22" style="border-radius:50%">Samuel Kariuki
                  </div>
                </td>
                <td>samkariuki@gmail.com</td>
                <td>+254710112233</td>
                <td>Central</td>
                <td>26</td>
                <td>KES 23,470</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-01</td>
                <td>2025-02-08</td>
              </tr>

              <tr data-status="Paid">
                <td>12.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=23" style="border-radius:50%">Janet Njeri
                  </div>
                </td>
                <td>janetnjeri@gmail.com</td>
                <td>+254734778899</td>
                <td>Nairobi</td>
                <td>47</td>
                <td>KES 58,340</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-02</td>
                <td>2025-02-09</td>
              </tr>

              <tr data-status="Paid">
                <td>13.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=24" style="border-radius:50%">Hassan Omar
                  </div>
                </td>
                <td>hassanomar@gmail.com</td>
                <td>+254729556677</td>
                <td>North Eastern</td>
                <td>17</td>
                <td>KES 16,890</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-03</td>
                <td>2025-02-10</td>
              </tr>

              <tr data-status="Paid">
                <td>14.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=25" style="border-radius:50%">Esther Atieno
                  </div>
                </td>
                <td>estheratieno@gmail.com</td>
                <td>+254708334455</td>
                <td>Nyanza</td>
                <td>29</td>
                <td>KES 25,640</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-04</td>
                <td>2025-02-11</td>
              </tr>

              <tr data-status="Paid">
                <td>15.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=26" style="border-radius:50%">Peter Mworia
                  </div>
                </td>
                <td>petermworia@gmail.com</td>
                <td>+254720667788</td>
                <td>Eastern</td>
                <td>21</td>
                <td>KES 19,300</td>
                <td><span class="badge suspended">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-05</td>
                <td>2025-02-12</td>
              </tr>

              <tr data-status="Paid">
                <td>16.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=27" style="border-radius:50%">Grace Wairimu
                  </div>
                </td>
                <td>gracewairimu@gmail.com</td>
                <td>+254712334455</td>
                <td>Central</td>
                <td>34</td>
                <td>KES 31,780</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-06</td>
                <td>2025-02-13</td>
              </tr>

              <tr data-status="Paid">
                <td>17.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=28" style="border-radius:50%">David Kimani
                  </div>
                </td>
                <td>davidkimani@gmail.com</td>
                <td>+254723889900</td>
                <td>Rift Valley</td>
                <td>44</td>
                <td>KES 49,560</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-07</td>
                <td>2025-02-14</td>
              </tr>

              <tr data-status="Paid">
                <td>18.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=29" style="border-radius:50%">Irene Jepkoech
                  </div>
                </td>
                <td>irenejep@gmail.com</td>
                <td>+254709112233</td>
                <td>Rift Valley</td>
                <td>23</td>
                <td>KES 20,880</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-08</td>
                <td>2025-02-15</td>
              </tr>

              <tr data-status="Paid">
                <td>19.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=30" style="border-radius:50%">Anthony Ochieng
                  </div>
                </td>
                <td>anthonyoch@gmail.com</td>
                <td>+254731556677</td>
                <td>Nyanza</td>
                <td>27</td>
                <td>KES 24,110</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-09</td>
                <td>2025-02-16</td>
              </tr>

              <tr data-status="Paid">
                <td>20.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=31" style="border-radius:50%">Cynthia Auma
                  </div>
                </td>
                <td>cynthumaa@gmail.com</td>
                <td>+254722998877</td>
                <td>Western</td>
                <td>32</td>
                <td>KES 29,430</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-10</td>
                <td>2025-02-17</td>
              </tr>

              <tr data-status="Paid">
                <td>21.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=32" style="border-radius:50%">Victor Mutiso
                  </div>
                </td>
                <td>victormutiso@gmail.com</td>
                <td>+254700334455</td>
                <td>Eastern</td>
                <td>18</td>
                <td>KES 17,290</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-11</td>
                <td>2025-02-18</td>
              </tr>

              <tr data-status="Paid">
                <td>22.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=33" style="border-radius:50%">Naomi Wambui
                  </div>
                </td>
                <td>naomiwambui@gmail.com</td>
                <td>+254719556677</td>
                <td>Nairobi</td>
                <td>40</td>
                <td>KES 53,770</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-12</td>
                <td>2025-02-19</td>
              </tr>

              <tr data-status="Paid">
                <td>23.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=34" style="border-radius:50%">Mohamed Abdi
                  </div>
                </td>
                <td>mohamedabdi@gmail.com</td>
                <td>+254713223344</td>
                <td>North Eastern</td>
                <td>24</td>
                <td>KES 22,150</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-13</td>
                <td>2025-02-20</td>
              </tr>

              <tr data-status="Paid">
                <td>24.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=35" style="border-radius:50%">Rebecca Akinyi
                  </div>
                </td>
                <td>rebeccaakinyi@gmail.com</td>
                <td>+254725667788</td>
                <td>Nyanza</td>
                <td>30</td>
                <td>KES 28,990</td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-14</td>
                <td>2025-02-21</td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>


    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  <script src="Scripts/general.js" type="text/javascript" defer></script>
  <script>
    // DataTables Script Js
    $(document).ready(function () {
      $('#transactionsTable').DataTable({
        pagingType: "simple_numbers", // only numbers + prev/next
        pageLength: 15,               // rows per page
        lengthChange: false,          // hide "Show X entries"
        searching: true,              // keep search box
        ordering: true,               // column sorting
        stateSave: true,              // ✅ remembers pagination, search & sort
        language: {
          paginate: {
            previous: "PREV",
            next: "NEXT"
          }
        }
      });
    });
  </script>
</body>
</html>
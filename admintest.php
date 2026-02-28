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
      <div class="admin-tab-panel active" data-tab="dashboard">
        <nav>
          <p>Sales Agents</p>
          <ul>
            <a href="">Admin ~ </a> 
            <a href="" class="active">Sales Agents</a><!-- 
            <a href="">Orders</a>
            <a href="">Users</a> -->
          </ul>
        </nav>
        <h2>Sales Agents Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-users"></i>
              <div>
                <h3>Total Agents</h3>
                <div class="value">188</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-user-check"></i>
              <div>
                <h3>Active Agents</h3>

                <div class="value">88</div>

                <small>↑ 12% productivity growth this month</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <h3>Total Commissions</h3>
                <div class="value">KES 1.3M</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-chart-simple"></i>
              <div>
                <h3>Total Referrals</h3>
                <div class="value">587</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Verified">Verified</option>
              <option value="Unverified">Unverified</option>
              <option value="Suspended">Suspended</option>
            </select>
            <select id="statusFilter">
              <option value="all">🌍&nbsp;Region</option>
              <option value="Nairobi">Nairobi</option>
              <option value="Coast">Coast</option>
              <option value="Western">Western</option>
            </select>
          </div>
          <table id="transactionsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Agent</th>
                <th>Phone</th>
                <th>Sub&nbsp;Agents</th>
                <th>Wallet</th>
                <th>Region</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>Blessed Emmanuel</td>
                <td>+254759578630</td>
                <td>67</td>
                <td>KES 45,000</td>
                <td>Coast</td>
                <td><span class="badge verified">Verified</span></td>
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
                <td>Samuel Kiptoo</td>
                <td>+254711223344</td>
                <td>72</td>
                <td>KES 88,400</td>
                <td>Rift Valley</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-16</td>
                <td>2025-01-18</td>
              </tr>

              <tr data-status="Paid">
                <td>3.</td>
                <td>Grace Njeri</td>
                <td>+254700123456</td>
                <td>29</td>
                <td>KES 21,600</td>
                <td>Central</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-17</td>
                <td>2025-01-20</td>
              </tr>

              <tr data-status="Paid">
                <td>4.</td>
                <td>Daniel Ochieng</td>
                <td>+254712667788</td>
                <td>40</td>
                <td>KES 39,500</td>
                <td>Nyanza</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-18</td>
                <td>2025-01-22</td>
              </tr>

              <tr data-status="Paid">
                <td>5.</td>
                <td>Faith Mutua</td>
                <td>+254723334455</td>
                <td>18</td>
                <td>KES 14,800</td>
                <td>Eastern</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-19</td>
                <td>2025-01-23</td>
              </tr>

              <tr data-status="Paid">
                <td>6.</td>
                <td>Kevin Odhiambo</td>
                <td>+254734556677</td>
                <td>63</td>
                <td>KES 72,100</td>
                <td>Western</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-20</td>
                <td>2025-01-24</td>
              </tr>

              <tr data-status="Paid">
                <td>7.</td>
                <td>Mercy Atieno</td>
                <td>+254700987654</td>
                <td>26</td>
                <td>KES 19,450</td>
                <td>Nairobi</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-21</td>
                <td>2025-01-25</td>
              </tr>

              <tr data-status="Paid">
                <td>8.</td>
                <td>Brian Kiplagat</td>
                <td>+254711556677</td>
                <td>54</td>
                <td>KES 65,900</td>
                <td>North Eastern</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-22</td>
                <td>2025-01-26</td>
              </tr>
              <tr data-status="Paid">
                <td>9.</td>
                <td>Lucy Wairimu</td>
                <td>+254722445566</td>
                <td>31</td>
                <td>KES 25,000</td>
                <td>Central</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-23</td>
                <td>2025-01-27</td>
              </tr>

              <tr data-status="Paid">
                <td>10.</td>
                <td>Isaac Kamau</td>
                <td>+254733112244</td>
                <td>47</td>
                <td>KES 44,200</td>
                <td>Eastern</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-24</td>
                <td>2025-01-28</td>
              </tr>

              <tr data-status="Paid">
                <td>11.</td>
                <td>Janet Chebet</td>
                <td>+254712889900</td>
                <td>39</td>
                <td>KES 36,700</td>
                <td>Rift Valley</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-25</td>
                <td>2025-01-29</td>
              </tr>

              <tr data-status="Paid">
                <td>12.</td>
                <td>Anthony Kariuki</td>
                <td>+254701998877</td>
                <td>22</td>
                <td>KES 17,300</td>
                <td>Nairobi</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-01-26</td>
                <td>2025-01-30</td>
              </tr>

              <tr data-status="Paid">
                <td>13.</td>
                <td>Rose Anyango</td>
                <td>+254734223344</td>
                <td>48</td>
                <td>KES 52,400</td>
                <td>Nyanza</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-01</td>
                <td>2025-02-05</td>
              </tr>

              <tr data-status="Paid">
                <td>14.</td>
                <td>Victor Maina</td>
                <td>+254722334411</td>
                <td>37</td>
                <td>KES 29,900</td>
                <td>Western</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-02</td>
                <td>2025-02-06</td>
              </tr>

              <tr data-status="Paid">
                <td>15.</td>
                <td>Agnes Nduta</td>
                <td>+254711445566</td>
                <td>33</td>
                <td>KES 24,300</td>
                <td>Coast</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-03</td>
                <td>2025-02-07</td>
              </tr>
              <tr data-status="Paid">
                <td>16.</td>
                <td>George Otieno</td>
                <td>+254733556600</td>
                <td>59</td>
                <td>KES 74,800</td>
                <td>Nyanza</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-04</td>
                <td>2025-02-08</td>
              </tr>

              <tr data-status="Paid">
                <td>17.</td>
                <td>Peter Mwangi</td>
                <td>+254722998877</td>
                <td>45</td>
                <td>KES 48,500</td>
                <td>Central</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-05</td>
                <td>2025-02-09</td>
              </tr>

              <tr data-status="Paid">
                <td>18.</td>
                <td>Esther Wanjiku</td>
                <td>+254711223300</td>
                <td>27</td>
                <td>KES 22,800</td>
                <td>Eastern</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-06</td>
                <td>2025-02-10</td>
              </tr>

              <tr data-status="Paid">
                <td>19.</td>
                <td>Michael Korir</td>
                <td>+254734112233</td>
                <td>61</td>
                <td>KES 80,000</td>
                <td>Rift Valley</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-07</td>
                <td>2025-02-11</td>
              </tr>

              <tr data-status="Paid">
                <td>20.</td>
                <td>Joyce Akinyi</td>
                <td>+254723009988</td>
                <td>30</td>
                <td>KES 28,600</td>
                <td>Nairobi</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-08</td>
                <td>2025-02-12</td>
              </tr>

              <tr data-status="Paid">
                <td>21.</td>
                <td>Collins Mutiso</td>
                <td>+254712334455</td>
                <td>34</td>
                <td>KES 32,000</td>
                <td>Eastern</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-09</td>
                <td>2025-02-13</td>
              </tr>

              <tr data-status="Paid">
                <td>22.</td>
                <td>Mary Chepkemoi</td>
                <td>+254701223344</td>
                <td>24</td>
                <td>KES 18,900</td>
                <td>Rift Valley</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-10</td>
                <td>2025-02-14</td>
              </tr>

              <tr data-status="Paid">
                <td>23.</td>
                <td>Patrick Wekesa</td>
                <td>+254722110099</td>
                <td>50</td>
                <td>KES 60,200</td>
                <td>Western</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-11</td>
                <td>2025-02-15</td>
              </tr>

              <tr data-status="Paid">
                <td>24.</td>
                <td>Hannah Mwikali</td>
                <td>+254733221144</td>
                <td>28</td>
                <td>KES 26,400</td>
                <td>Coast</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-12</td>
                <td>2025-02-16</td>
              </tr>

              <tr data-status="Paid">
                <td>25.</td>
                <td>Eric Njuguna</td>
                <td>+254711778899</td>
                <td>42</td>
                <td>KES 41,300</td>
                <td>Central</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-13</td>
                <td>2025-02-17</td>
              </tr>

              <tr data-status="Paid">
                <td>26.</td>
                <td>Naomi Wambui</td>
                <td>+254722556677</td>
                <td>36</td>
                <td>KES 34,900</td>
                <td>Nairobi</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions"><div>
                  <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                  <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                  <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div></td>
                <td>2025-02-14</td>
                <td>2025-02-18</td>
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
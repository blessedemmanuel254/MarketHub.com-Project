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
          <p>Seller</p>
          <ul>
            <a href="">Admin ~ </a> 
            <a href="" class="active">Seller</a><!-- 
            <a href="">Orders</a>
            <a href="">Users</a> -->
          </ul>
        </nav>
        <h2>Seller Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-users"></i>
              <div>
                <h3>Total Sellers</h3>
                <div class="value">385</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid">🛡</i>
              <div>
                <h3>Verified Sellers</h3>

                <div class="value">126</div>

                <small>↑ 12% productivity growth this month</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <h3>Total Seller Wallets</h3>
                <div class="value">KES 245,000</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-hand-holding-dollar"></i>
              <div>
                <h3>Pending Withdrawals</h3>
                <div class="value">KES 2,450,000</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Active">Active</option>
              <option value="Pending">Pending</option>
              <option value="Suspended">Suspended</option>
            </select>
            <select id="statusFilter">
              <option value="all">🛡&nbsp;KYC</option>
              <option value="Verified">Verified</option>
              <option value="Unverified">Unverified</option>
            </select>
            <select id="statusFilter">
              <option value="all">📦&nbsp;Has&nbsp;Products</option>
              <option value="Yes">Yes</option>
              <option value="No">No</option>
            </select>
          </div>
          <table id="transactionsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Seller</th>
                <th>Products</th>
                <th>Wallet</th>
                <th>KYC</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>GreenFarm Ltd</td>
                <td>45</td>
                <td>KES 45,000</td>
                <td><span class="badge verified">Verified</span></td>
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
                <td>Sunrise Traders</td>
                <td>38</td>
                <td>KES 38,000</td>
                <td><span class="badge unverified">Unverified</span></td>
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
                <td>2025-01-18</td>
              </tr>

              <tr data-status="Pending">
                <td>3.</td>
                <td>Lakeview Supplies</td>
                <td>22</td>
                <td>KES 22,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-20</td>
                <td>2025-01-20</td>
              </tr>

              <tr data-status="Suspended">
                <td>4.</td>
                <td>Elite Distributors</td>
                <td>15</td>
                <td>KES 15,000</td>
                <td><span class="badge unverified">Unverified</span></td>
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
                <td>2025-01-22</td>
              </tr>

              <tr data-status="Paid">
                <td>5.</td>
                <td>Prime Agro</td>
                <td>60</td>
                <td>KES 60,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-25</td>
                <td>2025-01-25</td>
              </tr>

              <tr data-status="Pending">
                <td>6.</td>
                <td>BlueSky Ventures</td>
                <td>18</td>
                <td>KES 18,000</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-01-28</td>
                <td>2025-01-28</td>
              </tr>

              <tr data-status="Paid">
                <td>7.</td>
                <td>Highland Wholesalers</td>
                <td>75</td>
                <td>KES 75,000</td>
                <td><span class="badge verified">Verified</span></td>
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
                <td>2025-02-01</td>
              </tr>

              <tr data-status="Suspended">
                <td>8.</td>
                <td>Urban Mart</td>
                <td>12</td>
                <td>KES 12,000</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td><span class="badge suspended">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-03</td>
                <td>2025-02-03</td>
              </tr>

              <tr data-status="Paid">
                <td>9.</td>
                <td>Harvest Hub</td>
                <td>41</td>
                <td>KES 41,000</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-05</td>
                <td>2025-02-05</td>
              </tr>

              <tr data-status="Pending">
                <td>10.</td>
                <td>Metro Supplies</td>
                <td>29</td>
                <td>KES 29,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-08</td>
                <td>2025-02-08</td>
              </tr>

              <tr data-status="Paid">
                <td>11.</td>
                <td>Golden Fields</td>
                <td>53</td>
                <td>KES 53,000</td>
                <td><span class="badge unverified">Unverified</span></td>
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
                <td>2025-02-10</td>
              </tr>

              <!-- 12–21 -->

              <tr data-status="Paid">
                <td>12.</td>
                <td>FreshLine Ltd</td>
                <td>34</td>
                <td>KES 34,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-12</td>
                <td>2025-02-12</td>
              </tr>

              <tr data-status="Pending">
                <td>13.</td>
                <td>AgroLink</td>
                <td>27</td>
                <td>KES 27,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-14</td>
                <td>2025-02-14</td>
              </tr>

              <tr data-status="Paid">
                <td>14.</td>
                <td>Capital Grocers</td>
                <td>49</td>
                <td>KES 49,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-16</td>
                <td>2025-02-16</td>
              </tr>

              <tr data-status="Suspended">
                <td>15.</td>
                <td>FarmPro Kenya</td>
                <td>19</td>
                <td>KES 19,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge suspended">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-18</td>
                <td>2025-02-18</td>
              </tr>

              <tr data-status="Paid">
                <td>16.</td>
                <td>Swift Traders</td>
                <td>44</td>
                <td>KES 44,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-20</td>
                <td>2025-02-20</td>
              </tr>

              <tr data-status="Pending">
                <td>17.</td>
                <td>NorthPoint Ltd</td>
                <td>31</td>
                <td>KES 31,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-22</td>
                <td>2025-02-22</td>
              </tr>

              <tr data-status="Paid">
                <td>18.</td>
                <td>EcoFarm Supplies</td>
                <td>58</td>
                <td>KES 58,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-24</td>
                <td>2025-02-24</td>
              </tr>

              <tr data-status="Suspended">
                <td>19.</td>
                <td>MarketLine Africa</td>
                <td>21</td>
                <td>KES 21,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge suspended">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-26</td>
                <td>2025-02-26</td>
              </tr>

              <tr data-status="Paid">
                <td>20.</td>
                <td>AgriPlus Ltd</td>
                <td>67</td>
                <td>KES 67,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-02-28</td>
                <td>2025-02-28</td>
              </tr>

              <tr data-status="Pending">
                <td>21.</td>
                <td>Vertex Traders</td>
                <td>26</td>
                <td>KES 26,000</td>
                <td><span class="badge verified">Verified</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td>2025-03-01</td>
                <td>2025-03-01</td>
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
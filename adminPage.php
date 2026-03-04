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
        <a href="#" class="nav-link" data-tab="salesagents"><i class="fa-solid fa-users"></i>Sales Agents</a>
        <a href="#" class="nav-link"  data-tab="sellers"><i class="fa-solid fa-store"></i>Sellers</a>
        <a href="#" class="nav-link" data-tab="buyers"><i class="fa-solid fa-cart-shopping"></i>Buyers</a>
        <a href="#" class="nav-link" data-tab="propertyowners"><i class="fa-solid fa-building"></i>Property Owners</a>
        <a href="#" class="nav-link" data-tab="withdrawals">
          <i class="fa-solid fa-money-bill-transfer"></i>Withdrawals
        </a>
        <a href="#" class="nav-link" data-tab="transactions">
          <i class="fa-solid fa-money-bill-transfer"></i>Transactions
        </a>
        <a href="settingsPage.php" class="nav-link"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="logout.php" class="nav-link-admin-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
      </nav>

    </div>
    <main class="adminMain">
      <div class="admin-tab-panel active" data-tab="dashboard">
        <nav>
          <p>Dashboard</p>
          <ul>
            <a href="#">Home ~ </a> 
            <a href="#" class="active">Dashboard</a>
          </ul>
        </nav>
        <h2>Super Admin Dashboard</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card">
              <h3>Users</h3>
              <div class="value">1,387</div>
              <ul class="list">
                <li><span>Sellers</span><strong>385</strong></li>
                <li><span>Buyers</span><strong>860</strong></li>
                <li><span>Property Owners</span><strong>142</strong></li>
              </ul>
            </div>

            <div class="card">
              <h3>Sales Agents</h3>

              <div class="value">126</div>

              <ul class="list">
                <li>
                  <span>Active Agents</span>
                  <strong>98</strong>
                </li>
                <li>
                  <span>Top Performers</span>
                  <strong>21</strong>
                </li>
                <li>
                  <span>Under Review</span>
                  <strong>7</strong>
                </li>
              </ul>
              <small>↑ 12% productivity growth this month</small>
            </div>

            <div class="card">
              <h3>Platform Commission</h3>
              <div class="value">KES 245,000</div>
              <div class="sub">Avg commission rate: 10%</div>
              <ul class="list">
                <li><span>E-commerce</span><strong>KES 165,000</strong></li>
                <li><span>Property Rentals</span><strong>KES 80,000</strong></li>
              </ul>
            </div>

            <div class="card">
              <h3>Gross Transaction Volume (GMV)</h3>
              <div class="value">KES 2,450,000</div>
              <div class="sub">All platform transactions (monthly)</div>
              <div class="progress"><div class="bar" style="width:82%"></div></div>
              <small>↑ 18% growth vs last month</small>
            </div>

            <div class="card">
              <h3>Net Profit</h3>
              <div class="value profit">KES 176,500</div>
              <div class="sub">Commission − Operating Costs</div>
              <div class="progress"><div class="bar" style="width:71%"></div></div>
              <small>Healthy margin (72%)</small>
            </div>

            <div class="card">
              <h3>Operational Costs</h3>
              <div class="value loss">KES -68,500</div>
              <div class="sub">Monthly expenses</div>
              <ul class="list">
                <li><span>Hosting & Servers</span><strong>18,000</strong></li>
                <li><span>Payments (MPESA)</span><strong>22,500</strong></li>
                <li><span>Staff & Ops</span><strong>28,000</strong></li>
              </ul>
            </div>

            <div class="card">
              <h3>Platform Health</h3>
              <div class="value">Stable</div>
              <ul class="list">
                <li><span>API</span><strong>Online</strong></li>
                <li><span>MPESA</span><strong>Connected</strong></li>
                <li><span>Disputes</span><strong>3 Active</strong></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- TRANSACTIONS -->
        <div class="table-wrapper">
          <h3>Transactions History</h3>
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">All Transactions</option>
              <option value="Paid">Completed</option>
              <option value="Shipped">Pending</option>
              <option value="Pending">Processing</option>
            </select>
          </div>
          <table id="ordersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Transaction ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Commission</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>#TX20491</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Pending">
                <td>2.</td>
                <td>#TX20492</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-16</td>
              </tr>
              <tr data-status="Paid">
                <td>3.</td>
                <td>#TX20493</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-17</td>
              </tr>
              <tr data-status="Pending">
                <td>4.</td>
                <td>#TX20494</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-18</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="admin-tab-panel" data-tab="salesagents">
        <nav>
          <p>Sales Agents</p>
          <ul>
            <a href="#">Admin ~ </a> 
            <a href="#" class="active">Sales Agents</a>
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
          <table id="salesagentsTable">
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
                <th>Talk</th>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-15</td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Paid">
                <td>2.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=13" style="border-radius:50%">Kevin Otieno
                  </div>
                </td>
                <td>+254712345601</td>
                <td>52</td>
                <td>KES 38,000</td>
                <td>Nairobi</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-18</td>
                <td>2025-01-20</td>
              </tr>

              <tr data-status="Paid">
                <td>3.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=14" style="border-radius:50%">Mercy Wanjiku
                  </div>
                </td>
                <td>+254798765432</td>
                <td>40</td>
                <td>KES 29,000</td>
                <td>Central</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-21</td>
                <td>2025-01-22</td>
              </tr>

              <tr data-status="Paid">
                <td>4.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=15" style="border-radius:50%">David Mwangi
                  </div>
                </td>
                <td>+254701223344</td>
                <td>73</td>
                <td>KES 64,500</td>
                <td>Western</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-25</td>
                <td>2025-01-26</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=16" style="border-radius:50%">Annette Chebet
                  </div>
                </td>
                <td>+254722334455</td>
                <td>61</td>
                <td>KES 41,000</td>
                <td>Rift Valley</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-27</td>
                <td>2025-01-29</td>
              </tr>

              <tr data-status="Paid">
                <td>6.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=17" style="border-radius:50%">Samuel Kiptoo
                  </div>
                </td>
                <td>+254711998877</td>
                <td>35</td>
                <td>KES 22,500</td>
                <td>Eastern</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-01</td>
                <td>2025-02-02</td>
              </tr>

              <tr data-status="Paid">
                <td>7.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=18" style="border-radius:50%">Brenda Achieng
                  </div>
                </td>
                <td>+254733445566</td>
                <td>49</td>
                <td>KES 31,200</td>
                <td>Nyanza</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-03</td>
                <td>2025-02-05</td>
              </tr>

              <tr data-status="Paid">
                <td>8.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=19" style="border-radius:50%">James Kariuki
                  </div>
                </td>
                <td>+254744556677</td>
                <td>58</td>
                <td>KES 47,900</td>
                <td>Coast</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-06</td>
                <td>2025-02-07</td>
              </tr>

              <tr data-status="Paid">
                <td>9.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=20" style="border-radius:50%">Faith Nyambura
                  </div>
                </td>
                <td>+254755667788</td>
                <td>44</td>
                <td>KES 36,000</td>
                <td>Nairobi</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-08</td>
                <td>2025-02-09</td>
              </tr>
              <tr data-status="Paid">
                <td>10.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=21" style="border-radius:50%">Peter Odhiambo
                  </div>
                </td>
                <td>+254766778899</td>
                <td>39</td>
                <td>KES 27,400</td>
                <td>Western</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-10</td>
                <td>2025-02-11</td>
              </tr>

              <tr data-status="Paid">
                <td>11.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=22" style="border-radius:50%">Lilian Atieno
                  </div>
                </td>
                <td>+254700112233</td>
                <td>68</td>
                <td>KES 53,600</td>
                <td>Nyanza</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-12</td>
                <td>2025-02-14</td>
              </tr>

              <tr data-status="Paid">
                <td>12.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=23" style="border-radius:50%">Charles Mutiso
                  </div>
                </td>
                <td>+254722556677</td>
                <td>47</td>
                <td>KES 34,800</td>
                <td>Eastern</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-15</td>
                <td>2025-02-16</td>
              </tr>

              <tr data-status="Paid">
                <td>13.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=24" style="border-radius:50%">Grace Wambui
                  </div>
                </td>
                <td>+254733998800</td>
                <td>55</td>
                <td>KES 42,300</td>
                <td>Central</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-17</td>
                <td>2025-02-18</td>
              </tr>

              <tr data-status="Paid">
                <td>14.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=25" style="border-radius:50%">Daniel Njoroge
                  </div>
                </td>
                <td>+254744001122</td>
                <td>62</td>
                <td>KES 48,900</td>
                <td>Nairobi</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-19</td>
                <td>2025-02-20</td>
              </tr>

              <tr data-status="Paid">
                <td>15.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=26" style="border-radius:50%">Judith Akinyi
                  </div>
                </td>
                <td>+254755112244</td>
                <td>36</td>
                <td>KES 23,700</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-21</td>
                <td>2025-02-22</td>
              </tr>

              <tr data-status="Paid">
                <td>16.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=27" style="border-radius:50%">Michael Karanja
                  </div>
                </td>
                <td>+254766334455</td>
                <td>50</td>
                <td>KES 39,100</td>
                <td>Rift Valley</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-23</td>
                <td>2025-02-24</td>
              </tr>
              <tr data-status="Paid">
                <td>17.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=28" style="border-radius:50%">Esther Jepkorir
                  </div>
                </td>
                <td>+254700223344</td>
                <td>42</td>
                <td>KES 30,500</td>
                <td>Central</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-25</td>
                <td>2025-02-26</td>
              </tr>

              <tr data-status="Paid">
                <td>18.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=29" style="border-radius:50%">Anthony Musyoka
                  </div>
                </td>
                <td>+254711334455</td>
                <td>37</td>
                <td>KES 26,800</td>
                <td>Eastern</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-27</td>
                <td>2025-02-28</td>
              </tr>

              <tr data-status="Paid">
                <td>19.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=30" style="border-radius:50%">Caroline Muthoni
                  </div>
                </td>
                <td>+254722445566</td>
                <td>64</td>
                <td>KES 50,200</td>
                <td>Nairobi</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-01</td>
                <td>2025-03-02</td>
              </tr>

              <tr data-status="Paid">
                <td>20.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=31" style="border-radius:50%">Francis Mutua
                  </div>
                </td>
                <td>+254733556677</td>
                <td>33</td>
                <td>KES 21,400</td>
                <td>Western</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-03</td>
                <td>2025-03-04</td>
              </tr>

              <tr data-status="Paid">
                <td>21.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=32" style="border-radius:50%">Joyce Auma
                  </div>
                </td>
                <td>+254744667788</td>
                <td>48</td>
                <td>KES 35,900</td>
                <td>Nyanza</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-05</td>
                <td>2025-03-06</td>
              </tr>

              <tr data-status="Paid">
                <td>22.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=33" style="border-radius:50%">Patrick Ndegwa
                  </div>
                </td>
                <td>+254755778899</td>
                <td>59</td>
                <td>KES 44,300</td>
                <td>Rift Valley</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-07</td>
                <td>2025-03-08</td>
              </tr>

              <tr data-status="Paid">
                <td>23.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=34" style="border-radius:50%">Cynthia Wekesa
                  </div>
                </td>
                <td>+254766889900</td>
                <td>46</td>
                <td>KES 32,700</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-09</td>
                <td>2025-03-10</td>
              </tr>

              <tr data-status="Paid">
                <td>24.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=35" style="border-radius:50%">Brian Omondi
                  </div>
                </td>
                <td>+254777990011</td>
                <td>51</td>
                <td>KES 37,500</td>
                <td>Nairobi</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-11</td>
                <td>2025-03-12</td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
      
      <div class="admin-tab-panel" data-tab="sellers">
        <nav>
          <p>Sellers</p>
          <ul>
            <a href="#">Admin ~ </a> 
            <a href="#" class="active">Sellers</a>
          </ul>
        </nav>
        <h2>Sellers Management</h2>
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
                <div class="value">KES 4.7M</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-hand-holding-dollar"></i>
              <div>
                <h3>Pending Withdrawals</h3>
                <div class="value">4</div>
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
          <table id="sellersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Seller</th>
                <th>Products</th>
                <th>Wallet</th>
                <th>KYC</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Talk</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On:</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=12" style="border-radius:50%">GreenFarm Ltd
                  </div>
                </td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-15</td>
                <td>2025-01-15</td>
              </tr>

              <tr data-status="Paid">
                <td>2.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=5" style="border-radius:50%">SilverTech Solutions
                  </div>
                </td>
                <td>12</td>
                <td>KES 12,800</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-01</td>
                <td>2025-02-04</td>
              </tr>

              <tr data-status="Paid">
                <td>3.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=33" style="border-radius:50%">BlueWave Traders
                  </div>
                </td>
                <td>32</td>
                <td>KES 30,500</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-22</td>
                <td>2025-01-25</td>
              </tr>

              <tr data-status="Paid">
                <td>4.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=18" style="border-radius:50%">AgroLink Ventures
                  </div>
                </td>
                <td>19</td>
                <td>KES 18,900</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-02</td>
                <td>2025-03-02</td>
              </tr>

              <tr data-status="Paid">
                <td>5.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=7" style="border-radius:50%">Nova Energy Ltd
                  </div>
                </td>
                <td>56</td>
                <td>KES 67,000</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td><span class="badge suspended">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2024-12-20</td>
                <td>2025-01-01</td>
              </tr>

              <tr data-status="Paid">
                <td>6.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=21" style="border-radius:50%">UrbanBrite Holdings
                  </div>
                </td>
                <td>11</td>
                <td>KES 10,200</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-05</td>
                <td>2025-03-06</td>
              </tr>

              <tr data-status="Paid">
                <td>7.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=30" style="border-radius:50%">Sunrise Dairy Co.
                  </div>
                </td>
                <td>27</td>
                <td>KES 25,000</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-18</td>
                <td>2025-02-18</td>
              </tr>

              <tr data-status="Paid">
                <td>8.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=2" style="border-radius:50%">Prime Logistics KE
                  </div>
                </td>
                <td>61</td>
                <td>KES 78,000</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2024-11-28</td>
                <td>2024-12-02</td>
              </tr>

              <tr data-status="Paid">
                <td>9.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=15" style="border-radius:50%">TechHive Africa
                  </div>
                </td>
                <td>14</td>
                <td>KES 14,400</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-08</td>
                <td>2025-02-08</td>
              </tr>

              <tr data-status="Paid">
                <td>10.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=11" style="border-radius:50%">MountPeak Hardware
                  </div>
                </td>
                <td>33</td>
                <td>KES 32,100</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-27</td>
                <td>2025-01-29</td>
              </tr>

              <tr data-status="Paid">
                <td>11.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=19" style="border-radius:50%">FreshDrop Organics
                  </div>
                </td>
                <td>21</td>
                <td>KES 20,700</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-01</td>
                <td>2025-03-01</td>
              </tr>

              <tr data-status="Paid">
                <td>12.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=23" style="border-radius:50%">RiftValley Cables
                  </div>
                </td>
                <td>72</td>
                <td>KES 91,000</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2024-10-15</td>
                <td>2024-10-20</td>
              </tr>

              <tr data-status="Paid">
                <td>13.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=9" style="border-radius:50%">QuickMed Pharma
                  </div>
                </td>
                <td>17</td>
                <td>KES 17,900</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-03</td>
                <td>2025-03-03</td>
              </tr>

              <tr data-status="Paid">
                <td>14.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=27" style="border-radius:50%">Stellar Foods KE
                  </div>
                </td>
                <td>38</td>
                <td>KES 37,600</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-14</td>
                <td>2025-02-15</td>
              </tr>

              <tr data-status="Paid">
                <td>15.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=14" style="border-radius:50%">EcoPlast Manufacturing
                  </div>
                </td>
                <td>26</td>
                <td>KES 26,500</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td><span class="badge active">Active</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-06</td>
                <td>2025-03-06</td>
              </tr>

              <tr data-status="Paid">
                <td>16.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=31" style="border-radius:50%">Highland Transport Co.
                  </div>
                </td>
                <td>52</td>
                <td>KES 63,000</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2024-12-05</td>
                <td>2024-12-09</td>
              </tr>

              <tr data-status="Paid">
                <td>17.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=6" style="border-radius:50%">OceanFresh Fisheries
                  </div>
                </td>
                <td>15</td>
                <td>KES 16,200</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-22</td>
                <td>2025-02-22</td>
              </tr>

              <tr data-status="Paid">
                <td>18.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=4" style="border-radius:50%">BrightStar Electronics
                  </div>
                </td>
                <td>29</td>
                <td>KES 29,900</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-13</td>
                <td>2025-01-14</td>
              </tr>

              <tr data-status="Paid">
                <td>19.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=29" style="border-radius:50%">Safeline Security Ltd
                  </div>
                </td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-11</td>
                <td>2025-02-12</td>
              </tr>

              <tr data-status="Paid">
                <td>20.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=8" style="border-radius:50%">MetroBuild Contractors
                  </div>
                </td>
                <td>48</td>
                <td>KES 52,700</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2024-11-20</td>
                <td>2024-11-21</td>
              </tr>

              <tr data-status="Paid">
                <td>21.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=37" style="border-radius:50%">ValleyFresh Grocers
                  </div>
                </td>
                <td>22</td>
                <td>KES 23,000</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-06</td>
                <td>2025-02-06</td>
              </tr>

              <tr data-status="Paid">
                <td>22.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=17" style="border-radius:50%">DigitalRise Media
                  </div>
                </td>
                <td>18</td>
                <td>KES 19,400</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-04</td>
                <td>2025-03-05</td>
              </tr>

              <tr data-status="Paid">
                <td>23.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=3" style="border-radius:50%">Alpha Timber Ltd
                  </div>
                </td>
                <td>58</td>
                <td>KES 70,000</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2024-10-30</td>
                <td>2024-10-30</td>
              </tr>

              <tr data-status="Paid">
                <td>24.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=25" style="border-radius:50%">Zenith Auto Parts
                  </div>
                </td>
                <td>41</td>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-03-07</td>
                <td>2025-03-07</td>
              </tr>              
              

            </tbody>
          </table>
        </div>
      </div>

      <!-- Buyers Management Table -->
      <div class="admin-tab-panel" data-tab="buyers">
        <nav>
          <p>Buyers</p>
          <ul>
            <a href="#">Admin ~ </a>
            <a href="#" class="active">Buyers</a>
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
          <table id="buyersTable">
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
                <th>Talk</th>
                <th>Created&nbsp;On:</th>
                <th>Updated&nbsp;On:</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=35" style="border-radius:50%">Emmanuel Werangai
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
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
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-14</td>
                <td>2025-02-21</td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
      <!-- Property Owners Management Table -->
      <div class="admin-tab-panel" data-tab="propertyowners">
        <nav>
          <p>Property Owners</p>
          <ul>
            <a href="#">Admin&nbsp;~</a>
            <a href="#" class="active">Property Owners</a>
          </ul>
        </nav>
        <h2>Property Owners Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-users"></i>
              <div>
                <h3>Total Owners</h3>
                <div class="value">267</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-house"></i>
              <div>
                <h3>Total Properties</h3>

                <div class="value">593</div>

                <small>↑ 12% productivity growth this month</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-money-bill-wave"></i>
              <div>
                <h3>Total Portfolio Value</h3>
                <div class="value">KES 49.3M</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-percent"></i>
              <div>
                <h3>Average Occupancy</h3>
                <div class="value">83%</div>
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
          </div>
          <table id="propertyownersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Owner</th>
                <th>Contact</th>
                <th>Properties</th>
                <th>Occupancy</th>
                <th>Verification</th>
                <th>Actions</th>
                <th>Talk</th>
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
                  <em>ID: 40757679</em>
                </td>
                <td><p class="contactOwer">emmanueltindi23@gmail.com <br>+254759578630</p></td>
                <td>9</td>
                <td>95%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-15</td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Unverified">
                <td>2.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=13" style="border-radius:50%">John Mwangi
                  </div>
                  <em>ID: 40757680</em>
                </td>
                <td><p class="contactOwer">johnmwangi@gmail.com <br>+254711000001</p></td>
                <td>5</td>
                <td>82%</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-16</td>
                <td>2025-01-16</td>
              </tr>

              <tr data-status="Verified">
                <td>3.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=14" style="border-radius:50%">Mary Wanjiku
                  </div>
                  <em>ID: 40757681</em>
                </td>
                <td><p class="contactOwer">marywanjiku@gmail.com <br>+254711000002</p></td>
                <td>12</td>
                <td>97%</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-17</td>
                <td>2025-01-17</td>
              </tr>

              <tr data-status="Unverified">
                <td>4.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=15" style="border-radius:50%">Kevin Otieno
                  </div>
                  <em>ID: 40757682</em>
                </td>
                <td><p class="contactOwer">kevinotieno@gmail.com <br>+254711000003</p></td>
                <td>3</td>
                <td>75%</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-18</td>
                <td>2025-01-18</td>
              </tr>

              <tr data-status="Verified">
                <td>5.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=16" style="border-radius:50%">Faith Njeri
                  </div>
                  <em>ID: 40757683</em>
                </td>
                <td><p class="contactOwer">faithnjeri@gmail.com <br>+254711000004</p></td>
                <td>18</td>
                <td>99%</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-19</td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Unverified">
                <td>6.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=17" style="border-radius:50%">Brian Kiptoo
                  </div>
                  <em>ID: 40757684</em>
                </td>
                <td><p class="contactOwer">briankiptoo@gmail.com <br>+254711000005</p></td>
                <td>4</td>
                <td>68%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-20</td>
                <td>2025-01-20</td>
              </tr>

              <tr data-status="Verified">
                <td>7.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=18" style="border-radius:50%">Lucy Achieng
                  </div>
                  <em>ID: 40757685</em>
                </td>
                <td><p class="contactOwer">lucyachieng@gmail.com <br>+254711000006</p></td>
                <td>14</td>
                <td>96%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-21</td>
                <td>2025-01-21</td>
              </tr>

              <tr data-status="Unverified">
                <td>8.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=19" style="border-radius:50%">Daniel Kariuki
                  </div>
                  <em>ID: 40757686</em>
                </td>
                <td><p class="contactOwer">danielkariuki@gmail.com <br>+254711000007</p></td>
                <td>2</td>
                <td>61%</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-22</td>
                <td>2025-01-22</td>
              </tr>

              <tr data-status="Verified">
                <td>9.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=20" style="border-radius:50%">Susan Mutua
                  </div>
                  <em>ID: 40757687</em>
                </td>
                <td><p class="contactOwer">susanmutua@gmail.com <br>+254711000008</p></td>
                <td>20</td>
                <td>98%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-23</td>
                <td>2025-01-23</td>
              </tr>

              <tr data-status="Unverified">
                <td>10.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=21" style="border-radius:50%">Peter Ndegwa
                  </div>
                  <em>ID: 40757688</em>
                </td>
                <td><p class="contactOwer">peterndegwa@gmail.com <br>+254711000009</p></td>
                <td>6</td>
                <td>73%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-24</td>
                <td>2025-01-24</td>
              </tr>

              <tr data-status="Verified">
                <td>11.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=22" style="border-radius:50%">Janet Kiplagat
                  </div>
                  <em>ID: 40757689</em>
                </td>
                <td><p class="contactOwer">janetk@gmail.com <br>+254711000010</p></td>
                <td>16</td>
                <td>94%</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-25</td>
                <td>2025-01-25</td>
              </tr>

              <tr data-status="Unverified">
                <td>12.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=23" style="border-radius:50%">Samuel Ouma
                  </div>
                  <em>ID: 40757690</em>
                </td>
                <td><p class="contactOwer">samuelouma@gmail.com <br>+254711000011</p></td>
                <td>1</td>
                <td>55%</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-26</td>
                <td>2025-01-26</td>
              </tr>
              <tr data-status="Verified">
                <td>13.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=24" style="border-radius:50%">Grace Wambui
                  </div>
                  <em>ID: 40757691</em>
                </td>
                <td><p class="contactOwer">gracewambui@gmail.com <br>+254711000012</p></td>
                <td>22</td>
                <td>99%</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-27</td>
                <td>2025-01-27</td>
              </tr>

              <tr data-status="Unverified">
                <td>14.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=25" style="border-radius:50%">Dennis Barasa
                  </div>
                  <em>ID: 40757692</em>
                </td>
                <td><p class="contactOwer">dennisbarasa@gmail.com <br>+254711000013</p></td>
                <td>3</td>
                <td>70%</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-28</td>
                <td>2025-01-28</td>
              </tr>

              <tr data-status="Verified">
                <td>15.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=26" style="border-radius:50%">Alice Chebet
                  </div>
                  <em>ID: 40757693</em>
                </td>
                <td><p class="contactOwer">alicechebet@gmail.com <br>+254711000014</p></td>
                <td>11</td>
                <td>93%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-29</td>
                <td>2025-01-29</td>
              </tr>

              <tr data-status="Unverified">
                <td>16.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=27" style="border-radius:50%">Michael Kimani
                  </div>
                  <em>ID: 40757694</em>
                </td>
                <td><p class="contactOwer">michaelkimani@gmail.com <br>+254711000015</p></td>
                <td>4</td>
                <td>66%</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-30</td>
                <td>2025-01-30</td>
              </tr>

              <tr data-status="Verified">
                <td>17.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=28" style="border-radius:50%">Esther Waithera
                  </div>
                  <em>ID: 40757695</em>
                </td>
                <td><p class="contactOwer">estherwaithera@gmail.com <br>+254711000016</p></td>
                <td>19</td>
                <td>97%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-31</td>
                <td>2025-01-31</td>
              </tr>

              <tr data-status="Unverified">
                <td>18.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=29" style="border-radius:50%">Paul Onyango
                  </div>
                  <em>ID: 40757696</em>
                </td>
                <td><p class="contactOwer">paulonyango@gmail.com <br>+254711000017</p></td>
                <td>2</td>
                <td>59%</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-01</td>
                <td>2025-02-01</td>
              </tr>

              <tr data-status="Verified">
                <td>19.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=30" style="border-radius:50%">Naomi Cherono
                  </div>
                  <em>ID: 40757697</em>
                </td>
                <td><p class="contactOwer">naomicherono@gmail.com <br>+254711000018</p></td>
                <td>15</td>
                <td>95%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-02</td>
                <td>2025-02-02</td>
              </tr>

              <tr data-status="Unverified">
                <td>20.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=31" style="border-radius:50%">Isaac Muriuki
                  </div>
                  <em>ID: 40757698</em>
                </td>
                <td><p class="contactOwer">isaacmuriuki@gmail.com <br>+254711000019</p></td>
                <td>5</td>
                <td>71%</td>
                <td><span class="badge suspendedSpan">Suspended</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-03</td>
                <td>2025-02-03</td>
              </tr>

              <tr data-status="Verified">
                <td>21.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=32" style="border-radius:50%">Brenda Atieno
                  </div>
                  <em>ID: 40757699</em>
                </td>
                <td><p class="contactOwer">brendaatieno@gmail.com <br>+254711000020</p></td>
                <td>17</td>
                <td>96%</td>
                <td><span class="badge pendingDocs">Pending&nbsp;Docs</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-04</td>
                <td>2025-02-04</td>
              </tr>

              <tr data-status="Unverified">
                <td>22.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=33" style="border-radius:50%">Joseph Karanja
                  </div>
                  <em>ID: 40757700</em>
                </td>
                <td><p class="contactOwer">josephkaranja@gmail.com <br>+254711000021</p></td>
                <td>3</td>
                <td>64%</td>
                <td><span class="badge unverified">Unverified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-05</td>
                <td>2025-02-05</td>
              </tr>

              <tr data-status="Verified">
                <td>23.</td>
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=34" style="border-radius:50%">Ruth Jepkosgei
                  </div>
                  <em>ID: 40757701</em>
                </td>
                <td><p class="contactOwer">ruthj@gmail.com <br>+254711000022</p></td>
                <td>21</td>
                <td>98%</td>
                <td><span class="badge verified">Verified</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-suspend"><i class="fa-solid fa-ban"></i></button>
                    <button class="btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-06</td>
                <td>2025-02-06</td>
              </tr>              

            </tbody>
          </table>
        </div>
      </div>

      <div class="admin-tab-panel" data-tab="transactions">
        <nav>
          <p>Transactions</p>
          <ul>
            <a href="#">Home ~ </a> 
            <a href="#" class="active">Transactions</a>
          </ul>
        </nav>
        <h2>View all tansactions</h2>
        <div class="admin-tab-content">
        </div>
        <!-- TRANSACTIONS -->
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">All Transactions</option>
              <option value="Delivered">Completed</option>
              <option value="Shipped">Pending</option>
              <option value="Processing">Processing</option>
            </select>
          </div>
          <table id="transactionsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Transaction ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Commission</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>1.</td>
                <td>#TX20491</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Pending">
                <td>2.</td>
                <td>#TX20492</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-16</td>
              </tr>
              <tr data-status="Paid">
                <td>3.</td>
                <td>#TX20493</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-17</td>
              </tr>
              <tr data-status="Pending">
                <td>4.</td>
                <td>#TX20494</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-18</td>
              </tr>
              <tr data-status="Paid">
                <td>5.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>6.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>7.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>8.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>9.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>10.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>11.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>12.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>13.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>14.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>15.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>16.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
              <tr data-status="Paid">
                <td>17.</td>
                <td>#TX20495</td>
                <td>Buyer → Seller</td>
                <td>KES 45,000</td>
                <td>KES 4,500</td>
                <td><span class="badge paid">Completed</span></td>
                <td>2025-01-19</td>
              </tr>
              <tr data-status="Pending">
                <td>18.</td>
                <td>#TX20496</td>
                <td>Tenant → Owner</td>
                <td>KES 18,000</td>
                <td>KES 1,800</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>2025-01-20</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="admin-tab-panel" data-tab="withdrawals">
        <nav>
          <p>Withdrawals</p>
          <ul>
            <a href="#">Admin&nbsp;~</a>
            <a href="#" class="active">Withdrawals</a>
          </ul>
        </nav>
        <h2>Withdrawals Management</h2>
        <div class="admin-tab-content">
          <div class="cards">
            <div class="card sub-card">
              <i class="fa-solid fa-hourglass-half"></i>
              <div>
                <h3>Pending Requests</h3>
                <div class="value">17</div>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-check-circle"></i>
              <div>
                <h3>Approved Today</h3>

                <div class="value">3.1M</div>

                <small>↑ 12% productivity growth this month</small>
              </div>
            </div>

            <div class="card sub-card">
              <i class="fa-solid fa-money-bill"></i>
              <div>
                <h3>Total Withdrawn</h3>
                <div class="value">KES 37.1M</div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="filter-bar">
            <select id="statusFilter">
              <option value="all">📌&nbsp;Status</option>
              <option value="Pending">Pending</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
            </select>
            <select id="statusFilter">
              <option value="all">👤&nbsp;Account&nbsp;Type</option>
              <option value="Seller">Seller</option>
              <option value="Agent">Agent</option>
              <option value="Property Owner">Property&nbsp;Owner</option>
            </select>
          </div>
          <table id="withdrawalsTable">
            <thead>
              <tr>
                <th>User</th>
                <th>Type</th>
                <th>Available</th>
                <th>Requested</th>
                <th>Method</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Talk</th>
                <th>Requested&nbsp;At:</th>
                <th>Updated&nbsp;At:</th>
              </tr>
            </thead>
            <tbody>
              <tr data-status="Paid">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=12" style="border-radius:50%">Blessed Emmanuel
                  </div>
                  <em>+254759578630</em>
                </td>
                <td>Agent</td>
                <td>KES 800,000</td>
                <td>KES 33,489</td>
                <td>M-pesa</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</i></button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-15</td>
                <td>2025-01-15</td>
              </tr>
              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=13" style="border-radius:50%">John Mwangi
                  </div>
                  <em>+254711000001</em>
                </td>
                <td>Agent</td>
                <td>KES 120,000</td>
                <td>KES 4,800</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-16</td>
                <td>2025-01-16</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=14" style="border-radius:50%">Mary Wanjiku
                  </div>
                  <em>+254711000002</em>
                </td>
                <td>Property Owner</td>
                <td>KES 560,000</td>
                <td>KES 22,400</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-17</td>
                <td>2025-01-17</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=15" style="border-radius:50%">Kevin Otieno
                  </div>
                  <em>+254711000003</em>
                </td>
                <td>Agent</td>
                <td>KES 75,000</td>
                <td>KES 3,000</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-18</td>
                <td>2025-01-18</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=16" style="border-radius:50%">Faith Njeri
                  </div>
                  <em>+254711000004</em>
                </td>
                <td>Seller</td>
                <td>KES 310,000</td>
                <td>KES 12,400</td>
                <td>M-pesa</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-19</td>
                <td>2025-01-19</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=17" style="border-radius:50%">Brian Kiptoo
                  </div>
                  <em>+254711000005</em>
                </td>
                <td>Property Owner</td>
                <td>KES 980,000</td>
                <td>KES 39,200</td>
                <td>Bank</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-20</td>
                <td>2025-01-20</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=18" style="border-radius:50%">Lucy Achieng
                  </div>
                  <em>+254711000006</em>
                </td>
                <td>Seller</td>
                <td>KES 44,000</td>
                <td>KES 1,760</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-21</td>
                <td>2025-01-21</td>
              </tr>
              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=19" style="border-radius:50%">Daniel Kariuki
                  </div>
                  <em>+254711000007</em>
                </td>
                <td>Property Owner</td>
                <td>KES 250,000</td>
                <td>KES 10,000</td>
                <td>M-pesa</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-22</td>
                <td>2025-01-22</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=20" style="border-radius:50%">Susan Mutua
                  </div>
                  <em>+254711000008</em>
                </td>
                <td>Seller</td>
                <td>KES 680,000</td>
                <td>KES 27,200</td>
                <td>Bank</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-23</td>
                <td>2025-01-23</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=21" style="border-radius:50%">Peter Ndegwa
                  </div>
                  <em>+254711000009</em>
                </td>
                <td>Agent</td>
                <td>KES 90,000</td>
                <td>KES 3,600</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-24</td>
                <td>2025-01-24</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=22" style="border-radius:50%">Janet Kiplagat
                  </div>
                  <em>+254711000010</em>
                </td>
                <td>Property Owner</td>
                <td>KES 1,200,000</td>
                <td>KES 48,000</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-25</td>
                <td>2025-01-25</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=23" style="border-radius:50%">Samuel Ouma
                  </div>
                  <em>+254711000011</em>
                </td>
                <td>Agent</td>
                <td>KES 340,000</td>
                <td>KES 13,600</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-26</td>
                <td>2025-01-26</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=24" style="border-radius:50%">Grace Wambui
                  </div>
                  <em>+254711000012</em>
                </td>
                <td>Seller</td>
                <td>KES 60,000</td>
                <td>KES 2,400</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-27</td>
                <td>2025-01-27</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=25" style="border-radius:50%">Dennis Barasa
                  </div>
                  <em>+254711000013</em>
                </td>
                <td>Agent</td>
                <td>KES 470,000</td>
                <td>KES 18,800</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-28</td>
                <td>2025-01-28</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=26" style="border-radius:50%">Alice Chebet
                  </div>
                  <em>+254711000014</em>
                </td>
                <td>Seller</td>
                <td>KES 150,000</td>
                <td>KES 6,000</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-29</td>
                <td>2025-01-29</td>
              </tr>
              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=27" style="border-radius:50%">Michael Kimani
                  </div>
                  <em>+254711000015</em>
                </td>
                <td>Agent</td>
                <td>KES 520,000</td>
                <td>KES 20,800</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-30</td>
                <td>2025-01-30</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=28" style="border-radius:50%">Esther Waithera
                  </div>
                  <em>+254711000016</em>
                </td>
                <td>Seller</td>
                <td>KES 210,000</td>
                <td>KES 8,400</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-01-31</td>
                <td>2025-01-31</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=29" style="border-radius:50%">Paul Onyango
                  </div>
                  <em>+254711000017</em>
                </td>
                <td>Propery Owner</td>
                <td>KES 65,000</td>
                <td>KES 2,600</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-01</td>
                <td>2025-02-01</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=30" style="border-radius:50%">Naomi Cherono
                  </div>
                  <em>+254711000018</em>
                </td>
                <td>Seller</td>
                <td>KES 890,000</td>
                <td>KES 35,600</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-02</td>
                <td>2025-02-02</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=31" style="border-radius:50%">Isaac Muriuki
                  </div>
                  <em>+254711000019</em>
                </td>
                <td>Agent</td>
                <td>KES 300,000</td>
                <td>KES 12,000</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-03</td>
                <td>2025-02-03</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=32" style="border-radius:50%">Brenda Atieno
                  </div>
                  <em>+254711000020</em>
                </td>
                <td>Propery Owner</td>
                <td>KES 55,000</td>
                <td>KES 2,200</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-04</td>
                <td>2025-02-04</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=33" style="border-radius:50%">Joseph Karanja
                  </div>
                  <em>+254711000021</em>
                </td>
                <td>Agent</td>
                <td>KES 760,000</td>
                <td>KES 30,400</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-05</td>
                <td>2025-02-05</td>
              </tr>

              <tr data-status="Pending">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=34" style="border-radius:50%">Ruth Jepkosgei
                  </div>
                  <em>+254711000022</em>
                </td>
                <td>Seller</td>
                <td>KES 180,000</td>
                <td>KES 7,200</td>
                <td>M-pesa</td>
                <td><span class="badge pending">Pending</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-06</td>
                <td>2025-02-06</td>
              </tr>

              <tr data-status="Rejected">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=35" style="border-radius:50%">Allan Mutiso
                  </div>
                  <em>+254711000023</em>
                </td>
                <td>Property Owner</td>
                <td>KES 95,000</td>
                <td>KES 3,800</td>
                <td>M-pesa</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-07</td>
                <td>2025-02-07</td>
              </tr>

              <tr data-status="Approved">
                <td>
                  <div class="adm-user-profile">
                    <img src="https://i.pravatar.cc/40?img=36" style="border-radius:50%">Lydia Muthoni
                  </div>
                  <em>+254711000024</em>
                </td>
                <td>Seller</td>
                <td>KES 640,000</td>
                <td>KES 25,600</td>
                <td>Bank</td>
                <td><span class="badge approved">Approved</span></td>
                <td class="actions">
                  <div>
                    <button class="btn-view"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-edit">Approve</button>
                    <button class="btn-suspend">Reject&nbsp;<i class="fa-solid fa-ban"></i></button>
                  </div>
                </td>
                <td class="comm-cell">
                  <button class="comm-btn">
                    <i class="fas fa-ellipsis-vertical"></i>
                  </button>

                  <div class="comm-dropdown">
                    <a href="tel:+254712345678"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/254712345678" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="mailto:blessed@email.com"><i class="fas fa-envelope"></i> Email</a>
                    <a href="#"><i class="fas fa-comment-dots"></i> SMS</a>
                  </div>
                </td>
                <td>2025-02-08</td>
                <td>2025-02-08</td>
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
    // DataTables Js
    $(document).ready(function () {

      const dataTableConfig = {
        pagingType: "simple_numbers",
        pageLength: 15,
        lengthChange: false,
        searching: true,
        ordering: true,
        stateSave: true,
        language: {
          paginate: {
            previous: "PREV",
            next: "NEXT"
          }
        }
      };

      const tables = $('#ordersTable, #salesagentsTable, #sellersTable, #buyersTable, #transactionsTable, #withdrawalsTable, #propertyownersTable')
        .DataTable(dataTableConfig);

      // Override ordersTable only
      $('#ordersTable').DataTable().page.len(10).draw(false);

    });
  </script>
  <script>
    /* ================= DROPDOWN LOGIC ================= */
    document.addEventListener("click", function (e) {

      // Close all dropdowns
      document.querySelectorAll(".comm-dropdown").forEach(dd => {
        dd.style.display = "none";
      });

      // Toggle clicked dropdown
      const btn = e.target.closest(".comm-btn");
      if (btn) {
        const cell = btn.closest(".comm-cell");
        const dropdown = cell.querySelector(".comm-dropdown");
        dropdown.style.display = "block";
        e.stopPropagation();
      }
    });
  </script>
</body>
</html>
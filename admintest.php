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
          <p>Property Owners</p>
          <ul>
            <a href="#">Admin&nbsp;~</a>
            <a href="#" class="active">Property Owners</a><!-- 
            <a href="">Orders</a>
            <a href="">Users</a> -->
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
          <table id="transactionsTable">
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
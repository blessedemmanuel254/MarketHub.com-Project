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
<body>
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
          <p>Dashboard</p>
          <ul>
            <a href="">Home ~ </a> 
            <a href="" class="active">Dashboard</a><!-- 
            <a href="">Orders</a>
            <a href="">Users</a> -->
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

            <div class="card sales-agents-card">
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
                <td><span class="badge paid">Completed</span></td>
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
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="admin-tab-panel" data-tab="transactions">
        <nav>
          <p>Transactions</p>
          <ul>
            <a href="">Home ~ </a> 
            <a href="" class="active">Transactions</a><!-- 
            <a href="">Orders</a>
            <a href="">Users</a> -->
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
                <td><span class="badge paid">Completed</span></td>
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
                <td><span class="badge pending">Pending</span></td>
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
                <td><span class="badge pending">Pending</span></td>
                <td>2025-01-20</td>
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
  <script src="Scripts/general.js" type="text/javascript"></script>
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
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
      
        <div class="admin-tab-panel" data-tab="products">
            <nav>
            <p>Products</p>
            <ul>
                <a href="#">Admin ~ </a> 
                <a href="#" class="active">Products</a>
            </ul>
            </nav>
            <h2>Market Hub Products</h2>
            <div class="admin-tab-content">
            <div class="cards">
                <div class="card sub-card">
                    <i class="fa-solid fa-box"></i>
                    <div>
                    <h3>Total Products</h3>
                    <div class="value">245</div>
                    <small>Products in system</small>
                    </div>
                </div>
                <div class="card sub-card">

                    <i class="fa-solid fa-circle-check"></i>
                    <div>
                    <h3>Active Products</h3>
                    <div class="value">5</div>
                    <small>Currently visible</small>
                    </div>
                </div>

                <div class="card sub-card">
                    
                    <i class="fa-solid fa-ban"></i>
                    <div>
                    <h3>Inactive Products</h3>
                    <div class="value">193</div>
                    <small>Disabled or hidden</small>
                    </div>
                </div>

                <div class="card sub-card">
                    <i class="fa-solid fa-coins"></i>
                    <div>
                    <h3>Total Product Value</h3>
                    <div class="value">KES 2.4M</div>
                    </div>
                </div>
            </div>
            </div>
            <div class="tabs-container">
                <div class="tabs">
                <button class="tab-btn-admin active" data-tab="beauty">Beauty</button>
                <button class="tab-btn-admin" data-tab="electronics">Electronics</button>
                <button class="tab-btn-admin" data-tab="fashion">Fashion</button>
                </div>
                <div id="company-products" class="tab-panel-admin">
                    <div class="tab-top">
                        <p>Market Hub Store<br><strong>Your control center for Market Hub products <i class="fa-regular fa-circle-check"></i></strong></p>
                        <button onclick="toggleProductsAdd(true)">
                        <i class="fa fa-plus"></i>&nbsp;<span>Add&nbsp;Product</span>
                        </button>

                    </div>

                    <!-- PRODUCTS GRID -->
                    <div id="beauty" class="products-grid-admin active">
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- 
                        <p class="noproducts-admin-p">No products uploaded yet. Click "Add Product" to start.</p> -->
                    </div>
                    <!-- PRODUCTS GRID -->
                    <div id="electronics" class="products-grid-admin">
                        <div class="product-card">
                            <img src="Images/6 Litre Electric Pressure Cooker.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">6 Litre Electric Pressure Cooker</div>
                            <div class="product-price">KES 5,200.00</div>
                            <p class="product-description">Fast cooking, energy saving, perfect for family meals.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- PRODUCTS GRID -->
                    <div id="fashion" class="products-grid-admin">
                        <div class="product-card">
                            <img src="Images/Ipcone 16-inch standing fan.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">16-inch Standing Fan</div>
                            <div class="product-price">KES 2,350.00</div>
                            <p class="product-description">Powerful airflow with adjustable height.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                        <div class="product-card">
                            <img src="Images/Executive Leather Laptop Bag.png" loading="lazy" decoding="async" alt="Executive Leather Laptop Bag">
                            <div class="product-name">Executive Leather Laptop Bag</div>
                            <div class="product-price">KES 1,300.00</div>
                            <p class="product-description">Premium executive laptop bag. Durable and stylish.</p>
                            <div class="card-actions">
                                <a href="#" class="edit">
                                <i class="fa fa-pen"></i> Edit
                                </a>
                                <form method="POST">
                                <input type="hidden" name="delete_product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="delete">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  <script>

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

        // Initialize all tables
        const tables = $('#ordersTable, #salesagentsTable, #sellersTable, #buyersTable, #transactionsTable, #withdrawalsTable, #propertyownersTable')
        .DataTable(dataTableConfig);

        // Override ordersTable only
        $('#ordersTable').DataTable().page.len(10).draw(false);

        // ===== Custom Status + Region filter for salesagentsTable =====
        var salesAgentsTable = $('#salesagentsTable').DataTable();

        // Custom filter function
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'salesagentsTable') return true; // only apply to salesagentsTable

        var statusFilter = $('#statusFilter').val();
        var regionFilter = $('#regionFilter').val();

        var rowStatus = $(data[6]).text() || data[6]; // Status column (6)
        var rowRegion = $(data[5]).text() || data[5]; // Region column (5)

        if (statusFilter !== 'all' && rowStatus.trim() !== statusFilter) {
            return false;
        }

        if (regionFilter !== 'all' && rowRegion.trim() !== regionFilter) {
            return false;
        }

        return true;
        });

        // Trigger filter on change
        $('#statusFilter, #regionFilter').on('change', function() {
        salesAgentsTable.draw();
        });

    });
    // MARKET HUB PRODUCTS GRID SWITCH JS

    document.addEventListener("DOMContentLoaded", function () {

    const tabButtons = document.querySelectorAll(".tab-btn-admin");
    const productPanels = document.querySelectorAll(".products-grid-admin");

    tabButtons.forEach((button) => {

        button.addEventListener("click", function () {

        const targetTab = this.dataset.tab;

        // Remove active class from buttons
        tabButtons.forEach(btn => btn.classList.remove("active"));

        // Activate clicked button
        this.classList.add("active");

        // Hide all product panels
        productPanels.forEach(panel => {
            panel.classList.remove("active");
        });

        // Show selected panel instantly
        const targetPanel = document.getElementById(targetTab);

        if (targetPanel) {
            targetPanel.classList.add("active");
        }

        });

    });

    });
  </script>

</body>
</html>
<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Total users
$total_users_stmt = $conn->prepare("SELECT COUNT(userId) AS total_users FROM users");
$total_users_stmt->execute();
$total_users_result = $total_users_stmt->get_result();
if ($total_users_result->num_rows > 0) {
    $total_users_row = $total_users_result->fetch_assoc();
    $total_users = $total_users_row['total_users'];
} else {
    $total_users = 0;
};

// Total agents
$total_agents_stmt = $conn->prepare('SELECT COUNT(agentId) AS total_agents FROM agents');
$total_agents_stmt->execute();
$total_agents_result = $total_agents_stmt->get_result();
if ($total_agents_result->num_rows > 0) {
    $total_agents_row = $total_agents_result->fetch_assoc();
    $total_agents = $total_agents_row['total_agents'];
} else {
    $total_agents = 0;
};

// Total customers
$total_customers_stmt = $conn->prepare('SELECT COUNT(customerId) AS total_customers FROM customers');
$total_customers_stmt->execute();
$total_customers_result = $total_customers_stmt->get_result();
if ($total_customers_result->num_rows > 0) {
    $total_customers_row = $total_customers_result->fetch_assoc();
    $total_customers = $total_customers_row['total_customers'];
} else {
    $total_customers = 0;
};

// Total suppliers
$total_suppliers_stmt = $conn->prepare('SELECT COUNT(supplierId) AS total_suppliers FROM suppliers');
$total_suppliers_stmt->execute();
$total_suppliers_result = $total_suppliers_stmt->get_result();
if ($total_suppliers_result->num_rows > 0) {
    $total_suppliers_row = $total_suppliers_result->fetch_assoc();
    $total_suppliers = $total_suppliers_row['total_suppliers'];
} else {
    $total_suppliers = 0;
};

// Total products
$total_products_stmt = $conn->prepare('SELECT COUNT(productId) AS total_products FROM products');
$total_products_stmt->execute();
$total_products_result = $total_products_stmt->get_result();
if ($total_products_result->num_rows > 0) {
    $total_products_row = $total_products_result->fetch_assoc();
    $total_products = $total_products_row['total_products'];
} else {
    $total_products = 0;
};

// Total purchases
$total_purchases_stmt = $conn->prepare('SELECT COUNT(purchaseNumber) AS total_purchases FROM purchases');
$total_purchases_stmt->execute();
$total_purchases_result = $total_purchases_stmt->get_result();
if ($total_purchases_result->num_rows > 0) {
    $total_purchases_row = $total_purchases_result->fetch_assoc();
    $total_purchases = $total_purchases_row['total_purchases'];
} else {
    $total_purchases = 0;
};

// Total sales
$total_sales_stmt = $conn->prepare('SELECT COUNT(invoiceNumber) AS total_orders FROM orders');
$total_sales_stmt->execute();
$total_sales_result = $total_sales_stmt->get_result();
if ($total_sales_result->num_rows > 0) {
    $total_sales_row = $total_sales_result->fetch_assoc();
    $total_orders = $total_sales_row['total_orders'];
} else {
    $total_sales = 0;
};

// Total quotations
$total_quotations_stmt = $conn->prepare('SELECT COUNT(referenceNumber) AS total_quotations FROM quotations');
$total_quotations_stmt->execute();
$total_quotations_result = $total_quotations_stmt->get_result();
if ($total_quotations_result->num_rows > 0) {
    $total_quotations_row = $total_quotations_result->fetch_assoc();
    $total_quotations = $total_quotations_row['total_quotations'];
} else {
    $total_quotations = 0;
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/sweetalert/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper">
        <div class="header">
            <div class="header-left active">
                <a href="index.php" class="logo">
                    <img src="assets/img/logo.png" alt="">
                </a>
                <a href="index.php" class="logo-small">
                    <img src="assets/img/logo-small.png" alt="">
                </a>
                <a id="toggle_btn" href="javascript:void(0);"></a>
            </div>

            <a id="mobile_btn" class="mobile_btn" href="#sidebar">
                <span class="bar-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </a>

            <ul class="nav user-menu">

                <li class="nav-item dropdown has-arrow main-drop">
                    <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                        <span class="user-img">
                            <img src="<?= !empty($_SESSION['profilePicture']) ? 'assets/img/profiles/' . $_SESSION['profilePicture'] : 'assets/img/profiles/avator1.jpg' ?>" alt="User Image">
                            <span class="status online"></span>
                        </span>
                    </a>

                    <!-- User Profile -->
                    <div class="dropdown-menu menu-drop-user">
                        <div class="profilename">
                            <div class="profileset">
                                <span class="user-img">
                                    <img src="<?= !empty($_SESSION['profilePicture']) ? 'assets/img/profiles/' . $_SESSION['profilePicture'] : 'assets/img/profiles/avator1.jpg' ?>" alt="User Image">
                                    <span class="status online"></span>
                                </span>
                                <div class="profilesets">
                                    <?php if (isset($_SESSION['username']) && isset($_SESSION['userRole'])) { ?>
                                        <h6><?= ($_SESSION['username']) ?></h6>
                                        <h5><?= ($_SESSION['userRole']) ?></h5>
                                    <?php } else { ?>
                                        <h6>Guest</h6>
                                        <h5>Visitor</h5>
                                    <?php } ?>
                                </div>
                            </div>

                            <hr class="m-0">
                            <a class="dropdown-item" href="profile.php"> <i class="me-2" data-feather="user"></i> My Profile</a>
                            <a class="dropdown-item" href="#"><i class="me-2" data-feather="settings"></i>Settings</a>
                            <hr class="m-0">
                            <a class="dropdown-item logout pb-0" href="signout.php">
                                <img src="assets/img/icons/log-out.svg" class="me-2" alt="img">
                                Logout
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="dropdown mobile-user-menu">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="profile.php">My Profile</a>
                    <a class="dropdown-item" href="#">Settings</a>
                    <a class="dropdown-item" href="signout.php">Logout</a>
                </div>
            </div>
        </div>

        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li class="active">
                            <a href="index.php"><img src="assets/img/icons/dashboard.svg" alt="img"><span> Dashboard</span></a>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/product.svg" alt="img"><span> Product</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="productlist.php">Product List</a></li>
                                <li><a href="categorylist.php">Category List</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/sales1.svg" alt="img"><span> Sales</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="saleslist.php">Sales List</a></li>
                                <li><a href="add-sales.php">Add Sales</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/purchase1.svg" alt="img"><span> Purchase</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="purchaselist.php">Purchase List</a></li>
                                <li><a href="addpurchase.php">Add Purchase</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/quotation1.svg" alt="img"><span> Quotation</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="quotationList.php">Quotation List</a></li>
                                <li><a href="addquotation.php">Add Quotation</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/users1.svg" alt="img"><span> People</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="customerlist.php">Customer List</a></li>
                                <li><a href="supplierlist.php">Supplier List</a></li>
                                <li><a href="agentlist.php">Agent List</a></li>
                                <li><a href="userlist.php">User List</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/time.svg" alt="img"><span> Report</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="inventoryreport.php">Inventory Report</a></li>
                                <li><a href="salesreport.php">Sales Report</a></li>
                                <li><a href="invoicereport.php">Invoice Report</a></li>
                                <li><a href="purchasereport.php">Purchase Report</a></li>
                                <li><a href="supplierreport.php">Supplier Report</a></li>
                                <li><a href="customerreport.php">Customer Report</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/settings.svg" alt="img"><span> Settings</span> <span class="menu-arrow"></span></a>
                            <ul>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-wrapper">
            <div class="content">
                <div class="row">

                    <!-- Total Orders -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash1.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_orders; ?>"></span></h5>
                                <h6>Total Sales</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Total Purchases -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash1">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash2.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_purchases; ?>"></span></h5>
                                <h6>Total Purchases</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Total Products -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash2">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash3.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_products; ?>"></span></h5>
                                <h6>Products</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Total Quotations -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash3">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash4.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_quotations; ?>"></span></h5>
                                <h6>Total Quotations</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Total Users -->
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count w-100">
                            <div class="dash-counts">
                                <h4><?= $total_users; ?></h4>
                                <h5>Users</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Customers -->
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count das1 w-100">
                            <div class="dash-counts">
                                <h4><?= $total_customers; ?></h4>
                                <h5>Customers</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user-check"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Suppliers -->
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count das2">
                            <div class="dash-counts">
                                <h4><?= $total_suppliers; ?></h4>
                                <h5>Suppliers</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="file-text"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Agents -->
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count das3">
                            <div class="dash-counts">
                                <h4><?= $total_agents; ?></h4>
                                <h5>Agents</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="file"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recently added orders -->
                    <div class="col-lg-6 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Recently Added Sales</h4>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a href="saleslist.php" class="dropdown-item">Orders List</a></li>
                                        <li><a href="add-sales.php" class="dropdown-item">Add Orders</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive dataview">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>UID</th>
                                                <th>Invoice#</th>
                                                <th>Customer</th>
                                                <th>Order Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch 4 recently added orders
                                            $recently_added_orders = $conn->prepare("SELECT orders.*, customers.customerName
                                                                                                FROM
                                                                                                    `orders`, customers
                                                                                                WHERE
                                                                                                    orders.customerId = customers.customerId
                                                                                                ORDER BY
                                                                                                    `created_at`
                                                                                                DESC
                                                                                                LIMIT 5");
                                            $recently_added_orders->execute();
                                            $recently_added_orders = $recently_added_orders->get_result();
                                            while ($row = $recently_added_orders->fetch_assoc()) {
                                                $order_uid = $row["orderUId"];
                                                $invoice_number = $row["invoiceNumber"];
                                                $currentStatus = $row["orderStatus"];
                                                $due_amount = $row["due"];

                                                if ($currentStatus != 2) {
                                                    $newStatus = $currentStatus;

                                                    if ($due_amount == 0) {
                                                        $newStatus = 1;
                                                    } else {
                                                        $newStatus = 0;
                                                    }

                                                    if ($newStatus != $currentStatus) {
                                                        $update_query = $conn->prepare("UPDATE orders SET orderStatus = ? WHERE invoiceNumber = ?");
                                                        $update_query->bind_param("is", $newStatus, $invoice_number);
                                                        $update_query->execute();
                                                        $update_query->close();

                                                        $row["orderStatus"] = $newStatus;
                                                    }
                                                }
                                            ?>
                                                <tr>
                                                    <td><?= $row['orderUId']; ?></td>
                                                    <td><?= $row['invoiceNumber']; ?></td>
                                                    <td><?= $row['customerName']; ?></td>
                                                    <td><?= date('d-m-Y', strtotime($row['orderDate'])); ?></td>
                                                    <td>
                                                        <?php if ($row["orderStatus"] == "0") : ?>
                                                            <span class="badges bg-lightyellow">Pending</span>
                                                        <?php elseif ($row["orderStatus"] == "1"): ?>
                                                            <span class="badges bg-lightgreen">Completed</span>
                                                        <?php else: ?>
                                                            <span class="badges bg-lightred">Cancelled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                        </tbody>
                                    <?php
                                            }
                                    ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Quotations -->
                    <div class="col-lg-6 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Pending Quotations</h4>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a href="quotationList.php" class="dropdown-item">Quotations List</a></li>
                                        <li><a href="addquotation.php" class="dropdown-item">Add Quotation</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">

                                <div class="table-responsive dataview">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>SNo</th>
                                                <th>Reference#</th>
                                                <th>Customer Name</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $pending_quotations_stmt = $conn->prepare("SELECT
                                                                                            quotations.*,
                                                                                            customers.customerName
                                                                                        FROM
                                                                                            quotations,
                                                                                            customers
                                                                                        WHERE
                                                                                            quotations.customerId = customers.customerId 
                                                                                            AND 
                                                                                            quotationStatus = 0
                                                                                        LIMIT 5");
                                            $pending_quotations_stmt->execute();
                                            $pending_quotations = $pending_quotations_stmt->get_result();
                                            $sn = 0;
                                            while ($row = $pending_quotations->fetch_assoc()) {
                                                $sn++;
                                            ?>
                                                <tr>
                                                    <td> <?= $sn; ?> </td>
                                                    <td> <?= $row['referenceNumber']; ?> </td>
                                                    <td> <?= $row['customerName']; ?> </td>
                                                    <td> <?= date('d-m-Y', strtotime($row['quotationDate'])); ?> </td>
                                                    <td> <?= number_format($row['totalAmount'], 2); ?> </td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="row">

                        <!-- Recently added purchases -->
                        <div class="col-lg-6 col-sm-12 col-12 d-flex">
                            <div class="card flex-fill">
                                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">Recently Added Purchases</h4>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li><a href="purchaselist.php" class="dropdown-item">Purchases List</a></li>
                                            <li><a href="addpurchase.php" class="dropdown-item">Purchase Add</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive dataview">
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>UID</th>
                                                    <th>Purchase#</th>
                                                    <th>Purchase Date</th>
                                                    <th>Total Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Fetch 4 recently added purchases
                                                $recently_added_purchases = $conn->prepare("SELECT *
                                                                                                FROM
                                                                                                    `purchases`
                                                                                                ORDER BY
                                                                                                    `created_at`
                                                                                                DESC
                                                                                                LIMIT 5");
                                                $recently_added_purchases->execute();
                                                $recently_added_purchases = $recently_added_purchases->get_result();
                                                while ($row = $recently_added_purchases->fetch_assoc()) {
                                                ?>
                                                    <tr>
                                                        <td><?= $row['purchaseUId']; ?></td>
                                                        <td><?= $row['purchaseNumber']; ?></td>
                                                        <td><?= date('d-m-Y', strtotime($row['purchaseDate'])); ?></td>
                                                        <td><?= number_format($row['totalAmount'], 2); ?> </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Alert -->
                        <div class="col-lg-6 col-sm-12 col-12 d-flex">
                            <div class="card flex-fill">
                                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">Products Quantity Alert</h4>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li><a href="productlist.php" class="dropdown-item">Products List</a></li>
                                            <!-- <li><a href="addquotation.php" class="dropdown-item">Add Quotation</a></li> -->
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="table-responsive dataview">
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>SNo</th>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>LastUpdated</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $quantity_alert_stmt = $conn->prepare("SELECT *
                                                                                        FROM
                                                                                            products
                                                                                        ORDER BY 
                                                                                            quantity ASC
                                                                                        LIMIT 5");
                                                $quantity_alert_stmt->execute();
                                                $quantity_alerts = $quantity_alert_stmt->get_result();
                                                $sn = 0;
                                                while ($row = $quantity_alerts->fetch_assoc()) {
                                                    $product_id = $row['productId'];
                                                    $quantity = $row['quantity'];
                                                    $quantityAlert = $row['quantityAlert'] ?? 0;
                                                    $currentStatus = $row['productStatus'];

                                                    $newStatus = $currentStatus;

                                                    if ($quantity == 0) {
                                                        $newStatus = 0;
                                                    } elseif ($quantity <= $quantityAlert) {
                                                        $newStatus = 2;
                                                    } else {
                                                        $newStatus = 1;
                                                    }

                                                    if ($newStatus != $currentStatus) {
                                                        $update_query = $conn->prepare("UPDATE products SET productStatus = ? WHERE productId = ?");
                                                        $update_query->bind_param("ii", $newStatus, $product_id);
                                                        $update_query->execute();
                                                        $update_query->close();

                                                        $row['productStatus'] = $newStatus;
                                                    }
                                                    $sn++;
                                                ?>
                                                    <tr>
                                                        <td> <?= $sn; ?> </td>
                                                        <td> <?= $row['productName']; ?> </td>
                                                        <td> <?= $row['quantity']; ?> </td>
                                                        <td> <?= date('d-m-Y', strtotime($row['updated_at'])); ?> </td>
                                                        <td>
                                                            <?php if ($row['productStatus'] == "0") : ?>
                                                                <span class="badges bg-lightred">OutOfStock</span>
                                                            <?php elseif ($row['productStatus'] == "1"): ?>
                                                                <span class="badges bg-lightgreen">Available</span>
                                                            <?php else: ?>
                                                                <span class="badges bg-lightyellow">LowStock</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="assets/js/jquery-3.6.0.min.js"></script>
                <script src="assets/js/feather.min.js"></script>
                <script src="assets/js/jquery.slimscroll.min.js"></script>
                <script src="assets/js/jquery.dataTables.min.js"></script>
                <script src="assets/js/dataTables.bootstrap4.min.js"></script>
                <script src="assets/js/bootstrap.bundle.min.js"></script>
                <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
                <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
                <script src="assets/js/script.js"></script>

                <!-- ApexCharts script to render Student & Class chart -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const chartData = <?php echo json_encode($chart_data); ?>;
                        const options = {
                            series: [{
                                name: 'Count',
                                data: chartData.values
                            }],
                            chart: {
                                type: 'bar',
                                height: 300
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '40%',
                                    endingShape: 'flat',
                                    distributed: true // Distribute colors across data points
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            colors: ['#5afa20ff', '#fc4c72ff'],
                            xaxis: {
                                categories: chartData.labels,
                                title: {
                                    text: 'Category'
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Count'
                                },
                                min: 0
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val;
                                    }
                                }
                            },
                            legend: {
                                show: false // Optional: Hide legend since we have one series
                            }
                        };
                        const chart = new ApexCharts(document.querySelector("#sales_charts"), options);
                        chart.render();
                    });
                </script>
            </div>
</body>

</html>
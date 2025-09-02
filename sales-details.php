<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['invoiceNumber'])) {
    echo "Invalid Invoice Number";
    exit;
}
// Get user id from session
$user_id = $_SESSION["id"];

// Get purchase number
$invoice_number = $_GET['invoiceNumber'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Sales Details</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/animate.css">

    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">

    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"> </div>
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
                        <span class="user-img"><img src="assets/img/profiles/avator1.jpg" alt="">
                            <span class="status online"></span>
                        </span>
                    </a>

                    <!-- User Profile -->
                    <div class="dropdown-menu menu-drop-user">
                        <div class="profilename">
                            <div class="profileset">
                                <span class="user-img"><img src="assets/img/profiles/avator1.jpg" alt="">
                                    <span class="status online"></span>
                                </span>
                                <div class="profilesets">
                                    <?php if (isset($_SESSION['username']) && isset($_SESSION['userRole'])) { ?>
                                        <h6><?= $_SESSION['username']; ?></h6>
                                        <h5><?= $_SESSION['userRole']; ?></h5>
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
                    <a class="dropdown-item" href="signin.php">Logout</a>
                </div>
            </div>
        </div>

        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li>
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
                                <li><a href="saleslist.php" class="active">Sales List</a></li>
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
                <div class="page-header">
                    <div class="page-title">
                        <h4>Sale Details</h4>
                        <h6>View sale details</h6>
                    </div>
                    <div class="page-btn">
                        <a href="saleslist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Orders List</a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="card-sales-split">
                            <h2>Invoice Number : <?= $invoice_number; ?></h2>
                            <ul>
                                <li>
                                    <a href="javascript:void(0);"><img src="assets/img/icons/edit.svg" alt="img"></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);"><img src="assets/img/icons/pdf.svg" alt="img"></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);"><img src="assets/img/icons/excel.svg" alt="img"></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);"><img src="assets/img/icons/printer.svg" alt="img"></a>
                                </li>
                            </ul>
                        </div>

                        <!-- First Table: Order Details -->
                        <div class="row">
                            <!-- Customer Information Table -->
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Customer Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <tbody>
                                                    <?php
                                                    // Get order 
                                                    $order_query = $conn->prepare("SELECT 
                                                                orders.*, 
                                                                customers.*, 
                                                                u1.username AS biller,
                                                                u2.username AS updater
                                                            FROM orders
                                                            JOIN customers ON orders.customerId = customers.customerId
                                                            JOIN users AS u1 ON orders.createdBy = u1.userId
                                                            JOIN users AS u2 ON orders.updatedBy = u2.userId
                                                            WHERE orders.invoiceNumber = ?");
                                                    $order_query->bind_param("s", $invoice_number);
                                                    $order_query->execute();
                                                    $order_result = $order_query->get_result();
                                                    if ($order_result->num_rows > 0) {
                                                        $order_row = $order_result->fetch_assoc();
                                                    ?>
                                                        <tr>
                                                            <td><strong>Name:</strong></td>
                                                            <td><?= $order_row['customerName']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Email:</strong></td>
                                                            <td>
                                                                <a href="mailto:<?= $order_row['customerEmail']; ?>">
                                                                    <?= $order_row['customerEmail']; ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Phone:</strong></td>
                                                            <td>
                                                                <a href="tel:<?= $order_row['customerPhone']; ?>">
                                                                    <?= $order_row['customerPhone']; ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Address:</strong></td>
                                                            <td><?= $order_row['customerAddress']; ?></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    $order_query->close();
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Statistics Table -->
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Order Statistics</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <tbody>
                                                    <?php
                                                    if (isset($order_row)) {
                                                    ?>
                                                        <tr>
                                                            <td><strong>Biller:</strong></td>
                                                            <td><?= $order_row['biller']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Updated By:</strong></td>
                                                            <td><?= $order_row['updater']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Order Date:</strong></td>
                                                            <td><?= $order_row['orderDate']; ?></td>
                                                        </tr>
                                                        <!-- <tr>
                                                            <td><strong>Created At:</strong></td>
                                                            <td><?= $order_row['created_at']; ?></td>
                                                        </tr> -->
                                                        <tr>
                                                            <td><strong>Updated At:</strong></td>
                                                            <td><?= $order_row['updated_at']; ?></td>
                                                        </tr>
                                                        <!-- <tr>
                                                            <td><strong>Invoice Number:</strong></td>
                                                            <td><?= $order_row['invoiceNumber']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Payment Type:</strong></td>
                                                            <td><?= $order_row['paymentType']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Status:</strong></td>
                                                            <td><?= $order_row['orderStatus'] == 1 ? 'Completed' : ($order_row['orderStatus'] == 0 ? 'Pending' : 'Cancelled'); ?></td>
                                                        </tr> -->
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

                        <!-- Financial Summary Table -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Financial Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Total Products</th>
                                                        <th>SubTotal</th>
                                                        <th>Order VAT(%)</th>
                                                        <th>Total</th>
                                                        <th>Paid</th>
                                                        <th>Due</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (isset($order_row)) {
                                                    ?>
                                                        <tr>
                                                            <td><?= $order_row['totalProducts']; ?></td>
                                                            <td class="text-primary"><strong>Tsh: <?= number_format($order_row['subTotal'], 2); ?></strong></td>
                                                            <td><?= $order_row['vat']; ?>%</td>
                                                            <td class="text-primary"><strong>Tsh: <?= number_format($order_row['total'], 2); ?></strong></td>
                                                            <td class="text-success"><strong>Tsh: <?= number_format($order_row['paid'], 2); ?></strong></td>
                                                            <td class="text-danger"><strong>Tsh: <?= number_format($order_row['due'], 2); ?></strong></td>
                                                            <td> <?= $order_row['orderStatus'] == 1 ? 'Completed' : ($order_row['orderStatus'] == 0 ? 'Pending' : 'Cancelled'); ?> </td>
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

                        <!-- Second Table: Product Details and Summary -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Product Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S/N</th>
                                                        <th>Product Name</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Cost</th>
                                                        <th>Total Cost</th>
                                                        <th>Discount</th>
                                                        <th>TAX</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Get order details and products
                                                    $details_query = $conn->query("SELECT 
                                                                        order_details.*, 
                                                                        products.productName, 
                                                                        products.tax
                                                                    FROM order_details
                                                                    JOIN products ON order_details.productId = products.productId
                                                                    WHERE order_details.invoiceNumber = '$invoice_number'
                                                                    ORDER BY order_details.orderDetailsId ASC");
                                                    $sn = 1;
                                                    while ($detail = $details_query->fetch_assoc()) {
                                                    ?>
                                                        <tr>
                                                            <td style="padding: 10px;vertical-align: top;"><?= $sn++; ?></td>
                                                            <td style="padding: 10px;vertical-align: top; display: flex;align-items: center;">
                                                                <?= $detail['productName']; ?>
                                                            </td>
                                                            <td style="padding: 10px;vertical-align: top;">
                                                                <?= $detail['quantity']; ?>
                                                            </td>
                                                            <td style="padding: 10px;vertical-align: top;">
                                                                <?= $detail['unitCost']; ?>
                                                            </td>
                                                            <td style="padding: 10px;vertical-align: top;">
                                                                <?= $detail['totalCost']; ?>
                                                            </td>
                                                            <td style="padding: 10px;vertical-align: top;">
                                                                <?= $detail['discount'] ?? '-'; ?>
                                                            </td>
                                                            <td style="padding: 10px;vertical-align: top;">
                                                                <?= $detail['tax']; ?>%
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
                </div>
            </div>
        </div>
    </div>


    <script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>
</body>

</html>
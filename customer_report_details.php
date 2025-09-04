<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['customerId'])) {
    echo "Invalid Invoice Number";
    exit;
}
// Get user id from session
$user_id = $_SESSION["id"];

// Get purchase number
$customer_id = $_GET['customerId'];

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
                                <li><a href="customerreport.php" class="active">Customer Report</a></li>
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
                        <h4>Customer Report</h4>
                        <h6>View Customer Report</h6>
                    </div>
                    <div class="page-btn">
                        <a href="customerreport.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Customer Reports</a>
                    </div>
                </div>
                <?php
                // Get customer information and order statistics
                $customer_query = $conn->query("SELECT 
                                    customers.*,
                                    COUNT(orders.invoiceNumber) AS total_orders,
                                    SUM(CASE WHEN orders.orderStatus = 1 THEN 1 ELSE 0 END) AS completed_orders,
                                    SUM(CASE WHEN orders.orderStatus = 0 THEN 1 ELSE 0 END) AS pending_orders,
                                    SUM(CASE WHEN orders.orderStatus = 2 THEN 1 ELSE 0 END) AS cancelled_orders,
                                    SUM(orders.total) AS total_amount,
                                    SUM(orders.paid) AS total_paid,
                                    SUM(orders.due) AS total_due
                                FROM customers 
                                LEFT JOIN orders ON customers.customerId = orders.customerId
                                WHERE customers.customerId = '$customer_id'
                                GROUP BY customers.customerId");

                if ($customer_query->num_rows > 0) {
                    $customer = $customer_query->fetch_assoc();
                ?>

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
                                                <tr>
                                                    <td><strong>Name:</strong></td>
                                                    <td><?= ($customer['customerName']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>
                                                        <a href="mailto:<?= $customer['customerEmail']; ?>">
                                                            <?= ($customer['customerEmail']); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Phone:</strong></td>
                                                    <td>
                                                        <a href="tel:<?= $customer['customerPhone']; ?>">
                                                            <?= ($customer['customerPhone']); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Address:</strong></td>
                                                    <td><?= ($customer['customerAddress']); ?></td>
                                                </tr>
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
                                                <tr>
                                                    <td><strong>Total Orders:</strong></td>
                                                    <td><span class="badge bg-primary"><?= $customer['total_orders']; ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Completed:</strong></td>
                                                    <td><span class="badge bg-success"><?= $customer['completed_orders']; ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pending:</strong></td>
                                                    <td><span class="badge bg-warning"><?= $customer['pending_orders']; ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cancelled:</strong></td>
                                                    <td><span class="badge bg-danger"><?= $customer['cancelled_orders']; ?></span></td>
                                                </tr>
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
                                            <tbody>
                                                <tr>
                                                    <td><strong>Total Amount:</strong></td>
                                                    <td class="text-primary"><strong>Tsh: <?= number_format($customer['total_amount'], 2); ?></strong></td>
                                                    <td><strong>Total Paid:</strong></td>
                                                    <td class="text-success"><strong>Tsh: <?= number_format($customer['total_paid'], 2); ?></strong></td>
                                                    <td><strong>Total Due:</strong></td>
                                                    <td class="text-danger"><strong>Tsh: <?= number_format($customer['total_due'], 2); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order History Table -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Order History</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice No.</th>
                                                    <th>Date</th>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>SubTotal</th>
                                                    <th>VAT</th>
                                                    <th>Discount</th>
                                                    <th>Total</th>
                                                    <th>Paid</th>
                                                    <th>Due</th>
                                                    <th>Status</th>
                                                    <!-- <th>Actions</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Get order details and products
                                                $details_query = $conn->query("SELECT 
                                                                orders.*,
                                                                order_details.*, 
                                                                products.productName,
                                                                customers.customerId
                                                            FROM orders
                                                            JOIN customers ON orders.customerId = customers.customerId
                                                            JOIN order_details ON orders.invoiceNumber = order_details.invoiceNumber
                                                            JOIN products ON order_details.productId = products.productId
                                                            WHERE orders.customerId = '$customer_id'
                                                            ORDER BY orders.orderDate DESC");
                                                $sn = 1;
                                                while ($detail = $details_query->fetch_assoc()) {
                                                    // Determine status badge
                                                    $statusBadge = '';
                                                    switch ($detail['orderStatus']) {
                                                        case 0:
                                                            $statusBadge = '<span class="badge bg-warning">Pending</span>';
                                                            break;
                                                        case 1:
                                                            $statusBadge = '<span class="badge bg-success">Completed</span>';
                                                            break;
                                                        case 2:
                                                            $statusBadge = '<span class="badge bg-danger">Cancelled</span>';
                                                            break;
                                                    }
                                                ?>
                                                    <tr>
                                                        <td><?= $sn++; ?></td>
                                                        <td><?= $detail['invoiceNumber']; ?></td>
                                                        <td><?= date('M d, Y', strtotime($detail['orderDate'])); ?></td>
                                                        <td><?= ($detail['productName']); ?></td>
                                                        <td><?= $detail['quantity']; ?></td>
                                                        <td><?= number_format($detail['subTotal'], 2); ?></td>
                                                        <td><?= $detail['vat']; ?>%</td>
                                                        <td>-</td>
                                                        <td><strong><?= number_format($detail['total'], 2); ?></strong></td>
                                                        <td class="text-success"><strong><?= number_format($detail['paid'], 2); ?></strong></td>
                                                        <td class="text-danger"><strong><?= number_format($detail['due'], 2); ?></strong></td>
                                                        <td><?= $statusBadge; ?></td>
                                                        <!-- <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary btn-sm" title="View Details">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <?php if ($detail['due'] > 0 && $detail['orderStatus'] != 2): ?>
                                                                    <button class="btn btn-outline-success btn-sm"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#paymentModal"
                                                                        onclick="openPaymentModal('<?= $detail['invoiceNumber']; ?>', '<?= ($customer['customerName']); ?>', <?= $detail['total']; ?>, <?= $detail['paid']; ?>, <?= $detail['due']; ?>)"
                                                                        title="Make Payment">
                                                                        <i class="fas fa-credit-card"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td> -->
                                                    </tr>
                                                <?php } ?>

                                                <?php if ($details_query->num_rows == 0): ?>
                                                    <tr>
                                                        <td colspan="13" class="text-center py-4">
                                                            <p class="text-muted">No orders found for this customer.</p>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php
                } else {
                    echo '<div class="alert alert-warning">Customer not found.</div>';
                }
                ?>
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
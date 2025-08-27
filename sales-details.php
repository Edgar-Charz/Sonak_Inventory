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
                            </a></div>
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
                                <li><a href="brandlist.php">Brand List</a></li>
                                <li><a href="addbrand.php">Add Brand</a></li>
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
                                <li><a href="purchaseorderreport.php">Purchase order report</a></li>
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
                        <div class="invoice-box table-height" style="max-width: 1600px;width:100%;overflow: auto;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                            <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">

                                <!-- Order details table -->
                                <tbody>
                                    <?php
                                    // Get order 
                                    $order_query = $conn->query("SELECT 
                                                                            orders.*, 
                                                                            customers.*, 
                                                                            u1.username AS biller,
                                                                            u2.username AS updater
                                                                        FROM orders
                                                                        JOIN customers ON orders.customerId = customers.customerId
                                                                        JOIN users AS u1 ON orders.createdBy = u1.userId
                                                                        JOIN users AS u2 ON orders.updatedBy = u2.userId
                                                                        WHERE orders.invoiceNumber = '$invoice_number'
                                                                        ");
                                    if ($order_query->num_rows > 0) {
                                        $order_row = $order_query->fetch_assoc();
                                    ?>
                                        <tr class="top">
                                            <td colspan="6" style="padding: 5px;vertical-align: top;">
                                                <table style="width: 100%;line-height: inherit;text-align: left;">
                                                    <tbody>
                                                        <tr>
                                                            <!-- Customer Info -->
                                                            <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                <h6 style="color:#7367F0;font-weight:600;line-height:35px;margin-bottom:10px;">Customer Info</h6>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Name:</strong> <?= $order_row['customerName']; ?>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Email:</strong>
                                                                    <a href="mailto:<?= $order_row['customerEmail']; ?>">
                                                                        <?= $order_row['customerEmail']; ?>
                                                                    </a>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Phone:</strong>
                                                                    <a href="tel:<?= $order_row['customerPhone']; ?>">
                                                                        <?= $order_row['customerPhone']; ?>
                                                                    </a>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Address:</strong> <?= $order_row['customerAddress']; ?>
                                                                </p>
                                                            </td>

                                                            <!-- Order Info -->
                                                            <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                <h6 style="color:#7367F0;font-weight:600;line-height:35px;margin-bottom:10px;">Order Info</h6>
                                                                <p style="font-size:14px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Biller:</strong> <?= $order_row['biller']; ?>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>UpdatedBy:</strong> <?= $order_row['updater']; ?>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Order Date:</strong>
                                                                    <?= $order_row['orderDate']; ?>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Created At:</strong>
                                                                    <?= $order_row['created_at']; ?>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Updated At:</strong>
                                                                    <?= $order_row['updated_at']; ?>
                                                                </p>
                                                            </td>

                                                            <!-- Payment Info -->
                                                            <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                <h6 style="color:#7367F0;font-weight:600;line-height:35px;margin-bottom:10px;">Payment Info</h6>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Invoice Number:</strong>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Payment Type:</strong>
                                                                </p>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Status:</strong>
                                                                </p>
                                                            </td>

                                                            <!-- Invoice Values -->
                                                            <td style="padding:5px;vertical-align:top;text-align:right;padding-bottom:20px">
                                                                <h6 style="color:#7367F0;font-weight:600;line-height:35px;margin-bottom:10px;">&nbsp;</h6>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <?= $order_row['invoiceNumber']; ?>
                                                                </p>
                                                                <p style="font-size:15px;color:#2E7D32;font-weight:400;margin:0;">
                                                                    <?= $order_row['paymentType']; ?>
                                                                </p>
                                                                <p style="font-size:15px;color:#2E7D32;font-weight:400;margin:0;">
                                                                    <?= $order_row['orderStatus'] == 1 ? 'Completed' : ($order_row['orderStatus'] == 0 ? 'Pending' : 'Cancelled'); ?>
                                                                </p>
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="heading " style="background: #F3F2F7;">
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                S/N
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                Product Name
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                Quantity
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                Unit Cost
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                Total Cost
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                Discount
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                TAX
                                            </td>
                                        </tr>

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
                                            <tr class="details" style="border-bottom:1px solid #E9ECEF ;">
                                                <td style="padding: 10px;vertical-align: top; ">
                                                    <?= $sn++; ?>
                                                </td>
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
                                    }
                                    ?>
                                </tbody>
                                <!-- /Order details table -->

                            </table>
                        </div>
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Total Products</label>
                                    <input type="text" value="<?= $order_row['totalProducts']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>SubTotal</label>
                                    <input type="text" value="<?= $order_row['subTotal']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Order VAT(%)</label>
                                    <input type="text" value="<?= $order_row['vat']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Total</label>
                                    <input type="text" value="<?= $order_row['total']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Paid</label>
                                    <input type="text" value="<?= $order_row['paid']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Due</label>
                                    <input type="text" value="<?= $order_row['due']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="select" aria-readonly="true">
                                        <option value="0" <?= $order_row['orderStatus'] == 0 ? 'selected' : ''; ?>>Pending</option>
                                        <option value="1" <?= $order_row['orderStatus'] == 1 ? 'selected' : ''; ?>>Completed</option>
                                        <option value="2" <?= $order_row['orderStatus'] == 2 ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
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
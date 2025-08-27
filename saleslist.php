<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id form session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Sales List</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/animate.css">

    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">

    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">

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
                        <h4>Sales List</h4>
                        <h6>Manage your sales</h6>
                    </div>
                    <div class="page-btn">
                        <a href="add-sales.php" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img" class="me-1">Add Sales</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-top">
                            <div class="search-set">
                                <div class="search-path">
                                    <a class="btn btn-filter" id="filter_search">
                                        <img src="assets/img/icons/filter.svg" alt="img">
                                        <span><img src="assets/img/icons/closes.svg" alt="img"></span>
                                    </a>
                                </div>
                                <div class="search-input">
                                    <a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
                                </div>
                            </div>
                            <div class="wordset">
                                <ul>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
                                    </li>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
                                    </li>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="print"><img src="assets/img/icons/printer.svg" alt="img"></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card" id="filter_inputs">
                            <div class="card-body pb-0">
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Reference No">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Completed</option>
                                                <option>Paid</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <a class="btn btn-filters ms-auto"><img src="assets/img/icons/search-whites.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="orderTable">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Invoice No.</th>
                                        <th>Customer Name</th>
                                        <th>Order Date</th>
                                        <th>Biller</th>
                                        <th>UpdatedBy</th>
                                        <th>PaymentType</th>
                                        <th>VAT(%)</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Due</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch all orders data from the database
                                    $orders_query = $conn->query("SELECT 
                                                                        orders.*, 
                                                                        order_details.*, 
                                                                        customers.customerName, 
                                                                        products.productName,
                                                                        u1.username AS biller,
                                                                        u2.username AS updater
                                                                    FROM 
                                                                        orders, 
                                                                        order_details, 
                                                                        customers, 
                                                                        products, 
                                                                        users AS u1, 
                                                                        users AS u2
                                                                    WHERE 
                                                                        orders.invoiceNumber = order_details.invoiceNumber
                                                                        AND orders.customerId = customers.customerId
                                                                        AND order_details.productId = products.productId
                                                                        AND orders.createdBy = u1.userId
                                                                        AND orders.updatedBy = u2.userId
                                                                    GROUP BY 
                                                                        orders.invoiceNumber
                                                                    ORDER BY 
                                                                        orders.orderUId DESC;");
                                    if ($orders_query->num_rows > 0) {
                                        while ($order_row = $orders_query->fetch_assoc()) {
                                            $order_uid = $order_row["orderUId"];
                                            $invoice_number = $order_row["invoiceNumber"]
                                    ?>
                                            <tr>
                                                <td> <?= $order_row["orderUId"]; ?> </td>
                                                <td> <?= $order_row["invoiceNumber"]; ?> </td>
                                                <td> <?= $order_row["customerName"]; ?> </td>
                                                <td> <?= $order_row["orderDate"]; ?> </td>
                                                <td> <?= $order_row["biller"]; ?> </td>
                                                <td> <?= $order_row["updater"]; ?> </td>
                                                <td> <?= $order_row["paymentType"]; ?> </td>
                                                <td> <?= $order_row["vat"]; ?>% </td>
                                                <td> <?= number_format($order_row["total"],2 ); ?> </td>
                                                <td class="text-success"> <?= number_format($order_row["paid"],2); ?> </td>
                                                <td class="text-danger"> <?= number_format($order_row["due"],2); ?> </td>
                                                <td>
                                                    <?php if ($order_row["orderStatus"] == "0") : ?>
                                                        <span class="badges bg-lightyellow">Pending</span>
                                                    <?php elseif ($order_row["orderStatus"] == "1"): ?>
                                                        <span class="badges bg-lightgreen">Completed</span>
                                                    <?php else: ?>
                                                        <span class="badges bg-lightred">Cancelled</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-center">
                                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a href="sales-details.php?invoiceNumber=<?= $invoice_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/eye1.svg" class="me-2" alt="img">
                                                                Sale Detail
                                                            </a>
                                                        </li>
                                                        <!-- <li>
                                                            <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#invoiceModal<?= $order_row['invoiceNumber']; ?>">
                                                                <img src="assets/img/icons/eye1.svg" class="me-2" alt="img">
                                                                View Invoice
                                                            </button>
                                                        </li> -->
                                                        <li>
                                                            <a href="edit-sales.php?invoiceNumber=<?= $invoice_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/edit.svg" class="me-2" alt="img">
                                                                Edit Sale
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showPayment<?= $invoice_number; ?>">
                                                                <img src="assets/img/icons/dollar-square.svg" class="me-2" alt="img">
                                                                Show Payments
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment">
                                                                <img src="assets/img/icons/plus-circle.svg" class="me-2" alt="img">
                                                                Create Payment
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="download-invoice.php?invoiceNumber=<?= $invoice_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/download.svg" class="me-2" alt="img">
                                                                Download PDF
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $order_uid; ?>)">
                                                                <img src="assets/img/icons/delete1.svg" class="me-2" alt="img">
                                                                Delete Sale
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>


                                            <!-- View Payment Modal -->
                                            <div class="modal fade" id="showPayment<?= $invoice_number; ?>" tabindex="-1" aria-labelledby="showPaymentModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="showPaymentModalLabel<?= $invoice_number; ?>">Show Payments</h5>
                                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Date</label>
                                                                        <div class="input-groupicon">
                                                                            <input type="text" value="<?= $order_row["orderDate"]; ?>" class="datetimepicker">
                                                                            <div class="addonset">
                                                                                <img src="assets/img/icons/calendars.svg" alt="calendar">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Invoice Number</label>
                                                                        <input type="text" value="<?= $order_row["invoiceNumber"]; ?>" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Total Amount</label>
                                                                        <input type="text" value="<?= $order_row["total"]; ?>" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Payment Type</label>
                                                                        <input type="text" value="<?= $order_row["paymentType"]; ?>" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Paid Amount</label>
                                                                        <input type="text" value="<?= $order_row["paid"]; ?>" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Due Amount</label>
                                                                        <input type="text" value="<?= $order_row["due"]; ?>" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /View Payment Modal -->
                                    <?php
                                        }
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

    <div class="modal fade" id="invoiceModal<?= $order_row['invoiceNumber']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoice <?= $order_row['invoiceNumber']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">

                    <div class="card">
                        <div class="card-body">
                            <div class="invoice-box table-height" style="max-width: 1600px;width:100%;overflow: auto;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                                <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">

                                    <!-- Order details table -->
                                    <tbody>
                                        <?php
                                        // Get order 
                                        $invoice_number = $order_row['invoiceNumber'];
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
                                                                        LIMIT 1");
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
                                                                        <strong>Order Status:</strong>
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
                                            $invoice_number = $order_row['invoiceNumber'];
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
    </div>

    <div class="modal fade" id="createpayment" tabindex="-1" aria-labelledby="createpayment" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Customer</label>
                                <div class="input-groupicon">
                                    <input type="text" value="2022-03-07" class="datetimepicker">
                                    <div class="addonset">
                                        <img src="assets/img/icons/calendars.svg" alt="img">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" value="INV/SL0101">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Received Amount</label>
                                <input type="text" value="0.00">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Paying Amount</label>
                                <input type="text" value="0.00">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Payment type</label>
                                <select class="select">
                                    <option>Cash</option>
                                    <option>Online</option>
                                    <option>Inprogress</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit">Submit</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="editpayment" tabindex="-1" aria-labelledby="editpayment" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Payment</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Customer</label>
                                <div class="input-groupicon">
                                    <input type="text" value="2022-03-07" class="datetimepicker">
                                    <div class="addonset">
                                        <img src="assets/img/icons/datepicker.svg" alt="img">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" value="INV/SL0101">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Received Amount</label>
                                <input type="text" value="0.00">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Paying Amount</label>
                                <input type="text" value="0.00">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Payment type</label>
                                <select class="select">
                                    <option>Cash</option>
                                    <option>Online</option>
                                    <option>Inprogress</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group mb-0">
                                <label>Note</label>
                                <textarea class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit">Submit</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to confirm sale deletion
        function confirmDelete(orderUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deletesale.php?id=' + orderUId;
                };
            });
        };

        // Trigger SweetAlert messages after redirect
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'success') {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Order has been deleted successfully.',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (status === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete the Order',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
        });
    </script>

    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>

    <!-- Orders Table  -->
    <script>
        $(document).ready(function() {
            if ($("#orderTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#orderTable")) {
                    $("#orderTable").DataTable({
                        destroy: true,
                        bFilter: true,
                        sDom: "fBtlpi",
                        pagingType: "numbers",
                        ordering: true,
                        language: {
                            search: " ",
                            sLengthMenu: "_MENU_",
                            searchPlaceholder: "Search Order...",
                            info: "_START_ - _END_ of _TOTAL_ items"
                        },
                        initComplete: function(settings, json) {
                            $(".dataTables_filter").appendTo("#tableSearch");
                            $(".dataTables_filter").appendTo(".search-input");
                        }
                    });
                }
            }
        });
    </script>

</body>

</html>
<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id form session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_POST['paymentBTN'])) {
    $invoiceNumber = $_POST['invoiceNumber'];
    $payingAmount  = floatval($_POST['payingAmount']);
    $paymentType   = $_POST['paymentType'];
    $updatedBy     = $user_id;

    // Fetch current paid & due
    $stmt = $conn->prepare("SELECT paid, due FROM orders WHERE invoiceNumber = ?");
    $stmt->bind_param("s", $invoiceNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        $currentPay = floatval($order['paid']);
        $currentDue = floatval($order['due']);

        // Prevent overpayment
        if ($payingAmount > $currentDue) {
            $payingAmount = $currentDue;
        }

        // New values
        $newPay = $currentPay + $payingAmount;
        $newDue = $currentDue - $payingAmount;

        // Update query
        $update = $conn->prepare("UPDATE orders 
                                  SET paid = ?, due = ?, paymentType = ?, updatedBy = ?, updated_at = NOW()
                                  WHERE invoiceNumber = ?");
        $update->bind_param("ddsds", $newPay, $newDue, $paymentType, $updatedBy, $invoiceNumber);

        if ($update->execute()) {
            // If fully paid, set orderStatus=1 in order_details
            if ($newDue == 0) {
                $updateDetails = $conn->prepare("UPDATE order_details SET status = 1 WHERE invoiceNumber = ?");
                $updateDetails->bind_param("s", $invoiceNumber);
                $updateDetails->execute();
                $updateDetails->close();
            }

            // If successfully
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Success',
                        text: 'Payment updated successfully',
                        timer: 5000
                    }).then(function() {
                        window.location.href = 'saleslist.php';
                    })
            });
            </script>";
        } else {
            // If failed
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update payment',
                        timer: 5000,
                        timerProgressBar: true
                    });
                });
            </script>";
        }

        $update->close();
    } else {
        echo "Order not found.";
    }
}
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
                                    <a class="btn btn-filter" id="">
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
                                            $invoice_number = $order_row["invoiceNumber"];
                                            $currentStatus = $order_row["orderStatus"];
                                            $due_amount = $order_row["due"];

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

                                                    $order_row["orderStatus"] = $newStatus;
                                                }
                                            }
                                    ?>
                                            <tr>
                                                <td> <?= $order_row["orderUId"]; ?> </td>
                                                <td> <?= $order_row["invoiceNumber"]; ?> </td>
                                                <td> <?= $order_row["customerName"]; ?> </td>
                                                <td> <?= date('d-m-Y', strtotime($order_row["orderDate"])); ?> </td>
                                                <td> <?= $order_row["biller"]; ?> </td>
                                                <td> <?= $order_row["updater"]; ?> </td>
                                                <td> <?= $order_row["paymentType"]; ?> </td>
                                                <td> <?= $order_row["vat"]; ?>% </td>
                                                <td> <?= number_format($order_row["total"], 2); ?> </td>
                                                <td class="text-success"> <?= number_format($order_row["paid"], 2); ?> </td>
                                                <td class="text-danger"> <?= number_format($order_row["due"], 2); ?> </td>
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
                                                                Order Detail
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="edit-sales.php?invoiceNumber=<?= $invoice_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/edit.svg" class="me-2" alt="img">
                                                                Edit Order
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showPayment<?= $invoice_number; ?>">
                                                                <img src="assets/img/icons/dollar-square.svg" class="me-2" alt="img">
                                                                Show Payments
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createPayment<?= $order_row['invoiceNumber']; ?>">
                                                                <img src="assets/img/icons/plus-circle.svg" class="me-2" alt="img">
                                                                Update Payment
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="download-invoice.php?invoiceNumber=<?= $invoice_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/download.svg" class="me-2" alt="img">
                                                                Download PDF
                                                            </a>
                                                        </li>

                                                        <?php if ($order_row['orderStatus'] == 0): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmCancelOrder(<?= $order_uid; ?>)">
                                                                    <img src="assets/img/icons/cancel.svg" class="me-2" alt="img">
                                                                    Cancel Order
                                                                </button>
                                                            </li>

                                                        <?php elseif ($order_row['orderStatus'] == 2): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmReactivateOrder(<?= $order_uid; ?>)">
                                                                    <img src="assets/img/icons/refresh.svg" class="me-2" alt="img">
                                                                    Reactivate Order
                                                                </button>
                                                            </li>

                                                        <?php endif; ?>

                                                        <li>
                                                            <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $order_uid; ?>)">
                                                                <img src="assets/img/icons/delete1.svg" class="me-2" alt="img">
                                                                Delete Order
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

                                            <!-- Create Payment Modal -->
                                            <div class="modal fade" id="createPayment<?= $invoice_number; ?>" tabindex="-1" aria-labelledby="createPaymentModal" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="createPaymentModal<?= $invoice_number; ?>">Create Payment</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                        </div>
                                                        <form method="POST" action="" id="paymentForm<?= $invoice_number; ?>">
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Customer</label>
                                                                            <input type="text" class="form-control" value="<?= ($order_row['customerName']); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Invoice Number</label>
                                                                            <input type="text" class="form-control" value="<?= $order_row['invoiceNumber']; ?>" readonly>
                                                                            <input type="hidden" name="invoiceNumber" value="<?= $order_row['invoiceNumber']; ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Total Amount</label>
                                                                            <input type="text" class="form-control" id="totalAmount<?= $order_row['invoiceNumber']; ?>" value="<?= number_format($order_row['total'], 2); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Already Paid</label>
                                                                            <input type="text" class="form-control" id="alreadyPaid<?= $order_row['invoiceNumber']; ?>" value="<?= number_format($order_row['paid'], 2); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Remaining (Due)</label>
                                                                            <input type="text" class="form-control" id="remainingDue<?= $order_row['invoiceNumber']; ?>" value="<?= number_format($order_row['due'], 2); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Paying Amount</label>
                                                                            <input type="number" step="0.01" min="0" max="<?= $order_row['due']; ?>" class="form-control" name="payingAmount" id="payingAmount<?= $order_row['invoiceNumber']; ?>" value="" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Payment Type</label>
                                                                            <select class="form-control" name="paymentType" required>
                                                                                <option>Cash</option>
                                                                                <option>Credit Card</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" name="paymentBTN" class="btn btn-submit">Submit</button>
                                                                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- / Create Payment Modal -->

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
        // Function to confirm order deletion
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

        // Function to confirm order cancellation
        function confirmCancelOrder(orderUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, cancel!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cancelsale.php?id=' + orderUId;
                };
            });
        };

        // Function to reactivate order
        function confirmReactivateOrder(orderUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reactivate!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'reactivate-sale.php?id=' + orderUId;
                };
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);

            // Sweetalerts for delete and cancel
            const alerts = [{
                    param: 'status',
                    value: 'success',
                    title: 'Deleted!',
                    text: 'Order has been deleted successfully.'
                },
                {
                    param: 'status',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to delete the Order'
                },
                {
                    param: 'message',
                    value: 'cancelled',
                    title: 'Cancelled!',
                    text: 'Order has been cancelled successfully.'
                },
                {
                    param: 'message',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to cancel the Order'
                },
                {
                    param: 'response',
                    value: 'reactivated',
                    title: 'Reactivated!',
                    text: 'Order has been reactivated successfully.'
                },
                {
                    param: 'response',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to reactivate the Order'
                }
            ];

            alerts.forEach(alert => {
                const paramValue = urlParams.get(alert.param);
                if (paramValue === alert.value) {
                    Swal.fire({
                        title: alert.title,
                        text: alert.text,
                        timer: 3000,
                        showConfirmButton: true
                    }).then(() => {
                        // Remove the URL param after showing alert
                        const url = new URL(window.location.href);
                        url.searchParams.delete(alert.param);
                        window.history.replaceState({}, document.title, url.pathname + url.search);
                    });
                }
            });
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // For every payment form on the page
            document.querySelectorAll("form[id^='paymentForm']").forEach(function(form) {
                let invoiceNumber = form.id.replace("paymentForm", "");

                let payingInput = document.getElementById("payingAmount" + invoiceNumber);
                let alreadyPaidEl = document.getElementById("alreadyPaid" + invoiceNumber);
                let remainingEl = document.getElementById("remainingDue" + invoiceNumber);

                // Store initial values
                let initialPaid = parseFloat(alreadyPaidEl.value.replace(/,/g, "")) || 0;
                let initialDue = parseFloat(remainingEl.value.replace(/,/g, "")) || 0;

                payingInput.addEventListener("input", function() {
                    let payingAmount = parseFloat(payingInput.value) || 0;

                    // Prevent overpayment
                    if (payingAmount > initialDue) {
                        payingAmount = initialDue;
                        payingInput.value = payingAmount.toFixed(2);
                        Swal.fire({
                            title: 'Warning',
                            text: 'Overpayment not allowed!',
                            timer: 3000
                        });
                    } else if (payingAmount < 0) {
                        payingAmount = 0;
                        payingInput.value = payingAmount.toFixed(2);
                        Swal.fire({
                            title: 'Warning',
                            text: 'Pay amount cannot be negative!',
                            timer: 3000
                        });
                    }

                    // Calculate new values
                    let newPaid = initialPaid + payingAmount;
                    let newDue = initialDue - payingAmount;

                    // Update fields (formatted to 2 decimals)
                    alreadyPaidEl.value = newPaid.toFixed(2);
                    remainingEl.value = newDue.toFixed(2);
                });

                // Reset values if form is closed
                form.addEventListener("reset", function() {
                    alreadyPaidEl.value = initialPaid.toFixed(2);
                    remainingEl.value = initialDue.toFixed(2);
                });
            });
        });
    </script>

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
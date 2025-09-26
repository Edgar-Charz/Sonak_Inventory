<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id form session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// If payment button is clicked
if (isset($_POST['paymentBTN'])) {
    $invoiceNumber = $_POST['invoiceNumber'] ?? '';
    $payingAmount = isset($_POST['payingAmount']) ? floatval(str_replace(',', '', $_POST['payingAmount'])) : 0;
    $paymentType = $_POST['paymentType'] ?? '';

    $updatedBy = $user_id;

    // Validate inputs
    if (empty($invoiceNumber)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'Invoice number is required.',
                    icon: 'error',
                    timer: 5000,
                    timerProgressBar: true
                });
            });
        </script>";
        exit();
    }
    if ($payingAmount <= 0.00) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'Paying amount must be greater than 0.',
                    icon: 'error',
                    timer: 5000,
                    timerProgressBar: true
                });
            });
        </script>";
        exit();
    }
    if (empty($paymentType)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'Payment type is required.',
                    icon: 'error',
                    timer: 5000,
                    timerProgressBar: true
                });
            });
        </script>";
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Fetch current paid & due
        $stmt = $conn->prepare("SELECT orderPaidAmount, orderDueAmount, orderCustomerId FROM orders WHERE orderInvoiceNumber = ?");
        $stmt->bind_param("s", $invoiceNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        if (!$order) {
            throw new Exception("Order not found for invoice number: $invoiceNumber");
        }

        $currentPay = floatval($order['orderPaidAmount']);
        $currentDue = floatval($order['orderDueAmount']);
        $customerId = intval($order['orderCustomerId']);

        // Prevent overpayment
        if ($payingAmount > $currentDue) {
            $payingAmount = $currentDue;
        }

        // Calculate new values
        $newPay = $currentPay + $payingAmount;
        $newDue = $currentDue - $payingAmount;

        // Update order payment
        $update = $conn->prepare("UPDATE orders 
                                  SET orderPaidAmount = ?, orderDueAmount = ?, orderUpdatedBy = ?, updated_at = ?
                                  WHERE orderInvoiceNumber = ?");
        $update->bind_param("ddiss", $newPay, $newDue, $updatedBy, $current_time, $invoiceNumber);

        if (!$update->execute()) {
            throw new Exception("Failed to update payment: " . $conn->error);
        }
        $update->close();

        // Insert into transactions table
        $insert_transaction_stmt = $conn->prepare("INSERT INTO transactions 
            (transactionCustomerId, transactionInvoiceNumber, transactionPaymentType, 
             transactionPaidAmount, transactionDueAmount, transactionDate, 
             transactionCreatedAt, transactionUpdatedAt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_transaction_stmt->bind_param(
            "issddsss",
            $customerId,
            $invoiceNumber,
            $paymentType,
            $payingAmount,
            $newDue,
            $current_time,
            $current_time,
            $current_time
        );

        if (!$insert_transaction_stmt->execute()) {
            throw new Exception("Failed to record transaction: " . $conn->error);
        }
        $insert_transaction_stmt->close();

        // If fully paid, update order_details status
        if ($newDue == 0) {
            $updateDetails = $conn->prepare("UPDATE order_details SET orderDetailStatus = 1 WHERE orderDetailInvoiceNumber = ?");
            $updateDetails->bind_param("s", $invoiceNumber);
            if (!$updateDetails->execute()) {
                throw new Exception("Failed to update order details status: " . $conn->error);
            }
            $updateDetails->close();
        }

        // Commit transaction
        $conn->commit();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Success',
                    text: 'Payment updated and transaction recorded successfully',
                    icon: 'success',
                    timer: 5000,
                    timerProgressBar: true
                }).then(function() {
                    window.location.href = 'saleslist.php';
                });
            });
        </script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: '" . addslashes($e->getMessage()) . "',
                    icon: 'error',
                    timer: 5000,
                    timerProgressBar: true
                });
            });
        </script>";
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
    <style>
        .swal-wide {
            width: 600px !important;
            padding: 2rem;
        }

        .swal-title-lg {
            font-size: 1.75rem;
        }

        .swal-input-lg {
            font-size: 1.1rem;
            padding: 0.75rem;
        }

        .swal-btn-lg {
            font-size: 1rem;
            padding: 0.6rem 1.2rem;
        }
    </style>
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
                    <a class="dropdown-item" href="signout.php">Logout</a>
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
                                <!-- <li><a href="add-sales.php">Add Sales</a></li> -->
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
                                <!-- <li><a href="inventoryreport.php">Inventory Report</a></li> -->
                                <li><a href="salesreport.php">Sales Report</a></li>
                                <li><a href="sales_payment_report.php">Sales Payment Report</a></li>
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
                    <!-- <div class="page-btn">
                        <a href="add-sales.php" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img" class="me-1">Add Sales</a>
                    </div> -->
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
                            <!-- <div class="wordset">
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
                            </div> -->
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
                                        <th>Created By</th>
                                        <!-- <th>UpdatedBy</th> -->
                                        <!-- <th>PaymentType</th> -->
                                        <th>VAT(%)</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Due</th>
                                        <th class="text-center">Status</th>
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
                                                                        orders.orderInvoiceNumber = order_details.orderDetailInvoiceNumber
                                                                        AND orders.orderCustomerId = customers.customerId
                                                                        AND order_details.orderDetailProductId = products.productId
                                                                        AND orders.orderCreatedBy = u1.userId
                                                                        AND orders.orderUpdatedBy = u2.userId
                                                                    GROUP BY 
                                                                        orders.orderInvoiceNumber
                                                                    ORDER BY 
                                                                        orders.orderUId DESC;");
                                    if ($orders_query->num_rows > 0) {
                                        while ($order_row = $orders_query->fetch_assoc()) {
                                            $order_uid = $order_row["orderUId"];
                                            $invoice_number = $order_row["orderInvoiceNumber"];
                                            $currentStatus = $order_row["orderStatus"];
                                            $due_amount = $order_row["orderDueAmount"];

                                            if ($currentStatus != 2 && $currentStatus != 3) {
                                                $newStatus = $currentStatus;

                                                if ($due_amount == 0) {
                                                    $newStatus = 1;
                                                } else {
                                                    $newStatus = 0;
                                                }

                                                if ($newStatus != $currentStatus) {
                                                    $update_query = $conn->prepare("UPDATE orders SET orderStatus = ? WHERE orderInvoiceNumber = ?");
                                                    $update_query->bind_param("is", $newStatus, $invoice_number);
                                                    $update_query->execute();
                                                    $update_query->close();

                                                    $order_row["orderStatus"] = $newStatus;
                                                }
                                            }
                                    ?>
                                            <tr>
                                                <td> <?= $order_row["orderUId"]; ?> </td>
                                                <td> <?= $order_row["orderInvoiceNumber"]; ?> </td>
                                                <td> <?= $order_row["customerName"]; ?> </td>
                                                <td> <?= date('d-m-Y', strtotime($order_row["orderDate"])); ?> </td>
                                                <td> <?= $order_row["biller"]; ?> </td>
                                                <!-- <td> <?= $order_row["updater"]; ?> </td> -->
                                                <!-- <td> <?= $order_row["paymentType"]; ?> </td> -->
                                                <td> <?= $order_row["orderVat"]; ?>% </td>
                                                <td> <?= number_format($order_row["orderTotalAmount"], 2); ?> </td>
                                                <td class="text-success"> <?= number_format($order_row["orderPaidAmount"], 2); ?> </td>
                                                <td class="text-danger"> <?= number_format($order_row["orderDueAmount"], 2); ?> </td>
                                                <td class="text-center">
                                                    <?php if ($order_row["orderStatus"] == "0") : ?>
                                                        <span class="badges bg-lightyellow">Pending</span>
                                                    <?php elseif ($order_row["orderStatus"] == "1"): ?>
                                                        <span class="badges bg-lightgreen">Completed</span>
                                                    <?php elseif ($order_row["orderStatus"] == "2") : ?>
                                                        <span class="badges bg-lightgrey">Cancelled</span>
                                                    <?php else: ?>
                                                        <span class="badges bg-lightred">Deleted</span>
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
                                                        <!-- <li>
                                                            <a href="edit-sales.php?invoiceNumber=<?= $invoice_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/edit.svg" class="me-2" alt="img">
                                                                Edit Order
                                                            </a>
                                                        </li> -->

                                                        <li>
                                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showPayment<?= $invoice_number; ?>">
                                                                <img src="assets/img/icons/dollar-square.svg" class="me-2" alt="img">
                                                                Show Payments
                                                            </button>
                                                        </li>

                                                        <?php if ($order_row['orderStatus'] == 3): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showDeleteReason<?= $invoice_number; ?>">
                                                                    <img src="assets/img/icons/info-circle.svg" class="me-2" alt="img">
                                                                    Delete Reason
                                                                </button>
                                                            </li>

                                                        <?php elseif ($order_row['orderStatus'] == 0): ?>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createPayment<?= $order_row['orderInvoiceNumber']; ?>">
                                                                    <img src="assets/img/icons/plus-circle.svg" class="me-2" alt="img">
                                                                    Update Payment
                                                                </a>
                                                            </li>
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

                                                        <?php if ($order_row['orderStatus'] == 2): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $order_uid; ?>)">
                                                                    <img src="assets/img/icons/delete1.svg" class="me-2" alt="img">
                                                                    Delete Order
                                                                </button>
                                                            </li>
                                                        <?php endif; ?>

                                                    </ul>
                                                </td>
                                            </tr>


                                            <!-- View Payment Modal -->
                                            <div class="modal fade" id="showPayment<?= $invoice_number; ?>" tabindex="-1" aria-labelledby="showPaymentModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="showPaymentModalLabel<?= $invoice_number; ?>">Show Payments</h5>
                                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <!-- <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Date</label>
                                                                        <div class="input-groupicon">
                                                                            <p class="form-control"><?= $order_row["orderDate"]; ?></p>
                                                                            <div class="addonset">
                                                                                <img src="assets/img/icons/calendars.svg" alt="calendar">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div> -->

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Invoice Number</label>
                                                                        <p class="form-control"><?= $order_row["orderInvoiceNumber"]; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Total Amount</label>
                                                                        <p class="form-control"><?= number_format($order_row["orderTotalAmount"], 2); ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Paid Amount</label>
                                                                        <p class="form-control"><?= number_format($order_row["orderPaidAmount"], 2); ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-6 col-sm-12 mb-3">
                                                                    <div class="form-group">
                                                                        <label>Due Amount</label>
                                                                        <p class="form-control"><?= number_format($order_row["orderDueAmount"], 2); ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-12 col-sm-12 col-12 text-center">
                                                                    <div class="form-group">
                                                                        <label style="text-align: center; font-size: large;">Transactions</label>
                                                                        <?php
                                                                        $transactions_stmt = $conn->prepare("SELECT * FROM transactions WHERE transactionInvoiceNumber = ?");
                                                                        $transactions_stmt->bind_param("s", $invoice_number);
                                                                        $transactions_stmt->execute();
                                                                        $transactions_result = $transactions_stmt->get_result();

                                                                        if ($transactions_result->num_rows > 0) {
                                                                            $i = 1;
                                                                            echo '<div class="row border p-2 mb-2">';
                                                                            echo '<div class="col-1"><strong>#</strong></div>';
                                                                            echo '<div class="col-3"><strong>Paid Amount</strong></div>';
                                                                            echo '<div class="col-3"><strong>Due Amount</strong></div>';
                                                                            echo '<div class="col-3"><strong>Payment Type</strong></div>';
                                                                            echo '<div class="col-2"><strong>Payment Date</strong></div>';
                                                                            echo '</div>';

                                                                            while ($transaction = $transactions_result->fetch_assoc()) {

                                                                                echo '<div class="row border p-2 mb-1">';
                                                                                echo "<div class='col-1'>{$i}</div>";
                                                                                echo "<div class='col-3'>" . number_format($transaction['transactionPaidAmount'], 2) . "</div>";
                                                                                echo "<div class='col-3'>" . number_format($transaction['transactionDueAmount'], 2) . "</div>";
                                                                                echo "<div class='col-3'>{$transaction['transactionPaymentType']}</div>";
                                                                                echo "<div class='col-2'>" . date('d-m-Y', strtotime($transaction['transactionDate'])) . "</div>";
                                                                                echo '</div>';
                                                                                $i++;
                                                                            }
                                                                        } else {
                                                                            echo "<p class='text-center text-muted'>No transactions available</p>";
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /View Payment Modal -->

                                            <!-- Create Payment Modal -->
                                            <div class="modal fade" id="createPayment<?= $invoice_number; ?>" tabindex="-1" aria-labelledby="createPaymentModal" aria-hidden="true">
                                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="createPaymentModal<?= $invoice_number; ?>">Update Payment</h5>
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
                                                                            <input type="text" class="form-control" value="<?= $order_row['orderInvoiceNumber']; ?>" readonly>
                                                                            <input type="hidden" name="invoiceNumber" value="<?= $order_row['orderInvoiceNumber']; ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Total Amount</label>
                                                                            <input type="text" class="form-control" id="totalAmount<?= $order_row['orderInvoiceNumber']; ?>" value="<?= number_format($order_row['orderTotalAmount'], 2); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Already Paid</label>
                                                                            <input type="text" class="form-control" id="alreadyPaid<?= $order_row['orderInvoiceNumber']; ?>" value="<?= number_format($order_row['orderPaidAmount'], 2); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Remaining (Due)</label>
                                                                            <input type="text" class="form-control" id="remainingDue<?= $order_row['orderInvoiceNumber']; ?>" value="<?= number_format($order_row['orderDueAmount'], 2); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Paying Amount</label>
                                                                            <input type="number" step="0.01" min="0" max="<?= $order_row['orderDueAmount']; ?>" class="form-control" name="payingAmount" id="payingAmount<?= $order_row['orderInvoiceNumber']; ?>" value="" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Payment Type</label>
                                                                            <select class="form-control" name="paymentType" required>
                                                                                <option value="" selected disabled>Payment Type</option>
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

                                            <!-- Delete Reason Modal -->
                                            <div class="modal fade" id="showDeleteReason<?= $invoice_number; ?>" tabindex="-1" aria-labelledby="showDeleteReasonModal<?= $invoice_number; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content border-danger">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title" id="showDeleteReasonModal<?= $invoice_number; ?>">
                                                                <i class="fa fa-info-circle me-2"></i> Delete Reason
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="card shadow-sm border-0">
                                                                <div class="card-body p-3">
                                                                    <ul class="list-group list-group-flush">
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <span class="fw-semibold text-muted">
                                                                                <i class="bi bi-receipt me-2 text-primary"></i> Invoice Number
                                                                            </span>
                                                                            <span class="fw-bold text-primary"><?= $invoice_number; ?></span>
                                                                        </li>

                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <span class="fw-semibold text-muted">
                                                                                <i class="bi bi-person-circle me-2 text-success"></i> To
                                                                            </span>
                                                                            <span><?= $order_row['customerName']; ?></span>
                                                                        </li>

                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <span class="fw-semibold text-muted">
                                                                                <i class="bi bi-calendar-x me-2 text-danger"></i> Deleted On
                                                                            </span>
                                                                            <span class="text-muted"><?= date('d M, Y', strtotime($order_row['updated_at'])); ?></span>
                                                                        </li>

                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <span class="fw-semibold text-muted">
                                                                                <i class="bi bi-person-badge me-2 text-warning"></i> Deleted By
                                                                            </span>
                                                                            <span><?= $order_row['updater']; ?></span>
                                                                        </li>

                                                                        <!-- Reason in textarea -->
                                                                        <li class="list-group-item">
                                                                            <span class="fw-semibold text-muted d-block mb-2">
                                                                                <i class="bi bi-exclamation-triangle me-2 text-danger" data-bs-toggle="tooltip" title="Reason for deletion"></i>
                                                                                Reason For Deletion
                                                                            </span>
                                                                            <div class="bg-light border-start border-danger ps-3 py-2 rounded">
                                                                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                                                                <span class="text-dark"><?= ($order_row['orderDescription']); ?></span>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Close</button>
                                                            <a href="sales-details.php?invoiceNumber=<?= $invoice_number; ?>" class="btn btn-outline-primary">View Order Details</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /Delete Reason Modal -->

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
                // icon: 'warning',
                title: 'Delete Order',
                html: `
                        <label for="deleteReason" style="display:block; margin-bottom:8px;">Please provide a reason for deleting this order:</label>
                        <textarea id="deleteReason" class="swal2-textarea" placeholder="Enter reason..." rows="6" style="width:100%; resize:vertical;"></textarea>
                    `,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal-wide',
                    title: 'swal-title-lg',
                    confirmButton: 'swal-btn-lg',
                    cancelButton: 'swal-btn-lg'
                },
                preConfirm: () => {
                    const reason = document.getElementById('deleteReason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('Reason is required');
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'deletesale.php';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'orderUId';
                    idInput.value = orderUId;

                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'deleteReason';
                    reasonInput.value = result.value;

                    form.appendChild(idInput);
                    form.appendChild(reasonInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });

        }


        // Function to confirm order cancellation
        function confirmCancelOrder(orderUId) {
            Swal.fire({
                icon: 'warning',
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
                icon: 'warning',
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

            const status = urlParams.get('status');
            const message = urlParams.get('message');
            const response = urlParams.get('response');
            const msg = urlParams.get('msg');
            const errorMsg = urlParams.get("errorMsg");

            // Delete
            if (status === 'deleted') {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Order has been deleted successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => clearParam('status'));
            }

            if (status === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: errorMsg ? decodeURIComponent(errorMsg) : 'Failed to delete the Order.',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => {
                    clearParam('status');
                    clearParam('errorMsg');
                });
            }

            // Reactivate
            if (response === 'reactivated') {
                Swal.fire({
                    title: 'Reactivated!',
                    text: 'Sale has been reactivated successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => clearParam('response'));
            }

            if (response === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: errorMsg ? decodeURIComponent(errorMsg) : 'Failed to reactivate the Sale Order.',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('response'));
            }

            if (response == 'insufficient') {
                Swal.fire({
                    title: 'Error!',
                    text: msg ? decodeURIComponent(msg) : 'Stock not enough to complete the operation.',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('response'));
            }

            // Cancel
            if (message === 'cancelled') {
                Swal.fire({
                    title: 'Cancelled!',
                    text: 'Sale order has been cancelled successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }

            if (message === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: errorMsg ? decodeURIComponent(errorMsg) : 'Failed to cancel the Sale order.',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }

            // Helper to remove URL parameters
            function clearParam(param) {
                const url = new URL(window.location.href);
                url.searchParams.delete(param);
                window.history.replaceState({}, document.title, url.pathname + url.search);
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
                            icon: 'warning',
                            title: 'Warning',
                            text: 'Overpayment not allowed!'
                        });
                    } else if (payingAmount < 0) {
                        payingAmount = 0;
                        payingInput.value = payingAmount.toFixed(2);
                        Swal.fire({
                            icon: 'warning',
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
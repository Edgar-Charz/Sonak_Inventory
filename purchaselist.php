<?php
include "includes/db_connection.php";
include "includes/session.php";

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Purchase List</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">

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
                                <li><a href="purchaselist.php" class="active">Purchase List</a></li>
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
                        <h4>PURCHASE LIST</h4>
                        <h6>Manage your purchases</h6>
                    </div>
                    <div class="page-btn">
                        <a href="addpurchase.php" class="btn btn-added">
                            <img src="assets/img/icons/plus.svg" alt="img">Add New Purchases
                        </a>
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
                                    <div class="col-lg col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" class="datetimepicker cal-icon" placeholder="Choose Date">
                                        </div>
                                    </div>
                                    <div class="col-lg col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Reference">
                                        </div>
                                    </div>
                                    <div class="col-lg col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Choose Supplier</option>
                                                <option>Supplier</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Choose Status</option>
                                                <option>Inprogress</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Choose Payment Status</option>
                                                <option>Payment Status</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-sm-6 col-12">
                                        <div class="form-group">
                                            <a class="btn btn-filters ms-auto"><img src="assets/img/icons/search-whites.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>PurchaseNo.</th>
                                        <th>Date</th>
                                        <th>TrackingNo.</th>
                                        <th>Supplier</th>
                                        <th>Agent</th>
                                        <!-- <th>Product</th> -->
                                        <th>CreatedBy</th>
                                        <th>UpdatedBy</th>
                                        <th>Grand Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch all purchases 
                                    $purchases_query = $conn->query("SELECT 
                                                                                purchases.*, 
                                                                                purchase_details.*, 
                                                                                suppliers.supplierName, 
                                                                                agents.agentName, 
                                                                                products.productName, 
                                                                                u1.username AS creater,
                                                                                u2.username AS updater
                                                                            FROM purchases
                                                                            JOIN purchase_details ON purchases.purchaseNumber = purchase_details.purchaseNumber
                                                                            JOIN suppliers ON suppliers.supplierId = purchases.supplierId
                                                                            LEFT JOIN agents ON agents.agentId = purchase_details.agentId
                                                                            JOIN products ON products.productId = purchase_details.productId
                                                                            JOIN users AS u1 ON purchases.createdBy = u1.userId
                                                                            JOIN users AS u2 ON purchases.updatedBy = u2.userId
                                                                            GROUP BY purchases.purchaseNumber
                                                                            ORDER BY purchases.purchaseUId DESC;");
                                    if ($purchases_query->num_rows > 0) {
                                        while ($purchase_row = $purchases_query->fetch_assoc()) {
                                            $purchase_number = $purchase_row['purchaseNumber'];
                                            $purchase_uid = $purchase_row['purchaseUId'];
                                    ?>
                                            <tr>
                                                <td> <?= $purchase_row['purchaseUId']; ?> </td>
                                                <td> <?= $purchase_row['purchaseNumber']; ?> </td>
                                                <td> <?= date('d-m-Y', strtotime($purchase_row['purchaseDate'])); ?> </td>
                                                <td> <?= !empty($purchase_row['trackingNumber']) ? $purchase_row['trackingNumber'] : 'N/A'; ?> </td>
                                                <td> <?= $purchase_row['supplierName']; ?> </td>
                                                <td> <?= !empty($purchase_row['agentName']) ? $purchase_row['agentName'] : 'N/A'; ?> </td>
                                                <!-- <td> <?= $purchase_row['productName']; ?> </td> -->
                                                <td> <?= $purchase_row['creater']; ?> </td>
                                                <td> <?= $purchase_row['updater']; ?> </td>
                                                <td> <?= number_format($purchase_row['totalAmount'], 2); ?> </td>
                                                <td>
                                                    <?php if ($purchase_row['purchaseStatus'] == "0") : ?>
                                                        <span class="badges bg-lightyellow">Pending</span>
                                                    <?php elseif ($purchase_row['purchaseStatus'] == "1"): ?>
                                                        <span class="badges bg-lightgreen">Completed</span>
                                                    <?php else: ?>
                                                        <span class="badges bg-lightred">Cancelled</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                                                <i class="fa fa-ellipsis-v"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                <!-- View Button -->
                                                                <!-- <li>
                                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewPurchase<?= $purchase_number; ?>">
                                                                        <img src="assets/img/icons/eye.svg" alt="View" style="width: 16px; margin-right: 6px;">
                                                                        View
                                                                    </button>
                                                                </li> -->
                                                                <li>
                                                                    <a href="viewpurchase.php?purchaseNumber=<?= $purchase_number; ?>" class="dropdown-item">
                                                                        <img src="assets/img/icons/eye.svg" alt="View" style="width: 16px; margin-right: 6px;">
                                                                        View
                                                                    </a>
                                                                </li>

                                                                <!-- Edit Button -->
                                                                <li>
                                                                    <a href="editpurchase.php?purchaseNumber=<?= $purchase_number; ?>" class="dropdown-item">
                                                                        <img src="assets/img/icons/edit.svg" alt="Edit" style="width: 16px; margin-right: 6px;">
                                                                        Edit
                                                                    </a>
                                                                </li>

                                                                <?php if ($purchase_row['purchaseStatus'] == 0): ?>
                                                                    <li>
                                                                        <button type="button" class="dropdown-item" onclick="confirmCancelPurchase(<?= $purchase_uid; ?>)">
                                                                            <img src="assets/img/icons/cancel.svg" class="me-2" alt="img">
                                                                            Cancel
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        <button type="button" class="dropdown-item" onclick="confirmCompletePurchase(<?= $purchase_uid; ?>)">
                                                                            <img src="assets/img/icons/check.svg" class="me-2" alt="img">
                                                                            Complete
                                                                        </button>
                                                                    </li>

                                                                <?php elseif ($purchase_row['purchaseStatus'] == 2): ?>
                                                                    <li>
                                                                        <button type="button" class="dropdown-item" onclick="confirmReactivatePurchase(<?= $purchase_uid; ?>)">
                                                                            <img src="assets/img/icons/refresh.svg" class="me-2" alt="img">
                                                                            Reactivate
                                                                        </button>
                                                                    </li>

                                                                <?php endif; ?>

                                                                <!-- Delete Button -->
                                                                <li>
                                                                    <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $purchase_uid; ?>)">
                                                                        <img src="assets/img/icons/delete.svg" alt="Delete" style="width: 16px; margin-right: 6px;">
                                                                        Delete
                                                                    </button>
                                                                </li>

                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- View Purchase Modal -->
                                            <div class="modal fade" id="viewPurchase<?= $purchase_number; ?>" tabindex="-1" aria-labelledby="viewPurchaseModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="viewPurchaseModalLabel"><?= $purchase_row['purchaseNumber']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- <div class="row">
                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Purchase No.</label>
                                                                        <p class="form-control"><?= $purchase_row['purchaseNumber']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Supplier Name</label>
                                                                        <p class="form-control"><?= $purchase_row['supplierName']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Purchase Date </label>
                                                                        <div class="input-groupicon">
                                                                            <p class="form-control"><?= $purchase_row['purchaseDate']; ?></p>
                                                                            <div class="addonset">
                                                                                <img src="assets/img/icons/calendars.svg" alt="img">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Product Name</label>
                                                                        <p class="form-control"><?= $purchase_row['productName']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Product Size</label>
                                                                        <p class="form-control"><?= $purchase_row['productSize']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Quantity</label>
                                                                        <p class="form-control"><?= $purchase_row['quantity']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Agent</label>
                                                                        <p class="form-control"><?= !empty($purchase_row['agentName']) ? $purchase_row['agentName'] : 'N/A'; ?> </p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Tracking No.</label>
                                                                        <p class="form-control"><?= $purchase_row['trackingNumber']; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div> -->

                                                            <!-- Costs Row -->
                                                            <!-- <div class="row">
                                                                <h5 class="mb-4 fw-bold">Cost Breakdown</h5>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label for="unit_cost" class="form-label">Unit Cost</label>
                                                                        <p class="form-control"><?= $purchase_row['unitCost']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label for="rate" class="form-label">Rate</label>
                                                                        <p class="form-control"><?= $purchase_row['rate']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label for="discount_percent" class="form-label">Discount (%)</label>

                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label for="total_cost" class="form-label">Total Cost</label>
                                                                        <p class="form-control"><?= $purchase_row['totalCost']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label for="agent_transport_cost" class="form-label">Agent Transport Cost</label>
                                                                        <p class="form-control"><?= $purchase_row['agentTransportationCost']; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label for="total_amount" class="form-label fw-bold">Total Amount</label>
                                                                        <p class="form-control fw-bold"><?= $purchase_row['totalAmount']; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div> -->
                                                            <!-- /Costs Row -->

                                                            <!-- Tracking Details -->
                                                            <!-- <div class="row mb-4">
                                                                <div class="col-12">
                                                                    <h6 class="fw-bold mb-3">Tracking Details</h6>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>To Agent Date</label>
                                                                        <div class="input-groupicon">
                                                                            <input type="text" value="<?= $purchase_row['dateToAgentAbroadWarehouse']; ?>" class="datetimepicker">
                                                                            <div class="addonset">
                                                                                <img src="assets/img/icons/calendars.svg" alt="img">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Received In Tanzania Date</label>
                                                                        <div class="input-groupicon">
                                                                            <input type="text" value="<?= $purchase_row['dateReceivedByAgentInCountryWarehouse']; ?>" class="datetimepicker">
                                                                            <div class="addonset">
                                                                                <img src="assets/img/icons/calendars.svg" alt="img">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>At Sonak</label>
                                                                        <div class="input-groupicon">
                                                                            <input type="text" value="<?= $purchase_row['dateReceivedByCompany']; ?>" class="datetimepicker">
                                                                            <div class="addonset">
                                                                                <img src="assets/img/icons/calendars.svg" alt="img">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-3 col-sm-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>Purchase Status</label>
                                                                        <p class="form-control"><?= ($purchase_row['purchaseStatus'] === 1) ? 'Completed' : (($purchase_row['purchaseStatus'] === 2) ? 'Cancelled' : 'Pending'); ?>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div> -->
                                                            <!-- /Tracking Details -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

    <script>
        // Function to confirm purchase deletion 
        function confirmDelete(purchaseUId) {
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
                    window.location.href = 'deletepurchase.php?id=' + purchaseUId;
                }
            });
        }

        // Function to confirm purchase completion
        function confirmCompletePurchase(purchaseUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, complete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'complete_purchase.php?id=' + purchaseUId;
                }
            });
        };

        // Function to confirm purchase cancellation
        function confirmCancelPurchase(purchaseUId) {
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
                    window.location.href = 'cancel_purchase.php?id=' + purchaseUId;
                }
            });
        };

        // Function to confirm purchase reactivation
        function confirmReactivatePurchase(purchaseUId) {
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
                    window.location.href = 'reactivate_purchase.php?id=' + purchaseUId;
                }
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);

            // Sweetalerts for delete, cancel, complete and reactivation
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
                    value: 'completed',
                    title: 'Completed!',
                    text: 'Purchase completed successfully.'
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
                    text: 'Purchase has been reactivated successfully.'
                },
                {
                    param: 'response',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to reactivate the Purchase'
                },
                {
                    param: 'msg',
                    value: 'cancelled',
                    title: 'Cancelled!',
                    text: 'Purchase has been cancelled successfully.'
                },
                {
                    param: 'msg',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to cancel the Purchase'
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

        // Trigger SweetAlert messages after redirect
        // document.addEventListener('DOMContentLoaded', function() {
        //     const urlParams = new URLSearchParams(window.location.search);
        //     const status = urlParams.get('status');

        //     if (status === 'success') {
        //         Swal.fire({
        //             title: 'Deleted!',
        //             text: 'Purchase has been deleted successfully.',
        //             timer: 3000,
        //             showConfirmButton: true
        //         }).then(() => {
        //             const url = new URL(window.location.href);
        //             url.searchParams.delete('status');
        //             window.history.replaceState({}, document.title, url.pathname + url.search);
        //         });
        //     }
        //     if (status === 'error') {
        //         Swal.fire({
        //             title: 'Error!',
        //             text: 'Failed to delete the Purchase.',
        //             timer: 3000,
        //             showConfirmButton: true
        //         }).then(() => {
        //             const url = new URL(window.location.href);
        //             url.searchParams.delete('status');
        //             window.history.replaceState({}, document.title, url.pathname + url.search);
        //         });
        //     }
        // });
    </script>

    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>

    <!--Purchases Table  -->
    <script>
        $(document).ready(function() {
            if ($("#purchaseTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#purchaseTable")) {
                    $("#purchaseTable").DataTable({
                        destroy: true,
                        bFilter: true,
                        sDom: "fBtlpi",
                        pagingType: "numbers",
                        ordering: true,
                        language: {
                            search: " ",
                            sLengthMenu: "_MENU_",
                            searchPlaceholder: "Search Purchases...",
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
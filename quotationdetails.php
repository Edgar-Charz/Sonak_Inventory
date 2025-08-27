<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['referenceNumber'])) {
    $referenceNumber = $_GET['referenceNumber'];

    // Fetch quotation data
    $quotation_query = $conn->prepare("SELECT q.*, c.customerName 
                                     FROM quotations q 
                                     LEFT JOIN customers c ON q.customerId = c.customerId 
                                     WHERE q.referenceNumber = ? LIMIT 1");
    $quotation_query->bind_param("s", $referenceNumber);
    $quotation_query->execute();
    $quotation_result = $quotation_query->get_result();
    $quotation = $quotation_result->fetch_assoc();

    // Fetch quotation details
    $details_query = $conn->prepare("SELECT qd.*, p.productName 
                                   FROM quotation_details qd 
                                   JOIN products p ON qd.productId = p.productId 
                                   WHERE qd.referenceNumber = ? 
                                   ORDER BY qd.quotationDetailsId ASC");
    $details_query->bind_param("s", $referenceNumber);
    $details_query->execute();
    $details_result = $details_query->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Quotation Details</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/animate.css">

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
                                <li><a href="quotationList.php" class="active">Quotation List</a></li>
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
                        <h4>Quotation Details</h4>
                        <h6>View quotation details</h6>
                    </div>
                    <div class="page-btn">
                        <a href="quotationlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Quotations List</a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="card-sales-split">
                            <h2>Quotation Number: <?= ($referenceNumber ?? 'N/A') ?></h2>
                            <ul>
                                <li>
                                    <a href="editquotation.php?referenceNumber=<?= ($referenceNumber ?? '') ?>"><img src="assets/img/icons/edit.svg" alt="img"></a>
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
                                <tbody>
                                    <?php if ($quotation) { ?>
                                        <tr class="top">
                                            <td colspan="7" style="padding: 5px;vertical-align: top;">
                                                <table style="width: 100%;line-height: inherit;text-align: left;">
                                                    <tbody>
                                                        <tr>
                                                            <!-- Customer Info -->
                                                            <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                <h6 style="color:#7367F0;font-weight:600;line-height:35px;margin-bottom:10px;">Customer Info</h6>
                                                                <p style="font-size:15px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Name:</strong> <?= ($quotation['customerName'] ?? 'N/A') ?>
                                                                </p>
                                                            </td>
                                                            <!-- Quotation Info -->
                                                            <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                <h6 style="color:#7367F0;font-weight:600;line-height:35px;margin-bottom:10px;">Quotation Info</h6>
                                                                <p style="font-size:14px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Reference Number:</strong> <?= ($quotation['referenceNumber']) ?>
                                                                </p>
                                                                <p style="font-size:14px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Total Amount:</strong> <?= number_format($quotation['totalAmount'], 2) ?>
                                                                </p>
                                                                <p style="font-size:14px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Quotation Date:</strong> <?= date('d/m/Y', strtotime($quotation['quotationDate'])) ?>
                                                                </p>
                                                                <p style="font-size:14px;color:#000;font-weight:400;margin:0;">
                                                                    <strong>Status:</strong> <?= ($quotation['quotationStatus'] == 1) ? 'Paid' : 'Unpaid' ?>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="heading" style="background: #F3F2F7;">
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">
                                                S/N
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">
                                                Product Name
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">
                                                Quantity
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">
                                                Unit Price
                                            </td>
                                            <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">
                                                Total Cost
                                            </td>
                                        </tr>
                                        <?php
                                        $sn = 1;
                                        while ($detail = $details_result->fetch_assoc()) {
                                        ?>
                                            <tr class="details" style="border-bottom:1px solid #E9ECEF ;">
                                                <td style="padding: 10px;vertical-align: top;">
                                                    <?= $sn++ ?>
                                                </td>
                                                <td style="padding: 10px;vertical-align: top;">
                                                    <?= ($detail['productName']) ?>
                                                </td>
                                                <td style="padding: 10px;vertical-align: top;">
                                                    <?= $detail['quantity'] ?>
                                                </td>
                                                <td style="padding: 10px;vertical-align: top;">
                                                    <?= number_format($detail['unitPrice'], 2) ?>
                                                </td>
                                                <td style="padding: 10px;vertical-align: top;">
                                                    <?= number_format($detail['subTotal'], 2) ?>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="4" style="padding: 10px; text-align: right; font-weight: bold;">Subtotal:</td>
                                            <td style="padding: 10px;"><?= number_format($quotation['subTotal'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="padding: 10px; text-align: right; font-weight: bold;">VAT (<?= $quotation['taxPercentage'] ?>%):</td>
                                            <td style="padding: 10px;"><?= number_format($quotation['taxAmount'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="padding: 10px; text-align: right; font-weight: bold;">Discount (<?= $quotation['discountPercentage'] ?>%):</td>
                                            <td style="padding: 10px;">-<?= number_format($quotation['discountAmount'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="padding: 10px; text-align: right; font-weight: bold;">Shipping:</td>
                                            <td style="padding: 10px;"><?= number_format($quotation['shippingAmount'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="padding: 10px; text-align: right; font-weight: bold;">Grand Total:</td>
                                            <td style="padding: 10px;"><?= number_format($quotation['totalAmount'], 2) ?></td>
                                        </tr>
                                    <?php } else { ?>
                                        <tr><td colspan="5">No quotation details found.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea class="form-control" readonly><?= ($quotation['note'] ?? '') ?></textarea>
                                </div>
                            </div>
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

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>
</body>

</html>
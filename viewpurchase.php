<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['purchaseNumber'])) {
    echo "Invalid Purchase Number";
    exit;
}

// Get user id from session
$user_id = $_SESSION["id"];

// Get purchase number
$purchase_number = $_GET['purchaseNumber'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dreams POS | View Purchase</title>

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
                        <h4>Purchase Details</h4>
                        <h6>View purchase details</h6>
                    </div>
                    <div class="page-btn">
                        <a href="purchaselist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Purchases List</a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="card-sales-split">
                            <h2>Purchase Number : <?= ($purchase_number); ?></h2>
                            <ul>
                                <li>
                                    <a href="editpurchase.php?purchaseNumber=<?= $purchase_number; ?>"><img src="assets/img/icons/edit.svg" alt="img"></a>
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



                        <?php
                        // Get purchase details
                        $purchase_query = $conn->prepare("SELECT 
                                                purchases.*, 
                                                suppliers.supplierId, suppliers.supplierName, suppliers.supplierEmail, suppliers.supplierPhone, suppliers.supplierAccountNumber, suppliers.supplierAccountHolder,
                                                u1.username AS purchaser,
                                                u2.username AS updater
                                            FROM purchases
                                            JOIN suppliers ON purchases.supplierId = suppliers.supplierId
                                            JOIN users AS u1 ON purchases.createdBy = u1.userId
                                            JOIN users AS u2 ON purchases.updatedBy = u2.userId
                                            WHERE purchases.purchaseNumber = ?");
                        $purchase_query->bind_param("s", $purchase_number);
                        $purchase_query->execute();
                        $purchase_result = $purchase_query->get_result();

                        if ($purchase_result->num_rows > 0) {
                            $purchase_row = $purchase_result->fetch_assoc();
                        ?>
                            <!-- Supplier Information Table -->
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Supplier Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Name:</strong></td>
                                                            <td><?= $purchase_row['supplierName']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Email:</strong></td>
                                                            <td>
                                                                <a href="mailto:<?= $purchase_row['supplierEmail']; ?>">
                                                                    <?= $purchase_row['supplierEmail']; ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Phone:</strong></td>
                                                            <td>
                                                                <a href="tel:<?= $purchase_row['supplierPhone']; ?>">
                                                                    <?= $purchase_row['supplierPhone']; ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Acc. No:</strong></td>
                                                            <td><?= !empty($purchase_row['supplierAccountNumber']) ? $purchase_row['supplierAccountNumber'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Acc. Name:</strong></td>
                                                            <td><?= !empty($purchase_row['supplierAccountHolder']) ? $purchase_row['supplierAccountHolder'] : 'N/A'; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Purchase Statistics Table -->
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Purchase Statistics</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Purchaser:</strong></td>
                                                            <td><?= $purchase_row['purchaser']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Updated By:</strong></td>
                                                            <td><?= $purchase_row['updater']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Purchase Date:</strong></td>
                                                            <td><?= date('d/m/Y', strtotime($purchase_row['purchaseDate'])); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Created At:</strong></td>
                                                            <td><?= date('d/m/Y H:i:s', strtotime($purchase_row['created_at'])); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Updated At:</strong></td>
                                                            <td><?= date('d/m/Y H:i:s', strtotime($purchase_row['updated_at'])); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Agent Information Table -->

                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Agent Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <tbody>
                                                        <?php
                                                        // Get agent information from purchase details
                                                        $agent_query = $conn->prepare("SELECT DISTINCT 
                                                                            agents.agentName, 
                                                                            agents.agentEmail, 
                                                                            agents.agentPhone,
                                                                            purchase_details.trackingNumber,
                                                                            purchase_details.agentTransportationCost
                                                                        FROM purchase_details
                                                                        LEFT JOIN agents ON purchase_details.agentId = agents.agentId 
                                                                        WHERE purchase_details.purchaseNumber = ? AND purchase_details.agentId IS NOT NULL 
                                                                        LIMIT 1");
                                                        $agent_query->bind_param("s", $purchase_number);
                                                        $agent_query->execute();
                                                        $agent_result = $agent_query->get_result();
                                                        $agent_info = $agent_result->fetch_assoc();
                                                        ?>
                                                        <tr>
                                                            <td><strong>Agent Name:</strong></td>
                                                            <td><?= !empty($agent_info['agentName']) ? $agent_info['agentName'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Agent Email:</strong></td>
                                                            <td>
                                                                <?php if (!empty($agent_info['agentEmail'])): ?>
                                                                    <a href="mailto:<?= $agent_info['agentEmail']; ?>">
                                                                        <?= $agent_info['agentEmail']; ?>
                                                                    </a>
                                                                <?php else: ?>
                                                                    N/A
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Agent Phone:</strong></td>
                                                            <td>
                                                                <?php if (!empty($agent_info['agentPhone'])): ?>
                                                                    <a href="tel:<?= $agent_info['agentPhone']; ?>">
                                                                        <?= $agent_info['agentPhone']; ?>
                                                                    </a>
                                                                <?php else: ?>
                                                                    N/A
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Tracking Number:</strong></td>
                                                            <td><?= !empty($agent_info['trackingNumber']) ? $agent_info['trackingNumber'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Transportation Cost:</strong></td>
                                                            <td><?= !empty($agent_info['agentTransportationCost']) ? number_format($agent_info['agentTransportationCost'], 2) : 'N/A'; ?></td>
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
                                                        <thead>
                                                            <th>Total Products</th>
                                                            <th>Total Amount</th>
                                                            <th>Agent Transportation Cost</th>
                                                            <th>Account No.</th>
                                                            <th>Bank</th>
                                                            <th>Status</th>
                                                        </thead>
                                                        <tr>
                                                            <td><?= number_format($purchase_row['totalProducts']); ?></td>
                                                            <td class="text-primary"><strong><?= number_format($purchase_row['totalAmount'], 2); ?></strong></td>
                                                            <td>
                                                                <?php
                                                                $transport_query = $conn->prepare("SELECT (agentTransportationCost) as totalTransportCost FROM purchase_details WHERE purchaseNumber = ?");
                                                                $transport_query->bind_param("s", $purchase_number);
                                                                $transport_query->execute();
                                                                $transport_result = $transport_query->get_result();
                                                                $transport_row = $transport_result->fetch_assoc();
                                                                ?>
                                                                <strong>Tsh: <?= number_format($transport_row['totalTransportCost'] ?? 0, 2); ?></strong>
                                                            </td>
                                                            <td><?= !empty($purchase_row['supplierAccountNumber']) ? $purchase_row['supplierAccountNumber'] : 'N/A'; ?></td>
                                                            <td><?= !empty($purchase_row['bankName']) ? $purchase_row['bankName'] : 'N/A'; ?></td>
                                                            <td><?= $purchase_row['purchaseStatus'] == 0 ? 'Pending' : ($purchase_row['purchaseStatus'] == 1 ? 'Completed' : 'Cancelled'); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Purchase Details Table -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Purchase Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>S/N</th>
                                                            <th>Product Name</th>
                                                            <th>Size</th>
                                                            <th>Quantity</th>
                                                            <th>Unit Cost</th>
                                                            <th>Rate</th>
                                                            <th>Total Cost</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        // Get purchase details and products using prepared statement
                                                        $details_query = $conn->prepare("SELECT 
                                                                        purchase_details.*, 
                                                                        products.productName,
                                                                        agents.agentName
                                                                    FROM purchase_details
                                                                    JOIN products ON purchase_details.productId = products.productId
                                                                    LEFT JOIN agents ON purchase_details.agentId = agents.agentId
                                                                    WHERE purchase_details.purchaseNumber = ?
                                                                    ORDER BY purchase_details.purchaseDetailsId ASC");
                                                        $details_query->bind_param("s", $purchase_number);
                                                        $details_query->execute();
                                                        $details_result = $details_query->get_result();

                                                        if ($details_result && $details_result->num_rows > 0) {
                                                            $sn = 1;
                                                            while ($detail = $details_result->fetch_assoc()) {
                                                        ?>
                                                                <tr>
                                                                    <td style="padding: 10px;vertical-align: top;"><?= $sn++; ?></td>
                                                                    <td style="padding: 10px;vertical-align: top; display: flex;align-items: center;">
                                                                        <?= $detail['productName']; ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= !empty($detail['productSize']) ? $detail['productSize'] : 'N/A'; ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= number_format($detail['quantity']); ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= number_format($detail['unitCost'], 2); ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= number_format($detail['rate']); ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= number_format($detail['totalCost'], 2); ?>
                                                                    </td>
                                                                </tr>
                                                        <?php
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No purchase details found.</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php
                        } else {
                            echo "<p>No purchase found with the provided purchase number.</p>";
                        }
                        ?>

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
<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['supplierId'])) {
    echo "Invalid Supplier ID";
    exit;
}
// Get user id from session
$user_id = $_SESSION["id"];

// Get supplier id
$supplier_id = $_GET['supplierId'];

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
                                <li><a href="supplierreport.php" class="active">Supplier Report</a></li>
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
                        <h4>Supplier Report Details</h4>
                        <h6>View Supplier Report Details</h6>
                    </div>
                    <div class="page-btn">
                        <a href="supplierreport.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Supplier Reports</a>
                    </div>
                </div>
                <?php
                // Filter conditions
                $whereClause = "suppliers.supplierId = '$supplier_id'";

                if (!empty($_GET['from_date'])) {
                    $fromDate = date('Y-m-d', strtotime($_GET['from_date']));
                    $whereClause .= " AND purchases.purchaseDate >= '$fromDate'";
                }
                if (!empty($_GET['to_date'])) {
                    $toDate = date('Y-m-d', strtotime($_GET['to_date']));
                    $whereClause .= " AND purchases.purchaseDate <= '$toDate'";
                }

                // Get supplier information and purchase statistics
                $supplier_query = $conn->query("SELECT 
                                    suppliers.*,
                                    COUNT(purchases.purchaseNumber) AS total_purchases,
                                    SUM(CASE WHEN purchases.purchaseStatus = 1 THEN 1 ELSE 0 END) AS completed_purchases,
                                    SUM(CASE WHEN purchases.purchaseStatus = 0 THEN 1 ELSE 0 END) AS pending_purchases,
                                    SUM(CASE WHEN purchases.purchaseStatus = 2 THEN 1 ELSE 0 END) AS cancelled_purchases,
                                    SUM(purchases.totalAmount) AS total_amount
                                FROM suppliers 
                                LEFT JOIN purchases ON suppliers.supplierId = purchases.supplierId
                                WHERE $whereClause
                                GROUP BY suppliers.supplierId");

                if ($supplier_query->num_rows > 0) {
                    $supplier = $supplier_query->fetch_assoc();
                ?>

                    <div class="row">
                        <!-- Customer Information Table -->
                        <div class="col-lg-6">
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
                                                    <td><?= ($supplier['supplierName']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>
                                                        <a href="mailto:<?= $supplier['supplierEmail']; ?>">
                                                            <?= ($supplier['supplierEmail']); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Phone:</strong></td>
                                                    <td>
                                                        <a href="tel:<?= $supplier['supplierPhone']; ?>">
                                                            <?= ($supplier['supplierPhone']); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Address:</strong></td>
                                                    <td><?= ($supplier['supplierAddress']); ?></td>
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
                                    <h5 class="card-title">Purchase Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Total Purchases:</strong></td>
                                                    <td><span class="badge bg-primary" style="font-size: 14px;"><?= $supplier['total_purchases']; ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Completed:</strong></td>
                                                    <td><span class="badge bg-success"><?= $supplier['completed_purchases']; ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pending:</strong></td>
                                                    <td><span class="badge bg-warning"><?= $supplier['pending_purchases']; ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cancelled:</strong></td>
                                                    <td><span class="badge bg-danger"><?= $supplier['cancelled_purchases']; ?></span></td>
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
                                                    <td class="text-primary"><strong>Tsh: <?= number_format($supplier['total_amount'], 2); ?></strong></td>
                                                    <!-- <td><strong>Total Paid:</strong></td>
                                                    <td class="text-success"><strong>Tsh: <?= number_format($supplier['total_paid'], 2); ?></strong></td>
                                                    <td><strong>Total Due:</strong></td>
                                                    <td class="text-danger"><strong>Tsh: <?= number_format($supplier['total_due'], 2); ?></strong></td> -->
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
                                    <h5 class="card-title">Purchase History</h5>
                                    <?php
                                    if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
                                        echo "<span style='color: #212529; font-weight: 500;'>
                                                <i class='fas fa-calendar-alt me-1'></i> From: <strong>" . ($_GET['from_date']) . "</strong> 
                                                To: <strong>" . ($_GET['to_date']) . "</strong>
                                             </span>";
                                    } elseif (!empty($_GET['from_date'])) {
                                        echo "<span style='color: #212529; font-weight: 500;'>
                                                <i class='fas fa-calendar-alt me-1'></i> From: <strong>" . ($_GET['from_date']) . "</strong>
                                              </span>";
                                    } elseif (!empty($_GET['to_date'])) {
                                        echo "<span style='color: #212529; font-weight: 500;'>
                                                    <i class='fas fa-calendar-alt me-1'></i> Up To: <strong>" . ($_GET['to_date']) . "</strong>
                                              </span>";
                                    }
                                    ?>
                                </div>
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
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0" id="supplierReportDetailsTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Purchase No.</th>
                                                    <th>Date</th>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                    <th>Purchased Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Filter conditions
                                                $whereClause = "purchases.supplierId = '$supplier_id'";

                                                if (!empty($_GET['from_date'])) {
                                                    $fromDate = date('Y-m-d', strtotime($_GET['from_date']));
                                                    $whereClause .= " AND purchases.purchaseDate >= '$fromDate'";
                                                }
                                                if (!empty($_GET['to_date'])) {
                                                    $toDate = date('Y-m-d', strtotime($_GET['to_date']));
                                                    $whereClause .= " AND purchases.purchaseDate <= '$toDate'";
                                                }

                                                // Get purchase details and products
                                                $details_query = $conn->query("SELECT 
                                                                purchases.*,
                                                                purchase_details.*, 
                                                                products.productName,
                                                                suppliers.supplierId
                                                            FROM purchases
                                                            JOIN suppliers ON purchases.supplierId = suppliers.supplierId
                                                            JOIN purchase_details ON purchases.purchaseNumber = purchase_details.purchaseNumber
                                                            JOIN products ON purchase_details.productId = products.productId
                                                            WHERE $whereClause
                                                            ORDER BY purchases.purchaseNumber DESC");
                                                $sn = 1;
                                                while ($detail = $details_query->fetch_assoc()) {
                                                    // Determine status badge
                                                    $statusBadge = '';
                                                    switch ($detail['purchaseStatus']) {
                                                        case 0:
                                                            $statusBadge = '<span class="badges bg-warning">Pending</span>';
                                                            break;
                                                        case 1:
                                                            $statusBadge = '<span class="badges bg-success">Completed</span>';
                                                            break;
                                                        case 2:
                                                            $statusBadge = '<span class="badges bg-danger">Cancelled</span>';
                                                            break;
                                                    }
                                                ?>
                                                    <tr>
                                                        <td><?= $sn++; ?></td>
                                                        <td><?= $detail['purchaseNumber']; ?></td>
                                                        <td><?= date('M d, Y', strtotime($detail['purchaseDate'])); ?></td>
                                                        <td><?= ($detail['productName']); ?></td>
                                                        <td><?= $detail['quantity']; ?></td>
                                                        <td><strong><?= number_format($detail['totalCost'], 2); ?></strong></td>
                                                        <td><?= $statusBadge; ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="viewpurchase.php?purchaseNumber=<?= $detail['purchaseNumber']; ?>" class="btn btn-outline-primary btn-sm" title="View Details">
                                                                    <i class="fas fa-eye text-dark">
                                                                        View
                                                                    </i> 
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>

                                                <?php if ($details_query->num_rows == 0): ?>
                                                    <tr>
                                                        <td colspan="13" class="text-center py-4">
                                                            <p class="text-muted">No purchases found for this supplier.</p>
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
                    echo '<div class="alert alert-warning">Supplier not found.</div>';
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

        <script>
            $(document).ready(function() {
                if ($("#supplierReportDetailsTable").length > 0) {
                    if (!$.fn.DataTable.isDataTable("#supplierReportDetailsTable")) {
                        $("#supplierReportDetailsTable").DataTable({
                            destroy: true,
                            bFilter: true,
                            sDom: "fBtlpi",
                            pagingType: "numbers",
                            ordering: true,
                            language: {
                                search: " ",
                                sLengthMenu: "_MENU_",
                                searchPlaceholder: "Search...",
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
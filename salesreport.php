<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Fetch customer name for display if customer_id is set
$customerName = '';
if (!empty($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];
    $customerStmt = $conn->prepare("SELECT customerName FROM customers WHERE customerId = ? AND customerStatus = 1");
    $customerStmt->bind_param("i", $customerId);
    $customerStmt->execute();
    $customerResult = $customerStmt->get_result();
    if ($customerResult->num_rows > 0) {
        $customerName = $customerResult->fetch_assoc()['customerName'];
    }
    $customerStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Sales Report</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">

    <link rel="stylesheet" href="assets/css/animate.css">

    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">

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
                                <li><a href="salesreport.php" class="active">Sales Report</a></li>
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
                        <h4>Sales Report</h4>
                        <h6>Manage your Sales Report</h6>
                    </div>
                </div>

                <?php if (!empty($_GET['from_date']) || !empty($_GET['to_date']) || !empty($_GET['customer_id'])): ?>
                    <div class="card mt-3 border-info shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-info fw-semibold">
                                    <i class="bi bi-funnel-fill me-1"></i> Applied Filters
                                </h6>
                                <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            </div>
                            <ul class="mb-0 ps-0" style="list-style: none;">
                                <?php if (!empty($_GET['from_date'])): ?>
                                    <li><strong>From:</strong> <?= $_GET['from_date']; ?></li>
                                <?php endif; ?>

                                <?php if (!empty($_GET['to_date'])): ?>
                                    <li><strong>To:</strong> <?= $_GET['to_date']; ?></li>
                                <?php endif; ?>

                                <?php if (!empty($customerName)): ?>
                                    <li><strong>Customer:</strong> <?= $customerName; ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

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
                                        <a href="download_sales_report.php?
                                            from_date=<?= urlencode($_GET['from_date'] ?? '') ?>&
                                            to_date=<?= urlencode($_GET['to_date'] ?? '') ?>&
                                            customer_id=<?= urlencode($_GET['customer_id'] ?? '') ?>"
                                            title="Download PDF">
                                            <img src="assets/img/icons/pdf.svg" alt="PDF Icon">
                                        </a>
                                    </li>
                                    <!-- <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
                                    </li>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="print"><img src="assets/img/icons/printer.svg" alt="img"></a>
                                    </li> -->
                                </ul>
                            </div>
                        </div>

                        <?php
                        // Handle filter inputs 
                        $conditions = ["orders.orderStatus = 1"];
                        $params = [];
                        $types = "";

                        if (!empty($_GET['from_date'])) {
                            $fromDate = date('Y-m-d', strtotime($_GET['from_date']));
                            $conditions[] = "orders.orderDate >= ?";
                            $params[] = $fromDate;
                            $types .= "s";
                        }
                        if (!empty($_GET['to_date'])) {
                            $toDate = date('Y-m-d', strtotime($_GET['to_date']));
                            $conditions[] = "orders.orderDate <= ?";
                            $params[] = $toDate;
                            $types .= "s";
                        }
                        if (!empty($_GET['customer_id'])) {
                            $customerId = $_GET['customer_id'];
                            $conditions[] = "orders.orderCustomerId = ?";
                            $params[] = $customerId;
                            $types .= "i";
                        }

                        $whereClause = implode(" AND ", $conditions);
                        $query = "SELECT
                                        products.productId AS 'Product ID',
                                        products.productName AS 'Product Name',
                                        SUM(order_details.orderDetailTotalCost) AS 'Sold Amount',
                                        SUM(order_details.orderDetailQuantity) AS 'Sold QTY',
                                        products.productQuantity AS 'Instock QTY'
                                    FROM
                                        order_details, products, orders
                                    WHERE order_details.orderDetailProductId = products.productId
                                        AND order_details.orderDetailInvoiceNumber = orders.orderInvoiceNumber
                                        AND $whereClause
                                    GROUP BY 
                                            products.productId, products.productName, products.productQuantity
                                    ORDER BY 
                                            SUM(order_details.orderDetailTotalCost) DESC";

                        $stmt = $conn->prepare($query);
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $sn = 0;
                        ?>
                        <form method="GET" action="salesreport.php">
                            <div class="card" id="filter_inputs">
                                <div class="card-body pb-0">
                                    <div class="row">
                                        <div class="col-lg-2 col-sm-6 col-12">
                                            <div class="form-group">
                                                <div class="input-groupicon">
                                                    <input type="text" name="from_date" value="<?= ($_GET['from_date'] ?? '') ?>" class="datetimepicker" placeholder="From Date">
                                                    <div class="addonset">
                                                        <img src="assets/img/icons/calendars.svg" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-6 col-12">
                                            <div class="form-group">
                                                <div class="input-groupicon">
                                                    <input type="text" name="to_date" value="<?= ($_GET['to_date'] ?? '') ?>" class="datetimepicker" placeholder="To Date">
                                                    <div class="addonset">
                                                        <img src="assets/img/icons/calendars.svg" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-6 col-12">
                                            <div class="form-group">
                                                <select name="customer_id" class="form-select">
                                                    <option value="">Select Customer</option>
                                                    <?php
                                                    $customerQuery = $conn->query("SELECT customerId, customerName 
                                                                           FROM customers 
                                                                           WHERE customerStatus = 1");
                                                    while ($customer = $customerQuery->fetch_assoc()) {
                                                        $selected = ($_GET['customer_id'] ?? '') == $customer['customerId'] ? 'selected' : '';
                                                        echo "<option value='" . $customer['customerId'] . "' $selected>" . ($customer['customerName']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-6 col-12 ms-auto">
                                            <div class="form-group  d-flex align-items-center justify-content-end gap-2">
                                                <button type="submit" class="btn btn-filters ms-auto">
                                                    <img src="assets/img/icons/search-whites.svg" alt="img">
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive mt-4">
                            <table class="table" id="saleReportTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">S/N</th>
                                        <th class="text-center">Product Name</th>
                                        <th class="text-center">Sold Amount</th>
                                        <th class="text-center">Sold QTY</th>
                                        <th class="text-center">Instock QTY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $sn++;
                                            echo "<tr>
                                            <td class='text-center'>" . ($sn) . "</td>
                                            <td class='text-center'>" . ($row['Product Name']) . "</td>
                                            <td class='text-center'>" . number_format($row['Sold Amount'], 2) . "</td>
                                            <td class='text-center'>" . number_format($row['Sold QTY']) . "</td>
                                            <td class='text-center'>" . number_format($row['Instock QTY']) . "</td>
                                         </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No records found.</td></tr>";
                                    }
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
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

    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>

    <script>
        $(document).ready(function() {
            if ($("#saleReportTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#saleReportTable")) {
                    $("#saleReportTable").DataTable({
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
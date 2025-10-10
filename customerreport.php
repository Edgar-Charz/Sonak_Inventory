<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone settings
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Customer Report</title>

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
                                <li><a href="salesreport.php">Sales Report</a></li>
                                <li><a href="sales_payment_report.php">Sales Payment Report</a></li>
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
                        <h6>Manage your Customer Report</h6>
                    </div>
                </div>

                <?php if (!empty($_GET['from_date']) || !empty($_GET['to_date'])): ?>
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
                                    <!-- <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
                                    </li> -->
                                    <!-- <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
                                    </li>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="print"><img src="assets/img/icons/printer.svg" alt="img"></a>
                                    </li> -->
                                </ul>
                            </div>
                        </div>

                        <!-- Customer Report -->
                        <?php
                        // Filter
                        $conditions = [];
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

                        $whereClause = !empty($conditions) ? implode(" AND ", $conditions) : "1=1";

                        // Query
                        $customer_report_stmt = $conn->prepare("SELECT 
                                                                            customers.customerId AS `customer_id`,
                                                                            customers.customerName AS `customer_name`,
                                                                            SUM(CASE WHEN orders.orderStatus IN (0,1) THEN orders.orderTotalAmount ELSE 0 END) AS `Amount`,
                                                                            SUM(CASE WHEN orders.orderStatus IN (0,1) THEN transactions_sum.total_paid ELSE 0 END) AS `Paid`,
                                                                            SUM(CASE WHEN orders.orderStatus IN (0,1) THEN (orders.orderTotalAmount - IFNULL(transactions_sum.total_paid,0)) ELSE 0 END) AS `due_amount`,

                                                                            -- Separate counts for each order status
                                                                            SUM(CASE WHEN orders.orderStatus = 0 THEN 1 ELSE 0 END) AS `Pending Orders`,
                                                                            SUM(CASE WHEN orders.orderStatus = 1 THEN 1 ELSE 0 END) AS `Completed Orders`,
                                                                            SUM(CASE WHEN orders.orderStatus = 2 THEN 1 ELSE 0 END) AS `Cancelled Orders`,
                                                                            SUM(CASE WHEN orders.orderStatus = 3 THEN 1 ELSE 0 END) AS `Deleted Orders`,

                                                                            CASE  
                                                                                WHEN SUM(CASE WHEN orders.orderStatus IN (0,1) THEN 1 ELSE 0 END) = 0 THEN 'No Orders'
                                                                                WHEN SUM(CASE WHEN orders.orderStatus IN (0,1) THEN (orders.orderTotalAmount - IFNULL(transactions_sum.total_paid,0)) ELSE 0 END) = 0 
                                                                                    AND SUM(CASE WHEN orders.orderStatus IN (0,1) THEN transactions_sum.total_paid ELSE 0 END) > 0 THEN 'Fully Paid'
                                                                                WHEN SUM(CASE WHEN orders.orderStatus IN (0,1) THEN transactions_sum.total_paid ELSE 0 END) = 0 THEN 'Unpaid'
                                                                                ELSE 'Partially Paid'
                                                                            END AS `Payment Status`
                                                                        FROM 
                                                                            customers 
                                                                        LEFT JOIN 
                                                                            orders ON orders.orderCustomerId = customers.customerId
                                                                        LEFT JOIN (
                                                                            SELECT transactionInvoiceNumber, SUM(transactionPaidAmount) AS total_paid
                                                                            FROM transactions
                                                                            GROUP BY transactionInvoiceNumber
                                                                        ) AS transactions_sum ON transactions_sum.transactionInvoiceNumber = orders.orderInvoiceNumber
                                                                        WHERE 
                                                                            $whereClause
                                                                        GROUP BY 
                                                                            customers.customerId, customers.customerName;");
                        if (!empty($params)) {
                            $customer_report_stmt->bind_param($types, ...$params);
                        }
                        $customer_report_stmt->execute();
                        $customer_report_result = $customer_report_stmt->get_result();
                        $sn = 0;
                        ?>

                        <form method="GET" action="customerreport.php">
                            <div class="card" id="filter_inputs">
                                <div class="card-body pb-0">
                                    <div class="row">
                                        <div class="col-lg-2 col-sm-6 col-12">
                                            <div class="form-group">
                                                <div class="input-groupicon">
                                                    <input type="text" name="from_date" value="<?= $_GET['from_date'] ?? '' ?>" placeholder="From Date" class="datetimepicker">
                                                    <div class="addonset">
                                                        <img src="assets/img/icons/calendars.svg" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-6 col-12">
                                            <div class="form-group">
                                                <div class="input-groupicon">
                                                    <input type="text" name="to_date" value="<?= $_GET['to_date'] ?? '' ?>" placeholder="To Date" class="datetimepicker">
                                                    <div class="addonset">
                                                        <img src="assets/img/icons/calendars.svg" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 col-sm-6 col-12 ms-auto">
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

                        <div class="table-responsive">
                            <table class="table" id="customerReportTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer Name </th>
                                        <th class="text-center">Sold Amount</th>
                                        <th class="text-center">Paid</th>
                                        <th class="text-center">Due Amount</th>
                                        <th class="text-center">Orders Status</th>
                                        <th class="text-center">Payment Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($customer_report_result->num_rows > 0) {
                                        while ($customer_report = $customer_report_result->fetch_assoc()) {
                                            $customer_id = $customer_report['customer_id'];
                                            $sn++;
                                    ?>
                                            <tr>
                                                <td><?= $sn; ?></td>
                                                <td><?= $customer_report['customer_name']; ?></td>
                                                <td class="text-center"><?= number_format($customer_report['Amount'], 2); ?></td>
                                                <td class="text-success text-center"><?= number_format($customer_report['Paid'], 2); ?></td>
                                                <td class="text-danger text-center"><?= number_format($customer_report['due_amount'], 2); ?></td>

                                                <td class="text-center">
                                                    <span class="badges bg-lightgreen"><?= $customer_report['Completed Orders']; ?> Completed</span>
                                                    <span class="badges bg-lightyellow"><?= $customer_report['Pending Orders']; ?> Pending</span>
                                                    <span class="badges bg-lightgrey"><?= $customer_report['Cancelled Orders']; ?> Cancelled</span>
                                                    <span class="badges bg-lightred"><?= $customer_report['Deleted Orders'] ?> Deleted</span>
                                                </td>

                                                <?php
                                                $paymentStatus = $customer_report['Payment Status'];
                                                switch ($paymentStatus) {
                                                    case 'Fully Paid':
                                                        $badgeClass = 'bg-success';
                                                        break;
                                                    case 'Unpaid':
                                                        $badgeClass = 'bg-danger';
                                                        break;
                                                    case 'Partially Paid':
                                                        $badgeClass = 'bg-info';
                                                        break;
                                                    default:
                                                        $badgeClass = 'bg-secondary';
                                                }
                                                ?>
                                                <td class="text-center"> <span class="badges <?= $badgeClass; ?>"><?= $paymentStatus; ?></span></td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class='btn-group btn-group-sm'>
                                                            <a href='customer_report_details.php?customerId=<?= $customer_id ?>&from_date=<?= urlencode($_GET['from_date'] ?? '') ?>&to_date=<?= urlencode($_GET['to_date'] ?? '') ?>' class='btn btn-outline-primary btn-sm' title='View Report'>
                                                                <i class='fas fa-eye text-dark'>
                                                                    View
                                                                </i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>No records found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="searchpart">
            <div class="searchcontent">
                <div class="searchhead">
                    <h3>Search </h3>
                    <a id="closesearch"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
                </div>
                <div class="searchcontents">
                    <div class="searchparts">
                        <input type="text" placeholder="search here">
                        <a class="btn btn-searchs">Search</a>
                    </div>
                    <div class="recentsearch">
                        <h2>Recent Search</h2>
                        <ul>
                            <li>
                                <h6><i class="fa fa-search me-2"></i> Settings</h6>
                            </li>
                            <li>
                                <h6><i class="fa fa-search me-2"></i> Report</h6>
                            </li>
                            <li>
                                <h6><i class="fa fa-search me-2"></i> Invoice</h6>
                            </li>
                            <li>
                                <h6><i class="fa fa-search me-2"></i> Sales</h6>
                            </li>
                        </ul>
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
            if ($("#customerReportTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#customerReportTable")) {
                    $("#customerReportTable").DataTable({
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
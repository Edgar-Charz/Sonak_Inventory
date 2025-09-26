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


    // Fetch sales counts
    $totalSalesQuery = $conn->query("SELECT COUNT(*) as count FROM orders");
    $totalSales = $totalSalesQuery->fetch_assoc()['count'];

    $pendingSalesQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 0");
    $pendingSales = $pendingSalesQuery->fetch_assoc()['count'];

    $completedSalesQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 1");
    $completedSales = $completedSalesQuery->fetch_assoc()['count'];

    $cancelledSalesQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 2");
    $cancelledSales = $cancelledSalesQuery->fetch_assoc()['count'];

    $deletedSalesQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 3");
    $deletedSales = $deletedSalesQuery->fetch_assoc()['count'];
    ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
     <title>Sonak Inventory | Sales Payment Report</title>

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
                                 <li><a href="sales_payment_report.php" class="active">Sales Payment Report</a></li>
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
                         <h4>Sales Payment Report</h4>
                         <h6>Manage your Sales Payment Report</h6>
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

                 <?php
                    // Handle filter inputs with prepared statements
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
                    if (!empty($_GET['customer_id'])) {
                        $customerId = $_GET['customer_id'];
                        $conditions[] = "orders.orderCustomerId = ?";
                        $params[] = $customerId;
                        $types .= "i";
                    }

                    // $whereClause = implode(" AND ", $conditions);
                    $whereClause = !empty($conditions) ? implode(" AND ", $conditions) : "1=1";
                    $query = "SELECT
                                    orders.*,
                                    customers.customerName
                                FROM
                                    orders
                                JOIN
                                    customers ON orders.orderCustomerId = customers.customerId
                                WHERE 
                                    $whereClause
                                ORDER BY
                                        orders.orderUId DESC";
                    $stmt = $conn->prepare($query);
                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $sn = 0;
                    ?>

                 <div class="card">
                     <div class="card-body">
                         <div class="row text-center">
                             <div class="col-md-3 mb-3">
                                 <div class="bg-light border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Total Sales</h6>
                                     <h3 class="text-primary" id="totalSales"><?= $totalSales; ?></h3>
                                 </div>
                             </div>
                             <div class="col-md-3 mb-3">
                                 <div class="bg-warning-subtle border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Pending Sales</h6>
                                     <h3 class="text-warning" id="pendingSales"><?= $pendingSales; ?></h3>
                                 </div>
                             </div>
                             <div class="col-md-2 mb-3">
                                 <div class="bg-success-subtle border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Completed Sales</h6>
                                     <h3 class="text-success" id="completedSales"><?= $completedSales; ?></h3>
                                 </div>
                             </div>
                             <div class="col-md-2 mb-3">
                                 <div class="bg-danger-subtle border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Cancelled Sales</h6>
                                     <h3 class="text-danger" id="cancelledSales"><?= $cancelledSales; ?></h3>
                                 </div>
                             </div>
                             <div class="col-md-2 mb-3">
                                 <div class="bg-danger-subtle border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Deleted Sales</h6>
                                     <h3 class="text-danger" id="deletedSales"><?= $deletedSales; ?></h3>
                                 </div>
                             </div>
                         </div>
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
                                 <!-- <div class="search-input">
                                     <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                                 </div> -->
                             </div>
                             <div class="wordset">
                                 <ul>
                                     <!-- <li>
                                         <a data-bs-toggle="tooltip" data-bs-placement="top" title="pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
                                     </li>
                                     <li>
                                         <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
                                     </li>
                                     <li>
                                         <a data-bs-toggle="tooltip" data-bs-placement="top" title="print"><img src="assets/img/icons/printer.svg" alt="img"></a>
                                     </li> -->
                                 </ul>
                             </div>
                         </div>

                         <form method="GET" action="sales_payment_report.php">
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
                                                        $customerQuery = $conn->query("SELECT customerId, customerName FROM customers WHERE customerStatus = 1");
                                                        while ($customer = $customerQuery->fetch_assoc()) {
                                                            $selected = (isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['customerId']) ? 'selected' : '';
                                                            echo "<option value='" . ($customer['customerId']) . "' $selected>" . ($customer['customerName']) . "</option>";
                                                        }
                                                        ?>
                                                 </select>
                                             </div>
                                         </div>
                                         <div class="col-lg-2 col-sm-6 col-12 ms-auto">
                                             <div class="form-group d-flex align-items-center justify-content-end gap-2">
                                                 <button type="submit" class="btn btn-filters">
                                                     <img src="assets/img/icons/search-whites.svg" alt="Search">
                                                 </button>
                                             </div>
                                         </div>

                                     </div>
                                 </div>
                             </div>
                         </form>
                         <div class="table-responsive">
                             <table class="table" id="salesPaymentReportTable">
                                 <thead>
                                     <tr>
                                         <th>#</th>
                                         <th>Invoice Number</th>
                                         <th>Customer Name</th>
                                         <th>Due Date</th>
                                         <th>Amount</th>
                                         <th>Paid</th>
                                         <th>Amount Due</th>
                                         <th>Status</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $dueDate = date('Y-m-d', strtotime($row['orderDate'] . ' +30 days'));
                                                $amount = number_format($row['orderTotalAmount'], 2);
                                                $paid = number_format($row['orderPaidAmount'], 2);
                                                $amountDue = number_format($row['orderDueAmount'], 2);
                                                $today = date('Y-m-d');

                                                if ($row['orderStatus'] == 3) {
                                                    $status = 'Deleted';
                                                    $statusClass = 'bg-lightred';
                                                } else if ($row['orderStatus'] == 2) {
                                                    $status = 'Cancelled';
                                                    $statusClass = 'bg-lightgrey';
                                                } else if ($row['orderStatus'] == 1 && $row['orderDueAmount'] == 0) {
                                                    $status = 'Fully Paid';
                                                    $statusClass = 'bg-lightgreen';
                                                } elseif ($row['orderDueAmount'] > 0 && $row['orderPaidAmount'] == 0) {
                                                    $status = 'UnPaid';
                                                    $statusClass = 'bg-lightgrey';
                                                } elseif ($row['orderDueAmount'] > 0 && $row['orderPaidAmount'] > 0 && $today <= $dueDate) {
                                                    $status = 'Partially Paid';
                                                    $statusClass = 'bg-lightyellow';
                                                } elseif ($row['orderDueAmount'] > 0 && $today > $dueDate) {
                                                    $status = 'Overdue';
                                                    $statusClass = 'bg-lightorange';
                                                } else {
                                                    $status = 'Unknown';
                                                    $statusClass = 'bg-lightgrey';
                                                }
                                                $sn++;

                                                echo "<tr>
                                                <td>" . $sn . "</td>
                                            <td>" . $row['orderInvoiceNumber'] . "</td>
                                            <td>" . $row['customerName'] . "</td>
                                            <td>" . date('d-m-Y', strtotime($dueDate)) . "</td>
                                            <td>" . $amount . "</td>
                                            <td class='text-success'>" . $paid . "</td>
                                            <td class='text-danger'>" . $amountDue . "</td>
                                            <td><span class='badges $statusClass'>" . $status . "</span></td>
                                          </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>No records found</td></tr>";
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
     <!-- <script>
         document.getElementById("searchInput").addEventListener("keyup", function() {
             let searchValue = this.value.toLowerCase();
             let rows = document.querySelectorAll("#salesPaymentReportTable tbody tr");

             let total = 0,
                 pending = 0,
                 completed = 0,
                 cancelled = 0;

             rows.forEach(row => {
                 let rowText = row.innerText.toLowerCase();

                 if (rowText.includes(searchValue)) {
                     row.style.display = "";

                     total++;

                     // Get status text
                     let status = row.querySelector("td:last-child span").innerText.toLowerCase();

                     if (status.includes("unpaid") || status.includes("partially paid") || status.includes("overdue")) {
                         pending++;
                     } else if (status.includes("fully paid")) {
                         completed++;
                     } else if (status.includes("cancelled")) {
                         cancelled++;
                     }
                 } else {
                     row.style.display = "none";
                 }
             });

             // Update totals
             document.getElementById("totalSales").innerText = total;
             document.getElementById("pendingSales").innerText = pending;
             document.getElementById("completedSales").innerText = completed;
             document.getElementById("cancelledSales").innerText = cancelled;
         });
     </script> -->


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
             if ($("#salesPaymentReportTable").length > 0) {
                 if (!$.fn.DataTable.isDataTable("#salesPaymentReportTable")) {
                     let table = $("#salesPaymentReportTable").DataTable({
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
                             $(".dataTables_filter input").attr("id", "searchInput");
                         }
                     });

                     // Listen to search, pagination, and sorting
                     table.on("draw", function() {
                         let total = 0,
                             pending = 0,
                             completed = 0,
                             cancelled = 0;
                         deleted = 0;

                         // Only visible rows after search/pagination
                         table.rows({
                             search: "applied"
                         }).every(function() {
                             total++;
                             let status = $(this.node()).find("td:last-child span").text().toLowerCase();

                             if (status.includes("unpaid") || status.includes("partially paid") || status.includes("overdue")) {
                                 pending++;
                             } else if (status.includes("fully paid")) {
                                 completed++;
                             } else if (status.includes("cancelled")) {
                                 cancelled++;
                             } else if (status.includes("deleted")) {
                                 deleted++;
                             }
                         });

                         // Update counters
                         $("#totalSales").text(total);
                         $("#pendingSales").text(pending);
                         $("#completedSales").text(completed);
                         $("#cancelledSales").text(cancelled);
                         $("#deletedSales").text(deleted);
                     });

                     // Trigger once on page load
                     table.draw();
                 }
             }
         });
     </script>

 </body>

 </html>
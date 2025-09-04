 <?php
    include 'includes/db_connection.php';
    include 'includes/session.php';

    // Fetch invoice counts
    $totalInvoicesQuery = $conn->query("SELECT COUNT(*) as count FROM orders");
    $totalInvoices = $totalInvoicesQuery->fetch_assoc()['count'];

    $pendingInvoicesQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 0");
    $pendingInvoices = $pendingInvoicesQuery->fetch_assoc()['count'];

    $completedInvoicesQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 1");
    $completedInvoices = $completedInvoicesQuery->fetch_assoc()['count'];

    $cancelledInvoicesQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 2");
    $cancelledInvoices = $cancelledInvoicesQuery->fetch_assoc()['count'];
    ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
     <title>Sonak Inventory | Invoice Report</title>

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
                                 <li><a href="invoicereport.php" class="active">Invoice Report</a></li>
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
                         <h4>Invoice Report</h4>
                         <h6>Manage your Invoice Report</h6>
                     </div>
                 </div>

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
                        $conditions[] = "orders.customerId = ?";
                        $params[] = $customerId;
                        $types .= "i";
                    }

                    // $whereClause = implode(" AND ", $conditions);
                    $whereClause = !empty($conditions) ? implode(" AND ", $conditions) : "1=1";
                    $query = "
                                    SELECT
                                        orders.*,
                                        customers.customerName
                                    FROM
                                        orders
                                    JOIN
                                        customers ON orders.customerId = customers.customerId
                                    WHERE $whereClause
                                    ORDER BY
                                        orders.orderUId DESC
                                ";
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
                                     <h6 class="card-title text-muted">Total Invoices</h6>
                                     <h3 class="text-primary"><?= $totalInvoices; ?></h3>
                                 </div>
                             </div>
                             <div class="col-md-3 mb-3">
                                 <div class="bg-warning-subtle border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Pending Invoices</h6>
                                     <h3 class="text-warning"><?= $pendingInvoices; ?></h3>
                                 </div>
                             </div>
                             <div class="col-md-3 mb-3">
                                 <div class="bg-success-subtle border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Completed Invoices</h6>
                                     <h3 class="text-success"><?= $completedInvoices; ?></h3>
                                 </div>
                             </div>
                             <div class="col-md-3 mb-3">
                                 <div class="bg-danger-subtle border rounded p-3 shadow-sm">
                                     <h6 class="card-title text-muted">Cancelled Invoices</h6>
                                     <h3 class="text-danger"><?= $cancelledInvoices; ?></h3>
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

                         <form method="GET" action="invoicereport.php">
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
                                                            $selected = ($_GET['customer_id'] ?? '') == $customer['customerId'] ? 'selected' : '';
                                                            echo "<option value='" . $customer['customerId'] . "' $selected>" . ($customer['customerName']) . "</option>";
                                                        }
                                                        ?>
                                                 </select>
                                             </div>
                                         </div>
                                         <div class="col-lg-1 col-sm-6 col-12 ms-auto">
                                             <div class="form-group">
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
                             <table class="table" id="invoiceReportTable">
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
                                                $dueDate = date('d-m-Y', strtotime($row['orderDate'] . ' +30 days'));
                                                $amount = number_format($row['total'], 2);
                                                $paid = number_format($row['paid'], 2);
                                                $amountDue = number_format($row['due'], 2);
                                                $today = date('Y-m-d');

                                                if ($row['orderStatus'] == 2) {
                                                    $status = 'Cancelled';
                                                    $statusClass = 'bg-lightred';
                                                } else if ($row['orderStatus'] == 1 && $row['due'] == 0) {
                                                    $status = 'Fully Paid';
                                                    $statusClass = 'bg-lightgreen';
                                                } elseif ($row['due'] > 0 && $row['paid'] == 0) {
                                                    $status = 'UnPaid';
                                                    $statusClass = 'bg-lightred';
                                                } elseif ($row['due'] > 0 && $row['paid'] > 0 && $today <= $dueDate) {
                                                    $status = 'Partially Paid';
                                                    $statusClass = 'bg-lightyellow';
                                                } elseif ($row['due'] > 0 && $today > $dueDate) {
                                                    $status = 'Overdue';
                                                    $statusClass = 'bg-lightorange';
                                                } else {
                                                    $status = 'Unknown';
                                                    $statusClass = 'bg-lightgray';
                                                }
                                                $sn++;

                                                echo "<tr>
                                                <td>" . $sn . "</td>
                                            <td>" . $row['invoiceNumber'] . "</td>
                                            <td>" . $row['customerName'] . "</td>
                                            <td>" . $dueDate . "</td>
                                            <td>" . $amount . "</td>
                                            <td>" . $paid . "</td>
                                            <td>" . $amountDue . "</td>
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
     <!-- <script>
            $(document).ready(function() {
                $('#invoiceReportTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "info": true
                });

                // Validate date inputs
                $('form').submit(function(e) {
                    let fromDate = $('input[name="from_date"]').val();
                    let toDate = $('input[name="to_date"]').val();
                    if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Error',
                            text: 'From Date cannot be after To Date',
                            timer: 3000
                        });
                    }
                });
            });
        </script> -->
     <script>
         $(document).ready(function() {
             if ($("#invoiceReportTable").length > 0) {
                 if (!$.fn.DataTable.isDataTable("#invoiceReportTable")) {
                     $("#invoiceReportTable").DataTable({
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
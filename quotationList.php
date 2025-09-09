<?php
include("includes/db_connection.php");
include("includes/session.php");

// Get user id from session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Quotation List</title>

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
                        <h4>Quotation List</h4>
                        <h6>Manage your Quotations</h6>
                    </div>
                    <div class="page-btn">
                        <a href="addquotation.php" class="btn btn-added">
                            <img src="assets/img/icons/plus.svg" alt="img" class="me-2"> Add Quotation
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
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" class="datetimepicker cal-icon" placeholder="Choose Date">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Reference ">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Choose Customer</option>
                                                <option>Customer1</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Choose Status</option>
                                                <option>Inprogress</option>
                                                <option>Complete</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-sm-6 col-12 ms-auto">
                                        <div class="form-group">
                                            <a class="btn btn-filters ms-auto"><img src="assets/img/icons/search-whites.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="quotationTable">
                                <thead>
                                    <tr>
                                        <th>SNo</th>
                                        <th>Refer.No</th>
                                        <th>Custmer Name</th>
                                        <th>Biller</th>
                                        <th>Date</th>
                                        <th>Tax(%)</th>
                                        <th>Discount(%)</th>
                                        <th>Grand Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch all quotations data from the database
                                    $quotations_query = $conn->query("SELECT 
                                                        quotations.*, 
                                                        quotation_details.*, 
                                                        customers.customerName, 
                                                        products.productName,
                                                        u1.username AS biller,
                                                        u2.username AS updater
                                                    FROM 
                                                        quotations, 
                                                        quotation_details, 
                                                        customers, 
                                                        products, 
                                                        users AS u1, 
                                                        users AS u2
                                                    WHERE 
                                                        quotations.referenceNumber = quotation_details.referenceNumber
                                                        AND quotations.customerId = customers.customerId
                                                        AND quotation_details.productId = products.productId
                                                        AND quotations.createdBy = u1.userId
                                                        AND quotations.updatedBy = u2.userId
                                                    GROUP BY 
                                                        quotations.referenceNumber
                                                    ORDER BY 
                                                        quotations.quotationUId DESC;");
                                    if ($quotations_query->num_rows > 0) {
                                        while ($quotation_row = $quotations_query->fetch_assoc()) {
                                            $quotation_uid = $quotation_row["quotationUId"];
                                            $reference_number = $quotation_row["referenceNumber"];
                                    ?>

                                            <tr>
                                                <td> <?= $quotation_uid; ?> </td>
                                                <td> <?= $reference_number; ?> </td>
                                                <td> <?= $quotation_row["customerName"]; ?> </td>
                                                <td> <?= $quotation_row["biller"]; ?> </td>
                                                <td> <?= date('d-m-Y', strtotime($quotation_row["quotationDate"])); ?> </td>
                                                <td> <?= $quotation_row["taxPercentage"]; ?>% </td>
                                                <td> <?= $quotation_row["discountPercentage"]; ?>% </td>
                                                <td> <?= number_format($quotation_row["totalAmount"], 2); ?> </td>
                                                <td>
                                                    <?php if ($quotation_row["quotationStatus"] == "0") : ?>
                                                        <span class="badges bg-lightgreen">Sent</span>
                                                    <?php elseif ($quotation_row["quotationStatus"] == "1"): ?>
                                                        <span class="badges bg-lightyellow">Approved</span>
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
                                                            <a href="quotationdetails.php?referenceNumber=<?= $reference_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/eye1.svg" class="me-2" alt="img">
                                                                Quotation Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="editquotation.php?referenceNumber=<?= $reference_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/edit.svg" class="me-2" alt="img">
                                                                Edit Quotation
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="download-quotation.php?referenceNumber=<?= $reference_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/download.svg" class="me-2" alt="img">
                                                                Download PDF
                                                            </a>
                                                        </li>

                                                        <?php if ($quotation_row["quotationStatus"] == "0"): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmApproveQuotation(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/check.svg" class="me-2" alt="img">
                                                                    Approve
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmCancelQuotation(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/cancel.svg" class="me-2" alt="img">
                                                                    Cancel
                                                                </button>
                                                            </li>

                                                        <?php elseif ($quotation_row['quotationStatus'] == '2'): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmReactivateQuotation(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/refresh.svg" class="me-2" alt="img">
                                                                    Reactivate
                                                                </button>
                                                            </li>

                                                        <?php endif; ?>
                                                        <li>
                                                            <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $quotation_uid; ?>)">
                                                                <img src="assets/img/icons/delete1.svg" class="me-2" alt="img">
                                                                Delete
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
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
        // Function to confirm deletion
        function confirmDelete(quotationUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deletequotation.php?id=' + quotationUId;
                }
            });
        };

        // Confirm cancel quotation
        function confirmCancelQuotation(quotationUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cancel_quotation.php?id=' + quotationUId;
                }
            });
        };

        // Confirm approve quotation
        function confirmApproveQuotation(quotationUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'approve_quotation.php?id=' + quotationUId;
                }
            });
        };

        // Confirm reactivation quotation
        function confirmReactivateQuotation(quotationUId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reactivate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'reactivate_quotation.php?id=' + quotationUId;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);

            // Sweetalerts for delete, cancel, approve and reactivation
            const alerts = [{
                    param: 'status',
                    value: 'success',
                    title: 'Deleted!',
                    text: 'Quotation deleted successfully.'
                },
                {
                    param: 'status',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to delete the Quotation'
                },
                {
                    param: 'message',
                    value: 'approved',
                    title: 'Approved!',
                    text: 'Quotation approved successfully.'
                },
                {
                    param: 'message',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to cancel the Quotation'
                },
                {
                    param: 'message',
                    value: 'invalid',
                    title: 'Error!',
                    text: 'Invalid Quotation UID'
                },
                {
                    param: 'message',
                    value: 'notfound',
                    title: 'Error!',
                    text: 'Quotation Not Found'
                },
                {
                    param: 'response',
                    value: 'reactivated',
                    title: 'Reactivated!',
                    text: 'Quotation has been reactivated successfully.'
                },
                {
                    param: 'response',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to reactivate the Quotation'
                },
                {
                    param: 'msg',
                    value: 'cancelled',
                    title: 'Cancelled!',
                    text: 'Quotation has been cancelled successfully.'
                },
                {
                    param: 'msg',
                    value: 'error',
                    title: 'Error!',
                    text: 'Failed to cancel the Quotation'
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

    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>

    <!-- Quotations Table  -->
    <script>
        $(document).ready(function() {
            if ($("#quotationTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#quotationTable")) {
                    $("#quotationTable").DataTable({
                        destroy: true,
                        bFilter: true,
                        sDom: "fBtlpi",
                        pagingType: "numbers",
                        ordering: true,
                        language: {
                            search: " ",
                            sLengthMenu: "_MENU_",
                            searchPlaceholder: "Search Quotation...",
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
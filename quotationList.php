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
                                        <th>Reference #</th>
                                        <th>Custmer Name</th>
                                        <th>Created By</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Tax(%)</th>
                                        <th class="text-center">Discount(%)</th>
                                        <th class="text-center">Grand Total</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
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
                                                        quotations.quotationReferenceNumber = quotation_details.quotationDetailReferenceNumber
                                                        AND quotations.quotationCustomerId = customers.customerId
                                                        AND quotation_details.quotationDetailProductId = products.productId
                                                        AND quotations.quotationCreatedBy = u1.userId
                                                        AND quotations.quotationUpdatedBy = u2.userId
                                                    GROUP BY 
                                                        quotations.quotationReferenceNumber
                                                    ORDER BY 
                                                        quotations.quotationUId DESC;");
                                    if ($quotations_query->num_rows > 0) {
                                        while ($quotation_row = $quotations_query->fetch_assoc()) {
                                            $quotation_uid = $quotation_row["quotationUId"];
                                            $reference_number = $quotation_row["quotationReferenceNumber"];
                                    ?>

                                            <tr>
                                                <td> <?= $quotation_uid; ?> </td>
                                                <td> <?= $reference_number; ?> </td>
                                                <td> <?= $quotation_row["customerName"]; ?> </td>
                                                <td> <?= $quotation_row["biller"]; ?> </td>
                                                <td class="text-center"> <?= date('d-m-Y', strtotime($quotation_row["quotationDate"])); ?> </td>
                                                <td class="text-center"> <?= $quotation_row["quotationTaxPercentage"]; ?>% </td>
                                                <td class="text-center"> <?= $quotation_row["quotationDiscountPercentage"]; ?>% </td>
                                                <td class="text-center"> <?= number_format($quotation_row["quotationTotalAmount"], 2); ?> </td>
                                                <td class="text-center">
                                                    <?php if ($quotation_row["quotationStatus"] == "0") : ?>
                                                        <span class="badges bg-lightgreen">Sent</span>
                                                    <?php elseif ($quotation_row["quotationStatus"] == "1"): ?>
                                                        <span class="badges bg-lightyellow">Approved</span>
                                                    <?php elseif ($quotation_row["quotationStatus"] == "2"): ?>
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
                                                            <a href="quotationdetails.php?referenceNumber=<?= $reference_number; ?>" class="dropdown-item">
                                                                <img src="assets/img/icons/eye1.svg" class="me-2" alt="View">
                                                                Quotation Details
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a href="download_invoice.php?
                                                                <?php if ($quotation_row['quotationStatus'] == '1') { ?>
                                                                    referenceNumber=<?= $reference_number; ?>&
                                                                    approverId=<?= $quotation_row['quotationUpdatedBy']; ?>
                                                                <?php } else { ?>
                                                                    referenceNumber=<?= $reference_number; ?>
                                                                <?php } ?>
                                                                " class="dropdown-item">
                                                                <img src="assets/img/icons/download.svg" class="me-2" alt="Download">
                                                                Download PDF
                                                            </a>
                                                        </li>

                                                        <?php if ($quotation_row['quotationStatus'] == "0"): ?>
                                                            <!-- Pending -->
                                                            <li>
                                                                <a href="editquotation.php?referenceNumber=<?= $reference_number; ?>" class="dropdown-item">
                                                                    <img src="assets/img/icons/edit.svg" class="me-2" alt="Edit">
                                                                    Edit Quotation
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmApproveQuotation(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/check.svg" class="me-2" alt="Approve">
                                                                    Approve
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmCancelQuotation(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/cancel.svg" class="me-2" alt="Cancel">
                                                                    Cancel
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/delete1.svg" class="me-2" alt="Delete">
                                                                    Delete
                                                                </button>
                                                            </li>

                                                        <?php elseif ($quotation_row['quotationStatus'] == "2"): ?>
                                                            <!-- Cancelled -->
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmReactivateQuotation(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/refresh.svg" class="me-2" alt="Reactivate">
                                                                    Reactivate
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $quotation_uid; ?>)">
                                                                    <img src="assets/img/icons/delete1.svg" class="me-2" alt="Delete">
                                                                    Delete
                                                                </button>
                                                            </li>
                                                        <?php elseif ($quotation_row['quotationStatus'] == "3"): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showDeleteReason<?= $reference_number; ?>">
                                                                    <img src="assets/img/icons/info-circle.svg" class="me-2" alt="img">
                                                                    Delete Reason
                                                                </button>
                                                            </li>
                                                        <?php endif; ?>

                                                    </ul>
                                                </td>
                                            </tr>

                                            <!-- Delete Reason Modal -->
                                            <div class="modal fade" id="showDeleteReason<?= $reference_number; ?>" tabindex="-1" aria-labelledby="showDeleteReasonModal<?= $reference_number; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content border-danger">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title" id="showDeleteReasonModal<?= $reference_number; ?>">
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
                                                                                <i class="bi bi-receipt me-2 text-primary"></i> Reference Number
                                                                            </span>
                                                                            <span class="fw-bold text-primary"><?= $reference_number; ?></span>
                                                                        </li>

                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <span class="fw-semibold text-muted">
                                                                                <i class="bi bi-person-circle me-2 text-success"></i> To
                                                                            </span>
                                                                            <span><?= $quotation_row['customerName']; ?></span>
                                                                        </li>

                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <span class="fw-semibold text-muted">
                                                                                <i class="bi bi-calendar-x me-2 text-danger"></i> Deleted On
                                                                            </span>
                                                                            <span class="text-muted"><?= date('d M, Y', strtotime($quotation_row['updated_at'])); ?></span>
                                                                        </li>

                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <span class="fw-semibold text-muted">
                                                                                <i class="bi bi-person-badge me-2 text-warning"></i> Deleted By
                                                                            </span>
                                                                            <span><?= $quotation_row['updater']; ?></span>
                                                                        </li>

                                                                        <!-- Reason in textarea -->
                                                                        <li class="list-group-item">
                                                                            <span class="fw-semibold text-muted d-block mb-2">
                                                                                <i class="bi bi-exclamation-triangle me-2 text-danger" data-bs-toggle="tooltip" title="Reason for deletion"></i>
                                                                                Reason For Deletion
                                                                            </span>
                                                                            <div class="bg-light border-start border-danger ps-3 py-2 rounded">
                                                                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                                                                <span class="text-dark"><?= ($quotation_row['quotationDescription']); ?></span>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Close</button>
                                                            <a href="quotationdetails.php?referenceNumber=<?= $reference_number; ?>" class="btn btn-outline-primary">View Quotation Details</a>
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
    <script>
        // Confirm delete quotation with reason input
        function confirmDelete(quotationUId) {
            Swal.fire({
                // icon: 'warning',
                title: 'Delete Quotation',
                html: `
                        <label for="deleteReason" style="display:block; margin-bottom:8px;">Please provide a reason for deleting this quotation:</label>
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
                    form.action = 'deletequotation.php';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'quotationUId';
                    idInput.value = quotationUId;

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

        };

        // Confirm cancel quotation
        function confirmCancelQuotation(quotationUId) {
            Swal.fire({
                icon: 'warning',
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
                icon: 'warning',
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
                icon: 'warning',
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

            const status = urlParams.get('status');
            const message = urlParams.get('message');
            const response = urlParams.get('response');
            const msg = urlParams.get('msg');
            const errorMsg = urlParams.get("errorMsg");

            // Delete Quotation
            if (status === 'success') {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Quotation deleted successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => clearParam('status'));
            }
            if (status === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: msg ? decodeURIComponent(msg) : 'Failed to delete the Quotation.',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => {
                    clearParam('status');
                    clearParam('msg');
                });
            }

            // Approve Quotation
            if (message === 'approved') {
                Swal.fire({
                    title: 'Approved!',
                    text: 'Quotation approved successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }
            if (message === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to approve the Quotation',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }
            if (message === 'stockerror') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Insufficient stock',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }
            if (message === 'invalid') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Invalid Quotation UID',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }
            if (message === 'notfound') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Quotation Not Found',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }
            if (message === 'accessdenied') {
                Swal.fire({
                    title: 'Access Denied!',
                    text: 'Only administrators can approve quotations.',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }
            if (message === 'missingcert') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Cannot approve quotation. Approver signature is missing.',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('message'));
            }
            // Reactivate   
            if (response === 'reactivated') {
                Swal.fire({
                    title: 'Reactivated!',
                    text: 'Quotation has been reactivated successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => clearParam('response'));
            }
            if (response === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: errorMsg ? decodeURIComponent(errorMsg) : 'Failed to reactivate the Quotation',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('response'));
            }

            // Cancel
            if (msg === 'cancelled') {
                Swal.fire({
                    title: 'Cancelled!',
                    text: 'Quotation has been cancelled successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => clearParam('msg'));
            }
            if (msg === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: errorMsg ? decodeURIComponent(errorMsg) : 'Failed to cancel the Quotation',
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => clearParam('msg'));
            }

            // Helper function 
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
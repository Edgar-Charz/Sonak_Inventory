<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if the add agent button was clicked
if (isset($_POST['addAgentBTN'])) {
    $agent_name = trim($_POST['agent_name']);
    $agent_phone = trim($_POST['agent_phone']);
    $agent_email = trim($_POST['agent_email']);

    // Check if agent exists
    $check_agent_stmt = $conn->prepare("SELECT * FROM agents WHERE agentEmail = ?");
    $check_agent_stmt->bind_param("s", $agent_email);
    $check_agent_stmt->execute();
    $result = $check_agent_stmt->get_result();

    if ($result->num_rows > 0) {
        // Agent already exists
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'Agent with this email already exists!'
                }).then(function(){
                    window.location.href = 'agentlist.php';
               });
            });
        </script>";
    } else {
        // Check if Agent Name already exists
        $check_agentname_stmt = $conn->prepare("SELECT * FROM agents WHERE agentName = ?");
        $check_agentname_stmt->bind_param("s", $agent_name);
        $check_agentname_stmt->execute();
        $agentname_result = $check_agentname_stmt->get_result();

        if ($agentname_result->num_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Agent with this name already exists!'
                        }).then(function(){
                            window.location.href = 'agentlist.php';
                       });
                    });
                </script>";
        } else {
            // Insert new agent
            $insert_stmt = $conn->prepare("INSERT INTO `agents`(`agentName`, `agentEmail`,`agentPhone`, `created_at`, `updated_at`) 
                                                        VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $agent_name, $agent_email, $agent_phone, $current_time, $current_time);

            if ($insert_stmt->execute()) {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Agent added successfully!'
                    }).then(function(){
                    window.location.href = 'agentlist.php';
               });
            });
            </script>";
            } else {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error adding agent. Please try again.'
                    }).then(function(){
                    window.location.href = 'agentlist.php';
               });
             });
            </script>";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Agents List</title>

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
                                <li><a href="quotationList.php">Quotation List</a></li>
                                <li><a href="addquotation.php">Add Quotation</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/users1.svg" alt="img"><span> People</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="customerlist.php">Customer List</a></li>
                                <li><a href="supplierlist.php">Supplier List</a></li>
                                <li><a href="agentlist.php" class="active">Agent List</a></li>
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
                        <h4>User List</h4>
                        <h6>Manage your Agents</h6>
                    </div>
                    <div class="page-btn">
                        <button type="button" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                            <img src="assets/img/icons/plus.svg" alt="img"> Add Agent
                        </button>
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

                        <div class="card" id="filter_inputs">
                            <div class="card-body pb-0">
                                <div class="row">
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter User Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Phone">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Email">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" class="datetimepicker cal-icon" placeholder="Choose Date">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Disable</option>
                                                <option>Enable</option>
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

                        <!-- Agents Table -->
                        <div class="table-responsive">
                            <table class="table" id="agentsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Agent Name </th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $agents_query =  $conn->query("SELECT * FROM agents");
                                    if ($agents_query->num_rows > 0) {
                                        while ($agent_row = $agents_query->fetch_assoc()) {
                                            $agent_id = $agent_row["agentId"];
                                    ?>
                                            <tr>
                                                <td> <?= $agent_row['agentId']; ?> </td>
                                                <td> <?= $agent_row['agentName']; ?> </td>
                                                <td> <?= $agent_row['agentPhone']; ?> </td>
                                                <td> <?= $agent_row['agentEmail']; ?> </td>
                                                <td>
                                                    <?php if ($agent_row['agentStatus'] == "1") : ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else : ?>
                                                        <span class="badge bg-danger">Inactive</span>
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
                                                                <li>
                                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewAgent<?= $agent_id; ?>">
                                                                        <img src="assets/img/icons/eye.svg" alt="View" style="width: 16px; margin-right: 6px;">
                                                                        View
                                                                    </button>
                                                                </li>

                                                                <!-- Edit Button -->
                                                                <li>
                                                                    <a href="editagent.php?id=<?= $agent_id; ?>" class="dropdown-item">
                                                                        <img src="assets/img/icons/edit.svg" alt="Edit" style="width: 16px; margin-right: 6px;">
                                                                        Edit
                                                                    </a>
                                                                </li>
                                                                <!-- Delete Button -->
                                                                <li>
                                                                    <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $agent_id; ?>)">
                                                                        <img src="assets/img/icons/delete.svg" alt="Delete" style="width: 16px; margin-right: 6px;">
                                                                        Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- View Agent Modal -->
                                            <div class="modal fade" id="viewAgent<?= $agent_id; ?>" tabindex="-1" aria-labelledby="viewAgentLabel<?= $agent_id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Agent Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>User ID</label>
                                                                        <p class="form-control"><?= $agent_id; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>User Name</label>
                                                                        <p class="form-control"><?= $agent_row['agentName']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Phone</label>
                                                                        <p class="form-control"><?= $agent_row['agentPhone']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Email</label>
                                                                        <p class="form-control"><?= $agent_row['agentEmail']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Status</label>
                                                                        <p class="form-control"><?= $agent_row['agentStatus'] == 1 ? 'Active' : 'InActive'; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /View Agent Modal -->

                                            <!-- Edit Agent Modal -->
                                            <div class="modal fade" id="editAgent<?= $agent_id; ?>" tabindex="-1" aria-labelledby="editAgentLabel<?= $agent_id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Agent</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                        </div>
                                                        <form action="" method="POST" id="edit-agent-form-<?= $agent_id; ?>">
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <input type="hidden" name="agentId" value="<?= $agent_id; ?>">

                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Agent ID <span class="text-danger">*</span></label>
                                                                            <input type="text" name="edit_agent_id" class="form-control" value="<?= $agent_row['agentId']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Agent Name <span class="text-danger">*</span></label>
                                                                            <input type="text" name="edit_agent_name" class="form-control" value="<?= $agent_row['agentName']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Phone <span class="text-danger">*</span></label>
                                                                            <input type="text" name="edit_agent_phone" class="form-control" value="<?= $agent_row['agentPhone']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Email <span class="text-danger">*</span></label>
                                                                            <input type="email" name="edit_agent_email" class="form-control" value="<?= $agent_row['agentEmail']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12 col-12">
                                                                        <div class="form-group">
                                                                            <label>Status <span class="text-danger">*</span></label>
                                                                            <select name="edit_agent_status" class="form-control" required>
                                                                                <option value="1" <?= $agent_row['agentStatus'] == 1 ? 'selected' : ''; ?>>Active</option>
                                                                                <option value="0" <?= $agent_row['agentStatus'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" name="updateAgentBTN" class="btn btn-primary">Save Changes</button>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /Edit Agent Modal -->
                                    <?php
                                        }
                                    }
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Agent Modal -->
                <div class="modal fade" id="addAgentModal" tabindex="-1" aria-labelledby="addAgentLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addAgentLabel">Add Agent</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                            </div>
                            <div class="modal-body">
                                <form action="" method="POST" id="agent-form" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Agent Name</label>
                                                <input type="text" name="agent_name" oninput="capitalizeFirstLetter(this)" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="text" name="agent_email" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Mobile</label>
                                                <input type="text" name="agent_phone" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button type="submit" name="addAgentBTN" class="btn btn-submit me-2">Submit</button>
                                            <button type="reset" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Add Agent Modal -->

            </div>
        </div>
    </div>



    <script>
        // Function to capitalize
        function capitalizeFirstLetter(input) {
            if (typeof input.value !== 'string' || input.value.length === 0) return;
            input.value = input.value.toLowerCase()
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }

        // Form Validation
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            // Required field check
            if (input.hasAttribute('required') && value === '') {
                return 'Please fill in all required fields.';
            }

            // Phone validation
            if ((name === 'agent_phone' || name === 'edit_agent_phone')) {
                if (!/^0\d{9}$/.test(value)) {
                    return 'Phone number must start with 0 and be exactly 10 digits.';
                }
            }

            // Email validation
            if ((name === 'agent_email' || name === 'edit_agent_email')) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    return 'Please enter a valid email address.';
                }
            }

            // Name validation
            if ((name === 'agent_name' || name === 'edit_agent_name') && value === '') {
                return 'Agent name can\'t be empty';
            }

            return true;
        }

        // Add agent form submission after validation
        document.getElementById("agent-form").addEventListener("submit", function(event) {
            var inputs = this.querySelectorAll('input, select');
            for (let input of inputs) {
                const result = validateInput(input);
                if (result !== true) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        text: result
                    });
                    input.focus();
                    return;
                }
            }
        });

        // Edit agent form submission after validation
        document.querySelectorAll('form[id^="edit-agent-form-"]').forEach(function(form) {
            form.addEventListener("submit", function(event) {
                var inputs = this.querySelectorAll('input, select');
                for (let input of inputs) {
                    const result = validateInput(input);
                    if (result !== true) {
                        event.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            text: result,
                            showConfirmButton: false,
                            timer: 3000
                        });
                        input.focus();
                        return;
                    }
                }
            });
        });

        // Function to confirm agent deletion 
        function confirmDelete(agentId) {
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
                    window.location.href = 'deleteagent.php?id=' + agentId;
                }
            });
        }

        // Trigger SweetAlert messages after redirect
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'success') {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Agent has been deleted successfully.',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (status === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete the agent.',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
        });
    </script>

    <script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>

    <!-- Customers Table  -->
    <script>
        $(document).ready(function() {
            if ($("#agentsTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#agentsTable")) {
                    $("#agentsTable").DataTable({
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

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $("#blah").attr("src", e.target.result);
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#imgInp").change(function() {
                readURL(this);
            });
        });
    </script>

</body>

</html>
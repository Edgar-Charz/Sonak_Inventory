<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if the add customer button was clicked
if (isset($_POST['addCustomerBTN'])) {
    $customer_name = trim($_POST['customer_name']);
    $customer_phone = trim($_POST['customer_phone']);
    $customer_email = trim($_POST['customer_email']);
    $customer_address = trim($_POST['customer_address']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Check if email exists
        $check_email_stmt = $conn->prepare("SELECT customerId FROM customers WHERE customerEmail = ?");
        $check_email_stmt->bind_param("s", $customer_email);
        $check_email_stmt->execute();
        $email_result = $check_email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            throw new Exception("Customer with this email already exists!");
        }

        // Check if name exists
        $check_name_stmt = $conn->prepare("SELECT customerId FROM customers WHERE customerName = ?");
        $check_name_stmt->bind_param("s", $customer_name);
        $check_name_stmt->execute();
        $name_result = $check_name_stmt->get_result();

        if ($name_result->num_rows > 0) {
            throw new Exception("Customer with this name already exists!");
        }

        // Insert new customer
        $insert_stmt = $conn->prepare("INSERT INTO customers (customerName, customerEmail, customerPhone, customerAddress, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssss", $customer_name, $customer_email, $customer_phone, $customer_address, $current_time, $current_time);
        $insert_stmt->execute();

        // Commit transaction
        $conn->commit();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Customer added successfully!'
                }).then(function(){
                    window.location.href = 'customerlist.php';
                });
            });
        </script>";
    } catch (Exception $e) {
        $conn->rollback();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '" . $e->getMessage() . "'
                }).then(function(){
                    window.location.href = 'customerlist.php';
                });
            });
        </script>";
    }
}

// Check if the update customer button was clicked
if (isset($_POST['updateSupplierBTN'])) {
    $customerId = $_POST['customerId'];
    $customer_name = trim($_POST['customer_name']);
    $customer_phone = trim($_POST['customer_phone']);
    $customer_email = trim($_POST['customer_email']);
    $customer_address = trim($_POST['customer_address']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Fetch current customer data
        $current_data_stmt = $conn->prepare("SELECT customerName, customerEmail, customerPhone, customerAddress, customerStatus FROM customers WHERE customerId = ?");
        $current_data_stmt->bind_param("i", $customerId);
        $current_data_stmt->execute();
        $current_data = $current_data_stmt->get_result()->fetch_assoc();

        // Check if no changes were made
        if (
            $current_data &&
            $current_data['customerName'] === $customer_name &&
            $current_data['customerPhone'] === $customer_phone &&
            $current_data['customerEmail'] === $customer_email &&
            $current_data['customerAddress'] === $customer_address
        ) {
            throw new Exception("no_changes");
        }

        // Check for duplicate email
        $check_email_stmt = $conn->prepare("SELECT customerId FROM customers WHERE customerEmail = ? AND customerId != ?");
        $check_email_stmt->bind_param("si", $customer_email, $customerId);
        $check_email_stmt->execute();
        if ($check_email_stmt->get_result()->num_rows > 0) {
            throw new Exception("Email address already exists for another customer.");
        }

        // Check for duplicate name
        $check_name_stmt = $conn->prepare("SELECT customerId FROM customers WHERE customerName = ? AND customerId != ?");
        $check_name_stmt->bind_param("si", $customer_name, $customerId);
        $check_name_stmt->execute();
        if ($check_name_stmt->get_result()->num_rows > 0) {
            throw new Exception("Customer name already exists for another customer.");
        }

        // Check for duplicate phone
        $check_phone_stmt = $conn->prepare("SELECT customerId FROM customers WHERE customerPhone = ? AND customerId != ?");
        $check_phone_stmt->bind_param("si", $customer_phone, $customerId);
        $check_phone_stmt->execute();
        if ($check_phone_stmt->get_result()->num_rows > 0) {
            throw new Exception("Phone number already exists for another customer.");
        }

        // Proceed with update
        $update_stmt = $conn->prepare("UPDATE customers SET customerName=?, customerPhone=?, customerEmail=?, customerAddress=?, updated_at=? WHERE customerId=?");
        $update_stmt->bind_param("sssssi", $customer_name, $customer_phone, $customer_email, $customer_address, $current_time, $customerId);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            $conn->commit();
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Customer updated successfully.'
                    }).then(function() {
                        window.location.href = 'customerlist.php';
                    });
                });
            </script>";
        } else {
            throw new Exception("no_changes");
        }
    } catch (Exception $e) {
        $conn->rollback();

        if ($e->getMessage() === "no_changes") {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Changes!',
                        text: 'You didn\\'t modify any customer details.'
                    }).then(function() {
                        window.location.href = 'customerlist.php';
                    });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: '" . $e->getMessage() . "'
                    }).then(function() {
                        window.location.href = 'customerlist.php';
                    });
                });
            </script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Customer List</title>

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
                                <li><a href="customerlist.php" class="active">Customer List</a></li>
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
                        <h4>Customer List</h4>
                        <h6>Manage your Customers</h6>
                    </div>
                    <div class="page-btn">
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <img src="assets/img/icons/plus.svg" alt="img"> Add Customer
                        </button>
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
                        </div>

                        <div class="card" id="filter_inputs">
                            <div class="card-body pb-0">
                                <div class="row">
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Customer Code">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Customer Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Phone Number">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Email">
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                                        <div class="form-group">
                                            <a class="btn btn-filters ms-auto"><img src="assets/img/icons/search-whites.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customers Table -->
                        <div class="table-responsive">
                            <table class="table" id="customersTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">S/N</th>
                                        <!-- <th class="text-center">ID</th> -->
                                        <th>Name</th>
                                        <th class="text-center">Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $customers_query =  $conn->query("SELECT * FROM customers");
                                    $sn = 0;
                                    if ($customers_query->num_rows > 0) {
                                        while ($customer_row = $customers_query->fetch_assoc()) {
                                            $customer_id = $customer_row["customerId"];
                                            $sn++;
                                    ?>
                                            <tr>
                                                <td class="text-center"> <?= $sn; ?> </td>
                                                <!-- <td class="text-center"> <?= $customer_row['customerId']; ?> </td> -->
                                                <td> <?= $customer_row['customerName']; ?> </td>
                                                <td class="text-center"> <?= $customer_row['customerPhone']; ?> </td>
                                                <td> <?= $customer_row['customerEmail']; ?> </td>
                                                <td> <?= $customer_row['customerAddress']; ?> </td>
                                                <td class="text-center">
                                                    <!-- <?php if ($customer_row['customerStatus'] == "1") : ?>
                                                        <span class="badges bg-lightgreen">Active</span>
                                                    <?php else : ?>
                                                        <span class="badges bg-lightred">Inactive</span>
                                                    <?php endif; ?> -->

                                                    <!-- Toggle Customer Status -->
                                                    <div class="status-toggle d-inline-flex align-items-center">
                                                        <input type="checkbox"
                                                            id="customer<?= $customer_id ?>"
                                                            class="check"
                                                            <?= $customer_row['customerStatus'] == 1 ? 'checked' : '' ?>
                                                            onchange="toggleCustomerStatus(<?= $customer_id ?>, this.checked)">
                                                        <label for="customer<?= $customer_id ?>" class="checktoggle ms-1"></label>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">

                                                        <!-- View Button -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary me-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewCustomer<?= $customer_id; ?>">
                                                            <i class="fas fa-eye text-dark"></i>
                                                        </button>

                                                        <!-- Edit Button -->
                                                        <!-- <a href="editcustomer.php?id=<?= $customer_id; ?>" class="dropdown-item">
                                                            <img src="assets/img/icons/edit.svg" alt="Edit" style="width: 16px; margin-right: 6px;">
                                                            Edit
                                                        </a> -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary me-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editCustomer<?= $customer_id; ?>">
                                                            <i class="fas fa-edit text-dark"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- View Customer Modal -->
                                            <div class="modal fade" id="viewCustomer<?= $customer_id; ?>" tabindex="-1" aria-labelledby="viewCustomerLabel<?= $customer_id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Customer Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <!-- <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Customer ID</label>
                                                                        <p class="form-control"><?= $customer_id; ?></p>
                                                                    </div>
                                                                </div> -->
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Customer Name</label>
                                                                        <p class="form-control"><?= $customer_row['customerName']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Phone</label>
                                                                        <p class="form-control"><?= $customer_row['customerPhone']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Email</label>
                                                                        <p class="form-control"><?= $customer_row['customerEmail']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Address</label>
                                                                        <p class="form-control"><?= $customer_row['customerAddress']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Status</label>
                                                                        <p class="form-control"><?= $customer_row['customerStatus'] == 1 ? 'Active' : 'InActive'; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /View Customer Modal -->

                                            <!-- Edit Customer Modal -->
                                            <div class="modal fade" id="editCustomer<?= $customer_id; ?>" tabindex="-1" aria-labelledby="editCustomerLabel<?= $customer_id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <form action="" method="POST">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Customer</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="customerId" value="<?= $customer_id; ?>">

                                                                <div class="row">
                                                                    <div class="col-lg-6 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Supplier Name</label>
                                                                            <input type="text" name="customer_name" class="form-control" value="<?= $customer_row['customerName']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Phone</label>
                                                                            <input type="text" name="customer_phone" class="form-control" value="<?= $customer_row['customerPhone']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Email</label>
                                                                            <input type="email" name="customer_email" class="form-control" value="<?= $customer_row['customerEmail']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Address</label>
                                                                            <input type="text" name="customer_address" class="form-control" value="<?= $customer_row['customerAddress']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <div class="col-lg-6 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Status</label>
                                                                            <select name="customer_status" class="form-control" required>
                                                                                <option value="1" <?= $customer_row['customerStatus'] == 1 ? 'selected' : ''; ?>>Active</option>
                                                                                <option value="0" <?= $customer_row['customerStatus'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                                                            </select>
                                                                        </div>
                                                                    </div> -->
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" name="updateSupplierBTN" class="btn btn-primary">Update</button>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <!-- /Edit Customer Modal -->
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Customer Modal -->
                <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSupplierModalLabel">Add Customer</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                            </div>
                            <form action="" method="POST" id="customer-form" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <!--Add Customer Form -->
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-6 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Customer Name</label>
                                                        <input type="text" name="customer_name" oninput="capitalizeFirstLetter(this);" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="text" name="customer_email" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" name="customer_phone" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Region</label>
                                                        <input type="text" id="region" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>District</label>
                                                        <input type="text" id="district" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Ward</label>
                                                        <input type="text" id="ward" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-12">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" name="customer_address" id="address" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="addCustomerBTN" class="btn btn-submit me-2">Submit</a>
                                        <button type="reset" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- / Add Customer Modal -->
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

        // Update address function 
        function updateAddress() {
            let region = document.getElementById("region").value.trim();
            let district = document.getElementById("district").value.trim();
            let ward = document.getElementById("ward").value.trim();

            let address = "";
            if (ward && district && region) {
                address = ward + ", " + district + ", " + region;
            } else if (district && region) {
                address = district + ", " + region;
            } else if (ward && region) {
                address = ward + ", " + region;
            } else if (ward && district) {
                address = ward + ", " + district;
            } else if (ward) {
                address = ward;
            } else if (district) {
                address = district;
            } else if (region) {
                address = region;
            }

            document.getElementById("address").value = address;
        }

        // Form Validation
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if (name === 'customer_phone' && !/^[0-9]{7,15}$/.test(value)) {
                return 'Please enter a valid Phone number.';
            }

            if (name === 'customer_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if ((name === 'customer_name') && value === '') {
                return 'Customer name can\'t be empty';
            }

            return true;
        }

        document.getElementById("customer-form").addEventListener("submit", function(event) {
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

        // Function to confirm customer deletion 
        function toggleCustomerStatus(customerId, isActive) {
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: isActive ? "Activate this customer?" : "Deactivate this customer?",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customer_status.php?id=' + customerId;
                } else {
                    document.getElementById("customer" + customerId).checked = !isActive;
                }
            });
        }

        // Trigger SweetAlert messages after redirect
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'deactivated') {
                Swal.fire({
                    icon: 'success',
                    title: 'Deactivated!',
                    text: 'Customer has been deactivated successfully.',
                    timer: 5000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (status === 'activated') {
                Swal.fire({
                    icon: 'success',
                    title: 'Activated!',
                    text: 'Customer has been activated successfully.',
                    timer: 5000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (status = 'notfound') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Customer not found.',
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Action on customer failed.',
                    showConfirmButton: false
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
            if ($("#customersTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#customersTable")) {
                    $("#customersTable").DataTable({
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
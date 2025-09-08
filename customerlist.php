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
    $account_holder = trim($_POST['customer_account_holder']);
    $account_number = trim($_POST['customer_account_number']);
    $bank_name = trim($_POST['customer_bank']);

    // Check if customer exists
    $check_customer_stmt = $conn->prepare("SELECT * FROM customers WHERE customerEmail = ?");
    $check_customer_stmt->bind_param("s", $customer_email);
    $check_customer_stmt->execute();
    $result = $check_customer_stmt->get_result();

    if ($result->num_rows > 0) {
        // Customer already exists
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error',
                    text: 'Customer with this email already exists!'
                }).then(function(){
                    window.location.href = 'customerlist.php';
               });
            });
        </script>";
    } else {
        // Check if Customer Name already exists
        $check_customername_stmt = $conn->prepare("SELECT * FROM customers WHERE customerName = ?");
        $check_customername_stmt->bind_param("s", $customer_name);
        $check_customername_stmt->execute();
        $customername_result = $check_customername_stmt->get_result();

        if ($customername_result->num_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Error',
                            text: 'Customer with this name already exists!'
                        }).then(function(){
                            window.location.href = 'customerlist.php';
                       });
                    });
                </script>";
        } else {
            // Insert new customer
            $insert_stmt = $conn->prepare("INSERT INTO `customers`(`customerName`, `customerEmail`, `customerPhone`, `customerAddress`, `customerAccountHolder`, `customerAccountNumber`, `bankName`, `created_at`, `updated_at`) 
                                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssssss", $customer_name, $customer_email, $customer_phone, $customer_address, $account_holder, $account_number, $bank_name, $current_time, $current_time);

            if ($insert_stmt->execute()) {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Success',
                        text: 'Customer added successfully!'
                    }).then(function(){
                    window.location.href = 'customerlist.php';
               });
            });
            </script>";
            } else {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error adding customer. Please try again.'
                    }).then(function(){
                    window.location.href = 'customerlist.php';
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
                                <li><a href="customerlist.php" class="active">Customer List</a></li>
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
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Account Holder</th>
                                        <th>Account Number</th>
                                        <th>Bank Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $customers_query =  $conn->query("SELECT * FROM customers");
                                    if ($customers_query->num_rows > 0) {
                                        while ($customer_row = $customers_query->fetch_assoc()) {
                                            $customer_id = $customer_row["customerId"];
                                    ?>
                                            <tr>
                                                <td> <?= $customer_row['customerId']; ?> </td>
                                                <td> <?= $customer_row['customerName']; ?> </td>
                                                <td> <?= $customer_row['customerPhone']; ?> </td>
                                                <td> <?= $customer_row['customerEmail']; ?> </td>
                                                <td> <?= $customer_row['customerAddress']; ?> </td>
                                                <td> <?= $customer_row['customerAccountHolder']; ?> </td>
                                                <td> <?= $customer_row['customerAccountNumber']; ?> </td>
                                                <td> <?= $customer_row['bankName']; ?> </td>
                                                <td>
                                                    <?php if ($customer_row['customerStatus'] == "1") : ?>
                                                        <span class="badge rounded-pill bg-success">Active</span>
                                                    <?php else : ?>
                                                        <span class="badge rounded-pill bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                                                <i class="fa fa-ellipsis-v"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                <li>
                                                                    <!-- View Button -->
                                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewCustomer<?= $customer_id; ?>">
                                                                        <img src="assets/img/icons/eye.svg" alt="View" style="width: 16px; margin-right: 6px;">
                                                                        View
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <!-- Edit Button -->
                                                                    <a href="editcustomer.php?id=<?= $customer_id; ?>" class="dropdown-item">
                                                                        <img src="assets/img/icons/edit.svg" alt="Edit" style="width: 16px; margin-right: 6px;">
                                                                        Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <!-- Delete Button -->
                                                                    <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $customer_id; ?>)">
                                                                        <img src="assets/img/icons/delete.svg" alt="Delete" style="width: 16px; margin-right: 6px;">
                                                                        Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
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
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Customer Name</label>
                                                                        <p class="form-control"><?= $customer_row['customerName']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Phone</label>
                                                                        <p class="form-control"><?= $customer_row['customerPhone']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Email</label>
                                                                        <p class="form-control"><?= $customer_row['customerEmail']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Bank</label>
                                                                        <p class="form-control"><?= $customer_row['bankName']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Account Number</label>
                                                                        <p class="form-control"><?= $customer_row['customerAccountNumber']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Account Holder</label>
                                                                        <p class="form-control"><?= $customer_row['customerAccountHolder']; ?></p>
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
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /View Customer Modal -->
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
                            <div class="modal-body">
                                <!--Add Customer Form -->
                                <div class="card">
                                    <div class="card-body">
                                        <form action="" method="POST" id="customer-form" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Customer Name</label>
                                                        <input type="text" name="customer_name" oninput="capitalizeFirstLetter(this);" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="text" name="customer_email" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" name="customer_phone" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Region</label>
                                                        <input type="text" id="region" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>District</label>
                                                        <input type="text" id="district" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Ward</label>
                                                        <input type="text" id="ward" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-12">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" name="customer_address" id="address" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Account Holder Name</label>
                                                        <input type="text" name="customer_account_holder" oninput="capitalizeFirstLetter(this);" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Account NUmber</label>
                                                        <input type="text" name="customer_account_number" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Bank Name</label>
                                                        <select name="customer_bank" class="select">
                                                            <option value="" selected disabled>Choose Bank</option>
                                                            <option>CRDB</option>
                                                            <option>NMB</option>
                                                            <option>NBC</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label> Avatar</label>
                                                        <div class="image-upload">
                                                            <input type="file">
                                                            <div class="image-uploads">
                                                                <img src="assets/img/icons/upload.svg" alt="img">
                                                                <h4>Drag and drop a file to upload</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> -->
                                                <div class="col-lg-12">
                                                    <button type="submit" name="addCustomerBTN" class="btn btn-submit me-2">Submit</a>
                                                        <button type="reset" class="btn btn-cancel">Cancel</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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

            if ((name === 'customer_address' || name === 'customer_account_holder') &&
                !/^[A-Za-z\s,]+$/.test(value)) {
                return 'Name fields should contain letters, spaces and commas only.';
            }

            if (name === 'customer_phone' && !/^[0-9]{7,15}$/.test(value)) {
                return 'Please enter a valid Phone number.';
            }

            if (name === 'customer_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (name === 'customer_account_number' && !/^[0-9]+$/.test(value)) {
                return 'Please enter a valid account number.';
            }

            if ((name === 'customer_name') && value === '') {
                return 'Customer name can\'t be empty';
            }

            if (name === 'customer_bank' && value === '') {
                return 'Please select a bank.';
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
        function confirmDelete(customerId) {
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
                    window.location.href = 'deletecustomer.php?id=' + customerId;
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
                    text: 'Customer has been deleted successfully.',
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
                    text: 'Failed to delete the customer.',
                    timer: 3000,
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
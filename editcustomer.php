<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid customer ID";
    exit;
}
// Get customer id
$customer_id = $_GET['id'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_POST['updateSupplierBTN'])) {
    $customerId = $_POST['customerId'];
    $customer_name = trim($_POST['customer_name']);
    $customer_phone = trim($_POST['customer_phone']);
    $customer_email = trim($_POST['customer_email']);
    $customer_address = trim($_POST['customer_address']);
    $customer_status = $_POST['customer_status'];

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
            $current_data['customerAddress'] === $customer_address &&
            $current_data['customerStatus'] === $customer_status
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
        $update_stmt = $conn->prepare("UPDATE customers SET customerName=?, customerPhone=?, customerEmail=?, customerAddress=?, customerStatus=?, updated_at=? WHERE customerId=?");
        $update_stmt->bind_param("ssssssi", $customer_name, $customer_phone, $customer_email, $customer_address, $customer_status, $current_time, $customerId);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            $conn->commit();
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Customer updated successfully.',
                        timer: 5000,
                        timerProgressBar: true
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
                        title: 'No Changes Detected',
                        text: 'You didn\\'t modify any customer details.',
                        timer: 5000,
                        timerProgressBar: true
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
                        text: '" . $e->getMessage() . "',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = 'editcustomer.php?id=$customerId';
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
    <title>Sonak Inventory | Edit Customer</title>

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
                        <h4>Edit Customer Management</h4>
                        <h6>Edit/Update Customer</h6>
                    </div>
                    <div class="page-btn">
                        <a href="customerlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Customers List</a>
                    </div>
                </div>

                <!-- Update Customer Form -->
                <div class="card">
                    <div class="card-body">
                        <?php
                        // Fetch customer data
                        $customer_stmt = $conn->prepare("SELECT * FROM customers WHERE customerId = ?");
                        $customer_stmt->bind_param("i", $customer_id);
                        $customer_stmt->execute();
                        $customer_result = $customer_stmt->get_result();
                        $customer_row = $customer_result->fetch_assoc();
                        ?>
                        <form action="" method="POST" id="update-customer-form" enctype="multipart/form-data">
                            <div class="row">
                                <input type="hidden" name="customerId" value="<?= $customer_id; ?>">

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
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="customer_status" class="form-control" required>
                                            <option value="1" <?= $customer_row['customerStatus'] == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?= $customer_row['customerStatus'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- <div class="col-lg-12">
                                    <div class="form-group">
                                        <label> Customer Image</label>
                                        <div class="image-upload">
                                            <input type="file" name="customer_image" accept="image/*">
                                            <div class="image-uploads">
                                                <img src="assets/img/icons/upload.svg" alt="img">
                                                <h4>Drag and drop a file to upload</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="col-lg-12">
                                    <button type="submit" name="updateSupplierBTN" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /Update Supplier Form -->


            </div>
        </div>
    </div>

    <script>
        // Form inputs Validation
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if (name === 'customer_phone' && !/^[0-9]{7,15}$/.test(value)) {
                return 'Please enter a validPhone number.';
            }

            if (name === 'customer_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            return true;
        }

        document.getElementById("update-customer-form").addEventListener("submit", function(event) {
            var inputs = this.querySelectorAll('input, select');
            for (let input of inputs) {
                var result = validateInput(input);
                if (result !== true) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        text: result,
                        position: 'center',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        input.focus();
                    });
                    return;
                }
            }
        });
    </script>


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
</body>

</html>
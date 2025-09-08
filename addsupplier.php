<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if the add supplier button was clicked
if (isset($_POST['addSupplierBTN'])) {
    $supplier_name = trim($_POST['supplier_name']);
    $supplier_phone = trim($_POST['supplier_phone']);
    $supplier_email = trim($_POST['supplier_email']);
    $supplier_address = trim($_POST['supplier_address']);
    $shop_name = trim($_POST['supplier_shop']);
    $type = trim($_POST['supplier_type']);
    $account_holder = trim($_POST['supplier_account_holder']);
    $account_number = trim($_POST['supplier_account_number']);
    $bank_name = trim($_POST['supplier_bank']);

    // Check if supplier exists
    $check_supplier_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierEmail = ?");
    $check_supplier_stmt->bind_param("s", $supplier_email);
    $check_supplier_stmt->execute();
    $result = $check_supplier_stmt->get_result();

    if ($result->num_rows > 0) {
        // Supplier already exists
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error',
                    text: 'Supplier with this email already exists!'
                }).then(function(){
                    window.location.href = 'addsupplier.php';
               });
            });
        </script>";
    } else {
        // Check if Supplier Name already exists
        $check_suppliername_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierName = ?");
        $check_suppliername_stmt->bind_param("s", $supplier_name);
        $check_suppliername_stmt->execute();
        $suppliername_result = $check_suppliername_stmt->get_result();

        if ($suppliername_result->num_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Error',
                            text: 'Supplier with this name already exists!'
                        }).then(function(){
                            window.location.href = 'addsupplier.php';
                       });
                    });
                </script>";
        } else {
            // Insert new supplier
            $insert_stmt = $conn->prepare("INSERT INTO `suppliers`(`supplierName`, `supplierEmail`, `supplierPhone`, `supplierAddress`, `shopName`, `type`, `supplierAccountHolder`, `supplierAccountNumber`, `bankName`, `created_at`, `updated_at`) 
                                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssssssss", $supplier_name, $supplier_email, $supplier_phone, $supplier_address, $shop_name, $type, $account_holder, $account_number, $bank_name, $current_time, $current_time);

            if ($insert_stmt->execute()) {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Success',
                        text: 'Supplier added successfully!'
                    }).then(function(){
                    window.location.href = 'addsupplier.php';
               });
            });
            </script>";
            } else {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error adding supplier. Please try again.'
                    }).then(function(){
                    window.location.href = 'addsupplier.php';
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
    <title>Sonak Inventory</title>

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
                                <li><a href="supplierlist.php" class="active">Supplier List</a></li>
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
                        <h4>Supplier Management</h4>
                        <h6>Add/Update Supplier</h6>
                    </div>
                    <div class="page-btn">
                        <a href="supplierlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp;Suppliers List</a>
                    </div>
                </div>

                <!-- Add Supplier Form -->
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" id="supplier-form" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Supplier Name</label>
                                        <input type="text" name="supplier_name" oninput="capitalizeFirstLetter(this)" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="text" name="supplier_email" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="supplier_phone" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Country</label>
                                        <input type="text" id="country" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" id="city" oninput="updateAddress(); capitalizeFirstLetter(this);">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" name="supplier_address" id="address" readonly required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Shop Name</label>
                                        <input type="text" name="supplier_shop" oninput="capitalizeFirstLetter(this)" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <input type="text" name="supplier_type" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Account Holder Name</label>
                                        <input type="text" name="supplier_account_holder" oninput="capitalizeFirstLetter(this)" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Account Number</label>
                                        <input type="text" name="supplier_account_number" class="form-control" required></input>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <select name="supplier_bank" class="select" required>
                                            <option value="" selected disabled>Choose Bank</option>
                                            <option>NMB</option>
                                            <option>CRDB</option>
                                            <option>NBC</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button type="submit" name="addSupplierBTN" class="btn btn-submit me-2">Submit</a>
                                        <button type="reset" class="btn btn-cancel">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End Add Supplier Form -->

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
            let city = document.getElementById("city").value.trim();
            let country = document.getElementById("country").value.trim();

            let address = "";
            if (city && country) {
                address = city + ", " + country;
            } else if (city) {
                address = city;
            } else if (country) {
                address = country;
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

            if ((name === 'supplier_address' || name === 'supplier_account_holder' || name === 'supplier_type') &&
                !/^[A-Za-z\s,]+$/.test(value)) {
                return 'Name fields should contain letters, spaces, and commas only.';
            }

            if (name === 'supplier_phone' && !/^0[0-9]{9}$/.test(value)) {
                return 'Phone number must start with 0 and contain 10 digits.';
            }

            if (name === 'supplier_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (name === 'supplier_account_number' && !/^[0-9]+$/.test(value)) {
                return 'Please enter a valid account number.';
            }

            if ((name === 'supplier_name' || name === 'supplier_shop') && value === '') {
                return 'Supplier name or shop can\'t be empty';
            }

            if (name === 'supplier_bank' && value === '') {
                return 'Please select a bank.';
            }

            return true;
        }

        document.getElementById("supplier-form").addEventListener("submit", function(event) {
            var inputs = this.querySelectorAll('input, select');
            for (let input of inputs) {
                const result = validateInput(input);
                if (result !== true) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        text: result,
                        position: 'top-end'
                    });
                    input.focus();
                    return;
                }
            }
        });
    </script>
    <script>

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
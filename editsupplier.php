<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid supplier ID";
    exit;
}
// Get supplier id
$supplier_id = $_GET['id'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Handle supplier update
if (isset($_POST['updateSupplierBTN'])) {
    $supplierId = $_POST['supplierId'];
    $supplier_name = trim($_POST['supplier_name']);
    $supplier_phone = trim($_POST['supplier_phone']);
    $supplier_email = trim($_POST['supplier_email']);
    $supplier_address = trim($_POST['supplier_address']);
    $shop_name = trim($_POST['supplier_shop']);
    $type = trim($_POST['supplier_type']);
    $account_holder = trim($_POST['supplier_account_holder']);
    $account_number = trim($_POST['supplier_account_number']);
    $bank_name = trim($_POST['supplier_bank']);
    $supplier_status = $_POST['supplier_status'];

    // Get current supplier data to compare for changes
    $current_data_query = "SELECT `supplierName`, `supplierEmail`, `supplierPhone`, `supplierAddress`, `shopName`, `type`, `supplierAccountHolder`, `supplierAccountNumber`, `bankName`, `supplierStatus` 
                                    FROM suppliers WHERE supplierId = ?";
    $current_data_stmt = $conn->prepare($current_data_query);
    $current_data_stmt->bind_param("i", $supplierId);
    $current_data_stmt->execute();
    $current_result = $current_data_stmt->get_result();
    $current_data = $current_result->fetch_assoc();

    // Check if no changes were made
    if (
        $current_data &&
        $current_data['supplierName'] == $supplier_name &&
        $current_data['supplierPhone'] == $supplier_phone &&
        $current_data['supplierEmail'] == $supplier_email &&
        $current_data['supplierAddress'] == $supplier_address &&
        $current_data['shopName'] == $shop_name &&
        $current_data['type'] == $type &&
        $current_data['supplierAccountHolder'] == $account_holder &&
        $current_data['supplierAccountNumber'] == $account_number &&
        $current_data['bankName'] == $bank_name &&
        $current_data['supplierStatus'] == $supplier_status
    ) {

        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Info!',
                        text: 'No changes were made to the supplier.',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = 'supplierlist.php';
                    });
                });
             </script>";
    } else {
        // Check if email already exists for another supplier
        $check_email_query = "SELECT supplierId FROM suppliers WHERE supplierEmail = ? AND supplierId != ?";
        $check_email_stmt = $conn->prepare($check_email_query);
        $check_email_stmt->bind_param("si", $supplier_email, $supplierId);
        $check_email_stmt->execute();
        $email_result = $check_email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            text: 'Email address already exists for another supplier.',
                            title: 'Error!',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(function() {
                            window.location.href = 'editsupplier.php?id=$supplierId';
                        });
                    });
                 </script>";
        } else {
            // Check if suppliername already exists for another supplier
            $check_suppliername_query = "SELECT supplierId FROM suppliers WHERE supplierName = ? AND supplierId != ?";
            $check_suppliername_stmt = $conn->prepare($check_suppliername_query);
            $check_suppliername_stmt->bind_param("si", $supplierName, $supplierId);
            $check_suppliername_stmt->execute();
            $suppliername_result = $check_suppliername_stmt->get_result();

            if ($suppliername_result->num_rows > 0) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function () {
                            Swal.fire({
                                text: 'Username already exists for another supplier.',
                                title: 'Error',
                                timer: 5000,
                                timerProgressBar: true
                            }).then(function() {
                                window.location.href = 'editsupplier.php?id=$supplierId';
                            });
                        });
                     </script>";
            } else {
                // Check if phone number already exists for another supplier
                $check_phone_query = "SELECT supplierId FROM suppliers WHERE supplierPhone = ? AND supplierId != ?";
                $check_phone_stmt = $conn->prepare($check_phone_query);
                $check_phone_stmt->bind_param("si", $supplier_phone, $supplierId);
                $check_phone_stmt->execute();
                $phone_result = $check_phone_stmt->get_result();

                if ($phone_result->num_rows > 0) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    text: 'Phone number already exists for another supplier.',
                                    title: 'Error',
                                    timer: 5000,
                                    timerProgressBar: true
                                }).then(function() {
                                    window.location.href = 'editsupplier.php?id=$supplierId';
                                });
                            });
                         </script>";
                } else {
                    // Proceed with the update
                    $update_supplier_query = "UPDATE suppliers 
                                                SET supplierName=?, supplierPhone=?, supplierEmail=?, supplierAddress=?, shopName=?, type=?, supplierAccountHolder=?, supplierAccountNumber=?, bankName=?, supplierStatus=?, updated_at=? 
                                                WHERE supplierId=?";
                    $update_supplier_stmt = $conn->prepare($update_supplier_query);
                    $update_supplier_stmt->bind_param("sssssssssssi", $supplier_name, $supplier_phone, $supplier_email, $supplier_address, $shop_name, $type, $account_holder, $account_number, $bank_name, $supplier_status, $current_time, $supplierId);

                    if ($update_supplier_stmt->execute()) {
                        if ($update_supplier_stmt->affected_rows > 0) {
                            echo "<script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            text: 'User updated successfully.',
                                            title: 'Success',
                                            timer: 5000,
                                            timerProgressBar: true
                                        }).then(function() {
                                            window.location.href = 'supplierlist.php';
                                        });
                                    });
                                  </script>";
                        } else {
                            echo "<script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            text: 'No changes were made to the supplier.',
                                            title: 'Info',
                                            timer: 5000,
                                            timerProgressBar: true
                                        }).then(function() {
                                            window.location.href = 'supplierlist.php';
                                        });
                                    });
                                  </script>";
                        }
                    } else {
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        text: 'Error updating supplier: " . $conn->error . "',
                                        title: 'Error',
                                        timer: 5000,
                                        timerProgressBar: true
                                    });
                                });
                            </script>";
                    }
                }
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
                        <h6>Edit/Update Customer</h6>
                    </div>
                    <div class="page-btn">
                        <a href="supplierlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Suppliers List</a>
                    </div>
                </div>

                <!-- Update Supplier Form -->
                <div class="card">
                    <div class="card-body">
                        <?php
                        // Fetch supplier data
                        $supplier_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierId = ?");
                        $supplier_stmt->bind_param("i", $supplier_id);
                        $supplier_stmt->execute();
                        $supplier_result = $supplier_stmt->get_result();
                        $supplier_row = $supplier_result->fetch_assoc();
                        ?>
                        <form action="" method="POST" id="update-supplier-form" enctype="multipart/form-data">
                            <div class="row">
                                <input type="hidden" name="supplierId" value="<?= $supplier_id; ?>">

                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Supplier Name</label>
                                        <input type="text" name="supplier_name" class="form-control" value="<?= $supplier_row['supplierName']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="supplier_phone" class="form-control" value="<?= $supplier_row['supplierPhone']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="supplier_email" class="form-control" value="<?= $supplier_row['supplierEmail']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" name="supplier_address" class="form-control" value="<?= $supplier_row['supplierAddress']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Shop Name</label>
                                        <input type="text" name="supplier_shop" class="form-control" value="<?= $supplier_row['shopName']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <input type="text" name="supplier_type" class="form-control" value="<?= $supplier_row['type']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Account Holder Name</label>
                                        <input type="text" name="supplier_account_holder" class="form-control" value="<?= $supplier_row['supplierAccountHolder']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Account Number</label>
                                        <input type="text" name="supplier_account_number" class="form-control" value="<?= $supplier_row['supplierAccountNumber']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <input type="text" name="supplier_bank" class="form-control" value="<?= $supplier_row['bankName']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="supplier_status" class="form-control" required>
                                            <option value="1" <?= $supplier_row['supplierStatus'] == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?= $supplier_row['supplierStatus'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- <div class="col-lg-12">
                                    <div class="form-group">
                                        <label> User Image</label>
                                        <div class="image-upload">
                                            <input type="file" name="supplier_image" accept="image/*">
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

            if (name === 'supplier_phone' && !/^[0-9]{7,15}$/.test(value)) {
                return 'Please enter a valid Phone number.';
            }

            if (name === 'supplier_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (name === 'supplier_account_number' && !/^[0-9]+$/.test(value)) {
                return 'Please enter a valid account number.';
            }

            return true;
        }

        document.getElementById("update-supplier-form").addEventListener("submit", function(event) {
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
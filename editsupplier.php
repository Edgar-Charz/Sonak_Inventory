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
    $supplier_type = trim($_POST['supplier_type']);
    $supplier_status = $_POST['supplier_status'];


    // Existing accounts
    $existing_account_uids = $_POST['existing_account_uid'] ?? [];
    $existing_account_holders = $_POST['existing_supplier_account_holder'] ?? [];
    $existing_account_numbers = $_POST['existing_supplier_account_number'] ?? [];
    $existing_bank_names = $_POST['existing_supplier_bank_name'] ?? [];

    // New added accounts
    $new_account_holders = $_POST['supplier_account_holder'] ?? [];
    $new_account_numbers = $_POST['supplier_account_number'] ?? [];
    $new_bank_names = $_POST['supplier_bank_name'] ?? [];

    // Removed accounts
    $delete_accounts = $_POST['delete_existing_account'] ?? [];

    // Enable MySQLi exceptions
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Check if supplier email exists
        $check_email_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierEmail = ? AND supplierId != ?");
        $check_email_stmt->bind_param("si", $supplier_email, $supplierId);
        $check_email_stmt->execute();
        $email_result = $check_email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            throw new Exception("Supplier with this email already exists!");
        }

        // Check if supplier name exists
        $check_name_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierName = ? AND supplierId != ?");
        $check_name_stmt->bind_param("si", $supplier_name, $supplierId);
        $check_name_stmt->execute();
        $name_result = $check_name_stmt->get_result();

        if ($name_result->num_rows > 0) {
            throw new Exception("Supplier with this name already exists!");
        }

        // Update supplier info
        $update_supplier_query = "UPDATE suppliers 
            SET supplierName=?, supplierPhone=?, supplierEmail=?, supplierAddress=?, supplierShopName=?, supplierType=?, supplierStatus=?, updated_at=? 
            WHERE supplierId=?";
        $update_stmt = $conn->prepare($update_supplier_query);
        $update_stmt->bind_param(
            "ssssssssi",
            $supplier_name,
            $supplier_phone,
            $supplier_email,
            $supplier_address,
            $shop_name,
            $supplier_type,
            $supplier_status,
            $current_time,
            $supplierId
        );
        $update_stmt->execute();

        // Update existing accounts
        for ($i = 0; $i < count($existing_account_uids); $i++) {
            $account_uid = $existing_account_uids[$i];

            if (in_array($account_uid, $delete_accounts)) continue;

            $update_account_query = "UPDATE bank_accounts 
                SET bankAccountHolderName=?, bankAccountNumber=?, bankAccountBankName=?, updated_at=? 
                WHERE bankAccountUId=?";
            $update_account_stmt = $conn->prepare($update_account_query);
            $update_account_stmt->bind_param(
                "ssssi",
                $existing_account_holders[$i],
                $existing_account_numbers[$i],
                $existing_bank_names[$i],
                $current_time,
                $account_uid
            );
            $update_account_stmt->execute();
        }

        // Delete marked accounts
        if (!empty($delete_accounts)) {
            $placeholders = implode(',', array_fill(0, count($delete_accounts), '?'));
            $types = str_repeat('i', count($delete_accounts));
            $update_query = "UPDATE bank_accounts SET bankAccountStatus = 0 WHERE bankAccountUId IN ($placeholders)";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param($types, ...$delete_accounts);
            $update_stmt->execute();
        }

        // Insert new accounts
        $insert_account_query = "INSERT INTO bank_accounts 
            (bankAccountSupplierId, bankAccountBankName, bankAccountHolderName, bankAccountNumber, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_account_query);

        for ($i = 0; $i < count($new_account_holders); $i++) {
            $account_holder = trim($new_account_holders[$i]);
            $account_number = trim($new_account_numbers[$i]);
            $bank_name  = trim($new_bank_names[$i]);

            if (!empty($account_holder) && !empty($account_number) && !empty($bank_name)) {

                // Check for duplicate account number
                $check_account_query = "SELECT bankAccountUId FROM bank_accounts WHERE bankAccountNumber = ? AND bankAccountSupplierId != ?";
                $check_account_stmt = $conn->prepare($check_account_query);
                $check_account_stmt->bind_param("si", $account_number, $supplier_id);
                $check_account_stmt->execute();
                $check_result = $check_account_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    throw new Exception("Bank account number $account_number already exists for another supplier.");
                }
                try {
                    $insert_stmt->bind_param(
                        "isssss",
                        $supplierId,
                        $bank_name,
                        $account_holder,
                        $account_number,
                        $current_time,
                        $current_time
                    );
                    $insert_stmt->execute();
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) {
                        throw new Exception("Bank account number $account_number already exists!");
                    } else {
                        throw $e;
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    text: 'Supplier updated successfully!'
                }).then(function() {
                    window.location.href='supplierlist.php';
                });
            });
        </script>";
    } catch (Exception $e) {
        $conn->rollback();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    text: '" . addslashes($e->getMessage()) . "',
                }).then(function() {
                    window.location.href='editsupplier.php?id=" . $supplierId . "';
                });
            });
        </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Edit Supplier</title>

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
                                <li><a href="customerlist.php">Customer List</a></li>
                                <li><a href="supplierlist.php" class="active">Supplier List</a></li>
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
                        <h4>Supplier Management</h4>
                        <h6>Edit/Update Customer</h6>
                    </div>
                    <div class="page-btn">
                        <a href="supplierlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Suppliers List</a>
                    </div>
                </div>

                <!-- Update Supplier Form -->
                <div class="card">
                    <form action="" method="POST" id="update-supplier-form" enctype="multipart/form-data">
                        <div class="card-body">
                            <?php
                            // Fetch supplier data
                            $supplier_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierId = ?");
                            $supplier_stmt->bind_param("i", $supplier_id);
                            $supplier_stmt->execute();
                            $supplier_result = $supplier_stmt->get_result();
                            $supplier_row = $supplier_result->fetch_assoc();

                            // Fetch supplier bank accounts
                            $accounts_query = $conn->prepare("SELECT * FROM bank_accounts WHERE bankAccountSupplierId = ?");
                            $accounts_query->bind_param("i", $supplier_id);
                            $accounts_query->execute();
                            $accounts_result = $accounts_query->get_result();
                            ?>
                            <div class="row">
                                <input type="hidden" name="supplierId" value="<?= $supplier_id; ?>">

                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Supplier Name</label>
                                        <input type="text" name="supplier_name" class="form-control" value="<?= $supplier_row['supplierName']; ?>" oninput="capitalizeFirstLetter(this)" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="supplier_phone" class="form-control" value="<?= $supplier_row['supplierPhone']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="supplier_email" class="form-control" value="<?= $supplier_row['supplierEmail']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" name="supplier_address" class="form-control" value="<?= $supplier_row['supplierAddress']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Shop Name</label>
                                        <input type="text" name="supplier_shop" class="form-control" value="<?= $supplier_row['supplierShopName']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <input type="text" name="supplier_type" class="form-control" value="<?= $supplier_row['supplierType']; ?>" required>
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

                                <!-- Bank Accounts Section -->
                                <div class="col-12">
                                    <label>Bank Accounts</label>
                                    <div id="accounts-wrapper">
                                        <?php
                                        if ($accounts_result->num_rows > 0) {
                                            while ($acc = $accounts_result->fetch_assoc()) { ?>
                                                <div class="row account-row mb-2" data-account-id="<?= $acc['bankAccountUId']; ?>">
                                                    <input type="hidden" name="existing_account_uid[]" value="<?= $acc['bankAccountUId']; ?>">
                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                        <input type="text" name="existing_supplier_account_holder[]" class="form-control"
                                                            value="<?= $acc['bankAccountHolderName']; ?>" required>
                                                    </div>
                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                        <input type="text" name="existing_supplier_account_number[]" class="form-control"
                                                            value="<?= $acc['bankAccountNumber']; ?>" required>
                                                    </div>
                                                    <div class="col-lg-3 col-sm-6 col-12">
                                                        <select name="existing_supplier_bank_name[]" class="form-control" required>
                                                            <option value="NMB" <?= $acc['bankAccountBankName'] == 'NMB' ? 'selected' : ''; ?>>NMB</option>
                                                            <option value="CRDB" <?= $acc['bankAccountBankName'] == 'CRDB' ? 'selected' : ''; ?>>CRDB</option>
                                                            <option value="NBC" <?= $acc['bankAccountBankName'] == 'NBC' ? 'selected' : ''; ?>>NBC</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-1 col-sm-6 col-12 d-flex align-items-center">
                                                        <button type="button" class="btn btn-danger btn-sm remove-account-existing">&times;</button>
                                                    </div>
                                                </div>
                                        <?php }
                                        } ?>
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-account">+ Add Account</button>
                                </div>

                                <div class="col-lg-12 mt-3">
                                    <button type="submit" name="updateSupplierBTN" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /Update Supplier Form -->

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

        // Add new bank account row
        document.getElementById('add-account').addEventListener('click', function() {
            let wrapper = document.getElementById('accounts-wrapper');
            let row = document.createElement('div');
            row.classList.add('row', 'account-row', 'mb-2');
            row.innerHTML = `
            <div class="col-lg-4 col-sm-6 col-12">
                <input type="text" name="supplier_account_holder[]" class="form-control" placeholder="Account Holder Name" required>
            </div>
            <div class="col-lg-4 col-sm-6 col-12">
                <input type="text" name="supplier_account_number[]" class="form-control" placeholder="Account Number" required>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <select name="supplier_bank_name[]" class="form-control" required>
                    <option value="" selected disabled>Choose Bank</option>
                    <option>NMB</option>
                    <option>CRDB</option>
                    <option>NBC</option>
                </select>
            </div>
            <div class="col-lg-1 col-sm-6 col-12 d-flex align-items-center">
                <button type="button" class="btn btn-danger btn-sm remove-account">&times;</button>
            </div>`;
            wrapper.appendChild(row);

            // Remove new row
            row.querySelector('.remove-account').addEventListener('click', function() {
                row.remove();
            });
        });

        // Remove existing account
        document.querySelectorAll('.remove-account-existing').forEach(function(btn) {
            btn.addEventListener('click', function() {
                let row = this.closest('.account-row');
                let accountId = row.getAttribute('data-account-id');

                // Mark for deletion
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_existing_account[]';
                input.value = accountId;
                row.appendChild(input);

                row.style.display = 'none';
            });
        });

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
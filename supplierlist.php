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
    $supplier_type = trim($_POST['supplier_type']);

    // Bank Accounts
    $account_holders = $_POST['supplier_account_holder'];
    $account_numbers = $_POST['supplier_account_number'];
    $bank_names      = $_POST['supplier_bank_name'];

    // Enable MySQLi exceptions
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Check if supplier email exists
        $check_email_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierEmail = ?");
        $check_email_stmt->bind_param("s", $supplier_email);
        $check_email_stmt->execute();
        $email_result = $check_email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            throw new Exception("Supplier with this email already exists!");
        }

        // Check if supplier name exists
        $check_name_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplierName = ?");
        $check_name_stmt->bind_param("s", $supplier_name);
        $check_name_stmt->execute();
        $name_result = $check_name_stmt->get_result();

        if ($name_result->num_rows > 0) {
            throw new Exception("Supplier with this name already exists!");
        }

        // Insert supplier
        $insert_supplier = $conn->prepare("INSERT INTO suppliers 
            (supplierName, supplierEmail, supplierPhone, supplierAddress, supplierShopName, supplierType, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_supplier->bind_param("ssssssss", $supplier_name, $supplier_email, $supplier_phone, $supplier_address, $shop_name, $supplier_type, $current_time, $current_time);
        $insert_supplier->execute();

        $supplier_id = $insert_supplier->insert_id;

        // Prepare bank account insert
        $insert_account = $conn->prepare("INSERT INTO bank_accounts 
            (bankAccountSupplierId, bankAccountBankName, bankAccountHolderName, bankAccountNumber, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?)");

        for ($i = 0; $i < count($account_holders); $i++) {
            $account_holder = trim($account_holders[$i]);
            $account_number = trim($account_numbers[$i]);
            $bank_name  = trim($bank_names[$i]);

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
                    $insert_account->bind_param("isssss", $supplier_id, $bank_name, $account_holder, $account_number, $current_time, $current_time);
                    $insert_account->execute();
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
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({ 
                icon: 'success', 
                text: 'Supplier added successfully!' 
                }).then(() => window.location.href = 'supplierlist.php');
            });
        </script>";
    } catch (Exception $e) {
        $conn->rollback();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({ 
                    icon: 'error', 
                    text: '" . addslashes($e->getMessage()) . "'
                }).then(() => window.location.href = 'supplierlist.php');
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
    <title>Sonak Inventory | Supplier List</title>

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
                        <h4>Supplier List</h4>
                        <h6>Manage your Supplier</h6>
                    </div>
                    <div class="page-btn">
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                            <img src="assets/img/icons/plus.svg" alt="img"> Add Supplier
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
                                            <input type="text" placeholder="Enter Supplier Code">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Supplier">
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
                                    <div class="col-lg-1 col-sm-6 col-12 ms-auto">
                                        <div class="form-group">
                                            <a class="btn btn-filters ms-auto"><img src="assets/img/icons/search-whites.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Suppliers Table -->
                        <div class="table-responsive">
                            <table class="table" id="suppliersTable">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <!-- <th>ID</th> -->
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>ShopName</th>
                                        <th>Type</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $suppliers_query =  $conn->query("SELECT * FROM suppliers");
                                    $sn = 0;
                                    if ($suppliers_query->num_rows > 0) {
                                        while ($supplier_row = $suppliers_query->fetch_assoc()) {
                                            $supplier_id = $supplier_row["supplierId"];
                                            $sn++;
                                    ?>
                                            <tr>
                                                <td><?= $sn; ?></td>
                                                <!-- <td><?= $supplier_row['supplierId']; ?></td> -->
                                                <td><?= $supplier_row['supplierName']; ?></td>
                                                <td><?= $supplier_row['supplierPhone']; ?></td>
                                                <td><?= $supplier_row['supplierEmail']; ?></td>
                                                <td><?= $supplier_row['supplierAddress']; ?></td>
                                                <td><?= $supplier_row['supplierShopName']; ?></td>
                                                <td><?= $supplier_row['supplierType']; ?></td>
                                                <td class="text-center">
                                                    <!-- <?php if ($supplier_row['supplierStatus'] == "1") : ?>
                                                        <span class="badges bg-lightgreen">Active</span>
                                                    <?php else : ?>
                                                        <span class="badges bg-lightred">Inactive</span>
                                                    <?php endif; ?> -->

                                                    <!-- Toggle Supplier Status -->
                                                    <div class="status-toggle d-inline-flex align-items-center">
                                                        <input type="checkbox"
                                                            id="supplier<?= $supplier_id ?>"
                                                            class="check"
                                                            <?= $supplier_row['supplierStatus'] == 1 ? 'checked' : '' ?>
                                                            onchange="toggleSupplierStatus(<?= $supplier_id ?>, this.checked)">
                                                        <label for="supplier<?= $supplier_id ?>" class="checktoggle ms-1"></label>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">

                                                        <!-- View Button -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary me-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewSupplier<?= $supplier_id; ?>">
                                                            <i class="fas fa-eye text-dark"></i>
                                                        </button>

                                                        <!-- Edit Button -->
                                                        <a href="editsupplier.php?id=<?= $supplier_id; ?>"
                                                            class="btn btn-sm btn-outline-primary me-2">
                                                            <i class="fas fa-edit text-dark"></i>
                                                        </a>

                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- View Supplier Modal -->
                                            <div class="modal fade" id="viewSupplier<?= $supplier_id; ?>" tabindex="-1" aria-labelledby="viewSupplierLabel<?= $supplier_id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Supplier Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <!-- <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Supplier ID</label>
                                                                        <p class="form-control"><?= $supplier_id; ?></p>
                                                                    </div>
                                                                </div> -->
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>User Name</label>
                                                                        <p class="form-control"><?= $supplier_row['supplierName']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Phone</label>
                                                                        <p class="form-control"><?= $supplier_row['supplierPhone']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Email</label>
                                                                        <p class="form-control"><?= $supplier_row['supplierEmail']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Address</label>
                                                                        <p class="form-control"><?= $supplier_row['supplierAddress']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Shop Name</label>
                                                                        <p class="form-control"><?= $supplier_row['supplierShopName']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Type</label>
                                                                        <p class="form-control"><?= $supplier_row['supplierType']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Status</label>
                                                                        <p class="form-control"><?= $supplier_row['supplierStatus'] == 1 ? 'Active' : 'InActive'; ?></p>
                                                                    </div>
                                                                </div>

                                                                <div class="col-lg-12 col-sm-12 col-12 text-center">
                                                                    <div class="form-group">
                                                                        <label style="text-align: center; font-size: large;">Bank Accounts</label>
                                                                        <?php
                                                                        $accounts_query = $conn->prepare("SELECT * FROM bank_accounts WHERE bankAccountSupplierId = ?");
                                                                        $accounts_query->bind_param("i", $supplier_id);
                                                                        $accounts_query->execute();
                                                                        $accounts_result = $accounts_query->get_result();

                                                                        if ($accounts_result->num_rows > 0) {
                                                                            $i = 1;
                                                                            echo '<div class="row border p-2 mb-2">';
                                                                            echo '<div class="col-1"><strong>#</strong></div>';
                                                                            echo '<div class="col-4"><strong>Account Holder</strong></div>';
                                                                            echo '<div class="col-3"><strong>Account Number</strong></div>';
                                                                            echo '<div class="col-2"><strong>Bank Name</strong></div>';
                                                                            echo '<div class="col-2"><strong>Status</strong></div>';
                                                                            echo '</div>';

                                                                            while ($account = $accounts_result->fetch_assoc()) {
                                                                                $bank_account_uid = $account['bankAccountUId'];

                                                                                echo '<div class="row border p-2 mb-1">';
                                                                                echo "<div class='col-1'>{$i}</div>";
                                                                                echo "<div class='col-4'>{$account['bankAccountHolderName']}</div>";
                                                                                echo "<div class='col-3'>{$account['bankAccountNumber']}</div>";
                                                                                echo "<div class='col-2'>{$account['bankAccountBankName']}</div>";
                                                                                echo "<div class='col-2'>" . ($account['bankAccountStatus'] == 1 ? 'Active' : 'InActive') . "</div>";
                                                                                echo '</div>';
                                                                                $i++;
                                                                            }
                                                                        } else {
                                                                            echo "<p class='text-center text-muted'>No accounts available</p>";
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End of View Supplier Modal -->
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Supplier Modal -->
                <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSupplierModalLabel">Add Supplier</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                            </div>
                            <form action="" method="POST" id="supplier-form" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <!--Add Supplier Form -->
                                    <div class="card">
                                        <div class="card-body">
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
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" name="supplier_address" id="address" readonly required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Shop Name</label>
                                                        <input type="text" name="supplier_shop" oninput="capitalizeFirstLetter(this)">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Type</label>
                                                        <input type="text" name="supplier_type" required>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label>Bank Accounts</label>
                                                    <div id="accounts-wrapper">
                                                        <div class="row account-row mb-2">
                                                            <div class="col-lg-4 col-sm-6 col-12">
                                                                <div class="form-group">
                                                                    <input type="text" name="supplier_account_holder[]" class="form-control"
                                                                        placeholder="Account Holder Name" required
                                                                        oninput="capitalizeFirstLetter(this)">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6 col-12">
                                                                <div class="form-group">
                                                                    <input type="text" name="supplier_account_number[]" class="form-control"
                                                                        placeholder="Account Number" required>
                                                                </div>
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
                                                                <div class="form-group">
                                                                    <button type="button" class="btn btn-danger btn-sm remove-account">&times;</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-account">+ Add Account</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="addSupplierBTN" class="btn btn-submit me-2">Submit</button>
                                    <button type="reset" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End of Add Supplier Modal -->
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

        // Add Account
        document.addEventListener("DOMContentLoaded", function() {
            const wrapper = document.getElementById("accounts-wrapper");
            const addBtn = document.getElementById("add-account");

            addBtn.addEventListener("click", function() {
                const newRow = document.createElement("div");
                newRow.classList.add("row", "account-row", "mb-2");
                newRow.innerHTML = `
            <div class="col-lg-4 col-sm-6 col-12">
                <input type="text" name="supplier_account_holder[]" class="form-control"
                       placeholder="Account Holder Name" required
                       oninput="capitalizeFirstLetter(this)">
            </div>
            <div class="col-lg-4 col-sm-6 col-12">
                <input type="text" name="supplier_account_number[]" class="form-control"
                       placeholder="Account Number" required>
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
            </div>
        `;
                wrapper.appendChild(newRow);

                // Attach remove button event
                newRow.querySelector(".remove-account").addEventListener("click", function() {
                    newRow.remove();
                });
            });

            // Attach to initial row
            document.querySelectorAll(".remove-account").forEach(function(btn) {
                btn.addEventListener("click", function() {
                    btn.closest(".account-row").remove();
                });
            });
        });

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

            if ((name === 'supplier_account_holder' || name === 'supplier_type') &&
                !/^[A-Za-z\s,]+$/.test(value)) {
                return 'Invalid input.';
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

            if ((name === 'supplier_name' || name === 'supplier_shop') && value === '') {
                return 'Supplier name or shop can\'t be empty';
            }

            if (name === 'supplier_bank_name' && value === '') {
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
                        text: result
                    });
                    input.focus();
                    return;
                }
            }
        });

        // Function to confirm supplier deletion 
        function toggleSupplierStatus(supplierId, isActive) {
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: isActive ? "Activate this supplier?" : "Deactivate this supplier?",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'supplier_status.php?id=' + supplierId;
                } else {
                    document.getElementById("supplier" + supplierId).checked = !isActive;
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
                    text: 'Supplier has been deactivated successfully.',
                    timer: 3000,
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
                    text: 'Supplier has been activated successfully.',
                    timer: 3000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (status === 'notfound') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Supplier not found.',
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
                    icon: 'error',
                    title: 'Error!',
                    text: 'Action failed.',
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

    <!--Supplier Table  -->
    <script>
        $(document).ready(function() {
            if ($("#suppliersTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#suppliersTable")) {
                    $("#suppliersTable").DataTable({
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
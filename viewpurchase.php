<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['purchaseNumber'])) {
    echo "Invalid Purchase Number";
    exit;
}

// Get user id from session
$user_id = $_SESSION["id"];

// Get purchase number and purchase status
$purchase_number = $_GET['purchaseNumber'];
$purchase_status = $_GET['purchaseStatus'];

// Get purchase UID
$purchase_uid_query = $conn->prepare("SELECT purchaseUId FROM purchases WHERE purchaseNumber = ?");
$purchase_uid_query->bind_param("s", $purchase_number);
$purchase_uid_query->execute();
$purchase_uid_result = $purchase_uid_query->get_result();
$purchase_uid = $purchase_uid_result->fetch_assoc()['purchaseUId'];
$purchase_uid_query->close();

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Fetch purchase details
$purchase_stmt = $conn->prepare("SELECT purchases.*, 
                                            bank_accounts.bankAccountBankName AS bankName, 
                                            bank_accounts.bankAccountNumber, 
                                            bank_accounts.bankAccountHolderName AS accountHolderName
                                        FROM purchases 
                                        LEFT JOIN bank_accounts 
                                        ON purchases.purchaseSupplierAccountNumber = bank_accounts.bankAccountNumber 
                                        WHERE purchases.purchaseNumber = ?");
$purchase_stmt->bind_param("s", $purchase_number);
$purchase_stmt->execute();
$purchase_result = $purchase_stmt->get_result();

if ($purchase_result->num_rows == 0) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Error!',
                text: 'Purchase not found!',
                timer: 5000,
                timerProgressBar: true
            }).then(function(){
                window.location.href = 'purchaselist.php';
            });
        });
    </script>";
    exit;
}

$purchase = $purchase_result->fetch_assoc();

// Check if editPurchaseBTN was clicked
if (isset($_POST['editPurchaseBTN'])) {

    $conn->begin_transaction();
    try {
        $supplierId     = $_POST['supplier_name'];
        $purchaseDate   = DateTime::createFromFormat('d-m-Y', $_POST['purchase_date'])->format('Y-m-d');
        $purchaseNumber = $_POST['purchase_number'];
        $bankAccountNumber = $_POST['bank_account_number'] ?? null;

        $updatedBy = $user_id;

        // Validate bank account number
        if ($bankAccountNumber) {
            $validate_bank_stmt = $conn->prepare("SELECT bankAccountNumber FROM bank_accounts WHERE bankAccountNumber = ? AND bankAccountSupplierId = ?");
            $validate_bank_stmt->bind_param("ss", $bankAccountNumber, $supplierId);
            $validate_bank_stmt->execute();
            $validate_bank_result = $validate_bank_stmt->get_result();
            if ($validate_bank_result->num_rows == 0) {
                throw new Exception("Invalid bank account number for the selected supplier.");
            }
            $validate_bank_stmt->close();
        }

        // Update purchase
        $update_purchase_stmt = $conn->prepare("UPDATE purchases 
                                                            SET purchaseSupplierId = ?, purchaseSupplierAccountNumber = ?, purchaseDate = ?, purchaseUpdatedBy = ?, updated_at = ? 
                                                            WHERE purchaseNumber = ?");
        $update_purchase_stmt->bind_param("ssssss", $supplierId, $bankAccountNumber, $purchaseDate, $updatedBy, $current_time, $purchaseNumber);
        $update_purchase_stmt->execute();
        $update_purchase_stmt->close();

        $conn->commit();
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Purchase updated successfully!',
                    timer: 5000,
                    timerProgressBar: true
                }).then(function(){
                    window.location.href = 'viewpurchase.php?purchaseNumber=$purchaseNumber&purchaseStatus=$purchase_status';
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
                    text: 'Transaction failed: " . addslashes($e->getMessage()) . "'
                }).then(function(){
                    window.location.href = 'viewpurchase.php?purchaseNumber=$purchaseNumber&purchaseStatus=$purchase_status';
                });
            });
        </script>";
    }
}

// Check if the editPurchaseProduct button was clicked
if (isset($_POST['editPurchaseProductBTN'])) {
    $conn->begin_transaction();
    try {
        $purchaseDetailUId = $_POST['purchaseDetailUId'];
        $purchaseNumber = $_POST['purchaseNumber'] ?? '';

        $quantity = str_replace(',', '', $_POST['quantity']) ?: 0;
        $unit_cost = str_replace(',', '', $_POST['unit_cost']) ?: 0;
        $rate = str_replace(',', '', $_POST['rate']) ?: 0;
        $total_cost = str_replace(',', '', $_POST['total_cost']) ?: 0;
        $product_size = !empty($_POST['product_size']) ? $_POST['product_size'] : null;

        $agent_id = !empty($_POST['agent_name']) ? $_POST['agent_name'] : null;
        $agent_bank_account_number = !empty($_POST['agent_bank_account_number']) ? $_POST['agent_bank_account_number'] : null;
        $tracking_number = !empty($_POST['tracking_number']) ? $_POST['tracking_number'] : null;
        $agent_transport_cost = !empty($_POST['agent_transport_cost']) ? str_replace(',', '', $_POST['agent_transport_cost']) : null;
        $agent_abroad_date = !empty($_POST['agent_abroad_date']) ? DateTime::createFromFormat('Y-m-d', $_POST['agent_abroad_date'])->format('Y-m-d') : null;
        $agent_tanzania_date = !empty($_POST['agent_tanzania_date']) ? DateTime::createFromFormat('Y-m-d', $_POST['agent_tanzania_date'])->format('Y-m-d') : null;
        $at_sonak_date = !empty($_POST['at_sonak_date']) ? DateTime::createFromFormat('Y-m-d', $_POST['at_sonak_date'])->format('Y-m-d') : null;

        $time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
        $current_time = $time->format("Y-m-d H:i:s");

        // Validate agent bank account number
        if ($agent_bank_account_number && $agent_id) {
            $validate_bank_stmt = $conn->prepare("SELECT bankAccountNumber FROM bank_accounts WHERE bankAccountNumber = ? AND bankAccountAgentId = ?");
            $validate_bank_stmt->bind_param("ss", $agent_bank_account_number, $agent_id);
            $validate_bank_stmt->execute();
            $validate_bank_result = $validate_bank_stmt->get_result();
            if ($validate_bank_result->num_rows == 0) {
                throw new Exception("Invalid bank account number for the selected agent.");
            }
            $validate_bank_stmt->close();
        }

        // Update purchase detail first
        $update_query = $conn->prepare("UPDATE 
                                            purchase_details 
                                        SET 
                                            purchaseDetailQuantity = ?, 
                                            purchaseDetailUnitCost = ?, 
                                            purchaseDetailRate = ?, 
                                            purchaseDetailTotalCost = ?, 
                                            purchaseDetailProductSize = ?, 
                                            purchaseDetailAgentId = ?, 
                                            purchaseAgentBankAccountNumber = ?,
                                            purchaseDetailTrackingNumber = ?, 
                                            agentTransportationCost = ?, 
                                            dateToAgentAbroadWarehouse = ?, 
                                            dateReceivedByAgentInCountryWarehouse = ?, 
                                            dateReceivedByCompany = ?,
                                            updated_at = ?
                                        WHERE 
                                            purchaseDetailUId= ?");
        $update_query->bind_param(
            "ididsissdssssi",
            $quantity,
            $unit_cost,
            $rate,
            $total_cost,
            $product_size,
            $agent_id,
            $agent_bank_account_number,
            $tracking_number,
            $agent_transport_cost,
            $agent_abroad_date,
            $agent_tanzania_date,
            $at_sonak_date,
            $current_time,
            $purchaseDetailUId
        );
        $update_query->execute();

        // If Date Received by Company is filled, update stock + mark as completed
        if (!empty($at_sonak_date)) {
            // Get product id + quantity
            $get_product = $conn->prepare("SELECT purchaseDetailProductId, purchaseDetailQuantity 
                                           FROM purchase_details 
                                           WHERE purchaseDetailUId = ?");
            $get_product->bind_param("i", $purchaseDetailUId);
            $get_product->execute();
            $prod = $get_product->get_result()->fetch_assoc();
            $product_id = $prod['purchaseDetailProductId'];
            $qty = $prod['purchaseDetailQuantity'];

            // Update stock
            $update_stock = $conn->prepare("UPDATE products 
                                            SET productQuantity = productQuantity + ?, updated_at = ? 
                                            WHERE productId = ?");
            $update_stock->bind_param("isi", $qty, $current_time, $product_id);
            $update_stock->execute();

            // Mark detail as completed
            $update_status = $conn->prepare("UPDATE purchase_details 
                                             SET purchaseDetailStatus = 1, updated_at = ? 
                                             WHERE purchaseDetailUId = ?");
            $update_status->bind_param("si", $current_time, $purchaseDetailUId);
            $update_status->execute();
            $update_status->close();

            // Check if all details are completed, then mark purchase as completed
            $check_all_completed = $conn->prepare("SELECT COUNT(*) AS incomplete_count 
                                                  FROM purchase_details 
                                                  WHERE purchaseDetailPurchaseNumber = ? 
                                                  AND purchaseDetailStatus = 0");
            $check_all_completed->bind_param("s", $purchase_number);
            $check_all_completed->execute();
            $count = $check_all_completed->get_result()->fetch_assoc()['incomplete_count'];
            if ($count == 0) {
                $complete_stmt = $conn->prepare("UPDATE 
                                                    purchases 
                                                SET 
                                                    purchaseStatus = 1, purchaseUpdatedBy = ?, updated_at = ? 
                                                WHERE 
                                                    purchaseUId = ?");
                $complete_stmt->bind_param("isi", $user_id, $current_time, $purchase_uid);
                $complete_stmt->execute();
                $complete_stmt->close();
            }
        }

        // Commit everything
        $conn->commit();
        $_SESSION['purchase_update_success'] = true;
        header("Location: viewpurchase.php?purchaseNumber=$purchaseNumber&purchaseStatus=$purchase_status");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to update purchase product: " . addslashes($e->getMessage()) . "',
                    icon: 'error'
                });
            });
        </script>";
    }
}

if (isset($_SESSION['purchase_update_success'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: 'Purchase product updated successfully!',
                icon: 'success',
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>";
    unset($_SESSION['purchase_update_success']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | View Purchase</title>

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
                                <li><a href="purchaselist.php" class="active">Purchase List</a></li>
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
                                <li><a href="agentlist.php">Agent List</a></li>
                                <li><a href="userlist.php">User List</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/time.svg" alt="img"><span> Report</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <!-- <li><a href="inventoryreport.php">Inventory Report</a></li> -->
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
                        <h4>Purchase Details</h4>
                        <h6>View purchase details</h6>
                    </div>
                    <div class="page-btn">
                        <a href="purchaselist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Purchases List</a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="card-sales-split">
                            <h2>Purchase Number : <?= ($purchase_number); ?></h2>
                            <ul>
                                <!-- <?php if ($purchase_status == 0) { ?>
                                    <li>
                                        <a href="editpurchase.php?purchaseNumber=<?= ($purchase_number); ?>">
                                            <img src="assets/img/icons/edit.svg" alt="Edit">
                                        </a>
                                    </li>
                                <?php } ?> -->
                                <!-- <?php if ($purchase_status == 0) { ?>
                                    <li>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editPurchaseModal"
                                            data-purchase-number="<?= ($purchase_number) ?>"
                                            title="Edit Purchase">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </li>
                                <?php } ?> -->

                            </ul>
                        </div>



                        <?php
                        // Get purchase details
                        $purchase_query = $conn->prepare("SELECT 
                                                        purchases.*, 
                                                        suppliers.supplierId, suppliers.supplierName, suppliers.supplierEmail, suppliers.supplierPhone, 
                                                        bank_accounts.bankAccountBankName AS supplierBankName,
                                                        bank_accounts.bankAccountNumber AS supplierBankAccountNumber,
                                                        bank_accounts.bankAccountHolderName AS supplierBankAccountHolderName,
                                                        u1.username AS purchaser,
                                                        u2.username AS updater
                                                    FROM purchases
                                                    JOIN suppliers ON purchases.purchaseSupplierId = suppliers.supplierId
                                                    LEFT JOIN bank_accounts ON purchases.purchaseSupplierAccountNumber = bank_accounts.bankAccountNumber
                                                    JOIN users AS u1 ON purchases.purchaseCreatedBy = u1.userId
                                                    JOIN users AS u2 ON purchases.purchaseUpdatedBy = u2.userId
                                                    WHERE purchases.purchaseNumber = ?");
                        $purchase_query->bind_param("s", $purchase_number);
                        $purchase_query->execute();
                        $purchase_result = $purchase_query->get_result();

                        if ($purchase_result->num_rows > 0) {
                            $purchase_row = $purchase_result->fetch_assoc();
                        ?>
                            <!-- Supplier Information Table -->
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Supplier Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Name:</strong></td>
                                                            <td><?= $purchase_row['supplierName']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Email:</strong></td>
                                                            <td>
                                                                <a href="mailto:<?= $purchase_row['supplierEmail']; ?>">
                                                                    <?= $purchase_row['supplierEmail']; ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Phone:</strong></td>
                                                            <td>
                                                                <a href="tel:<?= $purchase_row['supplierPhone']; ?>">
                                                                    <?= $purchase_row['supplierPhone']; ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Bank Name:</strong></td>
                                                            <td><?= !empty($purchase_row['supplierBankName']) ? $purchase_row['supplierBankName'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Acc. No:</strong></td>
                                                            <td><?= !empty($purchase_row['supplierBankAccountNumber']) ? $purchase_row['supplierBankAccountNumber'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Acc. Name:</strong></td>
                                                            <td><?= !empty($purchase_row['supplierBankAccountHolderName']) ? $purchase_row['supplierBankAccountHolderName'] : 'N/A'; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Purchase Statistics Table -->
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Purchase Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Purchaser:</strong></td>
                                                            <td><?= $purchase_row['purchaser']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Updated By:</strong></td>
                                                            <td><?= $purchase_row['updater']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Purchase Date:</strong></td>
                                                            <td><?= date('d/m/Y', strtotime($purchase_row['purchaseDate'])); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Created At:</strong></td>
                                                            <td><?= date('d/m/Y H:i:s', strtotime($purchase_row['created_at'])); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Updated At:</strong></td>
                                                            <td><?= date('d/m/Y H:i:s', strtotime($purchase_row['updated_at'])); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Agent Information Table -->

                            </div>

                            <!-- Financial Summary Table -->
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Financial Summary</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <tbody>
                                                        <thead>
                                                            <th>Total Products</th>
                                                            <th>Total Purchase Amount</th>
                                                            <th>Agent Transportation Cost</th>
                                                            <th>Status</th>
                                                        </thead>
                                                        <tr>
                                                            <td>
                                                                <?php
                                                                $count_query = $conn->prepare("SELECT 
                                                                                                            SUM(purchaseDetailQuantity) AS totalProducts 
                                                                                                        FROM 
                                                                                                            purchase_details 
                                                                                                        WHERE 
                                                                                                            purchaseDetailPurchaseNumber = ?");
                                                                $count_query->bind_param("s", $purchase_number);
                                                                $count_query->execute();
                                                                $count_result = $count_query->get_result();
                                                                $count_row = $count_result->fetch_assoc();
                                                                ?>
                                                                <strong><?= $count_row['totalProducts'] ?? 0; ?></strong>
                                                            </td>
                                                            <td class="text-primary">
                                                                <?php
                                                                $total_purchase_amount = $conn->prepare("SELECT SUM(purchaseDetailTotalCost) AS totalPurchasedAmount 
                                                                                                    FROM purchase_details 
                                                                                                    WHERE purchaseDetailPurchaseNumber = ?");
                                                                $total_purchase_amount->bind_param("s", $purchase_number);
                                                                $total_purchase_amount->execute();
                                                                $total_purchase_result = $total_purchase_amount->get_result();
                                                                $total_purchase_amount_row = $total_purchase_result->fetch_assoc();
                                                                ?>
                                                                <strong>Tsh: <?= number_format($total_purchase_amount_row['totalPurchasedAmount'], 2); ?></strong>
                                                            </td>
                                                            <td class="text-success">
                                                                <?php
                                                                $transport_query = $conn->prepare("SELECT SUM(agentTransportationCost) AS totalTransportCost 
                                                                                                                FROM purchase_details 
                                                                                                                WHERE purchaseDetailPurchaseNumber = ?");
                                                                $transport_query->bind_param("s", $purchase_number);
                                                                $transport_query->execute();
                                                                $transport_result = $transport_query->get_result();
                                                                $transport_row = $transport_result->fetch_assoc();
                                                                ?>
                                                                <strong>Tsh: <?= number_format($transport_row['totalTransportCost'] ?? 0, 2); ?></strong>
                                                            </td>
                                                            <td><?= $purchase_row['purchaseStatus'] == 0 ? 'Pending' : ($purchase_row['purchaseStatus'] == 1 ? 'Completed' : ($purchase_row['purchaseStatus'] == 2 ? 'Cancelled' : 'Deleted')); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Purchase Details Table -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Purchase Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>S/N</th>
                                                            <th>Product</th>
                                                            <th>Agent</th>
                                                            <!-- <th>Agent Bank Name</th> -->
                                                            <th>Agent Acc. No</th>
                                                            <!-- <th>Agent Acc. Name</th> -->
                                                            <th>Tracking Number</th>
                                                            <th>Size</th>
                                                            <th>Quantity</th>
                                                            <th>Unit Cost</th>
                                                            <th>Rate</th>
                                                            <th>Total Cost</th>
                                                            <th>Transportation</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        // Get purchase details and products using prepared statement
                                                        $details_query = $conn->prepare("SELECT 
                                                                                                    purchase_details.*, 
                                                                                                    products.productName,
                                                                                                    agents.agentName,
                                                                                                    bank_accounts.bankAccountBankName AS agentBankName,
                                                                                                    bank_accounts.bankAccountNumber AS agentBankAccountNumber,
                                                                                                    bank_accounts.bankAccountHolderName AS agentAccountHolderName
                                                                                                FROM purchase_details
                                                                                                JOIN products ON purchase_details.purchaseDetailProductId = products.productId
                                                                                                LEFT JOIN agents ON purchase_details.purchaseDetailAgentId = agents.agentId
                                                                                                LEFT JOIN bank_accounts ON purchase_details.purchaseAgentBankAccountNumber = bank_accounts.bankAccountNumber
                                                                                                WHERE purchase_details.purchaseDetailPurchaseNumber = ?
                                                                                                ORDER BY purchase_details.purchaseDetailUId ASC");
                                                        $details_query->bind_param("s", $purchase_number);
                                                        $details_query->execute();
                                                        $details_result = $details_query->get_result();

                                                        if ($details_result && $details_result->num_rows > 0) {
                                                            $sn = 1;
                                                            while ($detail = $details_result->fetch_assoc()) {
                                                        ?>
                                                                <tr>
                                                                    <td style="padding: 10px;vertical-align: top;"><?= $sn++; ?></td>
                                                                    <td style="padding: 10px;vertical-align: top; display: flex;align-items: center;">
                                                                        <?= $detail['productName']; ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= !empty($detail['agentName']) ? $detail['agentName'] : 'N/A'; ?>
                                                                    </td>
                                                                    <!-- <td style="padding: 10px;vertical-align: top;">
                                                                        <?= !empty($detail['agentBankName']) ? $detail['agentBankName'] : 'N/A'; ?>
                                                                    </td> -->
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= !empty($detail['agentBankAccountNumber']) ? $detail['agentBankAccountNumber'] : 'N/A'; ?>
                                                                    </td>
                                                                    <!-- <td style="padding: 10px;vertical-align: top;">
                                                                        <?= !empty($detail['agentAccountHolderName']) ? $detail['agentAccountHolderName'] : 'N/A'; ?>
                                                                    </td> -->
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= !empty($detail['purchaseDetailTrackingNumber']) ? $detail['purchaseDetailTrackingNumber'] : 'N/A'; ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= !empty($detail['purchaseDetailProductSize']) ? $detail['purchaseDetailProductSize'] : 'N/A'; ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top; text-align: center;">
                                                                        <?= number_format($detail['purchaseDetailQuantity']); ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= number_format($detail['purchaseDetailUnitCost'], 2); ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= number_format($detail['purchaseDetailRate']); ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top;">
                                                                        <?= number_format($detail['purchaseDetailTotalCost'], 2); ?>
                                                                    </td>
                                                                    <td style="padding: 10px;vertical-align: top; text-align: center;">
                                                                        <?= !empty($detail['agentTransportationCost']) ? number_format($detail['agentTransportationCost']) : 'N/A'; ?>
                                                                    </td>
                                                                    <td style="padding: 4px; vertical-align: middle;">
                                                                        <div class="d-flex gap-1">
                                                                            <?php if ($detail['purchaseDetailStatus'] == "0"): ?>
                                                                                <button
                                                                                    type="button"
                                                                                    class="btn btn-outline-primary btn-sm px-2 py-1"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#editProduct<?= $detail['purchaseDetailUId']; ?>"
                                                                                    title="Edit">
                                                                                    <i class="fas fa-edit"></i>
                                                                                </button>
                                                                            <?php endif; ?>
                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-outline-secondary btn-sm px-2 py-1"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#showProduct<?= $detail['purchaseDetailUId']; ?>"
                                                                                title="View">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>

                                                                </tr>

                                                                <!-- View Purchase Product Modal -->
                                                                <div class="modal fade" id="showProduct<?= $detail['purchaseDetailUId']; ?>" tabindex="-1" aria-labelledby="showProductLabel<?= $detail['purchaseDetailUId']; ?>" aria-hidden="true">
                                                                    <div class="modal-dialog modal-xl">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="showProductLabel<?= $detail['purchaseDetailUId']; ?>">View Purchase Product</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <div class="row">
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Product Name</label>
                                                                                            <p class="form-control"><?= ($detail['productName']); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Size</label>
                                                                                            <p class="form-control"><?= !empty($detail['purchaseDetailProductSize']) ? ($detail['purchaseDetailProductSize']) : 'N/A'; ?></p>
                                                                                            </p>
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Quantity</label>
                                                                                            <p class="form-control"><?= $detail['purchaseDetailQuantity']; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Unit Cost</label>
                                                                                            <p class="form-control"><?= number_format($detail['purchaseDetailUnitCost'], 2); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Rate</label>
                                                                                            <p class="form-control"><?= $detail['purchaseDetailRate']; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Total Cost</label>
                                                                                            <p class="form-control"><?= number_format($detail['purchaseDetailTotalCost'], 2); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Agent Name</label>
                                                                                            <p class="form-control"><?= !empty($detail['agentName']) ? ($detail['agentName']) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Agent Bank Name</label>
                                                                                            <p class="form-control"><?= !empty($detail['agentBankName']) ? ($detail['agentBankName']) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Agent Bank Account Number</label>
                                                                                            <p class="form-control"><?= !empty($detail['agentBankAccountNumber']) ? ($detail['agentBankAccountNumber']) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Agent Account Holder Name</label>
                                                                                            <p class="form-control"><?= !empty($detail['agentAccountHolderName']) ? ($detail['agentAccountHolderName']) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Tracking Number</label>
                                                                                            <p class="form-control"><?= !empty($detail['purchaseDetailTrackingNumber']) ? ($detail['purchaseDetailTrackingNumber']) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-12 mb-3">
                                                                                        <div class="form-group">
                                                                                            <label>Agent Transportation Cost</label>
                                                                                            <p class="form-control"><?= !empty($detail['agentTransportationCost']) ? number_format($detail['agentTransportationCost']) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                                        <div class="form-group">
                                                                                            <label>Date to Agent Abroad</label>
                                                                                            <p class="form-control"><?= !empty($detail['dateToAgentAbroadWarehouse']) ? date('d-m-Y', strtotime($detail['dateToAgentAbroadWarehouse'])) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                                        <div class="form-group">
                                                                                            <label>Date Received in Tanzania</label>
                                                                                            <p class="form-control"><?= !empty($detail['dateReceivedByAgentInCountryWarehouse']) ? date('d-m-Y', strtotime($detail['dateReceivedByAgentInCountryWarehouse'])) : 'N/A'; ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-4 col-sm-6 col-12">
                                                                                        <div class="form-group">
                                                                                            <label>Date Received by Company</label>
                                                                                            <p class="form-control"><?= !empty($detail['dateReceivedByCompany']) ? date('d-m-Y', strtotime($detail['dateReceivedByCompany'])) : 'N/A'; ?></p>
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
                                                                <!-- End of View Product Modal -->

                                                                <!-- Edit Purchase Product Modal -->
                                                                <div class="modal fade" id="editProduct<?= $detail['purchaseDetailUId']; ?>" tabindex="-1" aria-labelledby="editProductLabel<?= $detail['purchaseDetailUId']; ?>" aria-hidden="true">
                                                                    <div class="modal-dialog modal-xl">
                                                                        <form action="" method="POST">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Edit Purchase Product</h5>
                                                                                    <button type="button" class="btn btn-sm btn-info me-2 show-all-btn" onclick="toggleFields(this, true)" style="margin-left: 40px;"><i class="fas fa-chevron-down"></i></button>
                                                                                    <button type="button" class="btn btn-sm btn-warning me-2 hide-filled-btn" onclick="toggleFields(this, false)" style="display: none; margin-left: 40px;"><i class="fas fa-chevron-up"></i></button>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <input type="hidden" name="purchaseDetailUId" value="<?= $detail['purchaseDetailUId']; ?>">
                                                                                    <input type="hidden" name="purchaseNumber" value="<?= $detail['purchaseDetailPurchaseNumber']; ?>">
                                                                                    <div class="row">
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Product Name</label>
                                                                                                <input type="text" name="product_name" value="<?= ($detail['productName']); ?>" class="form-control" readonly>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Size</label>
                                                                                                <input type="text" name="product_size" value="<?= !empty($detail['purchaseDetailProductSize']) ? ($detail['purchaseDetailProductSize']) : ''; ?>" class="form-control">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Quantity</label>
                                                                                                <input type="text" id="quantity_<?= $detail['purchaseDetailUId']; ?>" name="quantity" value="<?= number_format($detail['purchaseDetailQuantity'], 0); ?>" min="1" step="1" value="<?= $detail['purchaseDetailQuantity']; ?>" class="form-control quantity" min="1" oninput="calculateTotal(this, '<?= $detail['purchaseDetailUId']; ?>')">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Unit Cost</label>
                                                                                                <input type="text" id="unitCost_<?= $detail['purchaseDetailUId']; ?>" name="unit_cost" value="<?= number_format($detail['purchaseDetailUnitCost'], 0); ?>" class="form-control unitCost" min="0" step="0.01" oninput="calculateTotal(this, '<?= $detail['purchaseDetailUId']; ?>')">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Rate</label>
                                                                                                <input type="text" id="rate_<?= $detail['purchaseDetailUId']; ?>" name="rate" value="<?= number_format($detail['purchaseDetailRate'], 2); ?>" class="form-control rate" min="0" step="0.01" oninput="calculateTotal(this, '<?= $detail['purchaseDetailUId']; ?>')">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Total Cost</label>
                                                                                                <input type="text" id="totalCost_<?= $detail['purchaseDetailUId']; ?>" name="total_cost" value="<?= number_format($detail['purchaseDetailTotalCost'], 2); ?>" class="form-control totalCost" readonly>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Agent Name</label>
                                                                                                <select class="form-control agentSelect" name="agent_name" data-purchase-details-id="<?= $detail['purchaseDetailUId']; ?>">
                                                                                                    <option value="" disabled <?= empty($detail['purchaseDetailAgentId']) ? 'selected' : ''; ?>>Select Agent</option>
                                                                                                    <?php
                                                                                                    $agents_query = $conn->query("SELECT * FROM agents");
                                                                                                    while ($agent = $agents_query->fetch_assoc()) {
                                                                                                        echo "<option value='{$agent['agentId']}'" . ($detail['purchaseDetailAgentId'] == $agent['agentId'] ? ' selected' : '') . ">{$agent['agentName']}</option>";
                                                                                                    }
                                                                                                    ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Agent Bank Name</label>
                                                                                                <select class="form-control agentBankSelect" name="agent_bank_name" id="agent_bank_name_<?= $detail['purchaseDetailUId']; ?>" disabled>
                                                                                                    <option value="<?= ($detail['agentBankName'] ?? '') ?>" selected>
                                                                                                        <?= ($detail['agentBankName'] ?? ($detail['purchaseDetailAgentId'] ? 'Choose Bank' : 'No agent selected')) ?>
                                                                                                    </option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Agent Bank Account Number</label>
                                                                                                <select class="form-control agentAccountSelect" name="agent_bank_account_number" id="agent_bank_account_number_<?= $detail['purchaseDetailUId']; ?>" disabled>
                                                                                                    <option value="<?= ($detail['agentBankAccountNumber'] ?? '') ?>" selected>
                                                                                                        <?= ($detail['agentBankAccountNumber'] ?? ($detail['agentBankName'] ? 'Choose Account Number' : 'No bank selected')) ?>
                                                                                                    </option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Agent Account Holder Name</label>
                                                                                                <input type="text" name="agent_account_holder_name" id="agent_account_holder_name_<?= $detail['purchaseDetailUId']; ?>" class="form-control" readonly value="<?= ($detail['agentAccountHolderName'] ?? ($detail['agentBankAccountNumber'] ? 'Unknown' : 'No account selected')) ?>">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Tracking Number</label>
                                                                                                <input type="text" name="tracking_number" value="<?= !empty($detail['purchaseDetailTrackingNumber']) ? ($detail['purchaseDetailTrackingNumber']) : ''; ?>" class="form-control">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-12 mb-3">
                                                                                            <div class="form-group">
                                                                                                <label>Agent Transportation Cost</label>
                                                                                                <input type="text" name="agent_transport_cost" value="<?= !empty($detail['agentTransportationCost']) ? number_format($detail['agentTransportationCost']) : ''; ?>" class="form-control quantity">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-6 col-12">
                                                                                            <div class="form-group">
                                                                                                <label>Date to Agent Abroad</label>
                                                                                                <input type="date" id="agent_abroad_date_<?= $detail['purchaseDetailUId']; ?>" name="agent_abroad_date" class="form-control"
                                                                                                    value="<?= !empty($detail['dateToAgentAbroadWarehouse']) ? ($detail['dateToAgentAbroadWarehouse']) : ''; ?>">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-6 col-12">
                                                                                            <div class="form-group">
                                                                                                <label>Date Received in Tanzania</label>
                                                                                                <input type="date" id="agent_tanzania_date_<?= $detail['purchaseDetailUId']; ?>" name="agent_tanzania_date" class="form-control"
                                                                                                    value="<?= !empty($detail['dateReceivedByAgentInCountryWarehouse']) ? ($detail['dateReceivedByAgentInCountryWarehouse']) : ''; ?>">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-4 col-sm-6 col-12">
                                                                                            <div class="form-group">
                                                                                                <label>Date Received by Company</label>
                                                                                                <input type="date" id="at_sonak_date_<?= $detail['purchaseDetailUId']; ?>" name="at_sonak_date" class="form-control"
                                                                                                    value="<?= !empty($detail['dateReceivedByCompany']) ? ($detail['dateReceivedByCompany']) : ''; ?>">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="submit" name="editPurchaseProductBTN" class="btn btn-primary">Update</button>
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                                <!-- End of Edit Product Modal -->
                                                        <?php
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>No purchase details found.</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php
                        } else {
                            echo "<p>No purchase found with the provided purchase number.</p>";
                        }
                        ?>

                        <!-- Edit Purchase Modal -->
                        <div class="modal fade" id="editPurchaseModal" tabindex="-1" aria-labelledby="editPurchaseModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                <div class="modal-content">
                                    <form action="" method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editPurchaseModalLabel">Edit Purchase</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="purchase_number" value="<?= ($purchase_number) ?>">

                                            <div class="row">
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Purchase No.</label>
                                                        <input type="text" name="purchase_number" class="form-control" value="<?= ($purchase['purchaseNumber']); ?>" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Purchase Date</label>
                                                        <input type="text" name="purchase_date" class="form-control" value="<?= date('d-m-Y', strtotime($purchase['purchaseDate'])); ?>" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Supplier Name</label>
                                                        <select class="form-control" name="supplier_name" id="supplier_name" required>
                                                            <option value="" disabled>Select Supplier</option>
                                                            <?php
                                                            $suppliers_query = $conn->query("SELECT * FROM suppliers");
                                                            while ($s = $suppliers_query->fetch_assoc()) {
                                                                $selected = $s['supplierId'] == $purchase_row['supplierId'] ? 'selected' : '';
                                                                echo '<option value="' . $s['supplierId'] . '" ' . $selected . '>' . $s['supplierName'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Bank Name</label>
                                                        <select class="form-control" name="bank_name" id="bank_name" disabled>
                                                            <option value="<?= ($purchase_row['supplierBankName'] ?? '') ?>" selected>
                                                                <?= !empty($purchase_row['supplierBankName']) ? $purchase_row['supplierBankName'] : ($purchase_row['supplierId'] ? 'Choose Bank' : 'No supplier selected') ?>
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Bank Account Number</label>
                                                        <select class="form-control" name="bank_account_number" id="bank_account_number" disabled>
                                                            <option value="<?= ($purchase_row['supplierBankAccountNumber'] ?? '') ?>" selected>
                                                                <?= !empty($purchase_row['supplierBankAccountNumber']) ? $purchase_row['supplierBankAccountNumber'] : ($purchase_row['supplierBankName'] ? 'Choose Account Number' : 'No bank selected') ?>
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Account Holder Name</label>
                                                        <input type="text" name="account_holder_name" id="account_holder_name" class="form-control" readonly
                                                            value="<?= !empty($purchase_row['supplierBankAccountHolderName']) ? $purchase_row['supplierBankAccountHolderName'] : ($purchase_row['supplierBankAccountNumber'] ? 'Unknown' : 'No account selected') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="editPurchaseBTN" class="btn btn-success">Save Changes</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- /End of Edit Purchase Modal -->

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // On modal show, hide filled fields by default
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('show.bs.modal', function() {
                    modal.querySelectorAll('input, select, textarea').forEach(field => {
                        let col = field.closest('.col-lg-4');
                        if (!col) return;

                        // hide if already has value
                        if (field.value && field.value.trim() !== '') {
                            col.style.display = 'none';
                        } else {
                            col.style.display = 'block';
                        }
                    });

                    // Ensure show button visible, hide button hidden
                    let showBtn = modal.querySelector('.show-all-btn');
                    let hideBtn = modal.querySelector('.hide-filled-btn');
                    if (showBtn) showBtn.style.display = 'inline-block';
                    if (hideBtn) hideBtn.style.display = 'none';
                });
            });
        });

        // Toggle function
        function toggleFields(btn, showAll) {
            let modal = btn.closest('.modal');
            if (showAll) {
                // Show all columns
                modal.querySelectorAll('.col-lg-4').forEach(col => {
                    col.style.display = 'block';
                    // Set fields that were previously filled to readonly/disabled
                    let field = col.querySelector('input, select, textarea');
                    if (field && field.value && field.value.trim() !== '') {
                        if (field.tagName === 'SELECT') {
                            field.disabled = false;
                        } else {
                            field.readOnly = true;
                        }
                    }
                });
                btn.style.display = 'none';
                modal.querySelector('.hide-filled-btn').style.display = 'inline-block';
            } else {
                // Hide filled fields again
                modal.querySelectorAll('input, select, textarea').forEach(field => {
                    let col = field.closest('.col-lg-4');
                    if (!col) return;

                    if (field.value && field.value.trim() !== '') {
                        col.style.display = 'none';
                    } else {
                        col.style.display = 'block';
                    }
                });
                btn.style.display = 'none';
                modal.querySelector('.show-all-btn').style.display = 'inline-block';
            }
        }
    </script>

    <script>
        // Format numbers with commas
        function numberFormatter(number, decimals = 0) {
            if (number === null || number === "null" || number === "") {
                return "";
            }

            try {
                let value = parseFloat(number);
                if (isNaN(value)) return "";
                return value.toLocaleString(undefined, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            } catch (e) {
                console.error("Invalid number format:", number, e);
                return "";
            }
        }

        // Calculate total cost
        function calculateTotal(input, purchaseDetailUId) {
            const modal = input.closest('.modal');
            if (!modal) return;

            const getNumber = (id, defaultValue = 0) => {
                const el = modal.querySelector(`#${id}_${purchaseDetailUId}`);
                if (!el) return defaultValue;
                const raw = el.value.replace(/,/g, '');
                return parseFloat(raw) || defaultValue;
            };

            const quantity = getNumber("quantity", 1);
            const unitCost = getNumber("unitCost", 1);
            const rate = getNumber("rate", 1);
            const total = quantity * unitCost * rate;

            const totalEl = modal.querySelector(`#totalCost_${purchaseDetailUId}`);
            if (totalEl) {
                totalEl.value = numberFormatter(total, 2);
            }
        }

        document.querySelectorAll('.unitCost, .rate, .quantity').forEach(input => {
            input.addEventListener('input', () => {
                // Remove all non-digit and non-decimal characters
                let raw = input.value.replace(/[^0-9.,]/g, '');

                // Prevent multiple decimals or commas
                const parts = raw.split('.');
                if (parts.length > 2) {
                    raw = parts[0] + '.' + parts.slice(1).join('');
                }

                input.value = raw;
            });

            input.addEventListener('blur', () => {
                const raw = input.value.replace(/,/g, '');
                if (input.classList.contains('quantity')) {
                    input.value = numberFormatter(raw, 0);
                } else {
                    input.value = numberFormatter(raw, 2);
                }
            });
        });



        document.addEventListener("DOMContentLoaded", function() {
            // Date validation for each modal
            document.querySelectorAll('.modal').forEach(modal => {
                const purchaseDetailUId = modal.id.replace('editProduct', '');
                const abroadDate = modal.querySelector(`#agent_abroad_date_${purchaseDetailUId}`);
                const tanzaniaDate = modal.querySelector(`#agent_tanzania_date_${purchaseDetailUId}`);
                const companyDate = modal.querySelector(`#at_sonak_date_${purchaseDetailUId}`);

                const today = new Date().toISOString().split("T")[0];

                if (abroadDate) {
                    abroadDate.setAttribute("min", today);
                    abroadDate.setAttribute("max", today);
                }

                if (tanzaniaDate) {
                    tanzaniaDate.setAttribute("min", today);
                    tanzaniaDate.setAttribute("max", today);
                    tanzaniaDate.addEventListener("change", function() {
                        if (tanzaniaDate.value && tanzaniaDate.value > today) {
                            tanzaniaDate.value = today;
                        }
                        if (companyDate) {
                            companyDate.setAttribute("min", tanzaniaDate.value);
                        }
                    });
                }

                if (companyDate) {
                    companyDate.setAttribute("max", today);
                    if (tanzaniaDate && tanzaniaDate.value) {
                        companyDate.setAttribute("min", tanzaniaDate.value);
                    } else {
                        companyDate.setAttribute("min", today);
                    }
                    if (tanzaniaDate) {
                        tanzaniaDate.addEventListener("change", function() {
                            if (tanzaniaDate.value) {
                                companyDate.setAttribute("min", tanzaniaDate.value);
                                if (companyDate.value && companyDate.value < tanzaniaDate.value) {
                                    companyDate.value = "";
                                }
                            } else {
                                companyDate.setAttribute("min", today);
                            }
                        });
                    }
                    companyDate.addEventListener("change", function() {
                        if (companyDate.value) {
                            if (companyDate.value > today) {
                                companyDate.value = today;
                            }
                            if (tanzaniaDate && tanzaniaDate.value && companyDate.value < tanzaniaDate.value) {
                                companyDate.value = tanzaniaDate.value;
                            }
                        }
                    });
                }
            });


            // Agent bank details logic
            function clearAgentBankFields(modal, from) {
                const purchaseDetailUId = modal.id.replace("editProduct", "");
                const bankSelect = modal.querySelector(`#agent_bank_name_${purchaseDetailUId}`);
                const accountSelect = modal.querySelector(`#agent_bank_account_number_${purchaseDetailUId}`);
                const holderInput = modal.querySelector(`#agent_account_holder_name_${purchaseDetailUId}`);

                if (from <= 1) {
                    bankSelect.innerHTML = '<option value="" selected>No agent selected</option>';
                    bankSelect.disabled = true;
                }
                if (from <= 2) {
                    accountSelect.innerHTML = '<option value="" selected>No bank selected</option>';
                    accountSelect.disabled = true;
                }
                if (from <= 3) {
                    holderInput.value = "No account selected";
                    holderInput.readOnly = true;
                }
            }

            // Initialize bank + account + holder when modal loads or already has values
            function initializeAgentBankDetails(modal, purchaseDetailUId, agentId) {
                if (!agentId) {
                    clearAgentBankFields(modal, 1);
                    return;
                }

                const bankSelect = modal.querySelector(`#agent_bank_name_${purchaseDetailUId}`);
                const accountSelect = modal.querySelector(`#agent_bank_account_number_${purchaseDetailUId}`);
                const holderInput = modal.querySelector(`#agent_account_holder_name_${purchaseDetailUId}`);
                const preSelectedBank = bankSelect.value; // Get pre-selected bank from HTML
                const preSelectedAccount = accountSelect.value; // Get pre-selected account number from HTML

                fetch(`get_agent_bank_details.php?agentId=${agentId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success && data.data.length > 0) {
                            bankSelect.innerHTML = '<option value="" disabled selected>Choose Bank</option>';
                            data.data.forEach((bank) => {
                                const option = document.createElement("option");
                                option.value = bank;
                                option.textContent = bank;
                                // Set pre-selected bank if it matches
                                if (bank === preSelectedBank) {
                                    option.selected = true;
                                }
                                bankSelect.appendChild(option);
                            });
                            bankSelect.disabled = false;

                            // If a bank was pre-selected, fetch its account numbers
                            if (preSelectedBank) {
                                fetch(`get_agent_bank_details.php?agentId=${agentId}&bankName=${encodeURIComponent(preSelectedBank)}`)
                                    .then((response) => response.json())
                                    .then((data) => {
                                        if (data.success && data.data.length > 0) {
                                            accountSelect.innerHTML = '<option value="" disabled selected>Choose Account Number</option>';
                                            data.data.forEach((account) => {
                                                const option = document.createElement("option");
                                                option.value = account.accountNumber;
                                                option.textContent = account.accountNumber;
                                                option.dataset.holder = account.holderName;
                                                // Set pre-selected account number if it matches
                                                if (account.accountNumber === preSelectedAccount) {
                                                    option.selected = true;
                                                }
                                                accountSelect.appendChild(option);
                                            });
                                            accountSelect.disabled = false;

                                            // If an account number is pre-selected, update the holder name
                                            if (preSelectedAccount) {
                                                const selectedOption = accountSelect.querySelector(`option[value="${preSelectedAccount}"]`);
                                                if (selectedOption) {
                                                    holderInput.value = selectedOption.dataset.holder || "Unknown";
                                                }
                                            }
                                        } else {
                                            clearAgentBankFields(modal, 2);
                                        }
                                    })
                                    .catch((error) => {
                                        Swal.fire({
                                            title: "Error",
                                            text: "Failed to fetch account numbers: " + error.message,
                                            icon: "error",
                                        });
                                    });
                            }
                        } else {
                            clearAgentBankFields(modal, 1);
                        }
                    })
                    .catch((error) => {
                        Swal.fire({
                            title: "Error",
                            text: "Failed to fetch bank accounts: " + error.message,
                            icon: "error",
                        });
                    });
            }

            // Initialize all modals with pre-filled agent
            document.querySelectorAll(".modal").forEach((modal) => {
                const purchaseDetailUId = modal.id.replace("editProduct", "");
                const agentSelect = modal.querySelector(`.agentSelect[data-purchase-details-id="${purchaseDetailUId}"]`);
                if (agentSelect && agentSelect.value) {
                    initializeAgentBankDetails(modal, purchaseDetailUId, agentSelect.value);
                }
            });

            // Handle agent change
            document.querySelectorAll(".agentSelect").forEach((agentSelect) => {
                agentSelect.addEventListener("change", function() {
                    const purchaseDetailUId = this.dataset.purchaseDetailsId;
                    const modal = this.closest(".modal");
                    const agentId = this.value;

                    clearAgentBankFields(modal, 1);

                    if (agentId) {
                        initializeAgentBankDetails(modal, purchaseDetailUId, agentId);
                    }
                });
            });

            // Handle bank name change
            document.querySelectorAll(".agentBankSelect").forEach((bankSelect) => {
                bankSelect.addEventListener("change", function() {
                    const purchaseDetailUId = this.id.replace("agent_bank_name_", "");
                    const modal = this.closest(".modal");
                    const agentId = modal.querySelector(`.agentSelect[data-purchase-details-id="${purchaseDetailUId}"]`).value;
                    const bankName = this.value;
                    const accountSelect = modal.querySelector(`#agent_bank_account_number_${purchaseDetailUId}`);
                    const holderInput = modal.querySelector(`#agent_account_holder_name_${purchaseDetailUId}`);

                    clearAgentBankFields(modal, 2);

                    if (bankName) {
                        fetch(`get_agent_bank_details.php?agentId=${agentId}&bankName=${encodeURIComponent(bankName)}`)
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.success && data.data.length > 0) {
                                    accountSelect.innerHTML = '<option value="" disabled selected>Choose Account Number</option>';
                                    data.data.forEach((account) => {
                                        const option = document.createElement("option");
                                        option.value = account.accountNumber;
                                        option.textContent = account.accountNumber;
                                        option.dataset.holder = account.holderName;
                                        accountSelect.appendChild(option);
                                    });
                                    accountSelect.disabled = false;

                                    // If pre-selected, trigger change
                                    if (accountSelect.value) {
                                        accountSelect.dispatchEvent(new Event("change"));
                                    }
                                } else {
                                    Swal.fire({
                                        title: "No Accounts",
                                        text: data.message || "No accounts found for this bank.",
                                        icon: "info",
                                        timer: 3000,
                                        timerProgressBar: true,
                                    });
                                }
                            })
                            .catch((error) => {
                                Swal.fire({
                                    title: "Error",
                                    text: "Failed to fetch account numbers: " + error.message,
                                    icon: "error",
                                });
                            });
                    }
                });
            });

            // Handle account number change
            document.querySelectorAll(".agentAccountSelect").forEach((accountSelect) => {
                accountSelect.addEventListener("change", function() {
                    const purchaseDetailUId = this.id.replace("agent_bank_account_number_", "");
                    const modal = this.closest(".modal");
                    const holderInput = modal.querySelector(`#agent_account_holder_name_${purchaseDetailUId}`);

                    clearAgentBankFields(modal, 3);

                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        holderInput.value = selectedOption.dataset.holder || "Unknown";
                    }
                });
            });

            // Supplier bank details logic
            function clearSupplierBankFields(modal, from) {
                const bankSelect = modal.querySelector(`#bank_name`);
                const accountSelect = modal.querySelector(`#bank_account_number`);
                const holderInput = modal.querySelector(`#account_holder_name`);

                if (from <= 1) {
                    bankSelect.innerHTML = '<option value="" selected>No supplier selected</option>';
                    bankSelect.disabled = true;
                }
                if (from <= 2) {
                    accountSelect.innerHTML = '<option value="" selected>No bank selected</option>';
                    accountSelect.disabled = true;
                }
                if (from <= 3) {
                    holderInput.value = "No account selected";
                    holderInput.readOnly = true;
                }
            }

            function initializeSupplierBankDetails(modal, supplierId) {
                if (!supplierId) {
                    clearSupplierBankFields(modal, 1);
                    return;
                }

                const bankSelect = modal.querySelector(`#bank_name`);
                const accountSelect = modal.querySelector(`#bank_account_number`);
                const holderInput = modal.querySelector(`#account_holder_name`);
                const preSelectedBank = bankSelect.value;
                const preSelectedAccount = accountSelect.value;

                fetch(`get_supplier_bank_details.php?supplierId=${supplierId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            bankSelect.innerHTML = '<option value="" disabled selected>Choose Bank</option>';
                            data.data.forEach(bank => {
                                const option = document.createElement("option");
                                option.value = bank;
                                option.textContent = bank;
                                if (bank === preSelectedBank) {
                                    option.selected = true;
                                }
                                bankSelect.appendChild(option);
                            });
                            bankSelect.disabled = false;

                            // If a bank was pre-selected, fetch its account numbers
                            if (preSelectedBank) {
                                fetch(`get_supplier_bank_details.php?supplierId=${supplierId}&bankName=${encodeURIComponent(preSelectedBank)}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success && data.data.length > 0) {
                                            accountSelect.innerHTML = '<option value="" disabled selected>Choose Account Number</option>';
                                            data.data.forEach(account => {
                                                const option = document.createElement("option");
                                                option.value = account.accountNumber;
                                                option.textContent = account.accountNumber;
                                                option.dataset.holder = account.holderName;
                                                if (account.accountNumber === preSelectedAccount) {
                                                    option.selected = true;
                                                }
                                                accountSelect.appendChild(option);
                                            });
                                            accountSelect.disabled = false;

                                            if (preSelectedAccount) {
                                                const selectedOption = accountSelect.querySelector(`option[value="${preSelectedAccount}"]`);
                                                if (selectedOption) {
                                                    holderInput.value = selectedOption.dataset.holder || "Unknown";
                                                }
                                            }
                                        } else {
                                            clearSupplierBankFields(modal, 2);
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire("Error", "Failed to fetch supplier account numbers: " + error.message, "error");
                                    });
                            }
                        } else {
                            clearSupplierBankFields(modal, 1);
                        }
                    })
                    .catch(error => {
                        Swal.fire("Error", "Failed to fetch supplier banks: " + error.message, "error");
                    });
            }

            // Event bindings for supplier modal 
            const supplierModal = document.querySelector("#editPurchaseModal");
            if (supplierModal) {
                const supplierSelect = supplierModal.querySelector("#supplier_name");
                const bankSelect = supplierModal.querySelector("#bank_name");
                const accountSelect = supplierModal.querySelector("#bank_account_number");

                // Initialize when modal opens with pre-selected supplier
                if (supplierSelect && supplierSelect.value) {
                    initializeSupplierBankDetails(supplierModal, supplierSelect.value);
                }

                // Supplier change
                supplierSelect.addEventListener("change", function() {
                    clearSupplierBankFields(supplierModal, 1);
                    if (this.value) {
                        initializeSupplierBankDetails(supplierModal, this.value);
                    }
                });

                // Bank change
                bankSelect.addEventListener("change", function() {
                    const supplierId = supplierSelect.value;
                    const bankName = this.value;

                    clearSupplierBankFields(supplierModal, 2);

                    if (bankName) {
                        fetch(`get_supplier_bank_details.php?supplierId=${supplierId}&bankName=${encodeURIComponent(bankName)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.data.length > 0) {
                                    accountSelect.innerHTML = '<option value="" disabled selected>Choose Account Number</option>';
                                    data.data.forEach(account => {
                                        const option = document.createElement("option");
                                        option.value = account.accountNumber;
                                        option.textContent = account.accountNumber;
                                        option.dataset.holder = account.holderName;
                                        accountSelect.appendChild(option);
                                    });
                                    accountSelect.disabled = false;
                                } else {
                                    Swal.fire("No Accounts", data.message || "No accounts found for this bank.", "info");
                                }
                            })
                            .catch(error => {
                                Swal.fire("Error", "Failed to fetch supplier account numbers: " + error.message, "error");
                            });
                    }
                });

                // Account change
                accountSelect.addEventListener("change", function() {
                    const holderInput = supplierModal.querySelector("#account_holder_name");
                    clearSupplierBankFields(supplierModal, 3);

                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        holderInput.value = selectedOption.dataset.holder || "Unknown";
                    }
                });
            }
        });
    </script>
    <script>

    </script>

    <script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>
</body>

</html>
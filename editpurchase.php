<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session 
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Get purchaseNumber from URL
$purchaseNumber = isset($_GET['purchaseNumber']) ? $_GET['purchaseNumber'] : '';

if (empty($purchaseNumber)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Error!',
                text: 'Invalid purchase number!',
                timer: 5000,
                timerProgressBar: true
            }).then(function(){
                window.location.href = 'purchaselist.php';
            });
        });
    </script>";
    exit;
}

// Fetch purchase details
$purchase_stmt = $conn->prepare("SELECT purchases.*, 
                                            bank_accounts.bankAccountBankName AS bankName, 
                                            bank_accounts.bankAccountNumber, 
                                            bank_accounts.bankAccountHolderName AS accountHolderName
                                        FROM purchases 
                                        LEFT JOIN bank_accounts 
                                        ON purchases.purchaseSupplierAccountNumber = bank_accounts.bankAccountNumber 
                                        WHERE purchases.purchaseNumber = ?");
$purchase_stmt->bind_param("s", $purchaseNumber);
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

// Fetch purchase details (products)
$details_stmt = $conn->prepare("SELECT * FROM purchase_details WHERE purchaseDetailPurchaseNumber = ?");
$details_stmt->bind_param("s", $purchaseNumber);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
$products = [];
while ($row = $details_result->fetch_assoc()) {
    $products[] = $row;
}

// Handle form submission for updating purchase
if (isset($_POST['updatePurchaseBTN'])) {

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
        $update_purchase_stmt = $conn->prepare(
            "UPDATE purchases SET purchaseSupplierId = ?, purchaseSupplierAccountNumber = ?, purchaseDate = ?, purchaseUpdatedBy = ?, updated_at = ? 
            WHERE purchaseNumber = ?"
        );
        $update_purchase_stmt->bind_param("ssssss", $supplierId, $bankAccountNumber, $purchaseDate, $updatedBy, $current_time, $purchaseNumber);
        $update_purchase_stmt->execute();
        $update_purchase_stmt->close();

        // Delete existing purchase details
        $delete_details_stmt = $conn->prepare("DELETE FROM purchase_details WHERE purchaseDetailPurchaseNumber = ?");
        $delete_details_stmt->bind_param("s", $purchaseNumber);
        $delete_details_stmt->execute();
        $delete_details_stmt->close();

        // Insert updated products
        foreach ($_POST['products'] as $product) {
            $productId   = $product['product_name'];
            $quantity    = $product['quantity'];
            $unitCost    = $product['unit_cost'];
            $rate        = $product['rate'];
            $totalCost   = $product['total_cost'];
            
            $insert_details_stmt = $conn->prepare("INSERT INTO purchase_details(
                purchaseDetailPurchaseNumber, purchaseDetailProductId, purchaseDetailAgentId, purchaseDetailTrackingNumber,
                purchaseDetailQuantity, purchaseDetailUnitCost, purchaseDetailRate, purchaseDetailTotalCost,
                agentTransportationCost, dateToAgentAbroadWarehouse,
                dateReceivedByAgentInCountryWarehouse, dateReceivedByCompany,
                created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_details_stmt->bind_param(
                "siisidddssssss",
                $purchaseNumber,
                $productId,
                $agentId,
                $trackingNo,
                $quantity,
                $unitCost,
                $rate,
                $totalCost,
                $transportCost,
                $dateToAgent,
                $dateReceivedTZ,
                $dateAtSonak,
                $current_time,
                $current_time
            );
            $insert_details_stmt->execute();
            $insert_details_stmt->close();
        }

        $conn->commit();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Purchase updated successfully!'
                }).then(function(){
                    window.location.href = 'purchaselist.php';
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
                    window.location.href = 'editpurchase.php?purchaseNumber=$purchaseNumber';
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
    <title>Sonak Inventory | Edit Purchase</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"></div>
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
                        <h4>Edit Purchase</h4>
                        <h6>Update Purchase Details</h6>
                    </div>
                    <div class="page-btn">
                        <a href="purchaselist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Purchases List</a>
                    </div>
                </div>
                <!-- Edit Purchase -->
                <div class="card">
                    <div class="card-body">
                        <!-- Step Progress Indicator -->
                        <div class="container mb-4">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="progress-container">
                                        <div class="step-progress d-flex justify-content-between align-items-center mb-3">
                                            <div class="step-item text-center" id="stepIndicator1">
                                                <div class="step-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">1</div>
                                                <div class="step-label mt-1 small">Purchase Details</div>
                                            </div>
                                            <div class="progress-line flex-grow-1 mx-3" style="height: 2px; background-color: #e9ecef;"></div>
                                            <div class="step-item text-center" id="stepIndicator2">
                                                <div class="step-circle bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</div>
                                                <div class="step-label mt-1 small">Add Products</div>
                                            </div>
                                            <div class="progress-line flex-grow-1 mx-3" style="height: 2px; background-color: #e9ecef;"></div>
                                            <div class="step-item text-center" id="stepIndicator3">
                                                <div class="step-circle bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">3</div>
                                                <div class="step-label mt-1 small">Summary</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Purchase Form -->
                        <form action="" method="POST" enctype="multipart/form-data" id="purchaseForm">
                            <!-- STEP 1: Purchase Details -->
                            <div id="step1">
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
                                            <input type="text" name="purchase_date" class="form-control datetimepicker" value="<?= date('d-m-Y', strtotime($purchase['purchaseDate'])); ?>" required>
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
                                                    $selected = $s['supplierId'] == $purchase['supplierId'] ? 'selected' : '';
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
                                                <option value="<?= ($purchase['bankName'] ?? '') ?>" selected>
                                                    <?= ($purchase['bankName'] ?? ($purchase['supplierId'] ? 'Choose Bank' : 'No supplier selected')) ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Bank Account Number</label>
                                            <select class="form-control" name="bank_account_number" id="bank_account_number" disabled>
                                                <option value="<?= ($purchase['bankAccountNumber'] ?? '') ?>" selected>
                                                    <?= ($purchase['bankAccountNumber'] ?? ($purchase['bankName'] ? 'Choose Account Number' : 'No bank selected')) ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Account Holder Name</label>
                                            <input type="text" name="account_holder_name" id="account_holder_name" class="form-control" readonly
                                                value="<?= ($purchase['accountHolderName'] ?? ($purchase['bankAccountNumber'] ? 'Unknown' : 'No account selected')) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="goStep2">Next</button>
                                </div>
                            </div>

                            <!-- STEP 2: Add Products -->
                            <div id="step2" style="display:none;">
                                <div id="productsContainer">
                                    <?php
                                    foreach ($products as $index => $product) {
                                    ?>
                                        <div class="row product-row align-items-end gy-2 mb-3">
                                            <div class="col-lg-3 col-sm-6 col-12">
                                                <label class="form-label">Product</label>
                                                <select name="products[<?= $index ?>][product_name]" class="form-control productSelect" required>
                                                    <option value="" disabled>Select Product</option>
                                                    <?php
                                                    $products_query = $conn->query("SELECT * FROM products");
                                                    while ($p = $products_query->fetch_assoc()) {
                                                        $selected = $p['productId'] == $product['purchaseDetailProductId'] ? 'selected' : '';
                                                        echo '<option value="' . $p['productId'] . '" ' . $selected . '>' . $p['productName'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="products[<?= $index ?>][quantity]" class="form-control quantity" value="<?= ($product['purchaseDetailQuantity']); ?>" min="1" required>
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Unit Cost</label>
                                                <input type="number" name="products[<?= $index ?>][unit_cost]" class="form-control unitCost" value="<?= ($product['purchaseDetailUnitCost']); ?>" step="0.01" required>
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Rate</label>
                                                <input type="number" name="products[<?= $index ?>][rate]" class="form-control rate" value="<?= ($product['purchaseDetailRate']); ?>" step="0.01" required>
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Total Cost</label>
                                                <input type="text" name="products[<?= $index ?>][total_cost]" class="form-control totalCost" value="<?= ($product['purchaseDetailTotalCost']); ?>" readonly>
                                            </div>
                                            <div class="col-lg-1 col-sm-6 col-12 text-end">
                                                <label class="form-label d-block">&nbsp;</label>
                                                <button type="button" class="btn btn-danger removeProduct">X</button>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <button type="button" class="btn btn-success" id="addProductRow">+ Add Product</button>
                                <br><br>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep1">Back</button>
                                    <button type="button" class="btn btn-primary" id="goStep3">Next</button>
                                </div>
                            </div>

                            <!-- STEP 3: Purchase Summary & Totals -->
                            <div id="step3" style="display:none;">
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered" id="summaryTable">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Unit Cost</th>
                                                <th>Rate</th>
                                                <th>Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Rows will be dynamically inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total Products</label>
                                            <input type="text" name="total_products" id="totalProducts" class="form-control" value="<?= array_sum(array_column($products, 'quantity')); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total Amount</label>
                                            <input type="text" id="totalAmount" name="total_amount" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Purchase Status</label>
                                            <select class="select" name="purchase_status" required>
                                                <option value="" disabled>Select Status</option>
                                                <option value="0" <?= $purchase['purchaseStatus'] == 0 ? 'selected' : ''; ?>>Pending</option>
                                                <option value="1" <?= $purchase['purchaseStatus'] == 1 ? 'selected' : ''; ?>>Completed</option>
                                                <option value="2" <?= $purchase['purchaseStatus'] == 2 ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div> -->
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep2">Back</button>
                                    <button type="submit" name="updatePurchaseBTN" class="btn btn-success">Update Purchase</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const supplierSelect = document.getElementById('supplier_name');
            const bankSelect = document.getElementById('bank_name');
            const accountSelect = document.getElementById('bank_account_number');
            const holderInput = document.getElementById('account_holder_name');

            // Clear downstream fields
            function clearFields(from) {
                if (from <= 1) {
                    bankSelect.innerHTML = '<option value="" selected>No supplier selected</option>';
                    bankSelect.disabled = true;
                }
                if (from <= 2) {
                    accountSelect.innerHTML = '<option value="" selected>No bank selected</option>';
                    accountSelect.disabled = true;
                }
                if (from <= 3) {
                    holderInput.value = 'No account selected';
                    holderInput.readOnly = true;
                }
            }

            // Initialize bank details on page load
            function initializeBankDetails() {
                const supplierId = supplierSelect.value;
                if (supplierId) {
                    fetch(`get_supplier_bank_details.php?supplierId=${supplierId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                bankSelect.innerHTML = '<option value="" disabled>Choose Bank</option>';
                                data.data.forEach(bank => {
                                    const option = document.createElement('option');
                                    option.value = bank;
                                    option.textContent = bank;
                                    if (bank === '<?= ($purchase['bankName'] ?? '') ?>') {
                                        option.selected = true;
                                    }
                                    bankSelect.appendChild(option);
                                });
                                bankSelect.disabled = false;

                                // Fetch account numbers for the selected bank
                                const bankName = bankSelect.value;
                                if (bankName) {
                                    fetch(`get_supplier_bank_details.php?supplierId=${supplierId}&bankName=${encodeURIComponent(bankName)}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success && data.data.length > 0) {
                                                accountSelect.innerHTML = '<option value="" disabled>Choose Account Number</option>';
                                                data.data.forEach(account => {
                                                    const option = document.createElement('option');
                                                    option.value = account.accountNumber;
                                                    option.textContent = account.accountNumber;
                                                    option.dataset.holder = account.holderName;
                                                    if (account.accountNumber === '<?= ($purchase['bankAccountNumber'] ?? '') ?>') {
                                                        option.selected = true;
                                                    }
                                                    accountSelect.appendChild(option);
                                                });
                                                accountSelect.disabled = false;

                                                // Set account holder name
                                                const selectedOption = accountSelect.options[accountSelect.selectedIndex];
                                                if (selectedOption.value) {
                                                    holderInput.value = selectedOption.dataset.holder || 'Unknown';
                                                }
                                            } else {
                                                clearFields(2);
                                            }
                                        })
                                        .catch(error => {
                                            Swal.fire({
                                                title: 'Error',
                                                text: 'Failed to fetch account numbers: ' + error.message,
                                                icon: 'error'
                                            });
                                        });
                                }
                            } else {
                                clearFields(1);
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to fetch bank accounts: ' + error.message,
                                icon: 'error'
                            });
                        });
                }
            }

            // Call initialization on page load
            initializeBankDetails();

            // On supplier change, fetch bank names
            supplierSelect.addEventListener('change', function() {
                const supplierId = this.value;
                clearFields(1);

                if (supplierId) {
                    fetch(`get_supplier_bank_details.php?supplierId=${supplierId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                bankSelect.innerHTML = '<option value="" selected>Choose Bank</option>';
                                data.data.forEach(bank => {
                                    const option = document.createElement('option');
                                    option.value = bank;
                                    option.textContent = bank;
                                    bankSelect.appendChild(option);
                                });
                                bankSelect.disabled = false;
                            } else {
                                Swal.fire({
                                    title: 'No Bank Accounts',
                                    text: data.message || 'No bank accounts found for this supplier.',
                                    icon: 'info'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to fetch bank accounts: ' + error.message,
                                icon: 'error'
                            });
                        });
                }
            });

            // On bank name change, fetch account numbers
            bankSelect.addEventListener('change', function() {
                const supplierId = supplierSelect.value;
                const bankName = this.value;
                clearFields(2);

                if (bankName) {
                    fetch(`get_supplier_bank_details.php?supplierId=${supplierId}&bankName=${encodeURIComponent(bankName)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                accountSelect.innerHTML = '<option value="" selected>Choose Account Number</option>';
                                data.data.forEach(account => {
                                    const option = document.createElement('option');
                                    option.value = account.accountNumber;
                                    option.textContent = account.accountNumber;
                                    option.dataset.holder = account.holderName;
                                    accountSelect.appendChild(option);
                                });
                                accountSelect.disabled = false;
                            } else {
                                Swal.fire({
                                    title: 'No Accounts',
                                    text: data.message || 'No accounts found for this bank.',
                                    icon: 'info',
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to fetch account numbers: ' + error.message,
                                icon: 'error'
                            });
                        });
                }
            });

            // On account number change, update holder name
            accountSelect.addEventListener('change', function() {
                clearFields(3);
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    holderInput.value = selectedOption.dataset.holder || 'Unknown';
                }
            });

            // Step 1 validation on Next
            document.getElementById('goStep2').addEventListener('click', function(e) {
                let valid = true;
                let errorMsg = "";
                const requiredFields = [
                    'purchase_number',
                    'supplier_name',
                    'purchase_date',
                    'bank_name',
                    'bank_account_number',
                    'account_holder_name'
                ];
                requiredFields.forEach(function(name) {
                    const field = document.getElementsByName(name)[0];
                    if (field && (field.value === '' || field.value === null || field.value === 'No supplier selected' || field.value === 'No bank selected' || field.value === 'No account selected' || field.value === 'Choose Bank' || field.value === 'Choose Account Number')) {
                        valid = false;
                        errorMsg += `Please fill the ${name.replace('_', ' ')} field.<br>`;
                    }
                });
                if (!valid) {
                    Swal.fire({
                        title: 'Error!',
                        html: errorMsg,
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                showStep(2);
            });

            // Step navigation functions
            function showStep(stepNumber) {
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step3').style.display = 'none';
                document.getElementById('step' + stepNumber).style.display = 'block';
                updateStepIndicators(stepNumber);
            }

            function updateStepIndicators(currentStep) {
                for (let i = 1; i <= 3; i++) {
                    const indicator = document.getElementById('stepIndicator' + i);
                    const circle = indicator.querySelector('.step-circle');
                    if (i === currentStep) {
                        circle.className = 'step-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center';
                    } else if (i < currentStep) {
                        circle.className = 'step-circle bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center';
                    } else {
                        circle.className = 'step-circle bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center';
                    }
                }
            }

            // Step navigation event listeners
            document.getElementById('goStep2').addEventListener('click', function() {
                showStep(2);
            });
            document.getElementById('backStep1').addEventListener('click', function() {
                showStep(1);
            });
            document.getElementById('goStep3').addEventListener('click', function() {
                calculateTotalAmount();
                const tableBody = document.querySelector("#summaryTable tbody");
                tableBody.innerHTML = "";
                document.querySelectorAll(".product-row").forEach(row => {
                    const productName = row.querySelector(".productSelect").options[row.querySelector(".productSelect").selectedIndex].text;
                    const quantity = row.querySelector(".quantity").value;
                    const unitCost = parseFloat(row.querySelector(".unitCost").value).toFixed(2);
                    const rate = parseFloat(row.querySelector(".rate").value).toFixed(2);
                    const totalCost = parseFloat(row.querySelector(".totalCost").value).toFixed(2);
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                <td>${productName}</td>
                <td>${quantity}</td>
                <td>${unitCost}</td>
                <td>${rate}</td>
                <td>${totalCost}</td>
            `;
                    tableBody.appendChild(tr);
                });
                showStep(3);
            });
            document.getElementById('backStep2').addEventListener('click', function() {
                showStep(2);
            });

            // Product row functions
            const productsContainer = document.getElementById("productsContainer");

            // Add product row
            document.getElementById("addProductRow").onclick = function() {
                let index = document.querySelectorAll(".product-row").length;
                let row = document.createElement("div");
                row.classList.add("row", "product-row", "align-items-end", "gy-2", "mb-3");
                row.innerHTML = `
            <div class="col-lg-2 col-sm-6 col-12">
                <label class="form-label">Product</label>
                <select name="products[${index}][product_name]" class="form-control productSelect" required>
                    <option value="" disabled selected>Select Product</option>
                    <?php
                    $products_query = $conn->query("SELECT * FROM products");
                    while ($p = $products_query->fetch_assoc()) {
                        echo '<option value="' . $p['productId'] . '">' . $p['productName'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-lg-2 col-sm-6 col-12">
                <label class="form-label">Quantity</label>
                <input type="number" name="products[${index}][quantity]" class="form-control quantity" value="1" min="1">
            </div>
            <div class="col-lg-2 col-sm-6 col-12">
                <label class="form-label">Unit Cost</label>
                <input type="number" name="products[${index}][unit_cost]" class="form-control unitCost" value="0" step="0.01">
            </div>
            <div class="col-lg-2 col-sm-6 col-12">
                <label class="form-label">Rate</label>
                <input type="number" name="products[${index}][rate]" class="form-control rate" value="1" step="0.01">
            </div>
            <div class="col-lg-2 col-sm-6 col-12">
                <label class="form-label">Total Cost</label>
                <input type="text" name="products[${index}][total_cost]" class="form-control totalCost" readonly>
            </div>
            <div class="col-lg-1 col-sm-6 col-12 text-end">
                <label class="form-label d-block">&nbsp;</label>
                <button type="button" class="btn btn-danger removeProduct">X</button>
            </div>
        `;

                productsContainer.appendChild(row);
                ["quantity", "unitCost", "rate"].forEach(cls => {
                    row.querySelector(`.${cls}`).addEventListener("input", () => updateRowTotal(row));
                });
                row.querySelector(".removeProduct").onclick = function() {
                    row.remove();
                    calculateTotalAmount();
                };
            };

            // Attach event listeners to existing rows
            document.querySelectorAll(".product-row").forEach(row => {
                ["quantity", "unitCost", "rate"].forEach(cls => {
                    row.querySelector(`.${cls}`).addEventListener("input", () => updateRowTotal(row));
                });
                row.querySelector(".removeProduct").onclick = function() {
                    row.remove();
                    calculateTotalAmount();
                };
            });

            function updateRowTotal(row) {
                const qty = parseFloat(row.querySelector(".quantity").value) || 0;
                const unit = parseFloat(row.querySelector(".unitCost").value) || 0;
                const rate = parseFloat(row.querySelector(".rate").value) || 1;
                const total = qty * unit * rate;
                row.querySelector(".totalCost").value = total.toFixed(2);
                calculateTotalAmount();
            }

            function calculateTotalAmount() {
                let totalAmount = 0;
                let totalQuantity = 0;

                document.querySelectorAll(".product-row").forEach(row => {
                    const qty = parseFloat(row.querySelector(".quantity")?.value) || 0;
                    const total = parseFloat(row.querySelector(".totalCost")?.value) || 0;
                    totalQuantity += qty;
                    totalAmount += total;
                });

                const transportCostInput = document.getElementById("agent_transport_cost");
                const transportCost = transportCostInput ? parseFloat(transportCostInput.value) || 0 : 0;
                if (transportCost > 0) {
                    totalAmount += transportCost;
                }

                const totalAmountInput = document.getElementById("totalAmount");
                if (totalAmountInput) totalAmountInput.value = totalAmount.toFixed(2);

                const totalProductsInput = document.getElementById("totalProducts");
                if (totalProductsInput) totalProductsInput.value = totalQuantity;
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
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>
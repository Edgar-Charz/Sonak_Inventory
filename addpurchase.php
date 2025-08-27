<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id form session 
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if the addPurchaseBTN button is clicked
if (isset($_POST['addPurchaseBTN'])) {
    $purchaseNumber = $_POST['purchase_number'];
    $supplierId     = $_POST['supplier_name'];
    $purchaseDate   = DateTime::createFromFormat('d-m-Y', $_POST['purchase_date'])->format('Y-m-d');
    $totalProducts = $_POST['total_products'];
    $totalAmount    = $_POST['total_amount'];
    $purchaseStatus = $_POST['purchase_status'];

    $agentId        = $_POST['agent_name'] ?? null;
    $trackingNo     = $_POST['tracking_number'] ?? null;
    $transportCost  = $_POST['agent_transport_cost'] ?? null;

    $dateToAgent    = !empty($_POST['agent_abroad_date']) ? DateTime::createFromFormat('d-m-Y', $_POST['agent_abroad_date'])->format('Y-m-d') : null;
    $dateReceivedTZ = !empty($_POST['agent_tanzania_date']) ? DateTime::createFromFormat('d-m-Y', $_POST['agent_tanzania_date'])->format('Y-m-d') : null;
    $dateAtSonak    = !empty($_POST['at_sonak_date']) ? DateTime::createFromFormat('d-m-Y', $_POST['at_sonak_date'])->format('Y-m-d') : null;

    $createdBy = $user_id;
    $updatedBy = $user_id;

    // Check if purchaseNumber exists
    $check_purchase_stmt = $conn->prepare("SELECT * FROM purchases WHERE purchaseNumber = ?");
    $check_purchase_stmt->bind_param("s", $purchaseNumber);
    $check_purchase_stmt->execute();
    $check_result = $check_purchase_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Purchase number exists, update purchase
        $row = $check_result->fetch_assoc();
        $newTotal = $row["totalAmount"] + $totalAmount;
        $newTotalProducts = $row["totalProducts"] + $totalProducts;

        $update_purchase_stmt = $conn->prepare("UPDATE purchases
                                                SET supplierId = ?, purchaseDate = ?, totalProducts = ?, totalAmount = ?, purchaseStatus = ?, updatedBy = ?, updated_at = ?
                                                WHERE purchaseNumber = ?");
        $update_purchase_stmt->bind_param("isidsiss", $supplierId, $purchaseDate, $newTotalProducts, $newTotal, $purchaseStatus, $updatedBy, $current_time, $purchaseNumber);

        $update_purchase_stmt->execute();
        $update_purchase_stmt->close();
    } else {
        // Insert into purchases
        $insert_purchase_stmt = $conn->prepare(
            "INSERT INTO purchases(purchaseNumber, supplierId, createdBy, updatedBy, purchaseDate, totalProducts, totalAmount, purchaseStatus, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert_purchase_stmt->bind_param("ssssssssss", $purchaseNumber, $supplierId, $createdBy, $updatedBy, $purchaseDate, $totalProducts, $totalAmount, $purchaseStatus, $current_time, $current_time);

        if ($insert_purchase_stmt->execute()) {

            // Loop over products submitted from step 2
            foreach ($_POST['products'] as $product) {
                $productId   = $product['product_name'];
                $quantity    = $product['quantity'];
                $unitCost    = $product['unit_cost'];
                $rate        = $product['rate'];
                $totalCost   = $product['total_cost'];

                // First check if this product already exists for the same purchase
                $check_stmt = $conn->prepare("SELECT purchaseDetailsId, quantity, totalCost 
                                                        FROM purchase_details 
                                                        WHERE purchaseNumber = ? AND productId = ?");
                $check_stmt->bind_param("si", $purchaseNumber, $productId);
                $check_stmt->execute();
                $result = $check_stmt->get_result();

                if ($result->num_rows > 0) {
                    // Already exists → Update instead of insert
                    $row = $result->fetch_assoc();
                    $newQty   = $row['quantity'] + $quantity;
                    $newTotal = $row['totalCost'] + $totalCost;

                    $update_stmt = $conn->prepare("UPDATE purchase_details 
                                                            SET quantity = ?, totalCost = ?, updated_at = ? 
                                                            WHERE purchaseDetailsId = ?");
                    $update_stmt->bind_param("idss", $newQty, $newTotal, $current_time, $row['purchaseDetailsId']);
                    $update_stmt->execute();
                } else {
                    // Insert new purchase detail
                    $insert_details_stmt = $conn->prepare("INSERT INTO purchase_details(
                                                                    purchaseNumber, productId, agentId, trackingNumber, productSize,
                                                                    quantity, unitCost, rate, totalCost,
                                                                    agentTransportationCost, dateToAgentAbroadWarehouse,
                                                                    dateReceivedByAgentInCountryWarehouse, dateReceivedByCompany,
                                                                    created_at, updated_at) 
                                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    $insert_details_stmt->bind_param(
                        "siissidddssssss",
                        $purchaseNumber,
                        $productId,
                        $agentId,
                        $trackingNo,
                        $productSize,
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
                }
            }

            echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Success!',
                text: 'Purchase added successfully!',                   
                timer: 5000,
                timerProgressBar: true
            }).then(function(){
                window.location.href = 'addpurchase.php';
            });
        });
    </script>";
        } else {
            echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Error!',
                text: 'Error adding purchase.',
                timer: 5000,
                timerProgressBar: true
            }).then(function(){
                window.location.href = 'addpurchase.php';
            });
        });
    </script>";
        }
    }
}
function generatePurchaseNumber($conn)
{
    $query = "SELECT purchaseNumber 
                FROM purchases 
                WHERE purchaseNumber 
                LIKE 'SNK-P%' 
                ORDER BY purchaseNumber 
                DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = intval(substr($row['purchaseNumber'], 6)); // Extract numeric part after 'SNK-P'
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1; // Start from 1 if no previous entry
    }

    // Pad with leading zeros to ensure 3 digits
    $formatted = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    return 'SNK-P' . $formatted;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Add Purchase</title>

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
                                <li><a href="addpurchase.php" class="active">Add Purchase</a></li>
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
                        <h4>Purchase Add</h4>
                        <h6>Add/Update Purchase</h6>
                    </div>
                    <div class="page-btn">
                        <a href="purchaselist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Purchases List</a>
                    </div>
                </div>
                <!-- Add Purchase -->
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

                        <!-- Add Purchase Form -->
                        <form action="" method="POST" enctype="multipart/form-data" id="purchaseForm">

                            <!-- STEP 1: Purchase Details -->
                            <div id="step1">
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Purchase No.</label>
                                            <input type="text" name="purchase_number" class="form-control" value="<?= generatePurchaseNumber($conn); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Supplier Name</label>
                                            <select class="form-control" name="supplier_name" required>
                                                <option value="" disabled selected>Select Supplier</option>
                                                <?php
                                                $suppliers_query = $conn->query("SELECT * FROM suppliers");
                                                while ($s = $suppliers_query->fetch_assoc()) {
                                                    echo '<option value="' . $s['supplierId'] . '">' . $s['supplierName'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Purchase Date</label>
                                            <input type="text" name="purchase_date" class="form-control datetimepicker" placeholder="DD-MM-YYYY" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Agent</label>
                                            <select class="form-control" name="agent_name">
                                                <option value="" disabled selected>Select Agent</option>
                                                <?php
                                                $agents_query = $conn->query("SELECT * FROM agents");
                                                while ($a = $agents_query->fetch_assoc()) {
                                                    echo '<option value="' . $a['agentId'] . '">' . $a['agentName'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Tracking No.</label>
                                            <input type="text" name="tracking_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Agent Transport Cost</label>
                                            <input type="number" name="agent_transport_cost" id="agent_transport_cost" class="form-control" value="0">
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
                                    <!-- Products rows will appear here -->
                                </div>
                                <button type="button" class="btn btn-success" id="addProductRow">
                                    + Add Product
                                </button>
                                <br><br>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep1">Back</button>
                                    <button type="button" class="btn btn-primary" id="goStep3">Next</button>
                                </div>
                            </div>

                            <!-- STEP 3:Purchase Summary & Totals -->
                            <div id="step3" style="display:none;">

                                <!-- Summary Table -->
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
                                            <input type="text" name="total_products" id="totalProducts" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total Amount</label>
                                            <input type="text" id="totalAmount" name="total_amount" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Purchase Status</label>
                                            <select class="select" name="purchase_status" id="" required>
                                                <option value="" disabled selected>Select Status</option>
                                                <option value="0">Pending</option>
                                                <option value="1">Completed</option>
                                                <option value="2">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep2">Back</button>
                                    <button type="submit" name="addPurchaseBTN" class="btn btn-success">Submit Purchase</button>
                                </div>
                            </div>
                        </form>
                        <!-- /Add Purchase Form -->
                    </div>
                </div>
                <!-- /Add Purchase -->
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Step 1 validation on Next
            document.getElementById('goStep2').addEventListener('click', function(e) {
                let valid = true;
                let errorMsg = "";
                const requiredFields = [
                    'purchase_number',
                    'supplier_name',
                    'purchase_date'
                ];
                requiredFields.forEach(function(name) {
                    const field = document.getElementsByName(name)[0];
                    if (field && (field.value === '' || field.value === null)) {
                        valid = false;
                        errorMsg += `Please fill the ${name.replace('_', ' ')} field.<br>`;
                    }
                });
                if (!valid) {
                    Swal.fire({
                        title: 'ValidationError!',
                        html: errorMsg,
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                showStep(2);
            });

            // Step 2 validation on Next
            document.getElementById('goStep3').addEventListener('click', function(e) {
                let valid = true;
                let errorMsg = "";
                const productRows = document.querySelectorAll('.product-row');
                if (productRows.length === 0) {
                    valid = false;
                    errorMsg += "Please add at least one product.<br>";
                } else {
                    productRows.forEach(function(row, idx) {
                        const productSelect = row.querySelector('.productSelect');
                        const quantity = row.querySelector('.quantity');
                        const unitCost = row.querySelector('.unitCost');
                        const rate = row.querySelector('.rate');
                        if (!productSelect.value) {
                            valid = false;
                            errorMsg += `Product row ${idx+1}: Select a product.<br>`;
                        }
                        if (!quantity.value || quantity.value <= 0) {
                            valid = false;
                            errorMsg += `Product row ${idx+1}: Enter a valid quantity.<br>`;
                        }
                        if (!unitCost.value || unitCost.value < 0) {
                            valid = false;
                            errorMsg += `Product row ${idx+1}: Enter a valid unit cost.<br>`;
                        }
                        if (!rate.value || rate.value < 0) {
                            valid = false;
                            errorMsg += `Product row ${idx+1}: Enter a valid rate.<br>`;
                        }
                    });
                }
                if (!valid) {
                    Swal.fire({
                        title: 'ValidationError!',
                        html: errorMsg,
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                // First, update totals
                calculateTotalAmount();
                // Populate summary table
                const tableBody = document.querySelector("#summaryTable tbody");
                tableBody.innerHTML = ""; // Clear previous rows
                document.querySelectorAll(".product-row").forEach(row => {
                    const productName = row.querySelector(".productSelect").value;
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
            // Step navigation functions
            function showStep(stepNumber) {
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step3').style.display = 'none';

                // Show current step
                document.getElementById('step' + stepNumber).style.display = 'block';

                // Update step indicators
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
                // First, update totals
                calculateTotalAmount();

                // Populate summary table
                const tableBody = document.querySelector("#summaryTable tbody");
                tableBody.innerHTML = ""; // Clear previous rows

                document.querySelectorAll(".product-row").forEach(row => {
                    const productName = row.querySelector(".productSelect").value;
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
                            <div class="col-lg-3 col-sm-6 col-12">
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

                // Attach input events for calculation
                ["quantity", "unitCost", "rate"].forEach(cls => {
                    row.querySelector(`.${cls}`).addEventListener("input", () => updateRowTotal(row));
                });

                // Remove row
                row.querySelector(".removeProduct").onclick = function() {
                    row.remove();
                    calculateTotalAmount();
                };
            };

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
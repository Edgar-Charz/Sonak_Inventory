<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id form session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_POST['addSaleBTN'])) {
    $invoiceNumber  = $_POST['invoice_number'];
    $customerId     = $_POST['customer_name'];
    $orderDate      = DateTime::createFromFormat('d-m-Y', $_POST['order_date'])->format('Y-m-d');
    $paymentType    = $_POST['payment_type'];
    $subTotal       = $_POST['sub_total'];
    $vat            = $_POST['vat'];
    $grandTotal     = $_POST['total'];
    $pay            = $_POST['pay'];
    $due            = $_POST['due'];
    // $paymentStatus  = $_POST['payment_status'];
    $totalProducts  = $_POST['total_products'];
    $products       = $_POST['products'];

    // Check if invoice exists
    $check = $conn->prepare("SELECT total, totalProducts FROM orders WHERE invoiceNumber = ?");
    $check->bind_param("s", $invoiceNumber);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Invoice exists → update order
        $row = $result->fetch_assoc();
        $newTotal = $row['total'] + $grandTotal;
        $newTotalProducts = $row['totalProducts'] + $totalProducts;

        $update = $conn->prepare("UPDATE orders 
                                            SET customerId = ?, orderDate = ?, subTotal = subTotal + ?, vat = ?, total = ?, paid = paid + ?, due = ?, orderStatus = ?, totalProducts = ?, updated_at = ?
                                            WHERE invoiceNumber = ?");
        $update->bind_param(
            "ssdddiisiss",
            $customerId,
            $orderDate,
            $subTotal,
            $vat,
            $newTotal,
            $pay,
            $due,
            $paymentStatus,
            $newTotalProducts,
            $current_time,
            $invoiceNumber
        );

        $update->execute();
        $update->close();
        // If fully paid, set orderStatus=1 in order_details
        if ($newTotal == ($row['paid'] + $pay)) {
            $updateDetails = $conn->prepare("UPDATE order_details SET orderStatus = 1 WHERE invoiceNumber = ?");
            $updateDetails->bind_param("s", $invoiceNumber);
            $updateDetails->execute();
            $updateDetails->close();
        }
    } else {
        // Invoice does not exist → insert new order
        $insertOrder = $conn->prepare("INSERT INTO orders 
            (invoiceNumber, customerId, createdBy, updatedBy, orderDate, subTotal, vat, total, paymentType, paid, due, totalProducts, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertOrder->bind_param(
            "siiisdidsddiss",
            $invoiceNumber,
            $customerId,
            $user_id,
            $user_id,
            $orderDate,
            $subTotal,
            $vat,
            $grandTotal,
            $paymentType,
            $pay,
            $due,
            $totalProducts,
            $current_time,
            $current_time
        );
        $insertOrder->execute();
        $insertOrder->close();

        // If fully paid, set orderStatus=1 in order_details
        if ($grandTotal == $pay) {
            $updateDetails = $conn->prepare("UPDATE order_details SET status = 1 WHERE invoiceNumber = ?");
            $updateDetails->bind_param("s", $invoiceNumber);
            $updateDetails->execute();
            $updateDetails->close();
        }
    }

    $check->close();

    // Insert/Update order details (for each product row)
    foreach ($products as $p) {
        $productId   = $p['product_id'];
        $unitPrice   = $p['unit_cost'];
        $quantity    = $p['quantity'];
        $totalCost   = $p['total_cost'];

        // Check if this product already exists in order_details for this invoice
        $checkDetail = $conn->prepare("SELECT orderDetailsId, quantity, totalCost 
                                   FROM order_details 
                                   WHERE invoiceNumber = ? AND productId = ?");
        $checkDetail->bind_param("ss", $invoiceNumber, $productId);
        $checkDetail->execute();
        $result = $checkDetail->get_result();

        if ($result->num_rows > 0) {
            // Already exists → update quantity and totalCost
            $row = $result->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;
            $newTotal    = $row['totalCost'] + $totalCost;

            $updateDetail = $conn->prepare("UPDATE order_details 
                                        SET quantity = ?, totalCost = ?, updated_at = ? 
                                        WHERE orderDetailsId = ?");
            $updateDetail->bind_param("idsi", $newQuantity, $newTotal, $current_time, $row['orderDetailsId']);
            $updateDetail->execute();
            $updateDetail->close();
        } else {
            // Doesn’t exist → insert new row
            $insertDetail = $conn->prepare("INSERT INTO order_details 
            (invoiceNumber, productId, unitCost, quantity, totalCost, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertDetail->bind_param("ssdidss", $invoiceNumber, $productId, $unitPrice, $quantity, $totalCost, $current_time, $current_time);

            $insertDetail->execute();
            $insertDetail->close();
        }

        $checkDetail->close();

        // Update remaining quantity in products table
        $updateProduct = $conn->prepare("UPDATE products 
                                     SET quantity = quantity - ?, updated_at = ?
                                     WHERE productId = ?");
        $updateProduct->bind_param("iss", $quantity, $current_time, $productId);
        $updateProduct->execute();
        $updateProduct->close();
    }

    echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                title: 'Success',
                text: 'Order saved successfully',
                timer: 5000
            }).then(function() {
                window.location.href = 'add-sales.php';
            });
          });
        </script>";
}

function generateInvoiceNumber($conn)
{ 
    $query = "SELECT invoiceNumber 
                FROM orders 
                WHERE invoiceNumber 
                LIKE 'SNK-S%' 
                ORDER BY invoiceNumber 
                DESC LIMIT 1";
    $result = $conn->query($query); 

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = intval(substr($row['invoiceNumber'], 6)); // Extract numeric part after 'SNK-P'
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1; // Start from 1 if no previous entry
    }

    // Pad with leading zeros to ensure 3 digits
    $formatted = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    return 'SNK-S' . $formatted;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Add Sales</title>

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
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/sales1.svg" alt="img"><span> Sales</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="saleslist.php">Sales List</a></li>
                                <li><a href="add-sales.php" class="active">Add Sales</a></li>
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
                        <h4>Add Sale</h4>
                        <h6>Add your new sale</h6>
                    </div>
                    <div class="page-btn">
                        <a href="saleslist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Orders List</a>
                    </div>
                </div>
                <!-- Add Sale -->
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
                                                <div class="step-label mt-1 small">Order Details</div>
                                            </div>
                                            <div class="progress-line flex-grow-1 mx-3" style="height: 2px; background-color: #e9ecef;"></div>
                                            <div class="step-item text-center" id="stepIndicator2">
                                                <div class="step-circle bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</div>
                                                <div class="step-label mt-1 small">Add Products</div>
                                            </div>
                                            <div class="progress-line flex-grow-1 mx-3" style="height: 2px; background-color: #e9ecef;"></div>
                                            <div class="step-item text-center" id="stepIndicator3">
                                                <div class="step-circle bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">3</div>
                                                <div class="step-label mt-1 small">Order Summary</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data" id="orderForm">

                            <!-- STEP 1: Order Details -->
                            <div id="step1">
                                <div class="row">
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Customer</label>
                                            <select class="select" name="customer_name" required>
                                                <option value="" disabled selected>Customer</option>
                                                <?php
                                                $customers_query = $conn->query("SELECT * FROM customers");
                                                while ($customers = $customers_query->fetch_assoc()) {
                                                    echo '<option value="' . $customers['customerId'] . '">' . $customers['customerName'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Invoice Number</label>
                                            <input type="text" name="invoice_number" class="form-control" value="<?= generateInvoiceNumber($conn); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Order Date</label>
                                            <input type="text" name="order_date" class="form-control datetimepicker" placeholder="DD-MM-YYYY" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select name="payment_type" class="select">
                                                <option selected disabled>Payment Type</option>
                                                <option>Cash</option>
                                                <option>Credit Card</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="goStep2">Next</button>
                                </div>
                            </div>

                            <!-- STEP 2: Add Products -->
                            <div id="step2" style="display:none;">
                                <div id="productsContainer"></div>
                                <button type="button" class="btn btn-success" id="addProductRow">+ Add Product</button>
                                <br><br>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep1">Back</button>
                                    <button type="button" class="btn btn-primary" id="goStep3">Next</button>
                                </div>
                            </div>

                            <!-- STEP 3: Summary & Totals -->
                            <div id="step3" style="display:none;">
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total Products</label>
                                            <input type="text" name="total_products" id="totalProducts" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Subtotal</label>
                                            <input type="text" name="sub_total" id="subTotal" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT (%)</label>
                                            <input type="number" name="vat" id="vat" class="form-control" value="0">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT Amount</label>
                                            <input type="text" name="vat_amount" id="vatAmount" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total</label>
                                            <input type="text" name="total" id="grandTotal" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Pay</label>
                                            <input type="number" name="pay" id="pay" class="form-control" value="0">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Due</label>
                                            <input type="text" name="due" id="due" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Payment Status</label>
                                            <select name="payment_status" class="select" required>
                                                <option value="" disabled selected>Payment Status</option>
                                                <option value="1">Paid</option>
                                                <option value="0">Unpaid</option>
                                            </select>
                                        </div>
                                    </div> -->
                                </div>
                                <!-- Summary Table -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered" id="orderSummaryTable">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Unit Cost</th>
                                                <th>Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Rows will be dynamically inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /Summary Table -->
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep2">Back</button>
                                    <button type="submit" name="addSaleBTN" class="btn btn-success">Submit Order</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /Add Sale -->
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
                    'customer_name',
                    'invoice_number',
                    'order_date'
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
                        title: 'Validation Error!',
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
                    });
                }
                if (!valid) {
                    Swal.fire({
                        title: 'Validation Error!',
                        html: errorMsg,
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                // First, update Totals
                calculateSummary();
                // Populate summary table
                const summaryBody = document.querySelector("#orderSummaryTable tbody");
                summaryBody.innerHTML = ""; // Clear previous rows
                document.querySelectorAll(".product-row").forEach(row => {
                    let productSelect = row.querySelector(".productSelect");
                    let productName = productSelect.options[productSelect.selectedIndex]?.text || "N/A";
                    let qty = parseFloat(row.querySelector(".quantity").value) || 0;
                    let unitCost = parseFloat(row.querySelector(".unitCost").value) || 0;
                    let total = parseFloat(row.querySelector(".totalCost").value) || 0;
                    let tr = document.createElement("tr");
                    tr.innerHTML = `
                                <td>${productName}</td>
                                <td>${qty}</td>
                                <td>${unitCost.toFixed(2)}</td>
                                <td>${total.toFixed(2)}</td>
                            `;
                    summaryBody.appendChild(tr);
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
                // First, update Totals
                calculateSummary();

                // Populate summary table
                const summaryBody = document.querySelector("#orderSummaryTable tbody");
                summaryBody.innerHTML = ""; // Clear previous rows

                document.querySelectorAll(".product-row").forEach(row => {
                    let productSelect = row.querySelector(".productSelect");
                    let productName = productSelect.options[productSelect.selectedIndex]?.text || "N/A";
                    let qty = parseFloat(row.querySelector(".quantity").value) || 0;
                    let unitCost = parseFloat(row.querySelector(".unitCost").value) || 0;
                    let total = parseFloat(row.querySelector(".totalCost").value) || 0;

                    // Append row to summary table
                    let tr = document.createElement("tr");
                    tr.innerHTML = `
                                <td>${productName}</td>
                                <td>${qty}</td>
                                <td>${unitCost.toFixed(2)}</td>
                                <td>${total.toFixed(2)}</td>
                            `;
                    summaryBody.appendChild(tr);
                });
                showStep(3);
            });
            document.getElementById('backStep2').addEventListener('click', function() {
                showStep(2);
            });

            // Products row container
            const productsContainer = document.getElementById("productsContainer");

            // Add product row
            document.getElementById("addProductRow").onclick = function() {
                let index = document.querySelectorAll(".product-row").length;
                let row = document.createElement("div");
                row.classList.add("row", "product-row", "align-items-end", "gy-2", "mb-3");

                row.innerHTML = `
                    <div class="col-lg-3">
                        <label class="form-label">Product</label>
                        <select name="products[${index}][product_id]" class="form-control productSelect" required>
                            <option value="" disabled selected>Select Product</option>
                            <?php
                            $products_query = $conn->query("SELECT * FROM products");
                            while ($p = $products_query->fetch_assoc()) {
                                echo '<option value="' . $p['productId'] . '" 
                                    data-price="' . $p['sellingPrice'] . '"
                                    data-quantity="' . $p['quantity'] . '">'
                                    . $p['productName'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Unit Cost</label>
                        <input type="text" name="products[${index}][unit_cost]" class="form-control unitCost" readonly>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Available</label>
                        <input type="text" name="products[${index}][available_quantity]" class="form-control availableQuantity" readonly>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="products[${index}][quantity]" class="form-control quantity" value="0" min="1">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Total Cost</label>
                        <input type="text" name="products[${index}][total_cost]" class="form-control totalCost" readonly>
                    </div>
                    <div class="col-lg-1 text-end">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="button" class="btn btn-danger removeProduct">X</button>
                    </div>
                `;
                productsContainer.appendChild(row);

                // Attach events
                row.querySelector(".productSelect").addEventListener("change", function() {
                    let price = this.options[this.selectedIndex].getAttribute("data-price");
                    let available = this.options[this.selectedIndex].getAttribute("data-quantity");

                    row.querySelector(".unitCost").value = price;
                    row.querySelector(".availableQuantity").value = available;

                    updateRowTotal(row);
                });

                row.querySelector(".quantity").addEventListener("input", function() {

                    // Update available quantity in real time
                    let enteredQty = parseFloat(this.value) || 0;
                    let originalAvailable = parseFloat(row.querySelector(".productSelect")
                        .options[row.querySelector(".productSelect").selectedIndex]
                        .getAttribute("data-quantity")) || 0;

                    // Block user from entering more than available
                    if (enteredQty > originalAvailable) {
                        this.value = originalAvailable; // reset to max available
                        enteredQty = originalAvailable;
                        Swal.fire({
                            title: 'Warning',
                            text: 'Quantity cannot exceed available stock!',
                            timer: 3000
                        });
                    }

                    // Update available quantity
                    let remaining = originalAvailable - enteredQty;
                    row.querySelector(".availableQuantity").value = remaining;

                    updateRowTotal(row);
                }); 

                row.querySelector(".removeProduct").onclick = function() {
                    row.remove();
                    calculateSummary();
                };
            };

            function updateRowTotal(row) {
                let qty = parseFloat(row.querySelector(".quantity").value) || 0;
                let price = parseFloat(row.querySelector(".unitCost").value) || 0;
                let total = qty * price;
                row.querySelector(".totalCost").value = total.toFixed(2);
                calculateSummary();
            }

            function calculateSummary() {
                let subTotal = 0;
                let totalProducts = 0;

                document.querySelectorAll(".product-row").forEach(row => {
                    let qty = parseFloat(row.querySelector(".quantity").value) || 0;
                    let cost = parseFloat(row.querySelector(".totalCost").value) || 0;
                    subTotal += cost;
                    totalProducts += qty;
                });

                // Subtotal and Total Products
                document.getElementById("subTotal").value = subTotal.toFixed(2);
                document.getElementById("totalProducts").value = totalProducts;

                // VAT and VAT Amount
                let vatPercent = parseFloat(document.getElementById("vat").value) || 0;
                let vatAmount = subTotal * vatPercent / 100;
                document.getElementById("vatAmount").value = vatAmount.toFixed(2);

                // Grand Total
                let grandTotal = subTotal + (subTotal * vatPercent / 100);
                document.getElementById("grandTotal").value = grandTotal.toFixed(2);

                // Pay Amount
                let pay = parseFloat(document.getElementById("pay").value) || 0;
                document.getElementById("due").value = (grandTotal - pay).toFixed(2);
            }

            // Event listeners
            document.getElementById("vat").addEventListener("input", calculateSummary);
            document.getElementById("pay").addEventListener("input", calculateSummary);

            // Restrict pay input to not exceed grand total
            document.getElementById("pay").addEventListener("input", function() {
                let grandTotal = parseFloat(document.getElementById("grandTotal").value) || 0;
                let payInput = this;
                let enteredPay = parseFloat(payInput.value) || 0;
                if (enteredPay > grandTotal) {
                    payInput.value = grandTotal;
                    Swal.fire({
                        title: 'Warning',
                        text: 'Pay amount cannot exceed the grand total!',
                        timer: 3000
                    });
                } else if (enteredPay < 0) {
                    payInput.value = 0;
                    enteredPay = 0;
                    Swal.fire({
                        title: 'Warning',
                        text: 'Pay amount cannot be negative!',
                        timer: 3000
                    });
                }
                calculateSummary();
            });
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
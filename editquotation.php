<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['referenceNumber'])) {
    $referenceNumber = $_GET['referenceNumber'];

    // Fetch existing quotation data
    $quotation_query = $conn->prepare("SELECT * FROM quotations WHERE referenceNumber = ? LIMIT 1");
    $quotation_query->bind_param("s", $referenceNumber);
    $quotation_query->execute();
    $quotation_result = $quotation_query->get_result();
    $quotation = $quotation_result->fetch_assoc();

    // Fetch quotation details
    $details_query = $conn->prepare("SELECT qd.*, p.productName 
                                    FROM quotation_details qd 
                                    JOIN products p ON qd.productId = p.productId 
                                    WHERE qd.referenceNumber = ?");
    $details_query->bind_param("s", $referenceNumber);
    $details_query->execute();
    $details_result = $details_query->get_result();
}

if (isset($_POST['updateQuotationBTN'])) {
    $referenceNumber  = $_POST['reference_number'];
    $customerId     = $_POST['customer_name'];
    $quotationDate  = DateTime::createFromFormat('d-m-Y', $_POST['quotation_date'])->format('Y-m-d');
    $subTotal       = $_POST['sub_total'];
    $vat            = $_POST['vat'];
    $vatAmount      = $_POST['vat_amount'];
    $discount       = $_POST['discount'];
    $discountAmount = $_POST['discount_amount'];
    $shippingAmount = $_POST['shipping_amount'];
    $grandTotal     = $_POST['total_amount'];
    $note           = $_POST['note'];
    $quotationStatus  = $_POST['quotation_status'];
    $totalProducts  = $_POST['total_products'];
    $products       = $_POST['products'];

    // Update quotation
    $update = $conn->prepare("UPDATE quotations 
                             SET customerId = ?, updatedBy = ?, quotationDate = ?, totalProducts = ?, subTotal = ?, taxPercentage = ?, taxAmount = ?, discountPercentage = ?, discountAmount = ?, shippingAmount = ?, totalAmount = ?, note = ?, quotationStatus = ?, updated_at = ? 
                             WHERE referenceNumber = ?");
    $update->bind_param(
        "iisidididddsiss",
        $customerId,
        $user_id,
        $quotationDate,
        $totalProducts,
        $subTotal,
        $vat,
        $vatAmount,
        $discount,
        $discountAmount,
        $shippingAmount,
        $grandTotal,
        $note,
        $quotationStatus,
        $current_time,
        $referenceNumber
    );
    $update->execute();
    $update->close();

    // Delete existing quotation details to replace with new ones
    $delete_details = $conn->prepare("DELETE FROM quotation_details WHERE referenceNumber = ?");
    $delete_details->bind_param("s", $referenceNumber);
    $delete_details->execute();
    $delete_details->close();

    // Insert updated quotation details
    foreach ($products as $p) {
        $productId   = $p['product_id'];
        $unitPrice   = $p['unit_cost'];
        $quantity    = $p['quantity'];
        $totalCost   = $p['total_cost'];

        $insertDetail = $conn->prepare("INSERT INTO quotation_details 
                                       (referenceNumber, productId, quantity, unitPrice, subTotal, created_at, updated_at) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertDetail->bind_param("siiddss", $referenceNumber, $productId, $quantity, $unitPrice, $totalCost, $current_time, $current_time);
        $insertDetail->execute();
        $insertDetail->close();
    }

    echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Success',
                    text: 'Quotation updated successfully',
                    timer: 5000
                }).then(function() {
                    window.location.href = 'quotationlist.php';
                });
            });
          </script>";
}

function generateReferenceNumber($conn)
{
    $query = "SELECT referenceNumber 
              FROM quotations 
              WHERE referenceNumber LIKE 'SNK-RF%' 
              ORDER BY referenceNumber DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = intval(substr($row['referenceNumber'], 6));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    return 'SNK-RF' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Edit Quotation</title>

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
                            </a></div>
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
                                <li><a href="addpurchase.php">Add Purchase</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="assets/img/icons/quotation1.svg" alt="img"><span> Quotation</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="quotationList.php" class="active">Quotation List</a></li>
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
                        <h4>Edit Quotation</h4>
                        <h6>Edit your existing quotation</h6>
                    </div>
                    <div class="page-btn">
                        <a href="quotationlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Quotations List</a>
                    </div>
                </div>
                <!-- Edit Quotation -->
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
                                                <div class="step-label mt-1 small">Quotation Details</div>
                                            </div>
                                            <div class="progress-line flex-grow-1 mx-3" style="height: 2px; background-color: #e9ecef;"></div>
                                            <div class="step-item text-center" id="stepIndicator2">
                                                <div class="step-circle bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</div>
                                                <div class="step-label mt-1 small">Edit Products</div>
                                            </div>
                                            <div class="progress-line flex-grow-1 mx-3" style="height: 2px; background-color: #e9ecef;"></div>
                                            <div class="step-item text-center" id="stepIndicator3">
                                                <div class="step-circle bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">3</div>
                                                <div class="step-label mt-1 small">Quotation Summary</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data" id="orderForm">
                            <input type="hidden" name="reference_number" value="<?= htmlspecialchars($referenceNumber ?? '') ?>">

                            <!-- STEP 1: Order Details -->
                            <div id="step1">
                                <div class="row">
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Customer</label>
                                            <select class="select" name="customer_name" required>
                                                <option value="" disabled>Select Customer</option>
                                                <?php
                                                $customers_query = $conn->query("SELECT * FROM customers");
                                                while ($customers = $customers_query->fetch_assoc()) {
                                                    $selected = ($quotation['customerId'] == $customers['customerId']) ? 'selected' : '';
                                                    echo '<option value="' . $customers['customerId'] . '" ' . $selected . '>' . $customers['customerName'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Reference Number</label>
                                            <input type="text" name="reference_number" class="form-control" value="<?= htmlspecialchars($referenceNumber ?? '') ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Quotation Date</label>
                                            <input type="text" name="quotation_date" class="form-control datetimepicker" value="<?= date('d-m-Y', strtotime($quotation['quotationDate'] ?? '')) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="goStep2">Next</button>
                                </div>
                            </div>

                            <!-- STEP 2: Edit Products -->
                            <div id="step2" style="display:none;">
                                <div id="productsContainer">
                                    <?php while ($detail = $details_result->fetch_assoc()) { ?>
                                        <div class="row product-row align-items-end gy-2 mb-3">
                                            <div class="col-lg-3">
                                                <label class="form-label">Product</label>
                                                <select name="products[0][product_id]" class="form-control productSelect" required>
                                                    <option value="" disabled>Select Product</option>
                                                    <?php
                                                    $products_query = $conn->query("SELECT * FROM products");
                                                    while ($p = $products_query->fetch_assoc()) {
                                                        $selected = ($detail['productId'] == $p['productId']) ? 'selected' : '';
                                                        echo '<option value="' . $p['productId'] . '" data-price="' . $p['sellingPrice'] . '" data-quantity="' . $p['quantity'] . '" ' . $selected . '>' . $p['productName'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Unit Cost</label>
                                                <input type="text" name="products[0][unit_cost]" class="form-control unitCost" value="<?= $detail['unitPrice'] ?>" readonly>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Available</label>
                                                <input type="text" name="products[0][available_quantity]" class="form-control availableQuantity" value="<?= $detail['quantity'] ?>" readonly>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="products[0][quantity]" class="form-control quantity" value="<?= $detail['quantity'] ?>" min="1">
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Total Cost</label>
                                                <input type="text" name="products[0][total_cost]" class="form-control totalCost" value="<?= $detail['subTotal'] ?>" readonly>
                                            </div>
                                            <div class="col-lg-1 text-end">
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

                            <!-- STEP 3: Summary & Totals -->
                            <div id="step3" style="display:none;">
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total Products</label>
                                            <input type="text" name="total_products" id="totalProducts" class="form-control" value="<?= $quotation['totalProducts'] ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Subtotal</label>
                                            <input type="text" name="sub_total" id="subTotal" class="form-control" value="<?= $quotation['subTotal'] ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT (%)</label>
                                            <input type="number" name="vat" id="vat" class="form-control" value="<?= $quotation['taxPercentage'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT Amount</label>
                                            <input type="text" name="vat_amount" id="vatAmount" class="form-control" value="<?= $quotation['taxAmount'] ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Discount(%)</label>
                                            <input type="number" name="discount" id="discount" class="form-control" value="<?= $quotation['discountPercentage'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Discount Amount</label>
                                            <input type="text" name="discount_amount" id="discountAmount" class="form-control" value="<?= $quotation['discountAmount'] ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Shipping Amount</label>
                                            <input type="number" name="shipping_amount" id="shippingAmount" class="form-control" value="<?= $quotation['shippingAmount'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total</label>
                                            <input type="text" name="total_amount" id="grandTotal" class="form-control" value="<?= $quotation['totalAmount'] ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Quotation Status</label>
                                            <select name="quotation_status" class="select" required>
                                                <option value="" disabled>Select Quotation Status</option>
                                                <option value="1" <?= $quotation['quotationStatus'] == 1 ? 'selected' : '' ?>>Paid</option>
                                                <option value="0" <?= $quotation['quotationStatus'] == 0 ? 'selected' : '' ?>>Unpaid</option>
                                            </select>
                                        </div>
                                    </div>
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
                                <br>
                                <!-- /Summary Table -->
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="note"><?= htmlspecialchars($quotation['note'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep2">Back</button>
                                    <button type="submit" name="updateQuotationBTN" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /Edit Quotation -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                calculateSummary();
                const summaryBody = document.querySelector("#orderSummaryTable tbody");
                summaryBody.innerHTML = "";

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
                            <option value="" disabled>Select Product</option>
                            <?php
                            $products_query = $conn->query("SELECT * FROM products");
                            while ($p = $products_query->fetch_assoc()) {
                                echo '<option value="' . $p['productId'] . '" data-price="' . $p['sellingPrice'] . '" data-quantity="' . $p['quantity'] . '">' . $p['productName'] . '</option>';
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

                row.querySelector(".productSelect").addEventListener("change", function() {
                    let price = this.options[this.selectedIndex].getAttribute("data-price");
                    let available = this.options[this.selectedIndex].getAttribute("data-quantity");
                    row.querySelector(".unitCost").value = price;
                    row.querySelector(".availableQuantity").value = available;
                    updateRowTotal(row);
                });

                row.querySelector(".quantity").addEventListener("input", function() {
                    let enteredQty = parseFloat(this.value) || 0;
                    let originalAvailable = parseFloat(row.querySelector(".productSelect")
                        .options[row.querySelector(".productSelect").selectedIndex]
                        .getAttribute("data-quantity")) || 0;

                    if (enteredQty > originalAvailable) {
                        this.value = originalAvailable;
                        enteredQty = originalAvailable;
                        Swal.fire({
                            title: 'Warning',
                            text: 'Quantity cannot exceed available stock!',
                            timer: 3000
                        });
                    }

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

                document.getElementById("subTotal").value = subTotal.toFixed(2);
                document.getElementById("totalProducts").value = totalProducts;

                let vatPercent = parseFloat(document.getElementById("vat").value) || 0;
                let vatAmount = subTotal * vatPercent / 100;
                document.getElementById("vatAmount").value = vatAmount.toFixed(2);

                let discountPercent = parseFloat(document.getElementById("discount").value) || 0;
                let discountAmount = subTotal * discountPercent / 100;
                document.getElementById("discountAmount").value = discountAmount.toFixed(2);

                let shippingAmount = parseFloat(document.getElementById("shippingAmount").value) || 0;
                let grandTotal = subTotal - discountAmount + vatAmount + shippingAmount;
                document.getElementById("grandTotal").value = grandTotal.toFixed(2);
            }

            document.getElementById("vat").addEventListener("input", calculateSummary);
            document.getElementById("discount").addEventListener("input", calculateSummary);
            document.getElementById("shippingAmount").addEventListener("input", calculateSummary);

            // Initialize with step 1
            showStep(1);
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
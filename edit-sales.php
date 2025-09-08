<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Get invoiceNumber
$invoiceNumber = isset($_GET['invoiceNumber']) ? $_GET['invoiceNumber'] : '';

if (empty($invoiceNumber)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Error!',
                text: 'Invalid invoice number!',
                timer: 5000,
                timerProgressBar: true
            }).then(function(){
                window.location.href = 'saleslist.php';
            });
        });
    </script>";
    exit;
}

// Fetch order details
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE invoiceNumber = ?");
$order_stmt->bind_param("s", $invoiceNumber);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Error!',
                text: 'Order not found!',
                timer: 5000,
                timerProgressBar: true
            }).then(function(){
                window.location.href = 'saleslist.php';
            });
        });
    </script>";
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order details (products)
$details_stmt = $conn->prepare("SELECT od.*, p.quantity AS current_stock, p.sellingPrice, p.productName FROM order_details od JOIN products p ON od.productId = p.productId WHERE od.invoiceNumber = ?");
$details_stmt->bind_param("s", $invoiceNumber);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
$order_products = [];
while ($row = $details_result->fetch_assoc()) {
    $order_products[] = $row;
}

// Check if updateSaleBTN was clicked
if (isset($_POST['updateSaleBTN'])) {

    // Start transaction
    $conn->begin_transaction();

    try {
        $customerId     = $_POST['customer_name'];
        $orderDate      = !empty($_POST['order_date']) ? date("Y-m-d", strtotime($_POST['order_date'])) : null;
        $subTotal       = $_POST['sub_total'];
        $vat            = $_POST['vat'];
        $vatAmount      = $_POST['vat_amount'];
        $discount       = $_POST['discount'];
        $discountAmount = $_POST['discount_amount'];
        $shippingAmount = $_POST['shipping_amount'];
        $grandTotal     = $_POST['total'];
        $paymentType    = $_POST['payment_type'];
        $pay            = $_POST['pay'];
        $due            = $_POST['due'];
        $totalProducts  = $_POST['total_products'];
        $products       = $_POST['products'];
        $invoiceNumber  = $_POST['invoice_number'];
        $current_time   = date('Y-m-d H:i:s');

        // Restore original quantities to stock
        foreach ($order_products as $old_product) {
            $update_product_stmt = $conn->prepare("UPDATE products SET quantity = quantity + ?, updated_at = ? WHERE productId = ?");
            $update_product_stmt->bind_param("iss", $old_product['quantity'], $current_time, $old_product['productId']);
            $update_product_stmt->execute();
            $update_product_stmt->close();
        }

        // Delete old order details
        $delete_details_stmt = $conn->prepare("DELETE FROM order_details WHERE invoiceNumber = ?");
        $delete_details_stmt->bind_param("s", $invoiceNumber);
        $delete_details_stmt->execute();
        $delete_details_stmt->close();

        // Update order
        $update_order_stmt = $conn->prepare("UPDATE orders 
            SET customerId = ?, orderDate = ?, subTotal = ?, vat = ?, vatAmount = ?, discount = ?, discountAmount = ?, shippingAmount = ?, total = ?, paymentType = ?, paid = ?, due = ?, totalProducts = ?, updated_at = ? 
            WHERE invoiceNumber = ?");
        $update_order_stmt->bind_param("isdididddsddiss", $customerId, $orderDate, $subTotal, $vat, $vatAmount, $discount, $discountAmount, $shippingAmount, $grandTotal, $paymentType, $pay, $due, $totalProducts, $current_time, $invoiceNumber);
        $update_order_stmt->execute();
        $update_order_stmt->close();

        // Insert new order details and subtract new quantities from stock
        foreach ($products as $p) {
            $productId   = $p['product_id'];
            $unitPrice   = $p['unit_cost'];
            $quantity    = $p['quantity'];
            $totalCost   = $p['total_cost'];

            // Validate stock
            $stock_stmt = $conn->prepare("SELECT quantity FROM products WHERE productId = ?");
            $stock_stmt->bind_param("s", $productId);
            $stock_stmt->execute();
            $stock_result = $stock_stmt->get_result();
            $stock = $stock_result->fetch_assoc();
            $stock_stmt->close();

            if ($quantity > $stock['quantity']) {
                throw new Exception("Insufficient stock for product ID: $productId");
            }

            // Insert new detail
            $insert_detail_stmt = $conn->prepare("INSERT INTO order_details 
                (invoiceNumber, productId, unitCost, quantity, totalCost, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_detail_stmt->bind_param("ssdidss", $invoiceNumber, $productId, $unitPrice, $quantity, $totalCost, $current_time, $current_time);
            $insert_detail_stmt->execute();
            $insert_detail_stmt->close();

            // Subtract from stock
            $update_product_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ?, updated_at = ? WHERE productId = ?");
            $update_product_stmt->bind_param("iss", $quantity, $current_time, $productId);
            $update_product_stmt->execute();
            $update_product_stmt->close();
        }

        $conn->commit();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Success',
                    text: 'Order updated successfully',
                    timer: 5000
                }).then(function() {
                    window.location.href = 'saleslist.php';
                });
            });
        </script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: '" . $e->getMessage() . "',
                    icon: 'error',
                    timer: 5000
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
    <title>Sonak Inventory | Edit Sales</title>

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
                                <li><a href="saleslist.php" class="active">Sales List</a></li>
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
                        <h4>Edit Sale</h4>
                        <h6>Update your sale</h6>
                    </div>
                    <div class="page-btn">
                        <a href="saleslist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Orders List</a>
                    </div>
                </div>
                <!-- Edit Sale -->
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
                                                <option value="" disabled>Select Customer</option>
                                                <?php
                                                $customers_query = $conn->query("SELECT * FROM customers");
                                                while ($customers = $customers_query->fetch_assoc()) {
                                                    $selected = ($customers['customerId'] == $order['customerId']) ? 'selected' : '';
                                                    echo '<option value="' . $customers['customerId'] . '" ' . $selected . '>' . $customers['customerName'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Invoice Number</label>
                                            <input type="text" name="invoice_number" class="form-control" value="<?= ($order['invoiceNumber']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Order Date</label>
                                            <input type="date" name="order_date" class="form-control" value="<?= ($order['orderDate']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select name="payment_type" class="select" required>
                                                <option value="" disabled>Payment Type</option>
                                                <option value="Cash" <?= ($order['paymentType'] == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                                                <option value="Credit Card" <?= ($order['paymentType'] == 'Credit Card') ? 'selected' : ''; ?>>Credit Card</option>
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
                                <div id="productsContainer">
                                    <?php foreach ($order_products as $index => $product): ?>
                                        <div class="row product-row align-items-end gy-2 mb-3">
                                            <div class="col-lg-3">
                                                <label class="form-label">Product</label>
                                                <select name="products[<?= $index ?>][product_id]" class="form-control productSelect" required>
                                                    <option value="" disabled>Select Product</option>
                                                    <?php
                                                    $products_query = $conn->query("SELECT * FROM products");
                                                    while ($p = $products_query->fetch_assoc()) {
                                                        $selected = ($p['productId'] == $product['productId']) ? 'selected' : '';
                                                        echo '<option value="' . $p['productId'] . '" 
                                                            data-price="' . $p['sellingPrice'] . '"
                                                            data-quantity="' . ($p['quantity'] + $product['quantity']) . '" ' . $selected . '>'
                                                            . $p['productName'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Unit Cost</label>
                                                <input type="text" name="products[<?= $index ?>][unit_cost]" class="form-control unitCost" value="<?= ($product['unitCost']); ?>" readonly>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Available</label>
                                                <input type="text" name="products[<?= $index ?>][available_quantity]" class="form-control availableQuantity" value="<?= $product['current_stock'] + $product['quantity']; ?>" readonly>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="products[<?= $index ?>][quantity]" class="form-control quantity" value="<?= ($product['quantity']); ?>" min="0">
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Total Cost</label>
                                                <input type="text" name="products[<?= $index ?>][total_cost]" class="form-control totalCost" value="<?= ($product['totalCost']); ?>" readonly>
                                            </div>
                                            <div class="col-lg-1 text-end">
                                                <label class="form-label d-block">&nbsp;</label>
                                                <button type="button" class="btn btn-danger removeProduct">X</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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
                                            <input type="text" name="total_products" id="totalProducts" class="form-control" value="<?= ($order['totalProducts']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Shipping Amount</label>
                                            <input type="number" name="shipping_amount" id="shippingAmount" class="form-control" value="<?= ($order['shippingAmount']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>SubTotal</label>
                                            <input type="text" name="sub_total" id="subTotal" class="form-control" value="<?= ($order['subTotal']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT (%)</label>
                                            <input type="number" name="vat" id="vat" class="form-control" value="<?= ($order['vat']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT Amount</label>
                                            <input type="text" name="vat_amount" id="vatAmount" class="form-control" value="<?= ($order['vatAmount']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Discount(%)</label>
                                            <input type="number" name="discount" id="discount" class="form-control" value="<?= ($order['discount']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Discount Amount</label>
                                            <input type="text" name="discount_amount" id="discountAmount" class="form-control" value="<?= ($order['discountAmount']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total</label>
                                            <input type="text" name="total" id="grandTotal" class="form-control" value="<?= ($order['total']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Paid Amount</label>
                                            <input type="number" name="pay" id="pay" class="form-control" value="<?= ($order['paid']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Due</label>
                                            <input type="text" name="due" id="due" class="form-control" value="<?= ($order['due']); ?>" readonly>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Payment Status</label>
                                            <select name="payment_status" class="select" required>
                                                <option value="" disabled>Select Payment Status</option>
                                                <option value="1" <?= ($order['orderStatus'] == 1) ? 'selected' : ''; ?>>Paid</option>
                                                <option value="0" <?= ($order['orderStatus'] == 0) ? 'selected' : ''; ?>>Unpaid</option>
                                            </select>
                                        </div>
                                    </div> -->
                                </div>
                                <!--Summary Table -->
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
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <!-- /Summary Table -->

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep2">Back</button>
                                    <button type="submit" name="updateSaleBTN" class="btn btn-success">Update Order</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /Edit Sale -->
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Step navigation
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
                        circle.className = 'step-circle text-white rounded-circle d-inline-flex align-items-center justify-content-center';
                        circle.classList.add(i === currentStep ? 'bg-primary' : i < currentStep ? 'bg-success' : 'bg-secondary');
                    }
                }

                // Step navigation event listeners
                document.getElementById('goStep2').addEventListener('click', () => showStep(2));
                document.getElementById('backStep1').addEventListener('click', () => showStep(1));
                document.getElementById('goStep3').addEventListener('click', () => {
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
                document.getElementById('backStep2').addEventListener('click', () => showStep(2));

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
                            <input type="number" name="products[${index}][quantity]" class="form-control quantity" value="0" min="0">
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
                    attachRowEvents(row);
                };

                function attachRowEvents(row) {
                    const productSelect = row.querySelector(".productSelect");
                    const quantityInput = row.querySelector(".quantity");

                    productSelect.addEventListener("change", function() {
                        let price = parseFloat(this.options[this.selectedIndex].getAttribute("data-price")) || 0;
                        let available = parseFloat(this.options[this.selectedIndex].getAttribute("data-quantity")) || 0;
                        row.querySelector(".unitCost").value = price.toFixed(2);
                        row.querySelector(".availableQuantity").value = available;
                        row.querySelector(".quantity").setAttribute("max", available);
                        updateRowTotal(row);
                    });

                    quantityInput.addEventListener("input", function() {
                        let enteredQty = parseFloat(this.value) || 0;
                        let originalAvailable = parseFloat(row.querySelector(".productSelect").options[row.querySelector(".productSelect").selectedIndex].getAttribute("data-quantity")) || 0;

                        // Prevent exceeding available stock
                        if (enteredQty > originalAvailable) {
                            this.value = originalAvailable;
                            enteredQty = originalAvailable;
                            Swal.fire({
                                title: 'Warning',
                                text: 'Quantity cannot exceed available stock!',
                                timer: 3000
                            });
                        }

                        // Update available quantity display
                        row.querySelector(".availableQuantity").value = (originalAvailable - enteredQty).toFixed(0);
                        updateRowTotal(row);
                    });

                    row.querySelector(".removeProduct").onclick = function() {
                        row.remove();
                        calculateSummary();
                    };
                }

                // Attach events to existing rows
                document.querySelectorAll(".product-row").forEach(row => {
                    attachRowEvents(row);
                    row.querySelector(".productSelect").dispatchEvent(new Event('change'));
                });

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

                    // Add Shipping Amount
                    let shippingAmount = parseFloat(document.getElementById("shippingAmount").value) || 0;
                    subTotal += shippingAmount;

                    // SubTotal & Total Products
                    document.getElementById("subTotal").value = subTotal.toFixed(2);
                    document.getElementById("totalProducts").value = totalProducts;

                    // VAT and VAT Amount
                    let vatPercent = parseFloat(document.getElementById("vat").value) || 0;
                    let vatAmount = subTotal * vatPercent / 100;
                    document.getElementById("vatAmount").value = vatAmount.toFixed(2);

                    // Discount
                    let discountPercent = parseFloat(document.getElementById("discount").value) || 0;
                    let discountAmount = subTotal * discountPercent / 100;
                    document.getElementById("discountAmount").value = discountAmount.toFixed(2);

                    // Grand Total
                    let grandTotal = subTotal - discountAmount + vatAmount;
                    document.getElementById("grandTotal").value = grandTotal.toFixed(2);

                    let pay = parseFloat(document.getElementById("pay").value) || 0;
                    document.getElementById("due").value = (grandTotal - pay).toFixed(2);
                }

                // Event listeners for inputs
                document.getElementById("shippingAmount").addEventListener("input", calculateSummary);
                document.getElementById("discount").addEventListener("input", calculateSummary);
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

                // Initialize Select2
                $('.select').select2();

                // Initial calculation
                calculateSummary();
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
    </div>
</body>

</html>
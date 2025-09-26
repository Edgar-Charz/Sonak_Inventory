<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: signin.php?timeout=true");
    exit();
}

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");
$today = $time->format("d-m-Y");

// Get quotationUId
if (isset($_GET['id'])) {
    $quotationUId = $_GET['id'];
}

if ($quotationUId <= 0) {
    header("Location: quotationlist.php?message=invalid");
    exit();
}

// Fetch quotation details
$quotation_stmt = $conn->prepare("SELECT * FROM quotations WHERE quotationUId = ?");
$quotation_stmt->bind_param("i", $quotationUId);
$quotation_stmt->execute();
$quotation_result = $quotation_stmt->get_result();

if ($quotation_result->num_rows == 0) {
    header("Location: quotationlist.php?message=notfound");
    exit();
}

$quotation = $quotation_result->fetch_assoc();
$referenceNumber = $quotation['quotationReferenceNumber'];

// Fetch quotation details (products)
$details_stmt = $conn->prepare("SELECT 
                                            quotation_details.*, 
                                            products.productQuantity AS current_stock, products.productSellingPrice, products.productName 
                                        FROM 
                                            quotation_details
                                        JOIN 
                                            products ON quotation_details.quotationDetailProductId = products.productId 
                                        WHERE 
                                            quotation_details.quotationDetailReferenceNumber = ?");
$details_stmt->bind_param("s", $referenceNumber);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
$quotation_products = [];
while ($row = $details_result->fetch_assoc()) {
    $quotation_products[] = $row;
}

// Handle form submission for creating order
if (isset($_POST['createOrderBTN'])) {
    $invoiceNumber  = $_POST['invoice_number'];
    $customerId     = $_POST['customer_name'];
    $orderDate      = !empty($_POST['order_date']) ? date("Y-m-d", strtotime($_POST['order_date'])) : null;
    $subTotal       = str_replace(',', '', $_POST['sub_total']);
    $vat            = $_POST['vat'];
    $vatAmount      = str_replace(',', '', $_POST['vat_amount']);
    $discount       = $_POST['discount'];
    $discountAmount = str_replace(',', '', $_POST['discount_amount']);
    $shippingAmount = str_replace(',', '', $_POST['shipping_amount']);
    $grandTotal     = str_replace(',', '', $_POST['total']);
    $paymentType    = $_POST['payment_type'];
    $pay            = str_replace(',', '', $_POST['pay']);
    $due            = str_replace(',', '', $_POST['due']);
    $totalProducts  = str_replace(',', '', $_POST['total_products']);
    $products       = $_POST['products'];

    // Begin transaction
    $conn->begin_transaction();

    // Insert new order
    $insert_order_stmt = $conn->prepare("INSERT INTO orders 
        (orderInvoiceNumber, orderCustomerId, orderCreatedBy, orderUpdatedBy, orderDate, orderTotalProducts, orderSubTotal, orderVat, orderVatAmount, orderDiscount, orderDiscountAmount, orderShippingAmount, orderTotalAmount, orderPaidAmount, orderDueAmount, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_order_stmt->bind_param(
        "siiisidididddddss",
        $invoiceNumber,
        $customerId,
        $user_id,
        $user_id,
        $orderDate,
        $totalProducts,
        $subTotal,
        $vat,
        $vatAmount,
        $discount,
        $discountAmount,
        $shippingAmount,
        $grandTotal,
        $pay,
        $due,
        $current_time,
        $current_time
    );

    if (!$insert_order_stmt->execute()) {
        $conn->rollback();
        header("Location: quotationlist.php?message=error");
        exit();
    }
    $insert_order_stmt->close();

    // Insert order details and update stock
    foreach ($products as $p) {
        $productId   = $p['product_id'];
        $unitPrice   = str_replace(',', '', $p['unit_cost']);
        $quantity    = str_replace(',', '', $p['quantity']);
        $totalCost   = str_replace(',', '', $p['total_cost']);

        // Validate stock
        $stock_stmt = $conn->prepare("SELECT productQuantity FROM products WHERE productId = ?");
        $stock_stmt->bind_param("i", $productId);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        $stock = $stock_result->fetch_assoc();
        $stock_stmt->close();

        if ($quantity > $stock['productQuantity']) {
            $conn->rollback();
            header("Location: quotationlist.php?message=stockerror");
            exit();
        }

        // Insert order detail
        $insert_detail_stmt = $conn->prepare("INSERT INTO order_details 
            (orderDetailInvoiceNumber, orderDetailProductId, orderDetailUnitCost, orderDetailQuantity, orderDetailTotalCost, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_detail_stmt->bind_param("sidiiss", $invoiceNumber, $productId, $unitPrice, $quantity, $totalCost, $current_time, $current_time);

        if (!$insert_detail_stmt->execute()) {
            $conn->rollback();
            header("Location: quotationlist.php?message=error");
            exit();
        }
        $insert_detail_stmt->close();

        // Subtract from stock
        $update_product_stmt = $conn->prepare("UPDATE products SET productQuantity = productQuantity - ?, updated_at = ? WHERE productId = ?");
        $update_product_stmt->bind_param("isi", $quantity, $current_time, $productId);

        if (!$update_product_stmt->execute()) {
            $conn->rollback();
            header("Location: quotationlist.php?message=error");
            exit();
        }
        $update_product_stmt->close();
    }

    // Update quotation status to Completed (1)
    $update_quotation_stmt = $conn->prepare("UPDATE quotations SET quotationStatus = 1, quotationUpdatedBy = ?, updated_at = ? WHERE quotationUId = ?");
    $update_quotation_stmt->bind_param("isi", $user_id, $current_time, $quotationUId);

    if (!$update_quotation_stmt->execute()) {
        $conn->rollback();
        header("Location: quotationlist.php?message=error");
        exit();
    }
    $update_quotation_stmt->close();

    // Update quotation details status to Approved (1)
    $details_stmt = $conn->prepare("UPDATE quotation_details SET quotationDetailStatus = 1, updated_at = ? WHERE quotationDetailReferenceNumber = ?");
    $details_stmt->bind_param("ss", $current_time, $referenceNumber);

    if (!$details_stmt->execute()) {
        $details_stmt->close();
        $conn->rollback();
        header("Location: quotationlist.php?message=error");
        exit();
    }
    $details_stmt->close();

    // Insert into transactions table
    $insert_transaction_stmt = $conn->prepare("INSERT INTO transactions (transactionCustomerId, 	transactionInvoiceNumber, transactionPaymentType, transactionPaidAmount, transactionDueAmount, transactionDate, transactionCreatedAt, transactionUpdatedAt) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_transaction_stmt->bind_param(
        "issddsss",
        $customerId,
        $invoiceNumber,
        $paymentType,
        $pay,
        $due,
        $orderDate,
        $current_time,
        $current_time
    );

    if (!$insert_transaction_stmt->execute()) {
        $conn->rollback();
        header("Location: quotationlist.php?message=error");
        exit();
    }
    $insert_transaction_stmt->close();


    // Commit if everything was successful
    $conn->commit();
    header("Location: quotationlist.php?message=approved");
    exit();
}

function generateInvoiceNumber($conn)
{
    $query = "SELECT orderInvoiceNumber 
                FROM orders 
                WHERE orderInvoiceNumber  
                LIKE 'SNK-S%' 
                ORDER BY orderInvoiceNumber 
                DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = intval(substr($row['orderInvoiceNumber'], 6));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
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
    <title>Sonak Inventory | Approve Quotation</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/plugins/sweetalert/sweetalert2.all.min.css">
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper">
        <!-- Header -->
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
                    <div class="dropdown-menu menu-drop-user">
                        <div class="profilename">
                            <div class="profileset">
                                <span class="user-img">
                                    <img src="<?= !empty($_SESSION['profilePicture']) ? 'assets/img/profiles/' . $_SESSION['profilePicture'] : 'assets/img/profiles/avator1.jpg' ?>" alt="User Image">
                                    <span class="status online"></span>
                                </span>
                                <div class="profilesets">
                                    <h6><?= ($_SESSION['username'] ?? 'Guest') ?></h6>
                                    <h5><?= ($_SESSION['userRole'] ?? 'User') ?></h5>
                                </div>
                            </div>
                            <hr class="m-0">
                            <a class="dropdown-item" href="profile.php"><i class="me-2" data-feather="user"></i> My Profile</a>
                            <a class="dropdown-item" href="#"><i class="me-2" data-feather="settings"></i>Settings</a>
                            <hr class="m-0">
                            <a class="dropdown-item logout pb-0" href="signout.php">
                                <img src="assets/img/icons/log-out.svg" class="me-2" alt="img">Logout
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

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li><a href="index.php"><img src="assets/img/icons/dashboard.svg" alt="img"><span> Dashboard</span></a></li>
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
                            <ul></ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Approve Quotation</h4>
                        <h6>Create order from quotation</h6>
                    </div>
                    <div class="page-btn">
                        <a href="quotationList.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Quotation List</a>
                    </div>
                </div>

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
                                            <select class="select" name="customer_name" required aria-readonly>
                                                <option value="" disabled>Select Customer</option>
                                                <?php
                                                $customers_query = $conn->query("SELECT * FROM customers");
                                                while ($customers = $customers_query->fetch_assoc()) {
                                                    $selected = ($customers['customerId'] == $quotation['quotationCustomerId']) ? 'selected' : '';
                                                    echo '<option value="' . $customers['customerId'] . '" ' . $selected . '>' . $customers['customerName'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Reference Number</label>
                                            <input type="text" name="reference_number" class="form-control" value="<?= ($quotation['quotationReferenceNumber']) ?>" readonly>
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
                                            <input type="text" name="order_date" class="form-control datetimepicker" placeholder="DD-MM-YYYY" value="<?= $today; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select name="payment_type" class="select">
                                                <option value="" disabled>Select Payment Type</option>
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
                                <div id="productsContainer">
                                    <?php foreach ($quotation_products as $index => $product): ?>
                                        <div class="row product-row align-items-end gy-2 mb-3">
                                            <div class="col-lg-3">
                                                <label class="form-label">Product</label>
                                                <select name="products[<?= $index ?>][product_id]" class="form-control productSelect" required>
                                                    <option value="" disabled>Select Product</option>
                                                    <?php
                                                    $products_query = $conn->query("SELECT * FROM products");
                                                    while ($p = $products_query->fetch_assoc()) {
                                                        $selected = ($p['productId'] == $product['quotationDetailProductId']) ? 'selected' : '';
                                                        echo '<option value="' . $p['productId'] . '" 
                                                            data-price="' . $p['productSellingPrice'] . '"
                                                            data-quantity="' . $p['productQuantity'] . '" ' . $selected . '>'
                                                            . $p['productName'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Unit Cost</label>
                                                <input type="text" name="products[<?= $index ?>][unit_cost]" class="form-control unitCost" value="<?= ($product['quotationDetailUnitPrice']) ?>" readonly>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Available</label>
                                                <input type="text" name="products[<?= $index ?>][available_quantity]" class="form-control availableQuantity" value="<?= ($product['current_stock']) ?>" readonly>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="products[<?= $index ?>][quantity]" class="form-control quantity" value="<?= ($product['quotationDetailQuantity']) ?>" min="0">
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Total Cost</label>
                                                <input type="text" name="products[<?= $index ?>][total_cost]" class="form-control totalCost" value="<?= ($product['quotationDetailSubTotal']) ?>" readonly>
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
                                            <input type="text" name="total_products" id="totalProducts" class="form-control" value="<?= ($quotation['quotationTotalProducts']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Shipping Amount</label>
                                            <input type="text" name="shipping_amount" id="shippingAmount" class="form-control" value="<?= ($quotation['quotationShippingAmount']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Subtotal</label>
                                            <input type="text" name="sub_total" id="subTotal" class="form-control" value="<?= ($quotation['quotationSubTotal']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT (%)</label>
                                            <input type="number" name="vat" id="vat" class="form-control" value="<?= ($quotation['quotationTaxPercentage']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>VAT Amount</label>
                                            <input type="text" name="vat_amount" id="vatAmount" class="form-control" value="<?= ($quotation['quotationTaxAmount']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Discount (%)</label>
                                            <input type="number" name="discount" id="discount" class="form-control" value="<?= ($quotation['quotationDiscountPercentage']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Discount Amount</label>
                                            <input type="text" name="discount_amount" id="discountAmount" class="form-control" value="<?= ($quotation['quotationDiscountAmount']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Total</label>
                                            <input type="text" name="total" id="grandTotal" class="form-control" value="<?= ($quotation['quotationTotalAmount']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Paid Amount</label>
                                            <input type="text" name="pay" id="pay" class="form-control" value="0.00">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Due</label>
                                            <input type="text" name="due" id="due" class="form-control" value="<?= ($quotation['quotationTotalAmount']) ?>" readonly>
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
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <!-- /Summary Table -->
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" id="backStep2">Back</button>
                                    <button type="submit" name="createOrderBTN" class="btn btn-success">Create Order</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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
        <script src="assets/js/script.js"></script>
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

                // Function to format numbers
                function numberFormatter(number, decimals = 0) {
                    if (!number || isNaN(number)) return (0).toLocaleString("en-US", {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                    return parseFloat(number).toLocaleString("en-US", {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                }

                // Function to update available quantities
                function updateAvailableQuantities() {
                    const productQuantities = {};

                    // Calculate total quantity used per product
                    document.querySelectorAll(".product-row").forEach(row => {
                        const productSelect = row.querySelector(".productSelect");
                        const productId = productSelect.value;
                        const qty = parseFloat(row.querySelector(".quantity").value.replace(/,/g, '')) || 0;
                        if (productId) {
                            productQuantities[productId] = (productQuantities[productId] || 0) + qty;
                        }
                    });

                    // Update available quantity for each row
                    document.querySelectorAll(".product-row").forEach(row => {
                        const productSelect = row.querySelector(".productSelect");
                        const productId = productSelect.value;
                        if (productId) {
                            const originalAvailable = parseFloat(productSelect.options[productSelect.selectedIndex].getAttribute("data-quantity")) || 0;
                            const usedQty = productQuantities[productId] || 0;
                            const availableQty = originalAvailable - usedQty;
                            row.querySelector(".availableQuantity").value = numberFormatter(availableQty, 0);
                        } else {
                            row.querySelector(".availableQuantity").value = '';
                        }
                    });
                }

                // Function to validate quantities and show alerts
                function validateQuantities() {
                    let hasExceedingQuantities = false;
                    let hasZeroQuantities = false;
                    let alertMessage = "";

                    document.querySelectorAll(".product-row").forEach(row => {
                        const productSelect = row.querySelector(".productSelect");
                        const productId = productSelect.value;
                        if (productId) {
                            const productName = productSelect.options[productSelect.selectedIndex]?.text || "Unknown Product";
                            const enteredQty = parseFloat(row.querySelector(".quantity").value.replace(/,/g, '')) || 0;
                            const originalAvailable = parseFloat(productSelect.options[productSelect.selectedIndex].getAttribute("data-quantity")) || 0;

                            if (enteredQty === 0) {
                                hasZeroQuantities = true;
                                alertMessage += `- ${productName}: Quantity cannot be zero\n`;
                            } else if (enteredQty > originalAvailable) {
                                hasExceedingQuantities = true;
                                alertMessage += `- ${productName}: Requested ${numberFormatter(enteredQty, 0)}, Available ${numberFormatter(originalAvailable, 0)}\n`;
                                // Adjust quantity to available stock
                                row.querySelector(".quantity").value = numberFormatter(originalAvailable, 0);
                                updateRowTotal(row);
                            }
                        }
                    });

                    if (hasZeroQuantities || hasExceedingQuantities) {
                        Swal.fire({
                            title: 'Invalid Input',
                            text: alertMessage,
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            updateAvailableQuantities();
                            calculateSummary();
                        });
                        return false; 
                    }
                    return true; 
                }

                // Step navigation event listeners
                document.getElementById('goStep2').addEventListener('click', () => showStep(2));
                document.getElementById('backStep1').addEventListener('click', () => showStep(1));
                document.getElementById('goStep3').addEventListener('click', () => {
                    if (!validateQuantities()) {
                        return; // Stop if validation fails
                    }

                    calculateSummary();
                    const summaryBody = document.querySelector("#orderSummaryTable tbody");
                    summaryBody.innerHTML = "";
                    document.querySelectorAll(".product-row").forEach(row => {
                        let productSelect = row.querySelector(".productSelect");
                        let productName = productSelect.options[productSelect.selectedIndex]?.text || "N/A";
                        let qty = parseFloat(row.querySelector(".quantity").value.replace(/,/g, '')) || 0;
                        let unitCost = parseFloat(row.querySelector(".unitCost").value.replace(/,/g, '')) || 0;
                        let total = parseFloat(row.querySelector(".totalCost").value.replace(/,/g, '')) || 0;
                        let tr = document.createElement("tr");
                        tr.innerHTML = `
                                    <td>${productName}</td>
                                    <td>${numberFormatter(qty, 0)}</td>
                                    <td>${numberFormatter(unitCost, 2)}</td>
                                    <td>${numberFormatter(total, 2)}</td>
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
                                data-price="' . $p['productSellingPrice'] . '"
                                data-quantity="' . $p['productQuantity'] . '">'
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
                    attachRowEvents(row);
                };

                function attachRowEvents(row) {
                    const productSelect = row.querySelector(".productSelect");
                    const quantityInput = row.querySelector(".quantity");

                    productSelect.addEventListener("change", function() {
                        let price = parseFloat(this.options[this.selectedIndex].getAttribute("data-price")) || 0;
                        let available = parseFloat(this.options[this.selectedIndex].getAttribute("data-quantity")) || 0;
                        row.querySelector(".unitCost").value = numberFormatter(price, 2);
                        row.querySelector(".availableQuantity").value = numberFormatter(available, 0);
                        row.querySelector(".quantity").setAttribute("max", available);
                        updateRowTotal(row);
                    });

                    quantityInput.addEventListener("input", function() {
                        let raw = this.value.replace(/[^0-9]/g, ''); // Allow only digits
                        let enteredQty = parseFloat(raw) || 0;
                        let originalAvailable = parseFloat(row.querySelector(".productSelect").options[row.querySelector(".productSelect").selectedIndex].getAttribute("data-quantity")) || 0;

                        // Prevent exceeding available stock
                        if (enteredQty > originalAvailable) {
                            this.value = numberFormatter(originalAvailable, 0);
                            enteredQty = originalAvailable;
                            Swal.fire({
                                title: 'Warning',
                                text: 'Quantity cannot exceed available stock!',
                                icon: 'warning',
                                timer: 3000
                            });
                        } else {
                            this.value = numberFormatter(enteredQty, 0);
                        }

                        // Update available quantity display
                        updateAvailableQuantities();
                        updateRowTotal(row);
                    });

                    quantityInput.addEventListener("blur", function() {
                        const raw = this.value.replace(/,/g, '');
                        const qty = parseFloat(raw) || 0;
                        this.value = numberFormatter(qty, 0);
                        updateRowTotal(row);
                    });

                    row.querySelector(".removeProduct").onclick = function() {
                        row.remove();
                        updateAvailableQuantities();
                        calculateSummary();
                    };
                }

                function updateRowTotal(row) {
                    let qty = parseFloat(row.querySelector(".quantity").value.replace(/,/g, '')) || 0;
                    let price = parseFloat(row.querySelector(".unitCost").value.replace(/,/g, '')) || 0;
                    let total = qty * price;
                    row.querySelector(".totalCost").value = numberFormatter(total, 2);
                    calculateSummary();
                }

                function calculateSummary() {
                    let subTotal = 0;
                    let totalProducts = 0;

                    document.querySelectorAll(".product-row").forEach(row => {
                        let qty = parseFloat(row.querySelector(".quantity").value.replace(/,/g, '')) || 0;
                        let cost = parseFloat(row.querySelector(".totalCost").value.replace(/,/g, '')) || 0;
                        subTotal += cost;
                        totalProducts += qty;
                    });

                    // Add shipping Amount
                    let shippingAmount = parseFloat(document.getElementById("shippingAmount").value.replace(/,/g, '')) || 0;
                    subTotal += shippingAmount;

                    document.getElementById("subTotal").value = numberFormatter(subTotal, 2);
                    document.getElementById("totalProducts").value = numberFormatter(totalProducts, 0);

                    let vatPercent = parseFloat(document.getElementById("vat").value) || 0;
                    let discountPercent = parseFloat(document.getElementById("discount").value) || 0;

                    let vatAmount = subTotal * vatPercent / 100;
                    let discountAmount = subTotal * discountPercent / 100;
                    let grandTotal = subTotal + vatAmount - discountAmount;

                    document.getElementById("vatAmount").value = numberFormatter(vatAmount, 2);
                    document.getElementById("discountAmount").value = numberFormatter(discountAmount, 2);
                    document.getElementById("grandTotal").value = numberFormatter(grandTotal, 2);

                    let pay = parseFloat(document.getElementById("pay").value.replace(/,/g, '')) || 0;
                    document.getElementById("due").value = numberFormatter(grandTotal - pay, 2);
                }

                function debounce(fn, delay) {
                    let timeout;
                    return function(...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => fn.apply(this, args), delay);
                    };
                }

                const shippingInput = document.getElementById("shippingAmount");

                // Allow only numbers and dot while typing
                shippingInput.addEventListener("input", () => {
                    shippingInput.value = shippingInput.value.replace(/[^0-9.]/g, '');
                });

                // Format after typing pause and update totals
                shippingInput.addEventListener("input", debounce(() => {
                    const raw = shippingInput.value.replace(/,/g, '');
                    const shippingAmount = parseFloat(raw) || 0;

                    shippingInput.value = numberFormatter(shippingAmount);
                    calculateSummary();
                }, 1000));

                // Format again on blur
                shippingInput.addEventListener("blur", () => {
                    const raw = shippingInput.value.replace(/,/g, '');
                    const shippingAmount = parseFloat(raw) || 0;

                    shippingInput.value = numberFormatter(shippingAmount, 2);
                    calculateSummary();
                });

                const vatInput = document.getElementById("vat");
                vatInput.addEventListener("input", function() {
                    if (this.value < 0) this.value = 0;
                    calculateSummary();
                });

                const discountInput = document.getElementById("discount");
                discountInput.addEventListener("input", function() {
                    if (this.value < 0) this.value = 0;
                    calculateSummary();
                });

                const payInput = document.getElementById("pay");

                // Allow only numbers and dot while typing
                payInput.addEventListener("input", () => {
                    payInput.value = payInput.value.replace(/[^0-9.]/g, '');
                });

                // Format after pause and validate
                payInput.addEventListener("input", debounce(() => {
                    const raw = payInput.value.replace(/,/g, '');
                    let enteredPay = parseFloat(raw) || 0;
                    const grandTotal = parseFloat(document.getElementById("grandTotal").value.replace(/,/g, '')) || 0;

                    // Validation
                    if (enteredPay > grandTotal) {
                        enteredPay = grandTotal;
                        Swal.fire({
                            title: 'Warning',
                            text: 'Pay amount cannot exceed the grand total!',
                            icon: 'warning',
                            timer: 3000
                        });
                    } else if (enteredPay < 0) {
                        enteredPay = 0;
                        Swal.fire({
                            title: 'Warning',
                            text: 'Pay amount cannot be negative!',
                            icon: 'warning',
                            timer: 3000
                        });
                    }

                    // Format and update
                    payInput.value = numberFormatter(enteredPay);
                    calculateSummary();
                }, 1000));

                // Format again on blur for safety
                payInput.addEventListener("blur", () => {
                    const raw = payInput.value.replace(/,/g, '');
                    let enteredPay = parseFloat(raw) || 0;
                    const grandTotal = parseFloat(document.getElementById("grandTotal").value.replace(/,/g, '')) || 0;

                    if (enteredPay > grandTotal) enteredPay = grandTotal;
                    if (enteredPay < 0) enteredPay = 0;

                    payInput.value = numberFormatter(enteredPay, 2);
                    calculateSummary();
                });

                payInput.addEventListener("blur", function() {
                    const raw = this.value.replace(/,/g, '');
                    const pay = parseFloat(raw) || 0;
                    this.value = numberFormatter(pay, 2);
                    calculateSummary();
                });

                // Attach events to existing rows
                document.querySelectorAll(".product-row").forEach(row => {
                    attachRowEvents(row);
                    // Format initial values
                    const initialPrice = parseFloat(row.querySelector(".unitCost").value) || 0;
                    const initialQty = parseFloat(row.querySelector(".quantity").value) || 0;
                    const initialTotal = parseFloat(row.querySelector(".totalCost").value) || 0;
                    row.querySelector(".unitCost").value = numberFormatter(initialPrice, 2);
                    row.querySelector(".quantity").value = numberFormatter(initialQty, 0);
                    row.querySelector(".totalCost").value = numberFormatter(initialTotal, 2);
                    row.querySelector(".productSelect").dispatchEvent(new Event('change'));
                });

                // Initial calculation and formatting on page load
                validateQuantities();
                updateAvailableQuantities();
                calculateSummary();
                // Format initial values for summary fields
                const initialShipping = parseFloat(document.getElementById("shippingAmount").value.replace(/,/g, '')) || 0;
                document.getElementById("shippingAmount").value = numberFormatter(initialShipping, 2);

                const initialPay = parseFloat(document.getElementById("pay").value.replace(/,/g, '')) || 0;
                document.getElementById("pay").value = numberFormatter(initialPay, 2);

                const initialSubTotal = parseFloat(document.getElementById("subTotal").value.replace(/,/g, '')) || 0;
                document.getElementById("subTotal").value = numberFormatter(initialSubTotal, 2);

                const initialTotalProducts = parseFloat(document.getElementById("totalProducts").value.replace(/,/g, '')) || 0;
                document.getElementById("totalProducts").value = numberFormatter(initialTotalProducts, 0);

                const initialVatAmount = parseFloat(document.getElementById("vatAmount").value.replace(/,/g, '')) || 0;
                document.getElementById("vatAmount").value = numberFormatter(initialVatAmount, 2);

                const initialDiscountAmount = parseFloat(document.getElementById("discountAmount").value.replace(/,/g, '')) || 0;
                document.getElementById("discountAmount").value = numberFormatter(initialDiscountAmount, 2);

                const initialGrandTotal = parseFloat(document.getElementById("grandTotal").value.replace(/,/g, '')) || 0;
                document.getElementById("grandTotal").value = numberFormatter(initialGrandTotal, 2);

                const initialDue = parseFloat(document.getElementById("due").value.replace(/,/g, '')) || 0;
                document.getElementById("due").value = numberFormatter(initialDue, 2);

                // Initialize Select2
                $('.select').select2();
            });
        </script>
    </div>
</body>

</html>
<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if add product button was clicked
if (isset($_POST['addProductBTN'])) {
    $product_name = $_POST['product_name'];
    $product_category = $_POST['product_category'];
    $product_type = $_POST['product_type'];
    $product_unit = $_POST['product_unit'];
    $product_quantity = $_POST['product_quantity'];
    $product_quantity_alert = $_POST['product_quantity_alert'];
    $product_buying_price = $_POST['product_buying_price'];
    $product_selling_price = $_POST['product_selling_price'];
    $product_tax = $_POST['product_tax'];
    $product_description = $_POST['product_description'];

    // Check if product exists
    $check_product_stmt = $conn->prepare("SELECT * FROM products WHERE productName = ?");
    $check_product_stmt->bind_param("s", $product_name);
    $check_product_stmt->execute();
    $result = $check_product_stmt->get_result();

    if ($result->num_rows > 0) {
        // Product already exists
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'Product with this name already exists!'
                }).then(function(){
                    window.location.href = 'productlist.php';
               });
            });
        </script>";
        exit;
    } else {
        // Insert new product 
        $insert_product_stmt = $conn->prepare("INSERT INTO `products`(`categoryId`, `unitId`, `productName`, `productType`, `quantity`, `buyingPrice`, `sellingPrice`, `quantityAlert`, `tax`, `notes`, `created_at`, `updated_at`) 
                                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_product_stmt->bind_param("iissssssssss", $product_category, $product_unit, $product_name, $product_type, $product_quantity, $product_buying_price, $product_selling_price, $product_quantity_alert, $product_tax, $product_description, $current_time, $current_time);

        if ($insert_product_stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Success',
                        text: 'Product added successfully!'
                    }).then(function(){
                        window.location.href = 'productlist.php';
                   });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error adding product. Please try again.'
                    }).then(function(){
                        window.location.href = 'productlist.php';
                   });
                });
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Product List</title>

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
                                <li><a href="productlist.php" class="active">Product List</a></li>
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
                        <h4>Product List</h4>
                        <h6>Manage your products</h6>
                    </div>
                    <div class="page-btn">
                        <button type="button" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <img src="assets/img/icons/plus.svg" alt="img">
                            Add New Product
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
                            <div class="wordset">
                                <ul>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
                                    </li>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
                                    </li>
                                    <li>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="print"><img src="assets/img/icons/printer.svg" alt="img"></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="productTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ProductName</th>
                                        <th>Category</th>
                                        <th>Unit</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>Buying</th>
                                        <th>Selling</th>
                                        <th>Tax</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch all products data from the database
                                    $products_query = $conn->query("SELECT products.*, categories.categoryName, units.unitName
                                                                                FROM products, categories, units
                                                                                WHERE categories.categoryId = products.categoryId 
                                                                                AND units.unitId = products.unitId");
                                    if ($products_query->num_rows > 0) {
                                        while ($product_row = $products_query->fetch_assoc()) {
                                            $product_id = $product_row['productId'];
                                            $quantity = $product_row['quantity'];
                                            $quantityAlert = $product_row['quantityAlert'] ?? 0;
                                            $currentStatus = $product_row['productStatus'];

                                            $newStatus = $currentStatus;

                                            if ($quantity == 0) {
                                                $newStatus = 0;
                                            } elseif ($quantity <= $quantityAlert) {
                                                $newStatus = 2;
                                            } else {
                                                $newStatus = 1;
                                            }

                                            if ($newStatus != $currentStatus) {
                                                $update_query = $conn->prepare("UPDATE products SET productStatus = ? WHERE productId = ?");
                                                $update_query->bind_param("ii", $newStatus, $product_id);
                                                $update_query->execute();
                                                $update_query->close();

                                                $product_row['productStatus'] = $newStatus;
                                            }
                                    ?>
                                            <tr>
                                                <td><?= $product_row['productId']; ?></td>
                                                <td><?= $product_row['productName']; ?></td>
                                                <td><?= $product_row['categoryName']; ?></td>
                                                <td><?= $product_row['unitName']; ?></td>
                                                <td><?= $product_row['productType']; ?></td>
                                                <td><?= $product_row['quantity']; ?></td>
                                                <td><?= number_format($product_row['buyingPrice'], 2); ?></td>
                                                <td><?= number_format($product_row['sellingPrice'], 2); ?></td>
                                                <td><?= $product_row['tax']; ?>%</td>
                                                <td>
                                                    <?php if ($product_row['productStatus'] == "0") : ?>
                                                        <span class="badges bg-lightred">OutOfStock</span>
                                                    <?php elseif ($product_row['productStatus'] == "1"): ?>
                                                        <span class="badges bg-lightgreen">Available</span>
                                                    <?php else: ?>
                                                        <span class="badges bg-lightyellow">LowStock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                                                <i class="fa fa-ellipsis-v"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                <!-- View Button -->
                                                                <li>
                                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewProduct<?= $product_id; ?>">
                                                                        <img src="assets/img/icons/eye.svg" alt="View" style="width: 16px; margin-right: 6px;">
                                                                        View
                                                                    </button>
                                                                </li>

                                                                <!-- Edit Button -->
                                                                <li>
                                                                    <a href="editproduct.php?id=<?= $product_id; ?>" class="dropdown-item">
                                                                        <img src="assets/img/icons/edit.svg" alt="Edit" style="width: 16px; margin-right: 6px;">
                                                                        Edit
                                                                    </a>
                                                                </li>
                                                                <!-- Delete Button -->
                                                                <li>
                                                                    <button type="button" class="dropdown-item" onclick="confirmDelete(<?= $product_id; ?>)">
                                                                        <img src="assets/img/icons/delete.svg" alt="Delete" style="width: 16px; margin-right: 6px;">
                                                                        Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- View Product Modal -->
                                            <div class="modal fade" id="viewProduct<?= $product_id; ?>" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="viewProductModalLabel"><?= $product_row['productName']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-lg-12 col-sm-12">
                                                                    <div class="card">
                                                                        <div class="card-body">
                                                                            <div class="bar-code-view text-center">
                                                                                <h6 class="barcode-label">Product Name: <?= $product_row['productName']; ?></h6>
                                                                            </div>
                                                                            <div class="productdetails">
                                                                                <ul class="product-bar">
                                                                                    <li>
                                                                                        <h4>Product ID</h4>
                                                                                        <h6><?= $product_row['productId']; ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Category</h4>
                                                                                        <h6><?= $product_row['categoryName']; ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Type</h4>
                                                                                        <h6><?= $product_row['productType']; ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Unit</h4>
                                                                                        <h6><?= $product_row['unitName']; ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Quantity</h4>
                                                                                        <h6><?= $product_row['quantity']; ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Minimum Qty</h4>
                                                                                        <h6><?= $product_row['quantityAlert']; ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Buying Price</h4>
                                                                                        <h6><?= number_format($product_row['buyingPrice'], 2); ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Selling Price</h4>
                                                                                        <h6><?= number_format($product_row['sellingPrice'], 2); ?></h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Tax</h4>
                                                                                        <h6><?= $product_row['tax']; ?>%</h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Status</h4>
                                                                                        <h6>
                                                                                            <?= $product_row['productStatus'] == 0 ? 'OutOfStock' : ($product_row['productStatus'] == 1 ? 'Available' : 'LowStock'); ?>
                                                                                        </h6>
                                                                                    </li>
                                                                                    <li>
                                                                                        <h4>Description</h4>
                                                                                        <h6><?= $product_row['notes']; ?></h6>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /View Product Modal -->
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Product Modal -->
                <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                            </div>
                            <div class="modal-body">
                                <!-- Add Product form -->
                                <div class="card">
                                    <div class="card-body">
                                        <form action="" method="POST" id="product-form" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Product Name</label>
                                                        <input type="text" name="product_name" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Category</label>
                                                        <select class="select" name="product_category" required>
                                                            <option value="" selected disabled>Choose Category</option>
                                                            <?php
                                                            // Select all categories from the database
                                                            $categories_query = $conn->query("SELECT * FROM categories");
                                                            if ($categories_query->num_rows > 0) {
                                                                while ($category = $categories_query->fetch_assoc()) {
                                                                    echo "<option value='" . $category['categoryId'] . "'>" . $category['categoryName'] . "</option>";
                                                                }
                                                            } else {
                                                                echo "<option value=''>No categories found</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Product Type</label>
                                                        <select class="select" name="product_type">
                                                            <option value="" selected disabled>Choose Brand</option>
                                                            <option>Samsung</option>
                                                            <option>Panasonic</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Unit</label>
                                                        <select class="select" name="product_unit" required>
                                                            <option value="" selected disabled>Choose Category Unit</option>
                                                            <?php
                                                            // Select all units from the database
                                                            $units_query = $conn->query("SELECT * FROM units");
                                                            if ($units_query->num_rows > 0) {
                                                                while ($unit = $units_query->fetch_assoc()) {
                                                                    echo "<option value='" . $unit['unitId'] . "'>" . $unit['unitName'] . "</option>";
                                                                }
                                                            } else {
                                                                echo "<option value=''>No units found</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Quantity</label>
                                                        <input type="text" name="product_quantity" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Quantity Alert</label>
                                                        <input type="text" name="product_quantity_alert">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Buying Price</label>
                                                        <input type="text" name="product_buying_price" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Selling Price</label>
                                                        <input type="text" name="product_selling_price" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Tax</label>
                                                        <input type="text" name="product_tax">
                                                    </div>
                                                </div>
                                                <!-- <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Discount Type</label>
                                                        <select class="select" name="product_discount">
                                                            <option>Percentage</option>
                                                            <option>10%</option>
                                                            <option>20%</option>
                                                        </select>
                                                    </div>
                                                </div> -->
                                                <!-- <div class="col-lg-3 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label> Status</label>
                                                        <select class="select">
                                                            <option>Closed</option>
                                                            <option>Open</option>
                                                        </select>
                                                    </div>
                                                </div> -->
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label>Description</label>
                                                        <textarea name="product_description" class="form-control"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <button type="submit" name="addProductBTN" class="btn btn-submit me-2">Submit</button>
                                                    <button type="reset" class="btn btn-cancel">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- End of add Product form -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End of Add Product Modal -->
            </div>
        </div>
    </div>

    <script>
        // Function to capitalize
        function capitalizeFirstLetter(input) {
            if (typeof input.value !== 'string' || input.value.length === 0) return;
            input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
        }

        // Form Validation
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if ((name === 'product_name') && !/^[A-Za-z\s,\.]+$/.test(value)) {
                return 'Name fields should contain letters only.';
            }


            if ((name === 'product_quantity' || name === 'product_quantity_alert') && !/^[1-9]\d*$/.test(value)) {
                return 'Quantity contains only numbers.';
            }

            if (name === 'product_tax' && !/^[0-9]+$/.test(value)) {
                return 'Tax contains only numbers.';
            }

            if ((name === 'product_buying_price' || name === 'product_selling_price') && !/^\d+(\.\d{1,2})?$/.test(value)) {
                return 'Invalid Price. Please enter a valid monetary amount.';
            }

            if ((name === 'product_category' || name === 'product_unit' || name === 'product_type') && value === '') {
                return 'Please select an option.';
            }
            return true;
        }

        document.getElementById("product-form").addEventListener("submit", function(event) {
            var inputs = this.querySelectorAll('input, select');
            for (let input of inputs) {
                const result = validateInput(input);
                if (result !== true) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Error!!',
                        text: result
                    });
                    input.focus();
                    return;
                }
            }
        });

        // Function to confirm product deletion 
        function confirmDelete(productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deleteproduct.php?id=' + productId;
                }
            });
        }

        // Trigger SweetAlert messages after redirect
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'success') {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Product has been deleted successfully.',
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
                    title: 'Error!',
                    text: 'Failed to delete the product.',
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

    <!-- Products Table  -->
    <script>
        $(document).ready(function() {
            if ($("#productTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#productTable")) {
                    $("#productTable").DataTable({
                        destroy: true,
                        bFilter: true,
                        sDom: "fBtlpi",
                        pagingType: "numbers",
                        ordering: true,
                        language: {
                            search: " ",
                            sLengthMenu: "_MENU_",
                            searchPlaceholder: "Search Products...",
                            info: "_START_ - _END_ of _TOTAL_ items"
                        },
                        initComplete: function(settings, json) {
                            $(".dataTables_filter").appendTo("#tableSearch");
                            $(".dataTables_filter").appendTo(".search-input");
                        }
                    });
                }
            }
        });
    </script>

</body>

</html>
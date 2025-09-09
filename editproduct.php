<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid product ID";
    exit;
}

// Get product id
$product_id = $_GET['id'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Handle product update
if (isset($_POST['updateProductBTN'])) {
    $product_id = $_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $product_category = $_POST['product_category'];
    $product_type = trim($_POST['product_type']);
    $product_unit = $_POST['product_unit'];
    $product_quantity = $_POST['product_quantity'];
    $product_quantity_alert = $_POST['product_quantity_alert'];
    $product_buying_price = $_POST['product_buying_price'];
    $product_selling_price = $_POST['product_selling_price'];
    $product_description = trim($_POST['product_description']);
    $product_tax = $_POST['product_tax'];
    // $product_status = $_POST['product_status'];

    // Get current product data to compare for changes
    $current_data_query = "SELECT productName, categoryId, productType, unitId, quantity, quantityAlert, buyingPrice, sellingPrice, notes, tax FROM products WHERE productId = ?";
    $current_data_stmt = $conn->prepare($current_data_query);
    $current_data_stmt->bind_param("i", $product_id);
    $current_data_stmt->execute();
    $current_result = $current_data_stmt->get_result();
    $current_data = $current_result->fetch_assoc();

    // Check if no changes were made
    if (
        $current_data &&
        $current_data['productName'] == $product_name &&
        $current_data['categoryId'] == $product_category &&
        $current_data['productType'] == $product_type &&
        $current_data['unitId'] == $product_unit &&
        $current_data['quantity'] == $product_quantity &&
        $current_data['quantityAlert'] == $product_quantity_alert &&
        $current_data['buyingPrice'] == $product_buying_price &&
        $current_data['sellingPrice'] == $product_selling_price &&
        $current_data['notes'] == $product_description &&
        $current_data['tax'] == $product_tax 
        // && $current_data['productStatus'] == $product_status
    ) {

        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Info',
                        text: 'No changes were made to the product.',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = 'productlist.php';
                    });
                });
             </script>";
    } else {
        // Check if product name already exists for another product
        $check_name_query = "SELECT productId FROM products WHERE productName = ? AND productId != ?";
        $check_name_stmt = $conn->prepare($check_name_query);
        $check_name_stmt->bind_param("si", $product_name, $product_id);
        $check_name_stmt->execute();
        $name_result = $check_name_stmt->get_result();

        if ($name_result->num_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            text: 'Product name already exists for another product.',
                            title: 'Error',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(function() {
                            window.location.href = 'editproduct.php?id=$product_id';
                        });
                    });
                 </script>";
        } else {
            // Proceed with the update
            $update_product_query = "UPDATE products SET productName=?, categoryId=?, productType=?, unitId=?, quantity=?, quantityAlert=?, buyingPrice=?, sellingPrice=?, notes=?, tax=?, updated_at=? WHERE productId=?";
            $update_product_stmt = $conn->prepare($update_product_query);
            $update_product_stmt->bind_param("siisiiddsssi", $product_name, $product_category, $product_type, $product_unit, $product_quantity, $product_quantity_alert, $product_buying_price, $product_selling_price, $product_description, $product_tax, $current_time, $product_id);

            if ($update_product_stmt->execute()) {
                if ($update_product_stmt->affected_rows > 0) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    text: 'Product updated successfully.',
                                    title: 'Success',
                                    timer: 5000,
                                    timerProgressBar: true
                                }).then(function() {
                                    window.location.href = 'productlist.php';
                                });
                            });
                          </script>";
                } else {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    text: 'No changes were made to the product.',
                                    title: 'Info',
                                    timer: 5000,
                                    timerProgressBar: true
                                }).then(function() {
                                    window.location.href = 'productlist.php';
                                });
                            });
                          </script>";
                }
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                text: 'Error updating product: " . $conn->error . "',
                                title: 'Error',
                                timer: 5000,
                                timerProgressBar: true
                            });
                        });
                    </script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sonak Inventory | Edit Product</title>

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
                        <h4>Product Edit</h4>
                        <h6>Update your product</h6>
                    </div>
                </div>

                <!-- Product Update -->
                <div class="card">
                    <div class="card-body">
                        <?php
                        // Select all products data
                        $product_query = $conn->query("SELECT products.*, categories.categoryName, units.unitName
                                              FROM products 
                                              INNER JOIN categories ON products.categoryId = categories.categoryId
                                              INNER JOIN units ON products.unitId = units.unitId
                                              WHERE products.productId = '$product_id'");
                        $product_row = $product_query->fetch_assoc();
                        ?>
                        <form action="" method="POST" id="update-product-form" enctype="multipart/form-data">
                            <div class="row">
                                <input type="hidden" name="product_id" value="<?= $product_id; ?>">

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Product Name</label>
                                        <input type="text" name="product_name" class="form-control" value="<?= $product_row['productName']; ?>" required>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select class="form-control select" name="product_category" required>
                                            <option value="<?= $product_row['categoryId']; ?>"><?= $product_row['categoryName']; ?></option>
                                            <?php
                                            // Add other category options
                                            $categories_query = $conn->query("SELECT * FROM categories WHERE categoryId != '{$product_row['categoryId']}'");
                                            while ($category = $categories_query->fetch_assoc()) {
                                                echo "<option value='{$category['categoryId']}'>{$category['categoryName']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <input type="text" name="product_type" class="form-control" value="<?= $product_row['productType']; ?>">
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <select class="form-control select" name="product_unit" required>
                                            <option value="<?= $product_row['unitId']; ?>"><?= $product_row['unitName']; ?></option>
                                            <?php
                                            // Add other unit options
                                            $units_query = $conn->query("SELECT * FROM units WHERE unitId != '{$product_row['unitId']}'");
                                            while ($unit = $units_query->fetch_assoc()) {
                                                echo "<option value='{$unit['unitId']}'>{$unit['unitName']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="product_quantity" class="form-control" value="<?= $product_row['quantity']; ?>" required>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Quantity Alert</label>
                                        <input type="number" name="product_quantity_alert" class="form-control" value="<?= $product_row['quantityAlert']; ?>" required>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Buying Price</label>
                                        <input type="number" step="0.01" name="product_buying_price" class="form-control" value="<?= $product_row['buyingPrice']; ?>">
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Selling Price</label>
                                        <input type="number" step="0.01" name="product_selling_price" class="form-control" value="<?= $product_row['sellingPrice']; ?>">
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Tax</label>
                                        <input type="number" step="0.01" name="product_tax" class="form-control" value="<?= $product_row['tax']; ?>">
                                    </div>
                                </div>

                                <!-- <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control select" name="product_status">
                                            <option value="2" <?= $product_row['productStatus'] == 2 ? 'selected' : ''; ?>>Low Stock</option>
                                            <option value="1" <?= $product_row['productStatus'] == 1 ? 'selected' : ''; ?>>Available</option>
                                            <option value="0" <?= $product_row['productStatus'] == 0 ? 'selected' : ''; ?>>Out Of Stock</option>
                                        </select>
                                    </div>
                                </div> -->

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="product_description"><?= $product_row['notes']; ?></textarea>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <button type="submit" name="updateProductBTN" class="btn btn-submit me-2">Update</button>
                                        <button type="reset" class="btn btn-cancel">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- /Edit Product Form -->
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
    <script>

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
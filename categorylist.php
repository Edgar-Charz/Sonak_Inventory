<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");


// Check if the add category button was clicked
if (isset($_POST['addBTN'])) {
    $category_id = $_POST['categoryId'];
    $category_name = trim($_POST['categoryName']);

    // Check if the category already exists
    $check_category_query = $conn->prepare("SELECT * FROM categories WHERE categoryId = ?");
    $check_category_query->bind_param("s", $category_id);
    $check_category_query->execute();
    $result = $check_category_query->get_result();

    if ($result->num_rows > 0) {
        // Category already exists
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                      text: 'Category with ID $category_id already exists.',
                      icon: 'error',
                      timer: 5000,
                      timerProgressBar: false
                    }).then(function() {
                      window.location.href = 'categorylist.php';
                    });
                });
            </script>";
    } else {
        // Insert the new category
        $insert_category_query = $conn->prepare("INSERT INTO categories (categoryId, categoryName, created_at, updated_at) VALUES (?, ?, ?, ?)");
        $insert_category_query->bind_param("ssss", $category_id, $category_name, $current_time, $current_time);

        if ($insert_category_query->execute()) {
            // Successfully add new category
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            text: 'Category added successfully.',
                            icon: 'success',
                            timer: 5000,
                            timerProgressBar: false
                        }).then(function() {
                            window.location.href = 'categorylist.php';
                        });
                    });
                </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            text: 'Error adding category: " . $conn->error . "',
                            icon: 'error',
                            timer: 5000,
                            timerProgressBar: false
                        });
                    });
                </script>";
        }
        $insert_category_query->close();
    }
    $check_category_query->close();
}

// Check if the update category button was clicked
if (isset($_POST['updateBTN'])) {
    $og_id = $_POST['og_category_id'];
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];

    // Get the current category data to compare
    $current_data_query = "SELECT categoryName FROM categories WHERE categoryId = ?";
    $current_data_stmt = $conn->prepare($current_data_query);
    $current_data_stmt->bind_param("s", $category_id);
    $current_data_stmt->execute();
    $current_result = $current_data_stmt->get_result();
    $current_data = $current_result->fetch_assoc();

    // Check if no changes were made
    if (
        $current_data &&
        $current_data['categoryName'] == $category_name &&
        $og_id == $category_id
    ) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'No changes were made to the category.',
                        icon: 'info',
                        timer: 5000,
                        timerProgressBar: false
                    }).then(function() {
                        window.location.href = 'categorylist.php';
                    });
                });
             </script>";
    } else {
        // Check if the category already exists
        $check_category_query = "SELECT * FROM categories WHERE categoryId = ? AND categoryId != ?";
        $check_category_stmt = $conn->prepare($check_category_query);
        $check_category_stmt->bind_param("ss", $category_id, $og_id);
        $check_category_stmt->execute();
        $check_result = $check_category_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                    text: 'Category with ID already exists.',
                    icon: 'error',
                    timer: 5000,
                    timerProgressBar: false
                }).then(function() {
                    window.location.href = 'categorylist.php';
                });
              });
             </script>";
        } else {
            $update_category_query = "UPDATE categories SET categoryName=?, updated_at=? WHERE categoryId = ?";
            $update_category_stmt = $conn->prepare($update_category_query);
            $update_category_stmt->bind_param("ssi", $category_name, $current_time,  $category_id);

            if ($update_category_stmt->execute()) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                        text: 'Category updated successfully.',
                        icon: 'success',
                        timer: 5000,
                        timerProgressBar: false
                    }).then(function() {
                         window.location.href = 'categorylist.php';
                    });
                  });
                  </script>";
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            text: 'Error updating category: " . $conn->error . "',
                            icon: 'error',
                            timer: 5000,
                            timerProgressBar: false
                        });
                    });
                </script>";
            }
        }
    }
}

// Check if the add unit button was clicked
if (isset($_POST['addUnitBTN'])) {
    $unit_name = $_POST['unit_name'];
    $unit_short_code = $_POST['unit_short_code'];

    // Check if the unit already exists
    $check_unit_query = "SELECT * FROM units WHERE unitShortCode = ?";
    $check_unit_stmt = $conn->prepare($check_unit_query);
    $check_unit_stmt->bind_param("s", $unit_short_code);
    $check_unit_stmt->execute();
    $check_unit_result = $check_unit_stmt->get_result();

    if ($check_unit_result->num_rows > 0) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'Unit with this short code already exists.',
                        icon: 'error',
                        timer: 5000,
                        timerProgressBar: false
                    });
                });
            </script>";
    } else {
        // Insert the new unit
        $insert_unit_query = "INSERT INTO units (unitName, unitShortCode) VALUES (?, ?)";
        $insert_unit_stmt = $conn->prepare($insert_unit_query);
        $insert_unit_stmt->bind_param("ss", $unit_name, $unit_short_code);

        if ($insert_unit_stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'Unit added successfully.',
                        icon: 'success',
                        timer: 5000,
                        timerProgressBar: false
                    });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'Error adding unit: " . $conn->error . "',
                        icon: 'error',
                        timer: 5000,
                        timerProgressBar: false
                    });
                });
            </script>";
        }
    }
}

// Check if the update unit button was clicked
if (isset($_POST['updateUnitBTN'])) {
    $unit_id = $_POST['unit_id'];
    $unit_name = $_POST['unit_name'];
    $unit_short_code = $_POST['unit_short_code'];

    // Get the current unit data to compare
    $current_data_query = "SELECT * FROM units WHERE unitId = ?";
    $current_data_stmt = $conn->prepare($current_data_query);
    $current_data_stmt->bind_param("s", $unit_id);
    $current_data_stmt->execute();
    $current_result = $current_data_stmt->get_result();
    $current_data = $current_result->fetch_assoc();

    // Check if no changes were made
    if (
        $current_data &&
        $current_data['unitName'] == $unit_name &&
        $current_data['unitShortCode'] == $unit_short_code &&
        $current_data['unitId'] == $unit_id
    ) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'No changes were made to the unit.',
                        icon: 'info',
                        timer: 5000,
                        timerProgressBar: false
                    }).then(function() {
                        window.location.href = 'categorylist.php';
                    });
                });
             </script>";
    } else {
        $update_unit_query = "UPDATE units SET unitName=?, unitShortCode=?, updated_at=? WHERE unitId = ?";
        $update_unit_stmt = $conn->prepare($update_unit_query);
        $update_unit_stmt->bind_param("sssi", $unit_name, $unit_short_code, $current_time,  $unit_id);

        if ($update_unit_stmt->execute()) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                        text: 'Unit updated successfully.',
                        icon: 'success',
                        timer: 5000,
                        timerProgressBar: false
                    }).then(function() {
                         window.location.href = 'categorylist.php';
                    });
                  });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            text: 'Error updating unit: " . $conn->error . "',
                            icon: 'error',
                            timer: 5000,
                            timerProgressBar: false
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
    <title>Sonak Inventory | Categories & Units</title>

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
                                <li><a href="productlist.php">Product List</a></li>
                                <li><a href="categorylist.php" class="active">Category List</a></li>
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
                        <h4>Product Category & Unit Lists</h4>
                        <h6>View/Search Product Category & Unit</h6>
                    </div>


                </div>

                <div class="row">
                    <!-- Category Row -->
                    <div class="col-lg-6 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Categories List</h4>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                                Add Category
                                            </button>
                                        </li>

                                    </ul>
                                </div>
                            </div>

                            <!-- Category Table -->
                            <div class="card-body">
                                <div class="table-top">
                                    <div class="search-set">
                                        <div class="search-path">
                                            <a class="btn btn-filter" id="filter_search_category">
                                                <img src="assets/img/icons/filter.svg" alt="img">
                                                <span><img src="assets/img/icons/closes.svg" alt="img"></span>
                                            </a>
                                        </div>
                                        <div class="search-input" id="tableSearchCategory">
                                            <a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table" id="categoryTable">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Category Name</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $categories_query = $conn->query("SELECT * FROM categories");
                                            $sn = 0;
                                            if ($categories_query->num_rows > 0) {
                                                while ($row = $categories_query->fetch_assoc()) {
                                                    $category_id = $row['categoryId'];
                                                    $sn++;
                                            ?>
                                                    <tr>
                                                        <td><?= $sn; ?></td>
                                                        <td><?= $row['categoryName']; ?></td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center">
                                                                <div class="dropdown">
                                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                                                        <i class="fa fa-ellipsis-v"></i>
                                                                    </a>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                        <li>
                                                                            <button type="button"
                                                                                class="dropdown-item"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#editCategoryModal<?= $category_id; ?>">
                                                                                <img src="assets/img/icons/edit.svg" alt="Edit" style="width: 16px; margin-right: 6px;">
                                                                                Edit Category
                                                                            </button>
                                                                        </li>
                                                                        <!-- <li>
                                                                            <button type="button"
                                                                                class="dropdown-item"
                                                                                onclick="confirmDelete('deletecategory.php?categoryId=<?= $row['categoryId']; ?>')">
                                                                                <img src="assets/img/icons/delete.svg" alt="Delete" style="width: 16px; margin-right: 6px;">
                                                                                Delete
                                                                            </button>
                                                                        </li> -->
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Edit Category Modal -->
                                                    <div class="modal fade" id="editCategoryModal<?= $category_id; ?>" tabindex="-1" aria-labelledby="editCategoryModalLabel<?= $category_id; ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <form method="POST" action="">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="editCategoryModalLabel<?= $category_id; ?>">Edit Category</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="og_category_id" value="<?= $category_id; ?>">
                                                                        <div class="mb-3">
                                                                            <!-- <label>Category ID</label> -->
                                                                            <input type="hidden" name="category_id" class="form-control" value="<?= $category_id; ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label>Category Name</label>
                                                                            <input type="text" class="form-control" name="category_name" value="<?= $row['categoryName']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit" name="updateBTN" class="btn btn-primary">Update</button>
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- End of Edit Category Modal -->
                                            <?php
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- /Category Table -->
                        </div>
                        <!-- Add Category Modal -->
                        <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="addCategoryForm" method="POST" action="">
                                            <div class="mb-3">
                                                <label for="categoryId" class="form-label">Category ID</label>
                                                <input type="number" class="form-control" id="categoryId" name="categoryId" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="categoryName" class="form-label">Category Name</label>
                                                <input type="text" class="form-control" id="categoryName" name="categoryName" required>
                                            </div>
                                            <div class="submit-section">
                                                <button type="submit" class="btn btn-primary save-category submit-btn" name="addBTN">Save</button>
                                                <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> -->
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End of Add Category Modal -->
                    </div>
                    <!-- /Category Row -->

                    <!-- Unit Row -->
                    <div class="col-lg-6 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Units List</h4>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                        <li>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                                                Add Unit
                                            </button>
                                        </li>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Units Table -->
                            <div class="card-body">
                                <div class="table-top">
                                    <div class="search-set">
                                        <div class="search-path">
                                            <a class="btn btn-filter" id="filter_search_unit">
                                                <img src="assets/img/icons/filter.svg" alt="img">
                                                <span><img src="assets/img/icons/closes.svg" alt="img"></span>
                                            </a>
                                        </div>
                                        <div class="search-input" id="tableSearchUnit">
                                            <a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table" id="unitTable">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Unit Name</th>
                                                <th>Short Code</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $units_query = $conn->query("SELECT * FROM units");
                                            $sn = 0;

                                            if ($units_query->num_rows > 0) {
                                                while ($unit_row = $units_query->fetch_assoc()) {
                                                    $unit_id = $unit_row["unitId"];
                                                    $sn++;
                                            ?>
                                                    <tr>
                                                        <td><?= $sn; ?></td>
                                                        <td><?= $unit_row['unitName']; ?></td>
                                                        <td><?= $unit_row['unitShortCode']; ?></td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center">
                                                                <div class="dropdown">
                                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                                                        <i class="fa fa-ellipsis-v"></i>
                                                                    </a>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                        <li>
                                                                            <button type="button"
                                                                                class="dropdown-item"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#editUnitModal<?= $unit_id; ?>">
                                                                                <img src="assets/img/icons/edit.svg" alt="Edit" style="width: 16px; margin-right: 6px;">
                                                                                Edit Unit
                                                                            </button>
                                                                        </li>
                                                                        <!-- <li>
                                                                            <button type="button"
                                                                                class="dropdown-item"
                                                                                onclick="confirmDelete('deletecategory.php?unitId=<?= $unit_row['unitId']; ?>')">
                                                                                <img src="assets/img/icons/delete.svg" alt="Delete" style="width: 16px; margin-right: 6px;">
                                                                                Delete
                                                                            </button>
                                                                        </li> -->
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Unit Modal -->
                                                    <div class="modal fade" id="editUnitModal<?= $unit_id; ?>" tabindex="-1" aria-labelledby="editUnitModalLabel<?= $unit_id; ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <form method="POST" action="">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="editUnitModalLabel<?= $unit_id; ?>">Edit Unit</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="unit_id" value="<?= $unit_id; ?>">
                                                                        <div class="mb-3">
                                                                            <label for="editUnitName" class="form-label">Unit Name</label>
                                                                            <input type="text" class="form-control" name="unit_name" value="<?= $unit_row['unitName']; ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="editUnitShortCode" class="form-label">Short Code</label>
                                                                            <input type="text" class="form-control" name="unit_short_code" value="<?= $unit_row['unitShortCode']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit" name="updateUnitBTN" class="btn btn-primary">Update</button>
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                            <?php
                                                }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- /Units Table -->
                        </div>
                        <!-- Add Unit Modal -->
                        <div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addUnitModalLabel">Add New Unit</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="unitName" class="form-label">Unit Name</label>
                                                <input type="text" class="form-control" name="unit_name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="unitShortCode" class="form-label">Short Code</label>
                                                <input type="text" class="form-control" name="unit_short_code" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="addUnitBTN" class="btn btn-primary">Save</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- / Add Unit Modal -->
                    </div>
                    <!-- /Unit Row -->

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                Swal.fire({
                    text: 'Deleted successfully.',
                    timer: 2000,
                    showConfirmButton: true
                }).then(function() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('deleted');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 1): ?>
                Swal.fire({
                    icon: 'error',
                    text: 'There was a problem deleting.'
                }).then(function() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('error');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            <?php endif; ?>
        });

        // Confirmation before delete
        function confirmDelete(deleteUrl) {
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
                    window.location.href = deleteUrl;
                }
            });
        }
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

    <script>
        $(document).ready(function() {
            // Category Table
            if ($("#categoryTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#categoryTable")) {
                    $("#categoryTable").DataTable({
                        destroy: true,
                        bFilter: true,
                        sDom: "fBtlpi",
                        pagingType: "numbers",
                        ordering: true,
                        language: {
                            search: " ",
                            sLengthMenu: "_MENU_",
                            searchPlaceholder: "Search Categories...",
                            info: "_START_ - _END_ of _TOTAL_ items"
                        },
                        initComplete: function(settings, json) {
                            $("#categoryTable_wrapper .dataTables_filter").appendTo("#tableSearchCategory");
                            $("#tableSearchCategory .btn-searchset").before($("#categoryTable_wrapper .dataTables_filter input"));
                            $("#categoryTable_wrapper .dataTables_filter").remove();
                        }
                    });
                }
            }

            // Units Table
            if ($("#unitTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#unitTable")) {
                    $("#unitTable").DataTable({
                        destroy: true,
                        bFilter: true,
                        sDom: "fBtlpi",
                        pagingType: "numbers",
                        ordering: true,
                        language: {
                            search: " ",
                            sLengthMenu: "_MENU_",
                            searchPlaceholder: "Search Units...",
                            info: "_START_ - _END_ of _TOTAL_ items"
                        },
                        initComplete: function(settings, json) {
                            $("#unitTable_wrapper .dataTables_filter").appendTo("#tableSearchUnit");
                            $("#tableSearchUnit .btn-searchset").before($("#unitTable_wrapper .dataTables_filter input"));
                            $("#unitTable_wrapper .dataTables_filter").remove();
                        }
                    });
                }
            }
        });
    </script>
</body>

</html>
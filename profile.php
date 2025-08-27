<?php
session_start();
include 'includes/db_connection.php';

// Select user data from session
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE userId = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_row = $user_result->fetch_assoc();
} else {
    header("Location: signin.php?timeout=true");
    exit();
}

// Handle profile update
if (isset($_POST['submit_btn'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_no']);
    $username = trim($_POST['username']);

    // Update user data
    $user_stmt = $conn->prepare('UPDATE users 
                                 SET first_name = ?, last_name = ?, email = ?, phone_no = ?, username = ? 
                                 WHERE user_id = ?');
    $user_stmt->bind_param('sssssi', $first_name, $last_name, $email, $phone, $username, $user_id);

    if ($user_stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'Success',
                    text: 'Profile updated successfully!'
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'profile.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    text: 'Failed to update profile. Please try again.'
                });
            });
        </script>";
    }
    $user_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, invoice, html5, responsive, Projects">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <title>Dreams Pos admin template</title>

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
                        <span class="user-img"><img src="assets/img/profiles/avator1.jpg" alt="">
                            <span class="status online"></span>
                        </span>
                    </a>

                    <!-- User Profile -->
                    <div class="dropdown-menu menu-drop-user">
                        <div class="profilename">
                            <div class="profileset">
                                <span class="user-img"><img src="assets/img/profiles/avator1.svg" alt="">
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
                        <h4>Profile</h4>
                        <h6>User Profile</h6>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="profile-set">
                            <div class="profile-head">
                            </div>
                            <div class="profile-top">
                                <div class="profile-content">
                                    <div class="profile-contentimg">
                                        <img src="assets/img/profiles/avator.jpg" alt="img" id="blah">
                                        <div class="profileupload">
                                            <input type="file" id="imgInp">
                                            <a href="javascript:void(0);"><img src="assets/img/icons/edit-set.svg" alt="img"></a>
                                        </div>
                                    </div>
                                    <div class="profile-contentname">
                                        <h2><?= $user_row['username'] ?></h2>
                                        <h4>Updates Your Photo and Personal Details.</h4>
                                    </div>
                                </div>
                                <div class="ms-auto">
                                    <a href="javascript:void(0);" class="btn btn-submit me-2">Save</a>
                                    <a href="javascript:void(0);" class="btn btn-cancel">Cancel</a>
                                </div>
                            </div>
                        </div>
                        <form id="profile_form" method="POST" action="">
                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="text" id="email" name="email" value="<?= $user_row['userEmail']; ?>" data-original="<?= $user_row['userEmail']; ?>">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" id="phone" name="phone" value="<?= $user_row['userPhone']; ?>" data-original="<?= $user_row['userPhone']; ?>">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input type="text" id="username" name="username" value="<?= $user_row['username']; ?>" data-original="<?= $user_row['username']; ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="submit_btn" class="btn btn-submit me-2">Submit</button>
                                    <button type="reset" class="btn btn-cancel">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
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

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

    <script src="assets/js/script.js"></script>

    <script>
        document.getElementById('profile_form').addEventListener('submit', function(e) {
            let hasChanges = false;
            let isValid = true;
            let errorMessage = '';

            const inputs = this.querySelectorAll('input');

            inputs.forEach(input => {
                const value = input.value.trim();
                const original = input.dataset.original ? input.dataset.original.trim() : '';

                // Check if field is empty
                if (!value) {
                    isValid = false;
                    errorMessage = 'Please fill out all the fields.';
                }

                // Check if value changed
                if (input.dataset.original !== undefined && value !== original) {
                    hasChanges = true;
                }

                // Email validation
                if (input.name === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                    }
                }

                // Phone validation (digits only, 7–15 chars)
                if (input.name === 'phone') {
                    const phoneRegex = /^[0-9]{10,15}$/;
                    if (!phoneRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number (10–15 digits).';
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    title: 'Invalid Input',
                    text: errorMessage,
                    timer: 2500,
                    showConfirmButton: false
                });
                return;
            }

            if (!hasChanges) {
                e.preventDefault();
                Swal.fire({
                    text: 'No changes made to your profile.',
                    timer: 2000,
                    showConfirmButton: true
                });
            }
        });
    </script>


</body>

</html>
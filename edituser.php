<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid user ID";
    exit;
}
// Get user id
$user_id = $_GET['id'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Handle user update
if (isset($_POST['updateUserBTN'])) {
    $userId = $_POST['userId'];
    $username = trim($_POST['username']);
    $user_phone = trim($_POST['user_phone']);
    $user_email = trim($_POST['user_email']);
    $user_role = trim($_POST['user_role']);
    $user_status = $_POST['user_status'];

    // Get current user data to compare for changes
    $current_data_query = "SELECT username, userPhone, userEmail, userRole, userStatus FROM users WHERE userId = ?";
    $current_data_stmt = $conn->prepare($current_data_query);
    $current_data_stmt->bind_param("i", $userId);
    $current_data_stmt->execute();
    $current_result = $current_data_stmt->get_result();
    $current_data = $current_result->fetch_assoc();

    // Check if no changes were made
    if (
        $current_data &&
        $current_data['username'] == $username &&
        $current_data['userPhone'] == $user_phone &&
        $current_data['userEmail'] == $user_email &&
        $current_data['userRole'] == $user_role &&
        $current_data['userStatus'] == $user_status
    ) {

        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'No changes were made to the user.',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
    } else {
        // Check if email already exists for another user
        $check_email_query = "SELECT userId FROM users WHERE userEmail = ? AND userId != ?";
        $check_email_stmt = $conn->prepare($check_email_query);
        $check_email_stmt->bind_param("si", $user_email, $userId);
        $check_email_stmt->execute();
        $email_result = $check_email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            text: 'Email address already exists for another user.',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(function() {
                            window.location.href = 'edituser.php?id=$userId';
                        });
                    });
                 </script>";
        } else {
            // Check if username already exists for another user
            $check_username_query = "SELECT userId FROM users WHERE username = ? AND userId != ?";
            $check_username_stmt = $conn->prepare($check_username_query);
            $check_username_stmt->bind_param("si", $username, $userId);
            $check_username_stmt->execute();
            $username_result = $check_username_stmt->get_result();

            if ($username_result->num_rows > 0) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function () {
                            Swal.fire({
                                text: 'Username already exists for another user.',
                                timer: 5000,
                                timerProgressBar: true
                            }).then(function() {
                                window.location.href = 'edituser.php?id=$userId';
                            });
                        });
                     </script>";
            } else {
                // Check if phone number already exists for another user
                $check_phone_query = "SELECT userId FROM users WHERE userPhone = ? AND userId != ?";
                $check_phone_stmt = $conn->prepare($check_phone_query);
                $check_phone_stmt->bind_param("si", $user_phone, $userId);
                $check_phone_stmt->execute();
                $phone_result = $check_phone_stmt->get_result();

                if ($phone_result->num_rows > 0) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    text: 'Phone number already exists for another user.',
                                    timer: 5000,
                                    timerProgressBar: true
                                }).then(function() {
                                    window.location.href = 'edituser.php?id=$userId';
                                });
                            });
                         </script>";
                } else {
                    // Proceed with the update
                    $update_user_query = "UPDATE users SET username=?, userPhone=?, userEmail=?, userRole=?, userStatus=?, updated_at=? WHERE userId=?";
                    $update_user_stmt = $conn->prepare($update_user_query);
                    $update_user_stmt->bind_param("ssssssi", $username, $user_phone, $user_email, $user_role, $user_status, $current_time, $userId);

                    if ($update_user_stmt->execute()) {
                        if ($update_user_stmt->affected_rows > 0) {
                            echo "<script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            text: 'User updated successfully.',
                                            timer: 5000,
                                            timerProgressBar: true
                                        }).then(function() {
                                            window.location.href = 'userlist.php';
                                        });
                                    });
                                  </script>";
                        } else {
                            echo "<script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            text: 'No changes were made to the user.',
                                            timer: 5000,
                                            timerProgressBar: true
                                        }).then(function() {
                                            window.location.href = 'userlist.php';
                                        });
                                    });
                                  </script>";
                        }
                    } else {
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        text: 'Error updating user: " . $conn->error . "',
                                        timer: 5000,
                                        timerProgressBar: true
                                    });
                                });
                            </script>";
                    }
                }
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
    <title>Sonak Inventory | Edit User</title>

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
                                <li><a href="userlist.php" class="active">User List</a></li>
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
                        <h4>User Management</h4>
                        <h6>Edit/Update User</h6>
                    </div>
                    <div class="page-btn">
                        <a href="userlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Users List</a>
                    </div>
                </div>

                <!-- Update User Form -->
                <div class="card">
                    <div class="card-body">
                        <?php
                        // Fetch user data
                        $stmt = $conn->prepare("SELECT * FROM users WHERE userId = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user_row = $result->fetch_assoc();
                        ?>
                        <form action="" method="POST" id="update-user-form" enctype="multipart/form-data">
                            <div class="row">
                                <input type="hidden" name="userId" value="<?= $user_id; ?>">

                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input type="text" name="username" class="form-control" value="<?= $user_row['username']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="user_phone" class="form-control" value="<?= $user_row['userPhone']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="user_email" class="form-control" value="<?= $user_row['userEmail']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Role</label>
                                        <input type="text" name="user_role" class="form-control" value="<?= $user_row['userRole']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="user_status" class="form-control" required>
                                            <option value="1" <?= $user_row['userStatus'] == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?= $user_row['userStatus'] == 0 ? 'selected' : ''; ?>>InActive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label> User Image</label>
                                        <div class="image-upload">
                                            <input type="file" name="user_image" accept="image/*">
                                            <div class="image-uploads">
                                                <img src="assets/img/icons/upload.svg" alt="img">
                                                <h4>Drag and drop a file to upload</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-12">
                                    <div class="product-list">
                                        <ul class="row">
                                            <li class="ps-0">
                                                <div class="productviewset">
                                                    <div class="productviewsimg">
                                                        <img src="assets/img/customer/profile2.jpg" alt="img">
                                                    </div>
                                                    <div class="productviewscontent">
                                                        <a href="javascript:void(0);" class="hideset"><i class="fa fa-trash-alt"></i></a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div> -->
                                <div class="col-lg-12">
                                    <button type="submit" name="updateUserBTN" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if (name === 'user_phone' && !/^0[0-9]{9}$/.test(value)) {
                return 'Phone number must start with 0 and contain 10 digits.';
            }

            if (name === 'user_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (name === 'user_role' && value === '') {
                return 'Please select a role.';
            }

            return true;
        }

        document.getElementById("update-user-form").addEventListener("submit", function(event) {
            var inputs = this.querySelectorAll('input, select');
            for (let input of inputs) {
                var result = validateInput(input);
                if (result !== true) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        text: result,
                        position: 'center',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        input.focus();
                    });
                    return;
                }
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
</body>

</html>
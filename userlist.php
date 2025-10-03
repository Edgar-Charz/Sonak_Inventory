<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if the add user button was clicked
if (isset($_POST['addUserBTN'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = $first_name . ' ' . $last_name;
    $phone = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Check if user email already exists
        $check_user_stmt = $conn->prepare("SELECT userId FROM users WHERE userEmail = ?");
        $check_user_stmt->bind_param("s", $email);
        $check_user_stmt->execute();
        $result = $check_user_stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("User with this email already exists!");
        }

        // Check if user name exists
        $check_username_stmt = $conn->prepare("SELECT userId FROM users WHERE username = ?");
        $check_username_stmt->bind_param("s", $username);
        $check_username_stmt->execute();
        $result = $check_username_stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("User with this name already exists!");
        }

        // Hash password
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Handle image upload
        $userPhoto = null;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {
            $targetDir = "assets/img/profiles/";
            $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["image"]["tmp_name"]);

            if ($check !== false) {
                $newFileName = "user_" . time() . "." . $imageFileType;
                $newFilePath = $targetDir . $newFileName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $newFilePath)) {
                    $userPhoto = $newFileName;
                } else {
                    throw new Exception("Failed to upload profile image.");
                }
            } else {
                throw new Exception("Invalid image file.");
            }
        }

        // Insert user
        $insert_stmt = $conn->prepare("INSERT INTO users (username, userPhone, userEmail, userPassword, userRole, userPhoto, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssssss", $username, $phone, $email, $password, $role, $userPhoto, $current_time, $current_time);
        $insert_stmt->execute();

        // // Get the newly created user ID
        // $userId = $conn->insert_id;

        // // Ensure certs directory exists
        // $certDir = __DIR__ . '/assets/certs/users/';
        // if (!is_dir($certDir)) {
        //     if (!mkdir($certDir, 0777, true)) {
        //         throw new Exception("Failed to create certificate directory: $certDir");
        //     }
        // }

        // // Generate key pair
        // $privkey = openssl_pkey_new([
        //     "private_key_bits" => 2048,
        //     "private_key_type" => OPENSSL_KEYTYPE_RSA,
        // ]);
        // if (!$privkey) {
        //     throw new Exception("Failed to generate private key: " . openssl_error_string());
        // }

        // // Generate CSR
        // $dn = [
        //     "countryName" => "TZ",
        //     "stateOrProvinceName" => "Dar es Salaam",
        //     "localityName" => "Riverside",
        //     "organizationName" => "Sonak Co. Ltd",
        //     "commonName" => "User $userId",
        //     "emailAddress" => "$email"
        // ];
        // $csr = openssl_csr_new($dn, $privkey);
        // if (!$csr) {
        //     throw new Exception("Failed to generate CSR: " . openssl_error_string());
        // }

        // $sscert = openssl_csr_sign($csr, null, $privkey, 365);
        // if (!$sscert) {
        //     throw new Exception("Failed to sign certificate: " . openssl_error_string());
        // }

        // // Generate password and paths
        // $userCertKeyPassword = bin2hex(random_bytes(8));
        // $userCertSerial = strtoupper(uniqid("CERT"));
        // $userCertIssued = date("Y-m-d");
        // $userCertExpiry = date("Y-m-d", strtotime("+1 year"));
        // $userCertPath = "assets/certs/users/user_{$userId}_cert.pem";
        // $userCertKey = "assets/certs/users/user_{$userId}_key.pem";

        // // Save key and cert to files
        // $privkeyFullPath = __DIR__ . '/' . $userCertKey;
        // $certFullPath = __DIR__ . '/' . $userCertPath;
        // if (!openssl_pkey_export_to_file($privkey, $privkeyFullPath, $userCertKeyPassword)) {
        //     throw new Exception("Failed to export private key: " . openssl_error_string());
        // }
        // if (!openssl_x509_export_to_file($sscert, $certFullPath)) {
        //     throw new Exception("Failed to export certificate: " . openssl_error_string());
        // }

        // // Insert into user_certificates table
        // $cert_stmt = $conn->prepare("INSERT INTO user_certificates (userCertId, userCertPath, userCertKey, userCertKeyPassword, userCertSerial, userCertIssued, userCertExpiry, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // if (!$cert_stmt) {
        //     throw new Exception("Failed to prepare certificate insert statement: " . $conn->error);
        // }
        // $cert_stmt->bind_param(
        //     "issssssss",
        //     $userId,
        //     $userCertPath,
        //     $userCertKey,
        //     $userCertKeyPassword,
        //     $userCertSerial,
        //     $userCertIssued,
        //     $userCertExpiry,
        //     $current_time,
        //     $current_time
        // );
        // if (!$cert_stmt->execute()) {
        //     throw new Exception("Failed to insert certificate info: " . $cert_stmt->error);
        // }

        // Commit transaction
        $conn->commit();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'User added successfully!'
                }).then(function(){
                    window.location.href = 'userlist.php';
                });
            });
        </script>";
    } catch (Exception $e) {
        $conn->rollback();

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    text: '" . $e->getMessage() . "'
                }).then(function(){
                    window.location.href = 'userlist.php';
                });
            });
        </script>";
    }
}

// Handle user update
if (isset($_POST['updateUserBTN'])) {
    $userId = $_POST['userId'];
    $username = trim($_POST['username']);
    $user_phone = trim($_POST['user_phone']);
    $user_email = trim($_POST['user_email']);
    $user_role = trim($_POST['user_role']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Get current user data
        $current_data_stmt = $conn->prepare("SELECT username, userPhone, userEmail, userRole, userStatus FROM users WHERE userId = ?");
        $current_data_stmt->bind_param("i", $userId);
        $current_data_stmt->execute();
        $current_data = $current_data_stmt->get_result()->fetch_assoc();

        if (
            $current_data &&
            $current_data['username'] == $username &&
            $current_data['userPhone'] == $user_phone &&
            $current_data['userEmail'] == $user_email &&
            $current_data['userRole'] == $user_role
        ) {
            throw new Exception("no_changes");
        }

        // Check for duplicate email
        $check_email_stmt = $conn->prepare("SELECT userId FROM users WHERE userEmail = ? AND userId != ?");
        $check_email_stmt->bind_param("si", $user_email, $userId);
        $check_email_stmt->execute();

        if ($check_email_stmt->get_result()->num_rows > 0) {
            throw new Exception("Email address already exists for another user.");
        }

        // Check for duplicate username
        $check_username_stmt = $conn->prepare("SELECT userId FROM users WHERE username = ? AND userId != ?");
        $check_username_stmt->bind_param("si", $username, $userId);
        $check_username_stmt->execute();

        if ($check_username_stmt->get_result()->num_rows > 0) {
            throw new Exception("Username already exists for another user.");
        }

        // Check for duplicate phone
        $check_phone_stmt = $conn->prepare("SELECT userId FROM users WHERE userPhone = ? AND userId != ?");
        $check_phone_stmt->bind_param("si", $user_phone, $userId);
        $check_phone_stmt->execute();

        if ($check_phone_stmt->get_result()->num_rows > 0) {
            throw new Exception("Phone number already exists for another user.");
        }

        // Proceed with update
        $update_user_stmt = $conn->prepare("UPDATE users SET username = ?, userPhone = ?, userEmail = ?, userRole = ?, updated_at = ? WHERE userId = ?");
        $update_user_stmt->bind_param("sssssi", $username, $user_phone, $user_email, $user_role, $current_time, $userId);
        $update_user_stmt->execute();

        if ($update_user_stmt->affected_rows > 0) {
            $conn->commit();
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'User updated successfully.'                        
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
            </script>";
        } else {
            throw new Exception("No changes were made to the user.");
        }
    } catch (Exception $e) {
        $conn->rollback();

        // Handle specific "no changes" alert
        if ($e->getMessage() === "no_changes") {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Changes',
                        text: 'You didn\\'t modify any user details.'                        
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
            </script>";
        } else {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '" . $e->getMessage() . "'
                }).then(function() {
                    window.location.href = 'userlist.php';
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
    <title>Sonak Inventory | Users List</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/animate.css">

    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">

    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">

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
                        <h4>User List</h4>
                        <h6>Manage your User</h6>
                    </div>
                    <div class="page-btn">
                        <button type="button" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <img src="assets/img/icons/plus.svg" alt="img"> Add User
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
                                    <a class="btn btn-searchset">
                                        <img src="assets/img/icons/search-white.svg" alt="img">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card" id="filter_inputs">
                            <div class="card-body pb-0">
                                <div class="row">
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter User Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Phone">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" placeholder="Enter Email">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <select class="select">
                                                <option>Disable</option>
                                                <option>Enable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-sm-6 col-12 ms-auto">
                                        <div class="form-group">
                                            <a class="btn btn-filters ms-auto"><img src="assets/img/icons/search-whites.svg" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Profile</th>
                                        <th>User name </th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch all users data
                                    $users_query = $conn->query("SELECT * FROM users");
                                    if ($users_query->num_rows > 0) {
                                        $sn = 0;
                                        while ($user_row = $users_query->fetch_assoc()) {
                                            $user_id = $user_row["userId"];
                                            $sn++;
                                    ?>
                                            <tr>
                                                <td><?= $sn; ?></td>
                                                <td class="productimgname">
                                                    <a href="javascript:void(0);" class="product-img">
                                                        <img src="assets/img/profiles/<?= $user_row['userPhoto'] ?>" alt="product">
                                                    </a>
                                                </td>
                                                <td><?= $user_row['username']; ?></td>
                                                <td><?= $user_row['userPhone']; ?></td>
                                                <td><?= $user_row['userEmail']; ?></td>
                                                <td><?= $user_row['userRole']; ?></td>
                                                <td class="text-center">
                                                    <!-- <?php if ($user_row['userStatus'] == "1") : ?>
                                                        <span class="badges bg-success">Active</span>
                                                    <?php else : ?>
                                                        <span class="badges bg-danger">Inactive</span>
                                                    <?php endif; ?> -->

                                                    <!--Toggle User Status -->
                                                    <?php if ($user_id != $_SESSION['id']): ?>
                                                        <div class="status-toggle d-inline-flex align-items-center">
                                                            <input type="checkbox"
                                                                id="user<?= $user_id ?>"
                                                                class="check"
                                                                <?= $user_row['userStatus'] == 1 ? 'checked' : '' ?>
                                                                onchange="toggleUserStatus(<?= $user_id ?>, this.checked)">
                                                            <label for="user<?= $user_id ?>" class="checktoggle ms-1"></label>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badges bg-success">Your Account</span>
                                                    <?php endif; ?>

                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center align-items-center">

                                                        <!-- View Button -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary me-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewUser<?= $user_id; ?>">
                                                            <i class="fas fa-eye text-dark"></i>
                                                        </button>

                                                        <!-- Edit Button -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-secondary me-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editUser<?= $user_id; ?>">
                                                            <i class="fas fa-edit text-dark"></i>
                                                        </button>

                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- View User Modal -->
                                            <div class="modal fade" id="viewUser<?= $user_id; ?>" tabindex="-1" aria-labelledby="viewUserLabel<?= $user_id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">User Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>User ID</label>
                                                                        <p class="form-control"><?= $user_id; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>User Name</label>
                                                                        <p class="form-control"><?= $user_row['username']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Phone</label>
                                                                        <p class="form-control"><?= $user_row['userPhone']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Email</label>
                                                                        <p class="form-control"><?= $user_row['userEmail']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Role</label>
                                                                        <p class="form-control"><?= $user_row['userRole']; ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-sm-12 col-12">
                                                                    <div class="form-group">
                                                                        <label>Status</label>
                                                                        <p class="form-control"><?= $user_row['userStatus'] == 1 ? 'Active' : 'InActive'; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End View User Modal -->

                                            <!-- Edit User Modal -->
                                            <div class="modal fade" id="editUser<?= $user_id; ?>" tabindex="-1" aria-labelledby="editUserLabel<?= $user_id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <form action="" method="POST">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="userId" value="<?= $user_id; ?>">

                                                                <div class="row">
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
                                                                            <select name="user_role" class="select" required>
                                                                                <option <?= $user_row['userRole'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                                                                <option <?= $user_row['userRole'] == 'Technician' ? 'selected' : ''; ?>>Technician</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <div class="col-lg-6 col-sm-6 col-12">
                                                                        <div class="form-group">
                                                                            <label>Status</label>
                                                                            <select name="user_status" class="select" required>
                                                                                <option value="1" <?= $user_row['userStatus'] == 1 ? 'selected' : ''; ?>>Active</option>
                                                                                <option value="0" <?= $user_row['userStatus'] == 0 ? 'selected' : ''; ?>>InActive</option>
                                                                            </select>
                                                                        </div>
                                                                    </div> -->
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" name="updateUserBTN" class="btn btn-primary">Update</button>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add User Modal -->
                <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                            </div>
                            <form action="" method="POST" id="user-form" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <!-- Add user form -->
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>First Name</label>
                                                        <input type="text" name="first_name" oninput="capitalizeWords(this)" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Last Name</label>
                                                        <input type="text" name="last_name" oninput="capitalizeWords(this)" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" name="phone_number" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="text" name="email" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Role</label>
                                                        <select class="select" name="role" required>
                                                            <option value="" disabled selected>Select</option>
                                                            <option>Admin</option>
                                                            <option>Technician</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <label>Password</label>
                                                        <div class="pass-group">
                                                            <input type="text" name="password" class=" pass-input" required>
                                                            <span class="fas toggle-password fa-eye-slash"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label> User Image</label>
                                                        <div class="image-upload">
                                                            <input type="file" name="image">
                                                            <div class="image-uploads">
                                                                <img src="assets/img/icons/upload.svg" alt="img">
                                                                <h4>Drag and drop a file to upload</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="addUserBTN" class="btn btn-submit me-2">Submit</button>
                                    <button type="reset" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
                <!-- End of Add User Modal -->

            </div>
        </div>
    </div>

    <script>
        // Function to capitalize
        function capitalizeWords(input) {
            if (typeof input.value !== 'string' || input.value.length === 0) return;
            input.value = input.value.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        }

        // Form Validation
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if ((name === 'first_name' || name === 'last_name') && !/^[A-Za-z]+$/.test(value)) {
                return 'Name fields should contain letters only.';
            }

            if (name === 'phone_number' && !/^[0-9]{7,15}$/.test(value)) {
                return 'Please enter a valid Phone number.';
            }

            if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (name === 'role' && value === '') {
                return 'Please select a role.';
            }

            if (name === 'password') {
                const isValid = (
                    value.length >= 4
                    // &&
                    // /[A-Z]/.test(value) &&
                    // /[a-z]/.test(value) &&
                    // /[0-9]/.test(value)
                    // && /[!@#$%^&*(),.?":{}|<>]/.test(value) 
                );
                if (!isValid) {
                    return 'Password must be at least 4 characters long';
                }
            }

            return true;
        }

        document.getElementById("user-form").addEventListener("submit", function(event) {
            var inputs = this.querySelectorAll('input, select');
            for (let input of inputs) {
                const result = validateInput(input);
                if (result !== true) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        text: result
                    });
                    input.focus();
                    return;
                }
            }
        });

        // Function to confirm user deletion 
        function toggleUserStatus(userId, isActive) {
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: isActive ? "Activate this user?" : "Deactivate this user?",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'user_status.php?id=' + userId;
                } else {
                    // revert checkbox state if cancelled
                    document.getElementById("user" + userId).checked = !isActive;
                }
            });
        }


        // Trigger SweetAlert messages after redirect
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');

            if (msg === 'deactivated') {
                Swal.fire({
                    icon: 'success',
                    title: 'Deactivated!',
                    text: 'User has been deactivated successfully.',
                    timer: 5000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (msg === 'activated') {
                Swal.fire({
                    icon: 'success',
                    title: 'Activated!',
                    text: 'User has been activated successfully.',
                    timer: 5000,
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (msg === 'notfound') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'User not found.',
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (msg === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Action failed.',
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            if (msg === 'self_delete') {
                Swal.fire({
                    icon: 'error',
                    title: 'Not Allowed!',
                    text: 'You cannot deactivate your own account.',
                    showConfirmButton: true
                }).then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
        });
    </script>

    <script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
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
    <!-- Table  -->
    <script>
        $(document).ready(function() {
            if ($("#usersTable").length > 0) {
                if (!$.fn.DataTable.isDataTable("#usersTable")) {
                    $("#usersTable").DataTable({
                        destroy: true,
                        bFilter: true,
                        sDom: "fBtlpi",
                        pagingType: "numbers",
                        ordering: true,
                        language: {
                            search: " ",
                            sLengthMenu: "_MENU_",
                            searchPlaceholder: "Search...",
                            info: "_START_ - _END_ of _TOTAL_ items"
                        },
                        initComplete: function(settings, json) {
                            $(".dataTables_filter").appendTo("#tableSearch");
                            $(".dataTables_filter").appendTo(".search-input");
                        }
                    });
                }
            }

            // Image preview functionality (unchanged)
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $("#blah").attr("src", e.target.result);
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#imgInp").change(function() {
                readURL(this);
            });
        });
    </script>
</body>

</html>
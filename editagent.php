<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid agent ID";
    exit;
}
// Get agent id
$agent_id = $_GET['id'];

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Handle agent update
if (isset($_POST['updateAgentBTN'])) {
    $agentId = $_POST['agentId'];
    $agent_name = trim($_POST['agent_name']);
    $agent_phone = trim($_POST['agent_phone']);
    $agent_email = trim($_POST['agent_email']);
    $agent_status = $_POST['agent_status'];

    // Get current agent data to compare for changes
    $current_data_query = "SELECT `agentName`, `agentEmail`, `agentPhone`, `agentStatus` 
                                    FROM agents WHERE agentId = ?";
    $current_data_stmt = $conn->prepare($current_data_query);
    $current_data_stmt->bind_param("i", $agentId);
    $current_data_stmt->execute();
    $current_result = $current_data_stmt->get_result();
    $current_data = $current_result->fetch_assoc();

    // Check if no changes were made
    if (
        $current_data &&
        $current_data['agentName'] == $agent_name &&
        $current_data['agentPhone'] == $agent_phone &&
        $current_data['agentEmail'] == $agent_email &&
        $current_data['agentStatus'] == $agent_status
    ) {

        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'No changes were made to the agent.',
                        icon: 'info',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = 'agentlist.php';
                    });
                });
             </script>";
    } else {
        // Check if email already exists for another agent
        $check_email_query = "SELECT agentId FROM agents WHERE agentEmail = ? AND agentId != ?";
        $check_email_stmt = $conn->prepare($check_email_query);
        $check_email_stmt->bind_param("si", $agent_email, $agentId);
        $check_email_stmt->execute();
        $email_result = $check_email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            text: 'Email address already exists for another agent.',
                            icon: 'error',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(function() {
                            window.location.href = 'editagent.php?id=$agentId';
                        });
                    });
                 </script>";
        } else {
            // Check if agentname already exists for another agent
            $check_agentname_query = "SELECT agentId FROM agents WHERE agentName = ? AND agentId != ?";
            $check_agentname_stmt = $conn->prepare($check_agentname_query);
            $check_agentname_stmt->bind_param("si", $agentName, $agentId);
            $check_agentname_stmt->execute();
            $agentname_result = $check_agentname_stmt->get_result();

            if ($agentname_result->num_rows > 0) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function () {
                            Swal.fire({
                                text: 'Username already exists for another agent.',
                                icon: 'error',
                                timer: 5000,
                                timerProgressBar: true
                            }).then(function() {
                                window.location.href = 'editagent.php?id=$agentId';
                            });
                        });
                     </script>";
            } else {
                // Check if phone number already exists for another agent
                $check_phone_query = "SELECT agentId FROM agents WHERE agentPhone = ? AND agentId != ?";
                $check_phone_stmt = $conn->prepare($check_phone_query);
                $check_phone_stmt->bind_param("si", $agent_phone, $agentId);
                $check_phone_stmt->execute();
                $phone_result = $check_phone_stmt->get_result();

                if ($phone_result->num_rows > 0) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    text: 'Phone number already exists for another agent.',
                                    icon: 'error',
                                    timer: 5000,
                                    timerProgressBar: true
                                }).then(function() {
                                    window.location.href = 'editagent.php?id=$agentId';
                                });
                            });
                         </script>";
                } else {
                    // Proceed with the update
                    $update_agent_query = "UPDATE agents 
                                                SET agentName=?, agentPhone=?, agentEmail=?, agentStatus=?, updated_at=? 
                                                WHERE agentId=?";
                    $update_agent_stmt = $conn->prepare($update_agent_query);
                    $update_agent_stmt->bind_param("sssssi", $agent_name, $agent_phone, $agent_email, $agent_status, $current_time, $agentId);

                    if ($update_agent_stmt->execute()) {
                        if ($update_agent_stmt->affected_rows > 0) {
                            echo "<script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            text: 'Agent updated successfully.',
                                            icon: 'success',
                                            timer: 5000,
                                            timerProgressBar: true
                                        }).then(function() {
                                            window.location.href = 'agentlist.php';
                                        });
                                    });
                                  </script>";
                        } else {
                            echo "<script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            text: 'No changes were made to the agent.',
                                            icon: 'info',
                                            timer: 5000,
                                            timerProgressBar: true
                                        }).then(function() {
                                            window.location.href = 'agentlist.php';
                                        });
                                    });
                                  </script>";
                        }
                    } else {
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        text: 'Error updating agent: " . $conn->error . "',
                                        icon: 'error',
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
    <title>Sonak Inventory | Edit Agent </title>

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
                                <li><a href="agentlist.php" class="active">Agent List</a></li>
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
                        <h4>Agent Management</h4>
                        <h6>Edit/Update Agent</h6>
                    </div>
                    <div class="page-btn">
                        <a href="agentlist.php" class="btn btn-added"><img src="assets/img/icons/card-list.svg" alt="image">&nbsp; Agents List</a>
                    </div>

                </div>

                <!-- Update Agent Form -->
                <div class="card">
                    <div class="card-body">
                        <?php
                        // Fetch agent data
                        $agent_stmt = $conn->prepare("SELECT * FROM agents WHERE agentId = ?");
                        $agent_stmt->bind_param("i", $agent_id);
                        $agent_stmt->execute();
                        $agent_result = $agent_stmt->get_result();
                        $agent_row = $agent_result->fetch_assoc();
                        ?>
                        <form action="" method="POST" id="update-agent-form" enctype="multipart/form-data">
                            <div class="row">
                                <input type="hidden" name="agentId" value="<?= $agent_id; ?>">

                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Agent Name</label>
                                        <input type="text" name="agent_name" value="<?= $agent_row['agentName']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="text" name="agent_email" value="<?= $agent_row['agentEmail']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Mobile</label>
                                        <input type="text" name="agent_phone" value="<?= $agent_row['agentPhone']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="agent_status" class="form-control" required>
                                            <option value="1" <?= $agent_row['agentStatus'] == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?= $agent_row['agentStatus'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button type="submit" name="updateAgentBTN" class="btn btn-submit me-2">Submit</button>
                                    <button type="reset" class="btn btn-cancel">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /Update Agent Form -->

            </div>
        </div>
    </div>

    <script>
        // Form inputs Validation
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if (name === 'agent_phone' && !/^[0-9]{10,12}$/.test(value)) {
                return 'Phone number must contain only numbers and atleast 10 to 12 digits.';
            }

            if (name === 'agent_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (name === 'agent_name' && value === '') {
                return 'Agent name must be filled.';
            }

            return true;
        }

        document.getElementById("update-agent-form").addEventListener("submit", function(event) {
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
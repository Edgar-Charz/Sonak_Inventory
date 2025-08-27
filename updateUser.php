<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if update user button was clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateUserBTN'])) {
    // Sanitize and validate inputs
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $name = trim($_POST['username']);
    $phone = trim($_POST['user_phone']);
    $email = filter_var(trim($_POST['user_email']), FILTER_VALIDATE_EMAIL);
    $role = trim($_POST['user_role']);
    $status = filter_var($_POST['user_status'], FILTER_VALIDATE_INT);

    // Validate required fields and phone number
    if (!$user_id || !$name || !$phone || !$email || !$role || !in_array($status, [0, 1])) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'Invalid input data. Please fill all fields correctly.',
                        icon: 'error',
                        timer: 6000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
        exit;
    }

    // Validate phone number (starts with 0, 10 digits)
    if (!preg_match('/^0\d{9}$/', $phone)) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'Phone number must start with 0 and be exactly 10 digits long.',
                        icon: 'error',
                        timer: 6000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
        exit;
    }

    // Get current user data
    $current_user_stmt = $conn->prepare("SELECT username, userPhone, userEmail, userRole, userStatus FROM users WHERE userId = ?");
    $current_user_stmt->bind_param("i", $user_id);
    $current_user_stmt->execute();
    $current_user_result = $current_user_stmt->get_result();
    $current_user_data = $current_user_result->fetch_assoc();
    $current_user_stmt->close();

    // Check if user exists
    if (!$current_user_data) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'User not found.',
                        icon: 'error',
                        timer: 6000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
        exit;
    }

    // Check if no changes were made
    if (
        $current_user_data['username'] === $name &&
        $current_user_data['userPhone'] === $phone &&
        $current_user_data['userEmail'] === $email &&
        $current_user_data['userRole'] === $role &&
        $current_user_data['userStatus'] == $status
    ) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'No changes were made to the user.',
                        icon: 'info',
                        timer: 6000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
        exit;
    }

    // Check if user email already exists for another user
    $check_user_stmt = $conn->prepare("SELECT userId FROM users WHERE userEmail = ? AND userId != ?");
    $check_user_stmt->bind_param("si", $email, $user_id);
    $check_user_stmt->execute();
    $result = $check_user_stmt->get_result();
    $check_user_stmt->close();

    if ($result->num_rows > 0) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'User with this email already exists.',
                        icon: 'error',
                        timer: 6000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
        exit;
    }

    // Update user data
    $update_stmt = $conn->prepare("UPDATE users SET username = ?, userPhone = ?, userEmail = ?, userRole = ?, userStatus = ?, updated_at = ? WHERE userId = ?");
    $update_stmt->bind_param("ssssisi", $name, $phone, $email, $role, $status, $current_time, $user_id);

    if ($update_stmt->execute()) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'User updated successfully.',
                        icon: 'success',
                        timer: 6000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
    } else {
        $error = addslashes($conn->error);
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        text: 'Error updating user: $error',
                        icon: 'error',
                        timer: 6000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.href = 'userlist.php';
                    });
                });
             </script>";
    }
    $update_stmt->close();
} else {
    // Redirect if accessed directly without POST
    header("Location: userlist.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sonak Inventory</title>
</head>

<body>


    <!-- View User Modal -->
    <div class="modal fade" id="viewUser<?= $user_id; ?>" tabindex="-1" aria-labelledby="viewUserLabel<?= $user_id; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                <label>UserName</label>
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
                                <p class="form-control"><?= $user_row['userStatus'] == 1 ? 'ACTIVE' : 'INACTIVE'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /View User Modal -->

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUser<?= $user_id; ?>" tabindex="-1" aria-labelledby="editUserLabel<?= $user_id; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="" method="POST" id="update-user-form">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?= $user_id; ?>">

                        <div class="row">
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label>UserName</label>
                                    <input type="text" name="username" class="form-control" value="<?= $user_row['username']; ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="user_phone" class="form-control" value="<?= $user_row['userPhone']; ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="user_email" class="form-control" value="<?= $user_row['userEmail']; ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label>Role</label>
                                    <input type="text" name="user_role" class="form-control" value="<?= $user_row['userRole']; ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="user_status" class="form-control" required>
                                        <option value="1" <?= $user_row['userStatus'] == 1 ? 'selected' : ''; ?>>ACTIVE</option>
                                        <option value="0" <?= $user_row['userStatus'] == 0 ? 'selected' : ''; ?>>INACTIVE</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="updateUserBTN" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- End Edit User Modal -->

    <td>
        <!-- View Button -->
        <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#viewUser<?= $user_id; ?>">
            <img src="assets/img/icons/eye.svg" alt="View">
        </button>

        <!-- Edit Button -->
        <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#editUser<?= $user_id; ?>">
            <img src="assets/img/icons/edit.svg" alt="Edit">
        </button>

        <!-- Delete Button -->
        <button class="btn">
            <img src="assets/img/icons/delete.svg" alt="Delete">
        </button>
    </td>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script>
        // Form Validation
        function validateInput(input) {
            var name = input.getAttribute('name');
            var value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if ((name === 'username') && !/^[A-Za-z]+$/.test(value)) {
                return 'Username should contain letters only.';
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
                const result = validateInput(input);
                if (result !== true) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        text: result,
                        position: 'top-end'
                    });
                    input.focus();
                    return;
                }
            }
        });
    </script>
</body>

</html>
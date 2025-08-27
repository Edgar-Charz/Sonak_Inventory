<?php
session_start();
include 'includes/db_connection.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if the form is submitted
if (isset($_POST['signupBTN'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = $first_name . ' ' . $last_name;
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($email) || empty($password)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    text: 'All fields are required!'
                });
            });
        </script>";
    } else {
        $check_stmt = $conn->prepare("SELECT userEmail FROM users WHERE userEmail = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'error',
                        text: 'Email already exists!'
                    }).then(function(){
                    window.location.href = 'signup.php';
                });
                });
            </script>";
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO `users`(`username`, `userPhone`, `userEmail`, `userRole`, `userPassword`, `created_at`, `updated_at`)  
                                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss",$username, $phone_number, $email,  $role,  $password, $current_time, $current_time);

            if ($stmt->execute()) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            icon: 'success',
                            text: 'Account created successfully! Redirecting to login...',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = 'signin.php';
                        });
                    });
                </script>";
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            icon: 'error',
                            text: 'Error creating account. Please try again.'
                        }).then(function(){
                           window.location.href = 'signup.php';
                       });
                    });
                </script>";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Sign Up</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/sweetalert/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .progress {
            height: 10px;
            margin-bottom: 20px;
        }

        .progress-bar {
            transition: width 0.3s ease-in-out;
        }

        .btn-navigation {
            margin: 10px 5px;
        }
    </style>
</head>

<body class="account-page">
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper">
                <div class="login-content">
                    <div class="login-userset">
                        <div class="login-box">
                            <div class="login-logo">
                                <img src="assets/img/logo.png" alt="img">
                            </div>
                            <div class="login-userheading">
                                <h3>Create an Account</h3>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 33.33%; background-color: orange;" id="progress-bar"></div>
                            </div>

                            <!-- User registration form -->
                            <form action="signup.php" method="POST" id="signup-form">
                                <!-- Step 1: Basic Info -->
                                <div class="form-step active" id="step-1">
                                    <div class="form-login">
                                        <label>First Name</label>
                                        <div class="form-addons">
                                            <input type="text" name="first_name" placeholder="Enter your first name" oninput="capitalizeFirstLetter(this)" required>
                                            <img src="assets/img/icons/users1.svg" alt="img">
                                        </div>
                                    </div>
                                    <div class="form-login">
                                        <label>Last Name</label>
                                        <div class="form-addons">
                                            <input type="text" name="last_name" placeholder="Enter your last name" oninput="capitalizeFirstLetter(this)" required>
                                            <img src="assets/img/icons/users1.svg" alt="img">
                                        </div>
                                    </div>
                                    <div class="form-login d-flex justify-content-end">
                                        <button type="button" class="btn btn-login btn-navigation" onclick="nextStep(2)">Next</button>
                                    </div>
                                </div>

                                <!-- Step 2: Contact Details -->
                                <div class="form-step" id="step-2">
                                    <div class="form-login">
                                        <label>Phone Number</label>
                                        <div class="form-addons">
                                            <input type="text" name="phone_number" placeholder="Enter your phone number" required>
                                            <img src="assets/img/icons/telephone.svg" alt="img">
                                        </div>
                                    </div>
                                    <div class="form-login">
                                        <label>Email</label>
                                        <div class="form-addons">
                                            <input type="email" name="email" placeholder="Enter your email" required>
                                            <img src="assets/img/icons/mail.svg" alt="img">
                                        </div>
                                    </div>
                                    <div class="form-login d-flex justify-content-between gap-2">
                                        <button type="button" class="btn btn-login btn-navigation flex-fill" onclick="prevStep(1)">Previous</button>
                                        <button type="button" class="btn btn-login btn-navigation flex-fill" onclick="nextStep(3)">Next</button>
                                    </div>
                                </div>

                                <!-- Step 3: Role & Password -->
                                <div class="form-step" id="step-3">
                                    <div class="form-login">
                                        <label>Role</label>
                                        <select name="role" class="form-control" required>
                                            <option value="" disabled selected>Select Role</option>
                                            <option>Admin</option>
                                            <option>Storekeeper</option>
                                        </select>
                                    </div>
                                    <div class="form-login">
                                        <label>Password</label>
                                        <div class="pass-group">
                                            <input type="password" name="password" class="pass-input" placeholder="Create a password" required>
                                            <!-- <span class="fas toggle-password fa-eye-slash"></span> -->
                                        </div>
                                    </div>
                                    <div class="form-login d-flex justify-content-between gap-2">
                                        <button type="button" class="btn btn-login btn-navigation flex-fill" onclick="prevStep(2)">Previous</button>
                                        <button type="submit" class="btn btn-login btn-navigation flex-fill" name="signupBTN">Sign Up</button>
                                    </div>
                                </div>
                            </form>
                            <div class="signinform text-center">
                                <h4>Already a user? <a href="signin.php" class="hover-a">Sign In</a></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script>
        function capitalizeFirstLetter(input) {
            if (typeof input.value !== 'string' || input.value.length === 0) return;
            input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
        }

        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(div => div.classList.remove('active'));
            document.getElementById(`step-${step}`).classList.add('active');
            document.getElementById('progress-bar').style.width = `${step * 33.33}%`;
        }

        function validateInput(input) {
            const name = input.getAttribute('name');
            const value = input.value.trim();

            if (!input.hasAttribute('required') && value === '') return true;

            if (value === '') {
                return 'Please fill in all fields.';
            }

            if ((name === 'first_name' || name === 'last_name') && !/^[A-Za-z]+$/.test(value)) {
                return 'Name fields should contain letters only.';
            }

            if (name === 'phone_number' && !/^0[0-9]{9}$/.test(value)) {
                return 'Phone number must start with 0 and contain 10 digits.';
            }

            if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Please enter a valid email address.';
            }

            if (name === 'role' && value === '') {
                return 'Please select a role.';
            }

            if (name === 'password') {
                const isValid = (
                    value.length >= 8 &&
                    /[A-Z]/.test(value) &&
                    /[a-z]/.test(value) &&
                    /[0-9]/.test(value)
                    // && /[!@#$%^&*(),.?":{}|<>]/.test(value) // optional special character check
                );
                if (!isValid) {
                    return 'Password must be at least 8 characters long and include uppercase, lowercase letters, and numbers.';
                }
            }

            return true;
        }

        function nextStep(step) {
            const currentStep = document.querySelector('.form-step.active');
            const inputs = currentStep.querySelectorAll('input, select');
            let valid = true;

            for (let input of inputs) {
                const result = validateInput(input);
                if (result !== true) {
                    Swal.fire({
                        icon: 'error',
                        text: result
                    });
                    input.focus();
                    valid = false;
                    break;
                }
            }

            if (valid) showStep(step);
        }

        function prevStep(step) {
            showStep(step);
        }

        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);

            document.getElementById("signup-form").addEventListener("submit", function(event) {
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
        });
    </script>


</body>

</html>
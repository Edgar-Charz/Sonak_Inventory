<?php
include 'includes/db_connection.php';

if (isset($_POST['restPasswordBTN'])) {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($phone)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE userEmail = ? AND userPhone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        text: 'A reset password link has been sent to your email.',
                        timer: 3000
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
                        text: 'Email and phone combination not found.',
                        timer: 3000
                    }).then(function() {
                        window.location.href = 'forgetpassword.php'; 
                    });                   
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    text: 'Please enter a valid email and phone number.',
                    timer: 3000
                }).then(function() {
                    window.location.href = 'forgetpassword.php';
                });
            });
        </script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login - Sonak Inventory</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

    <link rel="stylesheet" href="assets/plugins/sweetalert/sweetalert2.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="account-page">

    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper">
                <div class="login-content">
                    <div class="login-box">
                        <div class="login-userset ">
                            <div class="login-logo">
                                <img src="assets/img/logo.png" alt="img">
                            </div>
                            <div class="login-userheading">
                                <h3>Forgot password?</h3>
                                <h4>Don’t worry! it happens. Please enter the address <br>
                                    associated with your account.</h4>
                            </div>
                            <div class="form-login">
                                <form method="POST" action="">
                                    <div class="form-login">
                                        <label>Email</label>
                                        <div class="form-addons">
                                            <input type="email" name="email" placeholder="Enter your email address" required>
                                            <img src="assets/img/icons/mail.svg" alt="img">
                                        </div>
                                    </div>
                                    <div class="form-login">
                                        <label>Phone Number</label>
                                        <div class="form-addons">
                                            <input type="text" name="phone" placeholder="Enter your phone number" required>
                                            <img src="assets/img/icons/telephone.svg" alt="img">
                                        </div>
                                    </div>
                                    <div class="form-login">
                                        <div class="alreadyuser">
                                            <h4><a href="signin.php" class="hover-a">Login instead?</a></h4>
                                        </div>
                                    </div>
                                    <div class="form-login">
                                        <button class="btn btn-login" name="restPasswordBTN">Reset Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="login-img">
                    <img src="assets/img/login.jpg" alt="img">
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>

    <script src="assets/js/script.js"></script>
</body>

</html>
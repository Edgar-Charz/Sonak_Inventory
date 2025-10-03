<?php
session_start();
include 'includes/db_connection.php';

// Prevent cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if the user is logged in
if (isset($_GET['reason'])) {
    if ($_GET['reason'] === 'timeout') {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Session Expired',
                    text: 'You were logged out due to inactivity.',
                    confirmButtonText: 'Login Again'
                }).then(() => {
                    const url = new URL(window.location);
                    url.searchParams.delete('reason');
                    window.history.replaceState({}, document.title, url);
                });
            });
        </script>";
    } elseif ($_GET['reason'] === 'signout') {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Signed Out',
                    text: 'You have successfully logged out.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    const url = new URL(window.location);
                    url.searchParams.delete('reason');
                    window.history.replaceState({}, document.title, url);
                });
            });
        </script>";
    } elseif ($_GET['reason'] === 'notloggedin') {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Access Denied',
                    text: 'You have not logged in.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    const url = new URL(window.location);
                    url.searchParams.delete('reason');
                    window.history.replaceState({}, document.title, url);
                });
            });
        </script>";
    }
}

// Check if the login buttton was clicked
if (isset($_POST["loginBTN"])) {
    // $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check email
    $email_stmt = $conn->prepare("SELECT * FROM users WHERE userEmail = ?");
    $email_stmt->bind_param("s", $email);
    $email_stmt->execute();
    $email_result = $email_stmt->get_result();

    // Check username
    $username_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $username_stmt->bind_param("s", $username);
    $username_stmt->execute();
    $username_result = $username_stmt->get_result();

    //  Check if the email exists
    if ($username_result->num_rows === 1) {
        $user = $username_result->fetch_assoc();

        // Check if the user is active
        if ($user['userStatus'] == 1) {

            // Verify the password
            if (password_verify($password, $user['userPassword'])) {

                // Set session variables
                $_SESSION['id'] = $user['userId'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['userRole'] = $user['userRole'];
                $_SESSION['profilePicture'] = $user['userPhoto'];
                $_SESSION['login_success'] = true;

                echo "<script>
                            document.addEventListener('DOMContentLoaded', function () {
                            var loginBtn = document.querySelector('button[name=\"loginBTN\"]');
                            if (loginBtn) {
                                loginBtn.textContent = 'Redirecting...';
                                loginBtn.disabled = true;
                            }

                            setTimeout(function() {
                                window.location.href = 'index.php';
                            }, 50); 
                        });
                    </script>";
            } else {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Incorrect password!'
                    }).then(function(){
                      window.location.href = 'signin.php';
                    });
                });
            </script>";
            }
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Your account is inactive. Please contact support.'
                    }).then(function(){
                        window.location.href = 'signin.php';
                    });
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'warning',
                    title: 'Error!',
                    text: 'No account found with that email!'
                }).then(function(){
                    window.location.href = 'signin.php';
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
                    <div class="login-userset">
                        <div class="login-box">
                            <div class="login-logo">
                                <img src="assets/img/logo.png" alt="img">
                            </div>
                            <div class="login-userheading">
                                <h3>Sign In</h3>
                            </div>
                            <form action="" method="POST">
                                <!-- <div class="form-login">
                                    <label>Email</label>
                                    <div class="form-addons">
                                        <input type="email" name="email" placeholder="Enter your email address" required>
                                        <img src="assets/img/icons/mail.svg" alt="img">
                                    </div>
                                </div> -->
                                <div class="form-login">
                                    <label>UserName</label>
                                    <div class="form-addons">
                                        <input type="text" name="username" placeholder="Enter your username" required>
                                        <img src="assets/img/icons/users1.svg" alt="img">
                                    </div>
                                </div>
                                <div class="form-login">
                                    <label>Password</label>
                                    <div class="pass-group">
                                        <input type="password" name="password" class="pass-input" placeholder="Enter your password" required>
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                </div>
                                <div class="form-login">
                                    <div class="alreadyuser">
                                        <h4><a href="forgetpassword.php" class="hover-a">Forgot Password?</a></h4>
                                    </div>
                                </div>
                                <div class="form-login">
                                    <button type="submit" class="btn btn-login" name="loginBTN">Sign In</button>
                                </div>
                                <!-- <div class="signinform text-center">
                                    <h4>Don’t have an account? <a href="signup.php" class="hover-a">Sign Up</a></h4>
                                </div> -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        <?php if (!isset($_SESSION['username'])): ?>
            window.addEventListener('pageshow', function(event) {
                if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                    // Page was loaded from cache (back button)
                    window.location.href = 'signin.php?reason=notloggedin';
                }
            });
        <?php endif; ?>
    </script>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>
<?php
session_start();
include 'includes/db_connection.php';

if (isset($_GET['reason']) && $_GET['reason'] === 'timeout') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            const alertBox = document.createElement('div');
            alertBox.innerText = 'You have been logged out due to inactivity!';
            alertBox.style.position = 'fixed';
            alertBox.style.top = '20px';
            alertBox.style.right = '20px';
            alertBox.style.background = '#f44336';
            alertBox.style.color = '#fff';
            alertBox.style.padding = '10px 20px';
            alertBox.style.borderRadius = '5px';
            alertBox.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
            alertBox.style.zIndex = '9999';
            document.body.appendChild(alertBox);

            setTimeout(() => alertBox.remove(), 4000);
        });
    </script>";
}

// Check if the login buttton was clicked
if (isset($_POST["loginBTN"])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $login_stmt = $conn->prepare("SELECT * FROM users WHERE userEmail = ?");
    $login_stmt->bind_param("s", $email);
    $login_stmt->execute();
    $result = $login_stmt->get_result();

    //  Check if the email exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Check if the user is active
        if ($user['userStatus'] == 1) {
            // Verify the password
            if (password_verify($password, $user['userPassword'])) {
                $_SESSION['id'] = $user['userId'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['userRole'] = $user['userRole'];
                $_SESSION['profilePicture'] = $user['userPhoto'] ?? 'assets/img/profiles/avator1.jpg';
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
                }, 1000); 
            });
        </script>";
            } else {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
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
                        title: 'Error!',
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
    <meta name="robots" content="noindex, nofollow">
    <title>Login - Pos admin template</title>

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
                                <div class="form-login">
                                    <label>Email</label>
                                    <div class="form-addons">
                                        <input type="email" name="email" placeholder="Enter your email address" required>
                                        <img src="assets/img/icons/mail.svg" alt="img">
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
                                <div class="signinform text-center">
                                    <h4>Don’t have an account? <a href="signup.php" class="hover-a">Sign Up</a></h4>
                                </div>
                            </form>
                        </div>
                    </div>
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
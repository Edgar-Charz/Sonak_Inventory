<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

// Session timeout logic (10 minutes = 600 seconds)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: signin.php?reason=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// SweetAlert welcome message
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
    unset($_SESSION['login_success']);
    $username = ucwords(strtolower($_SESSION['username']));
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Welcome back!',
                text: '" . $username . "',
                confirmButtonText: 'OK',
                timer: 5000,
                timerProgressBar: true
            });
        });
    </script>";
}
?>
<script>
    // Config (15 minutes total, show warning 5 minutes before)
    const timeoutDuration = 900000;   
    const warningTime = 300000;       
    const logoutUrl = 'signout.php?reason=timeout';

    let countdownShown = false;
    let warningTimer;

    function showWarning() {
        if (!countdownShown) {
            countdownShown = true;
            let secondsLeft = (timeoutDuration - warningTime) / 1000; 

            const alertBox = document.createElement('div');
            alertBox.id = 'session-alert';
            alertBox.style.position = 'fixed';
            alertBox.style.top = '20px';
            alertBox.style.right = '20px';
            alertBox.style.padding = '15px';
            alertBox.style.backgroundColor = '#f44336';
            alertBox.style.color = '#fff';
            alertBox.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.3)';
            alertBox.style.zIndex = '9999';
            alertBox.style.borderRadius = '5px';
            alertBox.innerText = `Session will expire in ${secondsLeft} seconds...`;
            document.body.appendChild(alertBox);

            const countdown = setInterval(() => {
                secondsLeft--;
                alertBox.innerText = `Session will expire in ${secondsLeft} seconds...`;

                if (secondsLeft <= 0) {
                    clearInterval(countdown);
                    window.location.href = logoutUrl;
                }
            }, 1000);
        }
    }

    function resetTimer() {
        clearTimeout(warningTimer);
        countdownShown = false;

        const existingAlert = document.querySelector('#session-alert');
        if (existingAlert) existingAlert.remove();

        warningTimer = setTimeout(showWarning, timeoutDuration - warningTime);
    }

    // Reset timer on user activity
    ['click', 'mousemove', 'keypress', 'scroll'].forEach(evt =>
        document.addEventListener(evt, resetTimer)
    );

    // Start the warning timer
    warningTimer = setTimeout(showWarning, timeoutDuration - warningTime);
</script>

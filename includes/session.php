<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

// Session timeout configuration (15 minutes = 900 seconds)
$timeout_duration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: signin.php?reason=timeout");
    exit();
}

$_SESSION['last_activity'] = time();

// Calculate remaining time for JavaScript (in milliseconds)
$remaining_time = $timeout_duration * 1000;
$warning_time = 30000; // 30 seconds before timeout

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
    // Synchronized timeout configuration
    let timeoutDuration = <?php echo $remaining_time; ?>;
    const warningTime = <?php echo $warning_time; ?>;
    const logoutUrl = 'signout.php?reason=timeout';

    let countdownShown = false;
    let warningTimer;
    let logoutTimer;

    // Function to show warning
    function showWarning() {
        if (!countdownShown) {
            countdownShown = true;
            let secondsLeft = warningTime / 1000;
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

    // Function to start timers
    function startTimers() {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        countdownShown = false;
        const alertBox = document.getElementById('session-alert');
        if (alertBox) alertBox.remove();

        warningTimer = setTimeout(showWarning, timeoutDuration - warningTime);
        logoutTimer = setTimeout(() => {
            window.location.href = logoutUrl;
        }, timeoutDuration);
    }

    // Reset timers on user activity
    ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, () => {
            startTimers();
        });
    });

    // Start initial timers
    startTimers();
</script>
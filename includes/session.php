<?php
session_start();

// Clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin.php?reason=notloggedin");
    exit();
}

// Session timeout
$timeout_duration = 600;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: signin.php?reason=timeout");
    exit();
}

// Update last activity on page load
$_SESSION['last_activity'] = time();

// Remaining time (full session initially)
$remaining_time = $timeout_duration * 1000;
$warning_time = 30000;

// SweetAlert welcome message
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
    unset($_SESSION['login_success']);
    $username = ucwords(strtolower($_SESSION['username']));
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
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
    let timeoutDuration = <?php echo $remaining_time; ?>;
    const warningTime = <?php echo $warning_time; ?>;
    const logoutUrl = 'signout.php?reason=timeout';
    const updateUrl = 'update_session.php';

    let countdownShown = false;
    let warningTimer;
    let logoutTimer;

    // Show warning popup
    function showWarning() {
        if (!countdownShown) {
            countdownShown = true;
            let secondsLeft = Math.floor(warningTime / 1000);
            const alertBox = document.createElement('div');
            alertBox.id = 'session-alert';
            alertBox.style.position = 'fixed';
            alertBox.style.top = '10px';
            alertBox.style.right = '5%';
            alertBox.style.padding = '15px';
            alertBox.style.backgroundColor = '#097f2dff';
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

    // Start timers
    function startTimers() {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        countdownShown = false;
        const alertBox = document.getElementById('session-alert');
        if (alertBox) alertBox.remove();

        if (timeoutDuration <= warningTime) {
            showWarning();
            logoutTimer = setTimeout(() => {
                window.location.href = logoutUrl;
            }, timeoutDuration);
        } else {
            warningTimer = setTimeout(showWarning, timeoutDuration - warningTime);
            logoutTimer = setTimeout(() => {
                window.location.href = logoutUrl;
            }, timeoutDuration);
        }
    }

    // AJAX ping to update last activity and get remaining time
    function pingServer() {
        fetch(updateUrl, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    timeoutDuration = data.remainingTime; // Update with server-calculated remaining time
                    startTimers(); // Reset timers with new duration
                } else {
                    window.location.href = logoutUrl; // Redirect if session invalid
                }
            })
            .catch(err => console.error('Activity update failed:', err));
    }

    // Reset timers + ping server on user activity
    ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, () => {
            pingServer();
        });
    });

    // Start initial timers
    startTimers();
</script>
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
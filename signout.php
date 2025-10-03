<?php
session_start();

// Destroy all session data
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 1,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// Prevent browser from caching (so back button won't show protected pages)
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// If a reason was passed
if (isset($_GET['reason']) && $_GET['reason'] === 'timeout') {
    header("Location: signin.php?reason=timeout");
} else {
    header("Location: signin.php?reason=signout");
}
exit;

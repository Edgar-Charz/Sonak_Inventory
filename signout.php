<?php
session_start();

// Destroy all session data
$_SESSION = array();
session_destroy();

// Prevent browser from caching (so back button won't show protected pages)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// If a reason was passed (like timeout), keep it. Otherwise, redirect plain.
if (isset($_GET['reason']) && $_GET['reason'] === 'timeout') {
    header("Location: signin.php?reason=timeout");
} else {
    header("Location: signin.php");
}
exit;

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();

// Calculate remaining time
$timeout_duration = 600;
$elapsed = time() - $_SESSION['last_activity'];
$remaining_time = max(0, ($timeout_duration - $elapsed)) * 1000;

echo json_encode([
    'success' => true,
    'remainingTime' => $remaining_time
]);
exit();
?>
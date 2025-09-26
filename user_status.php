<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if user ID is provided
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Prevent deleting yourself
    if ($user_id === $_SESSION['id']) {
        header("Location: userlist.php?msg=self_delete");
        exit;
    }

    // Check current status
    $status_stmt = $conn->prepare("SELECT userStatus FROM users WHERE userId = ?");
    $status_stmt->bind_param("i", $user_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 1) {
        $user = $status_result->fetch_assoc();
        $current_status = $user['userStatus'];

        // Perform action based on status
        if ($current_status == 1) {
            // User is active, deactivate
            $update_stmt = $conn->prepare("UPDATE users SET userStatus = 0, updated_at = ? WHERE userId = ?");
            $update_stmt->bind_param("si", $current_time, $user_id);
            $update_stmt->execute();

            header("Location: userlist.php?msg=deactivated");
            exit;
        } else {
            // User is inactive, activate
            $update_stmt = $conn->prepare("UPDATE users SET userStatus = 1, updated_at = ? WHERE userId = ?");
            $update_stmt->bind_param("si", $current_time, $user_id);
            $update_stmt->execute();

            header("Location: userlist.php?msg=activated");
            exit;
        }
    } else {
        // User not found
        header("Location: userlist.php?msg=notfound");
        exit;
    }
} else {
    // Invalid request
    header("Location: userlist.php?msg=error");
    exit;
}

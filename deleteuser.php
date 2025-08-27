<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Prevent deleting yourself
    if ($user_id === $_SESSION['id']) {
        header("Location: userlist.php?status=self_delete");
        exit;
    }

    $delete_user_query = $conn->prepare("DELETE FROM users WHERE userId = ?");
    $delete_user_query->bind_param("i", $user_id);

    if ($delete_user_query->execute()) {
        header("Location: userlist.php?status=success");
        exit;
    } else {
        header("Location: userlist.php?status=error");
        exit;
    }
} else {
    header("Location: userlist.php?status=error");
}
exit;

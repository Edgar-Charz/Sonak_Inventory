<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (isset($_GET['id'])) {
    $customer_id = intval($_GET['id']);

    // Deleting customer
    $delete_customer_query = $conn->prepare("DELETE FROM customers WHERE customerId = ?");
    $delete_customer_query->bind_param("i", $customer_id);

    if ($delete_customer_query->execute()) {
        header("Location: customerlist.php?status=success");
        exit;
    } else {
        header("Location: customerlist.php?status=error");
        exit;
    }
} else {
    header("Location: customerlist.php?status=error");
}
exit;

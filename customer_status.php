<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if the id parameter is set
if (isset($_GET['id'])) {
    $customer_id = intval($_GET['id']);
    $current_time = date('Y-m-d H:i:s');

    //Check current status
    $status_stmt = $conn->prepare("SELECT customerStatus FROM customers WHERE customerId = ?");
    $status_stmt->bind_param("i", $customer_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 1) {
        $customer = $status_result->fetch_assoc();
        $current_status = $customer['customerStatus'];

        // Perform action based on status
        if ($current_status == 1) {
            // Customer is active → deactivate
            $update_stmt = $conn->prepare("UPDATE customers SET customerStatus = 0, updated_at = ? WHERE customerId = ?");
            $update_stmt->bind_param("si", $current_time, $customer_id);
            $update_stmt->execute();

            header("Location: customerlist.php?status=deactivated");
            exit;
        } else {
            // Customer is inactive → activate
            $update_stmt = $conn->prepare("UPDATE customers SET customerStatus = 1, updated_at = ? WHERE customerId = ?");
            $update_stmt->bind_param("si", $current_time, $customer_id);
            $update_stmt->execute();

            header("Location: customerlist.php?status=activated");
            exit;
        }
    } else {
        // Customer not found
        header("Location: customerlist.php?status=notfound");
        exit;
    }
} else {
    // Invalid request
    header("Location: customerlist.php?status=invalid");
    exit;
}

<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if supplier ID is provided
if (isset($_GET['id'])) {
    $supplier_id = intval($_GET['id']);
    $current_time = date('Y-m-d H:i:s');

    // Check current status
    $status_stmt = $conn->prepare("SELECT supplierStatus FROM suppliers WHERE supplierId = ?");
    $status_stmt->bind_param("i", $supplier_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 1) {
        $supplier = $status_result->fetch_assoc();
        $current_status = $supplier['supplierStatus'];

        // Perform action based on status
        if ($current_status == 1) {
            // Supplier is active, deactivate
            $update_stmt = $conn->prepare("UPDATE suppliers SET supplierStatus = 0, updated_at = ? WHERE supplierId = ?");
            $update_stmt->bind_param("si", $current_time, $supplier_id);
            $update_stmt->execute();

            header("Location: supplierlist.php?status=deactivated");
            exit;  
        } else {
            // Supplier is inactive, activate
            $update_stmt = $conn->prepare("UPDATE suppliers SET supplierStatus = 1, updated_at = ? WHERE supplierId = ?");
            $update_stmt->bind_param("si", $current_time, $supplier_id);
            $update_stmt->execute();

            header("Location: supplierlist.php?status=activated");
            exit;
        }
    } else {
        // Supplier not found
        header("Location: supplierlist.php?status=notfound");
        exit;
    }
} else {
    // Invalid request
    header("Location: supplierlist.php?status=error");
    exit;
}

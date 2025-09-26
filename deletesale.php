<?php
include("includes/db_connection.php");
include("includes/session.php");

// Get user id from session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_POST["orderUId"]) && isset($_POST["deleteReason"])) {
    $order_uid = $_POST["orderUId"];
    $delete_reason = trim($_POST["deleteReason"]);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Get invoice number
        $invoice_query = $conn->prepare("SELECT orderInvoiceNumber FROM orders WHERE orderUId = ?");
        $invoice_query->bind_param("i", $order_uid);
        $invoice_query->execute();
        $invoice_result = $invoice_query->get_result();

        if ($invoice_result->num_rows == 0) {
            throw new Exception("Order not found");
        }

        $invoice_number = $invoice_result->fetch_assoc()["orderInvoiceNumber"];
        $invoice_query->close();

        // Update orders table status
        $update_order_query = $conn->prepare("UPDATE orders SET orderStatus = 3, orderDescription = ?, orderUpdatedBy = ?, updated_at = ? WHERE orderUId = ?");
        $update_order_query->bind_param("sisi", $delete_reason, $user_id, $current_time, $order_uid);

        if (!$update_order_query->execute()) {
            throw new Exception("Failed to update order status");
        }
        $update_order_query->close();

        // Update order_details table status 
        $update_details_query = $conn->prepare("UPDATE order_details SET orderDetailStatus = 3, updated_at = ? WHERE orderDetailInvoiceNumber = ?");
        $update_details_query->bind_param("ss", $current_time, $invoice_number);

        if (!$update_details_query->execute()) {
            throw new Exception("Failed to update order details status");
        }
        $update_details_query->close();

        // Commit transaction
        $conn->commit();
        header("Location: saleslist.php?status=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: saleslist.php?status=error&errorMsg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: saleslist.php?status=error");
    exit;
}

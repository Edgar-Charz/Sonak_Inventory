<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET["id"])) {
    $order_uid = $_GET["id"];

    // Get invoice number
    $invoice_number_query = $conn->prepare("SELECT invoiceNumber FROM orders WHERE orderUId = ?");
    $invoice_number_query->bind_param("i", $order_uid);
    $invoice_number_query->execute();
    $invoice_number_result = $invoice_number_query->get_result();

    if ($invoice_number_result->num_rows == 0) {
        header("Location: saleslist.php?message=error");
        exit;
    }

    $invoice_number = $invoice_number_result->fetch_assoc()["invoiceNumber"];

    $conn->begin_transaction();

    // Fetch order details to restore stock
    $details_query = $conn->prepare("SELECT productId, quantity FROM order_details WHERE invoiceNumber = ?");
    $details_query->bind_param("s", $invoice_number);
    $details_query->execute();
    $details_result = $details_query->get_result();

    // Restore stock quantities
    while ($detail = $details_result->fetch_assoc()) {
        $product_id = $detail['productId'];
        $quantity = $detail['quantity'];

        $update_stock_query = $conn->prepare("UPDATE products SET quantity = quantity + ?, updated_at = ? WHERE productId = ?");
        $update_stock_query->bind_param("isi", $quantity, $current_time, $product_id);
        $update_stock_query->execute();
        $update_stock_query->close();
    }

    // Cancel order
    $cancel_order_query = $conn->prepare("UPDATE orders SET orderStatus = 2, updatedBy = ?, updated_at = ? WHERE orderUId = ?");
    $cancel_order_query->bind_param("isi", $user_id, $current_time, $order_uid);

    if ($cancel_order_query->execute()) {
        // Commit transaction
        $conn->commit();
        header("Location: saleslist.php?message=cancelled");
        exit;
    } else {
        $conn->rollback();
        header("Location: saleslist.php?message=error&reason=Failed to update order status");
        exit;
    }
} else {
    header("Location: saleslist.php?message=error");
    exit;
}

<?php
include 'includes/db_connection.php';
include 'includes/session.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$user_id = $_SESSION['id'];
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET["id"])) {
    $order_uid = $_GET["id"];

    try {
        // Get invoice number
        $invoice_number_query = $conn->prepare("SELECT orderInvoiceNumber FROM orders WHERE orderUId = ?");
        $invoice_number_query->bind_param("i", $order_uid);
        $invoice_number_query->execute();
        $invoice_number_result = $invoice_number_query->get_result();

        if ($invoice_number_result->num_rows == 0) {
            throw new Exception("Order not found.");
        }

        $invoice_number = $invoice_number_result->fetch_assoc()["orderInvoiceNumber"];
        $invoice_number_query->close();

        // Begin transaction
        $conn->begin_transaction();

        // Restore stock quantities
        $details_query = $conn->prepare("SELECT orderDetailProductId, orderDetailQuantity FROM order_details WHERE orderDetailInvoiceNumber = ?");
        $details_query->bind_param("s", $invoice_number);
        $details_query->execute();
        $details_result = $details_query->get_result();

        while ($detail = $details_result->fetch_assoc()) {
            $product_id = $detail['orderDetailProductId'];
            $quantity = $detail['orderDetailQuantity'];

            $update_stock_query = $conn->prepare("UPDATE products SET productQuantity = productQuantity + ?, updated_at = ? WHERE productId = ?");
            $update_stock_query->bind_param("isi", $quantity, $current_time, $product_id);
            $update_stock_query->execute();
            $update_stock_query->close();
        }
        $details_query->close();

        // Cancel order
        $cancel_order_query = $conn->prepare("UPDATE orders SET orderStatus = 2, orderUpdatedBy = ?, updated_at = ? WHERE orderUId = ?");
        $cancel_order_query->bind_param("isi", $user_id, $current_time, $order_uid);
        $cancel_order_query->execute();
        $cancel_order_query->close();

        // Cancel order details
        $cancel_details_query = $conn->prepare("UPDATE order_details SET orderDetailStatus = 2, updated_at = ? WHERE orderDetailInvoiceNumber = ?");
        $cancel_details_query->bind_param("ss", $current_time, $invoice_number);
        $cancel_details_query->execute();
        $cancel_details_query->close();

        // Commit transaction
        $conn->commit();
        header("Location: saleslist.php?message=cancelled");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Order cancellation failed: " . $e->getMessage());
        $error_message = urlencode($e->getMessage());
        header("Location: saleslist.php?message=error&errorMsg=" . $error_message);
        exit;
    }
} else {
    header("Location: saleslist.php?message=error");
    exit;
}

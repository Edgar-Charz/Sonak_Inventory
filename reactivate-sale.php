<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['id'])) {
    $order_uid = $_GET['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get invoice number
        $invoice_number_query = $conn->prepare("SELECT invoiceNumber FROM orders WHERE orderUId = ?");
        $invoice_number_query->bind_param("i", $order_uid);
        $invoice_number_query->execute();
        $invoice_number_result = $invoice_number_query->get_result();

        if ($invoice_number_result->num_rows == 0) {
            throw new Exception("Order not found");
        }

        $invoice_number = $invoice_number_result->fetch_assoc()["invoiceNumber"];

        // Fetch order details with product stock
        $details_query = $conn->prepare("SELECT order_details.productId, order_details.quantity, products.productName, products.quantity AS availableQty
                                                    FROM order_details, products
                                                    WHERE order_details.productId = products.productId
                                                    AND order_details.invoiceNumber = ?
        ");
        $details_query->bind_param("s", $invoice_number);
        $details_query->execute();
        $details_result = $details_query->get_result();

        $insufficient = [];
        $details = [];

        while ($detail = $details_result->fetch_assoc()) {
            $details[] = $detail;
            $needed_qty = $detail['quantity'];
            $available_qty = $detail['availableQty'];

            if ($needed_qty > $available_qty) {
                $insufficient[] = $detail['productName'] . " (needed: $needed_qty, available: $available_qty)";
            }
        }

        if (!empty($insufficient)) {
            // Rollback because stock is not enough
            $conn->rollback();

            $msg = urlencode(implode(", ", $insufficient));
            header("Location: saleslist.php?response=insufficient&msg=$msg");
            exit;
        }

        // Deduct stock
        foreach ($details as $detail) {
            $product_id = $detail['productId'];
            $quantity = $detail['quantity'];

            $update_stock_query = $conn->prepare("
                UPDATE products 
                SET quantity = quantity - ?, updated_at = ? 
                WHERE productId = ?
            ");
            $update_stock_query->bind_param("isi", $quantity, $current_time, $product_id);

            if (!$update_stock_query->execute()) {
                throw new Exception("Stock update failed for productId: $product_id");
            }
            $update_stock_query->close();
        }

        // Reactivate order → set status back to 0 (Pending)
        $reactivate_order_query = $conn->prepare("
            UPDATE orders 
            SET orderStatus = 0, updatedBy = ?, updated_at = ? 
            WHERE orderUId = ?
        ");
        $reactivate_order_query->bind_param("isi", $user_id, $current_time, $order_uid);

        if (!$reactivate_order_query->execute()) {
            throw new Exception("Order reactivation failed");
        }

        // Commit transaction
        $conn->commit();

        header("Location: saleslist.php?response=reactivated");
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        header("Location: saleslist.php?response=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: saleslist.php?response=error");
    exit;
}

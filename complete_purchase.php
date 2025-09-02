<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'];

// Time zone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['id'])) {
    $purchase_uid = $_GET['id'];

    // Begin transaction
    $conn->begin_transaction();

    // Verify purchase exists and is pending
    $purchase_stmt = $conn->prepare("SELECT purchaseNumber 
                                     FROM purchases 
                                     WHERE purchaseUId = ? 
                                     AND purchaseStatus = 0");
    $purchase_stmt->bind_param("i", $purchase_uid);
    $purchase_stmt->execute();
    $purchase_result = $purchase_stmt->get_result();

    if ($purchase_result->num_rows == 0) {
        $conn->rollback();
        header("Location: purchaselist.php?message=error");
        exit;
    }

    $purchase_number = $purchase_result->fetch_assoc()['purchaseNumber'];

    // Fetch purchase details to update stock
    $details_stmt = $conn->prepare("SELECT productId, quantity 
                                    FROM purchase_details 
                                    WHERE purchaseNumber = ?");
    $details_stmt->bind_param("s", $purchase_number);
    $details_stmt->execute();
    $details_result = $details_stmt->get_result();

    $stock_update_ok = true;

    while ($detail = $details_result->fetch_assoc()) {
        $product_id = $detail['productId'];
        $quantity   = $detail['quantity'];

        // Add quantities to stock
        $update_stock_stmt = $conn->prepare("UPDATE products 
                                             SET quantity = quantity + ?, updated_at = ? 
                                             WHERE productId = ?");
        $update_stock_stmt->bind_param("iss", $quantity, $current_time, $product_id);

        if (!$update_stock_stmt->execute()) {
            $stock_update_ok = false;
        }
        $update_stock_stmt->close();
    }

    if ($stock_update_ok) {
        // Update purchase status to completed
        $complete_stmt = $conn->prepare("UPDATE purchases 
                                         SET purchaseStatus = 1, updatedBy = ?, updated_at = ? 
                                         WHERE purchaseUId = ?");
        $complete_stmt->bind_param("isi", $user_id, $current_time, $purchase_uid);
        $complete_stmt->execute();
        $complete_stmt->close();

        // Update all purchase_details status to completed
        $details_update_stmt = $conn->prepare("UPDATE purchase_details 
                                               SET status = 1, updated_at = ? 
                                               WHERE purchaseNumber = ?");
        $details_update_stmt->bind_param("ss", $current_time, $purchase_number);
        $details_update_stmt->execute();
        $details_update_stmt->close();

        // Commit transaction
        $conn->commit();
        header("Location: purchaselist.php?message=completed");
        exit;
    } else {
        $conn->rollback();
        header("Location: purchaselist.php?message=error");
        exit;
    }
}

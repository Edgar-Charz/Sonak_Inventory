<?php
include 'includes/db_connection.php';
include 'includes/session.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions

$user_id = $_SESSION['id'];
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['id'])) {
    $purchase_uid = $_GET['id'];

    try {
        // Verify purchase exists and is cancelled
        $purchase_stmt = $conn->prepare("SELECT purchaseNumber FROM purchases WHERE purchaseUId = ? AND purchaseStatus = 2");
        $purchase_stmt->bind_param("i", $purchase_uid);
        $purchase_stmt->execute();
        $purchase_result = $purchase_stmt->get_result();

        if ($purchase_result->num_rows == 0) {
            throw new Exception("Purchase not found or not cancelled.");
        }

        $purchase_number = $purchase_result->fetch_assoc()['purchaseNumber'];
        $purchase_stmt->close();

        // Begin transaction
        $conn->begin_transaction();

        // Update purchase to pending
        $update_purchase_stmt = $conn->prepare("UPDATE purchases 
                                                SET purchaseStatus = 0, purchaseUpdatedBy = ?, updated_at = ? 
                                                WHERE purchaseUId = ?");
        $update_purchase_stmt->bind_param("isi", $user_id, $current_time, $purchase_uid);
        $update_purchase_stmt->execute();
        $update_purchase_stmt->close();

        // Update all purchase_details to pending
        $update_details_stmt = $conn->prepare("UPDATE purchase_details 
                                               SET purchaseDetailStatus = 0, updated_at = ? 
                                               WHERE purchaseDetailPurchaseNumber = ?");
        $update_details_stmt->bind_param("ss", $current_time, $purchase_number);
        $update_details_stmt->execute();
        $update_details_stmt->close();

        // Commit transaction 
        $conn->commit();
        header("Location: purchaselist.php?response=reactivated");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Purchase reactivation failed: " . $e->getMessage());

        $errorMessage = urlencode($e->getMessage()); 
        header("Location: purchaselist.php?response=error&errorMsg=$errorMessage");
        exit;
    }
} else {
    header("Location: purchaselist.php?response=error");
    exit;
}

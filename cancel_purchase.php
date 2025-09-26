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
        // Verify purchase exists and is pending
        $check_stmt = $conn->prepare("SELECT purchaseNumber, purchaseStatus FROM purchases WHERE purchaseUId = ?");
        $check_stmt->bind_param("i", $purchase_uid);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception("Purchase not found.");
        }

        $purchase = $result->fetch_assoc();
        $check_stmt->close();

        if ($purchase['purchaseStatus'] != 0) {
            throw new Exception("Purchase already completed or cancelled.");
        }

        $purchase_number = $purchase['purchaseNumber'];

        // Begin transaction
        $conn->begin_transaction();

        // Cancel purchase
        $cancel_stmt = $conn->prepare("UPDATE purchases SET purchaseStatus = 2, purchaseUpdatedBy = ?, updated_at = ? WHERE purchaseUId = ?");
        $cancel_stmt->bind_param("isi", $user_id, $current_time, $purchase_uid);
        $cancel_stmt->execute();
        $cancel_stmt->close();

        // Cancel purchase details
        $details_stmt = $conn->prepare("UPDATE purchase_details SET purchaseDetailStatus = 2, updated_at = ? WHERE purchaseDetailPurchaseNumber = ?");
        $details_stmt->bind_param("ss", $current_time, $purchase_number);
        $details_stmt->execute();
        $details_stmt->close();

        // Commit transaction
        $conn->commit();
        header("Location: purchaselist.php?msg=cancelled");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Purchase cancellation failed: " . $e->getMessage());
        
        $errorMessage = $e->getMessage(); 
        header("Location: purchaselist.php?msg=error&errorMsg=" . urlencode($errorMessage));
        exit;
    }
} else {
    header("Location: purchaselist.php?msg=error");
    exit;
}

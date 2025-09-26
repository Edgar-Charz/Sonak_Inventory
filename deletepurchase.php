<?php
include("includes/db_connection.php");
include("includes/session.php");

$user_id = $_SESSION['id'];
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET["id"])) {
    $purchase_uid = $_GET["id"];

    $conn->begin_transaction();

    try {
        // Get purchase number
        $purchase_query = $conn->prepare("SELECT purchaseNumber FROM purchases WHERE purchaseUId = ?");
        $purchase_query->bind_param("i", $purchase_uid);
        $purchase_query->execute();
        $purchase_result = $purchase_query->get_result();

        if ($purchase_result->num_rows == 0) {
            throw new Exception("Purchase not found");
        }

        $purchase_number = $purchase_result->fetch_assoc()["purchaseNumber"];
        $purchase_query->close();

        // Update purchases table
        $update_purchase_query = $conn->prepare("UPDATE purchases SET purchaseStatus = 3, purchaseUpdatedBy = ?, updated_at = ? WHERE purchaseUId = ?");
        $update_purchase_query->bind_param("isi", $user_id, $current_time, $purchase_uid);
        if (!$update_purchase_query->execute()) {
            throw new Exception("Failed to update purchase status");
        }
        $update_purchase_query->close();

        // Update purchase_details table
        $update_details_query = $conn->prepare("UPDATE purchase_details SET purchaseDetailStatus = 3, updated_at = ? WHERE purchaseDetailPurchaseNumber = ?");
        $update_details_query->bind_param("ss", $current_time, $purchase_number);
        if (!$update_details_query->execute()) {
            throw new Exception("Failed to update purchase details status");
        }
        $update_details_query->close();

        $conn->commit();
        header("Location: purchaselist.php?status=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Purchase delete failed: " . $e->getMessage());

        // Send exact error back to the page (URL encoded)
        $errorMessage = urlencode($e->getMessage());
        header("Location: purchaselist.php?status=error&errorMsg=$errorMessage");
        exit;
    }
} else {
    header("Location: purchaselist.php?status=error");
    exit;
}
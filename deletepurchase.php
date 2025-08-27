<?php
include 'includes/db_connection.php';
include 'includes/session.php';
    
if (isset($_GET['id'])) {
    $purchase_uid = $_GET['id'];

    // Deleting purchase
    $delete_purchase_query = $conn->prepare("DELETE FROM purchases WHERE purchaseUId = ?");
    $delete_purchase_query->bind_param("i", $purchase_uid);

    if ($delete_purchase_query->execute()) {
        header("Location: purchaselist.php?status=success");
        exit;
    } else {
        header("Location: purchaselist.php?status=error");
        exit;
    }
} else {
    header("Location: purchaselist.php?status=error");
}
exit;

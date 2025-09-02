<?php
include 'includes/db_connection.php';
include 'includes/session.php';

$user_id = $_SESSION['id'];
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['id'])) {
    $purchase_uid = $_GET['id'];

    $check_stmt = $conn->prepare("SELECT purchaseNumber, purchaseStatus FROM purchases WHERE purchaseUId = ?");
    $check_stmt->bind_param("i", $purchase_uid);
    $check_stmt->execute();
    $result = $check_stmt->get_result();    
    $purchase = $result->fetch_assoc();
    $check_stmt->close();

    if ($result->num_rows == 0 || $purchase['purchaseStatus'] != 0) {
        header("Location: purchaselist.php?msg=error");
        exit;
    }

    //  Purchase Number
    $purchase_number = $purchase['purchaseNumber'];

    $conn->begin_transaction();

    $cancel_stmt = $conn->prepare("UPDATE purchases SET purchaseStatus = 2, updatedBy = ?, updated_at = ? WHERE purchaseUId = ?");
    $cancel_stmt->bind_param("isi", $user_id, $current_time, $purchase_uid);

    if (!$cancel_stmt->execute()) {
        $cancel_stmt->close();
        $conn->rollback();
        header("Location: purchaselist.php?msg=error");
        exit;
    }
    $cancel_stmt->close();

    $details_stmt = $conn->prepare("UPDATE purchase_details SET status = 2, updated_at = ? WHERE purchaseNumber = ?");
    $details_stmt->bind_param("ss", $current_time, $purchase_number);

    if (!$details_stmt->execute()) {
        $details_stmt->close();
        $conn->rollback();
        header("Location: purchaselist.php?msg=error");
        exit;
    }
    $details_stmt->close();

    $conn->commit();
    header("Location: purchaselist.php?msg=cancelled");
    exit;
} else {
    header("Location: purchaselist.php?msg=error");
    exit;
}

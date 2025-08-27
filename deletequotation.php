<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (isset($_GET['id'])) {
    $quotation_uid = $_GET['id'];  

    // Delete quotation 
    $delete_quotation_query = $conn->prepare("DELETE FROM quotations WHERE quotationUId = ?");
    $delete_quotation_query->bind_param("s", $quotation_uid);

    if ($delete_quotation_query->execute()) {
        header("Location: quotationlist.php?status=success");
        exit;
    } else {
        header("Location: quotationlist.php?status=error");
        exit;
    }
} else {
    header("Location: quotationlist.php?status=error");
}
exit;

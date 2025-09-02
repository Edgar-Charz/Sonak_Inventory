<?php
include "includes/db_connection.php";
include "includes/session.php";

$user_id = $_SESSION['id'];

$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['id'])) {
    $quotation_id = $_GET['id'];

    $check_stmt = $conn->prepare("SELECT referenceNumber, quotationStatus FROM quotations WHERE quotationUId = ?");
    $check_stmt->bind_param("i", $quotation_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $quotation = $check_result->fetch_assoc();
    $check_stmt->close();
 
    if ($check_result->num_rows == 0 || $quotation['quotationStatus'] != 2) {
        header("Location: quotationlist.php?response=error");
        exit;
    }

    $reference_number = $quotation['referenceNumber'];

    // Begin transaction
    $conn->begin_transaction();

    $cancel_quotation_stmt = $conn->prepare("UPDATE quotations SET quotationStatus = 0, updatedBy = ?, updated_at = ? WHERE quotationUId = ?");
    $cancel_quotation_stmt->bind_param("isi", $user_id, $current_time, $quotation_id);

    if ($cancel_quotation_stmt->execute()) {
        $cancel_quotation_stmt->close();

        $details_stmt = $conn->prepare("UPDATE quotation_details SET status = 0, updated_at = ? WHERE referenceNumber = ?");
        $details_stmt->bind_param("ss", $current_time, $reference_number);

        if ($details_stmt->execute()) {
            $details_stmt->close();
            $conn->commit();
            header("Location: quotationlist.php?response=reactivated");
            exit;
        } else {
            $details_stmt->close();
            $conn->rollback();
        }
    } else {
        $cancel_quotation_stmt->close();
        $conn->rollback();
    }

    header("Location: quotationlist.php?response=error");
    exit;
}

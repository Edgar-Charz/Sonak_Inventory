<?php
include "includes/db_connection.php";
include "includes/session.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions

$user_id = $_SESSION['id'];
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['id'])) {
    $quotation_id = $_GET['id'];

    try {
        // Validate quotation exists and is cancelled
        $check_stmt = $conn->prepare("SELECT quotationReferenceNumber, quotationStatus FROM quotations WHERE quotationUId = ?");
        $check_stmt->bind_param("i", $quotation_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            throw new Exception("Quotation not found.");
        }

        $quotation = $check_result->fetch_assoc();
        $check_stmt->close();

        if ($quotation['quotationStatus'] != 2) {
            throw new Exception("Quotation is not cancelled.");
        }

        $reference_number = $quotation['quotationReferenceNumber']; 

        // Begin transaction
        $conn->begin_transaction();

        // Reactivate quotation
        $cancel_quotation_stmt = $conn->prepare("UPDATE quotations 
                                                 SET quotationStatus = 0, quotationUpdatedBy = ?, updated_at = ? 
                                                 WHERE quotationUId = ?");
        $cancel_quotation_stmt->bind_param("isi", $user_id, $current_time, $quotation_id);
        $cancel_quotation_stmt->execute();
        $cancel_quotation_stmt->close();

        // Reactivate quotation details
        $details_stmt = $conn->prepare("UPDATE quotation_details 
                                        SET quotationDetailStatus = 0, updated_at = ? 
                                        WHERE quotationDetailReferenceNumber = ?");
        $details_stmt->bind_param("ss", $current_time, $reference_number);
        $details_stmt->execute();
        $details_stmt->close();

        // Commit transaction
        $conn->commit();
        header("Location: quotationlist.php?response=reactivated");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Quotation reactivation failed: " . $e->getMessage());

        $error_message = urlencode($e->getMessage());
        header("Location: quotationlist.php?response=error&errorMsg=" . $error_message);
        exit;
    }
} else {
    header("Location: quotationlist.php?response=error");
    exit;
}

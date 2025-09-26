<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Get user id from session
$user_id = $_SESSION['id'];

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_POST["quotationUId"]) && isset($_POST["deleteReason"])) {
    $quotation_uid = $_POST["quotationUId"];
    $delete_reason = trim($_POST["deleteReason"]);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Get reference number
        $reference_query = $conn->prepare("SELECT quotationReferenceNumber FROM quotations WHERE quotationUId = ?");
        $reference_query->bind_param("i", $quotation_uid);
        $reference_query->execute();
        $reference_result = $reference_query->get_result();

        if ($reference_result->num_rows == 0) {
            throw new Exception("Quotation not found");
        }

        $reference_number = $reference_result->fetch_assoc()["quotationReferenceNumber"];
        $reference_query->close();

        // Update quotations table status
        $update_quotation_query = $conn->prepare("UPDATE quotations SET quotationStatus = 3, quotationDescription = ?, quotationUpdatedBy = ?, updated_at = ? WHERE quotationUId = ?");
        $update_quotation_query->bind_param("sisi",$delete_reason, $user_id, $current_time, $quotation_uid);

        if (!$update_quotation_query->execute()) {
            throw new Exception("Failed to update quotation status");
        }
        $update_quotation_query->close();

        // Update quotation_details table status 
        $update_details_query = $conn->prepare("UPDATE quotation_details SET quotationDetailStatus = 3, updated_at = ? WHERE quotationDetailReferenceNumber = ?");
        $update_details_query->bind_param("ss", $current_time, $reference_number);

        if (!$update_details_query->execute()) {
            throw new Exception("Failed to update quotation details status");
        }
        $update_details_query->close();

        // Commit transaction
        $conn->commit();
        header("Location: quotationList.php?status=success");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: quotationList.php?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: quotationList.php?status=error");
    exit;
}

<?php
include 'includes/db_connection.php';
include 'includes/session.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions for mysqli

$user_id = $_SESSION['id'];
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

if (isset($_GET['id'])) {
    $purchase_uid = $_GET['id'];

    try {
        $conn->begin_transaction();

        // Verify purchase exists and is pending
        $purchase_stmt = $conn->prepare("SELECT purchaseNumber FROM purchases WHERE purchaseUId = ? AND purchaseStatus = 0");
        $purchase_stmt->bind_param("i", $purchase_uid);
        $purchase_stmt->execute();
        $purchase_result = $purchase_stmt->get_result();

        if ($purchase_result->num_rows == 0) {
            throw new Exception("Purchase not found or already completed.");
        }

        $purchase_number = $purchase_result->fetch_assoc()['purchaseNumber'];

        // Check for missing agent/bank/account details
        $agent_check_stmt = $conn->prepare("SELECT 
                                                        COUNT(*) AS missing 
                                                        FROM 
                                                            purchase_details 
                                                        WHERE 
                                                            purchaseDetailPurchaseNumber = ? 
                                                            AND (purchaseDetailAgentId IS NULL OR purchaseAgentBankAccountNumber IS NULL)");
        $agent_check_stmt->bind_param("s", $purchase_number);
        $agent_check_stmt->execute();
        $missing = $agent_check_stmt->get_result()->fetch_assoc()['missing'];
        if ($missing > 0) {
            throw new Exception("Incomplete agent or bank details.");
        }

        // Check for missing product size
        $product_size_check = $conn->prepare("SELECT 
                                                        COUNT(*) AS missing 
                                                        FROM 
                                                            purchase_details 
                                                        WHERE 
                                                            purchaseDetailPurchaseNumber = ? 
                                                        AND 
                                                            (purchaseDetailProductSize IS NULL OR purchaseDetailProductSize = '')");
        $product_size_check->bind_param("s", $purchase_number);
        $product_size_check->execute();
        $product_size_missing = $product_size_check->get_result()->fetch_assoc()['missing'];
        if ($product_size_missing > 0) {
            throw new Exception("Missing product size.");
        }

        // Check for missing tracking number
        $tracking_check = $conn->prepare("SELECT 
                                                    COUNT(*) AS missing 
                                                    FROM 
                                                        purchase_details 
                                                    WHERE 
                                                        purchaseDetailPurchaseNumber = ? 
                                                    AND 
                                                        (purchaseDetailTrackingNumber IS NULL OR purchaseDetailTrackingNumber = '')");
        $tracking_check->bind_param("s", $purchase_number);
        $tracking_check->execute();
        $tracking_missing = $tracking_check->get_result()->fetch_assoc()['missing'];
        if ($tracking_missing > 0) {
            throw new Exception("Missing tracking number.");
        }

        // Check for missing transportation cost
        $transport_check = $conn->prepare("SELECT 
                                                        COUNT(*) AS missing
                                                        FROM 
                                                            purchase_details 
                                                        WHERE 
                                                            purchaseDetailPurchaseNumber = ? 
                                                        AND 
                                                            (agentTransportationCost IS NULL OR agentTransportationCost <= 0)");
        $transport_check->bind_param("s", $purchase_number);
        $transport_check->execute();
        $transport_missing = $transport_check->get_result()->fetch_assoc()['missing'];
        if ($transport_missing > 0) {
            throw new Exception("Missing or invalid transportation cost.");
        }

        // Check for missing date received
        $date_received_check = $conn->prepare("SELECT 
                                                            COUNT(*) AS missing 
                                                            FROM 
                                                                purchase_details 
                                                            WHERE 
                                                                purchaseDetailPurchaseNumber = ? AND dateReceivedByCompany IS NULL");
        $date_received_check->bind_param("s", $purchase_number);
        $date_received_check->execute();
        $date_received_missing = $date_received_check->get_result()->fetch_assoc()['missing'];
        if ($date_received_missing > 0) {
            throw new Exception("Missing date received by company.");
        }

        // Check for invalid quantity
        $quantity_check_stmt = $conn->prepare("SELECT 
                                                            COUNT(*) AS invalid_qty 
                                                        FROM 
                                                            purchase_details 
                                                        WHERE 
                                                            purchaseDetailPurchaseNumber = ? 
                                                        AND purchaseDetailQuantity <= 0");
        $quantity_check_stmt->bind_param("s", $purchase_number);
        $quantity_check_stmt->execute();
        $invalid_qty = $quantity_check_stmt->get_result()->fetch_assoc()['invalid_qty'];
        if ($invalid_qty > 0) {
            throw new Exception("Invalid quantity detected.");
        }

        // Fetch purchase details and update stock
        $details_stmt = $conn->prepare("SELECT 
                                                    purchaseDetailProductId, purchaseDetailQuantity 
                                                FROM 
                                                    purchase_details 
                                                 WHERE 
                                                    purchaseDetailPurchaseNumber = ?");
        $details_stmt->bind_param("s", $purchase_number);
        $details_stmt->execute();
        $details_result = $details_stmt->get_result();

        while ($detail = $details_result->fetch_assoc()) {
            $product_id = $detail['purchaseDetailProductId'];
            $quantity   = $detail['purchaseDetailQuantity'];

            $update_stock_stmt = $conn->prepare("UPDATE 
                                                            products 
                                                        SET 
                                                            productQuantity = productQuantity + ?, updated_at = ? 
                                                        WHERE 
                                                            productId = ?");
            $update_stock_stmt->bind_param("iss", $quantity, $current_time, $product_id);
            $update_stock_stmt->execute();
            $update_stock_stmt->close();
        }

        // Mark purchase as completed
        $complete_stmt = $conn->prepare("UPDATE 
                                                    purchases 
                                                SET 
                                                    purchaseStatus = 1, purchaseUpdatedBy = ?, updated_at = ? 
                                                WHERE 
                                                    purchaseUId = ?");
        $complete_stmt->bind_param("isi", $user_id, $current_time, $purchase_uid);
        $complete_stmt->execute();
        $complete_stmt->close();

        // Mark purchase_details as completed
        $details_update_stmt = $conn->prepare("UPDATE 
                                                            purchase_details 
                                                        SET 
                                                            purchaseDetailStatus = 1, updated_at = ? 
                                                        WHERE 
                                                            purchaseDetailPurchaseNumber = ?");
        $details_update_stmt->bind_param("ss", $current_time, $purchase_number);
        $details_update_stmt->execute();
        $details_update_stmt->close();

        // Commit transaction
        $conn->commit();
        header("Location: purchaselist.php?message=completed");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Purchase completion failed: " . $e->getMessage());

        // Send exact error back to the page (URL encoded)
        $errorMessage = urlencode($e->getMessage());
        header("Location: purchaselist.php?message=error&errorMsg=$errorMessage");
        exit;
    }
}

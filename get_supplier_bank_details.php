<?php
// Prevent PHP errors from breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'includes/db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    if (!isset($_GET['supplierId']) || !is_numeric($_GET['supplierId'])) {
        $response['message'] = 'Invalid supplier ID';
        echo json_encode($response);
        exit;
    }
 
    $supplierId = $_GET['supplierId'];

    // If bankName is provided, fetch account numbers and holder names
    if (isset($_GET['bankName']) && !empty($_GET['bankName'])) {
        $bankName = $_GET['bankName'];
        $stmt = $conn->prepare("SELECT bankAccountUId, bankAccountNumber, bankAccountHolderName 
                                FROM bank_accounts 
                                WHERE bankAccountSupplierId = ? AND bankAccountBankName = ?");
        $stmt->bind_param("is", $supplierId, $bankName);
        $stmt->execute();
        $result = $stmt->get_result();
        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = [
                'accountId' => $row['bankAccountUId'],
                'accountNumber' => $row['bankAccountNumber'],
                'holderName' => $row['bankAccountHolderName']
            ];
        }
        $response['success'] = true;
        $response['data'] = $accounts;
        $response['message'] = count($accounts) > 0 ? 'Accounts fetched successfully' : 'No accounts found for this bank';
    } else {
        // Fetch distinct bank names for the supplier
        $stmt = $conn->prepare("SELECT DISTINCT bankAccountBankName 
                                FROM bank_accounts 
                                WHERE bankAccountSupplierId = ?");
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();
        $banks = [];
        while ($row = $result->fetch_assoc()) {
            $banks[] = $row['bankAccountBankName'];
        }
        $response['success'] = true;
        $response['data'] = $banks;
        $response['message'] = count($banks) > 0 ? 'Banks fetched successfully' : 'No bank accounts found for this supplier';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
exit;

<?php
// Prevent PHP errors from breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'includes/db_connection.php';

header('Content-Type: application/json');

try {
    if (isset($_GET['agentId']) && !isset($_GET['bankName'])) {
        // Fetch distinct bank names for the agent
        $agentId = $_GET['agentId'];
        $stmt = $conn->prepare("SELECT DISTINCT bankAccountBankName FROM bank_accounts WHERE bankAccountAgentId = ?");
        $stmt->bind_param("s", $agentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $banks = [];
        while ($row = $result->fetch_assoc()) { 
            $banks[] = $row['bankAccountBankName'];
        }
        $stmt->close();

        echo json_encode(['success' => true, 'data' => $banks]);
    } elseif (isset($_GET['agentId']) && isset($_GET['bankName'])) {
        // Fetch account numbers and holder names for the agent and bank
        $agentId = $_GET['agentId'];
        $bankName = $_GET['bankName'];
        $stmt = $conn->prepare("SELECT bankAccountNumber, bankAccountHolderName FROM bank_accounts WHERE bankAccountAgentId = ? AND bankAccountBankName = ?");
        $stmt->bind_param("ss", $agentId, $bankName);
        $stmt->execute();
        $result = $stmt->get_result();
        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = [
                'accountNumber' => $row['bankAccountNumber'],
                'holderName' => $row['bankAccountHolderName']
            ];
        }
        $stmt->close();

        echo json_encode(['success' => true, 'data' => $accounts]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

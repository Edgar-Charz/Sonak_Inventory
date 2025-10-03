<?php
// get_payment_data.php
require_once 'includes/db_connection.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$type   = isset($_GET['type']) ? $_GET['type'] : '';
$provider = isset($_GET['provider']) ? $_GET['provider'] : '';

if ($action === 'providers') {
    // Return list of providers by type
    if ($type !== 'Bank' && $type !== 'Mobile Money') {
        echo json_encode([]);
        exit;
    }
    $stmt = $conn->prepare("SELECT DISTINCT paymentAccountProviderName 
                            FROM company_payment_accounts 
                            WHERE paymentAccountType = ? AND paymentAccountStatus = 1
                            ORDER BY paymentAccountProviderName");
    $stmt->bind_param('s', $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $providers = [];
    while ($row = $result->fetch_assoc()) {
        $providers[] = [
            'provider_name' => $row['paymentAccountProviderName']
        ];
    }
    echo json_encode($providers);
    exit;
} elseif ($action === 'accounts') {
    // Return list of accounts for a provider
    if (($type !== 'Bank' && $type !== 'Mobile Money') || empty($provider)) {
        echo json_encode([]);
        exit;
    }
    $stmt = $conn->prepare("SELECT paymentAccountUId, paymentAccountNumber, paymentAccountHolderName
                            FROM company_payment_accounts 
                            WHERE paymentAccountType = ? 
                              AND paymentAccountProviderName = ? 
                              AND paymentAccountStatus = 1
                            ORDER BY paymentAccountNumber");
    $stmt->bind_param('ss', $type, $provider);
    $stmt->execute();
    $result = $stmt->get_result();
    $accounts = [];
    while ($row = $result->fetch_assoc()) {
        $accounts[] = [
            'id'              => $row['paymentAccountUId'],     
            'account_number'  => $row['paymentAccountNumber'],
            'account_holder'  => $row['paymentAccountHolderName']
        ];
    }
    echo json_encode($accounts);
    exit;
} else {
    echo json_encode([]);
    exit;
}

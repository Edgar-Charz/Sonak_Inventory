<?php
require('assets/fpdf/fpdf.php');
require('includes/db_connection.php');

// Set timezone
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Initialize PDF
$pdf = new FPDF();
$pdf->AddPage('P', 'A4');
$pdf->SetMargins(10, 10);

// Header background
// $pdf->SetFillColor(230, 230, 230);
// $pdf->Rect(10, 10, 190, 20, 'F');

// Logo 
$pdf->Image('assets/img/logo.png', 155, 12, 45);

// Title
$pdf->SetFont('Times', 'B', 16);
$pdf->SetXY(12, 15);
$pdf->Cell(0, 10, ' Sales Report', 0, 1, 'L');

// Line 
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, 32, 200, 32);
$pdf->Ln(10);

// Filter details
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(0, 8, 'Report Filters', 0, 1, 'L');

$pdf->SetFont('Times', '', 10);

if (!empty($_GET['from_date'])) {
    $pdf->Cell(0, 6, 'From : ' . date('j F, Y', strtotime($_GET['from_date'])), 0, 1, 'L');
}

if (!empty($_GET['to_date'])) {
    $pdf->Cell(0, 6, 'To : ' . date('j F, Y', strtotime($_GET['to_date'])), 0, 1, 'L');
}

if (!empty($_GET['customer_id'])) {
    $customerQuery = $conn->prepare("SELECT customerName FROM customers WHERE customerId = ?");
    $customerQuery->bind_param("i", $_GET['customer_id']);
    $customerQuery->execute();
    $customerResult = $customerQuery->get_result();
    if ($customer = $customerResult->fetch_assoc()) {
        $pdf->Cell(0, 6, 'Customer : ' . $customer['customerName'], 0, 1, 'L');
    }
    $customerQuery->close();
}

// If no filters selected
if (empty($_GET['from_date']) && empty($_GET['to_date']) && empty($_GET['customer_id'])) {
    $pdf->Cell(0, 6, 'None', 0, 1, 'L');
}

// Generated time
$pdf->Ln(3);
$pdf->SetFont('Times', 'I', 9);
$pdf->Cell(0, 6, 'Generated on: ' . $current_time, 0, 1, 'L');
$pdf->Ln(5);

//Horizontal Line
$pdf->SetDrawColor(0, 0, 0); 
$pdf->SetLineWidth(0.3);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(10);

// Table headers
$pdf->SetFont('Times', 'B', 10);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(10, 10, 'S/N', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Product Price', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Sold QTY', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Instock QTY', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Sold Amount', 1, 0, 'C', true);
$pdf->Ln();

// Fetch filtered data
$conditions = ["orders.orderStatus = 1"];
$params = [];
$types = "";

if (!empty($_GET['from_date'])) {
    $fromDate = date('Y-m-d', strtotime($_GET['from_date']));
    $conditions[] = "orders.orderDate >= ?";
    $params[] = $fromDate;
    $types .= "s";
}
if (!empty($_GET['to_date'])) {
    $toDate = date('Y-m-d', strtotime($_GET['to_date']));
    $conditions[] = "orders.orderDate <= ?";
    $params[] = $toDate;
    $types .= "s";
}
if (!empty($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];
    $conditions[] = "orders.orderCustomerId = ?";
    $params[] = $customerId;
    $types .= "i";
}

$whereClause = implode(" AND ", $conditions);
$query = "SELECT
            products.productId AS 'Product ID',
            products.productName AS 'Product Name',
            products.productSellingPrice AS 'Product Price',
            SUM(order_details.orderDetailTotalCost) AS 'Sold Amount',
            SUM(order_details.orderDetailQuantity) AS 'Sold QTY',
            products.productQuantity AS 'Instock QTY'
        FROM
            order_details, products, orders
        WHERE order_details.orderDetailProductId = products.productId
            AND order_details.orderDetailInvoiceNumber = orders.orderInvoiceNumber
            AND $whereClause
        GROUP BY 
            products.productId, products.productName, products.productQuantity
        ORDER BY 
            SUM(order_details.orderDetailTotalCost) DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Table rows with zebra striping
$pdf->SetFont('Times', '', 10);
$pdf->SetFillColor(245, 245, 245);
$sn = 0;
$fill = false;

if ($result->num_rows > 0) {
    // Initialize totals
    $totalSoldQty = 0;
    $totalInstockQty = 0;
    $totalSoldAmount = 0;

    while ($row = $result->fetch_assoc()) {
        $sn++;
        $pdf->Cell(10, 10, $sn, 1, 0, 'C', $fill);
        $pdf->Cell(50, 10, $row['Product Name'], 1, 0, 'L', $fill);
        $pdf->Cell(30, 10, number_format($row['Product Price'], 2), 1, 0, 'C', $fill);
        $pdf->Cell(30, 10, number_format($row['Sold QTY'], 0), 1, 0, 'C', $fill);
        $pdf->Cell(30, 10, number_format($row['Instock QTY'], 0), 1, 0, 'C', $fill);
        $pdf->Cell(40, 10, number_format($row['Sold Amount'], 2), 1, 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;

        // Accumulate totals
        $totalSoldQty += $row['Sold QTY'];
        $totalInstockQty += $row['Instock QTY'];
        $totalSoldAmount += $row['Sold Amount'];
    }
} else {
    $pdf->Cell(190, 10, 'No records found.', 1, 0, 'C');
    $pdf->Ln();
}

// Total
$pdf->SetFont('Times', 'B', 10);
$pdf->SetFillColor(220, 230, 240);
$pdf->Cell(10, 10, '', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'TOTAL', 1, 0, 'C', true);
$pdf->Cell(30, 10, '', 1, 0, 'C', true);
$pdf->Cell(30, 10, number_format($totalSoldQty, 0), 1, 0, 'C', true);
$pdf->Cell(30, 10, number_format($totalInstockQty, 0), 1, 0, 'C', true);
$pdf->Cell(40, 10, number_format($totalSoldAmount, 2), 1, 0, 'C', true);
$pdf->Ln();

$stmt->close();
$conn->close();

// Footer, Fixed at bottom
$pdf->SetY(-35);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(1.5);

// Footer Content
$pdf->SetFont('Times', 'B', 7);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 5, 'SONAK COMPANY LIMITED', 0, 1, 'C');

$pdf->SetFont('Times', '', 8);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(0, 4, 'P.O. Box 78530 DAR ES SALAAM, TANZANIA', 0, 1, 'C');
$pdf->Cell(0, 4, 'Riverside St, Dar es Salaam', 0, 1, 'C');

// Output PDF
$pdf->Output('D', 'Sales_Report_' . date('Ymd_His') . '.pdf');

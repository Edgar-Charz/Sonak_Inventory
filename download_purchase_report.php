<?php
require('assets/fpdf/fpdf.php');
require('includes/db_connection.php');

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
$pdf->Cell(0, 10, ' Purchases Report', 0, 1, 'L');

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

if (!empty($_GET['supplier_id'])) {
    $supplierQuery = $conn->prepare("SELECT supplierName FROM suppliers WHERE supplierId = ?");
    $supplierQuery->bind_param("i", $_GET['supplier_id']);
    $supplierQuery->execute();
    $supplierResult = $supplierQuery->get_result();
    if ($supplier = $supplierResult->fetch_assoc()) {
        $pdf->Cell(0, 6, 'Supplier : ' . $supplier['supplierName'], 0, 1, 'L');
    }
    $supplierQuery->close();
}

// If no filters selected
if (empty($_GET['from_date']) && empty($_GET['to_date']) && empty($_GET['supplier_id'])) {
    $pdf->Cell(0, 6, 'None', 0, 1, 'L');
}

// Generated time
$pdf->Ln(3);
$pdf->SetFont('Times', 'I', 9);
$pdf->Cell(0, 6, 'Generated on: ' . date('d-M-Y H:i:s'), 0, 1, 'L');
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
$pdf->Cell(60, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Purchased QTY', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Instock QTY', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Purchased Amount', 1, 0, 'C', true);
$pdf->Ln();

// Fetch filtered data
$conditions = ["purchases.purchaseStatus = 1"];
$params = [];
$types = "";

if (!empty($_GET['from_date'])) {
    $fromDate = date('Y-m-d', strtotime($_GET['from_date']));
    $conditions[] = "purchases.purchaseDate >= ?";
    $params[] = $fromDate;
    $types .= "s";
}
if (!empty($_GET['to_date'])) {
    $toDate = date('Y-m-d', strtotime($_GET['to_date']));
    $conditions[] = "purchases.purchaseDate <= ?";
    $params[] = $toDate;
    $types .= "s";
}
if (!empty($_GET['supplier_id'])) {
    $supplierId = $_GET['supplier_id'];
    $conditions[] = "purchases.purchaseSupplierId = ?";
    $params[] = $supplierId;
    $types .= "i";
}

$whereClause = implode(" AND ", $conditions);
$query = "SELECT
                                products.productId AS 'Product ID',
                                products.productName AS 'Product Name',
                                SUM(purchase_details.purchaseDetailTotalCost) AS 'Purchased Amount',
                                SUM(purchase_details.purchaseDetailQuantity) AS 'Purchased QTY',
                                products.productQuantity AS 'Instock QTY'
                            FROM
                                purchase_details
                            JOIN 
                                products ON purchase_details.purchaseDetailProductId = products.productId
                            JOIN 
                                purchases ON purchase_details.purchaseDetailPurchaseNumber = purchases.purchaseNumber
                            WHERE $whereClause
                            GROUP BY 
                                products.productId, products.productName, products.productQuantity
                            ORDER BY 
                                SUM(purchase_details.purchaseDetailTotalCost) DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$sn  = 0;
$fill = false;

// Table rows with zebra striping
$pdf->SetFont('Times', '', 10);
$pdf->SetFillColor(245, 245, 245);
$sn = 0;
$fill = false;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sn++;
        $pdf->Cell(10, 10, $sn, 1, 0, 'C', $fill);
        $pdf->Cell(60, 10, $row['Product Name'], 1, 0, 'L', $fill);
        $pdf->Cell(35, 10, number_format($row['Purchased QTY'], 0), 1, 0, 'C', $fill);
        $pdf->Cell(35, 10, number_format($row['Instock QTY'], 0), 1, 0, 'C', $fill);
        $pdf->Cell(50, 10, number_format($row['Purchased Amount'], 2), 1, 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }
} else {
    $pdf->Cell(170, 10, 'No records found.', 1, 0, 'C');
    $pdf->Ln();
}

$stmt->close();
$conn->close();

// Footer, Fixed at bottom
$pdf->SetY(-35);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(1.5);

// Footer Content
$pdf->SetFont('Times', 'B', 10);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 5, 'THANK YOU FOR YOUR BUSINESS!', 0, 1, 'C');

$pdf->SetFont('Times', '', 8);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(0, 4, 'If you have any questions, please contact us.', 0, 1, 'C');
$pdf->Cell(0, 4, 'SONAK Company Ltd | Riverside St, Dar es Salaam | +255 123 456 789 | info@sonak.com', 0, 1, 'C');

// Output PDF
$pdf->Output('D', 'Purchases_Report_' . date('Ymd_His') . '.pdf');

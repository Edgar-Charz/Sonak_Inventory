<?php
require('assets/fpdf/fpdf.php');
require('includes/db_connection.php');

if (isset($_GET['referenceNumber'])) {
    $referenceNumber = $_GET['referenceNumber'];

    // Fetch quotation summary
    $stmt = $conn->prepare("SELECT 
                                quotations.*, 
                                customers.*, 
                                u1.username AS biller, 
                                u2.username AS updater 
                            FROM quotations 
                            JOIN customers ON quotations.customerId = customers.customerId 
                            JOIN users AS u1 ON quotations.createdBy = u1.userId 
                            JOIN users AS u2 ON quotations.updatedBy = u2.userId 
                            WHERE quotations.referenceNumber = ?");
    $stmt->bind_param("s", $referenceNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $quotation = $result->fetch_assoc();
 
    if ($quotation) {
        $pdf = new FPDF();
        $pdf->AddPage('P', 'A4');

        // Header background
        // $pdf->SetFillColor(230, 240, 255);
        // $pdf->Rect(10, 10, 190, 25, 'F');

        // Logo
        $pdf->Image('assets/img/logo.png', 155, 12, 45);

        // Title
        $pdf->SetFont('Arial', 'B', 28);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->SetXY(12, 15);
        $pdf->Cell(0, 15, 'INVOICE', 0, 1, 'L');

        $pdf->SetTextColor(0, 0, 0); // Reset to black
        $pdf->Ln(5);

        // 🔹 Bold Horizontal Line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // Invoice Info
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5, 'INVOICE NUMBER', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 5, $quotation['referenceNumber'], 0, 0, 'R');

        $pdf->setX(120);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5, 'INVOICE DATE', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 5, date('F j, Y', strtotime($quotation['quotationDate'])), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5, 'CUSTOMER ID', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 5, $quotation['customerId'], 0, 0, 'R');

        $pdf->SetX(120);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5, 'DUE DATE', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $dueDate = date('F j, Y', strtotime($quotation['quotationDate'] . ' +30 days'));
        $pdf->Cell(40, 5, $dueDate, 0, 1, 'R');
        $pdf->Ln(5);

        // 🔹 Normal Horizontal Line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // Billed To + Payment Info
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(95, 6, 'BILLED TO:', 0, 0);
        $pdf->Cell(95, 6, 'PAYMENT INFO:', 0, 1, 'R');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 6, $quotation['customerName'], 0, 0);
        $pdf->Cell(95, 6, 'Account Name: SONAK Ltd', 0, 1, 'R');

        $pdf->Cell(95, 6, $quotation['customerAddress'], 0, 0);
        $pdf->Cell(95, 6, 'Account Number: 123456789', 0, 1, 'R');

        $pdf->Cell(95, 6, $quotation['customerEmail'], 0, 0);
        $pdf->Cell(95, 6, 'Bank Name: CRDB Bank', 0, 1, 'R');

        $pdf->Ln(5);
        // 🔹 Normal Horizontal Line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(10);

        //Table Header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(65, 8, 'PRODUCT', 1, 0, 'C');
        $pdf->Cell(40, 8, 'UNIT COST', 1, 0, 'C');
        $pdf->Cell(40, 8, 'QUANTITY', 1, 0, 'C');
        $pdf->Cell(45, 8, 'AMOUNT', 1, 1, 'C');
        // Items
        $item_query = $conn->prepare("SELECT 
                                        qd.quantity, 
                                        qd.unitPrice, 
                                        qd.subTotal,
                                        p.productName 
                                      FROM quotation_details qd 
                                      JOIN products p ON qd.productId = p.productId 
                                      WHERE qd.referenceNumber = ?");
        $item_query->bind_param("s", $referenceNumber);
        $item_query->execute();
        $item_result = $item_query->get_result();

        $pdf->SetFont('Arial', '', 10);
        while ($item = $item_result->fetch_assoc()) {
            $pdf->Cell(65, 8, $item['productName'], 1, 0, 'C');
            $pdf->Cell(40, 8, number_format($item['unitPrice'], 2), 1, 0, 'C');
            $pdf->Cell(40, 8, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(45, 8, number_format($item['subTotal'], 2), 1, 1, 'C');
        }

        $pdf->Ln(10);

        // Totals Table
        $pdf->SetX(130);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 8, 'SubTotal', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['subTotal'], 2), 'B', 1, 'R');

        $pdf->SetX(130);
        $pdf->Cell(30, 8, 'Tax (' . $quotation['taxPercentage'] . '%)', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['taxAmount'], 2), 'B', 1, 'R');

        $pdf->SetX(130);
        $pdf->Cell(30, 8, 'Discount (' . $quotation['discountPercentage'] . '%)', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['discountAmount'], 2), 'B', 1, 'R');

        $pdf->SetX(130);
        $pdf->Cell(30, 8, 'Shipping', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['shippingAmount'], 2), 'B', 1, 'R');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetX(130);
        $pdf->Cell(30, 10, 'TOTAL', 'B', 0, 'L');
        $pdf->Cell(40, 10, number_format($quotation['totalAmount'], 2), 'B', 1, 'R');

        $pdf->Ln(5);



        // Set position below the total
        $pdf->SetY(-75);
        // Set font for section header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'Payment Terms', 0, 1, 'L');
        // Set font for body text
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, 'Payment is due within thirty (30) days of the invoice date. Late payments may incur a service charge of 5% per month.', 0, 'L');

        $pdf->Ln(5);

        // Signature line
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'Authorized by:', 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(90, 6, 'Name: ____________________________', 0, 0, 'L');
        $pdf->Cell(0, 6, 'Date: ______________________', 0, 1, 'R');

        $pdf->Cell(90, 6, 'Position: __________________________', 0, 0, 'L');

        // Footer - Fixed at bottom
        $pdf->SetY(-35);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(1.5);

        // Footer Content
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->Cell(0, 5, 'THANK YOU FOR YOUR BUSINESS!', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 4, 'If you have any questions, please contact us.', 0, 1, 'C');
        $pdf->Cell(0, 4, 'SONAK Company Ltd | Riverside St, Dar es Salaam | +255 123 456 789 | info@sonak.com', 0, 1, 'C');

        $pdf->Output('D', 'Invoice_' . $referenceNumber . '.pdf');
    } else {
        echo "Invoice not found.";
    }
} else {
    echo "No invoice number provided.";
}

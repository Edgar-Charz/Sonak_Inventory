<?php
require('assets/fpdf/fpdf.php');
require('includes/db_connection.php');

if (isset($_GET['referenceNumber'])) {
    $referenceNumber = $_GET['referenceNumber'];

    // Fetch quotation summary
    $quotation_stmt = $conn->prepare("SELECT 
                                        quotations.*, 
                                        customers.*, 
                                        u1.username AS biller, 
                                        u2.username AS updater
                                    FROM 
                                        quotations, customers, users AS u1, users AS u2
                                    WHERE 
                                        quotations.quotationCustomerId = customers.customerId
                                        AND quotations.quotationCreatedBy = u1.userId
                                        AND quotations.quotationUpdatedBy = u2.userId
                                        AND quotations.quotationReferenceNumber = ?");
    $quotation_stmt->bind_param("s", $referenceNumber);
    $quotation_stmt->execute();
    $quotation_result = $quotation_stmt->get_result();
    $quotation = $quotation_result->fetch_assoc();

    if ($quotation) {
        $pdf = new FPDF();
        $pdf->AddPage('P', 'A4');

        // Header background
        // $pdf->SetFillColor(230, 240, 255);
        // $pdf->Rect(10, 10, 190, 25, 'F');

        // Logo
        $pdf->Image('assets/img/logo.png', 155, 12, 45);

        // Title
        $pdf->SetFont('Times', 'B', 28);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->SetXY(12, 15);
        $pdf->Cell(0, 15, 'INVOICE', 0, 1, 'L');

        // Reset to black
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5);

        // Bold Horizontal Line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // Invoice Info
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(40, 5, 'INVOICE NUMBER', 0, 0, 'L');
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(40, 5, $quotation['quotationReferenceNumber'], 0, 0, 'R');

        $pdf->setX(120);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(40, 5, 'INVOICE DATE', 0, 0, 'L');
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(40, 5, date('F j, Y', strtotime($quotation['quotationDate'])), 0, 1, 'R');

        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(40, 5, 'CUSTOMER ID', 0, 0, 'L');
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(40, 5, $quotation['quotationCustomerId'], 0, 0, 'R');

        $pdf->SetX(120);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(40, 5, 'DUE DATE', 0, 0, 'L');
        $pdf->SetFont('Times', '', 10);
        $dueDate = date('F j, Y', strtotime($quotation['quotationDate'] . ' +30 days'));
        $pdf->Cell(40, 5, $dueDate, 0, 1, 'R');
        $pdf->Ln(5);

        // 🔹 Normal Horizontal Line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // Billed To + Payment Info
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(95, 6, 'BILLED TO:', 0, 0);
        $pdf->Cell(95, 6, 'PAYMENT INFO:', 0, 1, 'R');

        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(95, 6, $quotation['customerName'], 0, 0);
        $pdf->Cell(95, 6, 'Account Name: SONAK Ltd', 0, 1, 'R');

        $pdf->Cell(95, 6, $quotation['customerAddress'], 0, 0);
        $pdf->Cell(95, 6, 'Account Number: 123456789', 0, 1, 'R');

        $pdf->Cell(95, 6, $quotation['customerEmail'], 0, 0);
        $pdf->Cell(95, 6, 'Bank Name: CRDB Bank', 0, 1, 'R');

        $pdf->Ln(5);
        
        //Normal Horizontal Line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(10);

        //Table Header
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(10, 8, 'S/N', 1, 0, 'C');
        $pdf->Cell(55, 8, 'PRODUCT', 1, 0, 'C');
        $pdf->Cell(40, 8, 'UNIT COST', 1, 0, 'C');
        $pdf->Cell(40, 8, 'QUANTITY', 1, 0, 'C');
        $pdf->Cell(45, 8, 'AMOUNT', 1, 1, 'C');

        // Items
        $quotation_details_stmt = $conn->prepare("SELECT 
                                                    quotation_details.quotationDetailQuantity, 
                                                    quotation_details.quotationDetailUnitPrice, 
                                                    quotation_details.quotationDetailSubTotal,
                                                    products.productName 
                                                FROM 
                                                    quotation_details, products
                                                WHERE 
                                                    quotation_details.quotationDetailProductId = products.productId 
                                                AND 
                                                    quotation_details.quotationDetailReferenceNumber = ?");
        $sn = 0;
        $quotation_details_stmt->bind_param("s", $referenceNumber);
        $quotation_details_stmt->execute();
        $details_result = $quotation_details_stmt->get_result();

        $pdf->SetFont('Times', '', 10);
        while ($detail = $details_result->fetch_assoc()) {
            $sn++;
            $pdf->Cell(10, 8, $sn, 1, 0, 'C');
            $pdf->Cell(55, 8, $detail['productName'], 1, 0, 'L');
            $pdf->Cell(40, 8, number_format($detail['quotationDetailUnitPrice'], 2), 1, 0, 'C');
            $pdf->Cell(40, 8, $detail['quotationDetailQuantity'], 1, 0, 'C');
            $pdf->Cell(45, 8, number_format($detail['quotationDetailSubTotal'], 2), 1, 1, 'C');
        }

        $pdf->Ln(10);

        // Totals Table
        $pdf->SetX(130);
        $pdf->Cell(30, 8, 'Shipping', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['quotationShippingAmount'], 2), 'B', 1, 'R');

        $pdf->SetX(130);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(30, 8, 'SubTotal', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['quotationSubTotal'], 2), 'B', 1, 'R');

        $pdf->SetX(130);
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(30, 8, 'Tax (' . $quotation['quotationTaxPercentage'] . '%)', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['quotationTaxAmount'], 2), 'B', 1, 'R');

        $pdf->SetX(130);
        $pdf->Cell(30, 8, 'Discount (' . $quotation['quotationDiscountPercentage'] . '%)', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($quotation['quotationDiscountAmount'], 2), 'B', 1, 'R');

        $pdf->SetFont('Times', 'B', 11);
        $pdf->SetX(130);
        $pdf->Cell(30, 10, 'TOTAL', 'B', 0, 'L');
        $pdf->Cell(40, 10, number_format($quotation['quotationTotalAmount'], 2), 'B', 1, 'R');

        $pdf->Ln(5);



        // Set position below the total
        $pdf->SetY(-75);
        // Set font for section header
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(0, 6, 'Payment Terms', 0, 1, 'L');
        // Set font for body text
        $pdf->SetFont('Times', '', 9);
        $pdf->MultiCell(0, 5, $quotation['quotationDescription'], 0, 'L');

        $pdf->Ln(5);

        // Signature line
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(0, 6, 'Authorized by:', 0, 1, 'L');

        $pdf->SetFont('Times', '', 9);
        $pdf->Cell(90, 6, 'Name: ____________________________', 0, 0, 'L');
        $pdf->Cell(0, 6, 'Date: ______________________', 0, 1, 'R');

        $pdf->Cell(90, 6, 'Position: __________________________', 0, 0, 'L');

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

        $pdf->Output('D', 'Invoice_' . $referenceNumber . '.pdf');
    } else {
        echo "Invoice not found.";
    }
} else {
    echo "No invoice number provided.";
}

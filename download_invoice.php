<?php
require('assets/tcpdf/tcpdf.php');
require('includes/db_connection.php');

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));

// Get approver id, if approved
$approver_id = $_GET['approverId'] ?? null;

// Check if approverId is provided and valid
$has_signature = false;
if ($approver_id) {
    $username_query = $conn->prepare("SELECT 
        users.username, users.userEmail, user_certificates.userCertPath, user_certificates.userCertKey, user_certificates.userCertKeyPassword
        FROM users 
        JOIN user_certificates ON users.userId = user_certificates.userCertId
        WHERE users.userId = ?");
    $username_query->bind_param("i", $approver_id);
    $username_query->execute();
    $username_result = $username_query->get_result();
    $userData = $username_result->fetch_assoc();

    if ($userData) {
        $userName     = $userData['username'];
        $userEmail    = $userData['userEmail'];
        $certPath     = 'file://' . __DIR__ . '/' . $userData['userCertPath'];
        $keyPath      = 'file://' . __DIR__ . '/' . $userData['userCertKey'];
        $keyPassword  = $userData['userCertKeyPassword'] ?? '';
        if (!empty($certPath) && !empty($keyPath)) {
            $has_signature = true;
        }
    }
}

// Get quotation reference number
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
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('SONAK COMPANY LIMITED');
        $pdf->SetAuthor('SONAK COMPANY LIMITED');
        $pdf->SetTitle('Invoice ' . $quotation['quotationReferenceNumber']);
        $pdf->SetSubject('Invoice');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        // Logo
        $pdf->Image('assets/img/logo.png', 155, 5, 45);
        $pdf->SetXY(155, 20);
        $pdf->SetFont('times', 'BI', 10);
        $pdf->Cell(45, 6, 'Engineering Your Dreams', 0, 1, 'R');

        // Title
        $pdf->SetFont('times', 'B', 28);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->SetXY(12, 8);
        $pdf->Cell(0, 15, 'INVOICE', 0, 1, 'L');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(3);

        // Horizontal line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(3);

        // FROM
        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell(95, 5, 'FROM', 0, 0);
        $pdf->SetX(125);
        $pdf->Cell(80, 5, '', 0, 1);

        $pdf->SetLineWidth(0.1);
        $pdf->Line(10, $pdf->GetY(), 105, $pdf->GetY());
        $pdf->Ln(1);

        $pdf->SetFont('times', '', 10);
        $pdf->Cell(95, 5, 'SONAK COMPANY LIMITED', 0, 0);
        $pdf->SetX(125);
        $pdf->Cell(35, 5, 'INVOICE:', 0, 0, 'L');
        $pdf->Cell(40, 5, $quotation['quotationReferenceNumber'], 0, 1, 'R');

        $pdf->Cell(95, 5, 'P.O BOX: 78530 DAR ES SALAAM', 0, 0);
        $pdf->SetX(125);
        $pdf->Cell(35, 5, 'DATE:', 0, 0, 'L');
        $pdf->Cell(40, 5, date('F j, Y', strtotime($quotation['quotationDate'])), 0, 1, 'R');

        $pdf->Cell(95, 5, 'kafanabo.alfred@gmail.com', 0, 0);
        $pdf->SetX(125);
        $pdf->Cell(35, 5, 'TIN:', 0, 0, 'L');
        $pdf->Cell(40, 5, '139-589-135', 0, 1, 'R');

        $pdf->Cell(95, 5, '(+255) 757 788 043', 0, 0);
        $pdf->SetX(125);
        $pdf->Cell(35, 5, 'VRN:', 0, 0, 'L');
        $pdf->Cell(40, 5, '40-048558-Z', 0, 1, 'R');

        $pdf->Ln(2);

        // TO (Customer)
        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell(95, 5, 'TO', 0, 0);
        $pdf->SetX(120);
        $pdf->Cell(80, 5, '', 0, 1);

        $pdf->SetLineWidth(0.1);
        $pdf->Line(10, $pdf->GetY(), 105, $pdf->GetY());
        $pdf->Ln(1);

        $pdf->SetFont('times', '', 10);
        $pdf->Cell(95, 5, $quotation['customerName'], 0, 1);
        $pdf->Cell(95, 5, $quotation['customerAddress'], 0, 1);
        $pdf->Cell(95, 5, $quotation['customerEmail'], 0, 1);
        $pdf->Cell(95, 5, $quotation['customerPhone'], 0, 1);

        $pdf->Ln(2);

        // Horizontal line
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // Table Header
        $pdf->SetFont('times', 'B', 10);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(45, 8, 'ITEM', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'DESCRIPTION', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'PRICE', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'QTY', 1, 0, 'C', true);
        $pdf->Cell(45, 8, 'TOTAL PRICE', 1, 1, 'C', true);

        // Fetch items
        $quotation_details_stmt = $conn->prepare("SELECT 
                                            quotation_details.quotationDetailQuantity, 
                                            quotation_details.quotationDetailUnitPrice, 
                                            quotation_details.quotationDetailSubTotal,
                                            products.productName,
                                            products.productNotes
                                        FROM 
                                            quotation_details, products
                                        WHERE 
                                            quotation_details.quotationDetailProductId = products.productId 
                                        AND 
                                            quotation_details.quotationDetailReferenceNumber = ?");
        $quotation_details_stmt->bind_param("s", $referenceNumber);
        $quotation_details_stmt->execute();
        $details_result = $quotation_details_stmt->get_result();

        $pdf->SetFont('times', '', 10);
        while ($detail = $details_result->fetch_assoc()) {
            $pdf->Cell(45, 5, $detail['productName'], 1, 0, 'L');
            $pdf->Cell(50, 5, $detail['productNotes'], 1, 0, 'L');
            $pdf->Cell(10, 5, 'TZS', 1, 0, 'L');
            $pdf->Cell(20, 5, number_format($detail['quotationDetailUnitPrice']), 1, 0, 'R');
            $pdf->Cell(20, 5, $detail['quotationDetailQuantity'], 1, 0, 'C');
            $pdf->Cell(10, 5, 'TZS', 1, 0, 'L');
            $pdf->Cell(35, 5, number_format($detail['quotationDetailSubTotal']), 1, 1, 'R');
        }

        // Transportation Cost
        $pdf->Cell(45, 7, 'Transportation Cost', 1, 0, 'L');
        $pdf->Cell(50, 7, '', 1, 0, 'L');
        $pdf->Cell(10, 7, '', 'LTB', 0, 'L');
        $pdf->Cell(20, 7, '', 'RTB', 0, 'R');
        $pdf->Cell(20, 7, '', 1, 0, 'C');
        $pdf->Cell(10, 7, 'TZS', 'LTB', 0, 'L');
        $pdf->Cell(35, 7, number_format($quotation['quotationShippingAmount']), 'RTB', 1, 'R');

        $pdf->Ln(5);

        // Subtotal
        $pdf->setX(55);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(100, 7, 'TOTAL (VAT EXCLUSIVE)', 1, 0, 'C', true);
        $pdf->Cell(10, 7, 'TZS', 'LTB', 0, 'L');
        $pdf->Cell(35, 7, number_format($quotation['quotationSubTotal']), 'RTB', 1, 'R', true);

        // Discount
        $pdf->setX(55);
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(100, 7, 'DISCOUNT (' . $quotation['quotationDiscountPercentage'] . '%)', '1', 0, 'C');
        $pdf->Cell(10, 7, 'TZS', 'LTB', 0, 'L');
        $pdf->Cell(35, 7, number_format($quotation['quotationDiscountAmount']), 'RTB', 1, 'R');

        // Tax
        $pdf->setX(55);
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(100, 7, 'VAT (' . $quotation['quotationTaxPercentage'] . '%)', '1', 0, 'C');
        $pdf->Cell(10, 7, 'TZS', 'LTB', 0, 'L');
        $pdf->Cell(35, 7, number_format($quotation['quotationTaxAmount']), 'RTB', 1, 'R');

        // Grand Total
        $pdf->setX(55);
        $pdf->SetFillColor(220, 230, 240);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(100, 8, 'TOTAL (VAT INCLUSIVE)', '1', 0, 'C', true);
        $pdf->Cell(10, 8, 'TZS', 'LTB', 0, 'L', true);
        $pdf->Cell(35, 8, number_format($quotation['quotationTotalAmount']), 'RTB', 1, 'R', true);

        $pdf->Ln(5);

        //  Horizontal Line
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(3);

        // Account Info
        $pdf->SetFont('Times', 'B', 10);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->Cell(95, 5, 'ACCOUNT DETAILS', 0, 0);
        $pdf->SetX(120);
        $pdf->Cell(80, 5, '', 0, 1,);

        $pdf->SetX(10);
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(95, 5, 'Account Name: SONAK COMPANY LIMITED', 0, 1);
        $pdf->Cell(95, 5, 'Account No.: 23010055375', 0, 1);
        $pdf->Cell(95, 5, 'Bank Name: NMB', 0, 1);
        $pdf->Cell(95, 5, 'Branch Address: AIRPORT, Dar Es Salaam', 0, 1);


        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(2);


        // Set position below the total
        $pdf->SetY(-75);

        // Set font for section header
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(0, 5, 'Terms', 0, 1, 'L');

        // Set font for body text
        $pdf->SetFont('Times', '', 9);
        $pdf->MultiCell(0, 5, $quotation['quotationDescription'], 0, 'L');

        $pdf->Ln(5);


        // Signature section (only if approverId is provided and valid)
        if ($has_signature) {
            // Digital signature info
            $info = [
                'Name' => $userName,
                'Location' => 'Dar es Salaam',
                'Reason' => 'Document Approval',
                'ContactInfo' => $userEmail
            ];

            // Apply the digital signature
            $pdf->setSignature(
                $certPath,
                $keyPath,
                $keyPassword,
                '',
                2,
                $info
            );
            $pdf->setSignatureAppearance(150, 250, 40, 15);

            // Add visible note about digital signature
            $pdf->SetFont('times', 'B', 7);
            $pdf->SetTextColor(0, 102, 204);
            $pdf->Write(0, 'This document is digitally signed by ' . $userName);
            $pdf->SetTextColor(0, 0, 0);
        }

        // Footer
        $pdf->SetY(-35);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(1.5);
        $pdf->SetFont('times', 'B', 7);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->Cell(0, 5, 'THANK YOU FOR YOUR BUSINESS!', 0, 1, 'C');
        $pdf->SetFont('times', '', 8);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 4, 'SONAK COMPANY LIMITED | P.O. BOX 78530 DAR ES SALAAM, TANZANIA', 0, 1, 'C');
        $pdf->Cell(0, 4, ' Riverside St, Dar Es Salaam ', 0, 1, 'C');

        // Output
        $pdf->Output('Invoice_' . $referenceNumber . '.pdf', 'D');
    } else {
        echo "Invoice not found.";
    }
} else {
    echo "No invoice number provided.";
}

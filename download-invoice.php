<?php
require('assets/fpdf/fpdf.php');
require('includes/db_connection.php');

if (isset($_GET['invoiceNumber'])) {
    $invoiceNumber = $_GET['invoiceNumber'];

    // Fetch order summary
    $stmt = $conn->prepare("SELECT 
                                orders.*, 
                                customers.*, 
                                u1.username AS biller, 
                                u2.username AS updater 
                            FROM orders 
                            JOIN customers ON orders.customerId = customers.customerId 
                            JOIN users AS u1 ON orders.createdBy = u1.userId 
                            JOIN users AS u2 ON orders.updatedBy = u2.userId 
                            WHERE orders.invoiceNumber = ?");
    $stmt->bind_param("s", $invoiceNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        $pdf = new FPDF();
        $pdf->AddPage('P', 'A4');

        // Logo
        $pdf->Image('assets/img/logo.png', 160, 10, 45); 
        $pdf->SetFont('Arial', 'B', 28);
        $pdf->Cell(0, 20, 'INVOICE', 0, 1, 'L');
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
        $pdf->Cell(40, 5, $order['invoiceNumber'], 0, 0, 'R');

        $pdf->setX(120);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5, 'INVOICE DATE', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 5, date('F j, Y', strtotime($order['orderDate'])), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5, 'CUSTOMER ID', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 5, $order['customerId'], 0, 0, 'R');

        $pdf->setX(120);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5, 'DUE DATE', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 5, date('F j, Y', strtotime($order['created_at'])), 0, 1, 'R');

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
        $pdf->Cell(95, 6, $order['customerName'], 0, 0);
        $pdf->Cell(95, 6, 'Account Name: SONAK Ltd', 0, 1, 'R');

        $pdf->Cell(95, 6, $order['customerAddress'], 0, 0);
        $pdf->Cell(95, 6, 'Account Number: 123456789', 0, 1, 'R');

        $pdf->Cell(95, 6, $order['customerEmail'], 0, 0);
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
                                        od.quantity, 
                                        od.unitCost, 
                                        od.totalCost,
                                        p.productName 
                                      FROM order_details od 
                                      JOIN products p ON od.productId = p.productId 
                                      WHERE od.invoiceNumber = ? AND od.status = 1");
        $item_query->bind_param("s", $invoiceNumber);
        $item_query->execute();
        $item_result = $item_query->get_result();

        $pdf->SetFont('Arial', '', 10);
        while ($item = $item_result->fetch_assoc()) {
            $pdf->Cell(65, 8, $item['productName'], 1, 0, 'C');
            $pdf->Cell(40, 8, number_format($item['unitCost'], 2), 1, 0, 'C');
            $pdf->Cell(40, 8, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(45, 8, number_format($item['totalCost'], 2), 1, 1, 'C');
        }

        $pdf->Ln(10);

        // Totals Table
        $pdf->SetX(130);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 8, 'Subtotal', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($order['subTotal'], 2), 'B', 1, 'R');

        $pdf->SetX(130);
        $pdf->Cell(30, 8, 'Tax (18%)', 'B', 0, 'L');
        $pdf->Cell(40, 8, number_format($order['vat'], 2), 'B', 1, 'R');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetX(130);
        $pdf->Cell(30, 10, 'TOTAL', 'B', 0, 'L');
        $pdf->Cell(40, 10, number_format($order['total'], 2), 'B', 1, 'R');

        $pdf->Ln(5);

        // Footer - Fixed at bottom
        $pdf->SetY(-40); // Position near bottom
        $pdf->SetDrawColor(0, 0, 0); 
        $pdf->SetLineWidth(0.8);     
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Full width line
        $pdf->Ln(2);

        // Footer Content
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(0, 102, 204); 
        $pdf->Cell(0, 6, 'THANK YOU FOR YOUR BUSINESS!', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(60, 60, 60); 
        $pdf->Cell(0, 5, 'If you have any questions, please contact us.', 0, 1, 'C');
        $pdf->Cell(0, 5, 'SONAK Company Ltd | Riverside St, Dar es Salaam | +255 123 456 789 | info@sonak.com', 0, 1, 'C');
        
        $pdf->Output('D', 'Invoice_' . $invoiceNumber . '.pdf');
    } else {
        echo "Invoice not found.";
    }
} else {
    echo "No invoice number provided.";
}

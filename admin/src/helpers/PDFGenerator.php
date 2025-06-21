<?php
// src/helpers/PDFGenerator.php
namespace App\helpers;

use TCPDF;
use setasign\Fpdi\Tcpdf\Fpdi;

class PDFGenerator extends Fpdi {
    private $logoPath;
    private $primaryColor = [41, 128, 185];   // #2980b9
    private $secondaryColor = [52, 152, 219]; // #3498db
    private $accentColor = [231, 76, 60];     // #e74c3c

    public function __construct() {
        parent::__construct();
        $this->logoPath = __DIR__ . '/../../public/assets/images/logo.png';
    }

    public function Header() {
        // Draw header background
        $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Rect(0, 0, $this->getPageWidth(), 30, 'F');
        
        // Add logo
        if (file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 15, 8, 30);
        }
        
        // Add title
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(255);
        $this->SetXY(0, 10);
        $this->Cell(0, 0, $_ENV['APP_NAME'], 0, 1, 'C');
        
        // Add subtitle
        $this->SetFont('helvetica', '', 12);
        $this->SetXY(0, 18);
        $this->Cell(0, 0, 'Professional Client Management System', 0, 1, 'C');
        
        // Add decorative line
        $this->SetLineWidth(0.5);
        $this->SetDrawColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
        $this->Line(15, 30, $this->getPageWidth() - 15, 30);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetY(-10);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, 'Generated on ' . date('F j, Y, g:i a'), 0, 0, 'C');
    }

    public function createReceipt($data) {
        $this->SetTitle('Receipt #' . $data['receipt_number']);
        $this->SetAuthor($_ENV['APP_NAME']);
        $this->AddPage();
        
        // Receipt header
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Cell(0, 10, 'RECEIPT', 0, 1, 'C');
        $this->Ln(5);
        
        // Receipt details
        $this->SetFont('helvetica', '', 12);
        $this->SetTextColor(0);
        
        $this->Cell(40, 7, 'Receipt Number:', 0, 0);
        $this->Cell(0, 7, $data['receipt_number'], 0, 1);
        
        $this->Cell(40, 7, 'Date:', 0, 0);
        $this->Cell(0, 7, date('F j, Y', strtotime($data['date'])), 0, 1);
        
        $this->Cell(40, 7, 'Client:', 0, 0);
        $this->Cell(0, 7, $data['client_name'], 0, 1);
        
        $this->Cell(40, 7, 'Payment Method:', 0, 0);
        $this->Cell(0, 7, $data['payment_method'], 0, 1);
        
        $this->Ln(10);
        
        // Items table
        $this->SetFillColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        $this->SetTextColor(255);
        $this->SetDrawColor(200);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        
        // Header
        $this->Cell(100, 10, 'Description', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Quantity', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Amount', 1, 1, 'C', true);
        
        // Items
        $this->SetFont('');
        $this->SetTextColor(0);
        $this->SetFillColor(245);
        $fill = false;
        
        foreach ($data['items'] as $item) {
            $this->Cell(100, 10, $item['description'], 'LR', 0, 'L', $fill);
            $this->Cell(40, 10, $item['quantity'], 'LR', 0, 'C', $fill);
            $this->Cell(40, 10, '$' . number_format($item['amount'], 2), 'LR', 1, 'R', $fill);
            $fill = !$fill;
        }
        
        // Closing line
        $this->Cell(180, 0, '', 'T');
        $this->Ln(5);
        
        // Total
        $this->SetFont('', 'B');
        $this->Cell(140, 10, 'Total:', 0, 0, 'R');
        $this->Cell(40, 10, '$' . number_format($data['total'], 2), 0, 1, 'R');
        
        $this->Ln(15);
        
        // Notes
        $this->SetFont('', 'I');
        $this->MultiCell(0, 7, $data['notes']);
        
        // Signature
        $this->Ln(15);
        $this->Cell(0, 10, '___________________________', 0, 1, 'R');
        $this->Cell(0, 5, 'Authorized Signature', 0, 1, 'R');
        
        // Save to file
        $filename = 'receipt_' . $data['receipt_number'] . '.pdf';
        $filepath = __DIR__ . '/../../storage/receipts/' . $filename;
        $this->Output($filepath, 'F');
        
        return $filename;
    }

    public function createClientReport($clients) {
        $this->SetTitle('Client Report');
        $this->SetAuthor($_ENV['APP_NAME']);
        $this->AddPage();
        
        // Report header
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Cell(0, 10, 'CLIENT REPORT', 0, 1, 'C');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 7, 'Generated on ' . date('F j, Y'), 0, 1, 'C');
        $this->Ln(15);
        
        // Table header
        $this->SetFillColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        $this->SetTextColor(255);
        $this->SetDrawColor(200);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        
        $this->Cell(60, 10, 'Client Name', 1, 0, 'C', true);
        $this->Cell(60, 10, 'Email', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Phone', 1, 0, 'C', true);
        $this->Cell(20, 10, 'Projects', 1, 1, 'C', true);
        
        // Client data
        $this->SetFont('');
        $this->SetTextColor(0);
        $this->SetFillColor(245);
        $fill = false;
        
        foreach ($clients as $client) {
            $this->Cell(60, 10, $client['name'], 'LR', 0, 'L', $fill);
            $this->Cell(60, 10, $client['email'], 'LR', 0, 'L', $fill);
            $this->Cell(40, 10, $client['phone'], 'LR', 0, 'C', $fill);
            $this->Cell(20, 10, $client['project_count'], 'LR', 1, 'C', $fill);
            $fill = !$fill;
        }
        
        // Closing line
        $this->Cell(180, 0, '', 'T');
        
        // Save to file
        $filename = 'client_report_' . date('Ymd_His') . '.pdf';
        $filepath = __DIR__ . '/../../storage/exports/' . $filename;
        $this->Output($filepath, 'F');
        
        return $filename;
    }
}
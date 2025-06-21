<?php
declare(strict_types=1);

class PDFGenerator {
    public static function generateReceipt(array $data): string {
        $html = self::renderReceipt($data);
        
        $options = new Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Courier');
        
        $dompdf = new Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();
        
        $filename = 'receipt_' . $data['id'] . '.pdf';
        $filePath = RECEIPTS_PATH . '/' . $filename;
        file_put_contents($filePath, $dompdf->output());
        
        return $filename;
    }
    
    private static function renderReceipt(array $data): string {
        ob_start(); ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Receipt #<?= $data['id'] ?></title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; }
                .header { text-align: center; margin-bottom: 20px; }
                .info { margin-bottom: 30px; }
                .table { width: 100%; border-collapse: collapse; }
                .table th, .table td { padding: 8px; border: 1px solid #ddd; }
                .text-right { text-align: right; }
                .footer { margin-top: 50px; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2><?= getenv('APP_NAME') ?></h2>
                <p>Official Receipt</p>
            </div>
            
            <div class="info">
                <p><strong>Receipt #:</strong> <?= $data['id'] ?></p>
                <p><strong>Date:</strong> <?= date('M j, Y', strtotime($data['date'])) ?></p>
                <p><strong>Client:</strong> <?= $data['client_name'] ?></p>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= $data['description'] ?></td>
                        <td class="text-right">$<?= number_format($data['amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong>$<?= number_format($data['amount'], 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="footer">
                <p><?= getenv('APP_NAME') ?></p>
                <p><?= getenv('COMPANY_ADDRESS') ?></p>
                <p>Thank you for your business!</p>
            </div>
        </body>
        </html>
        <?php return ob_get_clean();
    }
    
    public static function generateFromHtml(string $html, string $filename): string {
        $options = new Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filePath = EXPORT_PATH . '/' . $filename;
        file_put_contents($filePath, $dompdf->output());
        
        return $filePath;
    }
}
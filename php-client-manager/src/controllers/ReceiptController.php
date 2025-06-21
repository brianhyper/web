<?php
declare(strict_types=1);

class ReceiptController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function generate(int $transactionId): string {
        $transaction = $this->getTransaction($transactionId);
        $client = (new ClientController())->get($transaction['client_id']);
        
        // Build receipt data
        $data = [
            'transaction_id' => $transactionId,
            'date' => date('M j, Y'),
            'client' => $client['name'],
            'amount' => number_format($transaction['amount'], 2),
            'description' => $transaction['description']
        ];
        
        // Generate PDF (would use a PDF library in real implementation)
        $fileName = 'receipt_' . $transactionId . '.pdf';
        $this->savePDF($fileName, $data);
        
        return $fileName;
    }

    public function savePDF(string $fileName, array $data): void {
        // In a real implementation, this would use Dompdf or similar
        $content = "Receipt #{$data['transaction_id']}\n";
        $content .= "Date: {$data['date']}\n";
        $content .= "Client: {$data['client']}\n";
        $content .= "Amount: \${$data['amount']}\n";
        $content .= "Description: {$data['description']}\n";
        
        file_put_contents(RECEIPT_PATH . '/' . $fileName, $content);
    }

    private function getTransaction(int $id): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM transactions 
             WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $_SESSION['user_id']
        ]);
        
        $transaction = $stmt->fetch();
        if (!$transaction) {
            throw new Exception('Transaction not found');
        }
        
        return $transaction;
    }
}
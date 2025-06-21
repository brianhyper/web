<?php
declare(strict_types=1);

class ExportController {
    public function clientsToCSV(): string {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT name, email, phone, address 
             FROM clients 
             WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $clients = $stmt->fetchAll();
        
        $csv = "Name,Email,Phone,Address\n";
        foreach ($clients as $client) {
            $csv .= sprintf('"%s","%s","%s","%s"' . "\n",
                $client['name'],
                $client['email'],
                $client['phone'],
                $client['address']
            );
        }
        
        $fileName = 'clients_' . date('Ymd') . '.csv';
        file_put_contents(EXPORT_PATH . '/' . $fileName, $csv);
        return $fileName;
    }

    public function transactionsToPDF(int $year, int $month): string {
        $start = date('Y-m-01', strtotime("$year-$month-01"));
        $end = date('Y-m-t', strtotime($start));
        
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT t.date, t.type, t.amount, t.description, c.name AS client
             FROM transactions t
             LEFT JOIN clients c ON c.id = t.client_id
             WHERE t.user_id = :user_id
             AND t.date BETWEEN :start AND :end"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'start' => $start,
            'end' => $end
        ]);
        
        $transactions = $stmt->fetchAll();
        $fileName = "transactions_{$year}_{$month}.pdf";
        
        // Generate PDF (simplified)
        $pdfContent = "Transactions for {$month}/{$year}\n\n";
        foreach ($transactions as $t) {
            $pdfContent .= "{$t['date']} - {$t['type']}: \${$t['amount']} ({$t['client']})\n";
        }
        
        file_put_contents(EXPORT_PATH . '/' . $fileName, $pdfContent);
        return $fileName;
    }
}
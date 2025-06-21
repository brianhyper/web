<?php
declare(strict_types=1);

class Receipt {
    public $id;
    public $transaction_id;
    public $file_path;
    public $created_at;

    private static $db;

    public function __construct() {
        self::$db = Database::getInstance();
    }

    public function save(): int {
        $data = [
            'transaction_id' => $this->transaction_id,
            'file_path' => $this->file_path
        ];

        if ($this->id) {
            $stmt = self::$db->prepare(
                "UPDATE receipts SET 
                 transaction_id = :transaction_id, file_path = :file_path
                 WHERE id = {$this->id}"
            );
            $stmt->execute($data);
            return $this->id;
        }

        $stmt = self::$db->prepare(
            "INSERT INTO receipts (transaction_id, file_path, created_at)
             VALUES (:transaction_id, :file_path, NOW())"
        );
        $stmt->execute($data);
        $this->id = self::$db->lastInsertId();
        return $this->id;
    }

    public static function find(int $id): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM receipts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function forTransaction(int $transactionId): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM receipts WHERE transaction_id = :id");
        $stmt->execute(['id' => $transactionId]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public function generatePDF(): string {
        $transaction = Budget::find($this->transaction_id);
        $client = $transaction->client_id ? Client::find($transaction->client_id) : null;
        
        // PDF generation logic would go here
        $fileName = 'receipt_' . $this->id . '.pdf';
        $this->file_path = $fileName;
        $this->save();
        
        return $fileName;
    }

    public function getFullPath(): string {
        return RECEIPT_PATH . '/' . $this->file_path;
    }
}
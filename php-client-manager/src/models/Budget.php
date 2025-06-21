<?php
declare(strict_types=1);

class Budget {
    public $id;
    public $user_id;
    public $client_id;
    public $type; // 'income' or 'expense'
    public $category;
    public $amount;
    public $description;
    public $date;
    public $created_at;

    private static $db;

    public function __construct() {
        self::$db = Database::getInstance();
    }

    public function save(): int {
        $data = [
            'user_id' => $this->user_id,
            'client_id' => $this->client_id,
            'type' => $this->type,
            'category' => $this->category,
            'amount' => $this->amount,
            'description' => $this->description,
            'date' => $this->date
        ];

        if ($this->id) {
            $stmt = self::$db->prepare(
                "UPDATE transactions SET 
                 client_id = :client_id, type = :type, category = :category,
                 amount = :amount, description = :description, date = :date
                 WHERE id = {$this->id}"
            );
            $stmt->execute($data);
            return $this->id;
        }

        $stmt = self::$db->prepare(
            "INSERT INTO transactions (user_id, client_id, type, category, amount, description, date, created_at)
             VALUES (:user_id, :client_id, :type, :category, :amount, :description, :date, NOW())"
        );
        $stmt->execute($data);
        $this->id = self::$db->lastInsertId();
        return $this->id;
    }

    public static function find(int $id): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM transactions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function incomeForUser(int $userId, string $period = 'month'): array {
        return self::transactionsByType($userId, 'income', $period);
    }

    public static function expensesForUser(int $userId, string $period = 'month'): array {
        return self::transactionsByType($userId, 'expense', $period);
    }

    private static function transactionsByType(int $userId, string $type, string $period): array {
        $db = Database::getInstance();
        $dateFilter = self::getDateFilter($period);
        
        $stmt = $db->prepare(
            "SELECT * FROM transactions 
             WHERE user_id = :user_id 
             AND type = :type
             AND $dateFilter"
        );
        
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    private static function getDateFilter(string $period): string {
        switch ($period) {
            case 'week':
                return "date >= CURDATE() - INTERVAL 7 DAY";
            case 'month':
                return "date >= CURDATE() - INTERVAL 30 DAY";
            case 'year':
                return "date >= CURDATE() - INTERVAL 1 YEAR";
            default:
                return "1=1";
        }
    }

    public static function summary(int $userId, string $start, string $end): array {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT 
                type,
                category,
                SUM(amount) AS total,
                COUNT(*) AS count
             FROM transactions
             WHERE user_id = :user_id
             AND date BETWEEN :start AND :end
             GROUP BY type, category"
        );
        
        $stmt->execute([
            'user_id' => $userId,
            'start' => $start,
            'end' => $end
        ]);
        
        return $stmt->fetchAll();
    }
}
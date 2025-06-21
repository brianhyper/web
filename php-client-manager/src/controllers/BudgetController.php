<?php
declare(strict_types=1);

class BudgetController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function recordIncome(array $data): int {
        $required = ['client_id', 'amount', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }
        
        (new ClientController())->verifyOwnership($data['client_id']);
        
        $stmt = $this->db->prepare(
            "INSERT INTO transactions (user_id, client_id, type, amount, description, date)
             VALUES (:user_id, :client_id, 'income', :amount, :description, NOW())"
        );
        
        return $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'client_id' => (int)$data['client_id'],
            'amount' => (float)$data['amount'],
            'description' => Security::sanitize($data['description'])
        ]) ? $this->db->lastInsertId() : 0;
    }

    public function recordExpense(array $data): int {
        $required = ['category', 'amount', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO transactions (user_id, type, category, amount, description, date)
             VALUES (:user_id, 'expense', :category, :amount, :description, NOW())"
        );
        
        return $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'category' => Security::sanitize($data['category']),
            'amount' => (float)$data['amount'],
            'description' => Security::sanitize($data['description'])
        ]) ? $this->db->lastInsertId() : 0;
    }

    public function getSummary(int $year, int $month): array {
        $start = date('Y-m-01', strtotime("$year-$month-01"));
        $end = date('Y-m-t', strtotime($start));
        
        $stmt = $this->db->prepare(
            "SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
             FROM transactions
             WHERE user_id = :user_id
             AND date BETWEEN :start AND :end"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'start' => $start,
            'end' => $end
        ]);
        
        return $stmt->fetch() ?: [
            'total_income' => 0,
            'total_expense' => 0
        ];
    }
}
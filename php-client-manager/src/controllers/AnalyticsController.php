<?php
declare(strict_types=1);

class AnalyticsController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDashboardData(): array {
        return [
            'clients' => $this->getClientStats(),
            'projects' => $this->getProjectStats(),
            'revenue' => $this->getRevenueTrend(),
            'upcoming' => $this->getUpcomingEvents()
        ];
    }
    
    private function getClientStats(): array {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN created_at > CURDATE() - INTERVAL 30 DAY THEN 1 ELSE 0 END) AS new
             FROM clients
             WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    private function getProjectStats(): array {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress
             FROM projects
             WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    private function getRevenueTrend(): array {
        $stmt = $this->db->prepare(
            "SELECT 
                DATE_FORMAT(date, '%Y-%m') AS month,
                SUM(amount) AS revenue
             FROM transactions
             WHERE user_id = :user_id
             AND type = 'income'
             AND date > CURDATE() - INTERVAL 6 MONTH
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month DESC
             LIMIT 6"
        );
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll();
    }
    
    private function getUpcomingEvents(): array {
        $stmt = $this->db->prepare(
            "SELECT title, start_time, client_id
             FROM events
             WHERE user_id = :user_id
             AND start_time > NOW()
             ORDER BY start_time ASC
             LIMIT 5"
        );
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll();
    }
}
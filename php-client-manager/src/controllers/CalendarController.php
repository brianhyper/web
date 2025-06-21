<?php
declare(strict_types=1);

class CalendarController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createEvent(array $data): int {
        $required = ['title', 'start_time'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO events (user_id, title, description, start_time, end_time, client_id)
             VALUES (:user_id, :title, :description, :start_time, :end_time, :client_id)"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'title' => Security::sanitize($data['title']),
            'description' => Security::sanitize($data['description'] ?? ''),
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'] ?? null,
            'client_id' => $data['client_id'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function getEvents(string $start, string $end): array {
        $stmt = $this->db->prepare(
            "SELECT id, title, start_time AS start, end_time AS end, 
                    client_id, description 
             FROM events
             WHERE user_id = :user_id
             AND start_time BETWEEN :start AND :end"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'start' => $start,
            'end' => $end
        ]);
        
        return $stmt->fetchAll();
    }

    public function sendReminders(): void {
        $now = date('Y-m-d H:i:s');
        $later = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        $stmt = $this->db->prepare(
            "SELECT e.*, u.email, c.name AS client_name
             FROM events e
             JOIN users u ON u.id = e.user_id
             LEFT JOIN clients c ON c.id = e.client_id
             WHERE e.start_time BETWEEN :now AND :later
             AND e.reminder_sent = 0"
        );
        
        $stmt->execute(['now' => $now, 'later' => $later]);
        $events = $stmt->fetchAll();
        
        foreach ($events as $event) {
            $this->sendEmailReminder($event);
            $this->markReminderSent($event['id']);
        }
    }
    
    private function sendEmailReminder(array $event): void {
        $subject = "Upcoming Event: {$event['title']}";
        $time = date('g:i a', strtotime($event['start_time']));
        $clientInfo = $event['client_id'] ? " with {$event['client_name']}" : "";
        
        $body = "You have an event coming up at {$time}{$clientInfo}.\n\n";
        $body .= "Event: {$event['title']}\n";
        $body .= "Description: {$event['description']}\n";
        
        Mailer::send($event['email'], $subject, $body);
    }
    
    private function markReminderSent(int $eventId): void {
        $stmt = $this->db->prepare(
            "UPDATE events SET reminder_sent = 1 WHERE id = :id"
        );
        $stmt->execute(['id' => $eventId]);
    }
}
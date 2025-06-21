<?php
declare(strict_types=1);

class CalendarEvent {
    public $id;
    public $user_id;
    public $client_id;
    public $title;
    public $description;
    public $start_time;
    public $end_time;
    public $reminder_sent = false;
    public $created_at;
    public $updated_at;

    private static $db;

    public function __construct() {
        self::$db = Database::getInstance();
    }

    public function save(): int {
        $data = [
            'user_id' => $this->user_id,
            'client_id' => $this->client_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time
        ];

        if ($this->id) {
            $stmt = self::$db->prepare(
                "UPDATE events SET 
                 title = :title, description = :description, 
                 start_time = :start_time, end_time = :end_time,
                 client_id = :client_id
                 WHERE id = {$this->id}"
            );
            $stmt->execute($data);
            return $this->id;
        }

        $stmt = self::$db->prepare(
            "INSERT INTO events (user_id, client_id, title, description, start_time, end_time, created_at)
             VALUES (:user_id, :client_id, :title, :description, :start_time, :end_time, NOW())"
        );
        $stmt->execute($data);
        $this->id = self::$db->lastInsertId();
        return $this->id;
    }

    public static function find(int $id): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function upcomingForUser(int $userId, int $limit = 5): array {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM events 
             WHERE user_id = :user_id
             AND start_time > NOW()
             ORDER BY start_time ASC
             LIMIT :limit"
        );
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function forDateRange(int $userId, string $start, string $end): array {
        $db = Database::getInstance();
        $stmt = $db::prepare(
            "SELECT * FROM events 
             WHERE user_id = :user_id
             AND start_time BETWEEN :start AND :end"
        );
        $stmt->execute([
            'user_id' => $userId,
            'start' => $start,
            'end' => $end
        ]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function markReminderSent(): bool {
        $stmt = self::$db->prepare(
            "UPDATE events SET reminder_sent = 1 WHERE id = :id"
        );
        return $stmt->execute(['id' => $this->id]);
    }
}
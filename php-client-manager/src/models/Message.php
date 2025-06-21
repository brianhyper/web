<?php
declare(strict_types=1);

class Message {
    public $id;
    public $user_id;
    public $client_id;
    public $message;
    public $response;
    public $is_bot = false;
    public $created_at;

    private static $db;

    public function __construct() {
        self::$db = Database::getInstance();
    }

    public function save(): int {
        $data = [
            'user_id' => $this->user_id,
            'client_id' => $this->client_id,
            'message' => $this->message,
            'response' => $this->response,
            'is_bot' => (int)$this->is_bot
        ];

        if ($this->id) {
            $stmt = self::$db->prepare(
                "UPDATE messages SET 
                 message = :message, response = :response
                 WHERE id = {$this->id}"
            );
            $stmt->execute($data);
            return $this->id;
        }

        $stmt = self::$db::prepare(
            "INSERT INTO messages (user_id, client_id, message, response, is_bot, created_at)
             VALUES (:user_id, :client_id, :message, :response, :is_bot, NOW())"
        );
        $stmt->execute($data);
        $this->id = self::$db->lastInsertId();
        return $this->id;
    }

    public static function conversation(int $userId, int $clientId, int $limit = 20): array {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM messages 
             WHERE user_id = :user_id 
             AND client_id = :client_id
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function recentBots(int $userId, int $limit = 10): array {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM messages 
             WHERE user_id = :user_id 
             AND is_bot = 1
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}
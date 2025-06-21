<?php
declare(strict_types=1);

class Project {
    public $id;
    public $user_id;
    public $client_id;
    public $title;
    public $description;
    public $status = 'planned';
    public $deadline;
    public $budget;
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
            'status' => $this->status,
            'deadline' => $this->deadline,
            'budget' => $this->budget
        ];

        if ($this->id) {
            $stmt = self::$db->prepare(
                "UPDATE projects SET 
                 title = :title, description = :description, status = :status,
                 deadline = :deadline, budget = :budget, client_id = :client_id
                 WHERE id = {$this->id}"
            );
            $stmt->execute($data);
            return $this->id;
        }

        $stmt = self::$db->prepare(
            "INSERT INTO projects (user_id, client_id, title, description, status, deadline, budget, created_at)
             VALUES (:user_id, :client_id, :title, :description, :status, :deadline, :budget, NOW())"
        );
        $stmt->execute($data);
        $this->id = self::$db->lastInsertId();
        return $this->id;
    }

    public static function find(int $id): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function forUser(int $userId, string $status = null): array {
        $db = Database::getInstance();
        $sql = "SELECT * FROM projects WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function addAttachment(string $fileName, string $originalName): bool {
        $stmt = self::$db->prepare(
            "INSERT INTO project_attachments (project_id, file_name, original_name)
             VALUES (:project_id, :file_name, :original_name)"
        );
        return $stmt->execute([
            'project_id' => $this->id,
            'file_name' => $fileName,
            'original_name' => $originalName
        ]);
    }

    public function getAttachments(): array {
        $stmt = self::$db->prepare(
            "SELECT * FROM project_attachments WHERE project_id = :id"
        );
        $stmt->execute(['id' => $this->id]);
        return $stmt->fetchAll();
    }

    public function updateStatus(string $status): bool {
        $allowed = ['planned', 'in_progress', 'completed', 'archived'];
        if (!in_array($status, $allowed)) {
            return false;
        }

        $stmt = self::$db->prepare(
            "UPDATE projects SET status = :status WHERE id = :id"
        );
        return $stmt->execute(['id' => $this->id, 'status' => $status]);
    }
    public function delete(): bool {
        $stmt = self::$db->prepare("DELETE FROM projects WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }
}
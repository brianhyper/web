<?php
declare(strict_types=1);

class Client {
    public $id;
    public $user_id;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $company;
    public $notes;
    public $created_at;
    public $updated_at;

    private static $db;

    public function __construct() {
        self::$db = Database::getInstance();
    }

    public function save(): int {
        $data = [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'company' => $this->company,
            'notes' => $this->notes
        ];

        if ($this->id) {
            $stmt = self::$db->prepare(
                "UPDATE clients SET 
                 name = :name, email = :email, phone = :phone,
                 address = :address, company = :company, notes = :notes
                 WHERE id = {$this->id}"
            );
            $stmt->execute($data);
            return $this->id;
        }

        $stmt = self::$db->prepare(
            "INSERT INTO clients (user_id, name, email, phone, address, company, notes, created_at)
             VALUES (:user_id, :name, :email, :phone, :address, :company, :notes, NOW())"
        );
        $stmt->execute($data);
        $this->id = self::$db->lastInsertId();
        return $this->id;
    }

    public static function find(int $id): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function allForUser(int $userId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM clients WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function search(int $userId, string $query): array {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM clients 
             WHERE user_id = :user_id 
             AND (name LIKE :query OR email LIKE :query OR company LIKE :query)"
        );
        $stmt->execute([
            'user_id' => $userId,
            'query' => '%' . $query . '%'
        ]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function delete(): bool {
        $stmt = self::$db->prepare("DELETE FROM clients WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }
}
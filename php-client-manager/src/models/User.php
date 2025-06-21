<?php
declare(strict_types=1);

class User {
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $avatar;
    public $verified = false;
    public $verification_token;
    public $token_expiry;
    public $created_at;
    public $updated_at;

    private static $db;

    public function __construct() {
        self::$db = Database::getInstance();
    }

    public function setPassword(string $password): void {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }

    public function save(): int {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'verified' => (int)$this->verified,
            'token' => $this->verification_token,
            'expiry' => $this->token_expiry
        ];

        if ($this->id) {
            $stmt = self::$db->prepare(
                "UPDATE users SET 
                 name = :name, email = :email, password = :password, role = :role,
                 avatar = :avatar, verified = :verified, 
                 verification_token = :token, token_expiry = :expiry
                 WHERE id = {$this->id}"
            );
            $stmt->execute($data);
            return $this->id;
        }

        $stmt = self::$db->prepare(
            "INSERT INTO users (name, email, password, role, avatar, verified, 
             verification_token, token_expiry, created_at)
             VALUES (:name, :email, :password, :role, :avatar, :verified, 
             :token, :expiry, NOW())"
        );
        $stmt->execute($data);
        $this->id = self::$db->lastInsertId();
        return $this->id;
    }

    public function verifyEmail(): void {
        $stmt = self::$db->prepare(
            "UPDATE users SET verified = 1, 
             verification_token = NULL, token_expiry = NULL 
             WHERE id = :id"
        );
        $stmt->execute(['id' => $this->id]);
        $this->verified = true;
    }

    public static function find(int $id): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function findByEmail(string $email): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function findByToken(string $token): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM users 
             WHERE verification_token = :token 
             AND token_expiry > NOW()"
        );
        $stmt->execute(['token' => $token]);
        return $stmt->fetchObject(self::class) ?: null;
    }

    public static function isEmailTaken(string $email): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
}
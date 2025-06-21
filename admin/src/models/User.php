<?php
namespace App\models;

use App\config\Database;
use App\helpers\Sanitizer;
use PDO;
use PDOException;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));
        $tokenExpires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $stmt = $this->db->prepare("
            INSERT INTO users 
            (name, email, password, verification_token, token_expires) 
            VALUES 
            (:name, :email, :password, :token, :expires)
        ");
        
        $stmt->execute([
            ':name' => Sanitizer::sanitize($name),
            ':email' => Sanitizer::sanitize($email),
            ':password' => $hashedPassword,
            ':token' => $verificationToken,
            ':expires' => $tokenExpires
        ]);
        
        return $this->db->lastInsertId();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM users 
            WHERE email = :email 
            AND deleted_at IS NULL
        ");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyEmail($token) {
        $stmt = $this->db->prepare("
            SELECT id 
            FROM users 
            WHERE verification_token = :token 
            AND token_expires > NOW() 
            AND deleted_at IS NULL
        ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $update = $this->db->prepare("
                UPDATE users 
                SET is_verified = 1, 
                    verification_token = NULL, 
                    token_expires = NULL 
                WHERE id = :id
            ");
            return $update->execute([':id' => $user['id']]);
        }
        return false;
    }

    public function updatePassword($userId, $newHash) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = :password 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':password' => $newHash,
            ':id' => $userId
        ]);
    }

    public function generatePasswordResetToken($email) {
        $user = $this->findByEmail($email);
        if (!$user) return false;
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET reset_token = :token, 
                reset_expires = :expires 
            WHERE id = :id
        ");
        
        $success = $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':id' => $user['id']
        ]);
        
        return $success ? $token : false;
    }

    public function resetPassword($token, $newPassword) {
        $stmt = $this->db->prepare("
            SELECT id 
            FROM users 
            WHERE reset_token = :token 
            AND reset_expires > NOW() 
            AND deleted_at IS NULL
        ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $update = $this->db->prepare("
                UPDATE users 
                SET password = :password, 
                    reset_token = NULL, 
                    reset_expires = NULL 
                WHERE id = :id
            ");
            return $update->execute([
                ':password' => $newHash,
                ':id' => $user['id']
            ]);
        }
        return false;
    }

    public function updateProfile($userId, $name, $email) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = :name, 
                email = :email 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name' => Sanitizer::sanitize($name),
            ':email' => Sanitizer::sanitize($email),
            ':id' => $userId
        ]);
    }

    public function assignRole($userId, $role) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET role = :role 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':role' => $role,
            ':id' => $userId
        ]);
    }

    public function delete($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET deleted_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $userId]);
    }

    public function getById($userId) {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM users 
            WHERE id = :id 
            AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
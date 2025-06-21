<?php
namespace App\models;

use App\config\Database;
use App\helpers\Sanitizer;
use PDO;
use PDOException;

class Client {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data) {
        $stmt = $this->db->prepare("
            INSERT INTO clients 
            (name, email, phone, company, address, notes, created_by) 
            VALUES 
            (:name, :email, :phone, :company, :address, :notes, :created_by)
        ");
        
        $stmt->execute([
            ':name' => Sanitizer::sanitize($data['name']),
            ':email' => Sanitizer::sanitize($data['email']),
            ':phone' => Sanitizer::sanitize($data['phone']),
            ':company' => Sanitizer::sanitize($data['company']),
            ':address' => Sanitizer::sanitize($data['address']),
            ':notes' => Sanitizer::sanitize($data['notes']),
            ':created_by' => $data['created_by']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, array $data, $userId, $userRole) {
        $sql = "UPDATE clients SET 
                name = :name, 
                email = :email, 
                phone = :phone, 
                company = :company, 
                address = :address, 
                notes = :notes 
                WHERE id = :id";
        
        // Restrict update to owner unless admin
        if ($userRole !== 'admin') {
            $sql .= " AND created_by = :user_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            ':id' => $id,
            ':name' => Sanitizer::sanitize($data['name']),
            ':email' => Sanitizer::sanitize($data['email']),
            ':phone' => Sanitizer::sanitize($data['phone']),
            ':company' => Sanitizer::sanitize($data['company']),
            ':address' => Sanitizer::sanitize($data['address']),
            ':notes' => Sanitizer::sanitize($data['notes'])
        ];
        
        if ($userRole !== 'admin') {
            $params[':user_id'] = $userId;
        }
        
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        // Use soft delete instead of physical delete
        $stmt = $this->db->prepare("
            UPDATE clients 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND created_by = :user_id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    public function getById($id, $userId, $userRole) {
        $sql = "SELECT * FROM clients WHERE id = :id AND deleted_at IS NULL";
        
        if ($userRole !== 'admin') {
            $sql .= " AND created_by = :user_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        $params = [':id' => $id];
        if ($userRole !== 'admin') {
            $params[':user_id'] = $userId;
        }
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($userId, $userRole, $search = '', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM clients WHERE deleted_at IS NULL";
        $params = [];
        
        if ($userRole !== 'admin') {
            $sql .= " AND created_by = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR email LIKE :search OR company LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :offset, :perPage";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($term, $userId, $userRole) {
        $sql = "SELECT id, name, email, phone, company 
                FROM clients 
                WHERE deleted_at IS NULL 
                AND (name LIKE :term OR email LIKE :term OR company LIKE :term)";
        
        if ($userRole !== 'admin') {
            $sql .= " AND created_by = :user_id";
        }
        
        $sql .= " LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [':term' => "%$term%"];
        if ($userRole !== 'admin') {
            $params[':user_id'] = $userId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count($userId, $userRole, $search = '') {
        $sql = "SELECT COUNT(*) AS count FROM clients WHERE deleted_at IS NULL";
        $params = [];
        
        if ($userRole !== 'admin') {
            $sql .= " AND created_by = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR email LIKE :search OR company LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    }
}
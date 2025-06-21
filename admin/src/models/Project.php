<?php
namespace App\models;

use App\config\Database;
use App\helpers\Sanitizer;
use PDO;
use PDOException;

class Project {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data) {
        $stmt = $this->db->prepare("
            INSERT INTO projects 
            (title, description, client_id, status, budget, start_date, end_date, created_by) 
            VALUES 
            (:title, :description, :client_id, :status, :budget, :start_date, :end_date, :created_by)
        ");
        
        $stmt->execute([
            ':title' => Sanitizer::sanitize($data['title']),
            ':description' => Sanitizer::sanitize($data['description']),
            ':client_id' => (int)$data['client_id'],
            ':status' => Sanitizer::sanitize($data['status']),
            ':budget' => (float)$data['budget'],
            ':start_date' => Sanitizer::sanitize($data['start_date']),
            ':end_date' => Sanitizer::sanitize($data['end_date']),
            ':created_by' => $data['created_by']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, array $data, $userId, $userRole) {
        $sql = "UPDATE projects SET 
                title = :title, 
                description = :description, 
                client_id = :client_id, 
                status = :status, 
                budget = :budget, 
                start_date = :start_date, 
                end_date = :end_date 
                WHERE id = :id";
        
        // Restrict update to owner unless admin
        if ($userRole !== 'admin') {
            $sql .= " AND created_by = :user_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            ':id' => $id,
            ':title' => Sanitizer::sanitize($data['title']),
            ':description' => Sanitizer::sanitize($data['description']),
            ':client_id' => (int)$data['client_id'],
            ':status' => Sanitizer::sanitize($data['status']),
            ':budget' => (float)$data['budget'],
            ':start_date' => Sanitizer::sanitize($data['start_date']),
            ':end_date' => Sanitizer::sanitize($data['end_date'])
        ];
        
        if ($userRole !== 'admin') {
            $params[':user_id'] = $userId;
        }
        
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        // Soft delete project
        $stmt = $this->db->prepare("
            UPDATE projects 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND created_by = :user_id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    public function getById($id, $userId, $userRole) {
        $sql = "SELECT p.*, c.name AS client_name 
                FROM projects p
                JOIN clients c ON p.client_id = c.id
                WHERE p.id = :id AND p.deleted_at IS NULL AND c.deleted_at IS NULL";
        
        if ($userRole !== 'admin') {
            $sql .= " AND p.created_by = :user_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        $params = [':id' => $id];
        if ($userRole !== 'admin') {
            $params[':user_id'] = $userId;
        }
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($userId, $userRole, $status = '', $clientId = '', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name AS client_name 
                FROM projects p
                JOIN clients c ON p.client_id = c.id
                WHERE p.deleted_at IS NULL AND c.deleted_at IS NULL";
        $params = [];
        
        if ($userRole !== 'admin') {
            $sql .= " AND p.created_by = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if (!empty($status)) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($clientId)) {
            $sql .= " AND p.client_id = :client_id";
            $params[':client_id'] = $clientId;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :offset, :perPage";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByClient($clientId, $userId, $userRole) {
        $sql = "SELECT * FROM projects 
                WHERE client_id = :client_id 
                AND deleted_at IS NULL";
        
        if ($userRole !== 'admin') {
            $sql .= " AND created_by = :user_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        $params = [':client_id' => $clientId];
        if ($userRole !== 'admin') {
            $params[':user_id'] = $userId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentProjects($limit, $userId, $userRole) {
        $sql = "SELECT p.id, p.title, p.status, c.name AS client_name 
                FROM projects p
                JOIN clients c ON p.client_id = c.id
                WHERE p.deleted_at IS NULL AND c.deleted_at IS NULL";
        
        if ($userRole !== 'admin') {
            $sql .= " AND p.created_by = :user_id";
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [':limit' => (int)$limit];
        if ($userRole !== 'admin') {
            $params[':user_id'] = $userId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count($userId, $userRole, $status = '', $clientId = '') {
        $sql = "SELECT COUNT(*) AS count 
                FROM projects p
                WHERE p.deleted_at IS NULL";
        $params = [];
        
        if ($userRole !== 'admin') {
            $sql .= " AND p.created_by = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if (!empty($status)) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($clientId)) {
            $sql .= " AND p.client_id = :client_id";
            $params[':client_id'] = $clientId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    }

    public function getTeamMembers($projectId) {
        // This would normally query a project_members table
        // For now, return an empty array
        return [];
    }
}
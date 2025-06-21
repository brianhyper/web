<?php
declare(strict_types=1);

class ClientController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int {
        // Validate input
        $name = Security::sanitize($data['name'] ?? '');
        $email = Security::sanitize($data['email'] ?? '');
        $phone = Security::sanitize($data['phone'] ?? '');
        
        if (empty($name)) {
            throw new Exception('Client name is required');
        }
        
        if (!empty($email) && !Security::validateEmail($email)) {
            throw new Exception('Invalid email format');
        }
        
        // Insert client
        $stmt = $this->db->prepare(
            "INSERT INTO clients (user_id, name, email, phone, address, created_at)
             VALUES (:user_id, :name, :email, :phone, :address, NOW())"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => Security::sanitize($data['address'] ?? '')
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        // Verify client belongs to user
        $this->verifyOwnership($id);
        
        $stmt = $this->db->prepare(
            "UPDATE clients SET 
             name = :name, 
             email = :email, 
             phone = :phone, 
             address = :address
             WHERE id = :id"
        );
        
        return $stmt->execute([
            'id' => $id,
            'name' => Security::sanitize($data['name']),
            'email' => Security::sanitize($data['email']),
            'phone' => Security::sanitize($data['phone']),
            'address' => Security::sanitize($data['address'])
        ]);
    }

    public function delete(int $id): bool {
        $this->verifyOwnership($id);
        
        $stmt = $this->db->prepare(
            "DELETE FROM clients WHERE id = :id"
        );
        
        return $stmt->execute(['id' => $id]);
    }

    public function get(int $id): array {
        $this->verifyOwnership($id);
        
        $stmt = $this->db->prepare(
            "SELECT * FROM clients WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function search(string $query): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM clients 
             WHERE user_id = :user_id 
             AND (name LIKE :query OR email LIKE :query)"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'query' => '%' . Security::sanitize($query) . '%'
        ]);
        
        return $stmt->fetchAll();
    }

    private function verifyOwnership(int $clientId): void {
        $stmt = $this->db->prepare(
            "SELECT user_id FROM clients WHERE id = :id"
        );
        $stmt->execute(['id' => $clientId]);
        $client = $stmt->fetch();
        
        if (!$client || $client['user_id'] !== $_SESSION['user_id']) {
            throw new Exception('Client not found or access denied');
        }
    }
}
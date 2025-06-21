<?php
declare(strict_types=1);

class ProjectController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int {
        $required = ['client_id', 'title', 'deadline'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }
        
        // Verify client ownership
        (new ClientController())->verifyOwnership($data['client_id']);
        
        $stmt = $this->db->prepare(
            "INSERT INTO projects (user_id, client_id, title, description, status, deadline, created_at)
             VALUES (:user_id, :client_id, :title, :description, 'planned', :deadline, NOW())"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'client_id' => (int)$data['client_id'],
            'title' => Security::sanitize($data['title']),
            'description' => Security::sanitize($data['description'] ?? ''),
            'deadline' => $data['deadline']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool {
        $allowed = ['planned', 'in_progress', 'completed', 'archived'];
        if (!in_array($status, $allowed)) {
            throw new Exception('Invalid status');
        }
        
        $this->verifyOwnership($id);
        
        $stmt = $this->db->prepare(
            "UPDATE projects SET status = :status WHERE id = :id"
        );
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public function addAttachment(int $projectId, array $file): bool {
        $this->verifyOwnership($projectId);
        
        if (!Security::validateFileUpload($file)) {
            throw new Exception('Invalid file type');
        }
        
        $fileName = $this->saveFile($file);
        
        $stmt = $this->db->prepare(
            "INSERT INTO project_attachments (project_id, file_name, original_name)
             VALUES (:project_id, :file_name, :original_name)"
        );
        
        return $stmt->execute([
            'project_id' => $projectId,
            'file_name' => $fileName,
            'original_name' => $file['name']
        ]);
    }

    private function verifyOwnership(int $projectId): void {
        $stmt = $this->db->prepare(
            "SELECT user_id FROM projects WHERE id = :id"
        );
        $stmt->execute(['id' => $projectId]);
        $project = $stmt->fetch();
        
        if (!$project || $project['user_id'] !== $_SESSION['user_id']) {
            throw new Exception('Project not found or access denied');
        }
    }
    
    private function saveFile(array $file): string {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('proj_') . '.' . $ext;
        $targetPath = UPLOAD_PATH . '/' . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('File upload failed');
        }
        
        return $fileName;
    }
}
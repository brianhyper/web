<?php
declare(strict_types=1);

class ChatBotController {
    public function processMessage(string $message, int $userId): array {
        $message = trim(strtolower($message));
        
        // Basic command processing
        if (strpos($message, 'client') !== false) {
            return $this->clientSearch($message);
        }
        
        if (strpos($message, 'project status') !== false) {
            return $this->projectStatus($message);
        }
        
        return [
            'response' => "I can help with: client info, project status. Ask me anything!",
            'type' => 'text'
        ];
    }
    
    private function clientSearch(string $query): array {
        preg_match('/client (.*)/', $query, $matches);
        $searchTerm = $matches[1] ?? '';
        
        if (empty($searchTerm)) {
            return ['response' => 'Please specify a client name', 'type' => 'text'];
        }
        
        $clients = (new ClientController())->search($searchTerm);
        
        if (empty($clients)) {
            return ['response' => 'No clients found', 'type' => 'text'];
        }
        
        $response = "Clients found:\n";
        foreach ($clients as $client) {
            $response .= "- {$client['name']}: {$client['email']}\n";
        }
        
        return [
            'response' => $response,
            'type' => 'text'
        ];
    }
    
    private function projectStatus(string $query): array {
        preg_match('/project status (.*)/', $query, $matches);
        $projectName = $matches[1] ?? '';
        
        if (empty($projectName)) {
            return ['response' => 'Please specify a project name', 'type' => 'text'];
        }
        
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT title, status, deadline 
             FROM projects 
             WHERE user_id = :user_id 
             AND title LIKE :title"
        );
        
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'title' => '%' . Security::sanitize($projectName) . '%'
        ]);
        
        $project = $stmt->fetch();
        
        if (!$project) {
            return ['response' => 'Project not found', 'type' => 'text'];
        }
        
        $status = ucfirst(str_replace('_', ' ', $project['status']));
        $deadline = date('M j, Y', strtotime($project['deadline']));
        
        return [
            'response' => "Project: {$project['title']}\nStatus: {$status}\nDeadline: {$deadline}",
            'type' => 'text'
        ];
    }
}
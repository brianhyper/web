<?php
declare(strict_types=1);

class ChatBot {
    private $commands = [
        'client' => 'handleClientSearch',
        'project' => 'handleProjectStatus',
        'invoice' => 'handleInvoiceStatus',
        'help' => 'handleHelp'
    ];
    
    public function processMessage(string $message, int $userId): array {
        $message = trim(strtolower($message));
        
        // Check for specific commands
        foreach ($this->commands as $trigger => $method) {
            if (strpos($message, $trigger) !== false) {
                return $this->{$method}($message, $userId);
            }
        }
        
        // Default response
        return [
            'type' => 'text',
            'content' => "I can help with client info, project status, and invoices. Try: 'Show client John'"
        ];
    }
    
    private function handleClientSearch(string $message, int $userId): array {
        preg_match('/client (.*)/', $message, $matches);
        $searchTerm = $matches[1] ?? '';
        
        if (empty($searchTerm)) {
            return [
                'type' => 'text',
                'content' => 'Please specify a client name'
            ];
        }
        
        $clients = (new ClientController())->search($searchTerm, $userId);
        
        if (empty($clients)) {
            return [
                'type' => 'text',
                'content' => 'No clients found'
            ];
        }
        
        return [
            'type' => 'list',
            'content' => array_map(function($client) {
                return [
                    'name' => $client['name'],
                    'email' => $client['email'],
                    'phone' => $client['phone']
                ];
            }, $clients)
        ];
    }
    
    private function handleProjectStatus(string $message, int $userId): array {
        preg_match('/project (.*)/', $message, $matches);
        $projectName = $matches[1] ?? '';
        
        if (empty($projectName)) {
            return [
                'type' => 'text',
                'content' => 'Please specify a project name'
            ];
        }
        
        $project = (new ProjectController())->findByName($projectName, $userId);
        
        if (!$project) {
            return [
                'type' => 'text',
                'content' => 'Project not found'
            ];
        }
        
        $status = ucfirst(str_replace('_', ' ', $project['status']));
        $deadline = date('M j, Y', strtotime($project['deadline']));
        
        return [
            'type' => 'card',
            'content' => [
                'title' => $project['title'],
                'fields' => [
                    ['name' => 'Status', 'value' => $status],
                    ['name' => 'Deadline', 'value' => $deadline],
                    ['name' => 'Client', 'value' => $project['client_name']]
                ]
            ]
        ];
    }
    
    private function handleHelp(string $message): array {
        return [
            'type' => 'text',
            'content' => "Available commands:\n"
                . "- Show client [name]\n"
                . "- Project status [name]\n"
                . "- Invoice status [number]\n"
                . "- Help"
        ];
    }
    
    public function logConversation(int $userId, string $message, array $response): void {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO chat_logs (user_id, message, response, created_at)
             VALUES (:user_id, :message, :response, NOW())"
        );
        
        $stmt->execute([
            'user_id' => $userId,
            'message' => $message,
            'response' => json_encode($response)
        ]);
    }
}
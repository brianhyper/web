<?php
declare(strict_types=1);

class CSRFMiddleware {
    public function handle(): void {
        // Only validate non-GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get token from request
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        // Validate token
        if (empty($token)) {
            $this->rejectRequest('CSRF token missing');
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $this->rejectRequest('CSRF session token not found');
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            $this->rejectRequest('Invalid CSRF token');
        }
        
        // Regenerate token after successful validation
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    private function rejectRequest(string $message): void {
        error_log("CSRF validation failed: $message");
        
        // Log potential CSRF attack
        Logger::warning("CSRF attempt detected", [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'request_uri' => $_SERVER['REQUEST_URI']
        ]);
        
        // Respond with error
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Security validation failed']);
        exit;
    }
}
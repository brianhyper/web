<?php
declare(strict_types=1);

class RoleMiddleware {
    private $requiredRoles;
    
    public function __construct(array $requiredRoles) {
        $this->requiredRoles = $requiredRoles;
    }
    
    public function handle(): void {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is authenticated
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Get current user role
        $userRole = $_SESSION['user_role'] ?? 'guest';
        
        // Check if user has required role
        if (!in_array($userRole, $this->requiredRoles, true)) {
            // Log unauthorized access attempt
            Logger::warning("Unauthorized access attempt", [
                'user_id' => $_SESSION['user_id'],
                'required_roles' => $this->requiredRoles,
                'current_role' => $userRole,
                'request_uri' => $_SERVER['REQUEST_URI']
            ]);
            
            // Show access denied
            http_response_code(403);
            include __DIR__ . '/../../frontend/pages/403.php';
            exit;
        }
    }
}
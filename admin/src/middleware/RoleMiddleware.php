<?php
// src/middleware/RoleMiddleware.php
namespace App\middleware;

use App\helpers\Logger;
use App\config\Constants;

class RoleMiddleware {
    public function handle($requiredRole = null) {
        // If no specific role required, just pass through
        if ($requiredRole === null) {
            return;
        }
        
        // Check if user is authenticated
        if (empty($_SESSION['user_id'])) {
            Logger::log("Role check failed - user not authenticated", [
                'required_role' => $requiredRole,
                'path' => $_SERVER['REQUEST_URI']
            ]);
            
            $_SESSION['error'] = "Authentication required for this action";
            header('Location: /login');
            exit;
        }
        
        // Check if user has the required role
        $userRole = $_SESSION['user_role'] ?? '';
        if ($userRole !== $requiredRole) {
            Logger::log("Insufficient privileges", [
                'user_id' => $_SESSION['user_id'],
                'user_role' => $userRole,
                'required_role' => $requiredRole,
                'path' => $_SERVER['REQUEST_URI']
            ]);
            
            http_response_code(403);
            include __DIR__ . '/../../src/views/errors/403.php';
            exit;
        }
    }
}
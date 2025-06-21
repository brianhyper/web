<?php
// src/middleware/CSRFMiddleware.php
namespace App\middleware;

use App\helpers\Logger;
use App\helpers\CSRF;
use App\config\Constants;

class CSRFMiddleware {
    public function handle() {
        // Only validate POST, PUT, PATCH, DELETE requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }
        
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        try {
            CSRF::validateToken($token);
        } catch (\Exception $e) {
            Logger::log("CSRF validation failed", [
                'user_id' => $_SESSION['user_id'] ?? 'guest',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'path' => $_SERVER['REQUEST_URI'],
                'error' => $e->getMessage()
            ]);
            
            http_response_code(403);
            echo "CSRF token validation failed. This request has been blocked for security reasons.";
            exit;
        }
        
        // Generate new token for next request
        $_SESSION['csrf_token'] = CSRF::generateToken();
    }
}
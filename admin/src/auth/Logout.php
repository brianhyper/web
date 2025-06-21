<?php
// src/auth/Logout.php
namespace App\auth;

use App\auth\SessionManager;

class Logout {
    public function handleLogout() {
        // Start session to access session variables
        SessionManager::startSecureSession();
        
        // Unset all session variables
        $_SESSION = [];
        
        // Clear remember me cookie
        $this->clearRememberMeCookie();
        
        // Destroy session
        SessionManager::destroySession();
        
        // Redirect to login page
        header('Location: /login');
        exit;
    }
    
    private function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            list($selector, $validator) = explode(':', $_COOKIE['remember_me']);
            
            // Delete token from database
            $db = \App\config\Database::getInstance();
            $stmt = $db->prepare("DELETE FROM remember_tokens WHERE selector = :selector");
            $stmt->execute([':selector' => $selector]);
            
            // Clear cookie
            setcookie(
                'remember_me',
                '',
                time() - 3600,
                '/',
                $_SERVER['HTTP_HOST'],
                isset($_SERVER['HTTPS']),
                true
            );
        }
    }
}
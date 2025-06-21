<?php
// src/middleware/AuthMiddleware.php
namespace App\middleware;

use App\helpers\Logger;
use App\config\Constants;

class AuthMiddleware {
    public function handle() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => 86400, // 1 day
                'cookie_secure' => isset($_SERVER['HTTPS']),
                'cookie_httponly' => true,
                'use_strict_mode' => true
            ]);
        }
        
        // Check if user is logged in
        if (empty($_SESSION['user_id'])) {
            Logger::log("Unauthenticated access attempt", [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'path' => $_SERVER['REQUEST_URI']
            ]);
            
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            $_SESSION['error'] = "You must be logged in to access this page";
            header('Location: /web/admin/src/auth/Login.php');
            exit;
        }
        
        // Check if email is verified
        if (empty($_SESSION['is_verified']) || !$_SESSION['is_verified']) {
            Logger::log("Unverified access attempt", [
                'user_id' => $_SESSION['user_id'],
                'path' => $_SERVER['REQUEST_URI']
            ]);
            
            $_SESSION['error'] = "Please verify your email address before accessing this page";
            header('Location: /web/admin/src/auth/Login.php');
            exit;
        }
        
        // Check session age
        $maxInactivity = Constants::SESSION_MAX_INACTIVITY;
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > $maxInactivity)) {
            $this->logoutDueToInactivity();
        }
        
        // Check user agent consistency
        if (isset($_SESSION['user_agent'])) {
            if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                Logger::log("User agent changed - possible session hijacking", [
                    'user_id' => $_SESSION['user_id'],
                    'old_agent' => $_SESSION['user_agent'],
                    'new_agent' => $_SERVER['HTTP_USER_AGENT']
                ]);
                $this->logoutDueToSecurity();
            }
        } else {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
    
    private function logoutDueToInactivity() {
        Logger::log("Session expired due to inactivity", [
            'user_id' => $_SESSION['user_id'],
            'last_activity' => $_SESSION['last_activity']
        ]);
        
        session_unset();
        session_destroy();
        
        $_SESSION['error'] = "Your session has expired due to inactivity. Please log in again.";
        header('Location: /web/admin/src/auth/Login.php');
        exit;
    }
    
    private function logoutDueToSecurity() {
        Logger::log("Session terminated for security reasons", [
            'user_id' => $_SESSION['user_id'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        session_unset();
        session_destroy();
        
        $_SESSION['error'] = "Your session was terminated for security reasons. Please log in again.";
        header('Location: /web/admin/src/auth/Login.php');
        exit;
    }
}
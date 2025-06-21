<?php
// src/auth/SessionManager.php
namespace App\auth;

class SessionManager {
    public static function startSecureSession() {
        // Configure session settings
        $sessionConfig = [
            'name' => 'SECURE_SESSION',
            'cookie_lifetime' => 86400, // 1 day
            'cookie_secure' => isset($_SERVER['HTTPS']), // Only send cookies over HTTPS
            'cookie_httponly' => true, // Prevent JavaScript access
            'use_strict_mode' => true, // Prevents session fixation
            'use_only_cookies' => 1, // Only use cookies for session
            'sid_length' => 128, // Strong session ID length
            'sid_bits_per_character' => 6 // More entropy
        ];

        // Apply configuration
        session_start($sessionConfig);

        // Regenerate session ID to prevent fixation
        if (empty($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }

    public static function destroySession() {
        // Unset all session variables
        $_SESSION = [];

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();
    }

    public static function validateSession() {
        // Check session age
        $maxSessionAge = 1800; // 30 minutes
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > $maxSessionAge)) {
            self::destroySession();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        // Check user agent consistency
        if (isset($_SESSION['user_agent'])) {
            if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                self::destroySession();
                return false;
            }
        } else {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        // Check IP address (optional, can cause issues with dynamic IPs)
        if (isset($_SESSION['ip_address'])) {
            if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
                error_log("IP address changed: {$_SESSION['ip_address']} to {$_SERVER['REMOTE_ADDR']}");
                // Comment out in production if users have dynamic IPs
                // self::destroySession();
                // return false;
            }
        } else {
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
        
        return true;
    }
}
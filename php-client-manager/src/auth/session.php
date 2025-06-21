<?php
declare(strict_types=1);

class SessionManager {
    public static function start(): void {
        // Set secure session parameters
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        // Regenerate ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    public static function validate(): bool {
        // Check if user is logged in
        if (empty($_SESSION['user_id'])) {
            return false;
        }
        
        // Validate session fingerprint
        $fingerprint = self::getFingerprint();
        if ($_SESSION['fingerprint'] !== $fingerprint) {
            self::destroy();
            return false;
        }
        
        return true;
    }
    
    public static function initSessionData(): void {
        if (!isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = self::getFingerprint();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
    
    private static function getFingerprint(): string {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return hash('sha256', $ip . $agent . getenv('APP_KEY'));
    }
    
    public static function destroy(): void {
        $_SESSION = [];
        if (session_id()) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
    }
}
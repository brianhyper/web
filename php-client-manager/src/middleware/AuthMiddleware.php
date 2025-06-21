<?php
declare(strict_types=1);

class AuthMiddleware {
    public function handle(): void {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is authenticated
        if (empty($_SESSION['user_id'])) {
            // Save requested URL for redirect after login
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            
            // Set error message
            $_SESSION['error'] = 'You must be logged in to access this page';
            
            // Redirect to login
            header('Location: /login');
            exit;
        }
        
        // Validate session fingerprint
        $this->validateSessionFingerprint();
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
    
    private function validateSessionFingerprint(): void {
        $currentFingerprint = $this->generateSessionFingerprint();
        
        if (!isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = $currentFingerprint;
            return;
        }
        
        if ($_SESSION['fingerprint'] !== $currentFingerprint) {
            // Possible session hijacking detected
            session_regenerate_id(true);
            session_unset();
            session_destroy();
            
            $_SESSION['error'] = 'Session security violation detected. Please login again.';
            header('Location: /login');
            exit;
        }
    }
    
    private function generateSessionFingerprint(): string {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return hash('sha256', $ip . $agent . getenv('APP_KEY'));
    }
}
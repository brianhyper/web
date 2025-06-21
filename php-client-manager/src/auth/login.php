<?php
declare(strict_types=1);

require __DIR__ . '/../../src/config/bootstrap.php';

use App\Helpers\Security;
use App\Models\User;
use App\Helpers\Logger;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

// Validate CSRF token
if (!Security::validateCsrf($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: /login');
    exit;
}

$email = Security::sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

try {
    $user = User::findByEmail($email);
    
    // Validate credentials
    if (!$user || !$user->verifyPassword($password)) {
        throw new Exception('Invalid credentials');
    }
    
    // Check verification status
    if (!$user->verified) {
        throw new Exception('Account not verified. Check your email');
    }

    // Regenerate session ID
    session_regenerate_id(true);
    
    // Set session data
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_role'] = $user->role;
    $_SESSION['user_name'] = $user->name;
    
    // Extend session for "remember me"
    if ($remember) {
        $sessionLifetime = 30 * 24 * 60 * 60; // 30 days
        setcookie(
            session_name(), 
            session_id(), 
            time() + $sessionLifetime, 
            '/', 
            '', 
            true, 
            true
        );
    }
    
    // Log successful login
    Logger::info("User logged in: {$email}", ['user_id' => $user->id]);
    
    header('Location: /dashboard');
} catch (Exception $e) {
    // Log failed attempt
    Logger::warning("Failed login: {$email}", ['error' => $e->getMessage()]);
    
    $_SESSION['error'] = $e->getMessage();
    header('Location: /login');
}
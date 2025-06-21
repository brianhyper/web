<?php
// public/verify.php
require_once __DIR__ . '/../src/config/App.php';
require_once __DIR__ . '/../src/config/Database.php';
require_once __DIR__ . '/../src/models/User.php';

use App\config\App;
use App\models\User;

// Initialize application
App::init();

// Start secure session
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

// Get token from URL
$token = $_GET['token'] ?? '';

// Validate token
if (empty($token)) {
    $_SESSION['error'] = "Invalid verification link";
    header('Location: /web/admin/src/auth/login.php');
    exit;
}

try {
    // Verify token
    $userModel = new User();
    $verificationResult = $userModel->verifyEmail($token);
    
    if ($verificationResult) {
        $_SESSION['success'] = "Email verified successfully! You can now login.";
    } else {
        $_SESSION['error'] = "Verification failed. Link may be expired or invalid.";
    }
} catch (Exception $e) {
    error_log("Verification error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred during verification. Please try again.";
}

// Redirect to login
header('Location: /web/admin/src/auth/login.php');
exit;
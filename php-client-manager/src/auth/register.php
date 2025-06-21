<?php
declare(strict_types=1);

require __DIR__ . '/../../src/config/bootstrap.php';

use App\Helpers\Security;
use App\Helpers\Mailer;
use App\Helpers\Token;
use App\Models\User;
use App\Helpers\Logger;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

// Validate CSRF token
if (!Security::validateCsrf($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: /register');
    exit;
}

// Sanitize inputs
$name = Security::sanitize($_POST['name'] ?? '');
$email = Security::sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

// Validate inputs
$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (empty($email) || !Security::validateEmail($email)) $errors[] = 'Valid email is required';
if (User::isEmailTaken($email)) $errors[] = 'Email is already registered';
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
if ($password !== $passwordConfirm) $errors[] = 'Passwords do not match';

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = ['name' => $name, 'email' => $email];
    header('Location: /register');
    exit;
}

try {
    // Create user
    $user = new User();
    $user->name = $name;
    $user->email = $email;
    $user->setPassword($password);
    $user->role = 'user';
    $user->verification_token = Token::generate('verification');
    $user->token_expiry = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);
    $userId = $user->save();

    // Send verification email
    $verificationLink = getenv('APP_URL') . "/verify?token={$user->verification_token}";
    $subject = "Verify Your Account";
    $body = "Hello {$name},\n\nPlease verify your account: {$verificationLink}\n\n";
    $body .= "This link expires in " . (TOKEN_EXPIRY / 60) . " minutes";

    Mailer::send($email, $subject, $body);
    
    // Log registration
    Logger::info("User registered: {$email}", ['user_id' => $userId]);
    
    $_SESSION['success'] = 'Registration successful! Check your email for verification link';
    header('Location: /login');
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = 'Registration failed';
    header('Location: /register');
}
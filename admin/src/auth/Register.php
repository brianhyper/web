<?php
// src/auth/Register.php
namespace App\auth;

use App\helpers\Mailer;
use App\helpers\Sanitizer;
use App\models\User;
use App\middleware\CSRFMiddleware;

class Register {
    public function showForm() {
        // Generate CSRF token
        $token = CSRFMiddleware::generateToken();
        include __DIR__ . '/../../views/auth/register.php';
    }

    public function handleRegistration() {
        try {
            // Validate CSRF token
            CSRFMiddleware::validateToken($_POST['csrf_token'] ?? '');

            // Sanitize inputs
            $name = Sanitizer::sanitize($_POST['name']);
            $email = Sanitizer::sanitize($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            // Validation
            $errors = [];
            if (empty($name)) $errors[] = "Name is required";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
            if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
            if ($password !== $confirmPassword) $errors[] = "Passwords do not match";

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                header('Location: /register');
                exit;
            }

            // Create user
            $userModel = new User();
            if ($userModel->findByEmail($email)) {
                $_SESSION['errors'] = ["Email already registered"];
                header('Location: /register');
                exit;
            }

            $userId = $userModel->create($name, $email, $password);
            
            // Send verification email
            $verificationToken = $userModel->generateVerificationToken($userId);
            $this->sendVerificationEmail($email, $name, $verificationToken);

            $_SESSION['success'] = "Registration successful! Please check your email to verify your account.";
            header('Location: /login');
            exit;
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed. Please try again."];
            header('Location: /register');
            exit;
        }
    }

    private function sendVerificationEmail($email, $name, $token) {
        $verificationLink = getenv('APP_URL') . "/verify.php?token=$token";
        
        $subject = "Verify Your Email Address";
        $message = "
            <html>
            <head>
                <title>Email Verification</title>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f9; }
                    .container { max-width: 600px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                    .header { background-color: #3498db; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { padding: 20px; }
                    .button { display: inline-block; padding: 12px 24px; background-color: #3498db; color: white !important; text-decoration: none; border-radius: 4px; font-weight: bold; }
                    .footer { margin-top: 20px; text-align: center; color: #777; font-size: 0.9em; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Welcome to Client Manager!</h2>
                    </div>
                    <div class='content'>
                        <p>Hi $name,</p>
                        <p>Please verify your email address to complete your registration:</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='$verificationLink' class='button'>Verify Email</a>
                        </p>
                        <p>If you didn't create an account, you can safely ignore this email.</p>
                        <p><strong>Verification Link:</strong><br>
                        <a href='$verificationLink'>$verificationLink</a></p>
                        <p>This link will expire in 1 hour.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Client Manager. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Use PHPMailer to send email
        $mailer = new Mailer();
        $mailer->send($email, $name, $subject, $message);
    }
}
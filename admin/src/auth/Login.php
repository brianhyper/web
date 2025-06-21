<?php
// src/auth/Login.php
namespace App\auth;

use App\models\User;
use App\middleware\CSRFMiddleware;
use App\helpers\Sanitizer;
use App\auth\SessionManager;

class Login {
    public function showForm() {
        $token = CSRFMiddleware::generateToken();
        include __DIR__ . '/../../views/auth/login.php';
    }

    public function handleLogin() {
        try {
            // inavalidate CSRF token
            CSRFMiddleware::validateToken($_POST['csrf_token'] ?? '');

            $email = Sanitizer::sanitize($_POST['email']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']);

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if (!$user) {
               //delay
                sleep(1);
                $_SESSION['errors'] = ["Invalid email or password"];
                header('Location: /web/admin/public/login');
                exit;
            }

            // inaCheck if user is verified
            if (!$user['is_verified']) {
                $_SESSION['errors'] = ["Please verify your email address before logging in."];
                header('Location: /web/admin/public/login');
                exit;
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if password needs rehashing
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $userModel->updatePassword($user['id'], $newHash);
                }
                
                // Start secure session
                SessionManager::startSecureSession();
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['is_verified'] = true;
                $_SESSION['login_time'] = time();
                
                // Set secure remember me cookie
                if ($remember) {
                    $this->setRememberMeCookie($user['id']);
                }
                
                // Regenerate session ID after login
                session_regenerate_id(true);
                
                // Redirect to dashboard
                header('Location: /web/admin/public//dashboard');
                exit;
            } else {
                sleep(1); // Delay to prevent timing attacks
                $_SESSION['errors'] = ["Invalid email or password"];
                header('Location: /web/admin/public/login');
                exit;
            }
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['errors'] = ["Login failed. Please try again."];
            header('Location: /web/admin/public/login');
            exit;
        }
    }
    
    private function setRememberMeCookie($userId) {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $validator);
        
        // 30 days expiration
        $expires = time() + 30 * 24 * 60 * 60;
        
        // Store in database
        $db = \App\config\Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO remember_tokens 
            (user_id, selector, token_hash, expires) 
            VALUES (:user_id, :selector, :token_hash, :expires)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':selector' => $selector,
            ':token_hash' => $tokenHash,
            ':expires' => date('Y-m-d H:i:s', $expires)
        ]);
        
        // Set cookie
        $cookieValue = $selector . ':' . $validator;
        setcookie(
            'remember_me',
            $cookieValue,
            $expires,
            '/',
            $_SERVER['HTTP_HOST'],
            isset($_SERVER['HTTPS']), // Secure flag
            true // HttpOnly
        );
    }
}
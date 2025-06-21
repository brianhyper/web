<?php
// client-manager/public/login.php
require_once __DIR__ . '/../app.php';


// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /web/client-manager/public/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Please enter both email and password.';
        } else {
            // Database check
            $stmt = $pdo->prepare("SELECT id, name, password, role, verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();



            if ($user && verify_password($password, $user['password'])) {
                if ($user['verified']) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name']; 
                    $_SESSION['last_login'] = time();
                    
                    log_activity('login_success', "User logged in: $email");
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Please verify your email address first';
                }
            } else {
                $error = 'Invalid credentials';
                log_activity('login_failed', "Failed login attempt for: $email");
            }
        }
    }
}

$pageTitle = "Login";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?? 'Login' ?> - Client Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Your main CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Sign In</h1>
                <p>Access your dashboard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required 
                               placeholder="your.email@example.com">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required 
                               placeholder="••••••••">
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox">
                        <input type="checkbox" name="remember"> 
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="link">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php" class="link">Sign up</a></p>
            </div>
        </div>
    </main>
</body>
</html>

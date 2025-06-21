<?php
// client-manager/public/register.php
require '../app.php';

// Redirect if already logged in


$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token';
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email already registered';
            } else {
                // Create user
                $verification_token = generate_token();
                $hashed_password = hash_password($password);
                
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_token, role) 
                                      VALUES (?, ?, ?, ?, 'staff')");
                $stmt->execute([$name, $email, $hashed_password, $verification_token]);
                
                // Send verification email
                $verification_link = "https://{$_SERVER['HTTP_HOST']}/verify.php?token=$verification_token";
                $subject = "Verify Your Account";
                $message = "Hi $name,<br><br>"
                         . "Please verify your account by clicking the link below:<br>"
                         . "<a href='$verification_link'>$verification_link</a><br><br>"
                         . "Thanks,<br>Client Manager Team";
                
                if (send_email($email, $subject, $message)) {
                    $success = 'Registration successful! Check your email for verification instructions.';
                    log_activity('user_registered', "New user: $email");
                } else {
                    $error = 'Failed to send verification email';
                }
            }
        }
    }
}

$pageTitle = "Register";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> - Client Manager</title>
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
            <h1>Create Account</h1>
            <p>Get started with your free account</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="name" name="name" required 
                           placeholder="John Doe">
                </div>
            </div>
            
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
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="••••••••">
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox">
                    <input type="checkbox" name="terms" required> 
                    <span>I agree to the <a href="terms.php" class="link">Terms of Service</a></span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php" class="link">Sign in</a></p>
        </div>
    </div>
</main>
<?php include '../footer.php'; ?>
</body>
</html>
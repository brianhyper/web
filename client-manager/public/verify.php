<?php
// client-manager/public/verify.php
require '../app.php';

$message = '';
$success = false;

if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE verification_token = ? AND verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Activate account
        $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Email verified successfully! You can now login to your account.";
            $success = true;
            log_activity('email_verified', "User verified: {$user['email']}");
        } else {
            $message = "Failed to verify email. Please contact support.";
        }
    } else {
        $message = "Invalid or expired verification token.";
    }
} else {
    $message = "Missing verification token.";
}

$pageTitle = "Email Verification";
include '../header.php';
?>

<main class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Email Verification</h1>
            <p>Account activation status</p>
        </div>
        
        <div class="auth-body">
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
                <?= $message ?>
            </div>
            
            <?php if ($success): ?>
                <div class="text-center mt-4">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login to Your Account
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <a href="register.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Register Again
                    </a>
                    <a href="contact.php" class="btn btn-link">
                        <i class="fas fa-life-ring"></i> Contact Support
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../footer.php'; ?>
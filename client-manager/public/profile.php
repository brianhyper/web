<?php
// client-manager/public/profile.php
require '../app.php';
authenticate(['admin', 'staff']);

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    validate_csrf();
    
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone'] ?? '');
    
    try {
        // Check if email already exists
        $existing = fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user['id']]);
        if ($existing) {
            throw new Exception("Email address is already in use");
        }
        
        // Update user profile
        update('users', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ], 'id = ?', [$user['id']]);
        
        // Update session data
        $_SESSION['user_name'] = $name;
        
        $success = "Profile updated successfully!";
        log_activity('profile_update', "Updated profile information");
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    validate_csrf();
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    try {
        // Verify current password
        if (!verify_password($current_password, $user['password'])) {
            throw new Exception("Current password is incorrect");
        }
        
        // Validate new password
        if ($new_password !== $confirm_password) {
            throw new Exception("New passwords do not match");
        }
        
        if (strlen($new_password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }
        
        // Update password
        $hashed_password = hash_password($new_password);
        update('users', ['password' => $hashed_password], 'id = ?', [$user['id']]);
        
        $success = "Password changed successfully!";
        log_activity('password_change', "Changed account password");
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle notification preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    validate_csrf();
    
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    
    try {
        update('users', [
            'email_notifications' => $email_notifications,
            'push_notifications' => $push_notifications
        ], 'id = ?', [$user['id']]);
        
        $success = "Notification preferences updated!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "My Profile";
include '../header.php';
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <!-- Profile Card -->
        <div class="card profile-card">
            <div class="card-header">
                <h2><i class="fas fa-user"></i> Personal Information</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="avatar-container">
                        <div class="avatar-large">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-camera"></i> Change
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <input type="text" id="role" class="form-control" 
                               value="<?= ucfirst($user['role']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="created_at">Member Since</label>
                        <input type="text" id="created_at" class="form-control" 
                               value="<?= date('M d, Y', strtotime($user['created_at'])) ?>" readonly>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
        
        <!-- Security Card -->
        <div class="card security-card">
            <div class="card-header">
                <h2><i class="fas fa-shield-alt"></i> Security</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="security-status">
                        <div class="status-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Two-factor authentication: <strong>Disabled</strong></span>
                        </div>
                        <div class="status-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Password last changed: 
                                <strong><?= date('M d, Y', strtotime($user['updated_at'])) ?></strong>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" 
                               class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" 
                               class="form-control" required>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Change Password</button>
                    
                    <hr>
                    
                    <div class="form-group">
                        <h3><i class="fas fa-desktop"></i> Active Sessions</h3>
                        <div class="session-list">
                            <div class="session-item">
                                <div class="session-info">
                                    <i class="fas fa-laptop"></i>
                                    <div>
                                        <strong>Chrome on Windows</strong>
                                        <small>New York, US • Last active: 2 hours ago</small>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-danger">Revoke</button>
                            </div>
                            <div class="session-item current">
                                <div class="session-info">
                                    <i class="fas fa-laptop"></i>
                                    <div>
                                        <strong>Firefox on macOS</strong>
                                        <small>Current session • San Francisco, US</small>
                                    </div>
                                </div>
                                <span class="badge badge-success">Current</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Notifications Card -->
        <div class="card notifications-card">
            <div class="card-header">
                <h2><i class="fas fa-bell"></i> Notifications</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="update_notifications" value="1">
                    
                    <div class="form-group">
                        <label>Notification Preferences</label>
                        <div class="preference-item">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="email_notifications" 
                                       name="email_notifications" <?= $user['email_notifications'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="email_notifications">Email Notifications</label>
                            </div>
                            <small class="text-muted">Receive important account notifications via email</small>
                        </div>
                        
                        <div class="preference-item">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="push_notifications" 
                                       name="push_notifications" <?= $user['push_notifications'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="push_notifications">Push Notifications</label>
                            </div>
                            <small class="text-muted">Get real-time updates on your device</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                    
                    <hr>
                    
                    <div class="form-group">
                        <label>Recent Notifications</label>
                        <div class="notification-list">
                            <?php
                            $notifications = fetch_all("
                                SELECT * FROM notifications 
                                WHERE user_id = ? 
                                ORDER BY created_at DESC 
                                LIMIT 5
                            ", [$_SESSION['user_id']]);
                            
                            if (empty($notifications)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No notifications yet</p>
                                </div>
                            <?php else: 
                                foreach ($notifications as $notification): 
                                    $icon = [
                                        'info' => 'info-circle text-primary',
                                        'warning' => 'exclamation-triangle text-warning',
                                        'success' => 'check-circle text-success',
                                        'error' => 'exclamation-circle text-danger'
                                    ][$notification['type']];
                            ?>
                                <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                                    <div class="notification-icon">
                                        <i class="fas fa-<?= $icon ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p><?= htmlspecialchars($notification['message']) ?></p>
                                        <small><?= date('M d, H:i', strtotime($notification['created_at'])) ?></small>
                                    </div>
                                    <?php if (!$notification['is_read']): ?>
                                        <span class="badge badge-primary">New</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; 
                            endif; ?>
                        </div>
                        <a href="notifications.php" class="btn btn-link btn-sm">View All Notifications</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.card-header {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid var(--border-color);
}

.card-header h2 {
    font-size: 1.1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body {
    padding: 20px;
}

.avatar-container {
    text-align: center;
    margin-bottom: 20px;
}

.avatar-large {
    width: 100px;
    height: 100px;
    background-color: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0 auto 10px;
}

.form-group {
    margin-bottom: 20px;
}

.security-status {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.status-item:last-child {
    margin-bottom: 0;
}

.session-list {
    margin-top: 15px;
}

.session-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
}

.session-item:last-child {
    border-bottom: none;
}

.session-item.current {
    background-color: rgba(26, 115, 232, 0.05);
    padding: 10px;
    border-radius: var(--border-radius);
    margin: 0 -10px;
}

.session-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.session-info i {
    font-size: 1.5rem;
    color: var(--text-secondary);
}

.preference-item {
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
}

.preference-item:last-child {
    border-bottom: none;
}

.notification-list {
    margin-top: 15px;
}

.notification-item {
    display: flex;
    padding: 10px;
    border-radius: var(--border-radius);
    margin-bottom: 10px;
    background-color: #f8f9fa;
    gap: 10px;
    align-items: flex-start;
}

.notification-item.unread {
    background-color: rgba(26, 115, 232, 0.1);
    border-left: 3px solid var(--primary);
}

.notification-icon {
    font-size: 1.2rem;
    min-width: 30px;
}

.notification-content {
    flex: 1;
}

.notification-content p {
    margin: 0;
}

.notification-content small {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.empty-state {
    text-align: center;
    padding: 30px 0;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 10px;
    opacity: 0.3;
}

@media (max-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Password strength meter
    const passwordInput = document.getElementById('new_password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strengthMeter = document.getElementById('password-strength');
            if (!strengthMeter) return;
            
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 15;
            
            // Character type checks
            if (/[A-Z]/.test(password)) strength += 20;
            if (/[a-z]/.test(password)) strength += 20;
            if (/[0-9]/.test(password)) strength += 20;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            
            // Update strength meter
            strengthMeter.style.width = Math.min(strength, 100) + '%';
            
            // Update classes
            strengthMeter.classList.remove('bg-danger', 'bg-warning', 'bg-success');
            if (strength < 50) {
                strengthMeter.classList.add('bg-danger');
            } else if (strength < 80) {
                strengthMeter.classList.add('bg-warning');
            } else {
                strengthMeter.classList.add('bg-success');
            }
        });
    }
});
</script>

<?php include '../footer.php'; ?>
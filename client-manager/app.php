<?php
// client-manager/app.php
session_start([
    'cookie_lifetime' => 86400, // 1 day
    'cookie_httponly' => true,
    'cookie_secure' => false,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Load environment configuration
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
} else {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Missing environment configuration');
}

// CSRF Token Management
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Authentication Check
 * @param array $roles Allowed roles (empty array allows all authenticated users)
 */
function authenticate($roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    if (!empty($roles) && !in_array($_SESSION['user_role'], $roles)) {
        header('HTTP/1.1 403 Forbidden');
        exit('Access denied. Insufficient permissions.');
    }
}

/**
 * Generate CSRF Token Field
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

/**
 * Validate CSRF Token
 */
function validate_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        exit('Invalid CSRF token');
    }
}

/**
 * Sanitize Input
 * @param string $input User input
 * @return string Sanitized output
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to specified URL
 * @param string $url Target URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Send Email
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email content (HTML)
 * @return bool True on success, False on failure
 */
function send_email($to, $subject, $body) {
    global $env;
    
    // Use PHPMailer if available
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $env['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $env['MAIL_USER'];
            $mail->Password = $env['MAIL_PASS'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom($env['MAIL_FROM'], 'Client Manager');
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    // Fallback to basic mail()
    $headers = "From: {$env['MAIL_FROM']}" . "\r\n" .
               "Reply-To: {$env['MAIL_FROM']}" . "\r\n" .
               "X-Mailer: PHP/" . phpversion() . "\r\n" .
               "Content-type: text/html; charset=UTF-8";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Regenerate session ID after privilege change
 */
function regenerate_session() {
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Generate secure tokens
 * @param int $length Token length in bytes
 * @return string Hexadecimal token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Verify a password against a hash
 * @param string $input The plain password
 * @param string $hash The hashed password from the database
 * @return bool
 */
function verify_password($input, $hash) {
    return password_verify($input, $hash);
}

/**
 * Hash a password for storing in the database
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Error reporting configuration
if ($env['APP_ENV'] === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

// Set timezone
date_default_timezone_set($env['TIMEZONE'] ?? 'UTC');

// Database connection
try {
    $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Database connection failed: ' . $e->getMessage());
}

/**
 * Log user activity
 * @param string $action Action performed by the user
 * @param string|null $details Additional details about the action
 */
function log_activity($action, $details = null) {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, $ip, $agent]);
}
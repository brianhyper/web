<?php
declare(strict_types=1);

require __DIR__ . '/../../src/config/bootstrap.php';

use App\Helpers\Logger;

// Destroy session completely
$_SESSION = [];

// Expire session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Log logout
if (isset($_SESSION['user_id'])) {
    Logger::info("User logged out: {$_SESSION['user_email']}");
}

header('Location: /login');
exit;
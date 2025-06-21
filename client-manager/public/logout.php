<?php
// client-manager/public/logout.php
require '../app.php';

// Log activity before destroying session
if (isset($_SESSION['user_id'])) {
    log_activity('logout', "User logged out: ID {$_SESSION['user_id']}");
}

// Destroy session completely
$_SESSION = [];
session_destroy();

// Expire session cookie
setcookie(session_name(), '', time() - 3600, '/');

// Redirect to login
header('Location: login.php');
exit;
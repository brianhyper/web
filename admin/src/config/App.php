<?php
namespace App\config;

use Dotenv\Dotenv;

class App {
    public static function init() {
        // Load environment variables
        self::loadEnvironment();
        
        // Set error reporting based on environment
        self::setErrorHandling();
        
        // Set default timezone
        date_default_timezone_set('UTC');
        
        // Configure session settings
        self::configureSession();
    }
    
    private static function loadEnvironment() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        
        // Validate required environment variables
        $dotenv->required([
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
            'APP_ENV', 'APP_URL', 'MAIL_HOST', 'MAIL_PORT',
            'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM',
            'JWT_SECRET', 'CSRF_SECRET'
        ])->notEmpty();
    }
    
    private static function setErrorHandling() {
        if ($_ENV['APP_ENV'] === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', __DIR__ . '/../../storage/logs/php_errors.log');
        }
        
        // Set custom error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }
    
    private static function configureSession() {
        session_name('SECURE_SESSION');
        session_set_cookie_params([
            'lifetime' => 86400, // 1 day
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
    
    public static function handleException($exception) {
        $code = $exception->getCode() ?: 500;
        http_response_code($code);
        
        $errorData = [
            'timestamp' => date('c'),
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]
        ];
        
        // Log error details
        error_log(json_encode($errorData, JSON_PRETTY_PRINT));
        
        // Show error based on environment
        if ($_ENV['APP_ENV'] === 'development') {
            echo "<pre>";
            print_r($errorData);
            echo "</pre>";
        } else {
            include __DIR__ . '/../../src/views/errors/500.php';
        }
        exit;
    }
}
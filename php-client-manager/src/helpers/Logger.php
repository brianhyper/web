<?php
declare(strict_types=1);

class Logger {
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const DEBUG = 'DEBUG';
    const AUDIT = 'AUDIT';
    
    public static function log(string $level, string $message, array $context = []): void {
        $logEntry = sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($context, JSON_UNESCAPED_SLASHES)
        );
        
        $logFile = STORAGE_PATH . '/logs/app_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Also log to system logger for errors
        if ($level === self::ERROR) {
            error_log($message . ' ' . json_encode($context));
        }
    }
    
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }
    
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }
    
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }
    
    public static function debug(string $message, array $context = []): void {
        if (getenv('APP_ENV') === 'development') {
            self::log(self::DEBUG, $message, $context);
        }
    }
    
    public static function audit(string $action, array $context = []): void {
        $context['user_id'] = $_SESSION['user_id'] ?? null;
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        self::log(self::AUDIT, "AUDIT: $action", $context);
    }
}
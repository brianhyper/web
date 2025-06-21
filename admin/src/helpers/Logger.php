<?php
// src/helpers/Logger.php
namespace App\helpers;

class Logger {
    private static $logPath;

    public static function init() {
        self::$logPath = __DIR__ . '/../../storage/logs/' . date('Y-m-d') . '.log';
        if (!file_exists(dirname(self::$logPath))) {
            mkdir(dirname(self::$logPath), 0755, true);
        }
    }

    public static function log($action, $context = []) {
        self::init();
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'user_id' => $_SESSION['user_id'] ?? 'system',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'context' => $context
        ];
        
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents(self::$logPath, $logLine, FILE_APPEND);
    }

    public static function getLogs($days = 7) {
        $logs = [];
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $file = __DIR__ . '/../../storage/logs/' . $date . '.log';
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $logs[] = json_decode($line, true);
                }
            }
        }
        return $logs;
    }
}
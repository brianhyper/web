<?php
namespace App\config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASSWORD'];
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_PERSISTENT => true
        ];
        
        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \Exception("Database connection error");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    public static function runMigrations() {
        $db = self::getInstance();
        $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.sql');
        
        foreach ($migrationFiles as $file) {
            $sql = file_get_contents($file);
            try {
                $db->exec($sql);
                error_log("Migration executed: " . basename($file));
            } catch (PDOException $e) {
                error_log("Migration failed: " . basename($file) . " - " . $e->getMessage());
            }
        }
    }
    
    public static function runSeeders() {
        $seedFiles = glob(__DIR__ . '/../../database/seeders/*.php');
        
        foreach ($seedFiles as $file) {
            try {
                require_once $file;
                $className = 'Database\Seeders\\' . basename($file, '.php');
                if (class_exists($className)) {
                    $seeder = new $className();
                    $seeder->run();
                    error_log("Seeder executed: " . basename($file));
                }
            } catch (\Exception $e) {
                error_log("Seeder failed: " . basename($file) . " - " . $e->getMessage());
            }
        }
    }
}
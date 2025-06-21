<?php
namespace Database\Seeders;

use PDO;
use App\config\Database;

class InitialDataSeeder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function run() {
        $this->createAdminUser();
        $this->createSampleClients();
        $this->createSampleProjects();
    }

    private function createAdminUser() {
        $password = password_hash('Admin@123', PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role, is_verified) 
            VALUES (:name, :email, :password, :role, 1)
        ");
        $stmt->execute([
            ':name' => 'Administrator',
            ':email' => 'admin@example.com',
            ':password' => $password,
            ':role' => 'admin'
        ]);
    }

    private function createSampleClients() {
        $clients = [
            ['Acme Corporation', 'contact@acme.com', '+1 (555) 123-4567', 'Acme Corp', '123 Main St, Anytown'],
            ['Globex Industries', 'info@globex.com', '+1 (555) 987-6543', 'Globex', '456 Oak Ave, Somewhere'],
            ['Wayne Enterprises', 'contact@wayne.com', '+1 (555) 456-7890', 'Wayne Corp', '789 Gotham Blvd, Gotham']
        ];
        
        $stmt = $this->db->prepare("
            INSERT INTO clients (name, email, phone, company, address, created_by) 
            VALUES (:name, :email, :phone, :company, :address, :created_by)
        ");
        
        foreach ($clients as $client) {
            $stmt->execute([
                ':name' => $client[0],
                ':email' => $client[1],
                ':phone' => $client[2],
                ':company' => $client[3],
                ':address' => $client[4],
                ':created_by' => 1
            ]);
        }
    }

    private function createSampleProjects() {
        $projects = [
            ['Website Redesign', 'Redesign of company website', 1, 'active', 5000.00, '2023-10-01', '2023-12-15'],
            ['Mobile App Development', 'iOS and Android application', 2, 'active', 15000.00, '2023-09-15', '2024-03-31'],
            ['CRM Implementation', 'Customer relationship management system', 3, 'planned', 8000.00, '2024-01-01', '2024-06-30']
        ];
        
        $stmt = $this->db->prepare("
            INSERT INTO projects (title, description, client_id, status, budget, start_date, end_date, created_by) 
            VALUES (:title, :description, :client_id, :status, :budget, :start_date, :end_date, :created_by)
        ");
        
        foreach ($projects as $project) {
            $stmt->execute([
                ':title' => $project[0],
                ':description' => $project[1],
                ':client_id' => $project[2],
                ':status' => $project[3],
                ':budget' => $project[4],
                ':start_date' => $project[5],
                ':end_date' => $project[6],
                ':created_by' => 1
            ]);
        }
    }
}

// Run seeder
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/config/Database.php';

$seeder = new InitialDataSeeder();
$seeder->run();

echo "Database seeded successfully!\n";
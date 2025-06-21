<?php
namespace App\controllers;

use App\config\Database;
use App\models\Client;
use App\models\Project;
use App\helpers\Sanitizer;
use App\helpers\Logger;
use App\middleware\AuthMiddleware;
use App\middleware\RoleMiddleware;
use App\helpers\CSRFMiddleware;

class ClientController {
    private $db;
    private $clientModel;
    private $projectModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->clientModel = new Client();
        $this->projectModel = new Project();
    }

    public function index() {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $search = $_GET['search'] ?? '';
        
        // Get clients with pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 10;
        $clients = $this->clientModel->getAll($userId, $userRole, $search, $page, $perPage);
        $totalClients = $this->clientModel->count($userId, $userRole, $search);
        $totalPages = ceil($totalClients / $perPage);
        
        include __DIR__ . '/../../src/views/clients/index.php';
    }

    public function create() {
        AuthMiddleware::handle();
        RoleMiddleware::handle(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFMiddleware::validateToken($_POST['csrf_token']);
            
            try {
                $data = [
                    'name' => Sanitizer::sanitize($_POST['name']),
                    'email' => Sanitizer::sanitize($_POST['email']),
                    'phone' => Sanitizer::sanitize($_POST['phone']),
                    'company' => Sanitizer::sanitize($_POST['company']),
                    'address' => Sanitizer::sanitize($_POST['address']),
                    'notes' => Sanitizer::sanitize($_POST['notes']),
                    'created_by' => $_SESSION['user_id']
                ];
                
                $clientId = $this->clientModel->create($data);
                
                Logger::log("Client created", [
                    'client_id' => $clientId,
                    'user_id' => $_SESSION['user_id']
                ]);
                
                $_SESSION['success'] = "Client created successfully";
                header('Location: /clients');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error creating client: " . $e->getMessage();
                header('Location: /clients/create');
                exit;
            }
        }
        
        include __DIR__ . '/../../src/views/clients/create.php';
    }

    public function edit($id) {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $client = $this->clientModel->getById($id, $userId, $userRole);
        
        if (!$client) {
            $_SESSION['error'] = "Client not found";
            header('Location: /clients');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFMiddleware::validateToken($_POST['csrf_token']);
            
            try {
                $data = [
                    'name' => Sanitizer::sanitize($_POST['name']),
                    'email' => Sanitizer::sanitize($_POST['email']),
                    'phone' => Sanitizer::sanitize($_POST['phone']),
                    'company' => Sanitizer::sanitize($_POST['company']),
                    'address' => Sanitizer::sanitize($_POST['address']),
                    'notes' => Sanitizer::sanitize($_POST['notes'])
                ];
                
                $this->clientModel->update($id, $data, $userId, $userRole);
                
                Logger::log("Client updated", [
                    'client_id' => $id,
                    'user_id' => $_SESSION['user_id']
                ]);
                
                $_SESSION['success'] = "Client updated successfully";
                header('Location: /clients');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error updating client: " . $e->getMessage();
                header("Location: /clients/edit/$id");
                exit;
            }
        }
        
        $projects = $this->projectModel->getByClient($id, $userId, $userRole);
        include __DIR__ . '/../../src/views/clients/edit.php';
    }

    public function delete($id) {
        AuthMiddleware::handle();
        RoleMiddleware::handle(['admin']);
        
        $userId = $_SESSION['user_id'];
        
        try {
            $this->clientModel->delete($id, $userId);
            
            Logger::log("Client deleted", [
                'client_id' => $id,
                'user_id' => $_SESSION['user_id']
            ]);
            
            $_SESSION['success'] = "Client deleted successfully";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error deleting client: " . $e->getMessage();
        }
        
        header('Location: /clients');
        exit;
    }

    public function search() {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $searchTerm = Sanitizer::sanitize($_GET['q']);
        
        $clients = $this->clientModel->search($searchTerm, $userId, $userRole);
        
        header('Content-Type: application/json');
        echo json_encode($clients);
        exit;
    }
}
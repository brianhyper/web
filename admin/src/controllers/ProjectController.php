<?php
namespace App\controllers;

use App\config\Database;
use App\models\Project;
use App\models\Client;
use App\models\Transaction;
use App\models\CalendarEvent;
use App\helpers\Sanitizer;
use App\helpers\Logger;
use App\middleware\AuthMiddleware;
use App\middleware\RoleMiddleware;
use App\helpers\CSRFMiddleware;

class ProjectController {
    private $db;
    private $projectModel;
    private $clientModel;
    private $transactionModel;
    private $calendarModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->projectModel = new Project();
        $this->clientModel = new Client();
        $this->transactionModel = new Transaction();
        $this->calendarModel = new CalendarEvent();
    }

    public function index() {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $status = $_GET['status'] ?? '';
        $clientId = $_GET['client_id'] ?? '';
        
        // Get projects with pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 10;
        $projects = $this->projectModel->getAll($userId, $userRole, $status, $clientId, $page, $perPage);
        $totalProjects = $this->projectModel->count($userId, $userRole, $status, $clientId);
        $totalPages = ceil($totalProjects / $perPage);
        
        // Get clients for filter dropdown
        $clients = $this->clientModel->getAll($userId, $userRole);
        
        include __DIR__ . '/../../src/views/projects/index.php';
    }

    public function create() {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $clients = $this->clientModel->getAll($userId, $userRole);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFMiddleware::validateToken($_POST['csrf_token']);
            
            try {
                $data = [
                    'title' => Sanitizer::sanitize($_POST['title']),
                    'description' => Sanitizer::sanitize($_POST['description']),
                    'client_id' => (int)$_POST['client_id'],
                    'status' => Sanitizer::sanitize($_POST['status']),
                    'budget' => (float)$_POST['budget'],
                    'start_date' => Sanitizer::sanitize($_POST['start_date']),
                    'end_date' => Sanitizer::sanitize($_POST['end_date']),
                    'created_by' => $userId
                ];
                
                $projectId = $this->projectModel->create($data);
                
                Logger::log("Project created", [
                    'project_id' => $projectId,
                    'user_id' => $userId
                ]);
                
                $_SESSION['success'] = "Project created successfully";
                header('Location: /projects');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error creating project: " . $e->getMessage();
                header('Location: /projects/create');
                exit;
            }
        }
        
        include __DIR__ . '/../../src/views/projects/create.php';
    }

    public function view($id) {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $project = $this->projectModel->getById($id, $userId, $userRole);
        
        if (!$project) {
            $_SESSION['error'] = "Project not found";
            header('Location: /projects');
            exit;
        }
        
        $client = $this->clientModel->getById($project['client_id'], $userId, $userRole);
        $transactions = $this->transactionModel->getByProject($id, $userId, $userRole);
        $events = $this->calendarModel->getByProject($id, $userId, $userRole);
        $teamMembers = $this->projectModel->getTeamMembers($id);
        
        include __DIR__ . '/../../src/views/projects/view.php';
    }

    public function edit($id) {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $project = $this->projectModel->getById($id, $userId, $userRole);
        $clients = $this->clientModel->getAll($userId, $userRole);
        
        if (!$project) {
            $_SESSION['error'] = "Project not found";
            header('Location: /projects');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFMiddleware::validateToken($_POST['csrf_token']);
            
            try {
                $data = [
                    'title' => Sanitizer::sanitize($_POST['title']),
                    'description' => Sanitizer::sanitize($_POST['description']),
                    'client_id' => (int)$_POST['client_id'],
                    'status' => Sanitizer::sanitize($_POST['status']),
                    'budget' => (float)$_POST['budget'],
                    'start_date' => Sanitizer::sanitize($_POST['start_date']),
                    'end_date' => Sanitizer::sanitize($_POST['end_date'])
                ];
                
                $this->projectModel->update($id, $data, $userId, $userRole);
                
                Logger::log("Project updated", [
                    'project_id' => $id,
                    'user_id' => $userId
                ]);
                
                $_SESSION['success'] = "Project updated successfully";
                header('Location: /projects');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error updating project: " . $e->getMessage();
                header("Location: /projects/edit/$id");
                exit;
            }
        }
        
        include __DIR__ . '/../../src/views/projects/edit.php';
    }

    public function delete($id) {
        AuthMiddleware::handle();
        RoleMiddleware::handle(['admin']);
        
        $userId = $_SESSION['user_id'];
        
        try {
            $this->projectModel->delete($id, $userId);
            
            Logger::log("Project deleted", [
                'project_id' => $id,
                'user_id' => $userId
            ]);
            
            $_SESSION['success'] = "Project deleted successfully";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error deleting project: " . $e->getMessage();
        }
        
        header('Location: /projects');
        exit;
    }

    public function addTeamMember($projectId) {
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFMiddleware::validateToken($_POST['csrf_token']);
            
            try {
                $memberId = (int)$_POST['member_id'];
                $this->projectModel->addTeamMember($projectId, $memberId, $userId, $userRole);
                
                Logger::log("Team member added", [
                    'project_id' => $projectId,
                    'member_id' => $memberId,
                    'user_id' => $userId
                ]);
                
                $_SESSION['success'] = "Team member added successfully";
                header("Location: /projects/view/$projectId");
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error adding team member: " . $e->getMessage();
                header("Location: /projects/view/$projectId");
                exit;
            }
        }
        
        include __DIR__ . '/../../src/views/projects/add_team_member.php';
    }
}
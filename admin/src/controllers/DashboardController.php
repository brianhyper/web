<?php
namespace App\controllers;

use App\config\Database;
use App\models\Client;
use App\models\Project;
use App\models\Transaction;
use App\models\CalendarEvent;
use App\helpers\Logger;
use App\middleware\AuthMiddleware;

class DashboardController {
    private $db;
    private $clientModel;
    private $projectModel;
    private $transactionModel;
    private $calendarModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->clientModel = new Client();
        $this->projectModel = new Project();
        $this->transactionModel = new Transaction();
        $this->calendarModel = new CalendarEvent();
    }

    public function index() {
        // Ensure user is authenticated
        AuthMiddleware::handle();
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        // Get dashboard statistics
        $stats = [
            'total_clients' => $this->clientModel->count($userId, $userRole),
            'active_projects' => $this->projectModel->countByStatus('active', $userId, $userRole),
            'pending_projects' => $this->projectModel->countByStatus('pending', $userId, $userRole),
            'recent_income' => $this->transactionModel->getRecentIncome(30, $userId, $userRole)
        ];
        
        // Get upcoming events
        $events = $this->calendarModel->getUpcomingEvents(5, $userId, $userRole);
        
        // Get recent projects
        $projects = $this->projectModel->getRecentProjects(5, $userId, $userRole);
        
        // Get financial overview
        $financials = $this->transactionModel->getFinancialOverview($userId, $userRole);
        
        // Log dashboard access
        Logger::log("Accessed dashboard", ['user_id' => $userId]);
        
        // Render dashboard view
        include __DIR__ . '/../../src/views/dashboard/index.php';
    }
}
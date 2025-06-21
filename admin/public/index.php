<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/App.php';
require_once __DIR__ . '/../src/config/Routes.php';

use App\config\App;
use App\config\Routes;

// Initialize application
App::init();

// Start session management
require_once __DIR__ . '/../src/auth/SessionManager.php';
\App\auth\SessionManager::startSecureSession();

// Handle routing
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Normalize the request path to remove the base path
$basePath = '/web/admin/public';
if (strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}
if ($requestPath === '' || $requestPath === false) {
    $requestPath = '/';
}

file_put_contents(__DIR__ . '/debug.log', $requestPath . PHP_EOL, FILE_APPEND);

// Create router instance
$router = new Routes();
$router->loadRoutes();

// Define routes (should be moved to Routes.php for better organization)
$router->addRoute('GET', '/', 'DashboardController@index');
$router->addRoute('GET', '/dashboard', 'DashboardController@index');
$router->addRoute('GET', '/register', 'Register@showForm');
$router->addRoute('POST', '/register', 'Register@handleRegistration');
$router->addRoute('GET', '/login', 'Login@showForm');
$router->addRoute('POST', '/login', 'Login@handleLogin');
$router->addRoute('GET', '/logout', 'Logout@handleLogout');
$router->addRoute('GET', '/verify.php', 'Verify@verifyEmail');

// Client routes
$router->addRoute('GET', '/clients', 'ClientController@index');
$router->addRoute('GET', '/clients/create', 'ClientController@create');
$router->addRoute('POST', '/clients/create', 'ClientController@store');
$router->addRoute('GET', '/clients/edit/{id}', 'ClientController@edit');
$router->addRoute('POST', '/clients/update/{id}', 'ClientController@update');
$router->addRoute('POST', '/clients/delete/{id}', 'ClientController@delete');

// Project routes
$router->addRoute('GET', '/projects', 'ProjectController@index');
$router->addRoute('GET', '/projects/create', 'ProjectController@create');
$router->addRoute('POST', '/projects/create', 'ProjectController@store');
$router->addRoute('GET', '/projects/view/{id}', 'ProjectController@view');
$router->addRoute('GET', '/projects/edit/{id}', 'ProjectController@edit');
$router->addRoute('POST', '/projects/update/{id}', 'ProjectController@update');

// Calendar routes
$router->addRoute('GET', '/calendar', 'CalendarController@index');
$router->addRoute('POST', '/calendar/events', 'CalendarController@getEvents');
$router->addRoute('POST', '/calendar/create', 'CalendarController@createEvent');

// Financial routes
$router->addRoute('GET', '/budget', 'BudgetController@index');
$router->addRoute('POST', '/transactions/create', 'BudgetController@createTransaction');
$router->addRoute('GET', '/receipts/generate/{id}', 'ReceiptController@generate');

// Chat routes
$router->addRoute('GET', '/chat', 'ChatBotController@index');
$router->addRoute('POST', '/chat/send', 'ChatBotController@sendMessage');
$router->addRoute('GET', '/chat/history', 'ChatBotController@getHistory');

// Export routes
$router->addRoute('GET', '/exports/clients/csv', 'ExportController@exportClientsCSV');
$router->addRoute('GET', '/exports/projects/csv', 'ExportController@exportProjectsCSV');
$router->addRoute('GET', '/exports/financial/pdf', 'ExportController@exportFinancialPDF');

// Handle the request
$router->dispatch($requestPath, $requestMethod);
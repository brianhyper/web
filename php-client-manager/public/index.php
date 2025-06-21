<?php
declare(strict_types=1);
require __DIR__ . '/../src/config/bootstrap.php';

// Initialize session with security settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Apply global middleware
(new AuthMiddleware())->handle();

// Define routes and their required roles
$routes = [
    '/' => ['view' => 'dashboard', 'roles' => ['user', 'staff', 'admin']],
    '/login' => ['view' => 'login', 'public' => true],
    '/register' => ['view' => 'register', 'public' => true],
    '/verify' => ['view' => 'verify', 'public' => true],
    '/logout' => ['handler' => 'logout', 'public' => true],
    '/clients' => ['view' => 'clients', 'roles' => ['user', 'staff', 'admin']],
    '/clients/new' => ['view' => 'client_new', 'roles' => ['user', 'staff', 'admin']],
    '/clients/edit' => ['view' => 'client_edit', 'roles' => ['user', 'staff', 'admin']],
    '/projects' => ['view' => 'projects', 'roles' => ['user', 'staff', 'admin']],
    '/calendar' => ['view' => 'calendar', 'roles' => ['user', 'staff', 'admin']],
    '/receipts' => ['view' => 'receipts', 'roles' => ['user', 'staff', 'admin']],
    '/settings' => ['view' => 'settings', 'roles' => ['admin']],
    '/api/clients' => ['handler' => 'ClientController::apiList', 'roles' => ['user', 'staff', 'admin']],
    '/admin/dashboard' => ['view' => 'admin_dashboard', 'roles' => ['admin']]
];

// Get request path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routeKey = rtrim($path, '/');

// Handle 404 for undefined routes
if (!isset($routes[$routeKey])) {
    http_response_code(404);
    $route = ['view' => '404'];
} else {
    $route = $routes[$routeKey];
}

// Apply CSRF middleware for non-GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && empty($route['public'])) {
    (new CSRFMiddleware())->handle();
}

// Apply role-based access control
if (!empty($route['roles']) && !isset($route['public'])) {
    (new RoleMiddleware($route['roles']))->handle();
}

// Handle route processing
try {
    if (isset($route['handler'])) {
        // Handle controller actions
        list($class, $method) = explode('::', $route['handler']);
        call_user_func([new $class(), $method]);
    } elseif (isset($route['view'])) {
        // Render views
        $viewPath = __DIR__ . "/../frontend/pages/{$route['view']}.php";
        
        if (!file_exists($viewPath)) {
            throw new Exception("View file not found: $viewPath");
        }
        
        // Set current page for active navigation
        $currentPage = $route['view'];
        
        // Render with layout
        require __DIR__ . '/../frontend/layouts/header.php';
        require $viewPath;
        require __DIR__ . '/../frontend/layouts/footer.php';
    }
} catch (Exception $e) {
    // Log error
    error_log("Route handler error: " . $e->getMessage());
    
    // Show error page
    http_response_code(500);
    require __DIR__ . '/../frontend/pages/500.php';
}

// Session regeneration for security
if (!isset($route['public']) && mt_rand(1, 10) === 1) {
    session_regenerate_id(true);
}
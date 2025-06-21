<?php
namespace App\config;

class Routes {
    private $routes = [];
    private $routeGroups = [];
    private $currentGroup = null;
    private $middlewareStack = [];

    public function addRoute($method, $path, $handler, $middleware = []) {
        $route = [
            'method' => $method,
            'path' => $this->applyGroupPrefix($path),
            'handler' => $handler,
            'middleware' => array_merge($this->middlewareStack, (array)$middleware)
        ];
        
        $this->routes[] = $route;
        return $this;
    }
    
    public function group($prefix, $callback, $middleware = []) {
        $previousGroup = $this->currentGroup;
        $previousMiddleware = $this->middlewareStack;
        
        $this->currentGroup = $prefix;
        $this->middlewareStack = array_merge($this->middlewareStack, (array)$middleware);
        
        call_user_func($callback, $this);
        
        $this->currentGroup = $previousGroup;
        $this->middlewareStack = $previousMiddleware;
    }
    
    private function applyGroupPrefix($path) {
        if ($this->currentGroup) {
            return rtrim($this->currentGroup, '/') . '/' . ltrim($path, '/');
        }
        return $path;
    }
    
    public function get($path, $handler, $middleware = []) {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post($path, $handler, $middleware = []) {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put($path, $handler, $middleware = []) {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete($path, $handler, $middleware = []) {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    public function dispatch($requestPath, $requestMethod) {
        $requestPath = parse_url($requestPath, PHP_URL_PATH);
        $matchedRoute = null;
        $params = [];
        
        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $requestMethod && preg_match($pattern, $requestPath, $matches)) {
                $matchedRoute = $route;
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                break;
            }
        }
        
        if ($matchedRoute) {
            $this->executeHandler($matchedRoute, $params);
        } else {
            $this->handleNotFound();
        }
    }
    
    private function convertToRegex($path) {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '@^' . $pattern . '$@';
    }
    
    private function executeHandler($route, $params) {
        try {
            // Apply middleware
            $this->applyMiddleware($route['middleware']);
            
            // Parse handler
            list($controller, $method) = $this->parseHandler($route['handler']);
            
            // Instantiate controller
            $controllerInstance = new $controller();
            
            // Inject dependencies
            $this->injectDependencies($controllerInstance);
            
            // Call method with parameters
            call_user_func_array([$controllerInstance, $method], $params);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    private function parseHandler($handler) {
        if (is_callable($handler)) {
            return $handler;
        }
        
        if (strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            $controllerClass = 'App\\controllers\\' . $controller;
            
            if (!class_exists($controllerClass)) {
                $controllerClass = 'App\\auth\\' . $controller;
            }
            
            if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                return [$controllerClass, $method];
            }
        }
        
        throw new \Exception("Invalid handler: " . print_r($handler, true));
    }
    
    private function applyMiddleware($middlewareList) {
        foreach ($middlewareList as $middleware) {
            $parts = explode(':', $middleware);
            $middlewareClass = 'App\\middleware\\' . $parts[0];
            $args = $parts[1] ?? null;
            
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                if ($args) {
                    $middlewareInstance->handle($args);
                } else {
                    $middlewareInstance->handle();
                }
            } else {
                throw new \Exception("Middleware class not found: $middlewareClass");
            }
        }
    }
    
    private function injectDependencies($instance) {
        if (method_exists($instance, 'setDatabase')) {
            $instance->setDatabase(Database::getInstance());
        }
    }
    
    private function handleNotFound() {
        http_response_code(404);
        include __DIR__ . '/../../src/views/errors/404.php';
        exit;
    }
    
    private function handleException($e) {
        http_response_code(500);
        error_log("Route dispatch error: " . $e->getMessage());
        include __DIR__ . '/../../src/views/errors/500.php';
        exit;
    }
    
    // Define application routes
    public function loadRoutes() {
        $this->group('', function($router) {
            // Authentication routes
            $router->get('/', 'DashboardController@index', ['AuthMiddleware']);
            $router->get('/login', 'Login@showForm');
            $router->post('/login', 'Login@handleLogin');
            $router->get('/register', 'Register@showForm');
            $router->post('/register', 'Register@handleRegistration');
            $router->get('/logout', 'Logout@handleLogout');
            $router->get('/verify.php', 'Verify@verifyEmail');
            
            // Dashboard
            $router->get('/dashboard', 'DashboardController@index', ['AuthMiddleware']);
            
            // Client management
            $router->group('/clients', function($router) {
                $router->get('', 'ClientController@index', ['AuthMiddleware']);
                $router->get('/create', 'ClientController@create', ['AuthMiddleware', 'RoleMiddleware:admin']);
                $router->post('/create', 'ClientController@store', ['AuthMiddleware', 'RoleMiddleware:admin']);
                $router->get('/edit/{id}', 'ClientController@edit', ['AuthMiddleware']);
                $router->post('/update/{id}', 'ClientController@update', ['AuthMiddleware']);
                $router->post('/delete/{id}', 'ClientController@delete', ['AuthMiddleware', 'RoleMiddleware:admin']);
                $router->get('/search', 'ClientController@search', ['AuthMiddleware']);
            });
            
            // Project management
            $router->group('/projects', function($router) {
                $router->get('', 'ProjectController@index', ['AuthMiddleware']);
                $router->get('/create', 'ProjectController@create', ['AuthMiddleware']);
                $router->post('/create', 'ProjectController@store', ['AuthMiddleware']);
                $router->get('/view/{id}', 'ProjectController@view', ['AuthMiddleware']);
                $router->get('/edit/{id}', 'ProjectController@edit', ['AuthMiddleware']);
                $router->post('/update/{id}', 'ProjectController@update', ['AuthMiddleware']);
                $router->post('/delete/{id}', 'ProjectController@delete', ['AuthMiddleware', 'RoleMiddleware:admin']);
            });
            
            // Calendar routes
            $router->group('/calendar', function($router) {
                $router->get('', 'CalendarController@index', ['AuthMiddleware']);
                $router->get('/events', 'CalendarController@getEvents', ['AuthMiddleware']);
                $router->post('/create', 'CalendarController@createEvent', ['AuthMiddleware']);
                $router->post('/update/{id}', 'CalendarController@updateEvent', ['AuthMiddleware']);
                $router->post('/delete/{id}', 'CalendarController@deleteEvent', ['AuthMiddleware']);
            });
            
            // Budget and financial routes
            $router->group('/finance', function($router) {
                $router->get('', 'BudgetController@index', ['AuthMiddleware']);
                $router->post('/transactions/create', 'BudgetController@createTransaction', ['AuthMiddleware']);
                $router->get('/transactions/{id}', 'BudgetController@viewTransaction', ['AuthMiddleware']);
                $router->post('/transactions/update/{id}', 'BudgetController@updateTransaction', ['AuthMiddleware']);
                $router->post('/transactions/delete/{id}', 'BudgetController@deleteTransaction', ['AuthMiddleware', 'RoleMiddleware:admin']);
                $router->get('/reports', 'BudgetController@reports', ['AuthMiddleware']);
            });
            
            // Chat routes
            $router->group('/chat', function($router) {
                $router->get('', 'ChatBotController@index', ['AuthMiddleware']);
                $router->post('/send', 'ChatBotController@sendMessage', ['AuthMiddleware']);
                $router->get('/history', 'ChatBotController@getHistory', ['AuthMiddleware']);
            });
            
            // Export routes
            $router->group('/exports', function($router) {
                $router->get('/clients/csv', 'ExportController@exportClientsCSV', ['AuthMiddleware']);
                $router->get('/projects/csv', 'ExportController@exportProjectsCSV', ['AuthMiddleware']);
                $router->get('/transactions/csv', 'ExportController@exportTransactionsCSV', ['AuthMiddleware']);
                $router->get('/financial/pdf', 'ExportController@exportFinancialPDF', ['AuthMiddleware']);
                $router->get('/receipt/{id}', 'ReceiptController@generate', ['AuthMiddleware']);
            });
            
            // Admin routes
            $router->group('/admin', function($router) {
                $router->get('', 'AdminController@dashboard', ['AuthMiddleware', 'RoleMiddleware:admin']);
                $router->get('/users', 'AdminController@manageUsers', ['AuthMiddleware', 'RoleMiddleware:admin']);
                $router->get('/audit-logs', 'AdminController@auditLogs', ['AuthMiddleware', 'RoleMiddleware:admin']);
                $router->get('/settings', 'AdminController@settings', ['AuthMiddleware', 'RoleMiddleware:admin']);
            });
        });
    }
}
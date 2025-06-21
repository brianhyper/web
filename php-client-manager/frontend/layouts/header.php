<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Client Manager') ?> - Modern CRM</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="/">
                    <i class="fas fa-rocket"></i>
                    <span>ClientManager</span>
                </a>
            </div>
            
            <nav class="nav" id="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <ul>
                        <li><a href="/dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a></li>
                        <li><a href="/clients" class="<?= $currentPage === 'clients' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i> Clients
                        </a></li>
                        <li><a href="/projects" class="<?= $currentPage === 'projects' ? 'active' : '' ?>">
                            <i class="fas fa-tasks"></i> Projects
                        </a></li>
                        <li><a href="/calendar" class="<?= $currentPage === 'calendar' ? 'active' : '' ?>">
                            <i class="fas fa-calendar"></i> Calendar
                        </a></li>
                        <li><a href="/receipts" class="<?= $currentPage === 'receipts' ? 'active' : '' ?>">
                            <i class="fas fa-receipt"></i> Receipts
                        </a></li>
                    </ul>
                <?php endif; ?>
            </nav>
            
            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="user-dropdown">
                            <img src="<?= htmlspecialchars($_SESSION['user_avatar'] ?? '/assets/images/avatar.png') ?>" 
                                 alt="User Avatar" class="avatar">
                            <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            <i class="fas fa-caret-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="/settings"><i class="fas fa-cog"></i> Settings</a>
                            <a href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline">Login</a>
                    <a href="/register" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
            
            <button id="menu-toggle" class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    
    <main class="container">
        <?php include __DIR__ . '/../components/alert.php'; ?>
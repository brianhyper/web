<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /web/client-manager/public/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    
    <title><?= $pageTitle ?> - Client Manager</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-cube"></i>
                    <span>ClientManager</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                        <a href="index.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'clients.php' ? 'active' : '' ?>">
                        <a href="clients.php">
                            <i class="fas fa-users"></i>
                            <span>Clients</span>
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'projects.php' ? 'active' : '' ?>">
                        <a href="projects.php">
                            <i class="fas fa-tasks"></i>
                            <span>Projects</span>
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'budget.php' ? 'active' : '' ?>">
                        <a href="budget.php">
                            <i class="fas fa-chart-pie"></i>
                            <span>Budget</span>
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'calendar.php' ? 'active' : '' ?>">
                        <a href="calendar.php">
                            <i class="fas fa-calendar"></i>
                            <span>Calendar</span>
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'chat.php' ? 'active' : '' ?>">
                        <a href="chat.php">
                            <i class="fas fa-comments"></i>
                            <span>Chat</span>
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'receipts.php' ? 'active' : '' ?>">
                        <a href="receipts.php">
                            <i class="fas fa-receipt"></i>
                            <span>Receipts</span>
                        </a>
                    </li>
                    
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="divider">Admin</li>
                        <li>
                            <a href="users.php">
                                <i class="fas fa-user-cog"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="reports.php">
                                <i class="fas fa-chart-bar"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="profile.php" class="user-profile">
                    <div class="avatar">
                        <?= isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : '?' ?>
                    </div>
                    <div class="user-info">
                        <strong><?= isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User' ?></strong>
                        <small><?= $_SESSION['user_role'] ?></small>
                    </div>
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search...">
                    </div>
                </div>
                
                <div class="topbar-right">
                    <div class="notifications">
                        <button class="btn-icon">
                            <i class="fas fa-bell"></i>
                            <span class="badge">3</span>
                        </button>
                    </div>
                    <div class="messages">
                        <button class="btn-icon">
                            <i class="fas fa-envelope"></i>
                            <span class="badge">5</span>
                        </button>
                    </div>
                    <div class="user-menu">
                        <div class="avatar">
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content-wrapper">
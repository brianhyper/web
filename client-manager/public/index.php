<?php
// client-manager/public/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// client-manager/public/index.php
require '../app.php';
authenticate(['admin', 'staff']);

// Fetch dashboard stats
$stats = [];
$stats['clients'] = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$stats['projects'] = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$stats['active_projects'] = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetchColumn();
$stats['revenue'] = $pdo->query("SELECT SUM(amount) FROM invoices WHERE status = 'paid'")->fetchColumn();

// Recent activities
$activities = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Upcoming deadlines
$deadlines = $pdo->query("SELECT title, deadline, 
                          CASE 
                            WHEN deadline < CURDATE() THEN 'overdue'
                            WHEN deadline <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'upcoming'
                            ELSE 'normal'
                          END AS status
                          FROM projects
                          WHERE deadline IS NOT NULL
                          ORDER BY deadline ASC
                          LIMIT 5")->fetchAll();

$pageTitle = "Dashboard";
include '../header.php';
?>

<main class="dashboard">
    <div class="dashboard-header">
        <h1>Dashboard</h1>
        <div class="header-actions">
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i> New Project
            </button>
        </div>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['clients'] ?></span>
                <span class="stat-label">Clients</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['projects'] ?></span>
                <span class="stat-label">Projects</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-orange">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">$<?= number_format($stats['revenue'], 2) ?></span>
                <span class="stat-label">Revenue</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <i class="fas fa-rocket"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['active_projects'] ?></span>
                <span class="stat-label">Active Projects</span>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-alt"></i> Upcoming Deadlines</h2>
                <a href="calendar.php" class="btn btn-link">View All</a>
            </div>
            
            <div class="deadline-list">
                <?php foreach ($deadlines as $deadline): ?>
                    <div class="deadline-item <?= $deadline['status'] ?>">
                        <div class="deadline-date">
                            <?= date('M j', strtotime($deadline['deadline'])) ?>
                        </div>
                        <div class="deadline-info">
                            <h3><?= htmlspecialchars($deadline['title']) ?></h3>
                            <span class="deadline-status">
                                <?= ucfirst($deadline['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-bell"></i> Recent Activity</h2>
                <a href="activity.php" class="btn btn-link">View All</a>
            </div>
            
            <div class="activity-list">
                <?php foreach ($activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-<?= 
                                strpos($activity['action'], 'create') !== false ? 'plus-circle' : 
                                (strpos($activity['action'], 'update') !== false ? 'edit' : 'check-circle') 
                            ?>"></i>
                        </div>
                        <div class="activity-content">
                            <p><?= htmlspecialchars($activity['action']) ?></p>
                            <small><?= date('M j, H:i', strtotime($activity['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-chart-bar"></i> Revenue Overview</h2>
        </div>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</main>

<?php include '../footer.php'; ?>
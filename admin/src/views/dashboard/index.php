<?php
$pageTitle = "Dashboard";
$currentPage = "dashboard";
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-muted">TOTAL CLIENTS</h5>
                            <h2 class="stat-number"><?= $stats['total_clients'] ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-muted">ACTIVE PROJECTS</h5>
                            <h2 class="stat-number"><?= $stats['active_projects'] ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-muted">PENDING PROJECTS</h5>
                            <h2 class="stat-number"><?= $stats['pending_projects'] ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-muted">RECENT INCOME</h5>
                            <h2 class="stat-number">$<?= number_format($stats['recent_income'], 2) ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Financial Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="financialChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upcoming Events</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($events as $event): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?= $event['title'] ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('M j, g:i a', strtotime($event['start_datetime'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">Project</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item text-center">
                            <a href="/calendar" class="text-decoration-none">View All Events</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Projects</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($projects as $project): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= $project['title'] ?></h6>
                                        <small class="text-muted"><?= $project['client_name'] ?></small>
                                    </div>
                                    <span class="badge bg-<?= $project['status'] === 'active' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($project['status']) ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item text-center">
                            <a href="/projects" class="text-decoration-none">View All Projects</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="/clients/create" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                Add Client
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/projects/create" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                Create Project
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/calendar" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i><br>
                                Add Event
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-plus text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">New client added</h6>
                                    <p class="mb-0">John Doe was added as a new client</p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Invoice paid</h6>
                                    <p class="mb-0">Invoice #INV-2023-001 was paid</p>
                                    <small class="text-muted">5 hours ago</small>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tasks text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Project completed</h6>
                                    <p class="mb-0">Website redesign project marked as completed</p>
                                    <small class="text-muted">Yesterday</small>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
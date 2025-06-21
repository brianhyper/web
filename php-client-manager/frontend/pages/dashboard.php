<?php $currentPage = 'dashboard'; ?>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    
    <div class="dashboard-content">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>! Here's what's happening today.</p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Summary</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3>24</h3>
                                <p>Total Clients</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-info">
                                <h3>18</h3>
                                <p>Active Projects</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3>$12,540</h3>
                                <p>Monthly Revenue</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3>5</h3>
                                <p>Upcoming Meetings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Activity</h2>
                    <a href="#" class="btn btn-link">View All</a>
                </div>
                <div class="card-body">
                    <ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus text-success"></i>
                            </div>
                            <div class="activity-content">
                                <h3>New client added</h3>
                                <p>John Doe was added to the system</p>
                                <span class="activity-time">2 hours ago</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-file-invoice text-primary"></i>
                            </div>
                            <div class="activity-content">
                                <h3>Invoice generated</h3>
                                <p>Invoice #INV-2023-056 for $1,200</p>
                                <span class="activity-time">5 hours ago</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-check-circle text-info"></i>
                            </div>
                            <div class="activity-content">
                                <h3>Project completed</h3>
                                <p>Website redesign project marked as completed</p>
                                <span class="activity-time">Yesterday</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Revenue Overview</h2>
                </div>
                <div class="card-body">
                    <canvas id="revenue-chart" height="250"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Upcoming Events</h2>
                    <a href="/calendar" class="btn btn-link">View Calendar</a>
                </div>
                <div class="card-body">
                    <ul class="event-list">
                        <li class="event-item">
                            <div class="event-date">
                                <span class="event-day">15</span>
                                <span class="event-month">JUN</span>
                            </div>
                            <div class="event-info">
                                <h3>Client Meeting</h3>
                                <p>With John Doe at Acme Inc.</p>
                                <span class="event-time"><i class="fas fa-clock"></i> 10:00 AM - 11:30 AM</span>
                            </div>
                        </li>
                        <li class="event-item">
                            <div class="event-date">
                                <span class="event-day">18</span>
                                <span class="event-month">JUN</span>
                            </div>
                            <div class="event-info">
                                <h3>Project Deadline</h3>
                                <p>Website redesign project delivery</p>
                                <span class="event-time"><i class="fas fa-clock"></i> All day</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
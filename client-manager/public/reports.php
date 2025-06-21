<?php
// client-manager/public/reports.php
require '../app.php';
authenticate(['admin', 'staff']);

$pageTitle = "Reports & Analytics";
include '../header.php';

// Default report period (last 30 days)
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

// Handle report filteringcd 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = sanitize($_POST['start_date']);
    $endDate = sanitize($_POST['end_date']);
    $reportType = sanitize($_POST['report_type']);
}

// Get financial data for charts
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) AS date,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
    FROM transactions
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$startDate, $endDate]);
$financialData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare chart data
$labels = [];
$incomeData = [];
$expenseData = [];

foreach ($financialData as $row) {
    $labels[] = date('M d', strtotime($row['date']));
    $incomeData[] = $row['income'];
    $expenseData[] = $row['expense'];
}

// Get project status data
$stmt = $pdo->query("
    SELECT 
        status,
        COUNT(*) AS count
    FROM projects
    GROUP BY status
");
$projectStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Clients by status
$stmt = $pdo->query("
    SELECT 
        status,
        COUNT(*) AS count
    FROM clients
    GROUP BY status
");
$clientsByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top clients by revenue
$stmt = $pdo->prepare("
    SELECT 
        c.name,
        SUM(r.amount) AS total_revenue
    FROM receipts r
    JOIN clients c ON r.client_id = c.id
    WHERE r.status = 'paid' AND r.payment_date BETWEEN ? AND ?
    GROUP BY c.id
    ORDER BY total_revenue DESC
    LIMIT 5
");
$stmt->execute([$startDate, $endDate]);
$topClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
        <div class="actions">
            <button class="btn btn-primary" id="exportReportBtn">
                <i class="fas fa-file-export"></i> Export Report
            </button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Report Filters</h2>
        </div>
        <div class="card-body">
            <form method="POST" class="report-filters">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" 
                                   class="form-control" value="<?= $startDate ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" 
                                   class="form-control" value="<?= $endDate ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" class="form-control">
                                <option value="financial">Financial Summary</option>
                                <option value="projects">Project Analysis</option>
                                <option value="clients">Client Statistics</option>
                                <option value="performance">Performance Metrics</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <!-- Financial Summary -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-money-bill-wave"></i> Financial Summary</h2>
                <span class="badge badge-primary">
                    <?= date('M d, Y', strtotime($startDate)) ?> - <?= date('M d, Y', strtotime($endDate)) ?>
                </span>
            </div>
            <div class="chart-container">
                <canvas id="financeChart"></canvas>
            </div>
            
            <div class="financial-metrics">
                <div class="metric-card">
                    <div class="metric-icon bg-green">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="metric-info">
                        <span class="metric-value">
                            $<?= number_format(array_sum($incomeData), 2) ?>
                        </span>
                        <span class="metric-label">Total Income</span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon bg-red">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="metric-info">
                        <span class="metric-value">
                            $<?= number_format(array_sum($expenseData), 2) ?>
                        </span>
                        <span class="metric-label">Total Expenses</span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon bg-blue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-info">
                        <span class="metric-value">
                            $<?= number_format(array_sum($incomeData) - array_sum($expenseData), 2) ?>
                        </span>
                        <span class="metric-label">Net Profit</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Project Analysis -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-tasks"></i> Project Analysis</h2>
            </div>
            <div class="chart-container">
                <canvas id="projectsChart"></canvas>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalProjects = array_sum(array_column($projectStatus, 'count'));
                        foreach ($projectStatus as $status):
                            $percentage = $totalProjects > 0 ? round(($status['count'] / $totalProjects) * 100) : 0;
                            $statusClassMap = [
                                'not_started' => 'secondary',
                                'in_progress' => 'primary',
                                'on_hold'     => 'warning',
                                'completed'   => 'success',
                                'planned'     => 'info',
                                'active'      => 'success'
                            ];
                            $statusClass = $statusClassMap[$status['status']] ?? 'secondary';
                        ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <?= ucwords(str_replace('_', ' ', $status['status'])) ?>
                                    </span>
                                </td>
                                <td><?= $status['count'] ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?= $statusClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= $percentage ?>%;" 
                                             aria-valuenow="<?= $percentage ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $percentage ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <!-- Client Statistics -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Client Statistics</h2>
            </div>
            <div class="chart-container">
                <canvas id="clientsChart"></canvas>
            </div>
            
            <div class="client-stats">
                <div class="stat-item">
                    <i class="fas fa-user-check text-success"></i>
                    <span class="stat-value">
                        <?= $clientsByStatus[0]['count'] ?? 0 ?>
                    </span>
                    <span class="stat-label">Active Clients</span>
                </div>
                
                <div class="stat-item">
                    <i class="fas fa-user-clock text-warning"></i>
                    <span class="stat-value">
                        <?= $clientsByStatus[1]['count'] ?? 0 ?>
                    </span>
                    <span class="stat-label">Inactive Clients</span>
                </div>
            </div>
        </div>
        
        <!-- Top Clients -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-trophy"></i> Top Clients by Revenue</h2>
            </div>
            <div class="top-clients">
                <?php if (!empty($topClients)): ?>
                    <?php foreach ($topClients as $index => $client): 
                        $rankClass = [
                            'text-primary', 'text-secondary', 'text-success'
                        ][$index % 3];
                    ?>
                        <div class="client-item">
                            <div class="client-rank">
                                <span class="rank-number <?= $rankClass ?>">#<?= $index + 1 ?></span>
                            </div>
                            <div class="client-info">
                                <h3><?= htmlspecialchars($client['name']) ?></h3>
                                <div class="client-revenue">
                                    <i class="fas fa-money-bill-wave"></i>
                                    $<?= number_format($client['total_revenue'], 2) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-pie"></i>
                        <p>No revenue data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Export Format Modal -->
<div class="modal" id="exportFormatModal" tabindex="-1" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); align-items:center; justify-content:center;">
  <div style="background:#fff; padding:30px; border-radius:8px; min-width:300px; text-align:center;">
    <h4>Choose Export Format</h4>
    <button class="btn btn-primary" id="exportCsvBtn">CSV</button>
    <button class="btn btn-danger" id="exportPdfBtn">PDF</button>
    <br><br>
    <button class="btn btn-secondary" id="closeExportModal">Cancel</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Financial Chart
    const financeCtx = document.getElementById('financeChart').getContext('2d');
    const financeChart = new Chart(financeCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Income',
                    data: <?= json_encode($incomeData) ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: <?= json_encode($expenseData) ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Projects Chart
    const projectsCtx = document.getElementById('projectsChart').getContext('2d');
    const projectsChart = new Chart(projectsCtx, {
        type: 'doughnut',
        data: {
            labels: [
                <?php foreach ($projectStatus as $status): ?>
                    "<?= ucwords(str_replace('_', ' ', $status['status'])) ?>",
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($projectStatus as $status): ?>
                        <?= $status['count'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    'rgba(108, 117, 125, 0.7)', // not_started
                    'rgba(26, 115, 232, 0.7)',   // in_progress
                    'rgba(255, 193, 7, 0.7)',    // on_hold
                    'rgba(40, 167, 69, 0.7)'     // completed
                ],
                borderColor: [
                    'rgb(108, 117, 125)',
                    'rgb(26, 115, 232)',
                    'rgb(255, 193, 7)',
                    'rgb(40, 167, 69)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    // Clients Chart
    const clientsCtx = document.getElementById('clientsChart').getContext('2d');
    const clientsChart = new Chart(clientsCtx, {
        type: 'pie',
        data: {
            labels: [
                <?php foreach ($clientsByStatus as $status): ?>
                    "<?= ucfirst($status['status']) ?>",
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($clientsByStatus as $status): ?>
                        <?= $status['count'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',   // active
                    'rgba(255, 193, 7, 0.7)'     // inactive
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    // Export Report
    document.getElementById('exportReportBtn').addEventListener('click', function() {
        const type = document.getElementById('report_type').value;
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        
        // Show export format modal
        document.getElementById('exportFormatModal').style.display = 'flex';
    });
    
    // Close export modal
    document.getElementById('closeExportModal').addEventListener('click', function() {
        document.getElementById('exportFormatModal').style.display = 'none';
    });
    
    // Export as CSV
    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        const type = document.getElementById('report_type').value;
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        
        window.location.href = `/web/client-manager/public/export_report.php?type=${type}&start=${start}&end=${end}&format=csv`;
    });
    
    // Export as PDF
    document.getElementById('exportPdfBtn').addEventListener('click', function() {
        const type = document.getElementById('report_type').value;
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        
        window.location.href = `/web/client-manager/public/export_report.php?type=${type}&start=${start}&end=${end}&format=pdf`;
    });
});
</script>

<style>
.report-filters {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: var(--border-radius);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.dashboard-section {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    transition: transform 0.3s ease;
}

.dashboard-section:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 10px;
}

.chart-container {
    height: 300px;
    margin-bottom: 20px;
    position: relative;
}

.financial-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.metric-card {
    background: #f8f9fa;
    border-radius: var(--border-radius);
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.metric-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.bg-green { background-color: #28a745; }
.bg-red { background-color: #dc3545; }
.bg-blue { background-color: #1a73e8; }

.metric-value {
    font-size: 1.2rem;
    font-weight: 600;
    display: block;
}

.metric-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.client-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
}

.stat-item i {
    font-size: 2rem;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    display: block;
}

.stat-label {
    color: var(--text-secondary);
}

.top-clients {
    margin-top: 20px;
}

.client-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
}

.client-item:last-child {
    border-bottom: none;
}

.client-rank {
    width: 40px;
    height: 40px;
    background-color: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.rank-number {
    font-weight: 700;
    font-size: 1.2rem;
}

.client-info {
    flex: 1;
}

.client-info h3 {
    margin: 0;
    font-size: 1.1rem;
}

.client-revenue {
    color: var(--primary);
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 30px 0;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 10px;
    opacity: 0.3;
}

.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.3);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    min-width: 300px;
    text-align: center;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .financial-metrics {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../footer.php'; ?>
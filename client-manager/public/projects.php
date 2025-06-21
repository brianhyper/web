<?php
// client-manager/public/projects.php
require '../app.php';
authenticate(['admin', 'staff']);

// Get projects with client names
$projects = $pdo->query("
    SELECT p.*, c.name AS client_name 
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    ORDER BY p.deadline DESC
")->fetchAll();

// Get all clients for dropdown
$clients = $pdo->query("SELECT id, name FROM clients WHERE status = 'active'")->fetchAll();

$pageTitle = "Project Management";
include '../header.php';
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-tasks"></i> Projects</h1>
        <div class="actions">
            <button id="newProjectBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Project
            </button>
        </div>
    </div>

    <div class="project-grid">
        <?php foreach ($projects as $project): 
            $statusClass = '';
            $statusText = ucfirst($project['status']);
            
            if ($project['status'] === 'completed') {
                $statusClass = 'bg-success';
            } elseif ($project['deadline'] && strtotime($project['deadline']) < time()) {
                $statusClass = 'bg-danger';
                $statusText = 'Overdue';
            } elseif ($project['status'] === 'in_progress') {
                $statusClass = 'bg-primary';
            } else {
                $statusClass = 'bg-warning';
            }
        ?>
            <div class="project-card">
                <div class="project-header">
                    <h3><?= htmlspecialchars($project['title']) ?></h3>
                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                </div>
                
                <div class="project-client">
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars($project['client_name'] ?? 'No Client') ?>
                </div>
                
                <div class="project-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <strong>Deadline:</strong>
                        <?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : 'Not set' ?>
                    </div>
                    
                    <div class="meta-item">
                        <i class="fas fa-wallet"></i>
                        <strong>Budget:</strong>
                        $<?= number_format($project['budget'], 2) ?>
                    </div>
                </div>
                
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" 
                         style="width: <?= $project['progress'] ?>%;" 
                         aria-valuenow="<?= $project['progress'] ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
                <div class="progress-label"><?= $project['progress'] ?>% Complete</div>
                
                <div class="project-actions">
                    <button class="btn btn-sm btn-outline-primary view-project" data-id="<?= $project['id'] ?>">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-primary edit-project" data-id="<?= $project['id'] ?>">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalTitle">New Project</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="projectForm" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" id="projectId" name="id">
                    
                    <div class="form-group">
                        <label for="title">Project Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_id">Client</label>
                                <select id="client_id" name="client_id" class="form-control">
                                    <option value="">Select Client</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="not_started">Not Started</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deadline">Deadline</label>
                                <input type="date" id="deadline" name="deadline" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="budget">Budget ($)</label>
                                <input type="number" id="budget" name="budget" class="form-control" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="progress">Progress (%)</label>
                                <input type="range" id="progress" name="progress" class="form-control-range" min="0" max="100">
                                <span id="progressValue">0%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectModal = new bootstrap.Modal(document.getElementById('projectModal'));
    const progressSlider = document.getElementById('progress');
    const progressValue = document.getElementById('progressValue');
    
    // Update progress value display
    progressSlider.addEventListener('input', () => {
        progressValue.textContent = progressSlider.value + '%';
    });
    
    // New Project Button
    document.getElementById('newProjectBtn').addEventListener('click', () => {
        document.getElementById('projectModalTitle').textContent = 'New Project';
        document.getElementById('projectForm').reset();
        progressValue.textContent = '0%';
        projectModal.show();
    });
    
    // Edit Project
    document.querySelectorAll('.edit-project').forEach(btn => {
        btn.addEventListener('click', function() {
            const projectId = this.dataset.id;
            fetch(`/api/projects/${projectId}`)
                .then(response => response.json())
                .then(project => {
                    document.getElementById('projectModalTitle').textContent = 'Edit Project';
                    document.getElementById('projectId').value = project.id;
                    document.getElementById('title').value = project.title;
                    document.getElementById('client_id').value = project.client_id;
                    document.getElementById('status').value = project.status;
                    document.getElementById('start_date').value = project.start_date;
                    document.getElementById('deadline').value = project.deadline;
                    document.getElementById('budget').value = project.budget;
                    document.getElementById('progress').value = project.progress;
                    document.getElementById('description').value = project.description;
                    progressValue.textContent = project.progress + '%';
                    projectModal.show();
                });
        });
    });
    
    // Submit Form
    document.getElementById('projectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/projects', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                projectModal.hide();
                location.reload(); // Refresh to show changes
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
});
</script>

<style>
.project-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.project-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    transition: transform 0.3s ease;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.project-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.project-header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.project-client {
    color: var(--text-secondary);
    margin-bottom: 15px;
}

.project-meta {
    margin-bottom: 15px;
}

.meta-item {
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.progress {
    margin: 15px 0 8px;
}

.progress-label {
    text-align: right;
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.project-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
</style>

<?php include '../footer.php'; ?>
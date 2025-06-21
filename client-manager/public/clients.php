<?php
// client-manager/public/clients.php
require '../app.php';
authenticate(['admin', 'staff']);

$pageTitle = "Client Management";
include '../header.php';

// Get client list
$clients = $pdo->query("SELECT * FROM clients ORDER BY created_at DESC")->fetchAll();
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Clients</h1>
        <div class="actions">
            <button id="newClientBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Client
            </button>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" id="exportCSV">CSV</a>
                    <a class="dropdown-item" href="#" id="exportPDF">PDF</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="toolbar">
                <div class="search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="clientSearch" placeholder="Search clients...">
                </div>
                <div class="filters">
                    <select id="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="clientsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Projects</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr data-id="<?= $client['id'] ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar mr-2">
                                            <?= strtoupper(substr($client['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($client['name']) ?></strong>
                                            <div class="text-muted small">Added: <?= date('M d, Y', strtotime($client['created_at'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($client['company'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($client['email']) ?></td>
                                <td><?= htmlspecialchars($client['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= $pdo->query("SELECT COUNT(*) FROM projects WHERE client_id = {$client['id']}")->fetchColumn() ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $client['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($client['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary view-client" data-id="<?= $client['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary edit-client" data-id="<?= $client['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-client" data-id="<?= $client['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Client Modal -->
<div class="modal fade" id="clientModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">New Client</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="clientForm" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" id="clientId" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company">Company</label>
                                <input type="text" id="company" name="company" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Client CRUD operations
    const clientModal = new bootstrap.Modal(document.getElementById('clientModal'));
    
    // New Client Button
    document.getElementById('newClientBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'New Client';
        document.getElementById('clientForm').reset();
        clientModal.show();
    });
    
    // Edit Client
    document.querySelectorAll('.edit-client').forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.dataset.id;
            fetch(`/api/clients/${clientId}`)
                .then(response => response.json())
                .then(client => {
                    document.getElementById('modalTitle').textContent = 'Edit Client';
                    document.getElementById('clientId').value = client.id;
                    document.getElementById('name').value = client.name;
                    document.getElementById('company').value = client.company;
                    document.getElementById('email').value = client.email;
                    document.getElementById('phone').value = client.phone;
                    document.getElementById('address').value = client.address;
                    document.getElementById('status').value = client.status;
                    document.getElementById('notes').value = client.notes;
                    clientModal.show();
                });
        });
    });
    
    // Submit Form
    document.getElementById('clientForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/clients', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                clientModal.hide();
                location.reload(); // Refresh to show changes
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
    
    // Delete Client
    document.querySelectorAll('.delete-client').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this client?')) {
                const clientId = this.dataset.id;
                fetch(`/api/clients/${clientId}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`tr[data-id="${clientId}"]`).remove();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        });
    });
});
</script>

<?php include '../footer.php'; ?>
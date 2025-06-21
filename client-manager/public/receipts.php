<?php
// client-manager/public/receipts.php
require '../app.php';
authenticate(['admin', 'staff']);

$pageTitle = "Receipt Management";
include '../header.php';

// Get receipts with client names
$receipts = $pdo->query("
    SELECT r.*, c.name AS client_name 
    FROM receipts r
    LEFT JOIN clients c ON r.client_id = c.id
    ORDER BY r.created_at DESC
")->fetchAll();

// Get clients for dropdown
$clients = $pdo->query("SELECT id, name FROM clients WHERE status = 'active'")->fetchAll();
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-receipt"></i> Receipts</h1>
        <div class="actions">
            <button id="newReceiptBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Receipt
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="receiptsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipts as $receipt): 
                            $statusClass = [
                                'paid' => 'success',
                                'unpaid' => 'danger',
                                'deposit' => 'warning'
                            ][$receipt['status']];
                        ?>
                            <tr>
                                <td>#<?= $receipt['id'] ?></td>
                                <td><?= htmlspecialchars($receipt['client_name']) ?></td>
                                <td>$<?= number_format($receipt['amount'], 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <?= ucfirst($receipt['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($receipt['issue_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($receipt['due_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($receipt['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/functions/generate_pdf.php?id=<?= $receipt['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary edit-receipt" 
                                                data-id="<?= $receipt['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-receipt" 
                                                data-id="<?= $receipt['id'] ?>">
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

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalTitle">New Receipt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="receiptForm" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" id="receiptId" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_id">Client *</label>
                                <select id="client_id" name="client_id" class="form-control" required>
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
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="deposit">Deposit</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">Amount ($) *</label>
                                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="issue_date">Issue Date *</label>
                                <input type="date" id="issue_date" name="issue_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="due_date">Due Date *</label>
                                <input type="date" id="due_date" name="due_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    
    // New Receipt Button
    document.getElementById('newReceiptBtn').addEventListener('click', () => {
        document.getElementById('receiptModalTitle').textContent = 'New Receipt';
        document.getElementById('receiptForm').reset();
        document.getElementById('issue_date').valueAsDate = new Date();
        
        // Set due date to 30 days from now
        const dueDate = new Date();
        dueDate.setDate(dueDate.getDate() + 30);
        document.getElementById('due_date').valueAsDate = dueDate;
        
        receiptModal.show();
    });
    
    // Edit Receipt
    document.querySelectorAll('.edit-receipt').forEach(btn => {
        btn.addEventListener('click', function() {
            const receiptId = this.dataset.id;
            fetch(`/api/receipts/${receiptId}`)
                .then(response => response.json())
                .then(receipt => {
                    document.getElementById('receiptModalTitle').textContent = 'Edit Receipt';
                    document.getElementById('receiptId').value = receipt.id;
                    document.getElementById('client_id').value = receipt.client_id;
                    document.getElementById('status').value = receipt.status;
                    document.getElementById('amount').value = receipt.amount;
                    document.getElementById('issue_date').value = receipt.issue_date;
                    document.getElementById('due_date').value = receipt.due_date;
                    document.getElementById('description').value = receipt.description;
                    document.getElementById('notes').value = receipt.notes;
                    receiptModal.show();
                });
        });
    });
    
    // Submit Form
    document.getElementById('receiptForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/receipts', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                receiptModal.hide();
                location.reload(); // Refresh to show changes
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
    
    // Delete Receipt
    document.querySelectorAll('.delete-receipt').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this receipt?')) {
                const receiptId = this.dataset.id;
                fetch(`/api/receipts/${receiptId}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
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
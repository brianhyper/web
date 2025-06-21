<?php
// client-manager/public/budget.php
require '../app.php';
authenticate(['admin', 'staff']);

// Get income/expense data for chart
$financialData = $pdo->query("
    SELECT 
        YEAR(created_at) AS year,
        MONTH(created_at) AS month,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
    FROM transactions
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY year, month
")->fetchAll();

// Prepare chart data
$labels = [];
$incomeData = [];
$expenseData = [];

foreach ($financialData as $row) {
    $monthName = date('M', mktime(0, 0, 0, $row['month'], 1));
    $labels[] = $monthName . ' ' . $row['year'];
    $incomeData[] = $row['income'];
    $expenseData[] = $row['expense'];
}

$pageTitle = "Budget Tracking";
include '../header.php';
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-chart-pie"></i> Budget Tracking</h1>
        <div class="actions">
            <button id="newTransactionBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Transaction
            </button>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-chart-line"></i> Financial Overview</h2>
            </div>
            <div class="chart-container">
                <canvas id="financeChart"></canvas>
            </div>
        </div>
        
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-exchange-alt"></i> Recent Transactions</h2>
                <a href="transactions.php" class="btn btn-link">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $transactions = $pdo->query("
                            SELECT * FROM transactions 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ")->fetchAll();
                        
                        foreach ($transactions as $transaction): 
                            $typeClass = $transaction['type'] === 'income' ? 'text-success' : 'text-danger';
                        ?>
                            <tr>
                                <td><?= date('M d', strtotime($transaction['created_at'])) ?></td>
                                <td><?= htmlspecialchars($transaction['description']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $transaction['type'] === 'income' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($transaction['type']) ?>
                                    </span>
                                </td>
                                <td class="<?= $typeClass ?>">
                                    <?= $transaction['type'] === 'income' ? '+' : '-' ?>
                                    $<?= number_format($transaction['amount'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-wallet"></i> Account Balances</h2>
        </div>
        <div class="account-cards">
            <?php 
            $accounts = $pdo->query("SELECT * FROM accounts")->fetchAll();
            
            foreach ($accounts as $account): 
                $balanceClass = $account['balance'] >= 0 ? 'text-success' : 'text-danger';
            ?>
                <div class="account-card">
                    <div class="account-icon">
                        <i class="fas fa-<?= $account['type'] === 'bank' ? 'university' : 'money-bill-wave' ?>"></i>
                    </div>
                    <div class="account-info">
                        <h3><?= htmlspecialchars($account['name']) ?></h3>
                        <span class="account-number"><?= htmlspecialchars($account['account_number']) ?></span>
                    </div>
                    <div class="account-balance <?= $balanceClass ?>">
                        $<?= number_format($account['balance'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<!-- Transaction Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalTitle">New Transaction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="transactionForm" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" id="transactionId" name="id">
                    
                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Amount ($) *</label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="account_id">Account *</label>
                        <select id="account_id" name="account_id" class="form-control" required>
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <input type="text" id="description" name="description" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="general">General</option>
                            <option value="salary">Salary</option>
                            <option value="supplies">Supplies</option>
                            <option value="marketing">Marketing</option>
                            <option value="travel">Travel</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const transactionModal = new bootstrap.Modal(document.getElementById('transactionModal'));
    
    // New Transaction Button
    document.getElementById('newTransactionBtn').addEventListener('click', () => {
        document.getElementById('transactionModalTitle').textContent = 'New Transaction';
        document.getElementById('transactionForm').reset();
        document.getElementById('date').valueAsDate = new Date();
        transactionModal.show();
    });
    
    // Finance Chart
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
});
</script>

<style>
.account-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.account-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.account-icon {
    width: 50px;
    height: 50px;
    background-color: rgba(26, 115, 232, 0.1);
    color: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.account-info {
    flex: 1;
}

.account-info h3 {
    margin: 0;
    font-size: 1rem;
}

.account-number {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.account-balance {
    font-weight: 600;
    font-size: 1.1rem;
}
</style>

<?php include '../footer.php'; ?>
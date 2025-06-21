<?php $currentPage = 'receipts'; ?>
<div class="receipts">
    <div class="page-header">
        <div>
            <h1 class="page-title">Receipts</h1>
            <p class="page-subtitle">Manage and generate payment receipts</p>
        </div>
        <div class="page-actions">
            <a href="/receipts/new" class="btn btn-primary">
                <i class="fas fa-plus"></i> Generate Receipt
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Receipts</h2>
            <div class="card-actions">
                <div class="filters">
                    <div class="filter-group">
                        <label>Status:</label>
                        <select>
                            <option>All</option>
                            <option>Paid</option>
                            <option>Pending</option>
                            <option>Overdue</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Date:</label>
                        <select>
                            <option>Last 30 days</option>
                            <option>This month</option>
                            <option>Last month</option>
                            <option>Custom</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>R-2023-056</td>
                        <td>Acme Corporation</td>
                        <td>Jun 12, 2023</td>
                        <td>$1,200.00</td>
                        <td>
                            <span class="badge badge-success">Paid</span>
                        </td>
                        <td class="actions">
                            <a href="#" class="btn btn-icon"><i class="fas fa-download"></i></a>
                            <a href="#" class="btn btn-icon"><i class="fas fa-print"></i></a>
                            <a href="#" class="btn btn-icon"><i class="fas fa-envelope"></i></a>
                        </td>
                    </tr>
                    <!-- Additional rows... -->
                </tbody>
            </table>
        </div>
    </div>
</div>
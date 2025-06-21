<?php $currentPage = 'clients'; ?>
<div class="clients">
    <div class="page-header">
        <div>
            <h1 class="page-title">Clients</h1>
            <p class="page-subtitle">Manage your client relationships</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-icon"><i class="fas fa-filter"></i> Filter</button>
            <a href="/clients/new" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Client
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Client List</h2>
            <div class="card-actions">
                <div class="search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search clients...">
                </div>
                <button class="btn btn-icon"><i class="fas fa-download"></i> Export</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <label class="checkbox">
                                <input type="checkbox"> 
                            </label>
                        </th>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Projects</th>
                        <th>Last Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <label class="checkbox">
                                <input type="checkbox"> 
                            </label>
                        </td>
                        <td>
                            <div class="client-info">
                                <div class="client-avatar">
                                    <img src="/assets/images/avatar1.png" alt="Client Avatar">
                                </div>
                                <div>
                                    <div class="client-name">Acme Corporation</div>
                                    <div class="client-company">Technology Solutions</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div>John Smith</div>
                                <div class="text-muted">john@acme.com</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-success">Active</span>
                        </td>
                        <td>3</td>
                        <td>Jun 12, 2023</td>
                        <td class="actions">
                            <a href="#" class="btn btn-icon"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn btn-icon"><i class="fas fa-edit"></i></a>
                            <a href="#" class="btn btn-icon btn-danger"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <!-- Additional rows... -->
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="table-footer">
                <div>Showing 1 to 10 of 45 entries</div>
                <div class="pagination">
                    <button class="btn btn-icon" disabled><i class="fas fa-chevron-left"></i></button>
                    <button class="btn active">1</button>
                    <button class="btn">2</button>
                    <button class="btn">3</button>
                    <button class="btn">4</button>
                    <button class="btn">5</button>
                    <button class="btn btn-icon"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
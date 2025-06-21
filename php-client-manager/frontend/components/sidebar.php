<aside class="sidebar">
    <div class="sidebar-header">
        <h3>Navigation</h3>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="/dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Overview
                </a>
            </li>
            <li>
                <a href="/clients" class="<?= $currentPage === 'clients' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Clients
                </a>
            </li>
            <li>
                <a href="/projects" class="<?= $currentPage === 'projects' ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i> Projects
                </a>
            </li>
            <li>
                <a href="/calendar" class="<?= $currentPage === 'calendar' ? 'active' : '' ?>">
                    <i class="fas fa-calendar"></i> Calendar
                </a>
            </li>
            <li>
                <a href="/receipts" class="<?= $currentPage === 'receipts' ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice"></i> Receipts
                </a>
            </li>
            <li>
                <a href="/analytics" class="<?= $currentPage === 'analytics' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </li>
            <li>
                <a href="/settings" class="<?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="help-card">
            <i class="fas fa-question-circle"></i>
            <h4>Need Help?</h4>
            <p>Contact our support team</p>
            <a href="mailto:support@clientmanager.com" class="btn btn-outline">Contact</a>
        </div>
    </div>
</aside>
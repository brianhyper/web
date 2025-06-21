<?php $currentPage = 'projects'; ?>
<div class="projects">
    <div class="page-header">
        <div>
            <h1 class="page-title">Projects</h1>
            <p class="page-subtitle">Track and manage your projects</p>
        </div>
        <div class="page-actions">
            <div class="view-toggle">
                <button class="btn btn-icon active"><i class="fas fa-list"></i></button>
                <button class="btn btn-icon"><i class="fas fa-th"></i></button>
            </div>
            <a href="/projects/new" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Project
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Project List</h2>
            <div class="card-actions">
                <div class="tabs">
                    <button class="tab active">All Projects</button>
                    <button class="tab">Active</button>
                    <button class="tab">Completed</button>
                    <button class="tab">Archived</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="project-list">
                <div class="project-card">
                    <div class="project-header">
                        <h3 class="project-title">Website Redesign</h3>
                        <div class="project-meta">
                            <span class="badge badge-primary">In Progress</span>
                            <span class="priority high">High Priority</span>
                        </div>
                    </div>
                    <div class="project-body">
                        <p>Complete redesign of company website with modern UI/UX</p>
                        <div class="project-progress">
                            <div class="progress">
                                <div class="progress-bar" style="width: 65%"></div>
                            </div>
                            <span>65% complete</span>
                        </div>
                        <div class="project-meta">
                            <div>
                                <i class="fas fa-calendar"></i>
                                <span>Due: Jun 30, 2023</span>
                            </div>
                            <div>
                                <i class="fas fa-user"></i>
                                <span>John Smith</span>
                            </div>
                        </div>
                    </div>
                    <div class="project-footer">
                        <div class="project-team">
                            <div class="team-members">
                                <img src="/assets/images/avatar1.png" alt="Member">
                                <img src="/assets/images/avatar2.png" alt="Member">
                                <img src="/assets/images/avatar3.png" alt="Member">
                                <span class="more">+2</span>
                            </div>
                        </div>
                        <div class="project-actions">
                            <a href="#" class="btn btn-icon"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn btn-icon"><i class="fas fa-edit"></i></a>
                        </div>
                    </div>
                </div>
                <!-- Additional project cards... -->
            </div>
        </div>
    </div>
</div>
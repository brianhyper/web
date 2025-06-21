<?php $currentPage = 'calendar'; ?>
<div class="calendar">
    <div class="page-header">
        <div>
            <h1 class="page-title">Calendar</h1>
            <p class="page-subtitle">Schedule and track your events</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i> New Event
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">June 2023</h2>
            <div class="calendar-controls">
                <button class="btn btn-icon"><i class="fas fa-chevron-left"></i></button>
                <button class="btn">Today</button>
                <button class="btn btn-icon"><i class="fas fa-chevron-right"></i></button>
                <div class="view-options">
                    <button class="btn">Day</button>
                    <button class="btn">Week</button>
                    <button class="btn active">Month</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="calendar-container">
                <div class="calendar-header">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>
                <div class="calendar-body">
                    <!-- Calendar days will be populated via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Upcoming Events</h2>
        </div>
        <div class="card-body">
            <div class="event-list">
                <div class="event-item">
                    <div class="event-time">
                        <span class="event-start">10:00 AM</span>
                        <span class="event-end">11:30 AM</span>
                    </div>
                    <div class="event-details">
                        <h3>Client Meeting - Acme Corp</h3>
                        <p>Discuss project requirements</p>
                        <div class="event-meta">
                            <span class="event-location"><i class="fas fa-map-marker-alt"></i> Conference Room A</span>
                            <span class="event-attendees"><i class="fas fa-users"></i> 5 attendees</span>
                        </div>
                    </div>
                    <div class="event-actions">
                        <button class="btn btn-icon"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
                <!-- Additional events... -->
            </div>
        </div>
    </div>
</div>
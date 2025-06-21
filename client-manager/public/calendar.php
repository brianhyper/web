<?php
// client-manager/public/calendar.php
require '../app.php';
authenticate(['admin', 'staff']);

// Get events for FullCalendar
$events = $pdo->query("
    SELECT 
        events.id, 
        events.title, 
        events.start, 
        events.end,
        CASE 
            WHEN events.status = 'completed' THEN '#28a745'
            WHEN events.deadline < CURDATE() THEN '#dc3545'
            WHEN events.deadline <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN '#ffc107'
            ELSE '#1a73e8'
        END AS color
    FROM events

    UNION

    SELECT 
        r.id, 
        CONCAT('Payment: ', c.name) AS title,
        r.payment_date AS start,
        r.payment_date AS end,
        CASE 
            WHEN r.status = 'paid' THEN '#28a745'
            WHEN r.status = 'deposit' THEN '#fd7e14'
            WHEN r.payment_date < CURDATE() THEN '#dc3545'
            WHEN r.payment_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN '#ffc107'
            ELSE '#1a73e8'
        END AS color
    FROM receipts r
    JOIN clients c ON r.client_id = c.id
")->fetchAll();

$pageTitle = "Calendar";
include '../header.php';
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Calendar</h1>
        <div class="actions">
            <button id="newEventBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Event
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</main>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">New Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="eventForm" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" id="eventId" name="id">
                    
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start">Start Date/Time *</label>
                                <input type="datetime-local" id="start" name="start" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end">End Date/Time</label>
                                <input type="datetime-local" id="end" name="end" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="color">Color</label>
                        <select id="color" name="color" class="form-control">
                            <option value="#1a73e8">Blue (Default)</option>
                            <option value="#28a745">Green (Completed)</option>
                            <option value="#ffc107">Yellow (Upcoming)</option>
                            <option value="#fd7e14">Orange (Warning)</option>
                            <option value="#dc3545">Red (Urgent)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveEventBtn">Save Event</button>
                    <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include FullCalendar CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    const events = <?= json_encode($events) ?>;
    
    // Initialize calendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventClick: function(info) {
            document.getElementById('eventModalTitle').textContent = 'Edit Event';
            document.getElementById('eventId').value = info.event.id;
            document.getElementById('title').value = info.event.title;
            document.getElementById('start').value = formatDateTime(info.event.start);
            document.getElementById('end').value = info.event.end ? formatDateTime(info.event.end) : '';
            document.getElementById('description').value = info.event.extendedProps.description || '';
            document.getElementById('color').value = info.event.backgroundColor;
            
            document.getElementById('deleteEventBtn').style.display = 'inline-block';
            eventModal.show();
        },
        dateClick: function(info) {
            document.getElementById('eventModalTitle').textContent = 'New Event';
            document.getElementById('eventForm').reset();
            document.getElementById('start').value = info.dateStr + 'T09:00';
            
            document.getElementById('deleteEventBtn').style.display = 'none';
            eventModal.show();
        }
    });
    
    calendar.render();
    
    // Format date for datetime-local input
    function formatDateTime(date) {
        return date.toISOString().slice(0, 16);
    }
    
    // New Event Button
    document.getElementById('newEventBtn').addEventListener('click', () => {
        document.getElementById('eventModalTitle').textContent = 'New Event';
        document.getElementById('eventForm').reset();
        document.getElementById('start').valueAsDate = new Date();
        
        document.getElementById('deleteEventBtn').style.display = 'none';
        eventModal.show();
    });
    
    // Submit Form
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/events', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                eventModal.hide();
                calendar.refetchEvents();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
    
    // Delete Event
    document.getElementById('deleteEventBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this event?')) {
            const eventId = document.getElementById('eventId').value;
            
            fetch(`/api/events/${eventId}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        eventModal.hide();
                        calendar.refetchEvents();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
    });
});
</script>

<style>
#calendar {
    max-width: 100%;
    margin: 0 auto;
}

.fc-event {
    cursor: pointer;
    border-radius: 4px;
    font-size: 0.9rem;
    padding: 2px 5px;
}

.fc-daygrid-event-dot {
    display: none;
}
</style>

<?php include '../footer.php'; ?>
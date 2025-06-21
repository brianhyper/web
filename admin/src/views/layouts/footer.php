    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= $_ENV['APP_NAME'] ?></h5>
                    <p class="text-muted">Comprehensive client and project management solution for modern businesses.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/dashboard" class="text-decoration-none text-white-50">Dashboard</a></li>
                        <li><a href="/clients" class="text-decoration-none text-white-50">Clients</a></li>
                        <li><a href="/projects" class="text-decoration-none text-white-50">Projects</a></li>
                        <li><a href="/calendar" class="text-decoration-none text-white-50">Calendar</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Connect</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-decoration-none text-white-50"><i class="fab fa-facebook me-2"></i> Facebook</a></li>
                        <li><a href="#" class="text-decoration-none text-white-50"><i class="fab fa-twitter me-2"></i> Twitter</a></li>
                        <li><a href="#" class="text-decoration-none text-white-50"><i class="fab fa-linkedin me-2"></i> LinkedIn</a></li>
                        <li><a href="#" class="text-decoration-none text-white-50"><i class="fab fa-github me-2"></i> GitHub</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center text-muted">
                &copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?>. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('financialChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Income',
                            data: [12000, 19000, 15000, 18000, 22000, 25000],
                            backgroundColor: 'rgba(67, 97, 238, 0.7)',
                            borderColor: 'rgba(67, 97, 238, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Expenses',
                            data: [8000, 12000, 10000, 9000, 11000, 13000],
                            backgroundColor: 'rgba(231, 76, 60, 0.7)',
                            borderColor: 'rgba(231, 76, 60, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Financial Overview'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
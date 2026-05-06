<?php
require_once __DIR__ . '/config/auth.php';
auth_require_login();
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Dashboard - Equipment Tracker';
$pageStyles = '<style>

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.overdue-alert {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border: 1px solid #fecaca;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.overdue-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 0.75rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.overdue-badge {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Additional dashboard styles */
.card-stats {
    position: relative;
}

.card-stats .card-body {
    position: relative;
    padding: 1.5rem 5.5rem 1.5rem 1.5rem;
}

.card-stats .stat-icon {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    width: 56px;
    height: 56px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-bottom: 0;
}

.quick-buttons .btn {
    min-width: 160px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.quick-buttons .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.overdue-alert {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border-left: 4px solid #dc3545;
}

.overdue-item {
    border-left: 3px solid #ffc107;
    padding-left: 0.75rem;
    font-size: 0.95rem;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-top: 20px;
}

.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    background: white;
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd, #4f46e5);
    border: none;
    border-radius: 12px;
    font-weight: 600;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0b5ed7, #4338ca);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #495057);
    border: none;
    border-radius: 12px;
    font-weight: 600;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #3d4144);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'dashboard'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-speedometer2 text-primary me-3"></i>Dashboard
                        </h1>
                        <p class="text-muted mb-0 fs-6">Welcome back, <strong><?php echo htmlspecialchars($user['username']); ?></strong>! Here's your equipment overview.</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="dropdown">
                            <button class="btn btn-light border-0 rounded-pill px-3 py-2 shadow-sm" type="button" id="notificationDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                <span id="notification-count" class="badge bg-danger rounded-pill ms-2" style="display:none;">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" aria-labelledby="notificationDropdownButton" style="min-width: 350px;">
                                <div class="px-3 py-3 border-bottom">
                                    <h6 class="fw-bold mb-0">Notifications</h6>
                                </div>
                                <div id="notification-items" class="list-group list-group-flush"></div>
                                <div class="p-3 text-center border-top">
                                    <button id="mark-all-read" class="btn btn-link btn-sm text-decoration-none">Mark all as read</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        <!-- Stats Cards -->
        <div class="row mb-5">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-semibold mb-1 fs-7 text-uppercase">Total Equipment</p>
                                <h2 class="mb-0 fw-bold text-dark" id="total-count">0</h2>
                                <small class="text-muted">All items</small>
                            </div>
                            <div class="stat-icon bg-primary bg-gradient text-white">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-semibold mb-1 fs-7 text-uppercase">Available</p>
                                <h2 class="mb-0 fw-bold text-success" id="available-count">0</h2>
                                <small class="text-muted">Ready to use</small>
                            </div>
                            <div class="stat-icon bg-success bg-gradient text-white">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-semibold mb-1 fs-7 text-uppercase">In Use</p>
                                <h2 class="mb-0 fw-bold text-warning" id="inuse-count">0</h2>
                                <small class="text-muted">Currently checked out</small>
                            </div>
                            <div class="stat-icon bg-warning bg-gradient text-white">
                                <i class="bi bi-arrow-right-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-semibold mb-1 fs-7 text-uppercase">Maintenance</p>
                                <h2 class="mb-0 fw-bold text-danger" id="maintenance-count">0</h2>
                                <small class="text-muted">Under service</small>
                            </div>
                            <div class="stat-icon bg-danger bg-gradient text-white">
                                <i class="bi bi-wrench"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-5 quick-buttons">
            <?php if ($user['role'] === 'Admin'): ?>
                <div class="col-auto mb-3">
                    <a href="equipment.php" class="btn btn-primary btn-lg px-4 py-3">
                        <i class="bi bi-plus-circle me-2"></i>
                        <span class="fw-semibold">Add Equipment</span>
                    </a>
                </div>
            <?php endif; ?>
            <div class="col-auto mb-3">
                <a href="scan.php" class="btn btn-secondary btn-lg px-4 py-3">
                    <i class="bi bi-qr-code-scan me-2"></i>
                    <span class="fw-semibold">Scan QR Code</span>
                </a>
            </div>
        </div>

        <!-- Overdue Equipment Alert -->
        <div class="row mb-4" id="overdue-section" style="display:none;">
            <div class="col-12">
                <div class="alert alert-danger overdue-alert d-flex align-items-start" role="alert">
                    <i class="bi bi-exclamation-triangle me-3 mt-1" style="font-size: 1.5rem;"></i>
                    <div style="flex: 1;">
                        <h5 class="alert-heading mb-2">⚠️ Overdue Equipment</h5>
                        <p class="mb-0"><span id="overdue-count">0</span> equipment item(s) have not been returned and are now overdue.</p>
                        <div id="overdue-list" class="mt-3"></div>
                        <?php if ($user['role'] === 'Admin'): ?>
                            <button class="btn btn-sm btn-danger mt-2" id="manage-overdue-btn">Manage Overdue</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="row mb-5">
            <div class="col-12 col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-gradient p-2 me-3">
                                <i class="bi bi-pie-chart text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Equipment Status</h5>
                                <small class="text-muted">Distribution overview</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-gradient p-2 me-3">
                                <i class="bi bi-bar-chart text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Quick Summary</h5>
                                <small class="text-muted">Status breakdown</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary p-2 me-3">
                                        <i class="bi bi-box-seam text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="fw-semibold">Total Equipment</span>
                                </div>
                                <span class="badge bg-primary rounded-pill fs-6 px-3 py-2" id="stat-total">0</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success p-2 me-3">
                                        <i class="bi bi-check-circle text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="fw-semibold">Available</span>
                                </div>
                                <span class="badge bg-success rounded-pill fs-6 px-3 py-2" id="stat-available">0</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning p-2 me-3">
                                        <i class="bi bi-arrow-right-circle text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="fw-semibold">In Use</span>
                                </div>
                                <span class="badge bg-warning rounded-pill fs-6 px-3 py-2" id="stat-in-use">0</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-danger p-2 me-3">
                                        <i class="bi bi-wrench text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="fw-semibold">Maintenance</span>
                                </div>
                                <span class="badge bg-danger rounded-pill fs-6 px-3 py-2" id="stat-maintenance">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-gradient p-2 me-3">
                                    <i class="bi bi-activity text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Recent Activity</h5>
                                    <small class="text-muted">Latest equipment actions</small>
                                </div>
                            </div>
                            <a href="logs.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-arrow-right me-1"></i>View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold ps-4">Equipment</th>
                                        <th class="border-0 fw-semibold">Action</th>
                                        <th class="border-0 fw-semibold">User</th>
                                        <th class="border-0 fw-semibold pe-4">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-clock-history fs-1 mb-3 text-muted"></i>
                                            <br>Loading recent activity...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        var statusChart = null;

        function renderStatusChart(data) {
            var ctx = document.getElementById('statusChart').getContext('2d');
            
            if (statusChart) {
                statusChart.destroy();
            }

            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Available', 'In Use', 'Maintenance'],
                    datasets: [{
                        data: [data.available, data.in_use, data.maintenance],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                        borderColor: ['#ffffff', '#ffffff', '#ffffff'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 13 },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });
        }

        function renderOverdueSection(items) {
            var $section = $('#overdue-section');
            var $list = $('#overdue-list');
            
            if (items.length === 0) {
                $section.hide();
                return;
            }

            $section.show();
            $('#overdue-count').text(items.length);
            $list.empty();

            items.forEach(function(item) {
                var days = Math.floor((new Date() - new Date(item.status_updated_at)) / (1000 * 60 * 60 * 24));
                var html = '<div class="overdue-item mb-2">' +
                    '<strong>' + item.name + '</strong>' +
                    '<br><small class="text-muted">Overdue for ' + days + ' days</small>' +
                    '</div>';
                $list.append(html);
            });
        }
        function renderNotifications(data) {
            var $items = $('#notification-items');
            var $count = $('#notification-count');
            $items.empty();

            if (!data || data.length === 0) {
                $items.append('<div class="list-group-item text-center text-muted">No notifications</div>');
                $count.hide();
                return;
            }

            data.forEach(function(item) {
                var labelClass = item.status === 'UNREAD' ? 'font-weight-bold' : 'text-muted';
                var equipmentLabel = item.equipment_name ? ' <small class="text-muted">(' + item.equipment_name + ')</small>' : '';
                var notificationHtml = '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start notification-item ' + labelClass + '" data-id="' + item.id + '">' +
                    '<div>' +
                    '<div>' + item.message + equipmentLabel + '</div>' +
                    '<small class="text-muted">' + item.created_at + '</small>' +
                    '</div>' +
                    '</button>';
                $items.append(notificationHtml);
            });
        }

        function loadDashboardData() {
            $.getJSON('api/get_dashboard_data.php').done(function(response) {
                if (!response || !response.success) {
                    return;
                }

                // Update stats cards
                $('#total-count').text(response.stats.total || 0);
                $('#available-count').text(response.stats.available || 0);
                $('#inuse-count').text(response.stats.in_use || 0);
                $('#maintenance-count').text(response.stats.maintenance || 0);

                // Update quick stats summary
                $('#stat-total').text(response.stats.total || 0);
                $('#stat-available').text(response.stats.available || 0);
                $('#stat-in-use').text(response.stats.in_use || 0);
                $('#stat-maintenance').text(response.stats.maintenance || 0);

                // Render status chart
                renderStatusChart(response.stats);

                // Render overdue section
                if (response.overdueItems) {
                    renderOverdueSection(response.overdueItems);
                }

                // Render recent logs
                var $logsBody = $('tbody').first();
                $logsBody.empty();
                if (Array.isArray(response.recentLogs) && response.recentLogs.length > 0) {
                    response.recentLogs.forEach(function(log) {
                        var row = '<tr>' +
                            '<td class="ps-4">' + $('<div>').text(log.equipment_name || 'Unknown').html() + '</td>' +
                            '<td>' + $('<div>').text(log.action || '').html() + '</td>' +
                            '<td>' + $('<div>').text(log.user || '').html() + '</td>' +
                            '<td class="pe-4">' + $('<div>').text(log.created_at || '').html() + '</td>' +
                            '</tr>';
                        $logsBody.append(row);
                    });
                } else {
                    $logsBody.append('<tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-clock-history fs-1 mb-3 text-muted"></i><br>No recent activity found.</td></tr>');
                }
            });
        }

        function loadNotifications() {
            $.getJSON('api/get_notifications.php').done(function(response) {
                if (response && response.success) {
                    renderNotifications(response.data);
                    var count = response.unread_count || 0;
                    if (count > 0) {
                        $('#notification-count').text(count).show();
                    } else {
                        $('#notification-count').hide();
                    }
                }
            });
        }

        function markNotification(id) {
            $.post('api/mark_notification_read.php', { id: id }, function(response) {
                if (response && response.success) {
                    loadNotifications();
                }
            }, 'json');
        }

        function markAllNotifications() {
            $.post('api/mark_notification_read.php', { all: '1' }, function(response) {
                if (response && response.success) {
                    loadNotifications();
                }
            }, 'json');
        }

        $(document).ready(function() {
            loadNotifications();
            loadDashboardData();

            $(document).on('click', '.notification-item', function() {
                var id = $(this).data('id');
                markNotification(id);
            });

            $('#mark-all-read').click(function(event) {
                event.preventDefault();
                markAllNotifications();
            });

            $('#manage-overdue-btn').click(function() {
                window.location.href = 'equipment.php';
            });
        });
    </script>
</body>
</html>
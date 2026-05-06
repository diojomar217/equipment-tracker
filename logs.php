<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role('Admin');
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Equipment Logs - Equipment Tracker';
$pageStyles = '<style>
/* Logs page specific styles */
.logs-header {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
}

.logs-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-text {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
}

.logs-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.logs-empty-state {
    text-align: center;
    padding: 3rem;
}

.logs-empty-state i {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.refresh-indicator {
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    opacity: 0.7;
}

.auto-refresh-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    border-radius: 12px;
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'logs'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-journal-text text-primary me-3"></i>Equipment Logs
                        </h1>
                        <p class="text-muted mb-0 fs-6">Track all equipment activities and user actions.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshLogs()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Logs Table Card -->
                <div class="card">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-gradient p-2 me-3">
                                    <i class="bi bi-activity text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Activity History</h5>
                                    <small class="text-muted">Recent equipment actions and changes</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Auto-refresh every 30 seconds</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <i class="bi bi-box-seam me-2"></i>Equipment
                                        </th>
                                        <th scope="col">
                                            <i class="bi bi-lightning me-2"></i>Activity
                                        </th>
                                        <th scope="col">
                                            <i class="bi bi-person me-2"></i>User
                                        </th>
                                        <th scope="col">
                                            <i class="bi bi-clock me-2"></i>Timestamp
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="logs-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="spinner-border text-primary mb-3" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="text-muted">Loading activity logs...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        function refreshLogs() {
            $('#logs-table-body').html('<tr><td colspan="4" class="text-center py-5"><div class="d-flex flex-column align-items-center"><div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Loading...</span></div><span class="text-muted">Refreshing logs...</span></div></td></tr>');
            loadLogs();
        }

        function loadLogs() {
            $.ajax({
                url: 'api/get_logs.php',
                type: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (response && response.success) {
                    renderLogs(response.data);
                } else {
                    showError(response.error || 'Unable to load logs.');
                }
            }).fail(function() {
                showError('Unable to load logs.');
            });
        }

        function renderLogs(data) {
            var $tbody = $('#logs-table-body');
            $tbody.empty();

            if (!Array.isArray(data) || data.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center text-muted py-5"><i class="bi bi-journal-x fs-1 mb-3 text-muted"></i><br>No activity logs found</td></tr>');
                return;
            }

            data.forEach(function(item) {
                var activityBadge = getActivityBadge(item.activity || item.action || '');
                var timestamp = formatTimestamp(item.created_at || '');

                var row = '<tr>' +
                    '<td class="fw-semibold">' + (item.equipment_name || 'Unknown Equipment') + '</td>' +
                    '<td>' + activityBadge + '</td>' +
                    '<td><i class="bi bi-person-circle me-2 text-muted"></i>' + (item.user || 'System') + '</td>' +
                    '<td class="text-muted small"><i class="bi bi-clock me-1"></i>' + timestamp + '</td>' +
                    '</tr>';
                $tbody.append(row);
            });
        }

        function getActivityBadge(activity) {
            var activityLower = activity.toLowerCase();
            var badgeClass = 'activity-added'; // default
            var iconClass = 'bi-plus-circle';
            var displayText = activity;

            if (activityLower.includes('check-in') || activityLower.includes('check_in')) {
                badgeClass = 'activity-checkin';
                iconClass = 'bi-check-circle';
                displayText = 'Checked In';
            } else if (activityLower.includes('check-out') || activityLower.includes('check_out')) {
                badgeClass = 'activity-checkout';
                iconClass = 'bi-arrow-right-circle';
                displayText = 'Checked Out';
            } else if (activityLower.includes('maintenance')) {
                badgeClass = 'activity-maintenance';
                iconClass = 'bi-wrench';
                displayText = 'Maintenance';
            } else if (activityLower.includes('added') || activityLower.includes('created')) {
                badgeClass = 'activity-added';
                iconClass = 'bi-plus-circle';
                displayText = 'Added';
            }

            return '<span class="activity-badge ' + badgeClass + '"><i class="bi ' + iconClass + ' me-1"></i>' + displayText + '</span>';
        }

        function formatTimestamp(timestamp) {
            if (!timestamp) return 'Unknown';

            var date = new Date(timestamp);
            if (isNaN(date.getTime())) return timestamp;

            var now = new Date();
            var diffMs = now - date;
            var diffMins = Math.floor(diffMs / 60000);
            var diffHours = Math.floor(diffMs / 3600000);
            var diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + ' minutes ago';
            if (diffHours < 24) return diffHours + ' hours ago';
            if (diffDays < 7) return diffDays + ' days ago';

            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        function showError(message) {
            $('#logs-table-body').html('<tr><td colspan="4" class="text-center text-danger py-5"><i class="bi bi-exclamation-triangle fs-1 mb-3"></i><br>' + message + '</td></tr>');
        }

        $(document).ready(function() {
            loadLogs();

            // Auto-refresh every 30 seconds
            setInterval(loadLogs, 30000);
        });
    </script>
</body>
</html>
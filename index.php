<?php
require_once __DIR__ . '/config/auth.php';
auth_require_login();
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Dashboard - Equipment Tracker';
$pageStyles = '<style>body { background-color: #e9ecef; } .sidebar { min-height: 100vh; border-right: 1px solid #dee2e6; background-color: #fff; } .sidebar .nav-link { color: #495057; } .sidebar .nav-link.active { background-color: #f8f9fa; font-weight: 600; } .dashboard-container { padding: 30px 0; } .card-stats .card-body { display: flex; align-items: center; justify-content: space-between; } .card-stats .stat-icon { width: 56px; height: 56px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.75rem; } .quick-buttons .btn { min-width: 150px; }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'dashboard'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-md-9 col-lg-10 dashboard-container px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3">Dashboard</h1>
                        <p class="text-muted mb-0">Overview of equipment status and recent activity.</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light border dropdown-toggle" type="button" id="notificationDropdownButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span id="notification-count" class="badge badge-danger ml-1" style="display:none;">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="notificationDropdownButton" style="min-width: 320px;">
                            <div class="px-3 py-2 border-bottom font-weight-bold">Notifications</div>
                            <div id="notification-items" class="list-group list-group-flush"></div>
                            <div class="p-2 text-center border-top">
                                <button id="mark-all-read" class="btn btn-link btn-sm">Mark all as read</button>
                            </div>
                        </div>
                    </div>
                </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card card-stats shadow-sm">
                    <div class="card-body">
                        <div>
                            <h6 class="text-uppercase text-muted">Total Equipment</h6>
                            <h3><span id="total-count">0</span></h3>
                        </div>
                        <div class="stat-icon bg-primary text-white">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-stats shadow-sm">
                    <div class="card-body">
                        <div>
                            <h6 class="text-uppercase text-muted">Available</h6>
                            <h3><span id="available-count">0</span></h3>
                        </div>
                        <div class="stat-icon bg-success text-white">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-stats shadow-sm">
                    <div class="card-body">
                        <div>
                            <h6 class="text-uppercase text-muted">In Use</h6>
                            <h3><span id="inuse-count">0</span></h3>
                        </div>
                        <div class="stat-icon bg-warning text-white">
                            <i class="bi bi-arrow-right-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-stats shadow-sm">
                    <div class="card-body">
                        <div>
                            <h6 class="text-uppercase text-muted">Maintenance</h6>
                            <h3><span id="maintenance-count">0</span></h3>
                        </div>
                        <div class="stat-icon bg-danger text-white">
                            <i class="bi bi-wrench"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4 quick-buttons">
            <?php if ($user['role'] === 'Admin'): ?>
                <div class="col-auto mb-2"><a href="equipment.php" class="btn btn-primary"><i class="bi bi-plus-lg mr-2"></i>Add Equipment</a></div>
            <?php endif; ?>
            <div class="col-auto mb-2"><a href="scan.php" class="btn btn-secondary"><i class="bi bi-qr-code-scan mr-2"></i>Scan QR</a></div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Recent Activity Logs
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Equipment</th>
                                        <th>Action</th>
                                        <th>User</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Loading recent activity...</td>
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js" integrity="sha384-LtrjvnR4/Jdsd0O4mFsXEDX0B+e5qFQFDeUqQSRreI6G0nDZK07bqYDl38opP0xV" crossorigin="anonymous"></script>
    <script>
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

                $('#total-count').text(response.stats.total || 0);
                $('#available-count').text(response.stats.available || 0);
                $('#inuse-count').text(response.stats.in_use || 0);
                $('#maintenance-count').text(response.stats.maintenance || 0);

                var $logsBody = $('#recent-logs-body');
                $logsBody.empty();
                if (Array.isArray(response.recentLogs) && response.recentLogs.length > 0) {
                    response.recentLogs.forEach(function(log) {
                        var row = '<tr>' +
                            '<td>' + $('<div>').text(log.equipment_name || 'Unknown').html() + '</td>' +
                            '<td>' + $('<div>').text(log.action || '').html() + '</td>' +
                            '<td>' + $('<div>').text(log.user || '').html() + '</td>' +
                            '<td>' + $('<div>').text(log.created_at || '').html() + '</td>' +
                            '</tr>';
                        $logsBody.append(row);
                    });
                } else {
                    $logsBody.append('<tr><td colspan="4" class="text-center py-4 text-muted">No recent activity found.</td></tr>');
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
        });
    </script>
</body>
</html>
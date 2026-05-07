<?php
require_once __DIR__ . '/config/auth.php';
auth_require_login();
$user = auth_user();
require_once __DIR__ . '/config/db.php';

$connection->close();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Notifications - Equipment Tracker';
$pageStyles = '<style>
/* Notifications page specific styles */
.notifications-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card-compact {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.stat-card-compact:hover {
    transform: translateY(-2px);
}

.stat-icon-compact {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin: 0 auto 1rem;
}

.stat-value-compact {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label-compact {
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 500;
}

.notifications-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.notifications-empty-state {
    text-align: center;
    padding: 3rem;
}

.notifications-empty-state i {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.notifications-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

@media (max-width: 576px) {
    .notifications-actions {
        flex-direction: column;
    }

    .notifications-actions .btn {
        width: 100%;
    }
}

.relative-time {
    font-size: 0.8rem;
    color: #6b7280;
}

.mark-read-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.auto-refresh-notice {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 0.75rem;
    color: #6b7280;
    background: rgba(255, 255, 255, 0.8);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'notifications'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-12 col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-bell text-primary me-3"></i>Notification Center
                        </h1>
                        <p class="text-muted mb-0 fs-6">Stay updated with equipment alerts and system notifications.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="mark-all-read-page" class="btn btn-outline-secondary">
                            <i class="bi bi-check-all me-2"></i>Mark All Read
                        </button>
                        <button class="btn btn-primary" onclick="refreshNotifications()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Notification Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="rounded-circle bg-danger bg-gradient p-3 d-inline-block mb-3">
                                    <i class="bi bi-exclamation-triangle text-white fs-4"></i>
                                </div>
                                <h3 class="fw-bold text-danger mb-1" id="unread-count">0</h3>
                                <p class="text-muted mb-0 small">Unread Notifications</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="rounded-circle bg-warning bg-gradient p-3 d-inline-block mb-3">
                                    <i class="bi bi-clock text-white fs-4"></i>
                                </div>
                                <h3 class="fw-bold text-warning mb-1" id="overdue-count">0</h3>
                                <p class="text-muted mb-0 small">Overdue Items</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Table Card -->
                <div class="card">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-gradient p-2 me-3">
                                    <i class="bi bi-bell-fill text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">All Notifications</h5>
                                    <small class="text-muted">System alerts and equipment status updates</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Auto-refresh every 60 seconds</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="notification-alert"></div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 60px;">
                                            <i class="bi bi-circle-fill text-muted"></i>
                                        </th>
                                        <th scope="col">
                                            <i class="bi bi-box-seam me-2"></i>Equipment
                                        </th>
                                        <th scope="col">
                                            <i class="bi bi-chat-dots me-2"></i>Message
                                        </th>
                                        <th scope="col">
                                            <i class="bi bi-tag me-2"></i>Type
                                        </th>
                                        <th scope="col">
                                            <i class="bi bi-clock me-2"></i>Created
                                        </th>
                                        <th scope="col" style="width: 120px;">
                                            <i class="bi bi-gear me-2"></i>Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="notification-table-body">
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="spinner-border text-primary mb-3" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="text-muted">Loading notifications...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        function refreshNotifications() {
            $('#notification-table-body').html('<tr><td colspan="6" class="text-center py-5"><div class="d-flex flex-column align-items-center"><div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Loading...</span></div><span class="text-muted">Refreshing notifications...</span></div></td></tr>');
            loadNotifications();
        }

        function showPageAlert(message, type) {
            $('#notification-alert').html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>');
        }

        function loadNotifications() {
            $.getJSON('api/get_notifications.php?all=1').done(function(response) {
                var $body = $('#notification-table-body');
                $body.empty();

                if (!response || !response.success || !Array.isArray(response.data) || response.data.length === 0) {
                    $body.append('<tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-bell-slash fs-1 mb-3 text-muted"></i><br>No notifications found.</td></tr>');
                    updateStats(0, 0, 0);
                    return;
                }

                var unreadCount = 0;
                var overdueCount = 0;
                var maintenanceCount = 0;

                response.data.forEach(function(item) {
                    var rowClass = item.status === 'UNREAD' ? 'unread' : '';
                    var statusDotClass = item.status === 'UNREAD' ? 'status-unread' : 'status-read';
                    var equipmentName = item.equipment_name ? item.equipment_name : 'Unknown Equipment';
                    var typeBadge = getTypeBadge(item.type);
                    var timestamp = formatTimestamp(item.created_at);
                    var actionButton = item.status === 'UNREAD'
                        ? '<button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="' + item.id + '"><i class="bi bi-check me-1"></i>Mark Read</button>'
                        : '<span class="text-muted small"><i class="bi bi-check-circle me-1"></i>Read</span>';

                    var row = '<tr class="' + rowClass + '">' +
                        '<td><span class="status-dot ' + statusDotClass + '"></span></td>' +
                        '<td class="fw-semibold">' + $('<div>').text(equipmentName).html() + '</td>' +
                        '<td>' + $('<div>').text(item.message).html() + '</td>' +
                        '<td>' + typeBadge + '</td>' +
                        '<td class="text-muted small"><i class="bi bi-clock me-1"></i>' + timestamp + '</td>' +
                        '<td>' + actionButton + '</td>' +
                        '</tr>';
                    $body.append(row);

                    // Count stats
                    if (item.status === 'UNREAD') unreadCount++;
                    if (item.type && item.type.toLowerCase().includes('overdue')) overdueCount++;
                    if (item.type && item.type.toLowerCase().includes('maintenance')) maintenanceCount++;
                });

                updateStats(unreadCount, overdueCount, maintenanceCount);
            }).fail(function() {
                $('#notification-table-body').html('<tr><td colspan="6" class="text-center text-danger py-5"><i class="bi bi-exclamation-triangle fs-1 mb-3"></i><br>Unable to load notifications.</td></tr>');
                updateStats(0, 0, 0);
            });
        }

        function getTypeBadge(type) {
            if (!type) return '<span class="badge notification-type-info">Info</span>';

            var typeLower = type.toLowerCase();
            var badgeClass = 'notification-type-info';
            var iconClass = 'bi-info-circle';
            var displayText = type;

            if (typeLower.includes('overdue')) {
                badgeClass = 'notification-type-overdue';
                iconClass = 'bi-exclamation-triangle';
                displayText = 'Overdue';
            } else if (typeLower.includes('maintenance')) {
                badgeClass = 'notification-type-maintenance';
                iconClass = 'bi-wrench';
                displayText = 'Maintenance';
            }

            return '<span class="badge ' + badgeClass + '"><i class="bi ' + iconClass + ' me-1"></i>' + displayText + '</span>';
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

        function updateStats(unread, overdue, maintenance) {
            $('#unread-count').text(unread);
            $('#overdue-count').text(overdue);
            $('#maintenance-count').text(maintenance);
        }

        function markNotification(id) {
            $.post('api/mark_notification_read.php', { id: id }, function(response) {
                if (response && response.success) {
                    loadNotifications();
                    showPageAlert('Notification marked as read.', 'success');
                } else {
                    showPageAlert(response.error || 'Unable to mark notification.', 'danger');
                }
            }, 'json');
        }

        function markAllRead() {
            $.post('api/mark_notification_read.php', { all: '1' }, function(response) {
                if (response && response.success) {
                    loadNotifications();
                    showPageAlert('All notifications marked as read.', 'success');
                } else {
                    showPageAlert(response.error || 'Unable to mark all notifications.', 'danger');
                }
            }, 'json');
        }

        $(document).ready(function() {
            loadNotifications();

            // Auto-refresh every 60 seconds
            setInterval(loadNotifications, 60000);

            $(document).on('click', '.mark-read-btn', function() {
                var id = $(this).data('id');
                markNotification(id);
            });

            $('#mark-all-read-page').click(function() {
                markAllRead();
            });
        });
    </script>
</body>
</html>

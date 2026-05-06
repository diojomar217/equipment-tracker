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
$pageStyles = '<style>body { background-color: #f8f9fa; } .sidebar { min-height: 100vh; border-right: 1px solid #dee2e6; background-color: #fff; } .sidebar .nav-link { color: #495057; } .sidebar .nav-link.active { background-color: #f8f9fa; font-weight: 600; } .content { padding: 30px 20px; } .notification-unread { background-color: #f8f9fa; }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'notifications'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-md-9 col-lg-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3">Notification Center</h1>
                        <p class="text-muted mb-0">View all system alerts and overdue equipment notices.</p>
                    </div>
                    <button id="mark-all-read-page" class="btn btn-outline-secondary btn-sm">Mark all read</button>
                </div>
                <div id="notification-alert"></div>
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Equipment</th>
                                        <th>Message</th>
                                        <th>Type</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="notification-table-body">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Loading notifications...</td>
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js" integrity="sha384-LtrjvnR4/Jdsd0O4mFsXEDX0B+e5qFQFDeUqQSRreI6G0nDZK07bqYDl38opP0xV" crossorigin="anonymous"></script>
    <script>
        function showPageAlert(message, type) {
            $('#notification-alert').html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>');
        }

        function loadNotifications() {
            $.getJSON('api/get_notifications.php?all=1').done(function(response) {
                var $body = $('#notification-table-body');
                $body.empty();

                if (!response || !response.success || !Array.isArray(response.data) || response.data.length === 0) {
                    $body.append('<tr><td colspan="6" class="text-center text-muted py-4">No notifications found.</td></tr>');
                    return;
                }

                response.data.forEach(function(item) {
                    var rowClass = item.status === 'UNREAD' ? 'notification-unread' : '';
                    var equipmentName = item.equipment_name ? item.equipment_name : 'Unknown';
                    var actionButton = item.status === 'UNREAD' ? '<button class="btn btn-sm btn-primary mark-read-btn" data-id="' + item.id + '">Mark read</button>' : '<span class="text-muted">Read</span>';
                    var row = '<tr class="' + rowClass + '">' +
                        '<td>' + item.status + '</td>' +
                        '<td>' + $('<div>').text(equipmentName).html() + '</td>' +
                        '<td>' + $('<div>').text(item.message).html() + '</td>' +
                        '<td>' + $('<div>').text(item.type).html() + '</td>' +
                        '<td>' + $('<div>').text(item.created_at).html() + '</td>' +
                        '<td>' + actionButton + '</td>' +
                        '</tr>';
                    $body.append(row);
                });
            }).fail(function() {
                $('#notification-table-body').html('<tr><td colspan="6" class="text-center text-danger py-4">Unable to load notifications.</td></tr>');
            });
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

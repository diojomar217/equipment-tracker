<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin', 'Staff']);
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'My Activity';
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
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.logs-table th {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    color: #374151;
    padding: 1rem;
    text-align: left;
}

.logs-table td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.logs-table tbody tr:hover {
    background: #f8fafc;
}

.activity-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
}

.activity-checkout {
    background: #fef3c7;
    color: #92400e;
}

.activity-checkin {
    background: #d1fae5;
    color: #065f46;
}

.activity-maintenance {
    background: #fee2e2;
    color: #991b1b;
}

.activity-other {
    background: #e0e7ff;
    color: #3730a3;
}

.equipment-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.equipment-link:hover {
    text-decoration: underline;
}

.user-info {
    display: flex;
    align-items: center;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    font-weight: 600;
    color: #374151;
}

.timestamp {
    color: #6b7280;
    font-size: 0.875rem;
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php
            $activePage = 'staff_logs';
            include __DIR__ . '/includes/sidebar.php';
            ?>
            <main class="col-12 col-md-9 col-lg-10 main-content py-4 px-4">
                <div class="logs-header">
                    <h1 class="h2 mb-2 fw-bold">My Activity</h1>
                    <p class="text-muted mb-0">Your equipment borrow/return history</p>
                </div>

                <div class="logs-stats">
                    <div class="stat-box">
                        <div class="stat-number text-primary" id="total-activities">0</div>
                        <div class="stat-text">Total Activities</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number text-success" id="checkouts">0</div>
                        <div class="stat-text">Borrowed</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number text-info" id="checkins">0</div>
                        <div class="stat-text">Returned</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number text-warning" id="maintenance">0</div>
                        <div class="stat-text">Maintenance</div>
                    </div>
                </div>

                <div class="logs-table-container">
                    <table class="table table-hover mb-0 logs-table">
                        <thead>
                            <tr>
                                <th>Equipment</th>
                                <th>Activity</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody id="logs-table-body">
                            <!-- Logs will be populated here -->
                        </tbody>
                    </table>
                </div>

                <div id="alert-container"></div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            function showAlert(message, type) {
                var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                $('#alert-container').html(alertHtml);
            }

            function showError(message) {
                showAlert(message, 'danger');
            }

            function renderLogs(data) {
                var $tbody = $('#logs-table-body');
                $tbody.empty();

                if (!Array.isArray(data) || data.length === 0) {
                    var emptyRow = '<tr>' +
                        '<td colspan="3" class="text-center py-5 text-muted">' +
                        '<i class="bi bi-journal-x fs-1 mb-3 text-muted"></i><br>No activity found</td>' +
                        '</tr>';
                    $tbody.append(emptyRow);
                    $('#total-activities').text('0');
                    $('#checkouts').text('0');
                    $('#checkins').text('0');
                    $('#maintenance').text('0');
                    return;
                }

                var stats = { total: data.length, checkouts: 0, checkins: 0, maintenance: 0 };

                data.forEach(function(item) {
                    var equipmentName = item.equipment_name || 'Unknown Equipment';
                    var action = item.action;
                    var activityClass = 'activity-other';
                    var activityText = action.replace(/_/g, ' ');

                    switch (action) {
                        case 'CHECK_OUT':
                        case 'BORROW':
                            activityClass = 'activity-checkout';
                            activityText = 'Borrowed';
                            stats.checkouts++;
                            break;
                        case 'CHECK_IN':
                        case 'RETURN':
                            activityClass = 'activity-checkin';
                            activityText = 'Returned';
                            stats.checkins++;
                            break;
                        case 'MAINTENANCE':
                            activityClass = 'activity-maintenance';
                            activityText = 'Sent to Maintenance';
                            stats.maintenance++;
                            break;
                        default:
                            activityText = action.replace(/_/g, ' ');
                    }

                    var row = '<tr>' +
                        '<td><a href="equipment_detail.php?id=' + item.equipment_id + '" class="equipment-link">' + equipmentName + '</a></td>' +
                        '<td><span class="activity-badge ' + activityClass + '">' + activityText + '</span></td>' +
                        '<td class="timestamp">' + new Date(item.created_at).toLocaleString() + '</td>' +
                        '</tr>';
                    $tbody.append(row);
                });

                $('#total-activities').text(stats.total);
                $('#checkouts').text(stats.checkouts);
                $('#checkins').text(stats.checkins);
                $('#maintenance').text(stats.maintenance);
            }

            function loadLogs() {
                $.ajax({
                    url: 'api/get_logs.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        user: '<?php echo htmlspecialchars($user['username']); ?>'
                    }
                }).done(function(response) {
                    if (response && response.success) {
                        renderLogs(response.data);
                    } else {
                        showError('No logs found');
                    }
                }).fail(function() {
                    showError('Unable to load activity logs.');
                });
            }

            loadLogs();
        });
    </script>
</body>
</html>
<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role('Admin');
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Equipment Logs';
$pageStyles = '<style>body { background-color: #f8f9fa; } .logs-container { margin-top: 40px; } .table td, .table th { vertical-align: middle; }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <?php $activePage = 'logs'; include __DIR__ . '/includes/topnav.php'; ?>
    <div class="container logs-container">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3">Equipment Logs</h1>
                <p class="text-muted">Recent equipment actions and history.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive shadow-sm bg-white rounded">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Equipment</th>
                                <th scope="col">Activity</th>
                                <th scope="col">User</th>
                                <th scope="col">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="logs-table-body">
                            <tr>
                                <td colspan="4" class="text-center py-4">Loading logs...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js" integrity="sha384-LtrjvnR4/Jdsd0O4mFsXEDX0B+e5qFQFDeUqQSRreI6G0nDZK07bqYDl38opP0xV" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            function renderLogs(data) {
                var $tbody = $('#logs-table-body');
                $tbody.empty();

                if (!Array.isArray(data) || data.length === 0) {
                    $tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">No logs found</td></tr>');
                    return;
                }

                data.forEach(function(item) {
                    var row = '<tr>' +
                        '<td>' + (item.equipment_name || 'Unknown') + '</td>' +
                        '<td>' + (item.activity || item.action || '') + '</td>' +
                        '<td>' + (item.user || '') + '</td>' +
                        '<td>' + (item.created_at || '') + '</td>' +
                        '</tr>';
                    $tbody.append(row);
                });
            }

            function showError(message) {
                $('#logs-table-body').html('<tr><td colspan="4" class="text-center text-danger py-4">' + message + '</td></tr>');
            }

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
        });
    </script>
</body>
</html>
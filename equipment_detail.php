<?php
require_once __DIR__ . '/config/auth.php';
auth_require_login();
$user = auth_user();

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '' || !filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400);
    echo 'Invalid equipment ID.';
    exit;
}

function fetch_api_json($endpoint, $params = []) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . ($basePath === '/' ? '' : $basePath);
    $url = $baseUrl . '/api/' . ltrim($endpoint, '/');

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    if (function_exists('curl_version')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            return null;
        }
        return json_decode($response, true);
    }

    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "Cookie: " . session_name() . "=" . session_id() . "\r\n",
            'timeout' => 10,
        ],
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }
    return json_decode($response, true);
}

$equipmentResponse = fetch_api_json('get_equipment_detail.php', ['id' => $id]);
if (!$equipmentResponse || empty($equipmentResponse['success']) || empty($equipmentResponse['data'])) {
    http_response_code(404);
    echo 'Equipment not found.';
    exit;
}

$equipment = $equipmentResponse['data'];

$logsResponse = fetch_api_json('get_logs.php', ['equipment_id' => $id]);
$logs = [];
if ($logsResponse && !empty($logsResponse['success']) && !empty($logsResponse['data'])) {
    $logs = $logsResponse['data'];
}

$qrText = !empty($equipment['qr_code']) ? $equipment['qr_code'] : 'equipment_id=' . $equipment['id'];
$imagePath = !empty($equipment['image']) ? $equipment['image'] : null;
$assignedTo = !empty($equipment['assigned_to']) ? $equipment['assigned_to'] : 'Unassigned';
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Equipment Detail - ' . htmlspecialchars($equipment['name']);
$pageStyles = '<style>body { background-color: #f8f9fa; } .sidebar { min-height: 100vh; border-right: 1px solid #dee2e6; background: #fff; } .sidebar .nav-link { color: #495057; } .sidebar .nav-link.active { background-color: #f8f9fa; font-weight: 600; } .timeline-item { position: relative; padding-left: 2.5rem; margin-bottom: 1.5rem; } .timeline-item::before { content: ""; position: absolute; left: 1rem; top: 0.4rem; width: 0.75rem; height: 0.75rem; background-color: #007bff; border-radius: 50%; } .timeline-line { position: absolute; left: 1.24rem; top: 1.1rem; width: 2px; height: calc(100% - 1.1rem); background-color: #dee2e6; } .status-pill { font-size: 0.85rem; } .equipment-image { max-width: 100%; max-height: 320px; object-fit: contain; } .qr-box { width: 220px; height: 220px; }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'equipment'; include __DIR__ . '/includes/sidebar.php'; ?>

                    <p class="text-muted small mb-0">Signed in as<br><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['role']); ?> access</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link py-2" href="index.php"><i class="bi bi-speedometer2 mr-2"></i>Dashboard</a>
                    <a class="nav-link py-2" href="equipment.php"><i class="bi bi-list-ul mr-2"></i>Equipment</a>
                    <a class="nav-link py-2" href="notifications.php"><i class="bi bi-bell mr-2"></i>Notifications</a>
                    <a class="nav-link py-2" href="scan.php"><i class="bi bi-qr-code-scan mr-2"></i>Scan</a>
                    <a class="nav-link py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right mr-2"></i>Logout</a>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 py-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="h3">Equipment Details</h1>
                        <p class="text-muted mb-0">Complete information, image, QR code, and history for this item.</p>
                    </div>
                    <a href="equipment.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h4><?php echo htmlspecialchars($equipment['name']); ?></h4>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($equipment['category']); ?></p>
                                        <span class="badge badge-<?php echo $equipment['status'] === 'AVAILABLE' ? 'success' : ($equipment['status'] === 'CHECK_OUT' ? 'warning' : 'secondary'); ?> status-pill mr-2"><?php echo htmlspecialchars($equipment['status']); ?></span>
                                        <span class="badge badge-info status-pill"><?php echo htmlspecialchars($equipment['location']); ?></span>
                                    </div>
                                    <?php if ($imagePath): ?>
                                        <div class="ml-4 text-right d-none d-md-block">
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Equipment image" class="equipment-image rounded border">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!$imagePath): ?>
                                    <div class="mt-4 text-center text-muted py-5 border rounded">
                                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                                        <div>No image uploaded</div>
                                    </div>
                                <?php endif; ?>

                                <div class="row mt-4">
                                    <div class="col-sm-6 mb-3">
                                        <div class="card card-sm border-0 shadow-sm">
                                            <div class="card-body p-3">
                                                <p class="text-uppercase text-muted mb-1">Category</p>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($equipment['category']); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="card card-sm border-0 shadow-sm">
                                            <div class="card-body p-3">
                                                <p class="text-uppercase text-muted mb-1">Assigned to</p>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($assignedTo); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="card card-sm border-0 shadow-sm">
                                            <div class="card-body p-3">
                                                <p class="text-uppercase text-muted mb-1">Location</p>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($equipment['location']); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="card card-sm border-0 shadow-sm">
                                            <div class="card-body p-3">
                                                <p class="text-uppercase text-muted mb-1">Status</p>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($equipment['status']); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Activity Timeline</h5>
                                <span class="badge badge-secondary"><?php echo count($logs); ?> events</span>
                            </div>
                            <div class="card-body">
                                <?php if (empty($logs)): ?>
                                    <div class="text-center text-muted py-5">No history available for this equipment.</div>
                                <?php else: ?>
                                    <div class="position-relative">
                                        <div class="timeline-line"></div>
                                        <?php foreach ($logs as $log): ?>
                                            <div class="timeline-item">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="font-weight-bold"><?php echo htmlspecialchars($log['action']); ?></span>
                                                    <small class="text-muted"><?php echo htmlspecialchars($log['created_at']); ?></small>
                                                </div>
                                                <div class="text-muted mb-1">By <?php echo htmlspecialchars($log['user'] ?: 'Unknown'); ?></div>
                                                <div class="small text-secondary">
                                                    <?php
                                                    switch ($log['action']) {
                                                        case 'CHECK_OUT':
                                                            echo 'Checked out to a user.';
                                                            break;
                                                        case 'CHECK_IN':
                                                            echo 'Checked in and returned to inventory.';
                                                            break;
                                                        case 'MAINTENANCE':
                                                            echo 'Sent to maintenance.';
                                                            break;
                                                        default:
                                                            echo 'Activity recorded.';
                                                            break;
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body text-center">
                                <h5 class="card-title">QR Code</h5>
                                <div id="detail-qrcode" class="qr-box mx-auto"></div>
                                <p class="text-muted mt-3 mb-0">Scan to manage this equipment quickly.</p>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Action Panel</h5>
                                <div class="form-group">
                                    <label for="detail-action-user">User</label>
                                    <input type="text" class="form-control" id="detail-action-user" placeholder="Enter your name">
                                </div>
                                <button id="btn-check-in" class="btn btn-success btn-block mb-2">Check-in</button>
                                <button id="btn-check-out" class="btn btn-warning btn-block mb-2">Check-out</button>
                                <button id="btn-maintenance" class="btn btn-danger btn-block">Mark Maintenance</button>
                                <div id="detail-action-alert" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js" integrity="sha384-LtrjvnR4/Jdsd0O4mFsXEDX0B+e5qFQFDeUqQSRreI6G0nDZK07bqYDl38opP0xV" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-ptDqdE5MX6/Tn5C7Hbk52z7MiC+SW061MvHjXmT4r1h0YcMxGIIbVZKRyXelQ2x4n1jaSor1+WTeettqQeAq6Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function showAlert(message, type) {
            $('#detail-action-alert').html(
                '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>'
            );
        }

        function updateStatus(status) {
            var user = $('#detail-action-user').val().trim();
            if (!user) {
                showAlert('Please enter your name before updating status.', 'warning');
                return;
            }

            $.post('api/update_status.php', {
                id: <?php echo (int)$equipment['id']; ?>,
                status: status,
                user: user
            }, function(response) {
                if (response && response.success) {
                    showAlert('Equipment status updated successfully.', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 800);
                } else {
                    showAlert(response.error || 'Unable to update status.', 'danger');
                }
            }, 'json').fail(function(xhr) {
                var error = 'Unable to update status.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    error = xhr.responseJSON.error;
                }
                showAlert(error, 'danger');
            });
        }

        $(document).ready(function() {
            new QRCode(document.getElementById('detail-qrcode'), {
                text: '<?php echo htmlspecialchars($qrText, ENT_QUOTES); ?>',
                width: 220,
                height: 220,
                correctLevel: QRCode.CorrectLevel.H
            });

            $('#btn-check-in').click(function() {
                updateStatus('CHECK_IN');
            });
            $('#btn-check-out').click(function() {
                updateStatus('CHECK_OUT');
            });
            $('#btn-maintenance').click(function() {
                updateStatus('MAINTENANCE');
            });
        });
    </script>
</body>
</html>

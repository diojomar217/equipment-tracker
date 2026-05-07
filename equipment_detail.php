<?php
require_once __DIR__ . '/config/auth.php';
auth_require_login();
$user = auth_user();

// Close the session to avoid locking issues with internal API calls
session_write_close();

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

        // Prevent the whole page from hanging in "pending" state
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'httpCode' => $httpCode > 0 ? $httpCode : 504,
                'error' => ($curlError ?: 'cURL request failed'),
            ];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return [
                'success' => false,
                'httpCode' => $httpCode > 0 ? $httpCode : 502,
                'error' => 'Invalid JSON returned by internal API',
            ];
        }

        if ($httpCode >= 400 || empty($decoded['success'])) {
            return [
                'success' => false,
                'httpCode' => $httpCode > 0 ? $httpCode : 500,
                'error' => $decoded['error'] ?? 'API request failed',
            ];
        }

        return $decoded;
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
    // If internal API call failed (auth/timeout/DB/etc) return the underlying reason
    $httpCode = isset($equipmentResponse['httpCode']) ? (int)$equipmentResponse['httpCode'] : 404;
    $apiError = $equipmentResponse['error'] ?? 'Equipment not found.';
    http_response_code($httpCode >= 100 ? $httpCode : 404);
    echo htmlspecialchars($apiError, ENT_QUOTES);
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
$pageStyles = '<style>
/* Equipment detail page specific styles */
.equipment-overview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.equipment-overview-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.equipment-image-container {
    text-align: center;
    margin-bottom: 2rem;
}

.equipment-image-container img {
    max-width: 100%;
    max-height: 300px;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.equipment-qr-section {
    text-align: center;
    margin-bottom: 2rem;
}

.equipment-actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .equipment-overview-grid {
        grid-template-columns: 1fr;
    }

    .equipment-actions-grid {
        grid-template-columns: 1fr;
    }
}

.equipment-timeline {
    position: relative;
}

.equipment-timeline::before {
    
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.equipment-activity-item {
    position: relative;
    padding-left: 3rem;
    margin-bottom: 1.5rem;
}

.equipment-activity-item::before {

    position: absolute;
    left: 0.25rem;
    top: 0.5rem;
    width: 1rem;
    height: 1rem;
    background: linear-gradient(135deg, #0d6efd, #4f46e5);
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.equipment-activity-content {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
}

.equipment-activity-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.equipment-activity-meta .user {
    font-weight: 600;
    color: #0f172a;
}

.equipment-activity-meta .timestamp {
    color: #6b7280;
    font-size: 0.85rem;
}

.equipment-activity-description {
    color: #374151;
    margin-bottom: 0;
}

.equipment-no-activity {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.equipment-no-activity i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.equipment-print-section {
    display: none;
}

@media print {
    .equipment-print-section {
        display: block;
    }

    .no-print {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'equipment'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-12 col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-box-seam text-primary me-3"></i><?php echo htmlspecialchars($equipment['name']); ?>
                        </h1>
                        <p class="text-muted mb-0 fs-6">Complete equipment information, history, and management details.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="equipment.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Equipment
                        </a>
                        <button class="btn btn-primary" onclick="printDetails()">
                            <i class="bi bi-printer me-2"></i>Print Details
                        </button>
                    </div>
                </div>

                <div class="row">
                    <!-- Equipment Overview -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-gradient p-2 me-3">
                                            <i class="bi bi-info-circle text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold">Equipment Overview</h5>
                                            <small class="text-muted">Basic information and current status</small>
                                        </div>
                                    </div>
                                    <?php
                                    $statusClass = 'status-available';
                                    if ($equipment['status'] === 'BORROWED') $statusClass = 'status-checked-out';
                                    elseif ($equipment['status'] === 'MAINTENANCE') $statusClass = 'status-maintenance';
                                    elseif ($equipment['status'] === 'OVERDUE') $statusClass = 'status-overdue';
                                    $statusLabel = $equipment['status'];
                                    if ($equipment['status'] === 'AVAILABLE') {
                                        $statusLabel = 'Available';
                                    } elseif ($equipment['status'] === 'BORROWED') {
                                        $statusLabel = 'Borrowed';
                                    } elseif ($equipment['status'] === 'MAINTENANCE') {
                                        $statusLabel = 'Maintenance';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <i class="bi bi-circle-fill me-1"></i><?php echo htmlspecialchars($statusLabel); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="detail-label">Equipment ID</div>
                                            <div class="detail-value">#<?php echo htmlspecialchars($equipment['id']); ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="detail-label">Category</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($equipment['category']); ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="detail-label">Location</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($equipment['location']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="detail-label">Assigned To</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($assignedTo); ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="detail-label">Purchase Date</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($equipment['purchase_date'] ?? 'Not specified'); ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="detail-label">Last Updated</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($equipment['updated_at'] ?? 'Unknown'); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($imagePath): ?>
                                    <div class="mt-4">
                                        <div class="detail-label mb-2">Equipment Image</div>
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Equipment image" class="equipment-image">
                                    </div>
                                <?php else: ?>
                                    <div class="mt-4 text-center text-muted py-4 border rounded">
                                        <i class="bi bi-image fs-1 mb-2 text-muted"></i>
                                        <div>No image uploaded</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code & Actions -->
                    <div class="col-lg-4 mb-4">
                        <div class="card mb-4">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-gradient p-2 me-3">
                                        <i class="bi bi-qr-code text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">QR Code</h5>
                                        <small class="text-muted">Scan for quick access</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div id="qr-code-container" class="qr-box mx-auto mb-3"></div>
                                <button class="btn btn-outline-primary btn-sm" onclick="downloadQR()">
                                    <i class="bi bi-download me-1"></i>Download QR
                                </button>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning bg-gradient p-2 me-3">
                                        <i class="bi bi-gear text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Quick Actions</h5>
                                        <small class="text-muted">Update equipment status</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Return Location</label>
                                    <select class="form-select" id="equipmentDetailReturnLocation">
                                        <option value="">Select a return location (required for borrowing)</option>
                                    </select>
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success" onclick="updateStatus('RETURN')">
                                        <i class="bi bi-check-circle me-2"></i>Return
                                    </button>
                                    <button class="btn btn-primary" onclick="updateStatus('BORROW')">
                                        <i class="bi bi-arrow-right-circle me-2"></i>Borrow
                                    </button>
                                    <?php if ($user['role'] === 'Admin' && $equipment['status'] === 'MAINTENANCE'): ?>
                                        <button class="btn btn-success" onclick="updateStatus('COMPLETE_MAINTENANCE')">
                                            <i class="bi bi-check-circle me-2"></i>Complete Maintenance
                                        </button>
                                    <?php elseif ($user['role'] === 'Admin'): ?>
                                        <button class="btn btn-warning" onclick="updateStatus('MAINTENANCE')">
                                            <i class="bi bi-wrench me-2"></i>Send to Maintenance
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="card">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-gradient p-2 me-3">
                                <i class="bi bi-clock-history text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Activity Timeline</h5>
                                <small class="text-muted">Recent actions and status changes</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($logs)): ?>
                            <div class="timeline">
                                <?php foreach ($logs as $log): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($log['activity'] ?? $log['action'] ?? 'Unknown Action'); ?></strong>
                                                    <div class="text-muted small">
                                                        by <?php echo htmlspecialchars($log['user'] ?? 'System'); ?>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($log['created_at'] ?? 'Unknown time'); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-clock-history fs-1 mb-3 text-muted"></i>
                                <p class="mb-0">No activity history available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <div class="modal fade" id="equipmentDetailConfirmModal" tabindex="-1" aria-labelledby="equipmentDetailConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="equipmentDetailConfirmModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="equipmentDetailConfirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="equipmentDetailConfirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        var currentUserId = <?php echo (int)($user['id'] ?? 0); ?>;
        var currentRole = '<?php echo htmlspecialchars($user['role'], ENT_QUOTES); ?>';
        var equipmentStatus = '<?php echo htmlspecialchars($equipment['status'], ENT_QUOTES); ?>';
        var equipmentAssignedTo = <?php echo (int)($equipment['assigned_to'] ?? 0); ?>;
        
        function showAlert(message, type) {
            // Create a temporary alert container if it doesn't exist
            if (!$('#action-alert').length) {
                $('body').append('<div id="action-alert" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>');
            }

            $('#action-alert').html(
                '<div class="alert alert-' + type + ' alert-dismissible fade show shadow" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>'
            );

            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('#action-alert .alert').fadeOut();
            }, 5000);
        }

        var equipmentDetailConfirmCallback = null;
        var equipmentDetailConfirmModal = null;

        function showEquipmentDetailConfirm(message, callback) {
            $('#equipmentDetailConfirmMessage').text(message);
            equipmentDetailConfirmCallback = callback;
            equipmentDetailConfirmModal.show();
        }

        function updateStatus(status) {
            var requestData = {
                id: <?php echo (int)$equipment['id']; ?>,
                status: status
            };

            if (status === 'BORROW') {
                var location = $('#equipmentDetailReturnLocation').val();
                if (!location) {
                    showAlert('Please select a return location before borrowing.', 'warning');
                    return;
                }
                requestData.return_location = location;
            }

            var confirmMessage = '';
            if (status === 'BORROW') {
                confirmMessage = 'Borrow this equipment and assign the selected return location?';
            } else if (status === 'RETURN') {
                confirmMessage = 'Return this equipment and make it available again?';
            } else if (status === 'MAINTENANCE') {
                confirmMessage = 'Send this equipment to maintenance? It will no longer be available for borrowing.';
            } else if (status === 'COMPLETE_MAINTENANCE') {
                confirmMessage = 'Mark maintenance complete and make this equipment available?';
            }

            function proceed() {
                var buttonText = '';
                switch (status) {
                    case 'RETURN': buttonText = 'Returning...'; break;
                    case 'BORROW': buttonText = 'Borrowing...'; break;
                    case 'MAINTENANCE': buttonText = 'Sending to Maintenance...'; break;
                    case 'COMPLETE_MAINTENANCE': buttonText = 'Completing maintenance...'; break;
                }
                showAlert(buttonText, 'info');

                $.post('api/update_status.php', requestData, function(response) {
                    if (response && response.success) {
                        showAlert('Equipment status updated successfully!', 'success');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
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

            if (confirmMessage) {
                showEquipmentDetailConfirm(confirmMessage, proceed);
            } else {
                proceed();
            }
        }

        function downloadQR() {
            var canvas = document.querySelector('#qr-code-container canvas');
            if (canvas) {
                var link = document.createElement('a');
                link.download = 'equipment-<?php echo $equipment['id']; ?>-qr.png';
                link.href = canvas.toDataURL();
                link.click();
            } else {
                showAlert('QR code not ready for download.', 'warning');
            }
        }

        function printDetails() {
            window.print();
        }

        $(document).ready(function() {
            // Generate QR Code
            new QRCode(document.getElementById('qr-code-container'), {
                text: '<?php echo htmlspecialchars($qrText, ENT_QUOTES); ?>',
                width: 220,
                height: 220,
                correctLevel: QRCode.CorrectLevel.H
            });

            equipmentDetailConfirmModal = new bootstrap.Modal(document.getElementById('equipmentDetailConfirmModal'));
            $('#equipmentDetailConfirmBtn').click(function() {
                if (equipmentDetailConfirmCallback) {
                    equipmentDetailConfirmCallback();
                }
                equipmentDetailConfirmModal.hide();
            });

            // Control button visibility based on status and ownership
            var returnBtn = $('button[onclick="updateStatus(\'RETURN\')"]');
            var borrowBtn = $('button[onclick="updateStatus(\'BORROW\')"]');
            var maintenanceBtn = $('button[onclick="updateStatus(\'MAINTENANCE\')"]');
            var completeMaintBtn = $('button[onclick="updateStatus(\'COMPLETE_MAINTENANCE\')"]');

            var isOwner = equipmentAssignedTo === currentUserId;
            var isAdmin = currentRole === 'Admin';

            // Hide all buttons by default
            returnBtn.hide();
            borrowBtn.hide();
            maintenanceBtn.hide();
            completeMaintBtn.hide();

            // Show appropriate buttons based on status
            if (equipmentStatus === 'AVAILABLE') {
                borrowBtn.show();
                if (isAdmin) {
                    maintenanceBtn.show();
                }
            } else if (equipmentStatus === 'BORROWED') {
                if (isOwner) {
                    returnBtn.show();
                    if (isAdmin) {
                        maintenanceBtn.show();
                    }
                } else if (isAdmin) {
                    maintenanceBtn.show();
                }
            } else if (equipmentStatus === 'MAINTENANCE') {
                if (isAdmin) {
                    completeMaintBtn.show();
                }
            }

            $.ajax({
                url: 'api/get_locations.php',
                type: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    var select = $('#equipmentDetailReturnLocation');
                    select.empty();
                    select.append('<option value="">Select a return location (required for borrowing)</option>');
                    response.locations.forEach(function(loc) {
                        select.append('<option value="' + loc.id + '">' + loc.name + '</option>');
                    });
                }
            }).fail(function() {
                console.error('Failed to load return locations');
            });
        });
    </script>
</body>
</html>

<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin', 'Staff']);
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'QR Scanner - Equipment Tracker';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'scan'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-12 col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-qr-code-scan text-primary me-3"></i>QR Scanner
                        </h1>
                        <p class="text-muted mb-0 fs-6">Scan equipment QR codes and manage borrow/return actions.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-gradient p-2 me-3">
                                        <i class="bi bi-camera text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Camera Scanner</h5>
                                        <small class="text-muted">Point camera at QR code</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <div id="qr-reader" class="flex-fill mb-3"></div>
                                <div id="scanner-message" class="mt-3 text-center text-muted small">
                                    <i class="bi bi-camera me-1"></i>Initializing camera...
                                </div>
                                <button id="scan-another-btn" class="btn btn-outline-primary w-100 d-none mt-2" type="button">Scan Another Equipment</button>
                                <div id="scanner-controls">
                                    <button id="switch-camera-btn" class="btn btn-outline-secondary w-100 d-none mt-2" type="button">Switch Camera</button>
                                    <div class="mt-3" id="manual-scan-group">
                                        <label for="manual-id-input" class="form-label small text-muted">Or enter Equipment ID manually:</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" class="form-control" id="manual-id-input" placeholder="e.g., 123" min="1">
                                        <button class="btn btn-outline-secondary" type="button" id="manual-load-btn">Load</button>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-gradient p-2 me-3">
                                        <i class="bi bi-info-circle text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Equipment Details</h5>
                                        <small class="text-muted">Scan to load info</small>
                                    </div>
                                </div>
                                <span id="equipment-status-badge"></span>
                            </div>
                            <div class="card-body">
                                <div id="equipment-info-empty" class="text-center text-muted py-5">
                                    <i class="bi bi-qr-code fs-1 mb-3 text-muted"></i>
                                    <p class="mb-0">Scan a QR code to load equipment details</p>
                                </div>
                                <dl class="row equipment-info d-none" id="equipment-info-list">
                                    <dt class="col-sm-4">Equipment ID</dt>
                                    <dd class="col-sm-8 fw-semibold" id="info-id"></dd>

                                    <dt class="col-sm-4">Name</dt>
                                    <dd class="col-sm-8 fw-semibold" id="info-name"></dd>

                                    <dt class="col-sm-4">Category</dt>
                                    <dd class="col-sm-8" id="info-category"></dd>

                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="info-status"></dd>

                                    <dt class="col-sm-4">Location</dt>
                                    <dd class="col-sm-8" id="info-location"></dd>
                                </dl>

                                <div id="action-message" class="mb-3 text-muted"></div>
                                <div id="current-user-group" class="mb-3 d-none">
                                    <label class="form-label fw-semibold">Current User</label>
                                    <div class="form-control form-control-lg bg-light text-dark" id="action-user-display"></div>
                                </div>
                                <div id="return-location-group" class="mb-3 d-none">
                                    <label class="form-label fw-semibold">Destination Location</label>
                                    <select class="form-select" id="return-location-select">
                                        <option value="">Select a location for returning this item later...</option>
                                    </select>
                                </div>
                                <div id="action-buttons" class="d-none">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <button id="btn-checkin" class="btn btn-success btn-lg w-100">
                                                <i class="bi bi-check-circle me-2"></i>Return Equipment
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <button id="btn-checkout" class="btn btn-primary btn-lg w-100">
                                                <i class="bi bi-arrow-right-circle me-2"></i>Borrow Equipment
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <button id="btn-maintenance" class="btn btn-warning btn-lg w-100">
                                                <i class="bi bi-wrench me-2"></i>Send to Maintenance
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <button id="btn-complete-maintenance" class="btn btn-success btn-lg w-100">
                                                <i class="bi bi-check-circle me-2"></i>Complete Maintenance
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="action-alert" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-labelledby="actionConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionConfirmModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="actionConfirmModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="actionConfirmModalConfirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        var currentEquipmentId = null;
        var currentUserId = <?php echo (int)($user['id'] ?? 0); ?>;
        var currentUser = '<?php echo htmlspecialchars($user['name'] ?: $user['username'], ENT_QUOTES); ?>';
        var currentRole = '<?php echo htmlspecialchars($user['role'], ENT_QUOTES); ?>';
        var html5QrcodeScanner = null;
        var availableCameras = [];
        var currentCameraIndex = 0;
        var isScannerHiddenOnMobile = false;
        var isScannerStarted = false;

        function showAlert(message, type) {
            $('#action-alert').html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>');
        }

        function resetEquipmentInfo() {
            currentEquipmentId = null;
            $('#equipment-info-list').addClass('d-none');
            $('#action-buttons').addClass('d-none');
            $('#return-location-group').addClass('d-none');
            $('#current-user-group').addClass('d-none');
            $('#return-location-select').val('');
            $('#equipment-info-empty').removeClass('d-none');
            $('#equipment-status-badge').text('');
            $('#action-alert').empty();
            $('#action-user-display').text('');
            $('#scan-another-btn').addClass('d-none');
            if (isScannerHiddenOnMobile) {
                $('#qr-reader').removeClass('d-none');
                isScannerHiddenOnMobile = false;
            }
            $('#scanner-message').text('Scan a QR code to load equipment details.');
        }

        function stopScanner() {
            if (html5QrcodeScanner && isScannerStarted) {
                html5QrcodeScanner.stop().then(function() {
                    isScannerStarted = false;
                }).catch(function(err) {
                    console.warn('Failed to stop scanner:', err);
                });
            }
        }

        function renderEquipmentInfo(data) {
            currentEquipmentId = data.id;
            $('#info-id').text(data.id);
            $('#info-name').text(data.name || '-');
            $('#info-category').text(data.category || '-');
            $('#info-status').text(data.status || '-');
            $('#info-location').text(data.location || '-');
            var badgeClass = 'bg-secondary';
            if (data.status === 'AVAILABLE') badgeClass = 'bg-success';
            else if (data.status === 'BORROWED') badgeClass = 'bg-primary';
            else if (data.status === 'MAINTENANCE') badgeClass = 'bg-warning text-dark';
            $('#equipment-status-badge').html('<span class="badge ' + badgeClass + '">' + (data.status || 'Unknown') + '</span>');
            $('#equipment-info-list').removeClass('d-none');
            $('#action-buttons').removeClass('d-none');
            $('#equipment-info-empty').addClass('d-none');
            $('#action-alert').empty();
            $('#action-user-display').text(currentUser);
            // $('#current-user-group').removeClass('d-none');
            $('#return-location-select').val(data.location_id || '');
            $('#scan-another-btn').removeClass('d-none');
            if (window.innerWidth < 768) {
                $('#qr-reader').addClass('d-none');
                $('#scanner-controls').addClass('d-none');
                isScannerHiddenOnMobile = true;
                stopScanner();
                $('#scanner-message').text('Equipment loaded. Scroll down to see details or tap Scan Another Equipment.');
            }
            updateActionButtons(data);
        }

        function fetchEquipmentById(id) {
            $('#scanner-message').text('Loading equipment details...');
            $.ajax({
                url: 'api/get_equipment_detail.php',
                type: 'GET',
                dataType: 'json',
                data: { id: id }
            }).done(function(response) {
                if (response && response.success && response.data) {
                    renderEquipmentInfo(response.data);
                    $('#scanner-message').text('Equipment loaded successfully.');
                } else {
                    resetEquipmentInfo();
                    showAlert(response.error || 'Equipment not found.', 'danger');
                    $('#scanner-message').text('Scan a QR code to load equipment details.');
                }
            }).fail(function() {
                resetEquipmentInfo();
                showAlert('Unable to load equipment details.', 'danger');
                $('#scanner-message').text('Scan a QR code to load equipment details.');
            });
        }

        var confirmActionCallback = null;
        var confirmActionModal = null;

        function showActionConfirm(message, callback) {
            $('#actionConfirmModalMessage').text(message);
            confirmActionCallback = callback;
            confirmActionModal.show();
        }

        function updateEquipmentStatus(status) {
            if (!currentEquipmentId) {
                showAlert('No equipment selected.', 'danger');
                return;
            }

            var requestData = {
                id: currentEquipmentId,
                status: status
            };

            if (status === 'BORROW') {
                var location = $('#return-location-select').val();
                if (!location) {
                    showAlert('Please select a return location destination before borrowing.', 'warning');
                    return;
                }
                requestData.return_location = location;
            }

            var confirmMessage = '';
            if (status === 'BORROW') {
                confirmMessage = 'Borrow this equipment and assign a destination location?';
            } else if (status === 'RETURN') {
                confirmMessage = 'Return this equipment and make it available again?';
            } else if (status === 'MAINTENANCE') {
                confirmMessage = 'Send this equipment to maintenance? It will no longer be available for borrowing.';
            } else if (status === 'COMPLETE_MAINTENANCE') {
                confirmMessage = 'Mark maintenance complete and make this equipment available?';
            }

            function proceedWithStatusUpdate() {
                $.ajax({
                    url: 'api/update_status.php',
                    type: 'POST',
                    dataType: 'json',
                    data: requestData
                }).done(function(response) {
                    if (response && response.success) {
                        showAlert(response.message || 'Status updated.', 'success');
                        fetchEquipmentById(currentEquipmentId);
                    } else {
                        showAlert(response.error || 'Unable to update status.', 'danger');
                    }
                }).fail(function() {
                    showAlert('Unable to update status.', 'danger');
                });
            }

            if (confirmMessage) {
                showActionConfirm(confirmMessage, proceedWithStatusUpdate);
                return;
            }

            proceedWithStatusUpdate();
        }
        
             

        function updateActionButtons(data) {
            var status = data.status;
            var assignedTo = data.assigned_to;
            var isOwner = assignedTo == currentUserId;
            var isAdmin = currentRole === 'Admin';

            $('#btn-checkin').hide();
            $('#btn-checkout').hide();
            $('#btn-maintenance').hide();
            $('#btn-complete-maintenance').hide();
            $('#return-location-group').addClass('d-none');
            $('#action-message').text('');
            $('#action-buttons').removeClass('d-none');

            if (status === 'AVAILABLE') {
                $('#btn-checkout').show();
                $('#return-location-group').removeClass('d-none');
                if (isAdmin) {
                    $('#btn-maintenance').show();
                }
                $('#action-message').text('This equipment is available. Select where you will return it, then borrow it.');
            } else if (status === 'BORROWED') {
                $('#return-location-group').addClass('d-none');
                if (isOwner) {
                    $('#btn-checkin').show();
                    if (isAdmin) {
                        $('#btn-maintenance').show();
                    }
                    if (data.return_location) {
                        $('#action-message').text('You currently have this equipment borrowed. Return it to ' + data.return_location + ' before clicking the return equipment button.');
                    } else if (data.location) {
                        $('#action-message').text('You currently have this equipment borrowed. Return it to ' + data.location + ' before clicking the return equipment button.');
                    } else {
                        $('#action-message').text('You currently have this equipment borrowed.');
                    }
                } else {
                    if (isAdmin) {
                        $('#btn-maintenance').show();
                        if (data.location) {
                            $('#action-message').text('Borrowed by ' + assignedTo + '. Admin can send this equipment to maintenance.');
                        } else {
                            $('#action-message').text('Borrowed by ' + assignedTo + '. Admin can send this equipment to maintenance.');
                        }
                    } else {
                        if (data.location) {
                            $('#action-message').text('Borrowed by ' + assignedTo + '. Expected return location: ' + data.location + '.');
                        } else {
                            $('#action-message').text('Borrowed by ' + assignedTo + '. You cannot return it.');
                        }
                        $('#action-buttons').addClass('d-none');
                    }
                }
            } else if (status === 'MAINTENANCE') {
                $('#action-buttons').removeClass('d-none');
                if (isAdmin) {
                    $('#btn-complete-maintenance').show();
                    $('#action-message').text('This equipment is currently in maintenance. Admin can complete maintenance to make it available again.');
                } else {
                    $('#action-buttons').addClass('d-none');
                    $('#action-message').text('This equipment is currently in maintenance and not available for borrowing.');
                }
            } else {
                $('#action-buttons').addClass('d-none');
                $('#action-message').text('This equipment is not available for actions right now.');
            }
        }

        function onScanSuccess(decodedText) {
            var match = decodedText.match(/equipment_id=(\d+)/);
            if (match && match[1]) {
                fetchEquipmentById(match[1]);
            } else {
                showAlert('QR code format not recognized.', 'warning');
            }
        }

        function onScanError(errorMessage) {
            console.warn('QR scan error:', errorMessage);
        }

        $(document).ready(function() {
            resetEquipmentInfo();

            // Load locations
            $.ajax({
                url: 'api/get_locations.php',
                type: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    var select = $('#return-location-select');
                    select.empty();
                    select.append('<option value="">Select a return location...</option>');
                    response.locations.forEach(function(loc) {
                        select.append('<option value="' + loc.id + '">' + loc.name + '</option>');
                    });
                }
            }).fail(function() {
                console.error('Failed to load locations');
            });

            html5QrcodeScanner = new Html5Qrcode('qr-reader');

            function getBackCameraIndex(cameras) {
                var backIndex = cameras.findIndex(function(camera) {
                    var label = (camera.label || '').toLowerCase();
                    return /back|rear|environment|world/.test(label);
                });
                return backIndex >= 0 ? backIndex : 0;
            }

            function startCamera(index) {
                if (!availableCameras.length) {
                    return;
                }
                currentCameraIndex = index;
                var cameraId = availableCameras[currentCameraIndex].id;
                $('#scanner-message').text('Starting camera...');
                html5QrcodeScanner.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: 250
                    },
                    onScanSuccess,
                    onScanError
                ).then(function() {
                    isScannerStarted = true;
                    $('#scanner-message').text('Camera started. Scan the QR code.');
                }).catch(function(err) {
                    $('#scanner-message').text('Unable to start camera: ' + err);
                    console.error(err);
                });
            }

            function switchCamera() {
                if (!availableCameras.length) {
                    return;
                }
                currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
                stopScanner();
                startCamera(currentCameraIndex);
            }

            Html5Qrcode.getCameras().then(function(cameras) {
                if (cameras && cameras.length) {
                    availableCameras = cameras;
                    currentCameraIndex = getBackCameraIndex(cameras);
                    startCamera(currentCameraIndex);
                    if (cameras.length > 1 && window.innerWidth < 768) {
                        $('#switch-camera-btn').removeClass('d-none');
                    }
                } else {
                    $('#scanner-message').text('No camera found.');
                }
            }).catch(function(err) {
                $('#scanner-message').text('Camera access denied or unavailable.');
                console.error(err);
            });

            $('#switch-camera-btn').click(switchCamera);

            $('#scan-another-btn').click(function() {
                resetEquipmentInfo();
                if (!isScannerStarted && availableCameras.length) {
                    $('#scanner-controls').removeClass('d-none');
                    $('#scan-another-btn').addClass('d-none');
                    $('#qr-reader').removeClass('d-none');
                    startCamera(currentCameraIndex);
                }
            });

            $('#btn-checkin').click(function() {
                updateEquipmentStatus('RETURN');
            });
            $('#btn-checkout').click(function() {
                updateEquipmentStatus('BORROW');
            });
            $('#btn-maintenance').click(function() {
                updateEquipmentStatus('MAINTENANCE');
            });
            $('#btn-complete-maintenance').click(function() {
                updateEquipmentStatus('COMPLETE_MAINTENANCE');
            });

            $('#manual-load-btn').click(function() {
                var id = $('#manual-id-input').val().trim();
                if (id && /^\d+$/.test(id)) {
                    fetchEquipmentById(id);
                } else {
                    showAlert('Please enter a valid Equipment ID.', 'warning');
                }
            });

            $('#manual-id-input').keypress(function(e) {
                if (e.which === 13) { // Enter key
                    $('#manual-load-btn').click();
                }
            });

            confirmActionModal = new bootstrap.Modal(document.getElementById('actionConfirmModal'));

            $('#actionConfirmModalConfirmBtn').click(function() {
                if (confirmActionCallback) {
                    confirmActionCallback();
                }
                confirmActionModal.hide();
            });
        });
    </script>
</body>
</html>
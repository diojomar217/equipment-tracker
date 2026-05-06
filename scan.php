<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin', 'Staff']);
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'QR Scanner - Equipment Tracker';
$pageStyles = '<style>
/* Scan page specific styles */
.scanner-card {
    margin-top: 0;
}

.scanner-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 992px) {
    .scanner-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

.scanner-message {
    text-align: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    margin-top: 1rem;
}

.equipment-details-card {
    position: sticky;
    top: 2rem;
}

.equipment-empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.equipment-empty-state i {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.action-buttons-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.equipment-info-display {
    background: #f8fafc;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.equipment-info-display dt {
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.equipment-info-display dd {
    margin-bottom: 0.75rem;
    margin-left: 0;
    font-weight: 500;
    color: #0f172a;
}

@media (max-width: 576px) {
    .equipment-info-display dt,
    .equipment-info-display dd {
        display: block;
        width: 100%;
        margin-left: 0;
    }
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'scan'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-qr-code-scan text-primary me-3"></i>QR Scanner
                        </h1>
                        <p class="text-muted mb-0 fs-6">Scan equipment QR codes and manage check-in/check-out actions.</p>
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

                                <div id="action-buttons" class="d-none">
                                    <div class="mb-3">
                                        <label for="action-user" class="form-label fw-semibold">Your Name</label>
                                        <input type="text" class="form-control form-control-lg" id="action-user" placeholder="Enter your name">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <button id="btn-checkin" class="btn btn-success btn-lg w-100">
                                                <i class="bi bi-check-circle me-2"></i>Check-in Equipment
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <button id="btn-checkout" class="btn btn-primary btn-lg w-100">
                                                <i class="bi bi-arrow-right-circle me-2"></i>Check-out Equipment
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <button id="btn-maintenance" class="btn btn-warning btn-lg w-100">
                                                <i class="bi bi-wrench me-2"></i>Send to Maintenance
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        var currentEquipmentId = null;

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
            $('#equipment-info-empty').removeClass('d-none');
            $('#equipment-status-badge').text('');
            $('#action-alert').empty();
        }

        function renderEquipmentInfo(data) {
            currentEquipmentId = data.id;
            $('#info-id').text(data.id);
            $('#info-name').text(data.name || '-');
            $('#info-category').text(data.category || '-');
            $('#info-status').text(data.status || '-');
            $('#info-location').text(data.location || '-');
            $('#equipment-status-badge').html('<span class="badge badge-secondary">' + (data.status || 'Unknown') + '</span>');
            $('#equipment-info-list').removeClass('d-none');
            $('#action-buttons').removeClass('d-none');
            $('#equipment-info-empty').addClass('d-none');
            $('#action-alert').empty();
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

        function updateEquipmentStatus(status) {
            var user = $('#action-user').val().trim();
            if (!currentEquipmentId) {
                showAlert('No equipment selected.', 'danger');
                return;
            }
            if (!user) {
                showAlert('Please enter your name before continuing.', 'warning');
                return;
            }

            $.ajax({
                url: 'api/update_status.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: currentEquipmentId,
                    status: status,
                    user: user
                }
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

            var html5QrcodeScanner = new Html5Qrcode('qr-reader');
            Html5Qrcode.getCameras().then(function(cameras) {
                if (cameras && cameras.length) {
                    var cameraId = cameras[0].id;
                    html5QrcodeScanner.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: 250
                        },
                        onScanSuccess,
                        onScanError
                    ).catch(function(err) {
                        $('#scanner-message').text('Unable to start camera: ' + err);
                    });
                } else {
                    $('#scanner-message').text('No camera found.');
                }
            }).catch(function(err) {
                $('#scanner-message').text('Camera access denied or unavailable.');
                console.error(err);
            });

            $('#btn-checkin').click(function() {
                updateEquipmentStatus('CHECK_IN');
            });
            $('#btn-checkout').click(function() {
                updateEquipmentStatus('CHECK_OUT');
            });
            $('#btn-maintenance').click(function() {
                updateEquipmentStatus('MAINTENANCE');
            });
        });
    </script>
</body>
</html>
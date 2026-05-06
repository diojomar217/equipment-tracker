<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin', 'Staff']);
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'QR Scanner - Equipment Tracker';
$pageStyles = '<style>body { background-color: #f8f9fa; } .scanner-card { margin-top: 20px; } #qr-reader { width: 100%; min-height: 60vh; max-height: 75vh; overflow: hidden; } #qr-reader video, #qr-reader canvas, #qr-reader img { width: 100% !important; height: 100% !important; object-fit: cover; } .equipment-info dt { width: 110px; } .equipment-info dd { margin-left: 120px; } #action-buttons .btn { min-height: 56px; font-size: 1rem; padding-top: .75rem; padding-bottom: .75rem; } @media (max-width: 767px) { .scanner-card { margin-top: 0.5rem; } #qr-reader { min-height: 55vh; max-height: none; } .equipment-info dt, .equipment-info dd { display: block; width: 100%; margin-left: 0; } .equipment-info dd { margin-bottom: 0.75rem; } }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <?php $activePage = 'scan'; $brandUrl = 'scan.php'; include __DIR__ . '/includes/topnav.php'; ?>

                <span class="navbar-text mr-3">Signed in as <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
                <?php if ($user['role'] === 'Admin'): ?>
                    <a class="nav-item nav-link" href="equipment.php">Equipment</a>
                    <a class="nav-item nav-link" href="logs.php">Logs</a>
                <?php endif; ?>
                <a class="nav-item nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid scanner-card px-3 px-md-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3">QR Scanner</h1>
                <p class="text-muted">Scan equipment QR codes and manage status actions.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        Camera Scanner
                    </div>
                    <div class="card-body p-2 d-flex flex-column">
                        <div id="qr-reader" class="flex-fill"></div>
                        <div id="scanner-message" class="mt-3 text-center text-muted">Point your camera at a QR code.</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Equipment Details</span>
                        <span id="equipment-status-badge"></span>
                    </div>
                    <div class="card-body">
                        <div id="equipment-info-empty" class="text-center text-muted py-5">
                            Scan a QR code to load equipment details.
                        </div>
                        <dl class="row equipment-info d-none" id="equipment-info-list">
                            <dt class="col-sm-4">Equipment ID</dt>
                            <dd class="col-sm-8" id="info-id"></dd>

                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8" id="info-name"></dd>

                            <dt class="col-sm-4">Category</dt>
                            <dd class="col-sm-8" id="info-category"></dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8" id="info-status"></dd>

                            <dt class="col-sm-4">Location</dt>
                            <dd class="col-sm-8" id="info-location"></dd>
                        </dl>

                        <div id="action-buttons" class="d-none">
                            <div class="form-group">
                                <label for="action-user">User</label>
                                <input type="text" class="form-control form-control-lg" id="action-user" placeholder="Enter your name">
                            </div>
                            <button id="btn-checkin" class="btn btn-success btn-lg btn-block mb-2">Check-in</button>
                            <button id="btn-checkout" class="btn btn-primary btn-lg btn-block mb-2">Check-out</button>
                            <button id="btn-maintenance" class="btn btn-warning btn-lg btn-block">Maintenance</button>
                        </div>

                        <div id="action-alert" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js" integrity="sha384-LtrjvnR4/Jdsd0O4mFsXEDX0B+e5qFQFDeUqQSRreI6G0nDZK07bqYDl38opP0xV" crossorigin="anonymous"></script>
    <script>
        var currentEquipmentId = null;

        function showAlert(message, type) {
            $('#action-alert').html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
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
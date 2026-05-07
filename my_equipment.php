<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin', 'Staff']);
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'My Equipment';
$pageStyles = '<style>
/* Equipment specific styles */
.equipment-table th {
    position: sticky;
    top: 0;
    z-index: 10;
}

.equipment-image-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.equipment-image-thumb:hover {
    transform: scale(1.05);
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php
            $activePage = 'my_equipment';
            include __DIR__ . '/includes/sidebar.php';
            ?>
            <main class="col-12 col-md-9 col-lg-10 main-content py-4 px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0 fw-bold">My Equipment</h1>
                        <p class="text-muted">Equipment currently assigned to you</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary fs-6 px-3 py-2 me-3" id="equipment-count">0</span>
                    </div>
                </div>

                <!-- Equipment Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 equipment-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">ID</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Location</th>
                                                <th class="text-center">Image</th>
                                                <?php if ($user['role'] === 'Admin'): ?>
                                                <th class="text-center">QR Code</th>
                                                <?php endif; ?>
                                                <th class="text-center pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="equipment-table-body">
                                            <!-- Equipment rows will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="alert-container"></div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            var currentRole = '<?php echo htmlspecialchars($user['role'], ENT_QUOTES); ?>';
            var isAdmin = currentRole === 'Admin';
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

            function renderTable(data) {
                var $tbody = $('#equipment-table-body');
                $tbody.empty();

                if (!Array.isArray(data) || data.length === 0) {
                    var colspan = isAdmin ? 8 : 7;
                    var emptyRow = '<tr>' +
                        '<td colspan="' + colspan + '" class="text-center py-5 text-muted">' +
                        '<i class="bi bi-inbox fs-1 mb-3 text-muted"></i><br>No equipment assigned to you</td>' +
                        '</tr>';
                    $tbody.append(emptyRow);
                    $('#equipment-count').text('0');
                    return;
                }

                $('#equipment-count').text(data.length);

                data.forEach(function(item) {
                    var qrContainerId = 'qrcode-' + item.id;
                    var printButton = '';
                    if (isAdmin && item.id !== undefined) {
                        printButton = '<a href="print_qr.php?id=' + encodeURIComponent(item.id) + '" target="_blank" class="btn btn-sm btn-outline-primary me-2"><i class="bi bi-printer"></i></a>';
                    }
                    var detailsButton = item.id !== undefined ?
                        '<a href="equipment_detail.php?id=' + encodeURIComponent(item.id) + '" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>' :
                        '';
                    var imageHtml = '';
                    if (item.image) {
                        imageHtml = '<img src="' + encodeURI(item.image) + '" alt="Equipment image" class="img-fluid rounded" style="max-height: 60px; max-width: 80px;">';
                    } else {
                        imageHtml = '<span class="text-muted small"><i class="bi bi-image"></i></span>';
                    }

                    var statusBadge = '';
                    switch(item.status) {
                        case 'AVAILABLE':
                            statusBadge = '<span class="badge bg-success">Available</span>';
                            break;
                        case 'BORROWED':
                            statusBadge = '<span class="badge bg-warning text-dark">Borrowed</span>';
                            break;
                        case 'MAINTENANCE':
                            statusBadge = '<span class="badge bg-danger">Maintenance</span>';
                            break;
                        default:
                            statusBadge = '<span class="badge bg-secondary">' + (item.status || 'Unknown') + '</span>';
                    }

                    var qrCodeCell = '';
                    if (isAdmin) {
                        qrCodeCell = '<td class="text-center"><div id="' + qrContainerId + '" class="mx-auto" style="width: 60px; height: 60px;"></div></td>';
                    }

                    var row = '<tr>' +
                        '<td class="ps-4 fw-semibold">' + (item.id !== undefined ? item.id : '') + '</td>' +
                        '<td class="fw-semibold">' + (item.name !== undefined ? item.name : '') + '</td>' +
                        '<td>' + (item.category !== undefined ? item.category : '') + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td><i class="bi bi-geo-alt text-muted me-1"></i>' + (item.location !== undefined ? item.location : '') + '</td>' +
                        '<td class="text-center">' + imageHtml + '</td>' +
                        qrCodeCell +
                        '<td class="text-center pe-4">' + printButton + detailsButton + '</td>' +
                        '</tr>';
                    $tbody.append(row);

                    if (isAdmin && item.id !== undefined) {
                        new QRCode(document.getElementById(qrContainerId), {
                            text: 'equipment_id=' + item.id,
                            width: 60,
                            height: 60,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    }
                });
            }

            function loadEquipment() {
                $.ajax({
                    url: 'api/get_equipment.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        assigned_to: '<?php echo (int)$user['id']; ?>'
                    }
                }).done(function(response) {
                    if (response && response.success) {
                        renderTable(response.data);
                    } else {
                        showError('No data found');
                    }
                }).fail(function() {
                    showError('Unable to load equipment data.');
                });
            }

            loadEquipment();
        });
    </script>
</body>
</html>
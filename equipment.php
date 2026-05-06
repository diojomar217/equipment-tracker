<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role('Admin');
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Equipment Management';
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

.filter-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.upload-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
    background: #fafafa;
}

.upload-zone:hover {
    border-color: #0d6efd;
    background: #f0f4ff;
}

.upload-zone.dragover {
    border-color: #0d6efd;
    background: #e0e7ff;
}

.status-filter .btn {
    margin: 0.25rem;
}

.pagination-container {
    margin-top: 2rem;
}

.equipment-modal .modal-dialog {
    max-width: 800px;
}

.equipment-form .row {
    margin-bottom: 1rem;
}

.image-preview {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0b5ed7, #4338ca);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}
.btn-outline-secondary {
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.btn-outline-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}
.table th {
    background-color: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    color: #374151;
    padding: 1rem;
}
.table td {
    vertical-align: middle;
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
}
.table-hover tbody tr:hover {
    background-color: #f8fafc;
}
.badge {
    font-weight: 500;
    padding: 0.375rem 0.75rem;
}
.form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'equipment'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-box-seam text-primary me-3"></i>Equipment Management
                        </h1>
                        <p class="text-muted mb-0 fs-6">Manage your equipment inventory, add new items, and track maintenance.</p>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="api/export_equipment_csv.php" class="btn btn-outline-secondary px-4 py-2" target="_blank">
                            <i class="bi bi-download me-2"></i>Export CSV
                        </a>
                        <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                            <i class="bi bi-plus-circle me-2"></i>Add Equipment
                        </button>
                    </div>
                </div>

                <!-- Upload Image Section -->
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-gradient p-2 me-3">
                                        <i class="bi bi-image text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Upload Equipment Images</h5>
                                        <small class="text-muted">Add photos to your equipment for better identification</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="upload-image-form" enctype="multipart/form-data">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-5">
                                            <label for="equipment-select" class="form-label fw-semibold">Select Equipment</label>
                                            <select class="form-control form-control-lg" id="equipment-select" name="equipment_id" required>
                                                <option value="">Choose equipment...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label for="equipment-image" class="form-label fw-semibold">Image File</label>
                                            <input type="file" class="form-control form-control-lg" id="equipment-image" name="image" accept="image/jpeg,image/png" required>
                                            <div class="form-text">JPG, JPEG, PNG only. Max 2MB.</div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-success btn-lg w-100">
                                                <i class="bi bi-upload me-2"></i>Upload
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <div id="upload-preview" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <label for="location-filter" class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt text-muted me-2"></i>Filter by Location
                                </label>
                                <select class="form-control form-control-lg" id="location-filter">
                                    <option value="">All locations</option>
                                    <option value="Room 101">Room 101</option>
                                    <option value="Storage">Storage</option>
                                    <option value="Office">Office</option>
                                    <option value="Lab">Lab</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipment Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-gradient p-2 me-3">
                                            <i class="bi bi-table text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold">Equipment Inventory</h5>
                                            <small class="text-muted">Complete list of all equipment</small>
                                        </div>
                                    </div>
                                    <div class="text-muted small">
                                        <span id="equipment-count">0</span> items total
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div id="alert-container"></div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">ID</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Location</th>
                                                <th>Image</th>
                                                <th>QR Code</th>
                                                <th class="pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="equipment-table-body">
                                            <tr>
                                                <td colspan="8" class="text-center py-5 text-muted">
                                                    <i class="bi bi-arrow-clockwise fs-1 mb-3 text-muted"></i>
                                                    <br>Loading equipment...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form id="add-equipment-form">
                    <div class="modal-header bg-primary bg-gradient text-white">
                        <h5 class="modal-title fw-bold" id="addEquipmentModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>Add New Equipment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="equipment-name" class="form-label fw-semibold">Equipment Name</label>
                                <input type="text" class="form-control form-control-lg" id="equipment-name" name="name" placeholder="Enter equipment name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="equipment-category" class="form-label fw-semibold">Category</label>
                                <input type="text" class="form-control form-control-lg" id="equipment-category" name="category" placeholder="e.g., Computer, Tool, Furniture" required>
                            </div>
                            <div class="col-12">
                                <label for="equipment-location" class="form-label fw-semibold">Location</label>
                                <select class="form-control form-control-lg" id="equipment-location" name="location" required>
                                    <option value="">Select a location...</option>
                                    <option value="Room 101">Room 101</option>
                                    <option value="Storage">Storage</option>
                                    <option value="Office">Office</option>
                                    <option value="Lab">Lab</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-2"></i>Save Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            function showAlert(message, type) {
                var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                $('#alert-container').html(alertHtml);
            }

            function renderTable(data) {
                var $tbody = $('#equipment-table-body');
                $tbody.empty();

                if (!Array.isArray(data) || data.length === 0) {
                    var emptyRow = '<tr>' +
                        '<td colspan="8" class="text-center py-5 text-muted">' +
                        '<i class="bi bi-inbox fs-1 mb-3 text-muted"></i><br>No equipment found</td>' +
                        '</tr>';
                    $tbody.append(emptyRow);
                    $('#equipment-count').text('0');
                    return;
                }

                $('#equipment-count').text(data.length);

                data.forEach(function(item) {
                    var qrContainerId = 'qrcode-' + item.id;
                    var printButton = item.id !== undefined ?
                        '<a href="print_qr.php?id=' + encodeURIComponent(item.id) + '" target="_blank" class="btn btn-sm btn-outline-primary me-2"><i class="bi bi-printer"></i></a>' :
                        '';
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
                        case 'CHECK_OUT':
                            statusBadge = '<span class="badge bg-warning text-dark">Checked Out</span>';
                            break;
                        case 'MAINTENANCE':
                            statusBadge = '<span class="badge bg-danger">Maintenance</span>';
                            break;
                        default:
                            statusBadge = '<span class="badge bg-secondary">' + (item.status || 'Unknown') + '</span>';
                    }

                    var row = '<tr>' +
                        '<td class="ps-4 fw-semibold">' + (item.id !== undefined ? item.id : '') + '</td>' +
                        '<td class="fw-semibold">' + (item.name !== undefined ? item.name : '') + '</td>' +
                        '<td>' + (item.category !== undefined ? item.category : '') + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td><i class="bi bi-geo-alt text-muted me-1"></i>' + (item.location !== undefined ? item.location : '') + '</td>' +
                        '<td class="text-center">' + imageHtml + '</td>' +
                        '<td class="text-center"><div id="' + qrContainerId + '" class="mx-auto" style="width: 60px; height: 60px;"></div></td>' +
                        '<td class="text-center pe-4">' + printButton + detailsButton + '</td>' +
                        '</tr>';
                    $tbody.append(row);

                    if (item.id !== undefined) {
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

            var locationOptions = ['Room 101', 'Storage', 'Office', 'Lab'];

            function populateEquipmentSelect(data) {
                var $select = $('#equipment-select');
                $select.find('option:not(:first)').remove();
                data.forEach(function(item) {
                    if (item.id !== undefined && item.name !== undefined) {
                        $select.append('<option value="' + item.id + '">' + item.name + ' (ID ' + item.id + ')</option>');
                    }
                });
            }

            function populateLocationFilter() {
                var $filter = $('#location-filter');
                var currentValue = $filter.val();
                $filter.find('option:not(:first)').remove();
                locationOptions.forEach(function(location) {
                    $filter.append('<option value="' + location + '">' + location + '</option>');
                });
                if (currentValue) {
                    $filter.val(currentValue);
                }
            }

            function showError(message) {
                var $tbody = $('#equipment-table-body');
                $tbody.html('<tr><td colspan="8" class="text-center text-danger py-4">' + message + '</td></tr>');
            }

            function loadEquipment() {
                $.ajax({
                    url: 'api/get_equipment.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        location: $('#location-filter').val()
                    }
                }).done(function(response) {
                    if (response && response.success) {
                        renderTable(response.data);
                        populateEquipmentSelect(response.data);
                    } else {
                        showError('No data found');
                    }
                }).fail(function() {
                    showError('Unable to load equipment data.');
                });
            }

            $('#upload-image-form').submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'api/upload_equipment_image.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function(response) {
                    if (response && response.success) {
                        $('#upload-image-form')[0].reset();
                        $('#upload-preview').html(
                            '<div class="alert alert-success">' + response.message + '</div>' +
                            '<div class="mt-3"><img src="' + encodeURI(response.image_path) + '" alt="Uploaded image preview" class="img-fluid img-thumbnail" style="max-width: 240px;"></div>'
                        );
                        showAlert('Image uploaded successfully.', 'success');
                        loadEquipment();
                    } else {
                        showAlert(response.error || 'Unable to upload image.', 'danger');
                    }
                }).fail(function(xhr) {
                    var errorMessage = 'Unable to upload image.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    showAlert(errorMessage, 'danger');
                });
            });

            $('#location-filter').change(loadEquipment);
            populateLocationFilter();

            $('#add-equipment-form').submit(function(event) {
                event.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: 'api/add_equipment.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json'
                }).done(function(response) {
                    if (response && response.success) {
                        $('#addEquipmentModal').modal('hide');
                        $('#add-equipment-form')[0].reset();
                        showAlert('Equipment added successfully.', 'success');
                        loadEquipment();
                    } else {
                        showAlert(response.error || 'Unable to save equipment.', 'danger');
                    }
                }).fail(function() {
                    showAlert('Unable to save equipment.', 'danger');
                });
            });

            loadEquipment();
        });
    </script>
</body>
</html>
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
            <main class="col-12 col-md-9 col-lg-10 content-area px-4">
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

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <label for="location-filter" class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt text-muted me-2"></i>Filter by Location
                                </label>
                                <select class="form-control form-control-lg" id="location-filter">
                                    <option value="">All locations</option>
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
                <form id="add-equipment-form" enctype="multipart/form-data">
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
                                <select class="form-control form-control-lg" id="equipment-category" name="category" required>
                                    <option value="">Select a category...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="equipment-location" class="form-label fw-semibold">Location</label>
                                <select class="form-control form-control-lg" id="equipment-location" name="location" required>
                                    <option value="">Select a location...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="equipment-return-location" class="form-label fw-semibold">Return Location (optional)</label>
                                <select class="form-control form-control-lg" id="equipment-return-location" name="return_location">
                                    <option value="">Select a return location...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="equipment-image" class="form-label fw-semibold">Equipment Image (Optional)</label>
                                <input type="file" class="form-control form-control-lg" id="equipment-image" name="image" accept="image/jpeg,image/png">
                                <div class="form-text">JPG, JPEG, PNG only. Max 2MB.</div>
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

    <div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form id="edit-equipment-form" enctype="multipart/form-data">
                    <input type="hidden" id="edit-equipment-id" name="id">
                    <div class="modal-header bg-warning bg-gradient text-dark">
                        <h5 class="modal-title fw-bold" id="editEquipmentModalLabel">
                            <i class="bi bi-pencil me-2"></i>Edit Equipment
                        </h5>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit-equipment-name" class="form-label fw-semibold">Equipment Name</label>
                                <input type="text" class="form-control form-control-lg" id="edit-equipment-name" name="name" placeholder="Enter equipment name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-equipment-category" class="form-label fw-semibold">Category</label>
                                <select class="form-control form-control-lg" id="edit-equipment-category" name="category" required>
                                    <option value="">Select a category...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit-equipment-location" class="form-label fw-semibold">Location</label>
                                <select class="form-control form-control-lg" id="edit-equipment-location" name="location" required>
                                    <option value="">Select a location...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit-equipment-return-location" class="form-label fw-semibold">Return Location (optional)</label>
                                <select class="form-control form-control-lg" id="edit-equipment-return-location" name="return_location">
                                    <option value="">Select a return location...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit-equipment-image" class="form-label fw-semibold">Equipment Image (Optional)</label>
                                <input type="file" class="form-control form-control-lg" id="edit-equipment-image" name="image" accept="image/jpeg,image/png">
                                <div class="form-text">JPG, JPEG, PNG only. Max 2MB. Leave empty to keep current image.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-check-circle me-2"></i>Update Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteEquipmentModal" tabindex="-1" aria-labelledby="deleteEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger bg-gradient text-white">
                    <h5 class="modal-title fw-bold" id="deleteEquipmentModalLabel">
                        <i class="bi bi-trash me-2"></i>Delete Equipment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0">Are you sure you want to delete <strong id="delete-equipment-name"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirm-delete-btn">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            // Load categories and locations
            $.ajax({
                url: 'api/get_categories.php',
                type: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    var categorySelects = ['#equipment-category', '#edit-equipment-category'];
                    categorySelects.forEach(function(selector) {
                        var select = $(selector);
                        select.empty();
                        select.append('<option value="">Select a category...</option>');
                        response.categories.forEach(function(cat) {
                            select.append('<option value="' + cat.id + '">' + cat.name + '</option>');
                        });
                    });
                }
            }).fail(function() {
                console.error('Failed to load categories');
            });

            $.ajax({
                url: 'api/get_locations.php',
                type: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    var locationSelects = ['#equipment-location', '#edit-equipment-location', '#equipment-return-location', '#edit-equipment-return-location'];
                    locationSelects.forEach(function(selector) {
                        var select = $(selector);
                        select.empty();
                        select.append('<option value="">Select a location...</option>');
                        response.locations.forEach(function(loc) {
                            select.append('<option value="' + loc.id + '">' + loc.name + '</option>');
                        });
                    });

                    // Populate location filter
                    var $filter = $('#location-filter');
                    $filter.find('option:not(:first)').remove();
                    response.locations.forEach(function(loc) {
                        $filter.append('<option value="' + loc.name + '">' + loc.name + '</option>');
                    });
                }
            }).fail(function() {
                console.error('Failed to load locations');
            });

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
                        '<a href="equipment_detail.php?id=' + encodeURIComponent(item.id) + '" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-eye"></i></a>' :
                        '';
                    var editButton = item.id !== undefined ?
                        '<button type="button" class="btn btn-sm btn-outline-warning me-2" onclick="editEquipment(' + item.id + ')"><i class="bi bi-pencil"></i></button>' :
                        '';
                    var deleteButton = item.id !== undefined ?
                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEquipment(' + item.id + ', \'' + item.name.replace(/'/g, '\\\'') + '\')"><i class="bi bi-trash"></i></button>' :
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

                    var row = '<tr>' +
                        '<td class="ps-4 fw-semibold">' + (item.id !== undefined ? item.id : '') + '</td>' +
                        '<td class="fw-semibold">' + (item.name !== undefined ? item.name : '') + '</td>' +
                        '<td>' + (item.category !== undefined ? item.category : '') + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td><i class="bi bi-geo-alt text-muted me-1"></i>' + (item.location !== undefined ? item.location : '') + '</td>' +
                        '<td class="text-center">' + imageHtml + '</td>' +
                        '<td class="text-center"><div id="' + qrContainerId + '" class="mx-auto" style="width: 60px; height: 60px;"></div></td>' +
                        '<td class="text-center pe-4">' + printButton + detailsButton + editButton + deleteButton + '</td>' +
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
                    } else {
                        showError('No data found');
                    }
                }).fail(function() {
                    showError('Unable to load equipment data.');
                });
            }

            $('#location-filter').change(loadEquipment);

            $('#add-equipment-form').submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'api/add_equipment.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false
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

            $('#edit-equipment-form').submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'api/update_equipment.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false
                }).done(function(response) {
                    if (response && response.success) {
                        $('#editEquipmentModal').modal('hide');
                        showAlert('Equipment updated successfully.', 'success');
                        loadEquipment();
                    } else {
                        showAlert(response.error || 'Unable to update equipment.', 'danger');
                    }
                }).fail(function() {
                    showAlert('Unable to update equipment.', 'danger');
                });
            });

            loadEquipment();
        });

        function editEquipment(id) {
            $.ajax({
                url: 'api/get_equipment_detail.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json'
            }).done(function(response) {
                if (response && response.success && response.data) {
                    var equipment = response.data;
                    $('#edit-equipment-id').val(equipment.id);
                    $('#edit-equipment-name').val(equipment.name);
                    $('#edit-equipment-category').val(equipment.category_id || '');
                    $('#edit-equipment-location').val(equipment.location_id || '');
                    $('#edit-equipment-return-location').val(equipment.return_location_id || '');
                    $('#editEquipmentModal').modal('show');
                } else {
                    showAlert('Unable to load equipment details.', 'danger');
                }
            }).fail(function() {
                showAlert('Unable to load equipment details.', 'danger');
            });
        }

        function deleteEquipment(id, name) {
            $('#delete-equipment-name').text(name);
            $('#confirm-delete-btn').off('click').on('click', function() {
                $.ajax({
                    url: 'api/delete_equipment.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json'
                }).done(function(response) {
                    if (response && response.success) {
                        $('#deleteEquipmentModal').modal('hide');
                        showAlert('Equipment deleted successfully.', 'success');
                        loadEquipment();
                    } else {
                        showAlert(response.error || 'Unable to delete equipment.', 'danger');
                    }
                }).fail(function() {
                    showAlert('Unable to delete equipment.', 'danger');
                });
            });
            $('#deleteEquipmentModal').modal('show');
        }
    </script>
</body>
</html>
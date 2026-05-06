<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role('Admin');
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Equipment Tracker';
$pageStyles = '<style>body { background-color: #f8f9fa; } .table-container { margin-top: 40px; } .table th, .table td { vertical-align: middle; }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <?php $brandUrl = 'equipment.php'; include __DIR__ . '/includes/topnav.php'; ?>
    <div class="container table-container">
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div>
                            <h1 class="h3 mb-2">Equipment Inventory</h1>
                            <p class="text-muted mb-0">A clean list of equipment fetched from the API.</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <a href="api/export_equipment_csv.php" class="btn btn-outline-secondary mr-2" target="_blank">Export CSV</a>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addEquipmentModal">
                                Add Equipment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Upload Equipment Image</h5>
                        <form id="upload-image-form" enctype="multipart/form-data">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-5">
                                    <label for="equipment-select">Equipment</label>
                                    <select class="form-control" id="equipment-select" name="equipment_id" required>
                                        <option value="">Select equipment</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="equipment-image">Image</label>
                                    <input type="file" class="form-control-file" id="equipment-image" name="image" accept="image/jpeg,image/png" required>
                                    <small class="form-text text-muted">JPG, JPEG, PNG only. Max 2MB.</small>
                                </div>
                                <div class="form-group col-md-2 text-right">
                                    <button type="submit" class="btn btn-success btn-block">Upload</button>
                                </div>
                            </div>
                        </form>
                        <div id="upload-preview" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="location-filter">Filter by Location</label>
                    <select class="form-control" id="location-filter">
                        <option value="">All locations</option>
                        <option value="Room 101">Room 101</option>
                        <option value="Storage">Storage</option>
                        <option value="Office">Office</option>
                        <option value="Lab">Lab</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="alert-container"></div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive shadow-sm bg-white rounded">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Category</th>
                                <th scope="col">Status</th>
                                <th scope="col">Location</th>
                                <th scope="col">Image</th>
                                <th scope="col">QR Code</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="equipment-table-body">
                            <tr>
                                <td colspan="8" class="text-center py-4">Loading equipment...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEquipmentModal" tabindex="-1" role="dialog" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="add-equipment-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEquipmentModalLabel">Add New Equipment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="equipment-name">Name</label>
                            <input type="text" class="form-control" id="equipment-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="equipment-category">Category</label>
                            <input type="text" class="form-control" id="equipment-category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="equipment-location">Location</label>
                            <select class="form-control" id="equipment-location" name="location" required>
                                <option value="">Select a location</option>
                                <option value="Room 101">Room 101</option>
                                <option value="Storage">Storage</option>
                                <option value="Office">Office</option>
                                <option value="Lab">Lab</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Equipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js" integrity="sha384-LtrjvnR4/Jdsd0O4mFsXEDX0B+e5qFQFDeUqQSRreI6G0nDZK07bqYDl38opP0xV" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-ptDqdE5MX6/Tn5C7Hbk52z7MiC+SW061MvHjXmT4r1h0YcMxGIIbVZKRyXelQ2x4n1jaSor1+WTeettqQeAq6Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            function showAlert(message, type) {
                var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
                $('#alert-container').html(alertHtml);
            }

            function renderTable(data) {
                var $tbody = $('#equipment-table-body');
                $tbody.empty();

                if (!Array.isArray(data) || data.length === 0) {
                    var emptyRow = '<tr>' +
                        '<td colspan="8" class="text-center text-muted py-4">No data found</td>' +
                        '</tr>';
                    $tbody.append(emptyRow);
                    return;
                }

                data.forEach(function(item) {
                    var qrContainerId = 'qrcode-' + item.id;
                    var printButton = item.id !== undefined ?
                        '<a href="print_qr.php?id=' + encodeURIComponent(item.id) + '" target="_blank" class="btn btn-sm btn-outline-primary mr-2">Print QR</a>' :
                        '';
                    var detailsButton = item.id !== undefined ?
                        '<a href="equipment_detail.php?id=' + encodeURIComponent(item.id) + '" class="btn btn-sm btn-outline-secondary">View</a>' :
                        '';
                    var imageHtml = '';
                    if (item.image) {
                        imageHtml = '<img src="' + encodeURI(item.image) + '" alt="Equipment image" class="img-fluid rounded" style="max-height: 80px;">';
                    } else {
                        imageHtml = '<span class="text-muted">No image</span>';
                    }
                    var row = '<tr>' +
                        '<td>' + (item.id !== undefined ? item.id : '') + '</td>' +
                        '<td>' + (item.name !== undefined ? item.name : '') + '</td>' +
                        '<td>' + (item.category !== undefined ? item.category : '') + '</td>' +
                        '<td>' + (item.status !== undefined ? item.status : '') + '</td>' +
                        '<td>' + (item.location !== undefined ? item.location : '') + '</td>' +
                        '<td class="text-center align-middle">' + imageHtml + '</td>' +
                        '<td class="text-center"><div id="' + qrContainerId + '" class="mx-auto"></div></td>' +
                        '<td class="text-center">' + printButton + detailsButton + '</td>' +
                        '</tr>';
                    $tbody.append(row);

                    if (item.id !== undefined) {
                        new QRCode(document.getElementById(qrContainerId), {
                            text: 'equipment_id=' + item.id,
                            width: 100,
                            height: 100
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
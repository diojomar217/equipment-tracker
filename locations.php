<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin']);
$user = auth_user();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/auth_helper.php';

auth_ensure_locations_table_exists($connection);

$locations = [];
$result = $connection->query('SELECT id, name, created_at FROM locations ORDER BY name');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
    $result->free();
}
$connection->close();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Location Management - Equipment Tracker';
$pageStyles = '';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'locations'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-12 col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-geo-alt text-primary me-3"></i>Location Management
                        </h1>
                        <p class="text-muted mb-0 fs-6">Manage return locations for equipment.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <h5 class="mb-0 fw-bold">Locations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($locations as $loc): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($loc['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($loc['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($loc['created_at']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editLocation(<?php echo $loc['id']; ?>, '<?php echo htmlspecialchars($loc['name']); ?>')">Edit</button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLocation(<?php echo $loc['id']; ?>, '<?php echo htmlspecialchars($loc['name']); ?>')">Delete</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <h5 class="mb-0 fw-bold">Add New Location</h5>
                            </div>
                            <div class="card-body">
                                <form id="add-location-form">
                                    <div class="row">
                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="location-name" class="form-label">Location Name</label>
                                            <input type="text" class="form-control form-control-lg" id="location-name" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg">Add Location</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Location Modal -->
                <div class="modal fade" id="editLocationModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Location</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-location-form">
                                    <input type="hidden" id="edit-location-id">
                                    <div class="mb-3">
                                        <label for="edit-location-name" class="form-label">Location Name</label>
                                        <input type="text" class="form-control" id="edit-location-name" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveEditLocation()">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Location Modal -->
                <div class="modal fade" id="deleteLocationModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Location</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete location <strong id="delete-location-name"></strong>? This action cannot be undone.</p>
                                <input type="hidden" id="delete-location-id">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" onclick="confirmDeleteLocation()">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        $('#add-location-form').submit(function(e) {
            e.preventDefault();
            var name = $('#location-name').val().trim();

            if (!name) {
                alert('Location name is required.');
                return;
            }

            $.ajax({
                url: 'api/add_location.php',
                type: 'POST',
                dataType: 'json',
                data: { name: name }
            }).done(function(response) {
                if (response.success) {
                    alert('Location added successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to add location.');
            });
        });

        function editLocation(id, name) {
            $('#edit-location-id').val(id);
            $('#edit-location-name').val(name);
            $('#editLocationModal').modal('show');
        }

        function saveEditLocation() {
            var id = $('#edit-location-id').val();
            var name = $('#edit-location-name').val().trim();

            if (!name) {
                alert('Location name is required.');
                return;
            }

            $.ajax({
                url: 'api/edit_location.php',
                type: 'POST',
                dataType: 'json',
                data: { id: id, name: name }
            }).done(function(response) {
                if (response.success) {
                    alert('Location updated successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to update location.');
            });
        }

        function deleteLocation(id, name) {
            $('#delete-location-id').val(id);
            $('#delete-location-name').text(name);
            $('#deleteLocationModal').modal('show');
        }

        function confirmDeleteLocation() {
            var id = $('#delete-location-id').val();

            $.ajax({
                url: 'api/delete_location.php',
                type: 'POST',
                dataType: 'json',
                data: { id: id }
            }).done(function(response) {
                if (response.success) {
                    alert('Location deleted successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to delete location.');
            });
        }
    </script>
</body>
</html>
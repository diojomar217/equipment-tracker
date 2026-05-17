<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin']);
$user = auth_user();
require_once __DIR__ . '/config/db.php';

$users = [];
$result = $connection->query('SELECT id, username, name, role, created_at FROM users ORDER BY created_at DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
}
$connection->close();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'User Management - Equipment Tracker';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'users'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-12 col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-people text-primary me-3"></i>User Management
                        </h1>
                        <p class="text-muted mb-0 fs-6">Manage users and their roles.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <h5 class="mb-0 fw-bold">Users</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $u): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($u['id']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($u['name'] ?: $u['username']); ?>
                                                        <div class="text-muted small">@<?php echo htmlspecialchars($u['username']); ?></div>
                                                    </td>
                                                    <td><span class="badge bg-<?php echo $u['role'] === 'Admin' ? 'primary' : 'secondary'; ?>"><?php echo htmlspecialchars($u['role']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['name']); ?>', '<?php echo htmlspecialchars($u['username']); ?>', '<?php echo htmlspecialchars($u['role']); ?>')">Edit</button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['name'] ?: $u['username']); ?>')">Delete</button>
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
                                <h5 class="mb-0 fw-bold">Add New User</h5>
                            </div>
                            <div class="card-body">
                                <form id="add-user-form">
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control form-control-lg" id="name" required>
                                        </div>
                                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control form-control-lg" id="username" required>
                                        </div>
                                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control form-control-lg" id="password" required>
                                        </div>
                                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select form-select-lg" id="role" required>
                                                <option value="">Select Role</option>
                                                <option value="Staff">Staff</option>
                                                <option value="Admin">Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add User</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit User Modal -->
                <div class="modal fade" id="editUserModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-user-form">
                                    <input type="hidden" id="edit-user-id">
                                    <div class="mb-3">
                                        <label for="edit-name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="edit-name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="edit-username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-password" class="form-label">New Password (leave blank to keep current)</label>
                                        <input type="password" class="form-control" id="edit-password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-role" class="form-label">Role</label>
                                        <select class="form-select" id="edit-role" required>
                                            <option value="Staff">Staff</option>
                                            <option value="Admin">Admin</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveEdit()">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete User Modal -->
                <div class="modal fade" id="deleteUserModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete user <strong id="delete-username"></strong>? This action cannot be undone.</p>
                                <input type="hidden" id="delete-user-id">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
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
        $('#add-user-form').submit(function(e) {
            e.preventDefault();
            var name = $('#name').val().trim();
            var username = $('#username').val().trim();
            var password = $('#password').val().trim();
            var role = $('#role').val();

            if (!name || !username || !password || !role) {
                alert('All fields are required.');
                return;
            }

            $.ajax({
                url: 'api/add_user.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    name: name,
                    username: username,
                    password: password,
                    role: role
                }
            }).done(function(response) {
                if (response.success) {
                    alert('User added successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to add user.');
            });
        });

        function editUser(id, name, username, role) {
            $('#edit-user-id').val(id);
            $('#edit-name').val(name);
            $('#edit-username').val(username);
            $('#edit-role').val(role);
            $('#edit-password').val('');
            $('#editUserModal').modal('show');
        }

        function saveEdit() {
            var id = $('#edit-user-id').val();
            var name = $('#edit-name').val().trim();
            var username = $('#edit-username').val().trim();
            var password = $('#edit-password').val().trim();
            var role = $('#edit-role').val();

            if (!name || !username || !role) {
                alert('Name, username and role are required.');
                return;
            }

            $.ajax({
                url: 'api/edit_user.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    name: name,
                    username: username,
                    password: password,
                    role: role
                }
            }).done(function(response) {
                if (response.success) {
                    alert('User updated successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to update user.');
            });
        }

        function deleteUser(id, username) {
            $('#delete-user-id').val(id);
            $('#delete-username').text(username);
            $('#deleteUserModal').modal('show');
        }

        function confirmDelete() {
            var id = $('#delete-user-id').val();

            $.ajax({
                url: 'api/delete_user.php',
                type: 'POST',
                dataType: 'json',
                data: { id: id }
            }).done(function(response) {
                if (response.success) {
                    alert('User deleted successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to delete user.');
            });
        }
    </script>
</body>
</html>
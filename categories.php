<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role(['Admin']);
$user = auth_user();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/auth_helper.php';

auth_ensure_categories_table_exists($connection);

$categories = [];
$result = $connection->query('SELECT id, name, created_at FROM categories ORDER BY name');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $result->free();
}
$connection->close();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Category Management - Equipment Tracker';
$pageStyles = '';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $activePage = 'categories'; include __DIR__ . '/includes/sidebar.php'; ?>
            <main class="col-12 col-md-9 col-lg-10 content-area px-4">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-2">
                            <i class="bi bi-tags text-primary me-3"></i>Category Management
                        </h1>
                        <p class="text-muted mb-0 fs-6">Manage equipment categories.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white border-0 pt-4 pb-3">
                                <h5 class="mb-0 fw-bold">Categories</h5>
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
                                            <?php foreach ($categories as $cat): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($cat['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($cat['created_at']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>')">Edit</button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>')">Delete</button>
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
                                <h5 class="mb-0 fw-bold">Add New Category</h5>
                            </div>
                            <div class="card-body">
                                <form id="add-category-form">
                                    <div class="row">
                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="category-name" class="form-label">Category Name</label>
                                            <input type="text" class="form-control form-control-lg" id="category-name" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg">Add Category</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Category Modal -->
                <div class="modal fade" id="editCategoryModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-category-form">
                                    <input type="hidden" id="edit-category-id">
                                    <div class="mb-3">
                                        <label for="edit-category-name" class="form-label">Category Name</label>
                                        <input type="text" class="form-control" id="edit-category-name" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveEditCategory()">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Category Modal -->
                <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete category <strong id="delete-category-name"></strong>? This action cannot be undone.</p>
                                <input type="hidden" id="delete-category-id">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" onclick="confirmDeleteCategory()">Delete</button>
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
        $('#add-category-form').submit(function(e) {
            e.preventDefault();
            var name = $('#category-name').val().trim();

            if (!name) {
                alert('Category name is required.');
                return;
            }

            $.ajax({
                url: 'api/add_category.php',
                type: 'POST',
                dataType: 'json',
                data: { name: name }
            }).done(function(response) {
                if (response.success) {
                    alert('Category added successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to add category.');
            });
        });

        function editCategory(id, name) {
            $('#edit-category-id').val(id);
            $('#edit-category-name').val(name);
            $('#editCategoryModal').modal('show');
        }

        function saveEditCategory() {
            var id = $('#edit-category-id').val();
            var name = $('#edit-category-name').val().trim();

            if (!name) {
                alert('Category name is required.');
                return;
            }

            $.ajax({
                url: 'api/edit_category.php',
                type: 'POST',
                dataType: 'json',
                data: { id: id, name: name }
            }).done(function(response) {
                if (response.success) {
                    alert('Category updated successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to update category.');
            });
        }

        function deleteCategory(id, name) {
            $('#delete-category-id').val(id);
            $('#delete-category-name').text(name);
            $('#deleteCategoryModal').modal('show');
        }

        function confirmDeleteCategory() {
            var id = $('#delete-category-id').val();

            $.ajax({
                url: 'api/delete_category.php',
                type: 'POST',
                dataType: 'json',
                data: { id: id }
            }).done(function(response) {
                if (response.success) {
                    alert('Category deleted successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }).fail(function() {
                alert('Unable to delete category.');
            });
        }
    </script>
</body>
</html>
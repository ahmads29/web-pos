<?php
$pageTitle = 'Categories Management';
require_once '../includes/header.php';
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                
                $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ss", $name, $description);
                mysqli_stmt_execute($stmt);
                break;

            case 'edit':
                $id = intval($_POST['id']);
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);

                $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $id);
                mysqli_stmt_execute($stmt);
                break;

            case 'delete':
                $id = intval($_POST['id']);
                mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
                break;
        }
    }
}

// Get all categories
$categories = [];
$result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
?>

<!-- Add Category Button -->
<div class="mb-4">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-circle"></i> Add New Category
    </button>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-category" 
                                    data-category='<?php echo json_encode($category); ?>'
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCategoryModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-category" 
                                    data-id="<?php echo $category['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
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
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
document.querySelectorAll('.edit-category').forEach(button => {
    button.addEventListener('click', function() {
        const category = JSON.parse(this.dataset.category);
        document.getElementById('edit_id').value = category.id;
        document.getElementById('edit_name').value = category.name;
        document.getElementById('edit_description').value = category.description;
    });
});

document.querySelectorAll('.delete-category').forEach(button => {
    button.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this category?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type='hidden' name='action' value='delete'>
                <input type='hidden' name='id' value='${this.dataset.id}'>
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
});
</script>
EOT;
require_once '../includes/footer.php';
?> 
<?php
$pageTitle = 'Products Management';
require_once '../includes/header.php';
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $price = floatval($_POST['price']);
                $quantity = intval($_POST['quantity']);
                $category_id = intval($_POST['category_id']);
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "../assets/images/products/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $filename = time() . '_' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename);
                    $image_path = 'assets/images/products/' . $filename;
                }

                $sql = "INSERT INTO products (name, description, price, quantity, category_id, image_path) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssdiis", $name, $description, $price, $quantity, $category_id, $image_path);
                mysqli_stmt_execute($stmt);
                break;

            case 'edit':
                $id = intval($_POST['id']);
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $price = floatval($_POST['price']);
                $quantity = intval($_POST['quantity']);
                $category_id = intval($_POST['category_id']);

                $sql = "UPDATE products SET name = ?, description = ?, price = ?, quantity = ?, category_id = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssdiii", $name, $description, $price, $quantity, $category_id, $id);
                mysqli_stmt_execute($stmt);
                break;

            case 'delete':
                $id = intval($_POST['id']);
                mysqli_query($conn, "DELETE FROM products WHERE id = $id");
                break;
        }
    }
}

// Get all products with category names
$products = [];
$result = mysqli_query($conn, "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.name
");

while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// Get all categories for the dropdown
$categories = [];
$result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
?>

<!-- Add Product Button -->
<div class="mb-4">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-circle"></i> Add New Product
    </button>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary" style="width: 50px; height: 50px;"></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['quantity']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-product" 
                                    data-product='<?php echo json_encode($product); ?>'
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProductModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-product" 
                                    data-id="<?php echo $product['id']; ?>">
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
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
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-control" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image" id="add_image" accept="image/*">
                        <img id="add_image_preview" src="#" alt="Image Preview" style="display:none; max-width:100px; margin-top:10px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
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
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-control" name="category_id" id="edit_category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" id="edit_quantity" required>
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
document.querySelectorAll('.edit-product').forEach(button => {
    button.addEventListener('click', function() {
        const product = JSON.parse(this.dataset.product);
        document.getElementById('edit_id').value = product.id;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_description').value = product.description;
        document.getElementById('edit_category_id').value = product.category_id;
        document.getElementById('edit_price').value = product.price;
        document.getElementById('edit_quantity').value = product.quantity;
    });
});

document.querySelectorAll('.delete-product').forEach(button => {
    button.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this product?')) {
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

// Add Product image preview
const addImageInput = document.getElementById('add_image');
const addImagePreview = document.getElementById('add_image_preview');
if (addImageInput) {
    addImageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            addImagePreview.src = URL.createObjectURL(this.files[0]);
            addImagePreview.style.display = 'block';
        } else {
            addImagePreview.style.display = 'none';
        }
    });
}
</script>
EOT;
require_once '../includes/footer.php';
?> 
<?php
require_once 'includes/header.php';

// Get all categories
$categories = [];
$result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// Get all products
$products = [];
$result = mysqli_query($conn, "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.quantity > 0 
    ORDER BY p.name
");
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Products Section -->
        <div class="col-md-8">
            <!-- Category Filter -->
            <div class="mb-3">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-category="all">All</button>
                    <?php foreach ($categories as $category): ?>
                        <button type="button" class="btn btn-outline-primary" 
                                data-category="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mb-3">
                <input type="text" class="form-control" id="productSearch" 
                       placeholder="Search products...">
            </div>

            <!-- Products Grid -->
            <div class="row" id="productsGrid">
                <?php foreach ($products as $product): ?>
                    <div class="col-4 col-sm-4 col-md-4 mb-3 product-item" 
                         data-category="<?php echo $product['category_id']; ?>"
                         data-name="<?php echo strtolower($product['name']); ?>">
                        <div class="card h-100">
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary" 
                                     style="height: 150px;"></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                    </small>
                                </p>
                                <p class="card-text">$<?php echo number_format($product['price'], 2); ?></p>
                                <button class="btn btn-primary w-100 add-to-cart" 
                                        data-product='<?php echo json_encode($product); ?>'>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shopping Cart</h5>
                </div>
                <div class="card-body">
                    <div id="cartItems">
                        <!-- Cart items will be added here dynamically -->
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 align-items-center">
                        <span>Delivery:</span>
                        <input type="number" class="form-control form-control-sm w-50" id="deliveryCost" value="0" min="0" step="0.01">
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong id="total">$0.00</strong>
                    </div>
                    <button class="btn btn-success w-100 mb-2" id="checkoutBtn" disabled>
                        Checkout
                    </button>
                    <button class="btn btn-danger w-100" id="clearCartBtn" disabled>
                        Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" class="form-control" id="amountPaid" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Change</label>
                    <input type="text" class="form-control" id="changeAmount" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="completeSaleBtn">Complete Sale</button>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
document.addEventListener('DOMContentLoaded', function() {
let cart = [];
// Filter products by category
    document.querySelectorAll('[data-category]').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            document.querySelectorAll('.product-item').forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
// Search products
    document.getElementById('productSearch').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            if (item.dataset.name.includes(search)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
// Add to cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            let product;
            try {
                product = JSON.parse(this.dataset.product);
            } catch (e) {
                alert('Error parsing product data: ' + e.message);
                return;
            }
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    quantity: 1
                });
            }
            updateCart();
        });
    });
// Update cart display
    function updateCart() {
        const cartItems = document.getElementById('cartItems');
        const subtotal = document.getElementById('subtotal');
        const total = document.getElementById('total');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const clearCartBtn = document.getElementById('clearCartBtn');
        const deliveryCostInput = document.getElementById('deliveryCost');
        cartItems.innerHTML = '';
        let subtotalAmount = 0;
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotalAmount += itemTotal;
            cartItems.innerHTML += `
                <div class='d-flex justify-content-between align-items-center mb-2'>
                    <div>
                        <h6 class='mb-0'>${item.name}</h6>
                        <small class='text-muted'>$${item.price.toFixed(2)} x ${item.quantity}</small>
                    </div>
                    <div class='d-flex align-items-center'>
                        <span class='me-2'>$${itemTotal.toFixed(2)}</span>
                        <button class='btn btn-sm btn-danger remove-item' data-id='${item.id}'>
                            <i class='bi bi-trash'></i>
                        </button>
                    </div>
                </div>
            `;
        });
        let deliveryCost = parseFloat(deliveryCostInput.value) || 0;
        const totalAmount = subtotalAmount + deliveryCost;
        subtotal.textContent = `$${subtotalAmount.toFixed(2)}`;
        total.textContent = `$${totalAmount.toFixed(2)}`;
        checkoutBtn.disabled = cart.length === 0;
        clearCartBtn.disabled = cart.length === 0;
        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                cart = cart.filter(item => item.id !== id);
                updateCart();
            });
        });
    }
// Clear cart
    document.getElementById('clearCartBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the cart?')) {
            cart = [];
            updateCart();
        }
    });
// Checkout
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
        modal.show();
    });
// Handle amount paid input
    document.getElementById('amountPaid').addEventListener('input', function() {
        const amountPaid = parseFloat(this.value) || 0;
        const total = parseFloat(document.getElementById('total').textContent.replace('$', ''));
        const change = amountPaid - total;
        document.getElementById('changeAmount').value = `$${change.toFixed(2)}`;
        document.getElementById('completeSaleBtn').disabled = change < 0;
    });
// Complete sale
    document.getElementById('completeSaleBtn').addEventListener('click', function() {
        const saleData = {
            items: cart,
            total: parseFloat(document.getElementById('total').textContent.replace('$', '')),
            amount_paid: parseFloat(document.getElementById('amountPaid').value),
            change: parseFloat(document.getElementById('changeAmount').value.replace('$', '')),
            delivery_cost: parseFloat(document.getElementById('deliveryCost').value) || 0
        };
        // Send sale data to server
        fetch('api/complete_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(saleData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Print receipt
                window.open('print_receipt.php?sale_id=' + data.sale_id, '_blank');
                // Clear cart and close modal
                cart = [];
                updateCart();
                bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
                // Show success message
                alert('Sale completed successfully!');
            } else {
                alert('Error completing sale: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error completing sale: ' + error.message);
        });
    });
// Update total when delivery cost changes
    document.getElementById('deliveryCost').addEventListener('input', function() {
        updateCart();
    });
});
</script>
EOT;
require_once 'includes/footer.php';
?> 
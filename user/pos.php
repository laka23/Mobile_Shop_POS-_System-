<?php 

require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$branch_id = $_SESSION['branch_id'];
$message = '';
$error = '';
$success_sale_id = null;
$sale_details = null;


if(isset($_POST['complete_sale'])) {
    $cart_items = json_decode($_POST['cart_data'], true);
    $payment_method = $_POST['payment_method'];
    $total_amount = floatval($_POST['total_amount']);
    
    if(!empty($cart_items) && $total_amount > 0) {
    
        $sale_number = 'SAL' . date('Ymd') . rand(1000, 9999);
        
        
        $stmt = $conn->prepare("INSERT INTO sales (sale_number, branch_id, user_id, total_amount, payment_method) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siisd", $sale_number, $branch_id, $_SESSION['user_id'], $total_amount, $payment_method);
        
        if($stmt->execute()) {
            $sale_id = $conn->insert_id;
            
          
            foreach($cart_items as $item) {
                $product_id = isset($item['product_id']) ? intval($item['product_id']) : 0;
                $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                $price = isset($item['price']) ? floatval($item['price']) : 0;
                $discount = isset($item['discount']) ? floatval($item['discount']) : 0;
                $subtotal = isset($item['subtotal']) ? floatval($item['subtotal']) : 0;
                
             
                $item_name = isset($item['name']) ? $item['name'] : 'Manual Item';
                
                if($product_id == 0) {
                    // Manual entry - store with name
                    $stmt2 = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, manual_item_name, quantity, price, discount, subtotal) VALUES (?, 0, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param("isiddd", $sale_id, $item_name, $quantity, $price, $discount, $subtotal);
                    $stmt2->execute();
                    $stmt2->close();
                } else {
                    // Regular product
                    $stmt2 = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, discount, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param("iiiddd", $sale_id, $product_id, $quantity, $price, $discount, $subtotal);
                    $stmt2->execute();
                    $stmt2->close();
                    
                    // Update stock
                    $conn->query("UPDATE stock SET quantity = quantity - $quantity WHERE product_id=$product_id AND branch_id=$branch_id");
                }
            }
            
            $stmt->close();
            
        
            $sale_details = $conn->query("SELECT s.*, b.name as branch_name, b.branch_code, b.location, u.full_name as cashier 
                                         FROM sales s 
                                         JOIN branches b ON s.branch_id = b.id 
                                         JOIN users u ON s.user_id = u.id 
                                         WHERE s.id = $sale_id")->fetch_assoc();
            
            $success_sale_id = $sale_id;
            $message = "Sale completed successfully! Sale #: $sale_number";
        } else {
            $error = "Error creating sale: " . $conn->error;
        }
    } else {
        $error = "Invalid cart data or total amount!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    .search-results {
        margin-top: 15px;
        max-height: 400px;
        overflow-y: auto;
    }
    .product-card {
        background: var(--dark-light);
        border: 1px solid var(--border);
        padding: 15px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }
    .product-card:hover {
        background: var(--dark);
        border-color: var(--primary);
        transform: translateY(-2px);
    }
    .manual-entry-form {
        background: var(--dark);
        padding: 20px;
        border-radius: 8px;
        border: 2px dashed var(--primary);
        margin-bottom: 20px;
    }
    .cart-table {
        width: 100%;
        margin-bottom: 20px;
    }
    .cart-table th, .cart-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    .qty-input, .discount-input {
        width: 70px;
        padding: 5px;
        background: var(--dark);
        border: 1px solid var(--border);
        color: var(--text);
        border-radius: 4px;
    }
    .cart-total {
        text-align: right;
        padding: 15px 0;
        border-top: 2px solid var(--primary);
        margin-top: 10px;
    }
    .pos-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .pos-container {
            grid-template-columns: 1fr;
        }
    }
    .manual-item-badge {
        background: var(--info);
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        margin-left: 5px;
    }
    
    /* Invoice Styles */
    .invoice-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        padding: 20px;
    }
    .invoice-container {
        background: white;
        max-width: 800px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    .invoice-content {
        padding: 40px;
        font-family: 'Courier New', monospace;
        color: #000;
    }
    .invoice-header {
        text-align: center;
        border-bottom: 2px dashed #000;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    .invoice-header h1 {
        font-size: 24px;
        margin: 0 0 10px 0;
        color: #000;
    }
    .invoice-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .invoice-table th,
    .invoice-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        color: #000;
    }
    .invoice-table th {
        background: #f5f5f5;
        font-weight: bold;
        border-top: 2px solid #000;
        border-bottom: 2px solid #000;
    }
    .invoice-totals {
        text-align: right;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #000;
    }
    .invoice-total-row {
        display: flex;
        justify-content: flex-end;
        gap: 20px;
        margin: 10px 0;
        font-size: 16px;
    }
    .invoice-grand-total {
        font-size: 20px;
        font-weight: bold;
        border-top: 2px solid #000;
        padding-top: 15px;
        margin-top: 15px;
    }
    .invoice-footer {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px dashed #000;
        font-size: 12px;
    }
    .invoice-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        padding: 20px;
        background: #f5f5f5;
        border-top: 1px solid #ddd;
    }
    .manual-item-badge-invoice {
        background: #3b82f6;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        margin-left: 5px;
    }
    @media print {
        .invoice-modal {
            position: static;
            background: white;
        }
        .invoice-actions {
            display: none;
        }
        .invoice-container {
            max-height: none;
            box-shadow: none;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/user_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>🛒 Point of Sale (POS)</h1>
                <button onclick="toggleManualEntry()" class="btn btn-info">📝 Manual Bill Entry</button>
            </div>
            
            <?php if($message && !$success_sale_id): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Manual Entry Form (Hidden by default) -->
            <div id="manualEntryForm" class="manual-entry-form" style="display:none;">
                <h3>📝 Manual Bill Entry</h3>
                <p style="color: var(--text-muted); margin-bottom: 15px;">Add custom items/services that are not in stock</p>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Product/Service Name:</label>
                        <input type="text" id="manualProductName" placeholder="e.g. Screen Repair" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Quantity:</label>
                        <input type="number" id="manualQuantity" value="1" min="1" class="form-control">
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Description (Optional):</label>
                        <input type="text" id="manualDescription" placeholder="Additional details" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Price (Rs):</label>
                        <input type="number" id="manualPrice" placeholder="0.00" step="0.01" min="0" class="form-control">
                    </div>
                </div>
                <button onclick="addManualItem()" class="btn btn-success">✔️ Add to Cart</button>
                <button onclick="toggleManualEntry()" class="btn btn-secondary">Cancel</button>
            </div>
            
            <div class="pos-container">
                <!-- Search Section -->
                <div class="pos-search-section">
                    <div class="card">
                        <div class="card-header">
                            <h2>🔍 Search Products</h2>
                        </div>
                        <div class="card-body">
                            <div class="search-bar">
                                <input type="text" id="searchInput" placeholder="Type product name or barcode..." class="form-control" autocomplete="off">
                            </div>
                            <div id="searchResults" class="search-results"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Section -->
                <div class="pos-cart-section">
                    <div class="card">
                        <div class="card-header">
                            <h2>🛒 Shopping Cart</h2>
                            <button onclick="clearCart()" class="btn btn-sm btn-danger">Clear Cart</button>
                        </div>
                        <div class="card-body">
                            <table class="cart-table" id="cartTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Disc%</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cartItems">
                                    <tr id="emptyCart">
                                        <td colspan="6" class="text-center" style="padding: 30px; color: var(--text-muted);">
                                            Cart is empty. Search for products or add manual items.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="cart-total">
                                <h3>Total: <span id="cartTotal"><?php echo CURRENCY; ?>0.00</span></h3>
                            </div>
                            
                            <form method="POST" action="" id="checkoutForm" onsubmit="return validateCheckout()">
                                <input type="hidden" name="cart_data" id="cartData">
                                <input type="hidden" name="total_amount" id="totalAmount">
                                
                                <div class="form-group">
                                    <label>Payment Method:</label>
                                    <select name="payment_method" required class="form-control">
                                        <option value="cash">💵 Cash</option>
                                        <option value="card">💳 Card</option>
                                        <option value="bank_transfer">🏦 Bank Transfer</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="complete_sale" class="btn btn-success btn-block" id="checkoutBtn" disabled>
                                    ✔️ Complete Sale & Print Bill
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Invoice Modal -->
    <?php if($success_sale_id && $sale_details): 
        // Get sale items
        $items_result = $conn->query("SELECT si.*, 
                                      COALESCE(si.manual_item_name, p.name, 'Unknown Item') as product_name,
                                      si.product_id
                                      FROM sale_items si 
                                      LEFT JOIN products p ON si.product_id = p.id AND si.product_id != 0
                                      WHERE si.sale_id = $success_sale_id");
    ?>
    <div class="invoice-modal" id="invoiceModal">
        <div class="invoice-container">
            <div class="invoice-content" id="invoiceContent">
                <div class="invoice-header">
                    <h1>📱 <?php echo SITE_NAME; ?></h1>
                    <p><?php echo $sale_details['branch_name']; ?> (<?php echo $sale_details['branch_code']; ?>)</p>
                    <p><?php echo $sale_details['location']; ?></p>
                    <p style="margin-top: 15px; font-weight: bold;">SALES INVOICE</p>
                </div>
                
                <div class="invoice-info">
                    <div><strong>Invoice No:</strong> <?php echo $sale_details['sale_number']; ?></div>
                    <div><strong>Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($sale_details['sale_date'])); ?></div>
                    <div><strong>Cashier:</strong> <?php echo $sale_details['cashier']; ?></div>
                    <div><strong>Payment:</strong> <?php echo strtoupper($sale_details['payment_method']); ?></div>
                </div>
                
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Disc%</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        $total_discount = 0;
                        while($item = $items_result->fetch_assoc()): 
                            $item_total = $item['subtotal'];
                            $original_total = $item['price'] * $item['quantity'];
                            $discount_amount = $original_total - $item_total;
                            $subtotal += $original_total;
                            $total_discount += $discount_amount;
                            $is_manual = ($item['product_id'] == 0);
                        ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($item['product_name']); ?>
                                <?php if($is_manual): ?>
                                    <span class="manual-item-badge-invoice">MANUAL</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo CURRENCY . number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['discount'], 2); ?>%</td>
                            <td><?php echo CURRENCY . number_format($item_total, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="invoice-totals">
                    <div class="invoice-total-row">
                        <span>Subtotal:</span>
                        <span><?php echo CURRENCY . number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if($total_discount > 0): ?>
                    <div class="invoice-total-row">
                        <span>Discount:</span>
                        <span>- <?php echo CURRENCY . number_format($total_discount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="invoice-total-row invoice-grand-total">
                        <span>GRAND TOTAL:</span>
                        <span><?php echo CURRENCY . number_format($sale_details['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div class="invoice-footer">
                    <p>Thank you for your business!</p>
                    <p>Please come again</p>
                    <p style="margin-top: 15px;">Powered by <?php echo _SITE_NAME; ?></p>
                </div>
            </div>
            
            <div class="invoice-actions">
                <button onclick="printInvoice()" class="btn btn-primary">🖨️ Print Invoice</button>
                <button onclick="closeInvoice()" class="btn btn-success">✅ New Sale</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="../assets/js/main.js"></script>
    <script>
    let cart = [];
    let searchTimeout = null;
    
    // Invoice functions
    function printInvoice() {
        window.print();
    }
    
    function closeInvoice() {
        window.location.href = 'pos.php';
    }
    
    // Real-time search as user types
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if(query.length < 2) {
            document.getElementById('searchResults').innerHTML = '';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchProduct(query);
        }, 300);
    });
    
    async function searchProduct(query) {
        try {
            const response = await fetch('ajax/search_product.php?q=' + encodeURIComponent(query) + '&branch_id=<?php echo $branch_id; ?>');
            const products = await response.json();
            displaySearchResults(products);
        } catch(error) {
            console.error('Search error:', error);
            document.getElementById('searchResults').innerHTML = '<div class="alert alert-error">Error searching products</div>';
        }
    }
    
    function displaySearchResults(products) {
        const resultsDiv = document.getElementById('searchResults');
        
        if(products.length === 0) {
            resultsDiv.innerHTML = '<div class="alert alert-info">No products found</div>';
            return;
        }
        
        let html = '';
        products.forEach(product => {
            const productJson = JSON.stringify(product).replace(/'/g, "&#39;");
            html += `
            <div class="product-card" onclick='addToCart(${productJson})'>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="margin: 0 0 5px 0;">${product.name}</h4>
                        <p style="margin: 0; color: var(--text-muted); font-size: 12px;">
                            Stock: ${product.quantity} | ${product.barcode}
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <strong style="color: var(--primary); font-size: 18px;"><?php echo CURRENCY; ?>${parseFloat(product.price).toFixed(2)}</strong>
                        ${product.discount > 0 ? `<p style="margin: 0; color: var(--success); font-size: 12px;">${product.discount}% OFF</p>` : ''}
                    </div>
                </div>
            </div>
            `;
        });
        
        resultsDiv.innerHTML = html;
    }
    
    function toggleManualEntry() {
        const form = document.getElementById('manualEntryForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        
        if(form.style.display === 'block') {
            document.getElementById('manualProductName').focus();
        }
    }
    
    function addManualItem() {
        const name = document.getElementById('manualProductName').value.trim();
        const quantity = parseInt(document.getElementById('manualQuantity').value) || 1;
        const description = document.getElementById('manualDescription').value.trim();
        const price = parseFloat(document.getElementById('manualPrice').value);
        
        if(!name) {
            alert('Please enter product/service name!');
            return;
        }
        
        if(!price || price <= 0) {
            alert('Please enter a valid price!');
            return;
        }
        
        const fullName = name + (description ? ` (${description})` : '');
        
        const manualProduct = {
            product_id: 0,
            name: fullName,
            quantity: quantity,
            price: price,
            discount: 0,
            max_quantity: 9999
        };
        
        cart.push(manualProduct);
        updateCart();
        
        // Clear form
        document.getElementById('manualProductName').value = '';
        document.getElementById('manualQuantity').value = '1';
        document.getElementById('manualDescription').value = '';
        document.getElementById('manualPrice').value = '';
        
        toggleManualEntry();
    }
    
    function addToCart(product) {
        const existing = cart.find(item => item.product_id === product.id && item.product_id !== 0);
        
        if(existing) {
            if(existing.quantity < product.quantity) {
                existing.quantity++;
            } else {
                alert('Not enough stock! Available: ' + product.quantity);
                return;
            }
        } else {
            cart.push({
                product_id: product.id,
                name: product.name,
                quantity: 1,
                price: parseFloat(product.price),
                discount: parseFloat(product.discount) || 0,
                max_quantity: parseInt(product.quantity)
            });
        }
        
        updateCart();
        document.getElementById('searchInput').value = '';
        document.getElementById('searchResults').innerHTML = '';
    }
    
    function removeFromCart(index) {
        if(confirm('Remove this item from cart?')) {
            cart.splice(index, 1);
            updateCart();
        }
    }
    
    function updateQuantity(index, newQty) {
        newQty = parseInt(newQty);
        
        if(newQty < 1) {
            alert('Quantity must be at least 1');
            return;
        }
        
        if(cart[index].product_id !== 0 && newQty > cart[index].max_quantity) {
            alert('Not enough stock! Available: ' + cart[index].max_quantity);
            return;
        }
        
        cart[index].quantity = newQty;
        updateCart();
    }
    
    function updateDiscount(index, discount) {
        discount = parseFloat(discount);
        
        if(discount < 0 || discount > 100) {
            alert('Discount must be between 0 and 100');
            return;
        }
        
        cart[index].discount = discount;
        updateCart();
    }
    
    function updateCart() {
        const tbody = document.getElementById('cartItems');
        const emptyRow = document.getElementById('emptyCart');
        
        if(cart.length === 0) {
            emptyRow.style.display = 'table-row';
            document.getElementById('checkoutBtn').disabled = true;
        } else {
            emptyRow.style.display = 'none';
            document.getElementById('checkoutBtn').disabled = false;
        }
        
        let html = '';
        let total = 0;
        
        cart.forEach((item, index) => {
            const price = item.price;
            const discountAmount = (price * item.discount / 100);
            const finalPrice = price - discountAmount;
            const subtotal = finalPrice * item.quantity;
            total += subtotal;
            
            item.subtotal = subtotal;
            
            const isManual = item.product_id === 0;
            
            html += `
            <tr style="${isManual ? 'background: rgba(59, 130, 246, 0.1);' : ''}">
                <td>
                    ${item.name}
                    ${isManual ? '<span class="manual-item-badge">Manual</span>' : ''}
                </td>
                <td>
                    <input type="number" value="${item.quantity}" min="1" 
                           ${!isManual ? `max="${item.max_quantity}"` : ''} 
                           onchange="updateQuantity(${index}, this.value)" 
                           class="qty-input">
                </td>
                <td><?php echo CURRENCY; ?>${price.toFixed(2)}</td>
                <td>
                    <input type="number" value="${item.discount}" min="0" max="100" step="0.01" 
                           onchange="updateDiscount(${index}, this.value)" 
                           class="discount-input">
                </td>
                <td><strong><?php echo CURRENCY; ?>${subtotal.toFixed(2)}</strong></td>
                <td>
                    <button onclick="removeFromCart(${index})" class="btn btn-sm btn-danger">✖</button>
                </td>
            </tr>
            `;
        });
        
        tbody.innerHTML = html + emptyRow.outerHTML;
        
        document.getElementById('cartTotal').textContent = '<?php echo CURRENCY; ?>' + total.toFixed(2);
        document.getElementById('cartData').value = JSON.stringify(cart);
        document.getElementById('totalAmount').value = total.toFixed(2);
    }
    
    function clearCart() {
        if(confirm('Clear all items from cart?')) {
            cart = [];
            updateCart();
        }
    }
    
    function validateCheckout() {
        if(cart.length === 0) {
            alert('Cart is empty!');
            return false;
        }
        
        const total = parseFloat(document.getElementById('totalAmount').value);
        if(total <= 0) {
            alert('Invalid total amount!');
            return false;
        }
        
        return confirm('Complete sale and show invoice?');
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if(e.key === 'F1') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }
        if(e.key === 'F2') {
            e.preventDefault();
            toggleManualEntry();
        }
    });
    </script>
</body>
</html>
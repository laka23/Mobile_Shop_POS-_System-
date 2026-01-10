<?php

require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$branch_id = $_SESSION['branch_id'];
$message = '';
$error = '';


if(isset($_POST['add_product'])) {
    $product_name = trim($_POST['product_name']);
    $category_name = trim($_POST['category_name']);
    $new_category = trim($_POST['new_category']);
    $description = trim($_POST['product_description']);
    
   
    $category_id = 0;
    
    if(!empty($new_category)) {
   
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_category);
        if($stmt->execute()) {
            $category_id = $stmt->insert_id;
        }
        $stmt->close();
    } elseif(!empty($category_name)) {
        $category_id = intval($category_name);
    }
    
    if(!empty($product_name) && $category_id > 0) {
        $stmt = $conn->prepare("INSERT INTO products (name, category_id, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $product_name, $category_id, $description);
        
        if($stmt->execute()) {
            $message = "Product '$product_name' added successfully!";
        } else {
            $error = "Error adding product: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Product name and category are required!";
    }
}


if(isset($_POST['add_stock_qty'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $discount = floatval($_POST['discount']);
    
    if($product_id > 0 && $quantity > 0 && $price > 0) {
        
        $check = $conn->query("SELECT id, quantity FROM stock WHERE product_id=$product_id AND branch_id=$branch_id");
        
        if($check && $check->num_rows > 0) {
         
            $existing = $check->fetch_assoc();
            $new_quantity = $existing['quantity'] + $quantity;
            $update_query = "UPDATE stock SET quantity=$new_quantity, price=$price, discount=$discount WHERE id={$existing['id']}";
            
            if($conn->query($update_query)) {
                $message = "Stock quantity added successfully! New total: $new_quantity";
            } else {
                $error = "Error updating stock: " . $conn->error;
            }
        } else {
         
            $branch_info = $conn->query("SELECT branch_code FROM branches WHERE id=$branch_id");
            if($branch_info && $branch_info->num_rows > 0) {
                $branch = $branch_info->fetch_assoc();
                $barcode = generate_barcode($branch['branch_code'], $product_id);
                
                $stmt = $conn->prepare("INSERT INTO stock (product_id, branch_id, quantity, price, discount, barcode, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if($stmt) {
                    $stmt->bind_param("iiiddsi", $product_id, $branch_id, $quantity, $price, $discount, $barcode, $_SESSION['user_id']);
                    if($stmt->execute()) {
                        $message = "New stock entry created successfully! Barcode: $barcode";
                    } else {
                        $error = "Error creating stock: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    } else {
        $error = "Invalid input data. Please check all fields.";
    }
}


$products = $conn->query("SELECT p.*, c.name as category_name, s.quantity as current_stock, s.price as current_price 
                          FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN stock s ON p.id = s.product_id AND s.branch_id = $branch_id
                          ORDER BY p.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
    }
    .modal-content {
        background-color: var(--dark-light);
        margin: 5% auto;
        padding: 30px;
        border: 1px solid var(--border);
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        max-height: 85vh;
        overflow-y: auto;
    }
    .close {
        color: var(--text-muted);
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close:hover {
        color: var(--primary);
    }
    .search-box {
        position: relative;
        margin-bottom: 10px;
    }
    .search-box input {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border: 1px solid var(--border);
        border-radius: 5px;
        background: var(--dark);
        color: var(--text);
        font-size: 14px;
    }
    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    .product-option {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }
    .product-option:hover {
        background: var(--dark);
    }
    .product-option.selected {
        background: var(--primary);
        color: white;
    }
    .product-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .product-name {
        font-weight: 600;
    }
    .product-category {
        font-size: 12px;
        color: var(--text-muted);
    }
    .product-stock {
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 3px;
        background: var(--dark);
    }
    .no-results {
        padding: 20px;
        text-align: center;
        color: var(--text-muted);
    }
    .selected-product {
        display: none;
        padding: 15px;
        background: var(--dark);
        border: 1px solid var(--primary);
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .selected-product.active {
        display: block;
    }
    .search-box {
        position: relative;
        margin-bottom: 10px;
    }
    .search-box input {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border: 1px solid var(--border);
        border-radius: 5px;
        background: var(--dark);
        color: var(--text);
        font-size: 14px;
    }
    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    .product-option {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }
    .product-option:hover {
        background: var(--dark);
    }
    .product-option.selected {
        background: var(--primary);
        color: white;
    }
    .product-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .product-name {
        font-weight: 600;
    }
    .product-category {
        font-size: 12px;
        color: var(--text-muted);
    }
    .product-stock {
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 3px;
        background: var(--dark);
    }
    .no-results {
        padding: 20px;
        text-align: center;
        color: var(--text-muted);
    }
    .selected-product {
        display: none;
        padding: 15px;
        background: var(--dark);
        border: 1px solid var(--primary);
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .selected-product.active {
        display: block;
    }
    .category-toggle {
        margin-top: 10px;
        padding: 10px;
        background: var(--dark);
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        color: var(--primary);
        border: 1px dashed var(--border);
    }
    .category-toggle:hover {
        border-color: var(--primary);
    }
    .new-category-input {
        display: none;
        margin-top: 10px;
    }
    .new-category-input.active {
        display: block;
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/user_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>➕ Add Stock Quantity</h1>
                <div>
                    <button onclick="openProductModal()" class="btn btn-success">+ New Product</button>
                </div>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Add Stock Form -->
            <div class="card">
                <div class="card-header">
                    <h2>Add Stock to Branch</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="addStockForm">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Filter by Category:</label>
                                <select id="categoryFilter" class="form-control" onchange="filterProducts()">
                                    <option value="">All Categories</option>
                                    <?php 
                                    $cats = $conn->query("SELECT * FROM categories ORDER BY name");
                                    while($cat = $cats->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Select Product:</label>
                                <select name="product_id" id="productSelect" required class="form-control" onchange="updateProductInfo()">
                                    <option value="">Choose Product</option>
                                    <?php 
                                    $prods = $conn->query("SELECT p.*, c.name as category_name, s.quantity as current_stock, s.price as current_price 
                                                          FROM products p 
                                                          JOIN categories c ON p.category_id = c.id 
                                                          LEFT JOIN stock s ON p.id = s.product_id AND s.branch_id = $branch_id
                                                          ORDER BY p.name");
                                    while($prod = $prods->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $prod['id']; ?>" 
                                                data-stock="<?php echo $prod['current_stock'] ?? 0; ?>"
                                                data-price="<?php echo $prod['current_price'] ?? ''; ?>"
                                                data-category="<?php echo $prod['category_id']; ?>"
                                                data-category-name="<?php echo $prod['category_name']; ?>">
                                            <?php echo $prod['name']; ?> (<?php echo $prod['category_name']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Current Stock:</label>
                                <input type="text" id="currentStock" readonly class="form-control" value="0" style="background: #1f2937; color: #10b981; font-weight: bold;">
                            </div>
                            
                            <div class="form-group">
                                <label>Quantity to Add:</label>
                                <input type="number" name="quantity" required min="1" class="form-control" placeholder="Enter quantity">
                            </div>
                        </div>
                      
                            
                            <div class="form-group">
                                <label>Price (Rs):</label>
                                <input type="number" name="price" id="priceInput" required step="0.01" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Discount (%):</label>
                            <input type="number" name="discount" value="0" step="0.01" max="100" class="form-control">
                        </div>
                        
                        <button type="submit" name="add_stock_qty" class="btn btn-primary btn-block">
                            ➕ Add Stock Quantity
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Current Stock Overview -->
            <div class="card">
                <div class="card-header">
                    <h2>📦 Current Stock Levels</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stock_overview = $conn->query("SELECT p.name, c.name as category, s.quantity, s.price, p.id 
                                                           FROM stock s 
                                                           JOIN products p ON s.product_id = p.id 
                                                           JOIN categories c ON p.category_id = c.id 
                                                           WHERE s.branch_id = $branch_id 
                                                           ORDER BY p.name");
                            if($stock_overview->num_rows > 0):
                                while($item = $stock_overview->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $item['name']; ?></td>
                                <td><span class="badge"><?php echo $item['category']; ?></span></td>
                                <td><strong><?php echo $item['quantity']; ?></strong></td>
                                <td><?php echo CURRENCY . number_format($item['price'], 2); ?></td>
                                <td>
                                    <button onclick="quickAdd(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')" class="btn btn-sm btn-success">Quick Add</button>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No stock entries yet</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeProductModal()">&times;</span>
            <h2>📦 Add New Product</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Product Name:</label>
                    <input type="text" name="product_name" required class="form-control" placeholder="e.g. Samsung Galaxy S21">
                </div>
                
                <div class="form-group">
                    <label>Select Category:</label>
                    <select name="category_name" id="categorySelect" class="form-control">
                        <option value="">Choose existing category</option>
                        <?php 
                        $cats = $conn->query("SELECT * FROM categories ORDER BY name");
                        while($cat = $cats->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    
                    <div class="category-toggle" onclick="toggleNewCategory()">
                        ➕ Or create new category
                    </div>
                    
                    <div id="newCategoryDiv" class="new-category-input">
                        <input type="text" name="new_category" class="form-control" placeholder="Enter new category name">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description (Optional):</label>
                    <textarea name="product_description" class="form-control" rows="3" placeholder="Product details..."></textarea>
                </div>
                
                <button type="submit" name="add_product" class="btn btn-success btn-block">Add Product</button>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
    function filterProducts() {
        const categoryId = document.getElementById('categoryFilter').value;
        const productSelect = document.getElementById('productSelect');
        const options = productSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if(option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const productCategory = option.getAttribute('data-category');
            
            if(categoryId === '' || productCategory === categoryId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset product selection
        productSelect.value = '';
        document.getElementById('currentStock').value = '0';
        document.getElementById('priceInput').value = '';
    }
    
    function updateProductInfo() {
        const select = document.getElementById('productSelect');
        const option = select.options[select.selectedIndex];
        
        if(option.value) {
            const currentStock = option.getAttribute('data-stock');
            const price = option.getAttribute('data-price');
            
            document.getElementById('currentStock').value = currentStock || '0';
            document.getElementById('priceInput').value = price || '';
        } else {
            document.getElementById('currentStock').value = '0';
            document.getElementById('priceInput').value = '';
        }
    }
    
    function quickAdd(productId, productName) {
        const quantity = prompt(`Add quantity for ${productName}:`, '10');
        if(quantity && quantity > 0) {
            document.getElementById('productSelect').value = productId;
            updateProductInfo();
            document.querySelector('input[name="quantity"]').value = quantity;
            document.getElementById('addStockForm').scrollIntoView({behavior: 'smooth'});
        }
    }
    
    // Product Modal Functions
    function openProductModal() {
        document.getElementById('productModal').style.display = 'block';
    }
    
    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
    }
    
    // Toggle new category input
    function toggleNewCategory() {
        const newCatDiv = document.getElementById('newCategoryDiv');
        const categorySelect = document.getElementById('categorySelect');
        
        if(newCatDiv.classList.contains('active')) {
            newCatDiv.classList.remove('active');
            categorySelect.disabled = false;
            categorySelect.required = true;
        } else {
            newCatDiv.classList.add('active');
            categorySelect.value = '';
            categorySelect.disabled = true;
            categorySelect.required = false;
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const productModal = document.getElementById('productModal');
        
        if (event.target == productModal) {
            closeProductModal();
        }
    }
    </script>
</body>
</html>
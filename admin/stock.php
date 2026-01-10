<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Add stock
if(isset($_POST['add_stock'])) {
    $product_id = $_POST['product_id'];
    $branch_id = $_POST['branch_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];
    
    // Get branch code
    $branch = $conn->query("SELECT branch_code FROM branches WHERE id=$branch_id")->fetch_assoc();
    $barcode = generate_barcode($branch['branch_code'], $product_id);
    
    // Check if stock exists for this product-branch combination
    $check = $conn->query("SELECT id, quantity FROM stock WHERE product_id=$product_id AND branch_id=$branch_id");
    
    if($check->num_rows > 0) {
        // Update existing stock
        $existing = $check->fetch_assoc();
        $new_quantity = $existing['quantity'] + $quantity;
        $conn->query("UPDATE stock SET quantity=$new_quantity, price=$price, discount=$discount WHERE id={$existing['id']}");
        $message = "Stock updated successfully!";
    } else {
        // Insert new stock
        $stmt = $conn->prepare("INSERT INTO stock (product_id, branch_id, quantity, price, discount, barcode) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidds", $product_id, $branch_id, $quantity, $price, $discount, $barcode);
        if($stmt->execute()) {
            $message = "Stock added successfully! Barcode: " . $barcode;
        }
    }
}

// Stock transfer
if(isset($_POST['transfer_stock'])) {
    $stock_id = $_POST['stock_id'];
    $to_branch = $_POST['to_branch'];
    $transfer_qty = $_POST['transfer_quantity'];
    
    // Get source stock details
    $source = $conn->query("SELECT * FROM stock WHERE id=$stock_id")->fetch_assoc();
    
    if($source['quantity'] >= $transfer_qty) {
        // Deduct from source
        $new_qty = $source['quantity'] - $transfer_qty;
        $conn->query("UPDATE stock SET quantity=$new_qty WHERE id=$stock_id");
        
        // Add to target branch
        $target_check = $conn->query("SELECT id, quantity FROM stock WHERE product_id={$source['product_id']} AND branch_id=$to_branch");
        
        if($target_check->num_rows > 0) {
            $target = $target_check->fetch_assoc();
            $target_new_qty = $target['quantity'] + $transfer_qty;
            $conn->query("UPDATE stock SET quantity=$target_new_qty WHERE id={$target['id']}");
        } else {
            // Create new stock entry for target branch
            $branch = $conn->query("SELECT branch_code FROM branches WHERE id=$to_branch")->fetch_assoc();
            $new_barcode = generate_barcode($branch['branch_code'], $source['product_id']);
            $conn->query("INSERT INTO stock (product_id, branch_id, quantity, price, discount, barcode) 
                         VALUES ({$source['product_id']}, $to_branch, $transfer_qty, {$source['price']}, {$source['discount']}, '$new_barcode')");
        }
        
        // Log transfer
        $conn->query("INSERT INTO stock_transfers (product_id, from_branch_id, to_branch_id, quantity, transferred_by) 
                     VALUES ({$source['product_id']}, {$source['branch_id']}, $to_branch, $transfer_qty, {$_SESSION['user_id']})");
        
        $message = "Stock transferred successfully!";
    } else {
        $error = "Insufficient stock quantity!";
    }
}

// Get all stock with product and branch info
$stock_list = $conn->query("SELECT s.*, p.name as product_name, b.name as branch_name, b.branch_code 
                            FROM stock s 
                            JOIN products p ON s.product_id = p.id 
                            JOIN branches b ON s.branch_id = b.id 
                            ORDER BY s.added_date DESC");

$products = $conn->query("SELECT * FROM products ORDER BY name");
$branches = $conn->query("SELECT * FROM branches WHERE status='active' ORDER BY name");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>📦 Stock Management</h1>
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
                    <h2>Add/Update Stock</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="form-inline">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category:</label>
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
                                <label>Product:</label>
                                <select name="product_id" id="productSelect" required class="form-control">
                                    <option value="">Select Product</option>
                                    <?php 
                                    $prods = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.name");
                                    while($prod = $prods->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $prod['id']; ?>" data-category="<?php echo $prod['category_id']; ?>">
                                            <?php echo $prod['name']; ?> (<?php echo $prod['category_name']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Branch:</label>
                                <select name="branch_id" required class="form-control">
                                    <option value="">Select Branch</option>
                                    <?php 
                                    $brs = $conn->query("SELECT * FROM branches WHERE status='active' ORDER BY name");
                                    while($br = $brs->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $br['id']; ?>"><?php echo $br['name']; ?> (<?php echo $br['branch_code']; ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Quantity:</label>
                                <input type="number" name="quantity" required min="1" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Price (Rs):</label>
                                <input type="number" name="price" required step="0.01" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Discount (%):</label>
                                <input type="number" name="discount" value="0" step="0.01" max="100" class="form-control">
                            </div>
                            
                            <button type="submit" name="add_stock" class="btn btn-primary">Add Stock</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Stock List -->
            <div class="card">
                <div class="card-header">
                    <h2>Current Stock</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
                                    <th>Product</th>
                                    <th>Branch</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Added Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($stock = $stock_list->fetch_assoc()): ?>
                                <tr class="<?php echo $stock['quantity'] <= $stock['alert_level'] ? 'low-stock-row' : ''; ?>">
                                    <td><span class="barcode"><?php echo $stock['barcode']; ?></span></td>
                                    <td><?php echo $stock['product_name']; ?></td>
                                    <td><span class="badge"><?php echo $stock['branch_name']; ?></span></td>
                                    <td><?php echo $stock['quantity']; ?></td>
                                    <td><?php echo CURRENCY . number_format($stock['price'], 2); ?></td>
                                    <td><?php echo $stock['discount']; ?>%</td>
                                    <td><?php echo date('Y-m-d', strtotime($stock['added_date'])); ?></td>
                                    <td>
                                        <button onclick="openTransferModal(<?php echo $stock['id']; ?>, '<?php echo $stock['product_name']; ?>', '<?php echo $stock['branch_name']; ?>', <?php echo $stock['quantity']; ?>)" 
                                                class="btn btn-sm btn-info">Transfer</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transfer Modal -->
    <div id="transferModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeTransferModal()">&times;</span>
            <h2>Transfer Stock</h2>
            <form method="POST" action="">
                <input type="hidden" name="stock_id" id="transfer_stock_id">
                
                <div class="form-group">
                    <label>Product:</label>
                    <input type="text" id="transfer_product" readonly class="form-control">
                </div>
                
                <div class="form-group">
                    <label>From Branch:</label>
                    <input type="text" id="transfer_from_branch" readonly class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Available Quantity:</label>
                    <input type="text" id="transfer_available" readonly class="form-control">
                </div>
                
                <div class="form-group">
                    <label>To Branch:</label>
                    <select name="to_branch" required class="form-control">
                        <option value="">Select Branch</option>
                        <?php 
                        $brs2 = $conn->query("SELECT * FROM branches WHERE status='active' ORDER BY name");
                        while($br2 = $brs2->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $br2['id']; ?>"><?php echo $br2['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Transfer Quantity:</label>
                    <input type="number" name="transfer_quantity" required min="1" class="form-control">
                </div>
                
                <button type="submit" name="transfer_stock" class="btn btn-primary">Transfer</button>
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
    }
    
    function openTransferModal(stockId, productName, branchName, quantity) {
        document.getElementById('transfer_stock_id').value = stockId;
        document.getElementById('transfer_product').value = productName;
        document.getElementById('transfer_from_branch').value = branchName;
        document.getElementById('transfer_available').value = quantity;
        document.getElementById('transferModal').style.display = 'block';
    }
    
    function closeTransferModal() {
        document.getElementById('transferModal').style.display = 'none';
    }
    </script>
</body>
</html>
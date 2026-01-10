<?php
// user/stock_view.php - Enhanced Stock View with Filters
require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$branch_id = $_SESSION['branch_id'];
$role = $_SESSION['role'];

// Filters
$category_filter = $_GET['category'] ?? '';
$product_filter = $_GET['product'] ?? '';

// Build query based on filters
$query = "SELECT s.*, p.name as product_name, p.category_id, c.name as category_name, b.name as branch_name 
          FROM stock s 
          JOIN products p ON s.product_id = p.id 
          JOIN categories c ON p.category_id = c.id 
          JOIN branches b ON s.branch_id = b.id 
          WHERE 1=1";

// Role-based access
if($role == 'user' && $branch_id) {
    $query .= " AND s.branch_id = $branch_id";
}

// Apply filters
if($category_filter) {
    $query .= " AND c.id = $category_filter";
}
if($product_filter) {
    $query .= " AND p.id = $product_filter";
}

$query .= " ORDER BY p.name, b.name";

$stock_list = $conn->query($query);

// Get products for filter (based on selected category)
$products_query = "SELECT * FROM products WHERE 1=1";
if($category_filter) {
    $products_query .= " AND category_id = $category_filter";
}
$products_query .= " ORDER BY name";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Stock - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php 
        if($role == 'admin') {
            include '../includes/admin_sidebar.php'; 
        } else {
            include '../includes/user_sidebar.php';
        }
        ?>
        
        <div class="content">
            <div class="page-header">
                <h1>📦 Current Stock Inventory</h1>
                <p>View and filter stock by category and product</p>
            </div>
            
            <!-- Filter Section -->
            <div class="card">
                <div class="card-header">
                    <h2>🔍 Filter Stock</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="form-inline">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category:</label>
                                <select name="category" id="categoryFilter" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php 
                                    $cats = $conn->query("SELECT * FROM categories ORDER BY name");
                                    while($cat = $cats->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo $cat['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Product:</label>
                                <select name="product" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Products</option>
                                    <?php 
                                    $prods = $conn->query($products_query);
                                    while($prod = $prods->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $prod['id']; ?>" <?php echo $product_filter == $prod['id'] ? 'selected' : ''; ?>>
                                            <?php echo $prod['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <?php if($category_filter || $product_filter): ?>
                            <a href="stock_view.php" class="btn btn-warning">Clear Filters</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Stock List -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 Stock Inventory</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <?php if($role == 'admin'): ?>
                                    <th>Branch</th>
                                    <?php endif; ?>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($stock_list->num_rows > 0): ?>
                                    <?php while($stock = $stock_list->fetch_assoc()): ?>
                                    <tr class="<?php echo $stock['quantity'] <= $stock['alert_level'] ? 'low-stock-row' : ''; ?>">
                                        <td><span class="barcode"><?php echo $stock['barcode']; ?></span></td>
                                        <td><?php echo $stock['product_name']; ?></td>
                                        <td><span class="badge"><?php echo $stock['category_name']; ?></span></td>
                                        <?php if($role == 'admin'): ?>
                                        <td><span class="badge badge-info"><?php echo $stock['branch_name']; ?></span></td>
                                        <?php endif; ?>
                                        <td><strong><?php echo $stock['quantity']; ?></strong></td>
                                        <td><?php echo CURRENCY . number_format($stock['price'], 2); ?></td>
                                        <td><?php echo $stock['discount']; ?>%</td>
                                        <td><?php echo date('Y-m-d', strtotime($stock['added_date'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?php echo $role == 'admin' ? '8' : '7'; ?>" class="text-center">
                                            No stock found matching your filters
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
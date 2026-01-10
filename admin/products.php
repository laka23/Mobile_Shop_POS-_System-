<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Add category
if(isset($_POST['add_category'])) {
    $name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if($stmt->execute()) {
        $message = "Category added successfully!";
    }
}

// Add product
if(isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    
    $stmt = $conn->prepare("INSERT INTO products (name, category_id, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $name, $category_id, $description);
    if($stmt->execute()) {
        $message = "Product added successfully!";
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>📦 Product Management</h1>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="grid-2">
                <!-- Add Category -->
                <div class="card">
                    <div class="card-header">
                        <h2>Add Category</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>Category Name:</label>
                                <input type="text" name="category_name" required class="form-control">
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                        </form>
                    </div>
                </div>
                
                <!-- Add Product -->
                <div class="card">
                    <div class="card-header">
                        <h2>Add Product</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>Product Name:</label>
                                <input type="text" name="product_name" required class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Category:</label>
                                <select name="category_id" required class="form-control">
                                    <option value="">Select Category</option>
                                    <?php 
                                    $cats = $conn->query("SELECT * FROM categories ORDER BY name");
                                    while($cat = $cats->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Description:</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Products List -->
            <div class="card">
                <div class="card-header">
                    <h2>All Products</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo $product['name']; ?></td>
                                <td><span class="badge"><?php echo $product['category_name']; ?></span></td>
                                <td><?php echo substr($product['description'], 0, 50); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
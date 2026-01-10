<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();

$low_stock = $conn->query("SELECT s.*, p.name as product_name, b.name as branch_name, b.branch_code 
                           FROM stock s 
                           JOIN products p ON s.product_id = p.id 
                           JOIN branches b ON s.branch_id = b.id 
                           WHERE s.quantity <= s.alert_level 
                           ORDER BY s.quantity ASC, b.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>⚠️ Low Stock Alert</h1>
                <p>Items running low in stock across all branches</p>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Low Stock Items</h2>
                </div>
                <div class="card-body">
                    <?php if($low_stock->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Current Qty</th>
                                <th>Alert Level</th>
                                <th>Price</th>
                                <th>Barcode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $low_stock->fetch_assoc()): ?>
                            <tr class="low-stock-row">
                                <td><?php echo $item['product_name']; ?></td>
                                <td><span class="badge"><?php echo $item['branch_name']; ?></span></td>
                                <td><strong class="text-danger"><?php echo $item['quantity']; ?></strong></td>
                                <td><?php echo $item['alert_level']; ?></td>
                                <td><?php echo CURRENCY . number_format($item['price'], 2); ?></td>
                                <td><span class="barcode"><?php echo $item['barcode']; ?></span></td>
                                <td>
                                    <a href="stock.php" class="btn btn-sm btn-primary">Add Stock</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-success">No low stock items! 🎉</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php

require_once '../config/database.php';
require_once '../config/config.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();

// Filters
$category_filter = $_GET['category'] ?? '';
$product_filter = $_GET['product'] ?? '';
$branch_filter = $_GET['branch'] ?? '';

// Build query
$query = "SELECT s.*, p.name as product_name, c.name as category_name, b.name as branch_name, b.branch_code 
          FROM stock s 
          JOIN products p ON s.product_id = p.id 
          JOIN categories c ON p.category_id = c.id 
          JOIN branches b ON s.branch_id = b.id 
          WHERE 1=1";

if($category_filter) {
    $query .= " AND c.id = $category_filter";
}
if($product_filter) {
    $query .= " AND p.id = $product_filter";
}
if($branch_filter) {
    $query .= " AND b.id = $branch_filter";
}

$query .= " ORDER BY c.name, p.name, b.name";

$stock_data = $conn->query($query);

// Calculate totals
$total_items = 0;
$total_value = 0;
$low_stock_items = 0;

$stock_summary = [];
while($row = $stock_data->fetch_assoc()) {
    $total_items += $row['quantity'];
    $total_value += ($row['quantity'] * $row['price']);
    if($row['quantity'] <= $row['alert_level']) {
        $low_stock_items++;
    }
    $stock_summary[] = $row;
}

// Get products for filter
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
    <title>Stock Report - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white;
        }
        .card {
            box-shadow: none;
            border: 1px solid #000;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header no-print">
                <h1>📊 Stock Report</h1>
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
            </div>
            
            <!-- Filters -->
            <div class="card no-print">
                <div class="card-header">
                    <h2>🔍 Filter Report</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="form-inline">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category:</label>
                                <select name="category" class="form-control" onchange="this.form.submit()">
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
                            
                            <div class="form-group">
                                <label>Branch:</label>
                                <select name="branch" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Branches</option>
                                    <?php 
                                    $branches = $conn->query("SELECT * FROM branches WHERE status='active' ORDER BY name");
                                    while($branch = $branches->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $branch['id']; ?>" <?php echo $branch_filter == $branch['id'] ? 'selected' : ''; ?>>
                                            <?php echo $branch['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <?php if($category_filter || $product_filter || $branch_filter): ?>
                            <a href="stock_report.php" class="btn btn-warning">Clear Filters</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Report Header (Print Only) -->
            <div style="text-align: center; margin-bottom: 20px; display: none;" class="print-only">
                <h1>📦 <?php echo SITE_NAME; ?> - Stock Report</h1>
                <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
                <?php if($category_filter): 
                    $cat_name = $conn->query("SELECT name FROM categories WHERE id=$category_filter")->fetch_assoc()['name'];
                ?>
                <p>Category: <strong><?php echo $cat_name; ?></strong></p>
                <?php endif; ?>
                <?php if($product_filter): 
                    $prod_name = $conn->query("SELECT name FROM products WHERE id=$product_filter")->fetch_assoc()['name'];
                ?>
                <p>Product: <strong><?php echo $prod_name; ?></strong></p>
                <?php endif; ?>
            </div>
            
            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">📦</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($total_items); ?></h3>
                        <p>Total Units</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($total_value, 2); ?></h3>
                        <p>Total Value</p>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-details">
                        <h3><?php echo $low_stock_items; ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <h3><?php echo count($stock_summary); ?></h3>
                        <p>Stock Entries</p>
                    </div>
                </div>
            </div>
            
            <!-- Stock Table -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 Stock Details</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Barcode</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($stock_summary)): ?>
                                <?php foreach($stock_summary as $item): 
                                    $item_value = $item['quantity'] * $item['price'];
                                    $is_low = $item['quantity'] <= $item['alert_level'];
                                ?>
                                <tr class="<?php echo $is_low ? 'low-stock-row' : ''; ?>">
                                    <td><?php echo $item['category_name']; ?></td>
                                    <td><?php echo $item['product_name']; ?></td>
                                    <td><?php echo $item['branch_name']; ?> (<?php echo $item['branch_code']; ?>)</td>
                                    <td><?php echo $item['barcode']; ?></td>
                                    <td><strong><?php echo $item['quantity']; ?></strong></td>
                                    <td><?php echo CURRENCY . number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['discount']; ?>%</td>
                                    <td><?php echo CURRENCY . number_format($item_value, 2); ?></td>
                                    <td>
                                        <?php if($is_low): ?>
                                            <span class="badge badge-danger">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">OK</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <!-- Total Row -->
                                <tr style="background: var(--dark); border-top: 3px solid var(--primary); font-weight: bold;">
                                    <td colspan="4" class="text-right">TOTAL:</td>
                                    <td><?php echo number_format($total_items); ?></td>
                                    <td colspan="2"></td>
                                    <td><?php echo CURRENCY . number_format($total_value, 2); ?></td>
                                    <td></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No stock found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <style>
    .print-only {
        display: none !important;
    }
    @media print {
        .print-only {
            display: block !important;
        }
        .text-right {
            text-align: right;
        }
    }
    </style>
</body>
</html>
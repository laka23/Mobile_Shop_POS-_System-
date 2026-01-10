<?php

require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$sale_id = $_GET['id'] ?? 0;

// Get sale details
$sale = $conn->query("SELECT s.*, b.name as branch_name, b.branch_code, b.location, u.full_name as cashier 
                      FROM sales s 
                      JOIN branches b ON s.branch_id = b.id 
                      JOIN users u ON s.user_id = u.id 
                      WHERE s.id = $sale_id")->fetch_assoc();

if(!$sale) {
    echo "Sale not found!";
    exit();
}

// Get sale items
$items = $conn->query("SELECT si.*, p.name as product_name 
                       FROM sale_items si 
                       LEFT JOIN products p ON si.product_id = p.id 
                       WHERE si.sale_id = $sale_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Details - <?php echo $sale['sale_number']; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php 
        if($_SESSION['role'] == 'admin') {
            include '../includes/admin_sidebar.php';
        } else {
            include '../includes/user_sidebar.php';
        }
        ?>
        
        <div class="content">
            <div class="page-header">
                <h1>🧾 Sale Details</h1>
                <div style="display: flex; gap: 10px;">
                    <a href="sales_history.php" class="btn btn-sm btn-info">← Back to History</a>
                    <a href="print_invoice.php?sale_id=<?php echo $sale['id']; ?>" target="_blank" class="btn btn-sm btn-primary">🖨️ Print Invoice</a>
                </div>
            </div>
            
            <!-- Sale Information -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 Sale Information</h2>
                </div>
                <div class="card-body">
                    <div class="info-section" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <div class="info-row">
                                <strong>Sale Number:</strong>
                                <span><?php echo $sale['sale_number']; ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Branch:</strong>
                                <span><?php echo $sale['branch_name']; ?> (<?php echo $sale['branch_code']; ?>)</span>
                            </div>
                            <div class="info-row">
                                <strong>Cashier:</strong>
                                <span><?php echo $sale['cashier']; ?></span>
                            </div>
                        </div>
                        <div>
                            <div class="info-row">
                                <strong>Date & Time:</strong>
                                <span><?php echo date('Y-m-d H:i:s', strtotime($sale['sale_date'])); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Payment Method:</strong>
                                <span class="badge badge-<?php echo $sale['payment_method']=='cash'?'success':'info'; ?>">
                                    <?php echo strtoupper($sale['payment_method']); ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <strong>Total Amount:</strong>
                                <span style="font-size: 1.5em; color: var(--primary);">
                                    <?php echo CURRENCY . number_format($sale['total_amount'], 2); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sale Items -->
            <div class="card">
                <div class="card-header">
                    <h2>🛒 Items Sold</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Discount %</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            while($item = $items->fetch_assoc()): 
                                $total += $item['subtotal'];
                            ?>
                            <tr>
                                <td>
                                    <?php 
                                    if($item['product_id'] == 0) {
                                        echo '<span class="badge badge-warning">Manual Entry</span>';
                                    } else {
                                        echo $item['product_name'];
                                    }
                                    ?>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo CURRENCY . number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['discount']; ?>%</td>
                                <td><strong><?php echo CURRENCY . number_format($item['subtotal'], 2); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                            <tr style="background: var(--dark); border-top: 2px solid var(--primary);">
                                <td colspan="4" class="text-right"><strong>GRAND TOTAL:</strong></td>
                                <td><strong style="font-size: 1.3em; color: var(--primary);">
                                    <?php echo CURRENCY . number_format($sale['total_amount'], 2); ?>
                                </strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

<style>
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    margin: 5px 0;
    border-bottom: 1px solid var(--border);
}

.text-right {
    text-align: right;
}
</style>
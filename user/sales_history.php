<?php

require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$branch_id = $_SESSION['branch_id'];
$role = $_SESSION['role'];

// Date filters
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Build query
$query = "SELECT s.*, b.name as branch_name, b.branch_code, u.full_name as cashier 
          FROM sales s 
          JOIN branches b ON s.branch_id = b.id 
          JOIN users u ON s.user_id = u.id 
          WHERE DATE(s.sale_date) BETWEEN '$date_from' AND '$date_to'";

if($role == 'user' && $branch_id) {
    $query .= " AND s.branch_id = $branch_id";
}

$query .= " ORDER BY s.sale_date DESC";

$sales = $conn->query($query);

// Calculate totals
$totals_query = "SELECT 
                    COUNT(*) as total_sales,
                    COALESCE(SUM(total_amount), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN payment_method='cash' THEN total_amount ELSE 0 END), 0) as cash_total,
                    COALESCE(SUM(CASE WHEN payment_method='card' THEN total_amount ELSE 0 END), 0) as card_total
                 FROM sales s 
                 WHERE DATE(s.sale_date) BETWEEN '$date_from' AND '$date_to'";

if($role == 'user' && $branch_id) {
    $totals_query .= " AND s.branch_id = $branch_id";
}

$totals = $conn->query($totals_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales History - <?php echo SITE_NAME; ?></title>
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
                <h1>📊 Sales History</h1>
                <p>View and analyze sales transactions</p>
            </div>
            
            <!-- Date Filter -->
            <div class="card">
                <div class="card-header">
                    <h2>📅 Filter by Date Range</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="form-inline">
                        <div class="form-row">
                            <div class="form-group">
                                <label>From Date:</label>
                                <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="form-control" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>To Date:</label>
                                <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="form-control" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                            <a href="sales_history.php" class="btn btn-warning">Reset</a>
                        </div>
                    </form>
                    
                    <!-- Quick Date Filters -->
                    <div style="margin-top: 15px;">
                        <strong>Quick Filters:</strong>
                        <a href="?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-info">Today</a>
                        <a href="?date_from=<?php echo date('Y-m-d', strtotime('yesterday')); ?>&date_to=<?php echo date('Y-m-d', strtotime('yesterday')); ?>" class="btn btn-sm btn-info">Yesterday</a>
                        <a href="?date_from=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-info">Last 7 Days</a>
                        <a href="?date_from=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-info">Last 30 Days</a>
                        <a href="?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-info">This Month</a>
                    </div>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($totals['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">🧾</div>
                    <div class="stat-details">
                        <h3><?php echo $totals['total_sales']; ?></h3>
                        <p>Total Invoices</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">💵</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($totals['cash_total'], 2); ?></h3>
                        <p>Cash Payments</p>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">💳</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($totals['card_total'], 2); ?></h3>
                        <p>Card Payments</p>
                    </div>
                </div>
            </div>
            
            <!-- Sales List -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 Sales Records (<?php echo $date_from; ?> to <?php echo $date_to; ?>)</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date & Time</th>
                                    <?php if($role == 'admin'): ?>
                                    <th>Branch</th>
                                    <?php endif; ?>
                                    <th>Cashier</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($sales->num_rows > 0): ?>
                                    <?php while($sale = $sales->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $sale['sale_number']; ?></strong></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($sale['sale_date'])); ?></td>
                                        <?php if($role == 'admin'): ?>
                                        <td><span class="badge"><?php echo $sale['branch_name']; ?></span></td>
                                        <?php endif; ?>
                                        <td><?php echo $sale['cashier']; ?></td>
                                        <td><strong style="color: var(--primary);"><?php echo CURRENCY . number_format($sale['total_amount'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $sale['payment_method']=='cash'?'success':'info'; ?>">
                                                <?php echo strtoupper($sale['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view_sale_details.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-info">View</a>
                                            <a href="print_invoice.php?sale_id=<?php echo $sale['id']; ?>" target="_blank" class="btn btn-sm btn-primary">Print</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?php echo $role == 'admin' ? '7' : '6'; ?>" class="text-center">
                                            No sales found for the selected date range
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
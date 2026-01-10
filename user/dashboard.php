<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$branch_id = $_SESSION['branch_id'];
$today = date('Y-m-d');

// Get user's branch stats
$sales_today = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total 
                             FROM sales 
                             WHERE branch_id=$branch_id AND DATE(sale_date)='$today'")->fetch_assoc()['total'];

$repairs_today = $conn->query("SELECT COALESCE(SUM(repair_cost), 0) as total 
                               FROM repairs 
                               WHERE branch_id=$branch_id AND DATE(completed_date)='$today' AND status='completed'")->fetch_assoc()['total'];

$low_stock_count = $conn->query("SELECT COUNT(*) as count 
                                FROM stock 
                                WHERE branch_id=$branch_id AND quantity <= alert_level")->fetch_assoc()['count'];

// Branch info
$branch = $conn->query("SELECT * FROM branches WHERE id=$branch_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/user_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>🏪 User Dashboard</h1>
                <p>Welcome, <?php echo $_SESSION['full_name']; ?>! - Branch: <?php echo $branch['name']; ?></p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($sales_today + $repairs_today, 2); ?></h3>
                        <p>Today's Total Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($sales_today, 2); ?></h3>
                        <p>Sales Today</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">🔧</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($repairs_today, 2); ?></h3>
                        <p>Repairs Today</p>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">📦</div>
                    <div class="stat-details">
                        <h3><?php echo $low_stock_count; ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2>⚡ Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="pos.php" class="action-btn primary">
                            <span class="icon">🛒</span>
                            <span>New Sale (POS)</span>
                        </a>
                        
                        <a href="repairs.php" class="action-btn info">
                            <span class="icon">🔧</span>
                            <span>Register Repair</span>
                        </a>
                        
                        <a href="returns.php" class="action-btn warning">
                            <span class="icon">↩️</span>
                            <span>Process Return</span>
                        </a>
                        
                        <a href="stock_view.php" class="action-btn success">
                            <span class="icon">📦</span>
                            <span>View Stock</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Notification -->
            <?php if($low_stock_count > 0): ?>
            <div class="alert alert-warning">
                ⚠️ Warning: <?php echo $low_stock_count; ?> items are running low in stock! 
                <a href="stock_view.php">View Details</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
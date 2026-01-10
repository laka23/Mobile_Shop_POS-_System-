<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();

// Get today's statistics
$today = date('Y-m-d');

// Total branches
$branches_count = $conn->query("SELECT COUNT(*) as count FROM branches WHERE status='active'")->fetch_assoc()['count'];

// Today's total revenue (all branches)
$sales_revenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(sale_date) = '$today'")->fetch_assoc()['total'];

$repairs_revenue = $conn->query("SELECT COALESCE(SUM(repair_cost), 0) as total FROM repairs WHERE DATE(completed_date) = '$today' AND status='completed'")->fetch_assoc()['total'];

$total_revenue = $sales_revenue + $repairs_revenue;

// Low stock count
$low_stock_count = $conn->query("SELECT COUNT(*) as count FROM stock WHERE quantity <= alert_level")->fetch_assoc()['count'];

// Branch-wise performance
$branch_stats = $conn->query("SELECT b.id, b.name, b.branch_code,
    COALESCE(SUM(s.total_amount), 0) as sales_today,
    COALESCE(SUM(r.repair_cost), 0) as repairs_today
    FROM branches b
    LEFT JOIN sales s ON b.id = s.branch_id AND DATE(s.sale_date) = '$today'
    LEFT JOIN repairs r ON b.id = r.branch_id AND DATE(r.completed_date) = '$today' AND r.status='completed'
    WHERE b.status = 'active'
    GROUP BY b.id, b.name, b.branch_code
    ORDER BY (COALESCE(SUM(s.total_amount), 0) + COALESCE(SUM(r.repair_cost), 0)) DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>📊 Admin Dashboard</h1>
                <p>Welcome back, <?php echo $_SESSION['full_name']; ?>!</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($total_revenue, 2); ?></h3>
                        <p>Today's Total Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($sales_revenue, 2); ?></h3>
                        <p>Sales Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">🔧</div>
                    <div class="stat-details">
                        <h3><?php echo CURRENCY . number_format($repairs_revenue, 2); ?></h3>
                        <p>Repairs Revenue</p>
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
            
            <!-- Branch Performance -->
            <div class="card">
                <div class="card-header">
                    <h2>🏢 Branch-wise Performance (Today)</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Code</th>
                                <th>Sales</th>
                                <th>Repairs</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($branch = $branch_stats->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $branch['name']; ?></td>
                                <td><span class="badge"><?php echo $branch['branch_code']; ?></span></td>
                                <td><?php echo CURRENCY . number_format($branch['sales_today'], 2); ?></td>
                                <td><?php echo CURRENCY . number_format($branch['repairs_today'], 2); ?></td>
                                <td><strong><?php echo CURRENCY . number_format($branch['sales_today'] + $branch['repairs_today'], 2); ?></strong></td>
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
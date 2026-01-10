<?php 
require_once '../config/config.php';
require_once '../config/database.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="main-container">

    <!-- SIDEBAR -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- CONTENT AREA -->
    <div class="content">

        <!-- PAGE TITLE -->
        <div class="page-header">
            <h1>📊 Reports Dashboard</h1>
            <p>System Overview & Business Analytics</p>
        </div>

        <!-- STAT CARDS -->
        <div class="stats-grid">

            <!-- Total Sales -->
            <div class="stat-card primary">
                <div class="stat-icon">💰</div>
                <div class="stat-details">
                <?php 
                    $totalSales = $conn->query("SELECT SUM(total_amount) AS total FROM sales")->fetch_assoc()['total'] ?? 0;
                ?>
                    <h3>Rs. <?php echo number_format($totalSales, 2); ?></h3>
                    <p>Total Sales</p>
                </div>
            </div>

            <!-- Invoice Count -->
            <div class="stat-card success">
                <div class="stat-icon">🧾</div>
                <div class="stat-details">
                <?php 
                    $invoiceCount = $conn->query("SELECT COUNT(*) AS cnt FROM sales")->fetch_assoc()['cnt'] ?? 0;
                ?>
                    <h3><?php echo $invoiceCount; ?></h3>
                    <p>Invoices</p>
                </div>
            </div>

            <!-- Completed Repairs -->
            <div class="stat-card info">
                <div class="stat-icon">🛠️</div>
                <div class="stat-details">
                <?php 
                    $completed = $conn->query("SELECT COUNT(*) AS cnt FROM repairs WHERE status='completed'")->fetch_assoc()['cnt'] ?? 0;
                ?>
                    <h3><?php echo $completed; ?></h3>
                    <p>Repairs Completed</p>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="stat-card warning">
                <div class="stat-icon">⚠️</div>
                <div class="stat-details">
                <?php 
                    $lowStock = $conn->query("SELECT COUNT(*) AS cnt FROM stock WHERE quantity <= alert_level")->fetch_assoc()['cnt'] ?? 0;
                ?>
                    <h3><?php echo $lowStock; ?></h3>
                    <p>Low Stock Items</p>
                </div>
            </div>

        </div>

        <!-- SALES REPORT -->
        <div class="card">
            <div class="card-header">
                <h2>📅 Recent Sales</h2>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Branch</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                        $salesQuery = "
                            SELECT s.sale_number, s.total_amount, s.payment_method, s.sale_date,
                                u.full_name AS user_name, b.name AS branch_name
                            FROM sales s
                            JOIN users u ON s.user_id = u.id
                            JOIN branches b ON s.branch_id = b.id
                            ORDER BY s.id DESC LIMIT 20
                        ";
                        $sales = $conn->query($salesQuery);

                        if ($sales->num_rows > 0) {
                            while ($row = $sales->fetch_assoc()) {
                                echo "
                                <tr>
                                    <td>{$row['sale_number']}</td>
                                    <td>{$row['user_name']}</td>
                                    <td>Rs. {$row['total_amount']}</td>
                                    <td>{$row['payment_method']}</td>
                                    <td>{$row['sale_date']}</td>
                                    <td>{$row['branch_name']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No sales found</td></tr>";
                        }
                        ?>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

        <!-- REPAIR REPORT -->
        <div class="card">
            <div class="card-header">
                <h2>🔧 Repair Report</h2>
            </div>

            <div class="card-body">

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Repair No</th>
                            <th>Customer</th>
                            <th>Device</th>
                            <th>Status</th>
                            <th>Cost</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                    $repairQuery = "
                        SELECT repair_number, customer_name, device_name, status, repair_cost, registered_date
                        FROM repairs
                        ORDER BY id DESC LIMIT 20
                    ";
                    $repairs = $conn->query($repairQuery);

                    if ($repairs->num_rows > 0) {
                        while ($row = $repairs->fetch_assoc()) {

                            $badge = $row['status'] == 'completed'
                                ? "<span class='badge badge-success'>Completed</span>"
                                : "<span class='badge badge-warning'>Pending</span>";

                            echo "
                            <tr>
                                <td>{$row['repair_number']}</td>
                                <td>{$row['customer_name']}</td>
                                <td>{$row['device_name']}</td>
                                <td>$badge</td>
                                <td>Rs. {$row['repair_cost']}</td>
                                <td>{$row['registered_date']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No repair records found</td></tr>";
                    }
                    ?>
                    </tbody>

                </table>

            </div>
        </div>

    </div>

</div>

</body>
</html>



.content {
    padding: 40px;
    max-width: 1200px;
    margin: auto;
}

.page-header h1 {
    color: var(--primary);
    margin-bottom: 5px;
}

.stats-grid {
    margin-bottom: 30px;
}

.card {
    margin-bottom: 30px;
}

.card-header h2 {
    color: var(--text);
}

.data-table th, .data-table td {
    padding: 14px;
    vertical-align: middle;
}

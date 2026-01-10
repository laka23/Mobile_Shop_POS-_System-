<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$branch_id = $_SESSION['branch_id'];
$message = '';

// Register new repair
if(isset($_POST['register_repair'])) {
    $repair_number = generate_repair_number();
    $customer_name = $_POST['customer_name'];
    $device_name = $_POST['device_name'];
    $imei_last5 = $_POST['imei_last5'];
    $error_description = $_POST['error_description'];
    $repair_cost = $_POST['repair_cost'];
    $parts_replaced = $_POST['parts_replaced'];
    
    $stmt = $conn->prepare("INSERT INTO repairs (repair_number, branch_id, user_id, customer_name, device_name, imei_last5, error_description, repair_cost, parts_replaced) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiisssds", $repair_number, $branch_id, $_SESSION['user_id'], $customer_name, $device_name, $imei_last5, $error_description, $repair_cost, $parts_replaced);
    
    if($stmt->execute()) {
        $message = "Repair registered successfully! Repair ID: $repair_number";
    }
}

// Mark as completed
if(isset($_GET['complete'])) {
    $repair_id = $_GET['complete'];
    $conn->query("UPDATE repairs SET status='completed', completed_date=NOW() WHERE id=$repair_id AND branch_id=$branch_id");
    $message = "Repair marked as completed!";
}

// Get repairs list
$repairs = $conn->query("SELECT * FROM repairs 
                         WHERE branch_id=$branch_id 
                         ORDER BY registered_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repairs Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/user_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>🔧 Repairs Management</h1>
                <p>Register and track device repairs</p>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Register Repair Form -->
            <div class="card">
                <div class="card-header">
                    <h2>📝 Register New Repair (F4)</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Customer Name:</label>
                                <input type="text" name="customer_name" required class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Device Name:</label>
                                <input type="text" name="device_name" required class="form-control" placeholder="e.g. Samsung Galaxy S21">
                            </div>
                        </div>
                        
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>IMEI Last 5 Digits:</label>
                                <input type="text" name="imei_last5" maxlength="5" pattern="[0-9]{5}" class="form-control" placeholder="12345">
                            </div>
                            
                            <div class="form-group">
                                <label>Repair Cost (Rs):</label>
                                <input type="number" name="repair_cost" required step="0.01" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Error/Issue Description:</label>
                            <textarea name="error_description" required class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Parts Replaced (Optional):</label>
                            <textarea name="parts_replaced" class="form-control" rows="2" placeholder="e.g. Screen, Battery"></textarea>
                        </div>
                        
                        <button type="submit" name="register_repair" class="btn btn-primary">Register Repair</button>
                    </form>
                </div>
            </div>
            
            <!-- Repairs List -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 Repair Records</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Repair ID</th>
                                <th>Customer</th>
                                <th>Device</th>
                                <th>IMEI</th>
                                <th>Issue</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($repair = $repairs->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $repair['repair_number']; ?></strong></td>
                                <td><?php echo $repair['customer_name']; ?></td>
                                <td><?php echo $repair['device_name']; ?></td>
                                <td><?php echo $repair['imei_last5']; ?></td>
                                <td><?php echo substr($repair['error_description'], 0, 30) . '...'; ?></td>
                                <td><?php echo CURRENCY . number_format($repair['repair_cost'], 2); ?></td>
                                <td>
                                    <?php if($repair['status'] == 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($repair['registered_date'])); ?></td>
                                <td>
                                    <?php if($repair['status'] == 'pending'): ?>
                                        <a href="?complete=<?php echo $repair['id']; ?>" 
                                           onclick="return confirm('Mark as completed?')" 
                                           class="btn btn-sm btn-success">Complete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
    // F4 key to focus on customer name input
    document.addEventListener('keydown', function(e) {
        if(e.key === 'F4') {
            e.preventDefault();
            document.querySelector('input[name="customer_name"]').focus();
        }
    });
    </script>
</body>
</html>
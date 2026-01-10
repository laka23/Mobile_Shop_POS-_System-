<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$branch_id = $_SESSION['branch_id'];
$message = '';

// Process return
if(isset($_POST['process_return'])) {
    $return_number = generate_return_number();
    $product_id = $_POST['product_id'];
    $customer_name = $_POST['customer_name'];
    $quantity = $_POST['quantity'];
    $reason = $_POST['reason'];
    $not_working = isset($_POST['not_working']) ? 1 : 0;
    $replacement_given = isset($_POST['replacement_given']) ? 1 : 0;
    $replacement_product_id = $replacement_given ? $_POST['replacement_product_id'] : NULL;
    $replacement_quantity = $replacement_given ? $_POST['replacement_quantity'] : NULL;
    
    // Insert return record
    $stmt = $conn->prepare("INSERT INTO returns (return_number, branch_id, user_id, product_id, customer_name, quantity, reason, replacement_given, replacement_product_id, replacement_quantity, not_working) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiisisisii", $return_number, $branch_id, $_SESSION['user_id'], $product_id, $customer_name, $quantity, $reason, $replacement_given, $replacement_product_id, $replacement_quantity, $not_working);
    
    if($stmt->execute()) {
        // Update stock based on return type
        if($not_working) {
            // If not working, deduct from stock (damaged)
            $conn->query("UPDATE stock SET quantity = quantity - $quantity WHERE product_id=$product_id AND branch_id=$branch_id");
        } else if(!$replacement_given) {
            // If no replacement, add back to stock
            $conn->query("UPDATE stock SET quantity = quantity + $quantity WHERE product_id=$product_id AND branch_id=$branch_id");
        }
        
        // If replacement given, deduct replacement product stock
        if($replacement_given) {
            $conn->query("UPDATE stock SET quantity = quantity - $replacement_quantity WHERE product_id=$replacement_product_id AND branch_id=$branch_id");
        }
        
        $message = "Return processed successfully! Return ID: $return_number";
    }
}

// Get returns list
$returns = $conn->query("SELECT r.*, p.name as product_name, rp.name as replacement_product_name 
                         FROM returns r 
                         JOIN products p ON r.product_id = p.id 
                         LEFT JOIN products rp ON r.replacement_product_id = rp.id 
                         WHERE r.branch_id=$branch_id 
                         ORDER BY r.return_date DESC");

// Get available products for dropdown
$products = $conn->query("SELECT p.id, p.name, s.quantity FROM products p 
                          JOIN stock s ON p.id = s.product_id 
                          WHERE s.branch_id=$branch_id AND s.quantity > 0 
                          ORDER BY p.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/user_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>↩️ Returns Management</h1>
                <p>Process customer returns and replacements</p>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Process Return Form -->
            <div class="card">
                <div class="card-header">
                    <h2>📝 Process Return</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="returnForm">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Select Product to Return:</label>
                                <select name="product_id" required class="form-control">
                                    <option value="">Select Product</option>
                                    <?php 
                                    $prods = $conn->query("SELECT p.id, p.name FROM products p 
                                                          JOIN stock s ON p.id = s.product_id 
                                                          WHERE s.branch_id=$branch_id 
                                                          ORDER BY p.name");
                                    while($prod = $prods->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $prod['id']; ?>"><?php echo $prod['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Customer Name:</label>
                                <input type="text" name="customer_name" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Return Quantity:</label>
                                <input type="number" name="quantity" required min="1" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Reason for Return:</label>
                                <input type="text" name="reason" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="not_working" id="notWorkingCheck">
                                <span>Not Working (Shop Test) - Will deduct from stock</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="replacement_given" id="replacementCheck" onchange="toggleReplacement()">
                                <span>Replacement Given</span>
                            </label>
                        </div>
                        
                        <div id="replacementSection" style="display:none;">
                            <div class="form-row-2">
                                <div class="form-group">
                                    <label>Replacement Product:</label>
                                    <select name="replacement_product_id" class="form-control">
                                        <option value="">Select Product</option>
                                        <?php 
                                        $prods2 = $conn->query("SELECT p.id, p.name, s.quantity FROM products p 
                                                               JOIN stock s ON p.id = s.product_id 
                                                               WHERE s.branch_id=$branch_id AND s.quantity > 0 
                                                               ORDER BY p.name");
                                        while($prod2 = $prods2->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $prod2['id']; ?>"><?php echo $prod2['name']; ?> (Stock: <?php echo $prod2['quantity']; ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Replacement Quantity:</label>
                                    <input type="number" name="replacement_quantity" min="1" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="process_return" class="btn btn-primary">Process Return</button>
                    </form>
                </div>
            </div>
            
            <!-- Returns List -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 Return Records</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Return ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Reason</th>
                                <th>Replacement</th>
                                <th>Not Working</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($return = $returns->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $return['return_number']; ?></strong></td>
                                <td><?php echo $return['customer_name']; ?></td>
                                <td><?php echo $return['product_name']; ?></td>
                                <td><?php echo $return['quantity']; ?></td>
                                <td><?php echo $return['reason'] ?: '-'; ?></td>
                                <td>
                                    <?php if($return['replacement_given']): ?>
                                        <span class="badge badge-info">Yes - <?php echo $return['replacement_product_name']; ?> (<?php echo $return['replacement_quantity']; ?>)</span>
                                    <?php else: ?>
                                        <span class="badge">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($return['not_working']): ?>
                                        <span class="badge badge-danger">Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($return['return_date'])); ?></td>
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
    function toggleReplacement() {
        const checkbox = document.getElementById('replacementCheck');
        const section = document.getElementById('replacementSection');
        section.style.display = checkbox.checked ? 'block' : 'none';
        
        // Make replacement fields required/not required
        const fields = section.querySelectorAll('input, select');
        fields.forEach(field => {
            field.required = checkbox.checked;
        });
    }
    </script>
</body>
</html>
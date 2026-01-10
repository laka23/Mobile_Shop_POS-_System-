<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Add new branch
if(isset($_POST['add_branch'])) {
    $branch_count = $conn->query("SELECT COUNT(*) as count FROM branches WHERE status='active'")->fetch_assoc()['count'];
    
    if($branch_count >= MAX_BRANCHES) {
        $error = "Maximum " . MAX_BRANCHES . " branches allowed!";
    } else {
        $name = $_POST['name'];
        $branch_code = strtoupper($_POST['branch_code']);
        $location = $_POST['location'];
        
        $stmt = $conn->prepare("INSERT INTO branches (name, branch_code, location) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $branch_code, $location);
        
        if($stmt->execute()) {
            $message = "Branch added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Delete branch
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("UPDATE branches SET status='inactive' WHERE id=$id");
    $message = "Branch deleted successfully!";
}

$branches = $conn->query("SELECT * FROM branches WHERE status='active' ORDER BY created_at DESC");
$branch_count = $conn->query("SELECT COUNT(*) as count FROM branches WHERE status='active'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>🏢 Branch Management</h1>
                <p>Manage your shop branches (<?php echo $branch_count; ?>/<?php echo MAX_BRANCHES; ?>)</p>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Add New Branch</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="form-inline">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Branch Name:</label>
                                <input type="text" name="name" required class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Branch Code (3 letters):</label>
                                <input type="text" name="branch_code" required maxlength="3" 
                                       pattern="[A-Za-z]{3}" class="form-control" style="text-transform:uppercase">
                            </div>
                            
                            <div class="form-group">
                                <label>Location:</label>
                                <input type="text" name="location" class="form-control">
                            </div>
                            
                            <button type="submit" name="add_branch" class="btn btn-primary">
                                Add Branch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Active Branches</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Branch Name</th>
                                <th>Code</th>
                                <th>Location</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($branch = $branches->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $branch['id']; ?></td>
                                <td><?php echo $branch['name']; ?></td>
                                <td><span class="badge badge-primary"><?php echo $branch['branch_code']; ?></span></td>
                                <td><?php echo $branch['location']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($branch['created_at'])); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $branch['id']; ?>" 
                                       onclick="return confirm('Delete this branch?')" 
                                       class="btn btn-sm btn-danger">Delete</a>
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
</body>
</html>
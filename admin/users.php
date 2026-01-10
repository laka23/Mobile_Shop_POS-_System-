<?php
require_once '../config/database.php';
require_once '../config/config.php';
check_admin();

$db = new Database();
$conn = $db->getConnection();

$message = '';

// Add user
if(isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    $branch_id = $_POST['branch_id'] ?: NULL;
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role, branch_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $password, $full_name, $role, $branch_id);
    if($stmt->execute()) {
        $message = "User added successfully!";
    }
}

// Delete user
if(isset($_GET['delete'])) {
    $conn->query("UPDATE users SET status='inactive' WHERE id={$_GET['delete']}");
    $message = "User deleted successfully!";
}

$users = $conn->query("SELECT u.*, b.name as branch_name FROM users u 
                       LEFT JOIN branches b ON u.branch_id = b.id 
                       WHERE u.status='active' ORDER BY u.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>👥 User Management</h1>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Add New User/Employee</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="form-inline">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Username:</label>
                                <input type="text" name="username" required class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Password:</label>
                                <input type="password" name="password" required class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Full Name:</label>
                                <input type="text" name="full_name" required class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Role:</label>
                                <select name="role" required class="form-control">
                                    <option value="user">User/Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Branch:</label>
                                <select name="branch_id" class="form-control">
                                    <option value="">All Branches</option>
                                    <?php 
                                    $branches = $conn->query("SELECT * FROM branches WHERE status='active' ORDER BY name");
                                    while($branch = $branches->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $branch['id']; ?>"><?php echo $branch['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Active Users</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Branch</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['full_name']; ?></td>
                                <td><span class="badge badge-<?php echo $user['role']=='admin'?'primary':'info'; ?>"><?php echo strtoupper($user['role']); ?></span></td>
                                <td><?php echo $user['branch_name'] ?: 'All Branches'; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       onclick="return confirm('Delete this user?')" 
                                       class="btn btn-sm btn-danger">Delete</a>
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
</body>
</html>
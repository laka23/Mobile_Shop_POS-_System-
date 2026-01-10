<?php
// =========================
// DATABASE CONNECTION
// =========================
$host = "sql100.infinityfree.com";
$user = "if0_40600774";
$pass = "Gxsm0iUq0kbA";
$db   = "if0_40600774_license_system";

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_errno) {
    die("DB Connection Failed: " . $conn->connect_error);
}

// =========================
// PROCESS UPDATE REQUEST
// =========================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE license_keys SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    echo "<script>alert('License Updated Successfully!'); window.location='edit_license.php?id=$id';</script>";
    exit;
}

// =========================
// FETCH LICENSE DETAILS
// =========================
$id = $_GET['id'];

$res = $conn->query("SELECT * FROM license_keys WHERE id='$id'");
$license = $res->fetch_assoc();

if (!$license) {
    die("❌ License not found!");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit License Status</title>

<style>
    body {
        font-family: "Poppins", sans-serif;
        background: #f3f4f6;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 550px;
        margin: 40px auto;
        padding: 25px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #374151;
    }

    .form-group {
        margin-bottom: 18px;
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #374151;
    }

    input, select {
        width: 100%;
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 15px;
        outline: none;
        transition: 0.2s;
    }

    input:focus, select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 4px #6366f1;
    }

    .btn {
        width: 100%;
        padding: 12px;
        background: #4f46e5;
        color: white;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn:hover {
        background: #3730a3;
    }

    .detail-box {
        background: #f1f5f9;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 15px;
    }

</style>

</head>
<body>

<div class="container">

    <h2>📝 Edit License Status</h2>

    <!-- Show License Details -->
    <div class="detail-box">
        <strong>License Key:</strong> <?php echo $license['license_key']; ?><br>
        <strong>Company:</strong> <?php echo $license['company_name']; ?><br>
        <strong>Type:</strong> <?php echo $license['license_type']; ?><br>
        <strong>Max Devices:</strong> <?php echo $license['max_devices']; ?><br>
        <strong>Max Branches:</strong> <?php echo $license['max_branches']; ?><br>
        <strong>Current Status:</strong> 
            <span style="color: 
                <?php 
                    if ($license['status'] == 'active') echo 'green';
                    elseif ($license['status'] == 'inactive') echo 'orange';
                    else echo 'red';
                ?>">
                <?php echo strtoupper($license['status']); ?>
            </span>
    </div>

    <!-- Status Edit Form -->
    <form method="POST" action="">

        <input type="hidden" name="id" value="<?php echo $license['id']; ?>">

        <div class="form-group">
            <label>Change Status:</label>
            <select name="status" required>
                <option value="active"   <?php if($license['status']=='active') echo 'selected'; ?>>Active</option>
                <option value="inactive" <?php if($license['status']=='inactive') echo 'selected'; ?>>Inactive</option>
                <option value="expired"  <?php if($license['status']=='expired') echo 'selected'; ?>>Expired</option>
            </select>
        </div>

        <button type="submit" class="btn">Update Status</button>

    </form>

</div>

</body>
</html>

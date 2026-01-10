<?php

$host = "sql100.infinityfree.com";
$user = "if0_40600774";
$pass = "Gxsm0iUq0kbA";
$db   = "if0_40600774_license_system";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
    die("DB Connection Failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $company  = $_POST['company_name'];
    $type     = $_POST['license_type']; 
    $branches = intval($_POST['max_branches']);
    $devices  = intval($_POST['max_devices']);
    $expiry   = $_POST['expiry_date'];
    $status   = $_POST['status'];


    if ($expiry == "" || $expiry == "null") {
        $expiry = null;
    }

    // Generate License Key
    $license = "LIC-" . strtoupper(bin2hex(random_bytes(8)));

    $stmt = $conn->prepare("
        INSERT INTO license_keys 
        (license_key, company_name, license_type, max_branches, max_devices, expiry_date, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssisss", $license, $company, $type, $branches, $devices, $expiry, $status);
    
    if ($stmt->execute()) {
        $message = "License Created Successfully! ✔ Key: $license";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create License</title>

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

    .msg {
        padding: 12px;
        background: #d1fae5;
        color: #065f46;
        border-left: 6px solid #10b981;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 14px;
    }
</style>

</head>
<body>

<div class="container">

    <h2>📝 Create New License</h2>

    <?php if ($message != ""): ?>
        <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-group">
            <label>Company Name:</label>
            <input type="text" name="company_name" required placeholder="Enter company name">
        </div>

        <div class="form-group">
            <label>License Type:</label>
            <select name="license_type" required>
                <option value="permanent">Permanent (Lifetime)</option>
                <option value="subscription">Subscription</option>
            </select>
        </div>

        <div class="form-group">
            <label>Max Branches:</label>
            <input type="number" name="max_branches" required min="1" value="1">
        </div>

        <div class="form-group">
            <label>Max Devices:</label>
            <input type="number" name="max_devices" required min="1" value="1">
        </div>

        <div class="form-group">
            <label>Expiry Date (Only for Subscription):</label>
            <input type="date" name="expiry_date">
        </div>

        <div class="form-group">
            <label>Status:</label>
            <select name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="expired">Expired</option>
            </select>
        </div>

        <button type="submit" class="btn">Generate License</button>

        <p style="text-align:center; margin-top:10px; color:#64748b;">
            License key will be generated automatically ✔
        </p>

    </form>
</div>

</body>
</html>

<?php
require_once 'config/database.php';
require_once 'config/config.php';

$db = new Database();
$conn = $db->getConnection();

$error = '';

// If system already activated → go to login
$check = $conn->query("SELECT * FROM license WHERE status='active'");
if ($check->num_rows > 0) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $registration_code = trim($_POST['registration_code']);
    $company_name = trim($_POST['company_name']);

   
    $device_id = md5(php_uname('n') . php_uname('m') . gethostbyname(gethostname()));

    $api_url = "https://mobilepos.xo.je/license-api/verify_license.php?key=$registration_code&device_id=$device_id";

    
    $response = @file_get_contents($api_url);

    if ($response === FALSE) {
        $error = "Cannot connect to license server!";
    } else {
        $data = json_decode($response, true);

        
        if (!empty($data['valid']) && $data['valid'] === true) {

          
            $stmt = $conn->prepare("
                INSERT INTO license (registration_code, company_name, activation_date, status)
                VALUES (?, ?, NOW(), 'active')
            ");
            $stmt->bind_param("ss", $registration_code, $company_name);
            $stmt->execute();

        
            header("Location: login.php?activated=1");
            exit();

        } else {
            
            $error = $data['message'] ?? "Invalid or expired license key!";
        }
    }
}
?>


<!-- 🔥 HTML PART — ACTIVATION PAGE UI 🔥 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Activation - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="activation-page">

    <div class="activation-container">
        <div class="activation-card">

            <h1>🔐 System Activation</h1>
            <p>Enter your license key to activate the POS system</p>

            <!-- ERROR MESSAGE -->
            <?php if(!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- ACTIVATION FORM -->
            <form method="POST" action="">
                
                <div class="form-group">
                    <label>Business / Company Name:</label>
                    <input type="text" name="company_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>License Key:</label>
                    <input type="text" name="registration_code" class="form-control" 
                           placeholder="Enter your license key" required>
                    <small>Your software provider will give you this key.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Activate System</button>
            </form>

            <p style="margin-top:15px; text-align:center; color:#ffffff;">
                Need <a href="" style="color: green !important;"> help?</a> Contact your software provider.
            </p>

        </div>
    </div>

</body>
</html>

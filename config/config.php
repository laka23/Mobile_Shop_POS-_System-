<?php
session_start();
date_default_timezone_set('Asia/Colombo');

define('SITE_NAME', 'Mobile Shop POS');
define('_SITE_NAME','Nextora Team');
define('CURRENCY', 'Rs.');
define('MAX_BRANCHES', 5);

// License validation
function check_license() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $result = $conn->query("SELECT * FROM license WHERE status = 'active' LIMIT 1");
    if($result->num_rows == 0) {
        header('Location: activate.php');
        exit();
    }
}

// Login check
function check_login() {
    if(!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

// Admin check
function check_admin() {
    check_login();
    if($_SESSION['role'] != 'admin') {
        header('Location: ../user/dashboard.php');
        exit();
    }
}

// Generate unique barcode
function generate_barcode($branch_code, $product_id) {
    $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return $branch_code . str_pad($product_id, 5, '0', STR_PAD_LEFT) . $random;
}

// Generate sale number
function generate_sale_number() {
    return 'SAL' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

// Generate repair number
function generate_repair_number() {
    return 'REP' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

// Generate return number
function generate_return_number() {
    return 'RET' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}
?>
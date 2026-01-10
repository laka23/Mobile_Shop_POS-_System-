<?php
header("Content-Type: application/json");

$conn = new mysqli("sql100.infinityfree.com", "if0_40600774", "Gxsm0iUq0kbA", "if0_40600774_license_system");

// READ PARAMETERS SAFELY
$key = isset($_GET['key']) ? trim($_GET['key']) : '';
$device_id = isset($_GET['device_id']) ? trim($_GET['device_id']) : '';

if ($key === '') {
    echo json_encode(["valid" => false, "message" => "No license key received"]);
    exit;
}

// FETCH LICENSE
$stmt = $conn->prepare("SELECT * FROM license_keys WHERE license_key=? AND status='active' LIMIT 1");
$stmt->bind_param("s", $key);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["valid" => false, "message" => "Invalid license"]);
    exit;
}

$license = $res->fetch_assoc();

// DEVICE LIMIT CHECK
$stmt2 = $conn->prepare("SELECT COUNT(*) AS cnt FROM license_devices WHERE license_key=?");
$stmt2->bind_param("s", $key);
$stmt2->execute();
$dev = $stmt2->get_result()->fetch_assoc();

if ($dev['cnt'] >= $license['max_devices']) {
    echo json_encode(["valid" => false, "message" => "Device limit reached"]);
    exit;
}

// REGISTER DEVICE
$stmt3 = $conn->prepare("INSERT INTO license_devices (license_key, device_id) VALUES (?, ?)");
$stmt3->bind_param("ss", $key, $device_id);
$stmt3->execute();

// SUCCESS
echo json_encode([
    "valid" => true,
    "company_name" => $license['company_name'],
    "license_type" => $license['license_type'],
    "max_branches" => $license['max_branches'],
    "max_devices" => $license['max_devices'],
    "expires" => $license['expiry_date']
]);

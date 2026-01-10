<?php
require_once '../../config/database.php';
require_once '../../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$query = $_GET['q'] ?? '';
$branch_id = $_GET['branch_id'] ?? $_SESSION['branch_id'];

if(strlen($query) < 2) {
    echo json_encode([]);
    exit();
}


$search = "%$query%";
$stmt = $conn->prepare("SELECT s.*, p.name, p.id as product_id 
                        FROM stock s 
                        JOIN products p ON s.product_id = p.id 
                        WHERE s.branch_id = ? 
                        AND (p.name LIKE ? OR s.barcode LIKE ?) 
                        AND s.quantity > 0 
                        ORDER BY p.name 
                        LIMIT 20");
$stmt->bind_param("iss", $branch_id, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['product_id'],
        'name' => $row['name'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'discount' => $row['discount'],
        'barcode' => $row['barcode']
    ];
}

header('Content-Type: application/json');
echo json_encode($products);
?>
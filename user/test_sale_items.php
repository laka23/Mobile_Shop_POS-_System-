<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../config/config.php';

$db = new Database();
$conn = $db->getConnection();

// Get the latest sale
$latest_sale = $conn->query("SELECT * FROM sales ORDER BY id DESC LIMIT 1")->fetch_assoc();

echo "<h2>Latest Sale Details:</h2>";
echo "<pre>";
print_r($latest_sale);
echo "</pre>";

echo "<h2>Sale Items in Database:</h2>";
$sale_id = $latest_sale['id'];
$items = $conn->query("SELECT * FROM sale_items WHERE sale_id = $sale_id");

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Product ID</th><th>Manual Item Name</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>";

while($item = $items->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $item['id'] . "</td>";
    echo "<td>" . $item['product_id'] . "</td>";
    echo "<td><strong>" . ($item['manual_item_name'] ?? 'NULL') . "</strong></td>";
    echo "<td>" . $item['quantity'] . "</td>";
    echo "<td>" . $item['price'] . "</td>";
    echo "<td>" . $item['subtotal'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Query Test - What print_invoice.php sees:</h2>";
$test_query = "SELECT si.*, 
                COALESCE(si.manual_item_name, p.name) as item_name,
                si.manual_item_name IS NOT NULL as is_manual
                FROM sale_items si 
                LEFT JOIN products p ON si.product_id = p.id 
                WHERE si.sale_id = $sale_id";

$test_result = $conn->query($test_query);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Item Name</th><th>Is Manual</th><th>Product ID</th><th>Manual Name</th><th>Quantity</th><th>Price</th></tr>";

while($item = $test_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>" . $item['item_name'] . "</strong></td>";
    echo "<td>" . ($item['is_manual'] ? 'YES' : 'NO') . "</td>";
    echo "<td>" . $item['product_id'] . "</td>";
    echo "<td>" . ($item['manual_item_name'] ?? 'NULL') . "</td>";
    echo "<td>" . $item['quantity'] . "</td>";
    echo "<td>" . $item['price'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>If manual items are NULL, the problem is in pos.php when saving</h3>";
echo "<h3>If manual items show here but not in print_invoice.php, the problem is in print_invoice.php</h3>";
?>
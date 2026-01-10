<?php

require_once '../config/database.php';
require_once '../config/config.php';
check_login();

$db = new Database();
$conn = $db->getConnection();

$sale_id = $_GET['sale_id'] ?? 0;

// Get sale details
$sale = $conn->query("SELECT s.*, b.name as branch_name, b.branch_code, b.location, u.full_name as cashier 
                      FROM sales s 
                      JOIN branches b ON s.branch_id = b.id 
                      JOIN users u ON s.user_id = u.id 
                      WHERE s.id = $sale_id")->fetch_assoc();

if(!$sale) {
    echo "Sale not found!";
    exit();
}

// Get sale items (including manual entries with product_id = 0)
$items = $conn->query("SELECT si.*, 
                       CASE 
                           WHEN si.product_id = 0 THEN si.manual_item_name 
                           ELSE p.name 
                       END as product_name,
                       si.product_id
                       FROM sale_items si 
                       LEFT JOIN products p ON si.product_id = p.id 
                       WHERE si.sale_id = $sale_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $sale['sale_number']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            padding: 20px;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .invoice {
            border: 2px solid #000;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 12px;
            margin: 3px 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }
        
        .items-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }
        
        .items-table th,
        .items-table td {
            text-align: left;
            padding: 8px 5px;
            font-size: 12px;
        }
        
        .items-table th {
            border-bottom: 2px solid #000;
            border-top: 2px solid #000;
        }
        
        .items-table tr {
            border-bottom: 1px dashed #ccc;
        }
        
        .manual-badge {
            background: #3b82f6;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            margin-left: 5px;
        }
        
        .totals {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #000;
            font-size: 11px;
        }
        
        .print-btn {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-btn button {
            padding: 12px 30px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }
        
        @media print {
            .print-btn {
                display: none;
            }
            
            body {
                padding: 0;
            }
            
            .invoice {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">🖨️ Print Invoice</button>
        <button onclick="window.close()" style="background: #dc2626;">❌ Close</button>
    </div>
    
    <div class="invoice">
        <div class="header">
            <h1>📱 <?php echo SITE_NAME; ?></h1>
            <p><?php echo $sale['branch_name']; ?> (<?php echo $sale['branch_code']; ?>)</p>
            <p><?php echo $sale['location']; ?></p>
            <p style="margin-top: 10px; font-weight: bold;">SALES INVOICE</p>
        </div>
        
        <div class="info-row">
            <span>Invoice No:</span>
            <span><strong><?php echo $sale['sale_number']; ?></strong></span>
        </div>
        
        <div class="info-row">
            <span>Date:</span>
            <span><?php echo date('Y-m-d H:i:s', strtotime($sale['sale_date'])); ?></span>
        </div>
        
        <div class="info-row">
            <span>Cashier:</span>
            <span><?php echo $sale['cashier']; ?></span>
        </div>
        
        <div class="info-row">
            <span>Payment:</span>
            <span><strong><?php echo strtoupper($sale['payment_method']); ?></strong></span>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Disc%</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                $total_discount = 0;
                while($item = $items->fetch_assoc()): 
                    $item_total = $item['subtotal'];
                    $original_total = $item['price'] * $item['quantity'];
                    $discount_amount = $original_total - $item_total;
                    $subtotal += $original_total;
                    $total_discount += $discount_amount;
                    
                    // Check if manual entry
                    $is_manual = ($item['product_id'] == 0);
                    $display_name = $item['product_name'] ? $item['product_name'] : 'Manual Item';
                ?>
                <tr>
                    <td>
                        <?php echo $display_name; ?>
                        <?php if($is_manual): ?>
                            <span class="manual-badge">MANUAL</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo CURRENCY . number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['discount']; ?>%</td>
                    <td><?php echo CURRENCY . number_format($item_total, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span><?php echo CURRENCY . number_format($subtotal, 2); ?></span>
            </div>
            
            <?php if($total_discount > 0): ?>
            <div class="total-row">
                <span>Discount:</span>
                <span>- <?php echo CURRENCY . number_format($total_discount, 2); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="total-row grand-total">
                <span>GRAND TOTAL:</span>
                <span><?php echo CURRENCY . number_format($sale['total_amount'], 2); ?></span>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Please come again</p>
            <p style="margin-top: 10px;">Powered by <?php echo _SITE_NAME; ?></p>
        </div>
    </div>
    
    <div class="print-btn">
        <a href="pos.php" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px;">✅ New Sale</a>
    </div>
</body>
</html>
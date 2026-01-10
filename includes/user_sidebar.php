<aside class="sidebar">
    <div class="sidebar-header">
        <h2>User Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':''; ?>">
            <span class="icon">🏪</span> Dashboard
        </a></li>
        <li><a href="pos.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='pos.php'?'active':''; ?>">
            <span class="icon">🛒</span> POS / Sales
        </a></li>
        <li><a href="repairs.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='repairs.php'?'active':''; ?>">
            <span class="icon">🔧</span> Repairs
        </a></li>
        <li><a href="returns.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='returns.php'?'active':''; ?>">
            <span class="icon">↩️</span> Returns
        </a></li>
        <li><a href="stock_view.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='stock_view.php'?'active':''; ?>">
            <span class="icon">📦</span> View Stock
        </a></li>
        <li><a href="add_stock.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='add_stock.php'?'active':''; ?>">
            <span class="icon">➕</span> Add Stock
        </a></li>
        <li><a href="sales_history.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='sales_history.php'?'active':''; ?>">
            <span class="icon">📊</span> Sales History
        </a></li>
    </ul>
</aside>
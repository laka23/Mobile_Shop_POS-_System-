<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':''; ?>">
            <span class="icon">📊</span> Dashboard
        </a></li>
        <li><a href="branches.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='branches.php'?'active':''; ?>">
            <span class="icon">🏢</span> Branches
        </a></li>
        <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='products.php'?'active':''; ?>">
            <span class="icon">📦</span> Products
        </a></li>
        <li><a href="stock.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='stock.php'?'active':''; ?>">
            <span class="icon">📦</span> Stock Management
        </a></li>
        <li><a href="../user/stock_view.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='stock_view.php'?'active':''; ?>">
            <span class="icon">👁️</span> View All Stock
        </a></li>
        <li><a href="../admin/users.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='users.php'?'active':''; ?>">
            <span class="icon">👥</span> Users
        </a></li>
        <li><a href="../user/sales_history.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='sales_history.php'?'active':''; ?>">
            <span class="icon">📈</span> Sales History
        </a></li>
        <li><a href="../admin/low_stock.php"<?php echo basename($_SERVER['PHP_SELF'])=='low_stock.php'?'active':''; ?>">
            <span class="icon">⚠️</span> Low Stock Alert
        </a></li>
        <li><a href="../admin/stock_report.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='reports.php'?'active':''; ?>">
            <span class="icon">📋</span> Reports
        </a></li>
    </ul>
</aside>

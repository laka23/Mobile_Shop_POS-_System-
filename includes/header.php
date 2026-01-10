<?php
if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<header style="background: var(--dark-light); padding: 15px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="margin: 0; color: var(--primary);">📱 <?php echo SITE_NAME; ?></h2>
    </div>
    <div style="display: flex; align-items: center; gap: 20px;">
        <span style="color: var(--text-muted);">
            👤 <?php echo $_SESSION['full_name']; ?> 
            <?php if($_SESSION['role'] == 'admin'): ?>
                <span style="background: var(--primary); padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 5px;">ADMIN</span>
            <?php endif; ?>
        </span>
        <a href="../logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</header>
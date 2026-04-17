<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <h2>Admin Panel</h2>
    <ul class="admin-menu">
        <li><a href="/admin/dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
        <li><a href="/admin/products.php" <?php echo in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'class="active"' : ''; ?>>Products</a></li>
        <li><a href="/admin/categories.php" <?php echo $current_page == 'categories.php' ? 'class="active"' : ''; ?>>Categories</a></li>
        <li><a href="/admin/orders.php" <?php echo in_array($current_page, ['orders.php', 'order-details.php']) ? 'class="active"' : ''; ?>>Orders</a></li>
        <li><a href="/admin/reports.php" <?php echo $current_page == 'reports.php' ? 'class="active"' : ''; ?>>Reports</a></li>
        <li><a href="/admin/users.php" <?php echo in_array($current_page, ['users.php', 'user-details.php']) ? 'class="active"' : ''; ?>>Users</a></li>
        <li><a href="/admin/payments.php" <?php echo $current_page == 'payments.php' ? 'class="active"' : ''; ?>>Payments</a></li>
        <li><a href="/admin/messages.php" <?php echo $current_page == 'messages.php' ? 'class="active"' : ''; ?>>Messages</a></li>
        <li><a href="/admin/subscribers.php" <?php echo $current_page == 'subscribers.php' ? 'class="active"' : ''; ?>>Subscribers</a></li>
        <li><a href="/admin/settings.php" <?php echo $current_page == 'settings.php' ? 'class="active"' : ''; ?>>Settings</a></li>
        <li><a href="/" target="_blank">View Store</a></li>
        <li><a href="/admin/logout.php">Logout</a></li>
    </ul>
</aside>

<?php
$current_page = basename($_SERVER['PHP_SELF']);

if (!function_exists('get_tenant_class')) {
    function get_tenant_class($page_name, $current_page) {
        if ($current_page === $page_name) {
            return "block px-3 py-2 rounded-md text-brand-700 bg-brand-50 dark:text-brand-300 dark:bg-brand-900/30 font-medium transition-colors";
        }
        return "block px-3 py-2 rounded-md text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors";
    }
}
?>
<aside class="w-full md:w-64 flex-shrink-0">
    <div class="glass-panel dark:bg-slate-800/80 dark:border-slate-700 rounded-xl shadow-lg p-6 sticky top-24 transition-colors duration-300">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Tenant Menu</h3>
        <nav class="space-y-2">
            <a href="dashboard.php" class="<?php echo get_tenant_class('dashboard.php', $current_page); ?>">Dashboard</a>
            <a href="browse_properties.php" class="<?php echo get_tenant_class('browse_properties.php', $current_page); ?>">Browse Properties</a>
            <a href="pay_rent.php" class="<?php echo get_tenant_class('pay_rent.php', $current_page); ?>">Pay Rent</a>
            <a href="payments.php" class="<?php echo get_tenant_class('payments.php', $current_page); ?>">Payment History</a>
            <a href="maintenance_request.php" class="<?php echo get_tenant_class('maintenance_request.php', $current_page); ?>">Maintenance</a>
            <a href="history.php" class="<?php echo get_tenant_class('history.php', $current_page); ?>">Records & History</a>
            <a href="notifications.php" class="<?php echo get_tenant_class('notifications.php', $current_page); ?> flex justify-between items-center">
                <span>Notifications</span>
                <?php if (isset($unread_count) && $unread_count > 0): ?>
                    <span class="bg-red-500 text-white rounded-full px-2 py-0.5 text-xs font-bold"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </div>
</aside>

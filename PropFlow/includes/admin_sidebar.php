<?php
$current_page = basename($_SERVER['PHP_SELF']);

if (!function_exists('get_admin_class')) {
    function get_admin_class($page_name, $current_page) {
        if ($current_page === $page_name) {
            return "block px-3 py-2 rounded-md text-brand-700 bg-brand-50 dark:text-brand-300 dark:bg-brand-900/30 font-medium transition-colors";
        }
        return "block px-3 py-2 rounded-md text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors";
    }
}
?>
<aside class="w-full md:w-64 flex-shrink-0">
    <div class="glass-panel dark:bg-slate-800/80 dark:border-slate-700 rounded-xl shadow-lg p-6 sticky top-24 transition-colors duration-300">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Admin Menu</h3>
        <nav class="space-y-2">
            <a href="dashboard.php" class="<?php echo get_admin_class('dashboard.php', $current_page); ?>">System Overview</a>
            <a href="users.php" class="<?php echo get_admin_class('users.php', $current_page); ?>">Users Management</a>
            <a href="properties.php" class="<?php echo get_admin_class('properties.php', $current_page); ?>">Property Moderation</a>
            <a href="tenancies.php" class="<?php echo get_admin_class('tenancies.php', $current_page); ?>">Global Tenancies</a>
            <a href="payments.php" class="<?php echo get_admin_class('payments.php', $current_page); ?>">Payment Ledger</a>
            <a href="maintenance.php" class="<?php echo get_admin_class('maintenance.php', $current_page); ?>">Maintenance</a>
            <a href="reports.php" class="<?php echo get_admin_class('reports.php', $current_page); ?>">Platform Analytics</a>
        </nav>
    </div>
</aside>

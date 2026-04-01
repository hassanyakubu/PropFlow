<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = get_user_id();
$db = new db_connection();

// Mark all unread notifications as read
$update_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id' AND is_read = 0";
$db->db_query($update_sql);

// Number of affected rows (how many were just read)
$just_read_count = $db->db_affected_rows();

$sql = "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC";
$notifications = $db->db_fetch_all($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/tenant_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Notifications</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Updates on rent, maintenance, and announcements.
                    </p>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <?php if ($notifications && count($notifications) > 0): ?>
                    <ul class="divide-y divide-gray-100 dark:divide-slate-700">
                        <?php foreach ($notifications as $notif): ?>
                            <li
                                class="p-6 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors flex items-start gap-4">
                                <div class="bg-brand-50 rounded-full p-3 flex-shrink-0">
                                    <svg class="h-6 w-6 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="text-gray-900 dark:text-white font-medium mb-1">
                                        <?php echo htmlspecialchars($notif['message']); ?>
                                        <?php if ($just_read_count > 0): ?>
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-brand-100 dark:bg-brand-900/30 text-brand-800 dark:text-brand-300">
                                                New
                                            </span>
                                            <?php $just_read_count--; ?>
                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo date('F j, Y, g:i a', strtotime($notif['created_at'])); ?>
                                    </p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-16">
                        <div
                            class="bg-gray-50 dark:bg-slate-700 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-4">
                            <svg class="h-12 w-12 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Notifications</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">You're completely caught up. When your
                            landlord updates maintenance requests or sends rent receipts, updates will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
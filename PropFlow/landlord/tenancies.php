<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

$db = new db_connection();
$user_id = get_user_id();

// Fetch existing tenancies
$sql_tenancies = "SELECT t.*, u.full_name, p.title 
                  FROM tenancies t 
                  JOIN users u ON t.tenant_id = u.user_id 
                  JOIN properties p ON t.property_id = p.property_id 
                  WHERE p.owner_id = '$user_id'
                  ORDER BY t.start_date DESC";
$tenancy_list = $db->db_fetch_all($sql_tenancies);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenancies - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/landlord_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manage Tenancies</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Active tenancies are created automatically when you
                    accept an application.</p>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'reminder_sent'): ?>
                <div
                    class="mb-4 bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-400 p-4 rounded-r shadow-sm">
                    <p class="font-medium">Payment reminder sent successfully!</p>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div
                    class="mb-4 bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400 p-4 rounded-r shadow-sm">
                    <p class="font-medium">Error sending reminder. Please try again.</p>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                <div class="p-4 border-b dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Current Tenancies</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Property</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tenant</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Start Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rent</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            <?php if ($tenancy_list): ?>
                                <?php foreach ($tenancy_list as $row): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($row['full_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('M d, Y', strtotime($row['start_date'])); ?>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 font-semibold">
                                            GH₵ <?php echo number_format($row['rent_amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-gray-300'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if ($row['status'] === 'active'): ?>
                                                <form action="../actions/send_reminder_action.php" method="POST"
                                                    class="inline-block">
                                                    <input type="hidden" name="tenancy_id"
                                                        value="<?php echo $row['tenancy_id']; ?>">
                                                    <button type="submit"
                                                        class="text-brand-600 dark:text-brand-400 hover:text-brand-800 hover:underline">
                                                        Send Reminder
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-gray-400 cursor-not-allowed">Ended</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No active
                                        tenancies</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
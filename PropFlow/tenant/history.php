<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = get_user_id();
$db = new db_connection();

// Get unread notifications count for sidebar
$sql_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$unread_result = $db->db_fetch_one($sql_unread);
$unread_count = $unread_result ? (int) $unread_result['unread_count'] : 0;

// Get Past Tenancies
$past_tenancies = $db->db_fetch_all("SELECT t.*, p.title as property_title, p.address, p.city 
    FROM tenancies t 
    JOIN properties p ON t.property_id = p.property_id 
    WHERE t.tenant_id = $user_id AND t.status = 'ended' 
    ORDER BY t.end_date DESC");

// Get Historical rent payment receipts (only for ended tenancies)
$past_payments = $db->db_fetch_all("SELECT pay.*, p.title as property_title 
    FROM payments pay
    JOIN tenancies t ON pay.tenancy_id = t.tenancy_id
    JOIN properties p ON t.property_id = p.property_id
    WHERE t.tenant_id = $user_id AND t.status = 'ended' AND pay.payment_status = 'paid'
    ORDER BY pay.payment_date DESC");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records & History - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Sidebar -->
        <?php include '../includes/tenant_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow space-y-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Records & History</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Review your past tenancy agreements and receipts.</p>
            </div>

            <!-- Past Tenancy Agreements -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Past Tenancies</h2>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                    <?php if ($past_tenancies && count($past_tenancies) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead class="bg-gray-50 dark:bg-slate-900/50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Property</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Location</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Start Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            End Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rent</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <?php foreach ($past_tenancies as $tenancy): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($tenancy['property_title']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo htmlspecialchars($tenancy['address'] . ', ' . $tenancy['city']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d, Y', strtotime($tenancy['start_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo $tenancy['end_date'] ? date('M d, Y', strtotime($tenancy['end_date'])) : 'Unknown'; ?>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-300">
                                                GH₵
                                                <?php echo number_format($tenancy['rent_amount'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <p>No past tenancies recorded.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Historical Payment Receipts -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Historical Receipts</h2>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                    <?php if ($past_payments && count($past_payments) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead class="bg-gray-50 dark:bg-slate-900/50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Property</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ref ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Method</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <?php foreach ($past_payments as $payment): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($payment['property_title']); ?>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                                <?php echo htmlspecialchars(substr($payment['transaction_reference'], 0, 10)); ?>...
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo ucfirst($payment['payment_method']); ?>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-300">
                                                GH₵
                                                <?php echo number_format($payment['amount'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <p>No historical receipts found for past tenancies.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

$db = new db_connection();
$user_id = get_user_id();

// Get unread notifications count
$sql_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$unread_result = $db->db_fetch_one($sql_unread);
$unread_count = $unread_result ? (int) $unread_result['unread_count'] : 0;

// Fetch tenant's payment history
$sql = "SELECT p.*, prop.title, t.payment_frequency 
        FROM payments p 
        JOIN tenancies t ON p.tenancy_id = t.tenancy_id 
        JOIN properties prop ON t.property_id = prop.property_id 
        WHERE t.tenant_id = '$user_id'
        ORDER BY p.payment_date DESC";

$payments = $db->db_fetch_all($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Sidebar -->
        <?php include '../includes/tenant_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Your Payment History</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Review all past and pending rent payments.</p>
                </div>
                <button onclick="printStatement()"
                    class="bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 font-medium py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Print Statement
                </button>
            </div>

            <!-- Payment Table -->
            <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-xl shadow-sm overflow-hidden"
                id="printArea">
                <div class="print-header hidden text-center mb-8">
                    <h2 class="text-2xl font-bold dark:text-white">Your Rent Payment Statement</h2>
                    <p class="text-gray-500 dark:text-gray-400">Generated:
                        <?php echo date('M d, Y'); ?>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Property</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Amount</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Method</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Reference</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            <?php if ($payments): ?>
                                <?php foreach ($payments as $pay): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('M d, Y', strtotime($pay['payment_date'])); ?>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($pay['title']); ?>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-200">
                                            GH₵
                                            <?php echo number_format($pay['amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo ucfirst($pay['payment_method']); ?>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                            <?php echo $pay['transaction_reference'] ?: 'N/A'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $pay['payment_status'] === 'paid' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400'; ?>">
                                                <?php echo ucfirst($pay['payment_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">You
                                        haven't made any payments yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        function printStatement() {
            var printContent = document.getElementById('printArea').innerHTML;
            var originalContent = document.body.innerHTML;

            document.body.innerHTML = '<div style="padding:40px;">' + printContent + '</div>';

            // Show print header
            var headers = document.getElementsByClassName('print-header');
            for (var i = 0; i < headers.length; i++) {
                headers[i].classList.remove('hidden');
                headers[i].style.display = 'block';
            }

            window.print();

            document.body.innerHTML = originalContent;
            location.reload(); // Reload to restore event listeners
        }
    </script>
</body>

</html>
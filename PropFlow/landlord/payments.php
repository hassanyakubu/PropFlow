<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

$db = new db_connection();
$user_id = get_user_id();

// Filter logic

$property_filter = isset($_GET['property_id']) ? $_GET['property_id'] : 'all';

$sql = "SELECT p.*, prop.title, u.full_name, t.payment_frequency 
        FROM payments p 
        JOIN tenancies t ON p.tenancy_id = t.tenancy_id 
        JOIN properties prop ON t.property_id = prop.property_id 
        JOIN users u ON t.tenant_id = u.user_id 
        WHERE prop.owner_id = '$user_id'";

if ($property_filter !== 'all') {
    $sql .= " AND prop.property_id = '$property_filter'";
}

$sql .= " ORDER BY p.payment_date DESC";

// Fetch properties for filter
$props = $db->db_fetch_all("SELECT property_id, title FROM properties WHERE owner_id = '$user_id'");

$payments = $db->db_fetch_all($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Sidebar -->
        <?php include '../includes/landlord_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Payment History</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">View and filter rent payments across your
                        properties.</p>
                </div>
                <button onclick="printStatement()"
                    class="bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 font-medium py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Print Statement
                </button>
            </div>

            <!-- Filter Bar -->
            <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 p-6 rounded-xl shadow-sm mb-8">
                <form method="GET" class="flex flex-col sm:flex-row gap-4 items-center">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Filter by
                        Property:</label>
                    <select name="property_id" onchange="this.form.submit()"
                        class="block w-full sm:w-64 pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm rounded-md shadow-sm">
                        <option value="all">All Properties</option>
                        <?php if ($props):
                            foreach ($props as $prop): ?>
                                <option value="<?php echo $prop['property_id']; ?>" <?php echo $property_filter == $prop['property_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prop['title']); ?>
                                </option>
                            <?php endforeach; endif; ?>
                    </select>
                </form>
            </div>

            <!-- Payment Table -->
            <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-xl shadow-sm overflow-hidden"
                id="printArea">
                <div class="print-header hidden text-center mb-8">
                    <h2 class="text-2xl font-bold">Rent Payment Statement</h2>
                    <p class="text-gray-500">Generated: <?php echo date('M d, Y'); ?></p>
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
                                    Tenant</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($pay['full_name']); ?>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-200">
                                            GH₵ <?php echo number_format($pay['amount'], 2); ?>
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
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No
                                        payments found for the
                                        selected criteria.</td>
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
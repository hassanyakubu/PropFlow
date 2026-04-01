<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = get_user_id();
$db = new db_connection();

// Get active tenancy
$sql = "SELECT t.*, p.title 
        FROM tenancies t 
        JOIN properties p ON t.property_id = p.property_id 
        WHERE t.tenant_id = '$user_id' AND t.status = 'active'
        LIMIT 1";
$tenancy = $db->db_fetch_one($sql);

// Get pending maintenance requests (optional, but good for context if we need it)
$sql_maint = "SELECT COUNT(*) as count FROM maintenance_requests WHERE tenant_id = '$user_id' AND status != 'resolved'";
$maint_count = $db->db_fetch_one($sql_maint)['count'];

// Get unread notifications count
$sql_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$unread_result = $db->db_fetch_one($sql_unread);
$unread_count = $unread_result ? (int) $unread_result['unread_count'] : 0;

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Rent - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Sidebar -->
        <?php include '../includes/tenant_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Pay Rent</h1>
                <p class="text-gray-500 mt-1">Manage your current and past payments.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">Payment initialization failed: <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($tenancy):
                // Fetch payment history for this tenancy
                $sql_payments = "SELECT * FROM payments WHERE tenancy_id = '{$tenancy['tenancy_id']}' ORDER BY payment_date DESC";
                $payments = $db->db_fetch_all($sql_payments);
                ?>
                <!-- Payment Form Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Payment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-500">Property</p>
                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($tenancy['title']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Rent Amount</p>
                            <p class="font-bold text-brand-600">GH₵ <?php echo number_format($tenancy['rent_amount'], 2); ?>
                            </p>
                        </div>
                    </div>

                    <form action="../actions/initialize_payment.php" method="POST" class="space-y-4">
                        <input type="hidden" name="tenancy_id" value="<?php echo $tenancy['tenancy_id']; ?>">

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount to Pay (GHS)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">GH₵</span>
                                </div>
                                <input type="number" name="amount" id="amount"
                                    value="<?php echo $tenancy['rent_amount']; ?>" step="0.01" required
                                    class="focus:ring-brand-500 focus:border-brand-500 block w-full pl-12 sm:text-sm border-gray-300 rounded-md py-3 text-lg font-medium">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Receipt Email</label>
                            <input type="email" name="email" id="email" value="<?php echo $_SESSION['user_email'] ?? ''; ?>"
                                required readonly
                                class="mt-1 bg-gray-50 focus:ring-brand-500 focus:border-brand-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2">
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            Pay with Paystack
                        </button>
                    </form>
                </div>

                <!-- Payment History Card -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Payment History</h3>
                    </div>
                    <?php if ($payments && count($payments) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Method</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reference</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($payments as $pay): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($pay['payment_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                GH₵ <?php echo number_format($pay['amount'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo ucfirst($pay['payment_method']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                                <?php echo $pay['transaction_reference'] ?: 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $pay['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                    <?php echo ucfirst($pay['payment_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">
                            No payment history found.
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">You do not have an active tenancy.</div>
            <?php endif; ?>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
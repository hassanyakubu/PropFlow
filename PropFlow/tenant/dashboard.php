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
$sql_tenancy = "SELECT t.*, p.title, p.address, p.price, p.property_id 
                FROM tenancies t 
                JOIN properties p ON t.property_id = p.property_id 
                WHERE t.tenant_id = '$user_id' AND t.status = 'active'
                LIMIT 1";
$tenancy = $db->db_fetch_one($sql_tenancy);

// Calculate next rent due dynamically
$next_due_date = 'N/A';
if ($tenancy) {
    try {
        // Fetch the most recent successful payment date
        $last_payment = $db->db_fetch_one("SELECT payment_date FROM payments WHERE tenancy_id = '{$tenancy['tenancy_id']}' AND payment_status = 'paid' ORDER BY payment_date DESC LIMIT 1");

        $base_date_str = $last_payment ? $last_payment['payment_date'] : $tenancy['start_date'];
        $base_date = new DateTime($base_date_str);

        $interval_spec = 'P1M'; // Default monthly
        if (isset($tenancy['payment_frequency'])) {
            if ($tenancy['payment_frequency'] === 'quarterly') {
                $interval_spec = 'P3M';
            } elseif ($tenancy['payment_frequency'] === 'bi-annually') {
                $interval_spec = 'P6M';
            } elseif ($tenancy['payment_frequency'] === 'yearly') {
                $interval_spec = 'P1Y';
            }
        }

        $interval = new DateInterval($interval_spec);

        // The next due date is at least one interval after the base date
        $next_due = clone $base_date;
        $next_due->add($interval);

        // If they are still behind, calculate the true next future date
        $current_date = new DateTime();
        while ($next_due <= $current_date) {
            $next_due->add($interval);
        }

        $next_due_date = $next_due->format('M d, Y');
    } catch (Exception $e) {
        $next_due_date = 'Invalid Date';
    }
}

// Get recent notifications
$sql_notif = "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 5";
$notifications = $db->db_fetch_all($sql_notif);

// Get unread notifications count
$sql_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$unread_result = $db->db_fetch_one($sql_unread);
$unread_count = $unread_result ? (int) $unread_result['unread_count'] : 0;

// Get pending maintenance requests
$sql_maint = "SELECT * FROM maintenance_requests WHERE tenant_id = '$user_id' AND status != 'resolved'";
$maintenance_requests = $db->db_fetch_all($sql_maint);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - PropFlow</title>
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
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome,
                    <?php
                    $tenant_info = $db->db_fetch_one("SELECT full_name FROM users WHERE user_id = '$user_id'");
                    $fname = $tenant_info ? explode(' ', trim($tenant_info['full_name']))[0] : 'Tenant';
                    echo htmlspecialchars($fname ?: get_user_first_name() ?: 'Tenant');
                    ?>
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Here is your current tenancy status.</p>
            </div>

            <!-- Renting Tip Bar -->
            <div id="renting-tip-bar"
                class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-500 p-4 mb-8 hidden rounded-r-lg shadow-sm">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <span class="font-bold">Tip:</span> <span id="tip-text">Loading tip...</span>
                            </p>
                        </div>
                    </div>
                    <button id="refresh-tip-btn"
                        class="ml-auto bg-blue-100 dark:bg-blue-800/50 text-blue-600 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 p-1 rounded-full focus:outline-none transition-colors"
                        title="Next Tip">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <?php if ($tenancy): ?>
                <!-- Active Tenancy Card -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden mb-8 border border-gray-100 dark:border-slate-700">
                    <div class="bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-4">
                        <h2 class="text-white text-lg font-bold">Current Residence</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($tenancy['title']); ?>
                                </h3>
                                <p class="text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <?php echo htmlspecialchars($tenancy['address']); ?>,
                                    <?php echo htmlspecialchars($tenancy['city']); ?>
                                </p>
                            </div>
                            <div class="mt-4 md:mt-0 text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide font-medium">
                                    <?php
                                    $freq_label = 'Monthly Rent';
                                    if (isset($tenancy['payment_frequency'])) {
                                        if ($tenancy['payment_frequency'] === 'quarterly') {
                                            $freq_label = 'Quarterly Rent';
                                        } elseif ($tenancy['payment_frequency'] === 'bi-annually') {
                                            $freq_label = 'Bi-Annual Rent';
                                        } elseif ($tenancy['payment_frequency'] === 'yearly') {
                                            $freq_label = 'Yearly Rent';
                                        }
                                    }
                                    echo $freq_label;
                                    ?>
                                </p>
                                <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">GH₵
                                    <?php echo number_format($tenancy['rent_amount'], 2); ?>
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-100 dark:border-slate-700 pt-6">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Lease Start</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    <?php echo date('M d, Y', strtotime($tenancy['start_date'])); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                    Active
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Next Payment Due</p>
                                <p class="font-medium text-red-600 dark:text-red-400"><?php echo $next_due_date; ?></p>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-4">
                            <a href="pay_rent.php"
                                class="flex-1 bg-brand-600 hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600 text-white text-center font-medium py-2 px-4 rounded-lg shadow-sm transition-colors">
                                Pay Rent
                            </a>
                            <a href="maintenance_request.php"
                                class="flex-1 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 text-center font-medium py-2 px-4 rounded-lg shadow-sm transition-colors">
                                Request Maintenance
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-6 mb-8 rounded-r-lg">
                    <h2 class="text-lg font-bold text-yellow-800 dark:text-yellow-400">No Active Tenancy</h2>
                    <p class="mt-2 text-yellow-700 dark:text-yellow-500">You are not currently renting a property. Browse
                        available properties to
                        find your next home.</p>
                    <a href="browse_properties.php"
                        class="inline-block mt-4 bg-yellow-100 dark:bg-yellow-800/50 text-yellow-800 dark:text-yellow-300 font-semibold py-2 px-4 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-700/50 transition-colors">Browse
                        Properties</a>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Notifications Section -->
                <section
                    class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Recent Notifications</h2>
                        <a href="notifications.php"
                            class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-800">View All</a>
                    </div>
                    <?php if ($notifications && count($notifications) > 0): ?>
                        <ul class="space-y-3">
                            <?php foreach ($notifications as $note): ?>
                                <li
                                    class="flex items-start p-3 <?php echo ($note['is_read'] == 0) ? 'bg-blue-50 dark:bg-slate-700 border-l-2 border-brand-500' : 'bg-gray-50 dark:bg-slate-700/50'; ?> rounded-lg transition-colors">
                                    <div class="flex-shrink-0">
                                        <?php if ($note['is_read'] == 0): ?>
                                            <span class="flex h-2 w-2 relative translate-y-1.5 mr-1">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                                            </span>
                                        <?php else: ?>
                                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-3 w-0 flex-1 pt-0.5">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                            <?php echo htmlspecialchars($note['message']); ?>
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo date('M d, H:i', strtotime($note['created_at'])); ?>
                                        </p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">No new notifications</p>
                    <?php endif; ?>
                </section>

                <!-- Maintenance History -->
                <section
                    class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Maintenance Status</h2>
                        <a href="maintenance_request.php"
                            class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-800">View
                            All</a>
                    </div>
                    <?php if ($maintenance_requests && count($maintenance_requests) > 0): ?>
                        <ul class="space-y-3">
                            <?php foreach ($maintenance_requests as $req): ?>
                                <li
                                    class="flex justify-between items-center p-3 border border-gray-100 dark:border-slate-700/50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                            <?php echo ucfirst($req['issue_category']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo date('M d', strtotime($req['created_at'])); ?>
                                        </p>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php
                                        if ($req['status'] === 'resolved')
                                            echo 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400';
                                        elseif ($req['status'] === 'in_progress')
                                            echo 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400';
                                        else
                                            echo 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300';
                                        ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($req['status'])); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">No active requests</p>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        const allTips = []; // Will store {text, type}
        const tipBar = document.getElementById('renting-tip-bar');
        const tipTextElement = document.getElementById('tip-text');
        let tipInterval;

        function showRandomTip() {
            if (allTips.length === 0) return;
            const randomIndex = Math.floor(Math.random() * allTips.length);
            const tip = allTips[randomIndex];

            // Fade out
            tipTextElement.style.opacity = '0';

            setTimeout(() => {
                tipTextElement.innerText = tip.text;
                tipTextElement.className = tip.type === 'do' ? 'text-green-700 font-medium' : 'text-red-700 font-medium';
                // Fade in
                tipTextElement.style.opacity = '1';
                tipTextElement.style.transition = 'opacity 0.5s';
            }, 200);
        }

        function startRotation() {
            if (tipInterval) clearInterval(tipInterval);
            tipInterval = setInterval(showRandomTip, 60000); // 1 minute
        }

        // Fetching and rotating tips
        fetch('../api/renting_tips.php')
            .then(response => response.json())
            .then(data => {
                if (data.dos && data.donts) {
                    data.dos.forEach(t => allTips.push({ text: t, type: 'do' }));
                    data.donts.forEach(t => allTips.push({ text: t, type: 'dont' }));

                    tipBar.classList.remove('hidden');
                    showRandomTip();
                    startRotation();
                }
            })
            .catch(err => console.error('Error loading tips:', err));

        document.getElementById('refresh-tip-btn').addEventListener('click', function () {
            showRandomTip();
            startRotation(); // Reset timer     });
    </script>
</body>

</html>
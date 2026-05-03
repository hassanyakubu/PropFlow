<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'landlord') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new db_connection();

// --- 1. Property Stats & Occupancy Rate ---
$stats = $db->db_fetch_one("SELECT 
    COUNT(*) as total_properties,
    SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_properties
    FROM properties 
    WHERE owner_id = $user_id AND status != 'archived'
");
$total_properties = $stats['total_properties'] ?? 0;
$occupied_properties = $stats['occupied_properties'] ?? 0;
$occupancy_rate = $total_properties > 0 ? round(($occupied_properties / $total_properties) * 100) : 0;

// --- 2. Lifetime Revenue ---
$revenue_query = "SELECT SUM(p.amount) as total_revenue
    FROM payments p
    JOIN tenancies t ON p.tenancy_id = t.tenancy_id
    JOIN properties prop ON t.property_id = prop.property_id
    WHERE prop.owner_id = $user_id AND p.payment_status = 'paid'";
$revenue_data = $db->db_fetch_one($revenue_query);
$total_revenue = $revenue_data['total_revenue'] ?? 0;

// --- 3. Maintenance Insights ---
$maint_stats = $db->db_fetch_one("SELECT 
    COUNT(*) as total_requests,
    SUM(CASE WHEN m.status = 'resolved' THEN 1 ELSE 0 END) as resolved_requests
    FROM maintenance_requests m
    JOIN properties p ON m.property_id = p.property_id
    WHERE p.owner_id = $user_id
");
$total_maint = $maint_stats['total_requests'] ?? 0;
$resolved_maint = $maint_stats['resolved_requests'] ?? 0;
$maintenance_rate = $total_maint > 0 ? round(($resolved_maint / $total_maint) * 100) : 0;

// --- 4. Monthly Revenue for Chart (Last 6 Months) ---
// Using MySQL DATE_FORMAT to group by YYYY-MM
$monthly_query = "
    SELECT 
        DATE_FORMAT(p.payment_date, '%Y-%m') as month_raw,
        DATE_FORMAT(p.payment_date, '%b %Y') as month,
        SUM(p.amount) as monthly_total
    FROM payments p
    JOIN tenancies t ON p.tenancy_id = t.tenancy_id
    JOIN properties prop ON t.property_id = prop.property_id
    WHERE prop.owner_id = $user_id 
      AND p.payment_status = 'paid'
      AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_raw, month
    ORDER BY month_raw ASC
";
$monthly_data = $db->db_fetch_all($monthly_query);

$chart_labels = [];
$chart_values = [];

if ($monthly_data) {
    foreach ($monthly_data as $row) {
        $chart_labels[] = $row['month'];
        $chart_values[] = floatval($row['monthly_total']);
    }
} else {
    // Fallback empty data if no payments recent
    $chart_labels = [date('M Y')];
    $chart_values = [0];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Sidebar -->
        <?php include '../includes/landlord_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reports & Analytics</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Analyze your portfolio's financial and operational
                    performance.</p>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Revenue -->
                <div
                    class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 relative overflow-hidden group">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent dark:from-green-900/10 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Lifetime Revenue</p>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-1">GH₵
                                <?php echo number_format($total_revenue, 2); ?>
                            </h3>
                        </div>
                        <div
                            class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Rate -->
                <div
                    class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 relative overflow-hidden group">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent dark:from-blue-900/10 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupancy Rate</p>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                <?php echo $occupancy_rate; ?>%
                            </h3>
                            <p class="text-xs text-gray-400 mt-1">
                                <?php echo $occupied_properties; ?> of
                                <?php echo $total_properties; ?> properties occupied
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Resolution -->
                <div
                    class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 relative overflow-hidden group">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent dark:from-orange-900/10 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Maintenance Resolution</p>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                <?php echo $maintenance_rate; ?>%
                            </h3>
                            <p class="text-xs text-gray-400 mt-1">
                                <?php echo $resolved_maint; ?> of
                                <?php echo $total_maint; ?> requests resolved
                            </p>
                        </div>
                        <div
                            class="p-3 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div
                class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 mb-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Revenue Over Time (Last 6 Months)</h3>
                <div class="relative h-80 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Prepare data from PHP
            const labels = <?php echo json_encode($chart_labels); ?>;
            const dataValues = <?php echo json_encode($chart_values); ?>;

            // Setup Chart
            const ctx = document.getElementById('revenueChart').getContext('2d');

            // Configure theme based on dark mode class on body
            const isDarkMode = document.body.classList.contains('dark');
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
            const textColor = isDarkMode ? '#cbd5e1' : '#64748b'; // slate-300 / slate-500

            // Create gradient
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(56, 189, 248, 0.5)'); // brand-sky-400
            gradient.addColorStop(1, 'rgba(56, 189, 248, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly Revenue (GH₵)',
                        data: dataValues,
                        borderColor: '#0ea5e9', // brand-500
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0ea5e9',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4 // Smooth curves
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Hide legend to match design
                        },
                        tooltip: {
                            backgroundColor: isDarkMode ? '#1e293b' : '#ffffff',
                            titleColor: isDarkMode ? '#f8fafc' : '#0f172a',
                            bodyColor: isDarkMode ? '#cbd5e1' : '#475569',
                            borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'GH₵ ' + context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: textColor,
                                font: { family: "'Inter', sans-serif" }
                            }
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                color: textColor,
                                font: { family: "'Inter', sans-serif" },
                                callback: function (value, index, values) {
                                    return 'GH₵ ' + value.toLocaleString();
                                }
                            },
                            beginAtZero: true
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });
        });
    </script>
</body>

</html>
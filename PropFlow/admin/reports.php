<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$db = new db_connection();

// --- 1. Total Platform Properties & Occupancy ---
$stats = $db->db_fetch_one("SELECT 
    COUNT(*) as total_properties,
    SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_properties
    FROM properties 
    WHERE status != 'archived'
");
$total_properties = $stats['total_properties'] ?? 0;
$occupied_properties = $stats['occupied_properties'] ?? 0;
$occupancy_rate = $total_properties > 0 ? round(($occupied_properties / $total_properties) * 100) : 0;

// --- 2. Lifetime Platform Transaction Volume ---
$revenue_query = "SELECT SUM(amount) as total_volume
    FROM payments 
    WHERE payment_status = 'paid'";
$revenue_data = $db->db_fetch_one($revenue_query);
$total_volume = $revenue_data['total_volume'] ?? 0;

// --- 3. Platform Maintenance Health ---
$maint_stats = $db->db_fetch_one("SELECT 
    COUNT(*) as total_requests,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_requests
    FROM maintenance_requests
");
$total_maint = $maint_stats['total_requests'] ?? 0;
$resolved_maint = $maint_stats['resolved_requests'] ?? 0;
$maintenance_rate = $total_maint > 0 ? round(($resolved_maint / $total_maint) * 100) : 0;

// --- 4. User Base ---
$user_stats = $db->db_fetch_one("SELECT 
    SUM(CASE WHEN role = 'landlord' THEN 1 ELSE 0 END) as total_landlords,
    SUM(CASE WHEN role = 'tenant' THEN 1 ELSE 0 END) as total_tenants
    FROM users 
    WHERE status = 'active'
");
$total_landlords = $user_stats['total_landlords'] ?? 0;
$total_tenants = $user_stats['total_tenants'] ?? 0;

// --- 5. Monthly Transaction Volume for Chart (Last 6 Months) ---
$monthly_query = "
    SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month_raw,
        DATE_FORMAT(payment_date, '%b %Y') as month,
        SUM(amount) as monthly_total
    FROM payments 
    WHERE payment_status = 'paid'
      AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
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
    $chart_labels = [date('M Y')];
    $chart_values = [0];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Analytics - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Platform Analytics</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">High-level financial and operational metrics for PropFlow.</p>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Transaction Volume -->
                <div class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent dark:from-green-900/10 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transaction Volume</p>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">GH₵ <?php echo number_format($total_volume, 0); ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Rate -->
                <div class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent dark:from-blue-900/10 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Global Occupancy</p>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $occupancy_rate; ?>%</h3>
                            <p class="text-xs text-gray-400 mt-1"><?php echo $occupied_properties; ?> / <?php echo $total_properties; ?> rented</p>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Health -->
                <div class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent dark:from-orange-900/10 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ticket Resolution</p>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $maintenance_rate; ?>%</h3>
                            <p class="text-xs text-gray-400 mt-1"><?php echo $resolved_maint; ?> / <?php echo $total_maint; ?> resolved</p>
                        </div>
                    </div>
                </div>
                
                <!-- Users -->
                <div class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-transparent dark:from-purple-900/10 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Users</p>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $total_landlords + $total_tenants; ?></h3>
                            <p class="text-xs text-gray-400 mt-1"><?php echo $total_landlords; ?> L / <?php echo $total_tenants; ?> T</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="glass-panel dark:bg-slate-800/80 rounded-2xl shadow-sm p-6 border dark:border-slate-700/50 mb-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Platform Transaction Volume (Last 6 Months)</h3>
                <div class="relative h-80 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labels = <?php echo json_encode($chart_labels); ?>;
            const dataValues = <?php echo json_encode($chart_values); ?>;
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const isDarkMode = document.body.classList.contains('dark');
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
            const textColor = isDarkMode ? '#cbd5e1' : '#64748b';

            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(56, 189, 248, 0.5)');
            gradient.addColorStop(1, 'rgba(56, 189, 248, 0.0)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Volume (GH₵)',
                        data: dataValues,
                        backgroundColor: '#0ea5e9',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
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
                                    return 'GH₵ ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: textColor, font: { family: "'Inter', sans-serif" } }
                        },
                        y: {
                            grid: { color: gridColor, drawBorder: false, borderDash: [5, 5] },
                            ticks: {
                                color: textColor,
                                font: { family: "'Inter', sans-serif" },
                                callback: function (value) { return 'GH₵ ' + value.toLocaleString(); }
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>

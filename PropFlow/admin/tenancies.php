<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Super Admin authorization
if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

$db = new db_connection();

// Fetch ALL tenancies system-wide
$sql_tenancies = "SELECT t.*, u_tenant.full_name as tenant_name, u_landlord.full_name as landlord_name, p.title 
                  FROM tenancies t 
                  JOIN users u_tenant ON t.tenant_id = u_tenant.user_id 
                  JOIN properties p ON t.property_id = p.property_id 
                  JOIN users u_landlord ON p.owner_id = u_landlord.user_id
                  ORDER BY t.status ASC, t.start_date DESC";
$tenancy_list = $db->db_fetch_all($sql_tenancies);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Tenancies - PropFlow Admin</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/admin_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="mb-8 border-b dark:border-slate-700 pb-4">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Global Tenancies</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Platform-wide overview mapping all renters to their properties and landlords.</p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                <div class="p-4 border-b dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">All Tenancy Agreements</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Landlord</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent / Mo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            <?php if ($tenancy_list): ?>
                                <?php foreach ($tenancy_list as $row): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($row['tenant_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                                                <?php echo htmlspecialchars($row['landlord_name']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('M d, Y', strtotime($row['start_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 font-semibold">
                                            GH₵ <?php echo number_format($row['rent_amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-gray-300'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No tenancies established on the platform yet.</td>
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

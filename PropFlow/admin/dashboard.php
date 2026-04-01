<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new db_connection();

// Get System-Wide Statistics
$total_landlords = $db->db_fetch_one("SELECT COUNT(*) as count FROM users WHERE role = 'landlord'")['count'];
$total_tenants = $db->db_fetch_one("SELECT COUNT(*) as count FROM users WHERE role = 'tenant'")['count'];
$total_properties = $db->db_fetch_one("SELECT COUNT(*) as count FROM properties WHERE status != 'archived'")['count'];
$pending_maintenance = $db->db_fetch_one("SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'open'")['count'];

// Get recent users
$recent_users = $db->db_fetch_all("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 5");

// Get recent properties
$recent_properties = $db->db_fetch_all("SELECT p.*, u.full_name as owner_name FROM properties p JOIN users u ON p.owner_id = u.user_id WHERE p.status != 'archived' ORDER BY p.created_at DESC LIMIT 5");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Admin Menu Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="mb-8 border-b dark:border-slate-700 pb-4">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Super-Admin Dashboard</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Platform-wide overview and metrics.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Landlords</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $total_landlords; ?>
                    </p>
                </div>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Tenants</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        <?php echo $total_tenants; ?></p>
                </div>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-brand-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Total Properties</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $total_properties; ?>
                    </p>
                </div>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Pending Maintenance</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $pending_maintenance; ?>
                    </p>
                </div>
            </div>

            <!-- Two-column sections -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                
                <!-- Recent Users -->
                <section>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Recent Users</h2>
                        <a href="users.php" class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-800 font-medium">View All →</a>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                        <?php if ($recent_users && count($recent_users) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                    <thead class="bg-gray-50 dark:bg-slate-900/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                                        <?php foreach ($recent_users as $user): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php echo $user['role'] === 'landlord' ? 'bg-indigo-100 text-indigo-800' : 'bg-teal-100 text-teal-800'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center text-gray-500">No users found.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Recent Properties -->
                <section>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Recent Properties</h2>
                        <a href="properties.php" class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-800 font-medium">View All →</a>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                        <?php if ($recent_properties && count($recent_properties) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                    <thead class="bg-gray-50 dark:bg-slate-900/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                                        <?php foreach ($recent_properties as $prop): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($prop['title']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo htmlspecialchars($prop['owner_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php echo $prop['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'; ?>">
                                                        <?php echo ucfirst($prop['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center text-gray-500">No properties found.</div>
                        <?php endif; ?>
                    </div>
                </section>
                
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
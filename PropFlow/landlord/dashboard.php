<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'landlord') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new db_connection();

// Get statistics
$total_properties = $db->db_fetch_one("SELECT COUNT(*) as count FROM properties WHERE owner_id = $user_id AND status != 'archived'")['count'];
$available_properties = $db->db_fetch_one("SELECT COUNT(*) as count FROM properties WHERE owner_id = $user_id AND status = 'available'")['count'];
$occupied_properties = $db->db_fetch_one("SELECT COUNT(*) as count FROM properties WHERE owner_id = $user_id AND status = 'occupied'")['count'];
$active_tenancies = $db->db_fetch_one("SELECT COUNT(*) as count FROM tenancies t JOIN properties p ON t.property_id = p.property_id WHERE p.owner_id = $user_id AND t.status = 'active'")['count'];

// Get recent properties
$properties = $db->db_fetch_all("SELECT * FROM properties WHERE owner_id = $user_id AND status != 'archived' ORDER BY created_at DESC LIMIT 5");

// Get recent maintenance requests
$maintenance = $db->db_fetch_all("SELECT m.*, p.title as property_title, u.full_name as tenant_name 
    FROM maintenance_requests m 
    JOIN properties p ON m.property_id = p.property_id 
    JOIN users u ON m.tenant_id = u.user_id 
    WHERE p.owner_id = $user_id 
    ORDER BY m.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Dashboard - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome,
                    <?php echo htmlspecialchars(get_user_first_name()); ?>
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Here's an overview of your properties.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-brand-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Total Properties</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $total_properties; ?>
                    </p>
                </div>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Available</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        <?php echo $available_properties; ?></p>
                </div>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-orange-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Occupied</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $occupied_properties; ?>
                    </p>
                </div>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-md transition-all">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Active Tenancies</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo $active_tenancies; ?>
                    </p>
                </div>
            </div>

            <section class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Recent Properties</h2>
                    <a href="add_property.php"
                        class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">Add
                        New
                        +</a>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                    <?php if ($properties && count($properties) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead class="bg-gray-50 dark:bg-slate-900/50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            City</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <?php foreach ($properties as $property): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($property['title']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo ucfirst(str_replace('_', ' ', $property['property_type'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo htmlspecialchars($property['city']); ?>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 font-semibold">
                                                GH₵ <?php echo number_format($property['price'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $property['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'; ?>">
                                                    <?php echo ucfirst($property['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="edit_property.php?id=<?php echo $property['property_id']; ?>"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">Edit</a>
                                                <a href="../actions/delete_property_action.php?id=<?php echo $property['property_id']; ?>"
                                                    class="text-orange-600 dark:text-orange-400 hover:text-orange-900"
                                                    onclick="return confirm('Archive this property?');">Archive</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <p>No properties yet.</p>
                            <a href="add_property.php"
                                class="mt-2 inline-block text-brand-600 dark:text-brand-400 font-medium">Add your first
                                property</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Recent Maintenance Requests</h2>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                    <?php if ($maintenance && count($maintenance) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Property</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tenant</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Priority</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <?php foreach ($maintenance as $request): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($request['property_title']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo htmlspecialchars($request['tenant_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo ucfirst($request['issue_category']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php
                                                    if ($request['priority'] === 'high')
                                                        echo 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400';
                                                    elseif ($request['priority'] === 'medium')
                                                        echo 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400';
                                                    else
                                                        echo 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400';
                                                    ?>">
                                                    <?php echo ucfirst($request['priority']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php
                                                    if ($request['status'] === 'resolved')
                                                        echo 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400';
                                                    elseif ($request['status'] === 'in_progress')
                                                        echo 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400';
                                                    else
                                                        echo 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300';
                                                    ?>">
                                                    <?php echo str_replace('_', ' ', ucfirst($request['status'])); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">No maintenance requests</div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Notifications Section -->
            <section class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-xl shadow-sm p-6 mt-8">
                <?php
                // Fetch Landlord Notifications
                $notif_sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
                $notifications = $db->db_fetch_all($notif_sql);
                ?>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Recent Notifications</h2>
                    <a href="reports.php"
                        class="block px-3 py-2 rounded-md text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Reports & Analytics</a>
                    <a href="history.php"
                        class="block px-3 py-2 rounded-md text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Records & History</a>
                    <a href="notifications.php"
                        class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-800">View All</a>
                </div>
                <?php if ($notifications && count($notifications) > 0): ?>
                    <ul class="space-y-3">
                        <?php foreach ($notifications as $note): ?>
                            <li
                                class="flex items-start p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg border border-gray-100 dark:border-slate-700">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-brand-500 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3 w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                        <?php echo htmlspecialchars($note['message']); ?>
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo date('M d, g:i A', strtotime($note['created_at'])); ?>
                                    </p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">You're all caught up! No recent
                            notifications.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
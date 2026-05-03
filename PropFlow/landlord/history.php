<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'landlord') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new db_connection();

// Get Archived Properties
$archived_properties = $db->db_fetch_all("SELECT * FROM properties WHERE owner_id = $user_id AND status = 'archived' ORDER BY created_at DESC");

// Get Past Tenancies
$past_tenancies = $db->db_fetch_all("SELECT t.*, p.title as property_title, u.full_name as tenant_name 
    FROM tenancies t 
    JOIN properties p ON t.property_id = p.property_id 
    JOIN users u ON t.tenant_id = u.user_id 
    WHERE p.owner_id = $user_id AND t.status = 'ended' 
    ORDER BY t.end_date DESC");

// Get Historical Maintenance Requests
$historical_maintenance = $db->db_fetch_all("SELECT m.*, p.title as property_title, u.full_name as tenant_name 
    FROM maintenance_requests m 
    JOIN properties p ON m.property_id = p.property_id 
    JOIN users u ON m.tenant_id = u.user_id 
    WHERE p.owner_id = $user_id AND m.status = 'resolved' 
    ORDER BY m.updated_at DESC");
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
        <?php include '../includes/landlord_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow space-y-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Records & History</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Review your archived properties, past tenancies, and
                    resolved maintenance requests.</p>
            </div>

            <!-- Archived Properties -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Archived Properties</h2>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                    <?php if ($archived_properties && count($archived_properties) > 0): ?>
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
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <?php foreach ($archived_properties as $property): ?>
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
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300">
                                                    Archived
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="../actions/restore_property_action.php?id=<?php echo $property['property_id']; ?>"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900"
                                                    onclick="return confirm('Restore this property to available?');">Restore</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <p>No archived properties.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Past Tenancies -->
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
                                            Tenant</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Start Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            End Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
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
                                                <?php echo htmlspecialchars($tenancy['tenant_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d, Y', strtotime($tenancy['start_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo $tenancy['end_date'] ? date('M d, Y', strtotime($tenancy['end_date'])) : 'Unknown'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300">
                                                    Ended
                                                </span>
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

            <!-- Historical Maintenance -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Historical Maintenance Requests</h2>
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden border dark:border-slate-700">
                    <?php if ($historical_maintenance && count($historical_maintenance) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead class="bg-gray-50 dark:bg-slate-900/50">
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
                                            Resolved Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <?php foreach ($historical_maintenance as $request): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($request['property_title']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo htmlspecialchars($request['tenant_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo ucfirst($request['issue_category']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo isset($request['updated_at']) ? date('M d, Y', strtotime($request['updated_at'])) : date('M d, Y', strtotime($request['created_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <p>No historical maintenance requests found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
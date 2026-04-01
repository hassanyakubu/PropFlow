<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$db = new db_connection();
$properties = $db->db_fetch_all("
    SELECT p.*, u.full_name as owner_name, u.email as owner_email 
    FROM properties p 
    JOIN users u ON p.owner_id = u.user_id 
    ORDER BY p.created_at DESC
");

// Handle Moderation actions (Force Archive/Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action_id = (int)$_GET['id'];
    
    if ($_GET['action'] === 'archive') {
        $conn = $db->db_conn();
        $stmt = $conn->prepare("UPDATE properties SET status = 'archived' WHERE property_id = ?");
        $stmt->bind_param("i", $action_id);
        $stmt->execute();
        header("Location: properties.php?success=archived");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Moderation - Admin - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>
<body class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Admin Menu Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="mb-8 border-b dark:border-slate-700 pb-4">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Property Moderation</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Global view of all listings on the platform.</p>
            </div>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'archived'): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 mb-6 rounded">
                    <p class="text-green-700 dark:text-green-400 font-medium">Property successfully restricted (Archived).</p>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Landlord</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (GHS)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            <?php foreach ($properties as $p): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                        <div class="text-xs text-gray-500 font-normal mt-1"><?php echo ucfirst(str_replace('_', ' ', $p['property_type'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($p['owner_name']); ?>
                                        <div class="text-xs"><?php echo htmlspecialchars($p['owner_email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-300">
                                        <?php echo number_format($p['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($p['city'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            if ($p['status'] === 'available') echo 'bg-green-100 text-green-800';
                                            elseif ($p['status'] === 'occupied') echo 'bg-blue-100 text-blue-800';
                                            else echo 'bg-orange-100 text-orange-800';
                                            ?>">
                                            <?php echo ucfirst($p['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="../public/property.php?id=<?php echo $p['property_id']; ?>" target="_blank" class="text-brand-600 hover:text-brand-800">View</a>
                                        <?php if ($p['status'] !== 'archived'): ?>
                                            <a href="properties.php?action=archive&id=<?php echo $p['property_id']; ?>" 
                                            class="text-orange-500 hover:text-orange-700 ml-2"
                                            onclick="return confirm('Force Archive this property? It will be removed from public listings.');">Archive</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
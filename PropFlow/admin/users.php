<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$db = new db_connection();
$users = $db->db_fetch_all("SELECT * FROM users ORDER BY role ASC, created_at DESC");

// Handle status updates if provided via GET (e.g., suspend or activate)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action_id = (int)$_GET['id'];
    $new_status = $_GET['action'] === 'suspend' ? 'suspended' : 'active';
    
    // Prevent admin from suspending themselves
    if ($action_id !== $_SESSION['user_id']) {
        $conn = $db->db_conn();
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_status, $action_id);
        $stmt->execute();
        header("Location: users.php?success=status_updated");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>
<body class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Admin Menu Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="mb-8 border-b dark:border-slate-700 pb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Users Management</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage all landlords and tenants on the platform.</p>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 mb-6 rounded">
                    <p class="text-green-700 dark:text-green-400 font-medium">User status updated successfully.</p>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($u['full_name']); ?>
                                        <?php if ($u['user_id'] == $_SESSION['user_id']) echo ' <span class="text-xs text-brand-500">(You)</span>'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            if ($u['role'] === 'admin') echo 'bg-purple-100 text-purple-800';
                                            elseif ($u['role'] === 'landlord') echo 'bg-indigo-100 text-indigo-800';
                                            else echo 'bg-teal-100 text-teal-800';
                                            ?>">
                                            <?php echo ucfirst($u['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $u['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($u['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <a href="users.php?action=suspend&id=<?php echo $u['user_id']; ?>" 
                                                   class="text-red-500 hover:text-red-700" 
                                                   onclick="return confirm('Suspend this user account?');">Suspend</a>
                                            <?php else: ?>
                                                <a href="users.php?action=activate&id=<?php echo $u['user_id']; ?>" 
                                                   class="text-green-500 hover:text-green-700"
                                                   onclick="return confirm('Re-activate this user account?');">Activate</a>
                                            <?php endif; ?>
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
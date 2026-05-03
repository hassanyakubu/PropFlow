<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();
$requests = [];

if ($conn) {
    $sql = "
        SELECT m.*, p.title as property_title, u_tenant.full_name as tenant_name, u_landlord.full_name as landlord_name
        FROM maintenance_requests m
        JOIN properties p ON m.property_id = p.property_id
        JOIN users u_tenant ON m.tenant_id = u_tenant.user_id
        JOIN users u_landlord ON p.owner_id = u_landlord.user_id
        ORDER BY m.created_at DESC
    ";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Maintenance - PropFlow Admin</title>
    <?php include '../includes/head_assets.php'; ?>
    <style>
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-open { background-color: #fee2e2; color: #ef4444; }
        .dark .status-open { background-color: rgba(239, 68, 68, 0.2); color: #fca5a5; }

        .status-pending { background-color: #fef3c7; color: #f59e0b; }
        .dark .status-pending { background-color: rgba(245, 158, 11, 0.2); color: #fcd34d; }

        .status-in_progress { background-color: #dbeafe; color: #3b82f6; }
        .dark .status-in_progress { background-color: rgba(59, 130, 246, 0.2); color: #93c5fd; }

        .status-resolved { background-color: #d1fae5; color: #10b981; }
        .dark .status-resolved { background-color: rgba(16, 185, 129, 0.2); color: #6ee7b7; }
    </style>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/admin_sidebar.php'; ?>

        <main class="flex-grow max-w-5xl">
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Platform Maintenance Oversight</h2>
                    <p class="text-gray-600 dark:text-gray-400">View all maintenance tickets, status, and responsible landlords.</p>
                </div>
            </div>

            <?php if (empty($requests)): ?>
                <div class="glass-panel dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl shadow-sm border border-white/50 p-12 text-center">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Maintenance Requests</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">There are currently no maintenance tickets open on the platform.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($requests as $req): ?>
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden transition-all hover:shadow-md">
                            
                            <!-- Header Area -->
                            <div class="border-b border-gray-100 dark:border-slate-700 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50 dark:bg-slate-800/50">
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="status-badge status-<?php echo strtolower($req['status']); ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($req['status'])); ?>
                                        </span>
                                        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                                            Ticket #<?php echo $req['request_id']; ?>
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($req['property_title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                        Submitted on <?php echo date('M d, Y', strtotime($req['created_at'])); ?> by <?php echo htmlspecialchars($req['tenant_name']); ?>
                                    </p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Responsible Landlord</p>
                                    <span class="font-bold text-gray-900 dark:text-white text-lg"><?php echo htmlspecialchars($req['landlord_name']); ?></span>
                                </div>
                            </div>

                            <!-- Body Area -->
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Left Col: Issue Info -->
                                <div>
                                    <div class="mb-4">
                                        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Issue Category</p>
                                        <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo ucfirst(htmlspecialchars($req['issue_category'])); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Description</p>
                                        <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-slate-900 p-4 rounded-xl border border-gray-100 dark:border-slate-700 text-sm whitespace-pre-wrap"><?php echo htmlspecialchars($req['description']); ?></p>
                                    </div>
                                </div>
                                <!-- Right Col: Landlord Notes (Read Only for Admin) -->
                                <div class="bg-gray-50/50 dark:bg-slate-700/20 p-5 rounded-xl border border-gray-100 dark:border-slate-700">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Landlord Log</h4>
                                    <?php if (!empty($req['landlord_notes'])): ?>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 italic">"<?php echo htmlspecialchars($req['landlord_notes']); ?>"</p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-400 italic">No updates provided by landlord yet.</p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($req['updated_at'])): ?>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-4 border-t border-gray-200 dark:border-slate-600 pt-2">
                                            Last action: <?php echo date('M d, Y h:i A', strtotime($req['updated_at'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

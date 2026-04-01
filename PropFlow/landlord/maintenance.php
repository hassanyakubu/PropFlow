<?php
include '../settings/core.php';
require_once '../settings/db_class.php';

require_login();
if (!is_landlord()) {
    header("Location: ../index.php");
    exit();
}

$landlord_id = get_user_id();
$db = new db_connection();
$conn = $db->db_conn();
$requests = [];

if ($conn) {
    $sql = "
        SELECT m.*, p.title as property_title, u.full_name as tenant_name
        FROM maintenance_requests m
        JOIN properties p ON m.property_id = p.property_id
        JOIN users u ON m.tenant_id = u.user_id
        WHERE p.owner_id = ?
        ORDER BY m.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $landlord_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Maintenance - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
    <style>
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-open {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-in_progress {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-resolved {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .update-form {
            display: flex;
            gap: 8px;
        }

        .update-form select {
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .btn-update {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-update:hover {
            background-color: #218838;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        <style>.status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-open {
            background-color: #fee2e2;
            color: #ef4444;
        }

        .dark .status-open {
            background-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        .dark .status-pending {
            background-color: rgba(245, 158, 11, 0.2);
            color: #fcd34d;
        }

        .status-in_progress {
            background-color: #dbeafe;
            color: #3b82f6;
        }

        .dark .status-in_progress {
            background-color: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }

        .status-resolved {
            background-color: #d1fae5;
            color: #10b981;
        }

        .dark .status-resolved {
            background-color: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }
    </style>
</head>

<body
    class="font-sans text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/landlord_sidebar.php'; ?>

        <main class="flex-grow max-w-5xl">
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Maintenance Tracking</h2>
                    <p class="text-gray-600 dark:text-gray-400">View and update maintenance requests for your
                        properties.</p>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-800 dark:text-green-300 font-medium">Request updated successfully and tenant
                            notified.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-800 dark:text-red-300 font-medium">Error updating status. Please try again.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($requests)): ?>
                <div
                    class="glass-panel dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl shadow-sm border border-white/50 p-12 text-center">
                    <div
                        class="w-20 h-20 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Maintenance Requests</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">There are currently no active or past
                        maintenance requests for any of your properties.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($requests as $req): ?>
                        <div
                            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden transition-all hover:shadow-md">

                            <!-- Header Area -->
                            <div
                                class="border-b border-gray-100 dark:border-slate-700 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50 dark:bg-slate-800/50">
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
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        Submitted on <?php echo date('M d, Y h:i A', strtotime($req['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Requested by</p>
                                    <div class="flex items-center sm:justify-end mt-1">
                                        <div
                                            class="w-6 h-6 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center text-xs font-bold mr-2">
                                            <?php echo strtoupper(substr($req['tenant_name'], 0, 1)); ?>
                                        </div>
                                        <span
                                            class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($req['tenant_name']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Body Area -->
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">

                                <!-- Left Col: Issue Info -->
                                <div>
                                    <div class="mb-4">
                                        <p
                                            class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                                            Issue Category</p>
                                        <p class="text-base font-semibold text-gray-900 dark:text-white">
                                            <?php echo ucfirst(htmlspecialchars($req['issue_category'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                                            Description</p>
                                        <p
                                            class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-slate-900 p-4 rounded-xl border border-gray-100 dark:border-slate-700 text-sm whitespace-pre-wrap">
                                            <?php echo htmlspecialchars($req['description']); ?></p>
                                    </div>
                                </div>
                                <!-- Right Col: Update Form -->
                                <div
                                    class="bg-blue-50/50 dark:bg-slate-700/20 p-5 rounded-xl border border-blue-100 dark:border-slate-700">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-brand-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                            </path>
                                        </svg>
                                        Update Ticket
                                    </h4>
                                    <form action="../actions/update_maintenance_action.php" method="POST" class="space-y-4">
                                        <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">

                                        <div>
                                            <label
                                                class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">Status</label>
                                            <select name="status"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow">
                                                <option value="pending" <?php echo $req['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_progress" <?php echo $req['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="resolved" <?php echo $req['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">Landlord
                                                Notes (Visible to Tenant)</label>
                                            <textarea name="landlord_notes" rows="2"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow resize-none"
                                                placeholder="Add details about repair schedule, parts ordered, or completion notes..."><?php echo htmlspecialchars($req['landlord_notes'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="flex items-center justify-between pt-2">
                                            <?php if (!empty($req['updated_at'])): ?>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 italic">
                                                    Last updated: <?php echo date('M d, h:i A', strtotime($req['updated_at'])); ?>
                                                </p>
                                            <?php else: ?>
                                                <span></span>
                                            <?php endif; ?>
                                            <button type="submit"
                                                class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-2 px-5 rounded-lg shadow-sm hover:shadow-md transition-all text-sm">
                                                Save Updates
                                            </button>
                                        </div>
                                    </form>
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
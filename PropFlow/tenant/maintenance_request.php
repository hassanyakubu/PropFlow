<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

$db = new db_connection();
$user_id = get_user_id();

// Fetch tenancy info to know property_id
$sql_tenancy = "SELECT tenancy_id, property_id FROM tenancies WHERE tenant_id = '$user_id' AND status = 'active' LIMIT 1";
$tenancy = $db->db_fetch_one($sql_tenancy);

// Get unread notifications count
$sql_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$unread_result = $db->db_fetch_one($sql_unread);
$unread_count = $unread_result ? (int) $unread_result['unread_count'] : 0;

$sql_requests = "SELECT * FROM maintenance_requests WHERE tenant_id = '$user_id' ORDER BY created_at DESC";
$requests = $db->db_fetch_all($sql_requests);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
    <style>
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
        <?php include '../includes/tenant_sidebar.php'; ?>

        <main class="flex-grow max-w-4xl">
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Maintenance</h2>
                    <p class="text-gray-600 dark:text-gray-400">Submit requests and track repair progress directly with
                        your landlord.</p>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-800 dark:text-green-300 font-medium">Request submitted successfully! The
                            landlord has been notified.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Submit New Request Form -->
            <?php if ($tenancy): ?>
                <div
                    class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 mb-10">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        Submit a Maintenance Request
                    </h3>

                    <form action="../actions/submit_maintenance_action.php" method="POST" class="space-y-6">
                        <input type="hidden" name="property_id" value="<?php echo $tenancy['property_id']; ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category"
                                    class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Issue
                                    Category</label>
                                <select name="category" id="category" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow">
                                    <option value="plumbing">Plumbing</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="structural">Structural</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="priority"
                                    class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Priority
                                    Level</label>
                                <select name="priority" id="priority" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow">
                                    <option value="low">Low (Routine)</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High (Urgent)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="description"
                                class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea name="description" id="description" rows="4" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow resize-none"
                                placeholder="Please provide specific details about the issue..."></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center">
                                Submit Request
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div
                    class="glass-panel dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl shadow-sm border border-orange-200 dark:border-orange-800 p-8 text-center bg-orange-50/50 dark:bg-orange-900/10 mb-10">
                    <svg class="w-12 h-12 text-orange-400 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <h3 class="text-xl font-bold text-orange-800 dark:text-orange-400 mb-2">No Active Tenancy</h3>
                    <p class="text-orange-700 dark:text-orange-300">You must be formally assigned to a property by a
                        landlord before you can submit maintenance requests.</p>
                </div>
            <?php endif; ?>

            <!-- Requests History Map -->
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Active & Past Requests</h3>
            </div>

            <?php if ($requests): ?>
                <div class="space-y-6">
                    <?php foreach ($requests as $req): ?>
                        <div
                            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-md transition-shadow">

                            <!-- Header -->
                            <div
                                class="bg-gray-50/80 dark:bg-slate-800/80 border-b border-gray-100 dark:border-slate-700 p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="status-badge status-<?php echo strtolower($req['status']); ?>">
                                            <?php
                                            if ($req['status'] === 'in_progress') {
                                                echo '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
                                            } elseif ($req['status'] === 'resolved') {
                                                echo '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                            }
                                            echo str_replace('_', ' ', ucfirst($req['status']));
                                            ?>
                                        </span>
                                        <span class="text-xs font-bold text-gray-400 uppercase">
                                            Ticket #<?php echo $req['request_id']; ?>
                                        </span>
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mt-2">
                                        <?php echo ucfirst(htmlspecialchars($req['issue_category'])); ?> Issue
                                    </h4>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-1">Created</p>
                                    <p class="text-gray-900 dark:text-white font-medium">
                                        <?php echo date('M d, Y', strtotime($req['created_at'])); ?></p>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="p-6">
                                <div class="mb-6">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Your Description
                                    </p>
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                        <?php echo htmlspecialchars($req['description']); ?></p>
                                </div>

                                <!-- Landlord Notes Area -->
                                <?php if (!empty($req['landlord_notes'])): ?>
                                    <div
                                        class="bg-blue-50/50 dark:bg-slate-900/50 rounded-xl p-5 border border-blue-100 dark:border-slate-700 relative">

                                        <div class="flex items-center gap-2 mb-3">
                                            <div
                                                class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <h5 class="text-sm font-bold text-gray-900 dark:text-white">Landlord Update</h5>
                                            <?php if (!empty($req['updated_at'])): ?>
                                                <span class="text-xs font-semibold text-gray-400 ml-auto">
                                                    <?php echo date('M d, g:i A', strtotime($req['updated_at'])); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                            <?php echo nl2br(htmlspecialchars($req['landlord_notes'])); ?>
                                        </p>
                                    </div>
                                <?php elseif ($req['status'] !== 'pending'): ?>
                                    <div
                                        class="flex items-center text-sm text-gray-500 dark:text-gray-400 mt-4 pt-4 border-t border-gray-100 dark:border-slate-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Status updated on <?php echo date('M d, Y', strtotime($req['updated_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div
                    class="glass-panel dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl shadow-sm border border-white/50 p-12 text-center">
                    <div
                        class="w-20 h-20 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Requests Found</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">You haven't submitted any maintenance
                        requests yet.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>

</html>
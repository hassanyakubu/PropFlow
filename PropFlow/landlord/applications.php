<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = get_user_id();
$db = new db_connection();

// Fetch pending applications for my properties
$sql = "SELECT a.*, p.title as property_title, u.full_name, u.email, u.phone 
        FROM applications a 
        JOIN properties p ON a.property_id = p.property_id 
        JOIN users u ON a.tenant_id = u.user_id 
        WHERE p.owner_id = '$user_id' AND a.status = 'pending' 
        ORDER BY a.created_at ASC";

$applications = $db->db_fetch_all($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/landlord_sidebar.php'; ?>

        <main class="flex-grow">
            <h1>Tenant Applications</h1>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'accepted'): ?>
                <div class="alert alert-success">Application accepted. Tenancy created!</div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'rejected'): ?>
                <div class="alert alert-info">Application rejected.</div>
            <?php endif; ?>

            <div class="card">
                <?php if ($applications): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['property_title']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['full_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($app['email']); ?><br><?php echo htmlspecialchars($app['phone']); ?></small>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars($app['message'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <!-- Accept Form (Requires rent details confirmation) -->
                                            <button
                                                onclick="showAcceptModal(<?php echo $app['application_id']; ?>, '<?php echo htmlspecialchars($app['property_title']); ?>', '<?php echo htmlspecialchars($app['full_name']); ?>')"
                                                class="btn btn-sm btn-success">Accept</button>

                                            <!-- Reject Form -->
                                            <form action="../actions/process_application_action.php" method="POST"
                                                style="display:inline;">
                                                <input type="hidden" name="application_id"
                                                    value="<?php echo $app['application_id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Reject application?')">Decline</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No pending applications.</p>
                <?php endif; ?>
            </div>

            <!-- Accept Modal -->
            <div id="acceptModal" class="modal"
                style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
                <div class="modal-content"
                    style="background:white; margin:10% auto; padding:20px; width:50%; border-radius:8px;">
                    <h2>Accept Application</h2>
                    <p id="modalText"></p>
                    <form action="../actions/process_application_action.php" method="POST">
                        <input type="hidden" name="application_id" id="modalAppId">
                        <input type="hidden" name="action" value="accept">

                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Confirm Acceptance</button>
                            <button type="button" onclick="document.getElementById('acceptModal').style.display='none'"
                                class="btn btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function showAcceptModal(id, title, tenant) {
                    document.getElementById('modalAppId').value = id;
                    document.getElementById('modalText').innerText = 'Creating tenancy for ' + tenant + ' at ' + title;
                    document.getElementById('acceptModal').style.display = 'block';
                }
            </script>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
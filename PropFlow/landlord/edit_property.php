<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Ensure user is logged in as landlord
if (!is_landlord()) {
    header("Location: ../public/login.php?error=unauthorized");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$property_id = (int) $_GET['id'];
$user_id = get_user_id();
$db = new db_connection();

// Create connection for escaping variables later in HTML if needed or just use fetched data
// Fetch property ensuring it belongs to this landlord
$sql = "SELECT * FROM properties WHERE property_id = '$property_id' AND owner_id = '$user_id'";
$property = $db->db_fetch_one($sql);

if (!$property) {
    header("Location: dashboard.php?error=not_found");
    exit();
}

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/landlord_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Property</h1>
                    <p class="text-gray-500 mt-1">Update property details.</p>
                </div>
                <a href="dashboard.php"
                    class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">Back
                    to Dashboard</a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">Property updated successfully!</div>
            <?php elseif ($error): ?>
                <div class="alert alert-error">Error updating property. Please try again.</div>
            <?php endif; ?>

            <form action="../actions/edit_property_action.php" method="POST" class="property-form"
                enctype="multipart/form-data">
                <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Property Title</label>
                        <input type="text" id="title" name="title"
                            value="<?php echo htmlspecialchars($property['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Rate/Amount (GH₵)</label>
                        <input type="number" id="price" name="price" value="<?php echo $property['price']; ?>" required
                            min="0" step="0.01">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lease_type">Listing Type</label>
                        <select id="lease_type" name="lease_type" required>
                            <option value="renting" <?php echo ($property['lease_type'] ?? 'renting') == 'renting' ? 'selected' : ''; ?>>For Rent</option>
                            <option value="leasing" <?php echo ($property['lease_type'] ?? '') == 'leasing' ? 'selected' : ''; ?>>For Lease</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_period">Payment Period</label>
                        <select id="payment_period" name="payment_period" required>
                            <option value="monthly" <?php echo ($property['payment_period'] ?? 'monthly') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="quarterly" <?php echo ($property['payment_period'] ?? '') == 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                            <option value="bi-annually" <?php echo ($property['payment_period'] ?? '') == 'bi-annually' ? 'selected' : ''; ?>>Bi-Annually (6 Months)</option>
                            <option value="yearly" <?php echo ($property['payment_period'] ?? '') == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                        rows="4"><?php echo htmlspecialchars($property['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address"
                            value="<?php echo htmlspecialchars($property['address']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city"
                            value="<?php echo htmlspecialchars($property['city']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="property_type">Property Type</label>
                        <select id="property_type" name="property_type" required>
                            <option value="apartment" <?php if ($property['property_type'] == 'apartment')
                                echo 'selected'; ?>>Apartment</option>
                            <option value="house" <?php if ($property['property_type'] == 'house')
                                echo 'selected'; ?>>
                                House</option>
                            <option value="single_room" <?php if ($property['property_type'] == 'single_room')
                                echo 'selected'; ?>>Single Room</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="available" <?php if ($property['status'] == 'available')
                                echo 'selected'; ?>>
                                Available</option>
                            <option value="occupied" <?php if ($property['status'] == 'occupied')
                                echo 'selected'; ?>>
                                Occupied</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="images">Add New Property Images (Optional)</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/gif"
                        class="w-full p-2 border border-gray-300 rounded-md">
                    <small class="text-gray-500 block mt-1">Uploading new images will append them to the property's
                        existing gallery.</small>
                </div>

                <div class="form-group">
                    <label for="map_location">Google Maps Embed Link (Optional)</label>
                    <input type="text" id="map_location" name="map_location"
                        placeholder="<iframe src='...'></iframe> OR https://maps.google.com/..."
                        class="w-full p-2 border border-gray-300 rounded-md"
                        value="<?php echo htmlspecialchars($property['map_location'] ?? ''); ?>">
                    <small class="text-gray-500 block mt-1">Go to Google Maps > Share > Embed a map (or Copy Link), and
                        paste it here.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Property</button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                    class="bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 font-medium py-2 px-4 rounded-lg shadow-sm transition-colors border border-red-200">
                    Delete Property Permanently
                </button>
            </div>

            <!-- Custom Delete Modal -->
            <div id="deleteModal"
                class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center backdrop-blur-sm">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 m-4 relative">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-4 mx-auto">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Delete Property</h3>
                    <p class="text-center text-gray-500 mb-6">Are you sure you want to permanently delete
                        "<?php echo htmlspecialchars($property['title']); ?>"? This action cannot be undone and all
                        associated data will be lost.</p>

                    <div class="flex justify-center gap-4">
                        <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Cancel
                        </button>
                        <a href="../actions/delete_property_action.php?id=<?php echo $property['property_id']; ?>"
                            class="px-4 py-2 bg-red-600 border border-transparent rounded-lg text-white hover:bg-red-700 font-medium transition-colors">
                            Yes, Delete It
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
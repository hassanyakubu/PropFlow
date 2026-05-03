<?php
require_once '../settings/core.php';

// Ensure user is logged in as landlord
if (!is_landlord()) {
    header("Location: ../public/login.php?error=unauthorized");
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
    <title>Add Property - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <?php include '../includes/landlord_sidebar.php'; ?>

        <main class="flex-grow">
            <div class="header-flex">
                <h1>Add New Property</h1>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">Property added successfully!</div>
            <?php elseif ($error): ?>
                <div class="alert alert-error">Error adding property. Please try again.</div>
            <?php endif; ?>

            <form action="../actions/add_property_action.php" method="POST" enctype="multipart/form-data"
                class="property-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Property Title</label>
                        <input type="text" id="title" name="title" required
                            placeholder="e.g. Modern 2-Bedroom Apartment">
                    </div>

                    <div class="form-group">
                        <label for="price">Rate/Amount (GH₵)</label>
                        <input type="number" id="price" name="price" required min="0" step="0.01"
                            placeholder="e.g. 1500">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lease_type">Listing Type</label>
                        <select id="lease_type" name="lease_type" required>
                            <option value="renting">For Rent</option>
                            <option value="leasing">For Lease</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_period">Payment Period</label>
                        <select id="payment_period" name="payment_period" required>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="bi-annually">Bi-Annually (6 Months)</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" required>
                    </div>

                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="property_type">Property Type</label>
                        <select id="property_type" name="property_type" required>
                            <option value="apartment">Apartment</option>
                            <option value="house">House</option>
                            <option value="single_room">Single Room</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="images">Property Images (Max 5)</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*" required>
                    <small>Hold Ctrl/Cmd to select multiple images</small>
                </div>

                <div class="form-group">
                    <label for="map_location">Google Maps Embed Link (Optional)</label>
                    <input type="text" id="map_location" name="map_location"
                        placeholder="<iframe src='...'></iframe> OR https://maps.google.com/..." class="w-full">
                    <small class="text-gray-500 block mt-1">Go to Google Maps > Share > Embed a map (or Copy Link), and
                        paste it here.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Property</button>
                </div>
            </form>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
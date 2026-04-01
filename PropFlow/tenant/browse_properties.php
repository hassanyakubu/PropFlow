<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = get_user_id();
$db = new db_connection();

// Get unread notifications count
$sql_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$unread_result = $db->db_fetch_one($sql_unread);
$unread_count = $unread_result ? (int) $unread_result['unread_count'] : 0;

// Get all available properties with landlord info
$sql = "SELECT p.*, u.full_name as landlord_name, u.phone as landlord_phone
        FROM properties p
        JOIN users u ON p.owner_id = u.user_id
        WHERE p.status = 'available'
        ORDER BY u.full_name, p.title";
$properties = $db->db_fetch_all($sql);

// Group by landlord
$grouped_properties = [];
if ($properties) {
    foreach ($properties as $prop) {
        $landlord = $prop['landlord_name'];
        if (!isset($grouped_properties[$landlord])) {
            $grouped_properties[$landlord] = [
                'phone' => $prop['landlord_phone'],
                'properties' => []
            ];
        }
        $grouped_properties[$landlord]['properties'][] = $prop;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Properties - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="flex flex-col md:flex-row flex-grow container mx-auto px-4 py-8 gap-8">
        <!-- Sidebar -->
        <?php include '../includes/tenant_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Browse Properties</h1>
                <p class="text-gray-500 mt-1">Explore available properties from our landlords.</p>
            </div>

            <?php if (empty($grouped_properties)): ?>
                <div class="bg-white rounded-xl shadow-sm p-8 text-center border border-gray-100">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No Properties Found</h3>
                    <p class="text-gray-500">There are currently no available properties listed.</p>
                </div>
            <?php else: ?>
                <div class="space-y-12">
                    <?php foreach ($grouped_properties as $landlord => $data): ?>
                        <section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Landlord:
                                        <?php echo htmlspecialchars($landlord); ?>
                                    </h2>
                                    <p class="text-sm text-gray-500 mt-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                            </path>
                                        </svg>
                                        <?php echo htmlspecialchars($data['phone']); ?>
                                    </p>
                                </div>
                                <span class="bg-brand-100 text-brand-800 text-xs font-semibold px-3 py-1 rounded-full">
                                    <?php echo count($data['properties']); ?> listings
                                </span>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <?php foreach ($data['properties'] as $prop): ?>
                                        <div
                                            class="bg-white border text-left border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow flex flex-col relative">
                                            <?php
                                            // Get main image if exists
                                            $prop_id = $prop['property_id'];
                                            $img_sql = "SELECT image_path FROM property_images WHERE property_id = '$prop_id' LIMIT 1";
                                            $img_result = $db->db_fetch_one($img_sql);
                                            $img_url = $img_result ? '../uploads/property_images/' . $img_result['image_path'] : '../public/assets/images/placeholder.jpg';
                                            ?>
                                            <div class="h-48 w-full bg-cover bg-center"
                                                style="background-image: url('<?php echo htmlspecialchars($img_url); ?>');">
                                                <div class="absolute mt-2 ml-2">
                                                    <span
                                                        class="px-2 py-1 text-xs font-semibold rounded bg-white text-gray-800 shadow-sm border border-gray-200">
                                                        For
                                                        <?php echo ucfirst($prop['lease_type'] ?? 'Rent'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="p-4 flex-grow flex flex-col">
                                                <h3 class="text-lg font-bold text-gray-900 mb-1 truncate"
                                                    title="<?php echo htmlspecialchars($prop['title']); ?>">
                                                    <?php echo htmlspecialchars($prop['title']); ?>
                                                </h3>
                                                <p class="text-gray-500 text-sm mb-4 flex items-center truncate">
                                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    <?php echo htmlspecialchars($prop['city']); ?>
                                                </p>

                                                <div class="mt-auto">
                                                    <div
                                                        class="flex justify-between items-center border-t border-gray-100 pt-3 mt-3">
                                                        <div>
                                                            <span class="text-xl font-bold text-brand-600">GH₵
                                                                <?php echo number_format($prop['price'], 2); ?>
                                                            </span>
                                                            <span class="text-xs text-gray-500">/
                                                                <?php echo ($prop['payment_period'] ?? 'monthly') == 'monthly' ? 'mo' : (($prop['payment_period'] ?? '') == 'yearly' ? 'yr' : substr($prop['payment_period'] ?? 'monthly', 0, 3)); ?>
                                                            </span>
                                                        </div>
                                                        <a href="../public/property.php?id=<?php echo $prop['property_id']; ?>"
                                                            class="text-sm font-medium text-brand-600 hover:text-brand-800 transition-colors">
                                                            View Details &rarr;
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
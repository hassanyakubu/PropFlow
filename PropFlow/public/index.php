<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

$db = new db_connection();
$properties = $db->db_fetch_all("SELECT p.*, pi.image_path, u.full_name as owner_name 
    FROM properties p 
    LEFT JOIN property_images pi ON p.property_id = pi.property_id 
    LEFT JOIN users u ON p.owner_id = u.user_id 
    WHERE p.status = 'available' 
    GROUP BY p.property_id 
    ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropFlow - Rental Property Management</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <section class="relative pt-16 pb-32 flex content-center items-center justify-center min-h-[75vh]">
        <div class="container relative mx-auto">
            <div class="items-center flex flex-wrap">
                <div class="w-full lg:w-6/12 px-4 ml-auto mr-auto text-center">
                    <div class="pr-12">
                        <h1
                            class="text-gray-900 dark:text-white font-extrabold text-5xl tracking-tight mb-4 drop-shadow-md">
                            Find Your Perfect Rental Property
                        </h1>
                        <p class="mt-4 text-lg text-gray-700 dark:text-gray-300 font-medium drop-shadow-sm mb-8">
                            Digitizing rental workflows for modern living. Manage properties, pay rent, and track
                            maintenance all in one place.
                        </p>
                        <?php if (!is_logged_in()): ?>
                            <div class="flex justify-center gap-4">
                                <a href="login.php"
                                    class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:-translate-y-1">Login</a>
                                <a href="register.php"
                                    class="bg-white hover:bg-gray-100 text-brand-600 font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:-translate-y-1">Register</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-20 relative block bg-white/80 backdrop-blur-md -mt-24 mx-4 rounded-xl shadow-xl">
        <div class="container mx-auto px-4 pt-12">
            <div class="flex flex-wrap justify-center mb-12">
                <div class="w-full lg:w-6/12 px-4 text-center">
                    <h2 class="text-3xl font-bold text-brand-900 mb-2">Available Properties</h2>
                    <p class="text-gray-500 text-lg">Browse our latest listings and find your new home today.</p>
                </div>
            </div>

            <div class="flex flex-wrap">
                <?php if ($properties && count($properties) > 0): ?>
                    <?php foreach ($properties as $property): ?>
                        <div class="w-full md:w-4/12 px-4 mb-8">
                            <div
                                class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden h-full flex flex-col">
                                <div class="relative h-48">
                                    <?php if ($property['image_path']): ?>
                                        <img src="../uploads/property_images/<?php echo htmlspecialchars($property['image_path']); ?>"
                                            alt="<?php echo htmlspecialchars($property['title']); ?>"
                                            class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-brand-100 flex items-center justify-center text-brand-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div
                                        class="absolute top-0 right-0 m-4 bg-brand-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                        <?php echo str_replace('_', ' ', $property['property_type']); ?>
                                    </div>
                                </div>
                                <div class="p-6 flex-grow flex flex-col">
                                    <h3 class="text-xl font-bold text-gray-800 mb-2 truncate"
                                        title="<?php echo htmlspecialchars($property['title']); ?>">
                                        <?php echo htmlspecialchars($property['title']); ?>
                                    </h3>
                                    <div class="flex items-center text-gray-500 mb-4 text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <?php echo htmlspecialchars($property['city']); ?>
                                    </div>
                                    <div class="mt-auto border-t border-gray-100 pt-4 flex justify-between items-center">
                                        <div class="text-brand-600 font-bold text-lg">
                                            GH₵ <?php echo number_format($property['price'], 2); ?>
                                            <span class="text-xs text-gray-400 font-normal">
                                                /<?php
                                                $period = $property['payment_period'] ?? 'monthly';
                                                if ($period == 'monthly')
                                                    echo 'mo';
                                                elseif ($period == 'quarterly')
                                                    echo 'qtr';
                                                elseif ($period == 'bi-annually')
                                                    echo '6mo';
                                                elseif ($period == 'yearly')
                                                    echo 'yr';
                                                ?>
                                            </span>
                                            <span class="block text-xs text-brand-500 font-medium mt-1">
                                                For <?php echo ucfirst($property['lease_type'] ?? 'Rent'); ?>
                                            </span>
                                        </div>
                                        <a href="property.php?id=<?php echo $property['property_id']; ?>"
                                            class="text-brand-500 hover:text-brand-700 font-semibold text-sm flex items-center">
                                            View Details
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="w-full text-center py-10">
                        <p class="text-gray-500 text-lg">No properties available at the moment. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
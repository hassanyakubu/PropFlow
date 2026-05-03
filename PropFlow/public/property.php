<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$property_id) {
    header("Location: index.php");
    exit();
}

$db = new db_connection();
$property = $db->db_fetch_one("SELECT p.*, u.full_name as owner_name, u.phone as owner_phone, u.email as owner_email 
    FROM properties p 
    LEFT JOIN users u ON p.owner_id = u.user_id 
    WHERE p.property_id = $property_id");

if (!$property) {
    header("Location: index.php");
    exit();
}

$images = $db->db_fetch_all("SELECT * FROM property_images WHERE property_id = $property_id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Back Button & Breadcrumbs -->
        <div class="mb-6">
            <?php
            $back_url = "index.php";
            if (is_logged_in()) {
                if (get_user_role() === 'tenant') {
                    $back_url = "../tenant/dashboard.php";
                } elseif (get_user_role() === 'landlord') {
                    $back_url = "../landlord/dashboard.php";
                }
            }
            ?>
            <a href="<?php echo $back_url; ?>" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors flex items-center text-sm font-medium w-fit">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back
            </a>
        </div>

        <!-- Image Gallery (Modern Layout) -->
        <div class="mb-8 rounded-2xl overflow-hidden shadow-lg border border-gray-100 dark:border-slate-800 bg-white dark:bg-slate-900">
            <?php if ($images && count($images) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 h-auto md:h-[500px]">
                    <!-- Main Large Image -->
                    <div class="col-span-1 md:col-span-2 h-64 md:h-full relative filter hover:brightness-105 transition-all duration-300">
                        <img src="../uploads/property_images/<?php echo htmlspecialchars($images[0]['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($property['title']); ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-6 left-6 flex gap-2">
                            <span class="bg-brand-600/90 backdrop-blur-sm text-white px-4 py-1.5 rounded-full text-sm font-bold shadow-md">For <?php echo ucfirst($property['lease_type'] ?? 'Rent'); ?></span>
                            <span class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm text-gray-800 dark:text-gray-200 px-4 py-1.5 rounded-full text-sm font-semibold shadow-md capitalize"><?php echo $property['status']; ?></span>
                        </div>
                    </div>
                    
                    <!-- Side Thumbnail Grid -->
                    <?php if (count($images) > 1): ?>
                        <div class="grid grid-rows-2 gap-2 h-64 md:h-full hidden md:grid">
                            <div class="h-full relative overflow-hidden group">
                                <img src="../uploads/property_images/<?php echo htmlspecialchars($images[1]['image_path']); ?>" alt="Gallery" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <?php if (count($images) > 2): ?>
                                <div class="h-full relative overflow-hidden group">
                                    <img src="../uploads/property_images/<?php echo htmlspecialchars($images[2]['image_path']); ?>" alt="Gallery" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                                    <?php if(count($images) > 3): ?>
                                        <div class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm transition-opacity hover:bg-black/40 cursor-pointer">
                                            <span class="text-white font-bold text-xl">+<?php echo count($images) - 3; ?> More</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="h-full bg-gray-100 dark:bg-slate-800 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="h-64 md:h-96 bg-gray-100 dark:bg-slate-800 flex items-center justify-center rounded-2xl border border-gray-200 dark:border-slate-700">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-gray-400 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <p class="text-gray-500 dark:text-gray-400 text-lg">No images available for this property</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative">
            <!-- Main Content (Left, 2 cols) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Title & Basic Info -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2 leading-tight"><?php echo htmlspecialchars($property['title']); ?></h1>
                            <p class="text-gray-500 dark:text-gray-400 flex items-center text-lg">
                                <svg class="w-5 h-5 mr-2 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <?php echo htmlspecialchars($property['address']) . ', ' . htmlspecialchars($property['city']); ?>
                            </p>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold mb-1">Asking Price</p>
                            <div class="text-3xl font-extrabold text-brand-600 dark:text-brand-400">GH₵ <?php echo number_format($property['price'], 2); ?></div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">/ <?php
                                $period = $property['payment_period'] ?? 'monthly';
                                if ($period == 'monthly') echo 'month';
                                elseif ($period == 'quarterly') echo 'quarter';
                                elseif ($period == 'bi-annually') echo '6 months';
                                elseif ($period == 'yearly') echo 'year';
                            ?></p>
                        </div>
                    </div>

                    <!-- Key Features Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-6 border-t border-gray-100 dark:border-slate-700">
                        <div class="flex flex-col items-center justify-center text-center p-4 bg-gray-50 dark:bg-slate-900/50 rounded-xl">
                            <svg class="w-8 h-8 text-gray-400 dark:text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Property Type</span>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo ucwords(str_replace('_', ' ', $property['property_type'])); ?></span>
                        </div>
                        <div class="flex flex-col items-center justify-center text-center p-4 bg-gray-50 dark:bg-slate-900/50 rounded-xl">
                            <svg class="w-8 h-8 text-gray-400 dark:text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Lease terms</span>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo ucfirst($property['lease_type'] ?? 'Rent'); ?></span>
                        </div>
                        <div class="flex flex-col items-center justify-center text-center p-4 bg-gray-50 dark:bg-slate-900/50 rounded-xl">
                            <svg class="w-8 h-8 text-gray-400 dark:text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Status</span>
                            <span class="text-sm font-bold text-<?php echo $property['status'] === 'available' ? 'green' : 'orange'; ?>-600 dark:text-<?php echo $property['status'] === 'available' ? 'green' : 'orange'; ?>-400 mt-1 capitalize"><?php echo $property['status']; ?></span>
                        </div>
                        <div class="flex flex-col items-center justify-center text-center p-4 bg-gray-50 dark:bg-slate-900/50 rounded-xl">
                            <svg class="w-8 h-8 text-gray-400 dark:text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">City</span>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-1"><?php echo htmlspecialchars($property['city']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                        About this property
                    </h3>
                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed text-lg">
                        <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>
                </div>

                <!-- Map -->
                <?php if (!empty($property['map_location'])): 
                    $map_url = htmlspecialchars($property['map_location']);
                    $is_embed = (strpos($map_url, '/embed') !== false || strpos($map_url, 'maps.google.com/maps?q=') !== false);
                ?>
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-3 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7l6-3 5.447 2.724A1 1 0 0121 7.618v10.764a1 1 0 01-1.447.894L15 17l-6 3z"></path></svg>
                            Location Map
                        </h3>
                        <?php if ($is_embed): ?>
                            <div class="w-full rounded-xl overflow-hidden shadow-inner border border-gray-200 dark:border-slate-700 bg-gray-100" style="height: 450px;">
                                <iframe src="<?php echo $map_url; ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="w-full rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-8 text-center bg-gray-50 dark:bg-slate-900/50">
                                <svg class="w-16 h-16 text-brand-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <p class="text-gray-600 dark:text-gray-400 text-lg mb-6">The landlord has provided a map link for this property.</p>
                                <a href="<?php 
                                    $clean_url = trim($map_url);
                                    echo preg_match('/^https?:\/\//i', $clean_url) ? $clean_url : 'https://' . $clean_url; 
                                ?>" target="_blank" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-md transition-all transform hover:-translate-y-0.5">
                                    Open in Google Maps
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar (Right, 1 col) -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    
                    <!-- Contact Card -->
                    <div class="glass-panel dark:bg-slate-800/90 dark:border-slate-700 p-8 rounded-2xl shadow-xl border border-white/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none">
                            <svg class="w-32 h-32 text-brand-600 dark:text-brand-400" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-7 21c-4.962 0-9-4.037-9-9s4.038-9 9-9 9 4.037 9 9-4.038 9-9 9zm2-13.437v-1.563h-4v1.563c-1.396.34-2.5 1.545-2.5 2.937 0 1.657 1.343 3 3 3h2c.552 0 1 .449 1 1s-.448 1-1 1h-3.5v1.562h4v-1.562c1.442-.361 2.5-1.586 2.5-3.048 0-1.657-1.343-3-3-3h-2c-.552 0-1-.448-1-1s.448-1 1-1h3.5z"/></svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 relative z-10">Property Manager</h3>
                        
                        <div class="flex items-center mb-6 relative z-10">
                            <div class="w-16 h-16 bg-gradient-to-br from-brand-400 to-brand-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-md">
                                <?php echo strtoupper(substr($property['owner_name'], 0, 1)); ?>
                            </div>
                            <div class="ml-4">
                                <p class="text-lg font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($property['owner_name']); ?></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Verified Landlord</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4 mb-8 relative z-10">
                            <a href="tel:<?php echo htmlspecialchars($property['owner_phone']); ?>" class="flex items-center p-3 sm:p-4 rounded-xl border border-gray-200 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-800 transition-colors group">
                                <div class="w-10 h-10 rounded-full bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Phone</p>
                                    <p class="text-gray-800 dark:text-gray-200 font-medium"><?php echo htmlspecialchars($property['owner_phone']); ?></p>
                                </div>
                            </a>
                        </div>

                        <!-- Action Button -->
                        <div class="relative z-10">
                            <?php if (is_logged_in() && $_SESSION['user_role'] === 'tenant' && $property['status'] === 'available'): ?>
                                <?php if (isset($_GET['success']) && $_GET['success'] == 'applied'): ?>
                                    <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 p-4 rounded-xl flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <div>
                                            <p class="font-bold">Application Sent!</p>
                                            <p class="text-sm mt-1 opacity-90">The landlord has been notified.</p>
                                        </div>
                                    </div>
                                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'already_applied'): ?>
                                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 p-4 rounded-xl flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div>
                                            <p class="font-bold">Already Applied</p>
                                            <p class="text-sm mt-1 opacity-90">You have an active application for this property.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <form action="../actions/apply_property_action.php" method="POST" class="space-y-4">
                                        <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                                        <div>
                                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message to Landlord</label>
                                            <textarea name="message" id="message" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-shadow resize-none" placeholder="Hi, I'm interested in this property..."></textarea>
                                        </div>
                                        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex justify-center items-center">
                                            Send Application
                                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php elseif (!is_logged_in()): ?>
                                <div class="bg-gray-50 dark:bg-slate-900/50 p-6 rounded-xl border border-gray-100 dark:border-slate-700 text-center">
                                    <p class="text-gray-600 dark:text-gray-400 mb-4 font-medium">Log in to contact the owner and apply for this property.</p>
                                    <a href="login.php" class="block w-full bg-gray-900 dark:bg-white dark:text-gray-900 text-white font-bold py-3 px-4 rounded-xl hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">Log In</a>
                                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">New here? <a href="register.php" class="text-brand-600 dark:text-brand-400 font-bold hover:underline">Register</a></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
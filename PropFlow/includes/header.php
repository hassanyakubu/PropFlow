<!-- Dark Mode Overlay -->
<div class="dark-overlay"></div>

<header
    class="sticky top-0 z-50 transition-all duration-300 glass-panel bg-white/80 dark:bg-slate-900/90 shadow-sm border-b border-gray-100 dark:border-slate-800 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="<?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? '../admin/dashboard.php' : '../public/index.php'; ?>" class="flex items-center gap-2 group">
                    <img class="h-10 w-auto transform transition-transform group-hover:scale-110"
                        src="../public/assets/images/logo.png" alt="PropFlow">
                    <span class="font-bold text-2xl text-brand-600 tracking-tight">PropFlow</span>
                </a>
            </div>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex space-x-8">
                <!-- Navigation links are available in the side menus for logged-in users -->
            </nav>

            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                <button id="theme-toggle"
                    class="p-2 rounded-full text-gray-500 hover:bg-gray-100 focus:outline-none transition-colors"
                    title="Toggle Dark Mode">
                    <!-- Sun Icon (Shows in Dark Mode - Click to switch to Light) -->
                    <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <!-- Moon Icon (Shows in Light Mode - Click to switch to Dark) -->
                    <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <span class="sr-only">Toggle Theme</span>
                </button>

                <?php if (is_logged_in()): ?>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-semibold text-gray-700">Hi,
                            <?php echo htmlspecialchars(get_user_first_name() ?? 'User'); ?></span>
                        <a href="../public/logout.php"
                            class="btn bg-white text-red-500 border border-red-200 hover:bg-red-50 hover:border-red-300 px-4 py-2 rounded-full text-sm font-medium transition-all shadow-sm">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="../public/login.php"
                        class="text-gray-600 hover:text-brand-600 font-medium text-sm transition-colors">Login</a>
                    <a href="../public/register.php"
                        class="bg-brand-600 text-white hover:bg-brand-700 px-5 py-2 rounded-full text-sm font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
    // Simple Dark Mode Toggle
    const toggleBtn = document.getElementById('theme-toggle');
    const body = document.body;
    const html = document.documentElement;

    // Sync body with html class which was processed in head_assets.php
    if (html.classList.contains('dark')) {
        body.classList.add('dark');
    }

    toggleBtn.addEventListener('click', () => {
        body.classList.toggle('dark');
        html.classList.toggle('dark');
        localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    });
</script>
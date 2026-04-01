<!-- Favicon -->
<link rel="icon" type="image/png" href="/PropFlow/public/assets/images/logo.png?v=9">
<link rel="shortcut icon" type="image/png" href="/PropFlow/public/assets/images/logo.png?v=9">
<link rel="apple-touch-icon" href="/PropFlow/public/assets/images/logo.png?v=9">

<!-- Styles & Scripts -->
<link rel="stylesheet" href="../public/assets/css/style.css?v=5">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#f0f9ff',
                        100: '#e0f2fe',
                        200: '#bae6fd',
                        300: '#7dd3fc',
                        400: '#38bdf8',
                        500: '#0ea5e9',
                        600: '#0284c7',
                        700: '#0369a1',
                        800: '#075985',
                        900: '#0c4a6e',
                    }
                }
            }
        }
    }
</script>
<script>
    // Check for saved theme preference or use system preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
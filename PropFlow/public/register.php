<?php
require_once '../settings/core.php';

if (is_logged_in()) {
    $role = $_SESSION['user_role'];
    header("Location: " . ($role === 'landlord' ? '../landlord/dashboard.php' : '../tenant/dashboard.php'));
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 glass-panel p-10 rounded-2xl shadow-2xl">
            <div>
                <h2 class="mt-2 text-center text-3xl font-extrabold text-brand-900">
                    Create Account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Join PropFlow today
                </p>
            </div>

            <?php if ($error || $success): ?>
                <div class="<?php echo $success ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700'; ?> border-l-4 p-4 mb-4"
                    role="alert">
                    <p class="font-bold"><?php echo $success ? 'Success' : 'Error'; ?></p>
                    <p class="text-sm">
                        <?php
                        if ($error === 'exists')
                            echo 'Email already registered';
                        elseif ($error === 'failed')
                            echo 'Registration failed. Please try again';
                        elseif ($error === 'weak_password')
                            echo 'Password must be > 6 chars, with at least 1 number and 1 special char';
                        elseif ($success === '1')
                            echo 'Registration successful! Please login';
                        else
                            echo htmlspecialchars($error);
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-4" action="../actions/register_action.php" method="POST" autocomplete="off">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required autocomplete="off"
                            class="mt-1 appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="email" required autocomplete="off"
                            class="mt-1 appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required autocomplete="off"
                            class="mt-1 appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">I am a</label>
                        <select id="role" name="role" required autocomplete="off"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm rounded-lg">
                            <option value="">Select role</option>
                            <option value="landlord">Landlord</option>
                            <option value="tenant">Tenant</option>
                        </select>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required minlength="6"
                            autocomplete="new-password"
                            class="mt-1 appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Min 6 chars, 1 number, 1 special char</p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm
                            Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                            autocomplete="new-password"
                            class="mt-1 appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-full text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-lg transition-transform transform hover:-translate-y-0.5">
                        Register
                    </button>
                </div>
                <!-- Force clean fields against aggressive browser autofill -->
                <script>
                    setTimeout(() => {
                        document.getElementById('full_name').value = '';
                        document.getElementById('email').value = '';
                        document.getElementById('phone').value = '';
                        document.getElementById('password').value = '';
                        document.getElementById('confirm_password').value = '';
                    }, 50);
                </script>
            </form>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="login.php" class="font-medium text-brand-600 hover:text-brand-500 transition-colors">
                        Login here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            // Password Complexity Check
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^a-zA-Z0-9]/.test(password);

            const showError = (message) => {
                // Create or find an error banner
                let errorBanner = document.getElementById('js-error-banner');
                if (!errorBanner) {
                    errorBanner = document.createElement('div');
                    errorBanner.id = 'js-error-banner';
                    errorBanner.className = 'bg-red-50 border-red-500 text-red-700 border-l-4 p-4 mb-4';
                    errorBanner.innerHTML = '<p class="font-bold">Error</p><p class="text-sm" id="js-error-text"></p>';
                    const form = document.querySelector('form');
                    form.parentNode.insertBefore(errorBanner, form);
                }
                document.getElementById('js-error-text').innerText = message;

                // Scroll to top to see error
                window.scrollTo(0, 0);
            };

            if (password.length <= 6 || !hasNumber || !hasSpecial) {
                e.preventDefault();
                showError('Password must be more than 6 characters, contain at least one number, and one special character.');
                return;
            }

            if (password !== confirm) {
                e.preventDefault();
                showError('Passwords do not match');
            }
        });
    </script>
</body>

</html>
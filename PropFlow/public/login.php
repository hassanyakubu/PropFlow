<?php
require_once '../settings/core.php';

if (is_logged_in()) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: " . ($role === 'landlord' ? '../landlord/dashboard.php' : '../tenant/dashboard.php'));
    }
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PropFlow</title>
    <?php include '../includes/head_assets.php'; ?>
</head>

<body class="font-sans text-gray-800 antialiased min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 glass-panel p-10 rounded-2xl shadow-2xl">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-brand-900">
                    Sign In
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Access your account to manage your property
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4" role="alert">
                    <p class="font-bold text-red-700">Error</p>
                    <p class="text-sm text-red-600">
                        <?php
                        if ($error === 'invalid')
                            echo 'Invalid email or password';
                        elseif ($error === 'suspended')
                            echo 'Your account has been suspended';
                        elseif ($error === 'required')
                            echo 'Please login to continue';
                        else
                            echo htmlspecialchars($error);
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="../actions/login_action.php" method="POST" autocomplete="off">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="mb-4">
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="off" required
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 focus:z-10 sm:text-sm"
                            placeholder="Email address" value="">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 focus:z-10 sm:text-sm"
                            placeholder="Password" value="">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-full text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-lg transition-transform transform hover:-translate-y-0.5">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-brand-300 group-hover:text-brand-200"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        Sign in
                    </button>
                </div>
                <!-- Force clean fields against aggressive browser autofill -->
                <script>
                    setTimeout(() => {
                        document.getElementById('email').value = '';
                        document.getElementById('password').value = '';
                    }, 50);
                </script>
            </form>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account?
                    <a href="register.php" class="font-medium text-brand-600 hover:text-brand-500 transition-colors">
                        Register here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
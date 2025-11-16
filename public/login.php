<?php
require __DIR__ . '/../src/db.php';

session_start();

$message = $_SESSION['flash_message'] ?? '';
if (isset($_SESSION['flash_message'])) {
    unset($_SESSION['flash_message']);
}

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $message = 'Registration successful! Please login with your credentials.';
}

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $_SESSION['flash_message'] = 'Email and password are required.';
        header('Location: login.php');
        exit;
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['flash_message'] = 'Invalid credentials.';
        header('Location: login.php');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];

    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white min-h-screen flex items-center justify-center">

    <main class="bg-[#111827] w-full max-w-md rounded-xl shadow-xl p-8 mx-4">

        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold">Sign In</h1>
        </header>

        <?php if ($message): ?>
            <p class="mb-4 text-sm text-white bg-green-900/40 border border-green-700 rounded-md px-3 py-2">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <section aria-labelledby="login-title">
            <h2 id="login-title" class="sr-only">Login Form</h2>

            <form action="login.php" method="POST" class="space-y-6">

                <div class="flex flex-col">
                    <label for="email" class="text-sm font-medium mb-1">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600
                               focus:outline-none focus:ring-2 focus:ring-green-500 text-white"
                    >
                </div>

                <div class="flex flex-col">
                    <label for="password" class="text-sm font-medium mb-1">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600
                               focus:outline-none focus:ring-2 focus:ring-green-500 text-white"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-green-700 hover:bg-green-800 transition-colors
                           py-2 rounded-lg font-semibold text-white text-lg shadow-md"
                >
                    Login
                </button>

            </form>
        </section>

        <footer class="text-center text-sm text-gray-400 mt-6">
            Don't have an account?
            <a href="register.php" class="text-blue-400 hover:underline">Create one</a>
        </footer>

    </main>

</body>
</html>

<?php
require __DIR__ . '/../src/db.php';

session_start();

$message = $_SESSION['flash_message'] ?? '';
if (isset($_SESSION['flash_message'])) {
    unset($_SESSION['flash_message']);
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

function validateRegistrationData(string $name, string $email, string $password): ?string {
    if ($name === '' || $email === '' || $password === '') return 'All fields are required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Please enter a valid email address.';
    if (strlen($password) < 6) return 'Password must be at least 6 characters.';
    if (!preg_match('/[0-9]/', $password)) return 'Password must contain at least one number.';
    if (!preg_match('/[\W_]/', $password)) return 'Password must contain at least one special character.';
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $error = validateRegistrationData($name, $email, $password);
    if ($error) {
        $_SESSION['flash_message'] = $error;
        header('Location: register.php');
        exit;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $userExists = $stmt->fetch();
    
    if ($userExists) {
        $_SESSION['flash_message'] = 'Email is already registered.';
        header('Location: register.php');
        exit;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hashedPassword]);
    header('Location: login.php?registered=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <main class="bg-[#111827] w-full max-w-md rounded-xl shadow-xl p-8 mx-4">
        <header class="mb-6 text-center">
            <h1 class="text-3xl font-bold">Create Account</h1>
        </header>

        <?php if ($message !== ''): ?>
            <p class="mb-4 text-sm text-red-400 bg-red-900/40 border border-red-700 rounded-md px-3 py-2">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <section aria-labelledby="register-title">
            <h2 id="register-title" class="sr-only">Registration Form</h2>
            <form action="register.php" method="POST" class="space-y-6">
                <div class="flex flex-col">
                    <label for="name" class="text-sm font-medium mb-1">Name</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600
                               focus:outline-none focus:ring-2 focus:ring-green-500 text-white"
                    >
                </div>
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
                        minlength="6"
                        pattern="^(?=.*[0-9])(?=.*[\W_]).{6,}$"
                        title="Must be at least 6 characters, include one number and one special character."
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600
                               focus:outline-none focus:ring-2 focus:ring-green-500 text-white"
                    >
                </div>
                <button
                    type="submit"
                    class="w-full bg-green-700 hover:bg-green-800 transition-colors
                           py-2 rounded-lg font-semibold text-white text-lg shadow-md"
                >
                    Register
                </button>
            </form>
        </section>

        <footer class="text-center text-sm text-gray-400 mt-6">
            Already have an account?
            <a href="login.php" class="text-blue-400 hover:underline">Login</a>
        </footer>
    </main>
</body>
</html>
